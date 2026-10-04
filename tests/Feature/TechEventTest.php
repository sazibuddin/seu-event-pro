<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TechEventTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    private function eventData(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Laravel Workshop',
            'category' => 'Workshop',
            'description' => 'Learn Laravel.',
            'location' => 'Lab 3',
            'event_date' => now()->addWeek()->format('Y-m-d\TH:i'),
            'capacity' => 2,
            'status' => 'published',
        ], $overrides);
    }

    // ---------- Public pages ----------

    public function test_home_lists_only_upcoming_published_events_and_filters(): void
    {
        Event::factory()->create(['title' => 'Visible Hackathon', 'category' => 'Hackathon']);
        Event::factory()->create(['title' => 'Visible Seminar', 'category' => 'Seminar']);
        Event::factory()->create(['title' => 'Secret Draft', 'status' => 'draft']);
        Event::factory()->create(['title' => 'Old Past Event', 'event_date' => now()->subDay()]);

        $this->get('/')->assertOk()
            ->assertSee('Visible Hackathon')->assertSee('Visible Seminar')
            ->assertDontSee('Secret Draft')->assertDontSee('Old Past Event');

        $this->get('/?category=Hackathon')->assertSee('Visible Hackathon')->assertDontSee('Visible Seminar');
        $this->get('/?search=Seminar')->assertSee('Visible Seminar')->assertDontSee('Visible Hackathon');
    }

    public function test_event_detail_uses_slug_and_hides_drafts_from_public(): void
    {
        $event = Event::factory()->create(['slug' => 'my-event', 'capacity' => 10]);
        $draft = Event::factory()->create(['slug' => 'draft-event', 'status' => 'draft']);

        $this->get('/events/my-event')->assertOk()->assertSee($event->title)->assertSee('10')->assertSee('Log in to book');
        $this->get('/events/draft-event')->assertNotFound();
        $this->actingAs($this->admin())->get('/events/draft-event')->assertOk();
        $this->assertSame(url('/events/my-event'), route('events.show', $event));
    }

    public function test_output_is_escaped_against_xss(): void
    {
        Event::factory()->create(['slug' => 'xss', 'title' => '<script>alert(1)</script>', 'description' => '<b>bold</b>']);

        $this->get('/events/xss')->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<b>bold</b>', false);
    }

    // ---------- User auth ----------

    public function test_user_can_register_and_is_never_admin(): void
    {
        $this->post('/register', [
            'name' => 'Rahim', 'email' => 'rahim@example.com', 'phone' => '0171',
            'password' => 'secret123', 'password_confirmation' => 'secret123',
            'role' => 'admin', // attempted privilege escalation
        ])->assertRedirect(route('home'));

        $user = User::where('email', 'rahim@example.com')->first();
        $this->assertAuthenticatedAs($user);
        $this->assertSame('user', $user->fresh()->role);
        $this->assertSame('0171', $user->phone);
    }

    public function test_register_validation_and_unique_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->post('/register', ['name' => '', 'email' => 'taken@example.com', 'password' => '1', 'password_confirmation' => '2'])
            ->assertSessionHasErrors(['name', 'email', 'password']);
        $this->assertGuest();
    }

    public function test_user_login_and_logout(): void
    {
        $user = User::factory()->create(['password' => 'secret123']);

        $this->post('/login', ['email' => $user->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post('/login', ['email' => $user->email, 'password' => 'secret123'])->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);

        $this->post('/logout')->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_admin_using_user_login_goes_to_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create(['password' => 'secret123']);

        $this->post('/login', ['email' => $admin->email, 'password' => 'secret123'])->assertRedirect(route('admin.dashboard'));
    }

    // ---------- Admin auth & access control ----------

    public function test_admin_login_only_accepts_admins(): void
    {
        $user = User::factory()->create(['password' => 'secret123']);
        $admin = User::factory()->admin()->create(['password' => 'secret123']);

        $this->get('/admin/login')->assertOk()->assertSee('Admin Login');

        $this->post('/admin/login', ['email' => $user->email, 'password' => 'secret123'])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'secret123'])->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);

        $this->post('/admin/logout')->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }

    public function test_admin_routes_are_protected(): void
    {
        $event = Event::factory()->create();

        // Guests go to the admin login page
        $this->get('/admin/dashboard')->assertRedirect(route('admin.login'));
        $this->get('/admin/events')->assertRedirect(route('admin.login'));
        // Guests on user pages go to the user login page
        $this->get('/my-registrations')->assertRedirect(route('login'));

        // Normal users get 403 everywhere in /admin
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->get('/admin/dashboard')->assertForbidden();
        $this->get('/admin/events')->assertForbidden();
        $this->get("/admin/events/{$event->id}/attendees")->assertForbidden();
        $this->post('/admin/events', $this->eventData())->assertForbidden();
        $this->delete("/admin/events/{$event->id}")->assertForbidden();
        $this->assertDatabaseHas('events', ['id' => $event->id]);
    }

    public function test_admin_pages_render(): void
    {
        $event = Event::factory()->create();
        Registration::create(['event_id' => $event->id, 'user_id' => User::factory()->create()->id, 'ticket_code' => 'TCK-111111', 'registered_at' => now()]);

        $this->actingAs($this->admin());
        $this->get('/admin/dashboard')->assertOk()->assertSee('Total Events')->assertSee('TCK-111111');
        $this->get('/admin/events')->assertOk()->assertSee($event->title);
        $this->get('/admin/events/create')->assertOk();
        $this->get("/admin/events/{$event->id}/edit")->assertOk()->assertSee($event->title);
        $this->get("/admin/events/{$event->id}/attendees")->assertOk()->assertSee('TCK-111111');
    }

    public function test_admin_event_list_shows_newest_created_first(): void
    {
        Event::factory()->create(['title' => 'Older Event', 'event_date' => now()->addMonth()]);
        Event::factory()->create(['title' => 'Newest Event', 'event_date' => now()->addDay()]);

        $this->actingAs($this->admin())->get('/admin/events')
            ->assertSeeInOrder(['Newest Event', 'Older Event']);
    }

    // ---------- Admin event CRUD ----------

    public function test_admin_can_create_event_with_banner_and_unique_slug(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $this->actingAs($admin);

        $this->post('/admin/events', $this->eventData(['banner_image' => UploadedFile::fake()->image('b.jpg')]))
            ->assertRedirect(route('admin.events.index'));
        $this->post('/admin/events', $this->eventData())->assertRedirect(route('admin.events.index'));

        $events = Event::orderBy('id')->get();
        $this->assertSame(['laravel-workshop', 'laravel-workshop-2'], $events->pluck('slug')->all());
        $this->assertSame($admin->id, $events[0]->user_id);
        Storage::disk('public')->assertExists($events[0]->banner_image);
    }

    public function test_event_validation(): void
    {
        $this->actingAs($this->admin());

        $this->post('/admin/events', $this->eventData([
            'title' => '', 'category' => 'Party', 'capacity' => 0, 'status' => 'live',
            'event_date' => now()->subDay()->format('Y-m-d\TH:i'),
        ]))->assertSessionHasErrors(['title', 'category', 'capacity', 'status', 'event_date']);

        $this->assertDatabaseCount('events', 0);
    }

    public function test_admin_can_update_and_delete_event(): void
    {
        $this->actingAs($this->admin());
        $event = Event::factory()->create(['capacity' => 5]);
        Registration::create(['event_id' => $event->id, 'user_id' => User::factory()->create()->id, 'ticket_code' => 'TCK-222222', 'registered_at' => now()]);
        Registration::create(['event_id' => $event->id, 'user_id' => User::factory()->create()->id, 'ticket_code' => 'TCK-333333', 'registered_at' => now()]);

        // Can't shrink capacity below seats already taken
        $this->put("/admin/events/{$event->id}", $this->eventData(['capacity' => 1]))->assertSessionHasErrors('capacity');

        $this->put("/admin/events/{$event->id}", $this->eventData(['title' => 'Renamed Event', 'capacity' => 2]))
            ->assertRedirect(route('admin.events.index'));
        $event->refresh();
        $this->assertSame('Renamed Event', $event->title);
        $this->assertSame('renamed-event', $event->slug);
        $this->assertSame(2, $event->capacity);

        $this->delete("/admin/events/{$event->id}")->assertRedirect(route('admin.events.index'));
        $this->assertDatabaseCount('events', 0);
        $this->assertDatabaseCount('registrations', 0); // cascade
    }

    public function test_attendee_search_and_status_update(): void
    {
        $this->actingAs($this->admin());
        $event = Event::factory()->create(['capacity' => 1]);
        $alice = User::factory()->create(['name' => 'Alice Rahman']);
        $bob = User::factory()->create(['name' => 'Bob Karim']);
        $ra = Registration::create(['event_id' => $event->id, 'user_id' => $alice->id, 'ticket_code' => 'TCK-444444', 'registered_at' => now()]);
        $rb = Registration::create(['event_id' => $event->id, 'user_id' => $bob->id, 'ticket_code' => 'TCK-555555', 'registered_at' => now(), 'registration_status' => 'cancelled']);

        $url = "/admin/events/{$event->id}/attendees";
        $this->get("$url?search=Alice")->assertSee('Alice Rahman')->assertDontSee('Bob Karim');
        $this->get("$url?search=TCK-555555")->assertSee('Bob Karim')->assertDontSee('Alice Rahman');

        $this->patch("$url/{$ra->id}", ['registration_status' => 'attended'])->assertSessionHas('success');
        $this->assertSame('attended', $ra->fresh()->registration_status);

        // Event full (capacity 1) -> cannot restore Bob's cancelled ticket
        $this->patch("$url/{$rb->id}", ['registration_status' => 'confirmed'])->assertSessionHas('error');
        $this->assertSame('cancelled', $rb->fresh()->registration_status);

        $this->patch("$url/{$ra->id}", ['registration_status' => 'hacked'])->assertSessionHasErrors('registration_status');

        // Registration from another event can't be edited via this URL
        $other = Event::factory()->create();
        $this->patch("/admin/events/{$other->id}/attendees/{$ra->id}", ['registration_status' => 'cancelled'])->assertNotFound();
    }

    // ---------- Registration flow ----------

    public function test_user_registers_gets_ticket_and_cannot_double_register(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create(['slug' => 'ev', 'capacity' => 10]);
        $this->actingAs($user);

        $this->post('/events/ev/register')->assertSessionHas('success');
        $reg = Registration::first();
        $this->assertMatchesRegularExpression('/^TCK-\d{6}$/', $reg->ticket_code);
        $this->assertSame('confirmed', $reg->registration_status);

        $this->post('/events/ev/register')->assertSessionHas('error');
        $this->assertDatabaseCount('registrations', 1);

        $this->get('/events/ev')->assertSee($reg->ticket_code)->assertSee('Cancel registration');
        $this->get('/my-registrations')->assertOk()->assertSee($event->title)->assertSee($reg->ticket_code);
    }

    public function test_guest_cannot_register(): void
    {
        Event::factory()->create(['slug' => 'ev']);
        $this->post('/events/ev/register')->assertRedirect(route('login'));
        $this->assertDatabaseCount('registrations', 0);
    }

    public function test_capacity_is_enforced(): void
    {
        $event = Event::factory()->create(['slug' => 'ev', 'capacity' => 1]);

        $this->actingAs(User::factory()->create())->post('/events/ev/register')->assertSessionHas('success');
        $this->actingAs(User::factory()->create())->post('/events/ev/register')->assertSessionHas('error', 'Sorry, this event is fully booked.');

        $this->assertSame(1, $event->registrations()->count());
        $this->get('/events/ev')->assertSee('Fully booked');
    }

    public function test_cannot_register_for_draft_cancelled_or_past_events(): void
    {
        $this->actingAs(User::factory()->create());
        Event::factory()->create(['slug' => 'cancelled', 'status' => 'cancelled']);
        Event::factory()->create(['slug' => 'past', 'event_date' => now()->subDay()]);
        Event::factory()->create(['slug' => 'draft', 'status' => 'draft']);

        $this->post('/events/cancelled/register')->assertSessionHas('error');
        $this->post('/events/past/register')->assertSessionHas('error');
        $this->post('/events/draft/register')->assertSessionHas('error');
        $this->assertDatabaseCount('registrations', 0);
    }

    public function test_admin_cannot_register_for_events(): void
    {
        Event::factory()->create(['slug' => 'ev']);
        $this->actingAs($this->admin())->post('/events/ev/register')->assertSessionHas('error');
        $this->assertDatabaseCount('registrations', 0);
    }

    public function test_cancel_frees_seat_and_user_can_register_again(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create(['slug' => 'ev', 'capacity' => 1]);
        $this->actingAs($user);

        $this->post('/events/ev/register');
        $this->delete('/events/ev/register')->assertSessionHas('success');
        $this->assertSame('cancelled', Registration::first()->registration_status);
        $this->assertSame(1, $event->fresh()->seatsLeft());

        // Re-register reuses the same row (unique user+event index) with a new ticket
        $this->post('/events/ev/register')->assertSessionHas('success');
        $this->assertDatabaseCount('registrations', 1);
        $this->assertSame('confirmed', Registration::first()->registration_status);
        $this->assertSame(0, $event->fresh()->seatsLeft());

        // Nothing to cancel twice
        $this->delete('/events/ev/register');
        $this->delete('/events/ev/register')->assertSessionHas('error');
    }

    public function test_csrf_protection_is_active_on_forms(): void
    {
        // Laravel skips CSRF checks in unit tests, so verify the token is printed in the forms instead.
        $this->get('/login')->assertSee('name="_token"', false);
        $this->get('/register')->assertSee('name="_token"', false);
        $this->get('/admin/login')->assertSee('name="_token"', false);
    }

    public function test_seeder_creates_working_admin(): void
    {
        $this->seed();

        $this->post('/admin/login', ['email' => 'admin@techevent.com', 'password' => 'admin12345'])
            ->assertRedirect(route('admin.dashboard'));
        $this->assertTrue(auth()->user()->isAdmin());
    }
}
