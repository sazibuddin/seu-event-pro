<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Discover Tech Events') | {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-gray-50 text-gray-900 antialiased">
    <nav class="border-b border-gray-200 bg-white">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3 px-4 py-4">
            <a href="{{ route('home') }}" class="text-xl font-bold text-indigo-600">SEUEvent<span class="text-gray-900">Pro</span></a>

            <div class="flex flex-wrap items-center gap-2 text-sm font-medium">
                <a href="{{ route('home') }}" class="rounded-md px-3 py-2 text-gray-600 hover:bg-gray-100 hover:text-gray-900">Events</a>

                @auth
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="rounded-md px-3 py-2 text-gray-600 hover:bg-gray-100 hover:text-gray-900">Admin Panel</a>
                    @else
                        <a href="{{ route('registrations.index') }}" class="rounded-md px-3 py-2 text-gray-600 hover:bg-gray-100 hover:text-gray-900">My Registrations</a>
                    @endif
                    <span class="hidden px-2 text-gray-400 sm:inline">|</span>
                    <span class="hidden text-gray-700 sm:inline">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-md px-3 py-2 text-gray-600 hover:bg-gray-100 hover:text-gray-900">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="rounded-md px-3 py-2 text-gray-600 hover:bg-gray-100 hover:text-gray-900">Login</a>
                    <a href="{{ route('register') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">Sign Up</a>
                @endauth
            </div>
        </div>
    </nav>

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="border-t border-gray-200 bg-white">
        <div class="mx-auto text-center gap-2 px-4 py-6 text-sm text-gray-500 sm:flex-row">
            <p>&copy; {{ date('Y') }} SEUEvent Pro. CSE 471 Project.</p>
        </div>
    </footer>
</body>
</html>
