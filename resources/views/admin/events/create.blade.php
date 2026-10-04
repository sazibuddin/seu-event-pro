@extends('layouts.admin')

@section('title', 'Create Event')

@section('content')
    @include('admin.events._form', [
        'action' => route('admin.events.store'),
        'method' => 'POST',
        'submitLabel' => 'Create Event',
    ])
@endsection
