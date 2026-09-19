@extends('admin.layout')

@section('title', 'Event Master')

@section('content')
<div class="space-y-6">
    <div class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-fuchsia-700 via-orange-600 to-amber-400 p-6 text-white shadow-xl md:p-8">
        <div class="absolute -right-16 -top-20 h-64 w-64 rounded-full bg-white/20 blur-3xl"></div>
        <div class="relative z-10 flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <div class="inline-flex rounded-full bg-white/20 px-4 py-2 text-sm font-black backdrop-blur">🎊 Fulawala Event Master</div>
                <h1 class="mt-4 text-3xl font-black md:text-4xl">Master Events</h1>
                <p class="mt-2 max-w-2xl text-white/90">Create reusable wedding, birthday, pooja and decoration events with photos, videos, plans and descriptions.</p>
            </div>
            <a href="{{ route('admin.event-masters.create') }}" class="rounded-2xl bg-white px-6 py-3 text-center text-sm font-black text-orange-700 shadow-lg">+ Add Master Event</a>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-2xl border border-green-200 bg-green-50 px-5 py-4 font-semibold text-green-800">✅ {{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
        @foreach([
            ['label' => 'Total Events', 'value' => $stats['total'], 'icon' => '🎉'],
            ['label' => 'Active', 'value' => $stats['active'], 'icon' => '✅'],
            ['label' => 'Inactive', 'value' => $stats['inactive'], 'icon' => '⏸️'],
            ['label' => 'Plans', 'value' => $stats['plans'], 'icon' => '💐'],
        ] as $card)
            <div class="rounded-3xl border border-orange-100 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-black text-gray-500">{{ $card['label'] }}</p>
                        <p class="mt-2 text-3xl font-black text-gray-900">{{ $card['value'] }}</p>
                    </div>
                    <div class="grid h-12 w-12 place-items-center rounded-2xl bg-orange-50 text-2xl">{{ $card['icon'] }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm">
        <form method="GET" action="{{ route('admin.event-masters.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-black text-gray-700">Search</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Event name or description..." class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
            </div>
            <div>
                <label class="mb-2 block text-sm font-black text-gray-700">Status</label>
                <select name="status" class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                    <option value="">All</option>
                    <option value="active" @selected($status === 'active')>Active</option>
                    <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button class="flex-1 rounded-2xl bg-gray-900 px-5 py-3 font-black text-white">Filter</button>
                <a href="{{ route('admin.event-masters.index') }}" class="rounded-2xl border border-gray-200 px-5 py-3 font-black text-gray-700">Reset</a>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
        @forelse($events as $event)
            <div class="overflow-hidden rounded-[2rem] border border-orange-100 bg-white shadow-sm">
                <div class="grid gap-0 md:grid-cols-[190px_1fr]">
                    <div class="min-h-52 bg-orange-50">
                        @if($event->cover_image_url)
                            <img src="{{ $event->cover_image_url }}" alt="{{ $event->name }}" class="h-full min-h-52 w-full object-cover">
                        @else
                            <div class="grid h-full min-h-52 place-items-center text-6xl">🌸</div>
                        @endif
                    </div>
                    <div class="p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-black uppercase tracking-wide text-orange-600">Master Event #{{ $event->id }}</p>
                                <h2 class="mt-1 text-xl font-black text-gray-900">{{ $event->name }}</h2>
                            </div>
                            <span class="rounded-full px-3 py-1 text-xs font-black {{ $event->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">{{ ucfirst($event->status) }}</span>
                        </div>

                        <p class="mt-3 line-clamp-2 text-sm font-medium leading-6 text-gray-600">{{ $event->short_description ?: $event->description ?: 'No description added.' }}</p>

                        <div class="mt-4 grid grid-cols-4 gap-2 text-center">
                            <div class="rounded-2xl bg-orange-50 px-2 py-3"><p class="text-lg font-black">{{ $event->photos_count }}</p><p class="text-[10px] font-black uppercase text-gray-500">Photos</p></div>
                            <div class="rounded-2xl bg-blue-50 px-2 py-3"><p class="text-lg font-black">{{ $event->videos_count }}</p><p class="text-[10px] font-black uppercase text-gray-500">Videos</p></div>
                            <div class="rounded-2xl bg-purple-50 px-2 py-3"><p class="text-lg font-black">{{ $event->plans_count }}</p><p class="text-[10px] font-black uppercase text-gray-500">Plans</p></div>
                            <div class="rounded-2xl bg-green-50 px-2 py-3"><p class="text-sm font-black">{{ $event->starting_price !== null ? '₹'.number_format((float)$event->starting_price, 0) : '-' }}</p><p class="text-[10px] font-black uppercase text-gray-500">Starts</p></div>
                        </div>

                        <div class="mt-5 flex flex-wrap gap-2">
                            <a href="{{ route('admin.event-masters.edit', $event) }}" class="rounded-2xl bg-orange-600 px-5 py-2.5 text-sm font-black text-white">Edit Event</a>
                            <form method="POST" action="{{ route('admin.event-masters.destroy', $event) }}" onsubmit="return confirm('Delete this master event and all its media/plans?')">
                                @csrf
                                @method('DELETE')
                                <button class="rounded-2xl border border-red-200 bg-red-50 px-5 py-2.5 text-sm font-black text-red-700">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="xl:col-span-2 rounded-[2rem] border border-dashed border-orange-200 bg-white p-12 text-center">
                <div class="text-5xl">🎊</div>
                <h3 class="mt-4 text-2xl font-black text-gray-900">No master events found</h3>
                <p class="mt-2 text-gray-500">Create your first event package with photos, videos, plans and description.</p>
            </div>
        @endforelse
    </div>

    <div class="rounded-3xl border border-orange-100 bg-white p-4">{{ $events->links() }}</div>
</div>
@endsection
