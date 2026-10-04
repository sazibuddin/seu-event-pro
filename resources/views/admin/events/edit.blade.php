@extends('layouts.admin')

@section('title', 'Edit Event')

@section('content')
    @include('admin.events._form', [
        'action' => route('admin.events.update', $event),
        'method' => 'PUT',
        'submitLabel' => 'Save Changes',
    ])
@endsection
