@extends('admin.layout')

@section('title', 'Edit Home Banner')

@section('content')
    @include('admin.home-banners._form', [
        'formTitle' => 'Edit home banner',
        'formHelp' => 'Changes are reflected in the app when the home screen reloads.',
        'formAction' => route('admin.home-banners.update', $banner),
        'isEditing' => true,
    ])
@endsection
