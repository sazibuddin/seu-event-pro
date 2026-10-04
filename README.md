# SEUEvent Pro

Event registration and management system. CSE 471 project.
Built with Laravel 13, Blade, Tailwind CSS v4 and MySQL.

## Setup

```bash
composer install
npm install && npm run build
cp .env.example .env        # then set DB_CONNECTION=mysql and the DB_* values
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link    # needed for event banner images
php artisan serve
```

Open http://127.0.0.1:8000

## Demo accounts (created by `DatabaseSeeder`)

| Role  | Login page    | Email               | Password   |
|-------|---------------|---------------------|------------|
| Admin | `/admin/login` | admin@techevent.com | admin12345 |
| User  | `/login`       | user@techevent.com  | user12345  |

## Features

- **Public:** browse upcoming events, search by title, filter by category, event detail page with a live seat counter.
- **Users:** sign up, log in, register for an event (gets a ticket code like `TCK-892314`), cancel and register again, see all tickets on **My Registrations**.
- **Admin:** dashboard stats, full event CRUD with banner upload, attendee list per event with search, and attendee status updates (confirmed, attended, cancelled).

## Rules enforced

- A user can register for an event only once (unique `[user_id, event_id]` index).
- Capacity can never be exceeded. The event row is locked during registration.
- Only published, upcoming events accept registrations. Drafts are hidden from the public.
- Event capacity can't be lowered below the number of seats already taken.
- `role` is not mass-assignable, so users can't make themselves admin.
- CSRF on every form, validation on every input, Eloquent everywhere, `{{ }}` escaping.

## Tests

```bash
php artisan test
```

23 feature tests cover auth, admin access control, CRUD, the registration rules and XSS escaping.
