@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="mx-auto max-w-md px-4 py-16">
    <div class="rounded-xl border border-gray-200 bg-white p-8 shadow-sm">
        <h1 class="text-2xl font-bold">Welcome back</h1>
        <p class="mt-1 text-sm text-gray-500">Log in to register for events.</p>

        <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
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

            <button type="submit" class="w-full rounded-lg bg-indigo-600 py-2.5 font-semibold text-white hover:bg-indigo-700">Log in</button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-500">
            No account? <a href="{{ route('register') }}" class="font-medium text-indigo-600 hover:underline">Sign up</a>
        </p>
    </div>
</div>
@endsection
