@extends('admin.layout')

@section('title', 'Event Bookings')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-orange-600 via-amber-500 to-yellow-400 p-6 md:p-8 text-white shadow-xl">
        <div class="absolute -right-20 -top-20 h-60 w-60 rounded-full bg-white/20 blur-3xl"></div>
        <div class="absolute -bottom-24 left-10 h-64 w-64 rounded-full bg-red-500/20 blur-3xl"></div>

        <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-white/20 px-4 py-2 text-sm font-black backdrop-blur">
                    🎉 Fulawala Event Desk
                </div>

                <h1 class="mt-4 text-3xl md:text-4xl font-black tracking-tight">
                    Event Bookings
                </h1>

                <p class="mt-2 max-w-2xl text-white/90">
                    Manage wedding, birthday, pooja and decoration bookings with customer details, venue, budget, quotation and booking status.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3 text-center">
                <div class="rounded-2xl bg-white/20 px-5 py-4 backdrop-blur">
                    <p class="text-2xl font-black">{{ $stats['total'] ?? 0 }}</p>
                    <p class="text-xs font-bold uppercase text-white/80">Total Bookings</p>
                </div>

                <div class="rounded-2xl bg-white/20 px-5 py-4 backdrop-blur">
                    <p class="text-2xl font-black">₹{{ number_format((float) ($stats['total_budget'] ?? 0), 2) }}</p>
                    <p class="text-xs font-bold uppercase text-white/80">Total Budget</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="rounded-2xl border border-green-200 bg-green-50 px-5 py-4 font-semibold text-green-800 shadow-sm">
            ✅ {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 font-semibold text-red-800 shadow-sm">
            ⚠️ {{ session('error') }}
        </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
        <div class="rounded-3xl border border-orange-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-black text-gray-500">Total</p>
                    <h3 class="mt-2 text-3xl font-black text-gray-900">{{ $stats['total'] ?? 0 }}</h3>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-orange-100 text-2xl">📋</div>
            </div>
        </div>

        <div class="rounded-3xl border border-blue-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-black text-gray-500">Today</p>
                    <h3 class="mt-2 text-3xl font-black text-gray-900">{{ $stats['today'] ?? 0 }}</h3>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-blue-100 text-2xl">📅</div>
            </div>
        </div>

        <div class="rounded-3xl border border-yellow-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-black text-gray-500">Pending</p>
                    <h3 class="mt-2 text-3xl font-black text-gray-900">{{ $stats['pending'] ?? 0 }}</h3>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-yellow-100 text-2xl">⏳</div>
            </div>
        </div>

        <div class="rounded-3xl border border-green-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-black text-gray-500">Confirmed</p>
                    <h3 class="mt-2 text-3xl font-black text-gray-900">{{ $stats['confirmed'] ?? 0 }}</h3>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-green-100 text-2xl">✅</div>
            </div>
        </div>

        <div class="rounded-3xl border border-purple-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-black text-gray-500">Completed</p>
                    <h3 class="mt-2 text-3xl font-black text-gray-900">{{ $stats['completed'] ?? 0 }}</h3>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-purple-100 text-2xl">🏆</div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm">
        <form method="GET" action="{{ route('admin.event-bookings.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-6">
            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-black text-gray-700">Search</label>
                <input
                    type="text"
                    name="search"
                    value="{{ $filters['search'] ?? '' }}"
                    placeholder="ID, customer, venue, requirement..."
                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-black text-gray-700">Event Type</label>
                <select
                    name="event_type"
                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
                >
                    <option value="">All Types</option>
                    @foreach($eventTypeOptions as $type)
                        <option value="{{ $type }}" @selected(($filters['event_type'] ?? '') == $type)>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-black text-gray-700">Status</label>
                <select
                    name="status"
                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
                >
                    <option value="">All Status</option>
                    @foreach($statusOptions as $item)
                        <option value="{{ $item }}" @selected(($filters['status'] ?? '') == $item)>
                            {{ $item }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-black text-gray-700">From Date</label>
                <input
                    type="date"
                    name="date_from"
                    value="{{ $filters['date_from'] ?? '' }}"
                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-black text-gray-700">To Date</label>
                <input
                    type="date"
                    name="date_to"
                    value="{{ $filters['date_to'] ?? '' }}"
                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
                >
            </div>

            <div class="md:col-span-6 flex flex-col gap-3 sm:flex-row sm:justify-end">
                <a
                    href="{{ route('admin.event-bookings.index') }}"
                    class="rounded-2xl border border-gray-200 bg-white px-6 py-3 text-center text-sm font-black text-gray-700 transition hover:bg-gray-50"
                >
                    Reset
                </a>

                <button
                    type="submit"
                    class="rounded-2xl bg-gradient-to-r from-orange-600 to-amber-500 px-8 py-3 text-sm font-black text-white shadow-lg shadow-orange-200 transition hover:scale-[1.01]"
                >
                    Apply Filter
                </button>
            </div>
        </form>
    </div>

    {{-- Booking Cards --}}
    <div class="space-y-4">
        @forelse($bookings as $b)
            @php
                $statusText = $b->booking_status ?: 'Pending';

                $statusClass = match(strtolower((string) $statusText)) {
                    'confirmed', 'accepted' => 'bg-green-100 text-green-700 border-green-200',
                    'completed' => 'bg-purple-100 text-purple-700 border-purple-200',
                    'cancelled', 'rejected' => 'bg-red-100 text-red-700 border-red-200',
                    'quotation sent' => 'bg-blue-100 text-blue-700 border-blue-200',
                    'in progress' => 'bg-indigo-100 text-indigo-700 border-indigo-200',
                    default => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                };

                $eventDate = $b->event_date
                    ? \Illuminate\Support\Carbon::parse($b->event_date)->format('d M Y')
                    : '-';

                $eventTime = $b->event_time
                    ? \Illuminate\Support\Carbon::parse($b->event_time)->format('h:i A')
                    : '-';

                $quotation = $b->quotation;

                $referenceImage = null;

                if ($b->reference_image) {
                    if (\Illuminate\Support\Str::startsWith($b->reference_image, ['http://', 'https://'])) {
                        $referenceImage = $b->reference_image;
                    } else {
                        $referenceImage = asset('storage/' . $b->reference_image);
                    }
                }
            @endphp

            <details class="group overflow-hidden rounded-[2rem] border border-orange-100 bg-white shadow-sm transition hover:shadow-xl hover:shadow-orange-100">
                <summary class="cursor-pointer list-none p-5 md:p-6">
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-12 md:items-center">

                        <div class="md:col-span-2">
                            <p class="text-xs font-black uppercase tracking-wide text-gray-400">Booking</p>
                            <div class="mt-1 flex items-center gap-3">
                                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-gradient-to-br from-orange-500 to-amber-400 text-lg font-black text-white">
                                    #{{ $b->id }}
                                </div>
                                <div>
                                    <p class="font-black text-gray-900">Booking #{{ $b->id }}</p>
                                    <p class="text-xs font-semibold text-gray-500">
                                        {{ $b->created_at ? $b->created_at->format('d M Y, h:i A') : '-' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <p class="text-xs font-black uppercase tracking-wide text-gray-400">Customer</p>
                            <p class="mt-1 font-black text-gray-900">{{ $b->user->name ?? '-' }}</p>
                            <p class="text-xs text-gray-500">{{ $b->user->mobile ?? $b->user->email ?? '-' }}</p>
                        </div>

                        <div class="md:col-span-2">
                            <p class="text-xs font-black uppercase tracking-wide text-gray-400">Event Type</p>
                            <p class="mt-1 font-black text-gray-900">{{ $b->event_type ?: '-' }}</p>
                            <p class="text-xs text-gray-500">{{ $eventDate }} · {{ $eventTime }}</p>
                        </div>

                        <div class="md:col-span-2">
                            <p class="text-xs font-black uppercase tracking-wide text-gray-400">Venue</p>
                            <p class="mt-1 line-clamp-1 font-black text-gray-900">{{ $b->venue_address ?: '-' }}</p>
                            <p class="text-xs text-gray-500">Event Location</p>
                        </div>

                        <div class="md:col-span-1">
                            <p class="text-xs font-black uppercase tracking-wide text-gray-400">Budget</p>
                            <p class="mt-1 text-lg font-black text-gray-900">
                                ₹{{ number_format((float) $b->budget, 2) }}
                            </p>
                        </div>

                        <div class="md:col-span-2">
                            <div class="flex flex-wrap gap-2">
                                <span class="inline-flex rounded-full border px-3 py-1 text-xs font-black {{ $statusClass }}">
                                    {{ $statusText }}
                                </span>

                                @if($quotation)
                                    <span class="inline-flex rounded-full border border-green-200 bg-green-50 px-3 py-1 text-xs font-black text-green-700">
                                        Quotation Ready
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full border border-gray-200 bg-gray-50 px-3 py-1 text-xs font-black text-gray-600">
                                        No Quotation
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="md:col-span-1 text-right">
                            <span class="inline-flex rounded-2xl bg-gray-100 px-4 py-2 text-xs font-black text-gray-700 transition group-open:bg-orange-100 group-open:text-orange-700">
                                View
                            </span>
                        </div>
                    </div>
                </summary>

                <div class="border-t border-orange-100 bg-gradient-to-br from-orange-50/80 to-white p-5 md:p-6">
                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">

                        {{-- Main Details --}}
                        <div class="lg:col-span-2 space-y-5">
                            <div class="rounded-3xl border border-orange-100 bg-white p-5">
                                <h3 class="mb-5 text-lg font-black text-gray-900">Event Details</h3>

                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div class="rounded-2xl bg-orange-50 p-4">
                                        <p class="text-xs font-black uppercase text-orange-600">Event Type</p>
                                        <p class="mt-1 font-black text-gray-900">{{ $b->event_type ?: '-' }}</p>
                                    </div>

                                    <div class="rounded-2xl bg-orange-50 p-4">
                                        <p class="text-xs font-black uppercase text-orange-600">Event Date & Time</p>
                                        <p class="mt-1 font-black text-gray-900">{{ $eventDate }} · {{ $eventTime }}</p>
                                    </div>

                                    <div class="rounded-2xl bg-gray-50 p-4">
                                        <p class="text-xs font-black uppercase text-gray-500">Budget</p>
                                        <p class="mt-1 font-black text-gray-900">₹{{ number_format((float) $b->budget, 2) }}</p>
                                    </div>

                                    <div class="rounded-2xl bg-gray-50 p-4">
                                        <p class="text-xs font-black uppercase text-gray-500">Current Status</p>
                                        <p class="mt-1">
                                            <span class="inline-flex rounded-full border px-3 py-1 text-xs font-black {{ $statusClass }}">
                                                {{ $statusText }}
                                            </span>
                                        </p>
                                    </div>

                                    <div class="md:col-span-2 rounded-2xl bg-gray-50 p-4">
                                        <p class="text-xs font-black uppercase text-gray-500">Venue Address</p>
                                        <p class="mt-1 font-semibold leading-6 text-gray-900">{{ $b->venue_address ?: '-' }}</p>
                                    </div>

                                    <div class="md:col-span-2 rounded-2xl bg-gray-50 p-4">
                                        <p class="text-xs font-black uppercase text-gray-500">Requirement</p>
                                        <p class="mt-1 font-semibold leading-6 text-gray-900">{{ $b->requirement ?: 'No requirement added.' }}</p>
                                    </div>

                                    <div class="md:col-span-2 rounded-2xl bg-gray-50 p-4">
                                        <p class="text-xs font-black uppercase text-gray-500">Special Instructions</p>
                                        <p class="mt-1 font-semibold leading-6 text-gray-900">{{ $b->special_instructions ?: 'No special instructions.' }}</p>
                                    </div>
                                </div>
                            </div>

                            @if($referenceImage)
                                <div class="rounded-3xl border border-orange-100 bg-white p-5">
                                    <h3 class="mb-4 text-lg font-black text-gray-900">Reference Image</h3>
                                    <img
                                        src="{{ $referenceImage }}"
                                        alt="Reference Image"
                                        class="h-72 w-full rounded-3xl object-cover"
                                    >
                                </div>
                            @endif
                        </div>

                        {{-- Customer + Quotation + Status --}}
                        <div class="space-y-5">
                            <div class="rounded-3xl border border-orange-100 bg-white p-5">
                                <h3 class="mb-4 text-lg font-black text-gray-900">Customer Details</h3>

                                <div class="space-y-3 text-sm">
                                    <div>
                                        <p class="font-black text-gray-500">Name</p>
                                        <p class="font-semibold text-gray-900">{{ $b->user->name ?? '-' }}</p>
                                    </div>

                                    <div>
                                        <p class="font-black text-gray-500">Email</p>
                                        <p class="font-semibold text-gray-900">{{ $b->user->email ?? '-' }}</p>
                                    </div>

                                    <div>
                                        <p class="font-black text-gray-500">Mobile</p>
                                        <p class="font-semibold text-gray-900">{{ $b->user->mobile ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-3xl border border-orange-100 bg-white p-5">
                                <h3 class="mb-4 text-lg font-black text-gray-900">Quotation Status</h3>

                                @if($quotation)
                                    <div class="space-y-3">
                                        <div class="rounded-2xl border border-green-100 bg-green-50 p-4">
                                            <p class="text-xs font-black uppercase text-green-700">Quotation Available</p>
                                            <p class="mt-1 text-2xl font-black text-green-800">
                                                ₹{{ number_format((float) ($quotation->amount ?? 0), 2) }}
                                            </p>
                                            <p class="mt-1 text-sm font-semibold text-green-700">
                                                {{ $quotation->quotation_status ?? $quotation->status ?? 'Created' }}
                                            </p>
                                        </div>
                                    </div>
                                @else
                                    <div class="rounded-2xl border border-yellow-100 bg-yellow-50 p-4">
                                        <p class="font-black text-yellow-800">No quotation created</p>
                                        <p class="mt-1 text-sm font-semibold text-yellow-700">
                                            Create quotation from the Quotations page.
                                        </p>
                                    </div>
                                @endif
                            </div>

                            <div class="rounded-3xl border border-orange-100 bg-white p-5">
                                <h3 class="mb-4 text-lg font-black text-gray-900">Quick Update</h3>

                                <form method="POST" action="{{ route('admin.event-bookings.update-status', $b) }}" class="space-y-4">
                                    @csrf
                                    @method('PATCH')

                                    <div>
                                        <label class="mb-2 block text-sm font-black text-gray-700">Booking Status</label>
                                        <select
                                            name="booking_status"
                                            class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
                                        >
                                            @foreach($statusOptions as $item)
                                                <option value="{{ $item }}" @selected($b->booking_status == $item)>
                                                    {{ $item }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <button
                                        type="submit"
                                        class="w-full rounded-2xl bg-gray-900 px-5 py-3 text-sm font-black text-white shadow-lg transition hover:bg-orange-600"
                                    >
                                        Update Booking
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </details>
        @empty
            <div class="rounded-[2rem] border border-dashed border-orange-200 bg-white p-12 text-center">
                <div class="mx-auto grid h-20 w-20 place-items-center rounded-full bg-orange-100 text-4xl">
                    🎉
                </div>
                <h3 class="mt-5 text-2xl font-black text-gray-900">No event bookings found</h3>
                <p class="mt-2 text-gray-500">Try changing filters or wait for new customer bookings.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="rounded-3xl border border-orange-100 bg-white p-4 shadow-sm">
        {{ $bookings->links() }}
    </div>
</div>
@endsection