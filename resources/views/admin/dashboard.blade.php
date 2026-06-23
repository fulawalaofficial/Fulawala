@extends('admin.layout')
@section('title','Dashboard')
@section('content')
<h1 class="text-3xl font-black mb-6">Dashboard</h1>
<div class="grid md:grid-cols-3 gap-4">
    @foreach($cards as $label => $value)
        <div class="bg-white rounded-2xl border border-orange-200 p-5">
            <p class="text-gray-500">{{ $label }}</p>
            <p class="text-3xl font-black text-orange-700 mt-2">{{ $value }}</p>
        </div>
    @endforeach
</div>
@endsection
