@extends('layouts.app')

@section('title', 'Sign Up')

@section('content')
<div class="mx-auto max-w-md px-4 py-16">
    <div class="rounded-xl border border-gray-200 bg-white p-8 shadow-sm">
        <h1 class="text-2xl font-bold">Create your account</h1>
        <p class="mt-1 text-sm text-gray-500">Join SEUEvent Pro and grab your seat.</p>

        <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
            @csrf

            @foreach ([
                ['name', 'Full name', 'text', true],
                ['email', 'Email', 'email', true],
                ['phone', 'Phone (optional)', 'text', false],
            ] as [$field, $label, $type, $required])
                <div>
                    <label for="{{ $field }}" class="block text-sm font-medium text-gray-700">{{ $label }}</label>
                    <input id="{{ $field }}" type="{{ $type }}" name="{{ $field }}" value="{{ old($field) }}" @required($required)
                           class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-none">
                    @error($field) <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            @endforeach

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input id="password" type="password" name="password" required
                       class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-none">
                @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required
                       class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-none">
            </div>

            <button type="submit" class="w-full rounded-lg bg-indigo-600 py-2.5 font-semibold text-white hover:bg-indigo-700">Create account</button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-500">
            Already registered? <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:underline">Log in</a>
        </p>
    </div>
</div>
@endsection
