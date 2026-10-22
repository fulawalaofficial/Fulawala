@extends('admin.layout')

@section('title', 'Add Home Banner')

@section('content')
    @include('admin.home-banners._form', [
        'formTitle' => 'Add home banner',
        'formHelp' => 'Publish a new image to the Fulawala mobile home slider.',
        'formAction' => route('admin.home-banners.store'),
        'isEditing' => false,
    ])
@endsection
