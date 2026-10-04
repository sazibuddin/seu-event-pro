{{-- Shared by create.blade.php and edit.blade.php. Expects $event, $action, $method, $submitLabel. --}}
@php
    $input = 'mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-none';
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="max-w-3xl space-y-5 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    @if ($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">Please fix the errors below.</div>
    @endif

    <div>
        <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
        <input id="title" type="text" name="title" value="{{ old('title', $event->title) }}" required class="{{ $input }}">
        @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
            <select id="category" name="category" required class="{{ $input }}">
                <option value="">Select a category</option>
                @foreach (\App\Models\Event::CATEGORIES as $category)
                    <option value="{{ $category }}" @selected(old('category', $event->category) === $category)>{{ $category }}</option>
                @endforeach
            </select>
            @error('category') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
            <select id="status" name="status" required class="{{ $input }}">
                @foreach (\App\Models\Event::STATUSES as $status)
                    <option value="{{ $status }}" @selected(old('status', $event->status) === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            @error('status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="event_date" class="block text-sm font-medium text-gray-700">Date &amp; time</label>
            <input id="event_date" type="datetime-local" name="event_date" required class="{{ $input }}"
                   value="{{ old('event_date', $event->event_date?->format('Y-m-d\TH:i')) }}">
            @error('event_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="capacity" class="block text-sm font-medium text-gray-700">Capacity (seats)</label>
            <input id="capacity" type="number" name="capacity" min="1" value="{{ old('capacity', $event->capacity) }}" required class="{{ $input }}">
            @error('capacity') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    <div>
        <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
        <input id="location" type="text" name="location" value="{{ old('location', $event->location) }}" required class="{{ $input }}">
        @error('location') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
        <textarea id="description" name="description" rows="6" required class="{{ $input }}">{{ old('description', $event->description) }}</textarea>
        @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="banner_image" class="block text-sm font-medium text-gray-700">Banner image (optional, max 2 MB)</label>
        @if ($event->bannerUrl())
            <img src="{{ $event->bannerUrl() }}" alt="Current banner" class="mt-2 h-32 rounded-lg object-cover">
            <p class="mt-1 text-xs text-gray-500">Upload a new image to replace the current one.</p>
        @endif
        <input id="banner_image" type="file" name="banner_image" accept="image/png,image/jpeg,image/webp"
               class="mt-2 block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:font-medium file:text-indigo-700 hover:file:bg-indigo-100">
        @error('banner_image') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div class="flex items-center gap-3 border-t border-gray-100 pt-5">
        <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 font-semibold text-white hover:bg-indigo-700">{{ $submitLabel }}</button>
        <a href="{{ route('admin.events.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
    </div>
</form>
