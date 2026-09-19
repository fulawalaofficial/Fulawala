@extends('admin.layout')

@section('title', 'Edit Master Event')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 rounded-[2rem] bg-gradient-to-r from-orange-600 to-amber-400 p-6 text-white shadow-xl md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-sm font-black uppercase text-white/80">Event Master</p>
            <h1 class="mt-1 text-3xl font-black">Edit: {{ $eventMaster->name }}</h1>
        </div>
        <a href="{{ route('admin.event-masters.index') }}" class="rounded-2xl bg-white/20 px-5 py-3 text-center font-black backdrop-blur">← Back to Events</a>
    </div>

    @if(session('success'))
        <div class="rounded-2xl border border-green-200 bg-green-50 px-5 py-4 font-semibold text-green-800">✅ {{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.event-masters.update', $eventMaster) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')
        @include('admin.event-masters._form')
        <div class="flex justify-end">
            <button class="rounded-2xl bg-gradient-to-r from-orange-600 to-amber-500 px-8 py-3 font-black text-white shadow-lg">Save Changes</button>
        </div>
    </form>
</div>
@endsection
