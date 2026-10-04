<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login | {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-gray-900 px-4 antialiased">
    <div class="w-full max-w-md">
        <div class="mb-6 text-center">
            <p class="text-2xl font-bold text-white">SEUEvent<span class="text-indigo-400">Pro</span></p>
            <p class="mt-1 text-sm text-gray-400">Administrator access</p>
        </div>

        <div class="rounded-xl bg-white p-8 shadow-xl">
            <h1 class="text-xl font-bold text-gray-900">Admin Login</h1>

            <form method="POST" action="{{ route('admin.login') }}" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-none">
                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input id="password" type="password" name="password" required
                           class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-none">
                    @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="remember" class="rounded border-gray-300"> Remember me
                </label>

                <button type="submit" class="w-full rounded-lg bg-gray-900 py-2.5 font-semibold text-white hover:bg-gray-800">Log in as Admin</button>
            </form>
        </div>

        <p class="mt-6 text-center text-sm text-gray-400">
            <a href="{{ route('home') }}" class="hover:text-white">&larr; Back to site</a>
        </p>
    </div>
</body>
</html>
