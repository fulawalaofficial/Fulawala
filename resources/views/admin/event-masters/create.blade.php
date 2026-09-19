@extends('admin.layout')

@section('title', 'Create Master Event')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 rounded-[2rem] bg-gradient-to-r from-orange-600 to-amber-400 p-6 text-white shadow-xl md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-sm font-black uppercase text-white/80">Event Master</p>
            <h1 class="mt-1 text-3xl font-black">Create Master Event</h1>
        </div>
        <a href="{{ route('admin.event-masters.index') }}" class="rounded-2xl bg-white/20 px-5 py-3 text-center font-black backdrop-blur">← Back to Events</a>
    </div>

    <form method="POST" action="{{ route('admin.event-masters.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @include('admin.event-masters._form')
        <div class="flex justify-end">
            <button class="rounded-2xl bg-gradient-to-r from-orange-600 to-amber-500 px-8 py-3 font-black text-white shadow-lg">Create Master Event</button>
        </div>
    </form>
</div>
@endsection
