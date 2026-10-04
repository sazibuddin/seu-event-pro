@extends('layouts.app')

@section('content')
{{-- Hero --}}
<section class="bg-gradient-to-br from-indigo-600 to-purple-700 text-white">
    <div class="mx-auto max-w-6xl px-4 py-16 text-center sm:py-20">
        <h1 class="text-3xl font-bold sm:text-5xl">Discover &amp; join tech events</h1>
        <p class="mx-auto mt-4 max-w-2xl text-indigo-100">Workshops, hackathons, seminars and meetups. Find your next event and reserve a seat in seconds.</p>

        <form method="GET" action="{{ route('home') }}" class="mx-auto mt-8 flex max-w-xl gap-2">
            @if (request('category'))
                <input type="hidden" name="category" value="{{ request('category') }}">
            @endif
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search events by title..."
                   class="flex-1 rounded-lg border-0 bg-white px-4 py-3 text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-indigo-300 focus:outline-none">
            <button type="submit" class="rounded-lg bg-gray-900 px-5 py-3 font-semibold hover:bg-gray-800">Search</button>
        </form>
    </div>
</section>

<div class="mx-auto max-w-6xl px-4 py-10">
    @include('partials.flash')

    {{-- Category pills --}}
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('home', array_filter(['search' => request('search')])) }}"
           class="rounded-full px-4 py-1.5 text-sm font-medium {{ request('category') ? 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-100' : 'bg-indigo-600 text-white' }}">
            All
        </a>
        @foreach ($categories as $category)
            <a href="{{ route('home', array_filter(['category' => $category, 'search' => request('search')])) }}"
               class="rounded-full px-4 py-1.5 text-sm font-medium {{ request('category') === $category ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-100' }}">
                {{ $category }}
            </a>
        @endforeach
    </div>

    <div class="mt-8 flex items-baseline justify-between">
        <h2 class="text-2xl font-bold">Upcoming events</h2>
        <p class="text-sm text-gray-500">{{ $events->total() }} found</p>
    </div>

    @if ($events->isEmpty())
        <div class="mt-6 rounded-xl border border-dashed border-gray-300 bg-white p-12 text-center text-gray-500">
            No events match your search.
            @if (request('search') || request('category'))
                <a href="{{ route('home') }}" class="font-medium text-indigo-600 hover:underline">Clear filters</a>
            @endif
        </div>
    @else
        <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($events as $event)
                @include('partials.event-card', ['event' => $event])
            @endforeach
        </div>

        <div class="mt-8">{{ $events->links() }}</div>
    @endif
</div>
@endsection
