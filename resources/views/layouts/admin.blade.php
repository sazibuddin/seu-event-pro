<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') | {{ config('app.name') }} Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-100 text-gray-900 antialiased">
    @php
        $links = [
            ['route' => 'admin.dashboard', 'match' => 'admin.dashboard', 'label' => 'Dashboard'],
            ['route' => 'admin.events.index', 'match' => 'admin.events.*', 'label' => 'Manage Events'],
            ['route' => 'admin.events.create', 'match' => 'admin.events.create', 'label' => 'Create Event'],
        ];
    @endphp

    <div class="md:flex">
        <aside class="bg-gray-900 text-gray-300 md:fixed md:inset-y-0 md:w-64">
            <div class="flex items-center justify-between px-6 py-5 md:block">
                <a href="{{ route('admin.dashboard') }}" class="text-lg font-bold text-white">SEUEvent<span class="text-indigo-400">Pro</span></a>
                <p class="text-xs text-gray-500 md:mt-1">Admin Panel</p>
            </div>

            <nav class="flex gap-1 overflow-x-auto px-3 pb-3 text-sm md:flex-col md:pb-0">
                @foreach ($links as $link)
                    @php
                        // "Manage Events" should not light up on the Create page.
                        $active = request()->routeIs($link['match'])
                            && ! ($link['route'] === 'admin.events.index' && request()->routeIs('admin.events.create'));
                    @endphp
                    <a href="{{ route($link['route']) }}"
                       class="whitespace-nowrap rounded-md px-3 py-2 {{ $active ? 'bg-gray-800 text-white' : 'hover:bg-gray-800 hover:text-white' }}">
                        {{ $link['label'] }}
                    </a>
                @endforeach
                <a href="{{ route('home') }}" class="whitespace-nowrap rounded-md px-3 py-2 hover:bg-gray-800 hover:text-white">View Site</a>
            </nav>

            <div class="hidden border-t border-gray-800 px-6 py-4 text-sm md:absolute md:inset-x-0 md:bottom-0 md:block">
                <p class="text-white">{{ auth()->user()->name }}</p>
                <p class="truncate text-xs text-gray-500">{{ auth()->user()->email }}</p>
            </div>
        </aside>

        <div class="flex-1 md:ml-64">
            <header class="flex items-center justify-between border-b border-gray-200 bg-white px-6 py-4">
                <h1 class="text-lg font-semibold">@yield('title', 'Admin')</h1>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="rounded-md border border-gray-300 px-3 py-1.5 text-sm hover:bg-gray-50">Logout</button>
                </form>
            </header>

            <main class="p-6">
                @include('partials.flash')
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
