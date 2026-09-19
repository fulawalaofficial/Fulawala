@extends('admin.layout')

@section('title', 'Event Bookings')

@section('content')
@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Str;

    $statusMeta = [
        'request submitted' => [
            'label' => 'Request Submitted',
            'class' => 'border-orange-200 bg-orange-50 text-orange-700',
            'dot' => 'bg-orange-500',
            'icon' => '📨',
        ],
        'pending' => [
            'label' => 'Pending',
            'class' => 'border-amber-200 bg-amber-50 text-amber-700',
            'dot' => 'bg-amber-500',
            'icon' => '⏳',
        ],
        'quotation sent' => [
            'label' => 'Quotation Sent',
            'class' => 'border-blue-200 bg-blue-50 text-blue-700',
            'dot' => 'bg-blue-500',
            'icon' => '🧾',
        ],
        'accepted' => [
            'label' => 'Accepted',
            'class' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'dot' => 'bg-emerald-500',
            'icon' => '👍',
        ],
        'confirmed' => [
            'label' => 'Confirmed',
            'class' => 'border-green-200 bg-green-50 text-green-700',
            'dot' => 'bg-green-500',
            'icon' => '✅',
        ],
        'in progress' => [
            'label' => 'In Progress',
            'class' => 'border-indigo-200 bg-indigo-50 text-indigo-700',
            'dot' => 'bg-indigo-500',
            'icon' => '🛠️',
        ],
        'completed' => [
            'label' => 'Completed',
            'class' => 'border-purple-200 bg-purple-50 text-purple-700',
            'dot' => 'bg-purple-500',
            'icon' => '🏆',
        ],
        'cancelled' => [
            'label' => 'Cancelled',
            'class' => 'border-red-200 bg-red-50 text-red-700',
            'dot' => 'bg-red-500',
            'icon' => '✕',
        ],
    ];

    $workflow = [
        ['label' => 'Request', 'statuses' => ['request submitted', 'pending']],
        ['label' => 'Quotation', 'statuses' => ['quotation sent']],
        ['label' => 'Confirmed', 'statuses' => ['accepted', 'confirmed']],
        ['label' => 'Service', 'statuses' => ['in progress']],
        ['label' => 'Completed', 'statuses' => ['completed']],
    ];
@endphp

<div class="space-y-6">

    {{-- =========================================================
         HERO
    ========================================================== --}}
    <section class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-orange-700 via-orange-600 to-amber-400 p-6 text-white shadow-xl md:p-8">
        <div class="absolute -right-16 -top-20 h-64 w-64 rounded-full bg-white/15 blur-2xl"></div>
        <div class="absolute -bottom-28 left-1/3 h-72 w-72 rounded-full bg-red-500/20 blur-3xl"></div>

        <div class="relative z-10 flex flex-col gap-6 xl:flex-row xl:items-center xl:justify-between">
            <div class="max-w-3xl">
                <div class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/15 px-4 py-2 text-xs font-black uppercase tracking-[0.16em] backdrop-blur">
                    <span>🎉</span>
                    Fulawala Event Operations
                </div>

                <h1 class="mt-4 text-3xl font-black tracking-tight md:text-4xl">
                    Event Booking Management
                </h1>

                <p class="mt-3 max-w-2xl text-sm font-medium leading-6 text-white/90 md:text-base">
                    Review customer requests, event plans, venue details, budgets, quotations and service progress from one professional dashboard.
                </p>

                <div class="mt-5 flex flex-wrap gap-3">
                    <a
                        href="{{ route('admin.event-masters.index') }}"
                        class="inline-flex items-center gap-2 rounded-2xl bg-white px-5 py-3 text-sm font-black text-orange-700 shadow-lg transition hover:-translate-y-0.5"
                    >
                        <span>🎊</span>
                        Event Master
                    </a>

                    <a
                        href="{{ route('admin.quotations.index') }}"
                        class="inline-flex items-center gap-2 rounded-2xl border border-white/25 bg-white/15 px-5 py-3 text-sm font-black text-white backdrop-blur transition hover:bg-white/20"
                    >
                        <span>🧾</span>
                        Quotations
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:min-w-[360px]">
                <div class="rounded-3xl border border-white/15 bg-white/15 p-4 backdrop-blur">
                    <p class="text-xs font-black uppercase tracking-wide text-white/70">Total Bookings</p>
                    <p class="mt-2 text-3xl font-black">{{ number_format($stats['total'] ?? 0) }}</p>
                </div>

                <div class="rounded-3xl border border-white/15 bg-white/15 p-4 backdrop-blur">
                    <p class="text-xs font-black uppercase tracking-wide text-white/70">Booking Value</p>
                    <p class="mt-2 text-2xl font-black">
                        ₹{{ number_format((float) ($stats['total_budget'] ?? 0), 0) }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- =========================================================
         FLASH MESSAGES
    ========================================================== --}}
    @if(session('success'))
        <div class="flex items-start gap-3 rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-green-800 shadow-sm">
            <span class="mt-0.5">✅</span>
            <div>
                <p class="font-black">Updated successfully</p>
                <p class="mt-1 text-sm font-semibold">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-red-800 shadow-sm">
            <span class="mt-0.5">⚠️</span>
            <div>
                <p class="font-black">Something needs attention</p>
                <p class="mt-1 text-sm font-semibold">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-red-800 shadow-sm">
            <p class="font-black">Please check the following:</p>
            <ul class="mt-2 list-disc space-y-1 pl-5 text-sm font-semibold">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- =========================================================
         OPERATION STATS
    ========================================================== --}}
    <section class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
        @php
            $cards = [
                [
                    'label' => 'New Requests',
                    'value' => $stats['new_requests'] ?? 0,
                    'icon' => '📨',
                    'class' => 'border-orange-100 bg-orange-50',
                    'iconClass' => 'bg-orange-100',
                ],
                [
                    'label' => 'Today Events',
                    'value' => $stats['today'] ?? 0,
                    'icon' => '📅',
                    'class' => 'border-blue-100 bg-blue-50',
                    'iconClass' => 'bg-blue-100',
                ],
                [
                    'label' => 'Upcoming',
                    'value' => $stats['upcoming'] ?? 0,
                    'icon' => '🗓️',
                    'class' => 'border-cyan-100 bg-cyan-50',
                    'iconClass' => 'bg-cyan-100',
                ],
                [
                    'label' => 'Confirmed',
                    'value' => $stats['confirmed'] ?? 0,
                    'icon' => '✅',
                    'class' => 'border-green-100 bg-green-50',
                    'iconClass' => 'bg-green-100',
                ],
                [
                    'label' => 'Completed',
                    'value' => $stats['completed'] ?? 0,
                    'icon' => '🏆',
                    'class' => 'border-purple-100 bg-purple-50',
                    'iconClass' => 'bg-purple-100',
                ],
                [
                    'label' => 'All Bookings',
                    'value' => $stats['total'] ?? 0,
                    'icon' => '📋',
                    'class' => 'border-gray-100 bg-white',
                    'iconClass' => 'bg-gray-100',
                ],
            ];
        @endphp

        @foreach($cards as $card)
            <div class="rounded-3xl border p-4 shadow-sm {{ $card['class'] }}">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wide text-gray-500">
                            {{ $card['label'] }}
                        </p>
                        <p class="mt-2 text-3xl font-black text-gray-900">
                            {{ number_format($card['value']) }}
                        </p>
                    </div>

                    <div class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl text-xl {{ $card['iconClass'] }}">
                        {{ $card['icon'] }}
                    </div>
                </div>
            </div>
        @endforeach
    </section>

    {{-- =========================================================
         FILTERS
    ========================================================== --}}
    <section class="rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm md:p-6">
        <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-black text-gray-900">Find a Booking</h2>

                    @if(($activeFilterCount ?? 0) > 0)
                        <span class="rounded-full bg-orange-100 px-2.5 py-1 text-[10px] font-black uppercase text-orange-700">
                            {{ $activeFilterCount }} active
                        </span>
                    @endif
                </div>

                <p class="mt-1 text-sm font-medium text-gray-500">
                    Search by booking ID, customer, phone, event, plan, venue, PIN code or requirement.
                </p>
            </div>

            @if(($activeFilterCount ?? 0) > 0)
                <a
                    href="{{ route('admin.event-bookings.index') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-gray-50 px-4 py-2 text-xs font-black text-gray-700 transition hover:bg-gray-100"
                >
                    Clear all filters
                </a>
            @endif
        </div>

        <form method="GET" action="{{ route('admin.event-bookings.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-6">
            <div class="md:col-span-2 xl:col-span-2">
                <label class="mb-2 block text-xs font-black uppercase tracking-wide text-gray-500">
                    Search
                </label>

                <div class="relative">
                    <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">⌕</span>

                    <input
                        type="text"
                        name="search"
                        value="{{ $filters['search'] ?? '' }}"
                        placeholder="Booking #, customer, mobile, venue..."
                        class="w-full rounded-2xl border border-gray-200 bg-gray-50 py-3 pl-10 pr-4 text-sm font-semibold text-gray-900 outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
                    >
                </div>
            </div>

            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-wide text-gray-500">
                    Event
                </label>

                <select
                    name="event_type"
                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm font-semibold text-gray-900 outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
                >
                    <option value="">All Events</option>

                    @foreach($eventTypeOptions as $type)
                        <option value="{{ $type }}" @selected(($filters['event_type'] ?? '') === $type)>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-wide text-gray-500">
                    Status
                </label>

                <select
                    name="status"
                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm font-semibold text-gray-900 outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
                >
                    <option value="">All Statuses</option>

                    @foreach($statusOptions as $item)
                        <option value="{{ $item }}" @selected(($filters['status'] ?? '') === $item)>
                            {{ $item }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-wide text-gray-500">
                    From
                </label>

                <input
                    type="date"
                    name="date_from"
                    value="{{ $filters['date_from'] ?? '' }}"
                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm font-semibold text-gray-900 outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
                >
            </div>

            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-wide text-gray-500">
                    To
                </label>

                <input
                    type="date"
                    name="date_to"
                    value="{{ $filters['date_to'] ?? '' }}"
                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm font-semibold text-gray-900 outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
                >
            </div>

            <div class="md:col-span-2 xl:col-span-6 flex flex-col gap-3 border-t border-gray-100 pt-4 sm:flex-row sm:justify-end">
                <a
                    href="{{ route('admin.event-bookings.index') }}"
                    class="rounded-2xl border border-gray-200 bg-white px-6 py-3 text-center text-sm font-black text-gray-700 transition hover:bg-gray-50"
                >
                    Reset
                </a>

                <button
                    type="submit"
                    class="rounded-2xl bg-gray-900 px-7 py-3 text-sm font-black text-white shadow-lg transition hover:bg-orange-600"
                >
                    Apply Filters
                </button>
            </div>
        </form>
    </section>

    {{-- =========================================================
         RESULT HEADER
    ========================================================== --}}
    <section class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-black text-gray-900">Booking Requests</h2>
            <p class="mt-1 text-sm font-medium text-gray-500">
                Showing {{ $bookings->firstItem() ?? 0 }}–{{ $bookings->lastItem() ?? 0 }}
                of {{ $bookings->total() }} booking{{ $bookings->total() === 1 ? '' : 's' }}.
            </p>
        </div>

        <div class="rounded-2xl border border-orange-100 bg-orange-50 px-4 py-2 text-xs font-black text-orange-700">
            Latest requests shown first
        </div>
    </section>

    {{-- =========================================================
         BOOKING LIST
    ========================================================== --}}
    <section class="space-y-4">
        @forelse($bookings as $b)
            @php
                $statusText = $b->booking_status ?: 'Pending';
                $statusKey = strtolower(trim((string) $statusText));
                $meta = $statusMeta[$statusKey] ?? [
                    'label' => $statusText,
                    'class' => 'border-gray-200 bg-gray-50 text-gray-700',
                    'dot' => 'bg-gray-400',
                    'icon' => '•',
                ];

                $eventDateValue = $b->event_date ? Carbon::parse($b->event_date) : null;
                $eventDate = $eventDateValue ? $eventDateValue->format('d M Y') : '-';

                $eventTime = $b->event_time
                    ? Carbon::parse($b->event_time)->format('h:i A')
                    : '-';

                $isToday = $eventDateValue?->isToday() ?? false;
                $isPast = $eventDateValue?->isPast() && !$isToday;
                $daysAway = $eventDateValue
                    ? Carbon::today()->diffInDays($eventDateValue, false)
                    : null;

                $eventName = optional($b->eventMaster)->name
                    ?? ($b->event_type ?: 'Custom Event');

                $eventPlanName = optional($b->eventPlan)->name;

                $customerName = optional($b->user)->name ?: 'Customer';
                $customerMobile = optional($b->user)->mobile;
                $customerEmail = optional($b->user)->email;

                $addressName = optional($b->address)->name;
                $addressType = optional($b->address)->address_type;
                $addressMobile = optional($b->address)->number;

                $quotation = $b->quotation;

                $referenceImage = null;
                if ($b->reference_image) {
                    $referenceImage = Str::startsWith($b->reference_image, ['http://', 'https://'])
                        ? $b->reference_image
                        : asset('storage/' . ltrim($b->reference_image, '/'));
                }

                $coverImage = optional($b->eventMaster)->cover_image_url;

                $currentWorkflowIndex = collect($workflow)->search(
                    fn ($step) => in_array($statusKey, $step['statuses'], true)
                );

                $isCancelled = $statusKey === 'cancelled';
            @endphp

            <details class="group overflow-hidden rounded-[2rem] border border-gray-200 bg-white shadow-sm transition hover:border-orange-200 hover:shadow-xl hover:shadow-orange-100/50">
                <summary class="cursor-pointer list-none p-4 md:p-5">
                    <div class="grid grid-cols-1 gap-4 xl:grid-cols-[80px_minmax(0,1.4fr)_minmax(0,1fr)_minmax(0,1.1fr)_150px_150px] xl:items-center">

                        {{-- Event Thumbnail --}}
                        <div>
                            <div class="relative h-20 w-20 overflow-hidden rounded-2xl bg-orange-50">
                                @if($coverImage)
                                    <img
                                        src="{{ $coverImage }}"
                                        alt="{{ $eventName }}"
                                        class="h-full w-full object-cover"
                                    >
                                @else
                                    <div class="grid h-full w-full place-items-center text-3xl">🌸</div>
                                @endif

                                <span class="absolute bottom-1.5 left-1.5 rounded-lg bg-gray-900/80 px-2 py-1 text-[9px] font-black text-white backdrop-blur">
                                    #{{ $b->id }}
                                </span>
                            </div>
                        </div>

                        {{-- Event + Customer --}}
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="truncate text-lg font-black text-gray-900">
                                    {{ $eventName }}
                                </h3>

                                @if($eventPlanName)
                                    <span class="rounded-full bg-purple-50 px-2.5 py-1 text-[10px] font-black text-purple-700">
                                        {{ $eventPlanName }}
                                    </span>
                                @endif
                            </div>

                            <p class="mt-1 truncate text-sm font-bold text-gray-700">
                                {{ $customerName }}
                            </p>

                            <p class="mt-1 truncate text-xs font-semibold text-gray-500">
                                {{ $customerMobile ?: ($customerEmail ?: 'No customer contact') }}
                            </p>
                        </div>

                        {{-- Date --}}
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-wide text-gray-400">
                                Event Schedule
                            </p>

                            <p class="mt-1 font-black text-gray-900">
                                {{ $eventDate }}
                            </p>

                            <div class="mt-1 flex flex-wrap items-center gap-2 text-xs font-semibold text-gray-500">
                                <span>{{ $eventTime }}</span>

                                @if($isToday)
                                    <span class="rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-black text-blue-700">
                                        Today
                                    </span>
                                @elseif($daysAway !== null && $daysAway > 0)
                                    <span class="rounded-full bg-cyan-50 px-2 py-0.5 text-[10px] font-black text-cyan-700">
                                        {{ $daysAway }} day{{ $daysAway === 1 ? '' : 's' }}
                                    </span>
                                @elseif($isPast)
                                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-black text-gray-600">
                                        Past
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Venue --}}
                        <div class="min-w-0">
                            <p class="text-[10px] font-black uppercase tracking-wide text-gray-400">
                                Venue
                            </p>

                            <p class="mt-1 line-clamp-2 text-sm font-bold leading-5 text-gray-800">
                                {{ $b->venue_address ?: 'Address not available' }}
                            </p>
                        </div>

                        {{-- Budget --}}
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-wide text-gray-400">
                                Budget
                            </p>

                            <p class="mt-1 text-lg font-black text-gray-900">
                                @if($b->budget !== null)
                                    ₹{{ number_format((float) $b->budget, 0) }}
                                @else
                                    <span class="text-sm text-gray-500">Not set</span>
                                @endif
                            </p>

                            @if($quotation)
                                <p class="mt-1 text-[10px] font-black text-green-700">
                                    Quotation ready
                                </p>
                            @else
                                <p class="mt-1 text-[10px] font-black text-amber-700">
                                    Quotation pending
                                </p>
                            @endif
                        </div>

                        {{-- Status / View --}}
                        <div class="flex items-center justify-between gap-3 xl:flex-col xl:items-stretch">
                            <span class="inline-flex items-center justify-center gap-2 rounded-full border px-3 py-2 text-xs font-black {{ $meta['class'] }}">
                                <span class="h-2 w-2 rounded-full {{ $meta['dot'] }}"></span>
                                {{ $meta['label'] }}
                            </span>

                            <span class="inline-flex items-center justify-center rounded-xl bg-gray-100 px-3 py-2 text-xs font-black text-gray-700 transition group-open:bg-orange-100 group-open:text-orange-700">
                                <span class="group-open:hidden">View Details</span>
                                <span class="hidden group-open:inline">Hide Details</span>
                            </span>
                        </div>
                    </div>
                </summary>

                {{-- =====================================================
                     EXPANDED BOOKING
                ====================================================== --}}
                <div class="border-t border-gray-100 bg-gray-50/70 p-4 md:p-6">

                    {{-- Workflow --}}
                    <div class="mb-5 rounded-3xl border border-gray-200 bg-white p-4">
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <div>
                                <h4 class="font-black text-gray-900">Booking Progress</h4>
                                <p class="mt-1 text-xs font-semibold text-gray-500">
                                    Current stage: {{ $statusText }}
                                </p>
                            </div>

                            @if($isCancelled)
                                <span class="rounded-full border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-black text-red-700">
                                    Booking cancelled
                                </span>
                            @endif
                        </div>

                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-5">
                            @foreach($workflow as $index => $step)
                                @php
                                    $stepDone = !$isCancelled
                                        && $currentWorkflowIndex !== false
                                        && $index <= $currentWorkflowIndex;

                                    $stepCurrent = !$isCancelled
                                        && $currentWorkflowIndex !== false
                                        && $index === $currentWorkflowIndex;
                                @endphp

                                <div class="rounded-2xl border p-3 text-center
                                    {{ $stepDone
                                        ? 'border-green-200 bg-green-50'
                                        : 'border-gray-200 bg-gray-50'
                                    }}">
                                    <div class="mx-auto grid h-7 w-7 place-items-center rounded-full text-xs font-black
                                        {{ $stepDone
                                            ? 'bg-green-600 text-white'
                                            : 'bg-gray-200 text-gray-500'
                                        }}">
                                        {{ $stepDone ? '✓' : $index + 1 }}
                                    </div>

                                    <p class="mt-2 text-[10px] font-black uppercase tracking-wide
                                        {{ $stepCurrent ? 'text-green-700' : 'text-gray-600' }}">
                                        {{ $step['label'] }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">

                        {{-- =================================================
                             LEFT / MAIN DETAILS
                        ================================================== --}}
                        <div class="space-y-5 xl:col-span-2">

                            {{-- Event --}}
                            <div class="rounded-3xl border border-gray-200 bg-white p-5">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <p class="text-xs font-black uppercase tracking-wide text-orange-600">
                                            Event Information
                                        </p>

                                        <h3 class="mt-1 text-xl font-black text-gray-900">
                                            {{ $eventName }}
                                        </h3>

                                        @if($eventPlanName)
                                            <p class="mt-1 text-sm font-bold text-purple-700">
                                                Selected Plan: {{ $eventPlanName }}
                                            </p>
                                        @endif
                                    </div>

                                    @if($b->eventMaster)
                                        <a
                                            href="{{ route('admin.event-masters.edit', $b->eventMaster) }}"
                                            class="inline-flex items-center justify-center rounded-xl border border-orange-200 bg-orange-50 px-4 py-2 text-xs font-black text-orange-700 transition hover:bg-orange-100"
                                        >
                                            Open Master Event
                                        </a>
                                    @endif
                                </div>

                                <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <div class="rounded-2xl bg-orange-50 p-4">
                                        <p class="text-[10px] font-black uppercase tracking-wide text-orange-600">
                                            Event Date
                                        </p>
                                        <p class="mt-1 font-black text-gray-900">
                                            {{ $eventDate }}
                                        </p>
                                    </div>

                                    <div class="rounded-2xl bg-orange-50 p-4">
                                        <p class="text-[10px] font-black uppercase tracking-wide text-orange-600">
                                            Event Time
                                        </p>
                                        <p class="mt-1 font-black text-gray-900">
                                            {{ $eventTime }}
                                        </p>
                                    </div>

                                    <div class="rounded-2xl bg-gray-50 p-4">
                                        <p class="text-[10px] font-black uppercase tracking-wide text-gray-500">
                                            Customer Budget
                                        </p>
                                        <p class="mt-1 font-black text-gray-900">
                                            {{ $b->budget !== null ? '₹'.number_format((float) $b->budget, 2) : 'Not provided' }}
                                        </p>
                                    </div>

                                    <div class="rounded-2xl bg-gray-50 p-4">
                                        <p class="text-[10px] font-black uppercase tracking-wide text-gray-500">
                                            Current Status
                                        </p>

                                        <span class="mt-1 inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-black {{ $meta['class'] }}">
                                            <span class="h-2 w-2 rounded-full {{ $meta['dot'] }}"></span>
                                            {{ $statusText }}
                                        </span>
                                    </div>

                                    <div class="sm:col-span-2 rounded-2xl bg-gray-50 p-4">
                                        <p class="text-[10px] font-black uppercase tracking-wide text-gray-500">
                                            Venue
                                        </p>

                                        <p class="mt-1 whitespace-pre-line text-sm font-semibold leading-6 text-gray-900">
                                            {{ $b->venue_address ?: 'No venue address available.' }}
                                        </p>

                                        @if($addressType || $addressName || $addressMobile)
                                            <div class="mt-3 flex flex-wrap gap-2">
                                                @if($addressType)
                                                    <span class="rounded-full bg-white px-3 py-1 text-[10px] font-black text-gray-600">
                                                        {{ $addressType }}
                                                    </span>
                                                @endif

                                                @if($addressName)
                                                    <span class="rounded-full bg-white px-3 py-1 text-[10px] font-black text-gray-600">
                                                        Contact: {{ $addressName }}
                                                    </span>
                                                @endif

                                                @if($addressMobile)
                                                    <span class="rounded-full bg-white px-3 py-1 text-[10px] font-black text-gray-600">
                                                        {{ $addressMobile }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    <div class="sm:col-span-2 rounded-2xl border border-blue-100 bg-blue-50 p-4">
                                        <p class="text-[10px] font-black uppercase tracking-wide text-blue-600">
                                            Customer Requirement
                                        </p>

                                        <p class="mt-2 whitespace-pre-line text-sm font-semibold leading-6 text-gray-900">
                                            {{ $b->requirement ?: 'No requirement added.' }}
                                        </p>
                                    </div>

                                    <div class="sm:col-span-2 rounded-2xl border border-purple-100 bg-purple-50 p-4">
                                        <p class="text-[10px] font-black uppercase tracking-wide text-purple-600">
                                            Special Instructions
                                        </p>

                                        <p class="mt-2 whitespace-pre-line text-sm font-semibold leading-6 text-gray-900">
                                            {{ $b->special_instructions ?: 'No special instructions.' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- Reference Image --}}
                            @if($referenceImage)
                                <div class="rounded-3xl border border-gray-200 bg-white p-5">
                                    <div class="mb-4">
                                        <p class="text-xs font-black uppercase tracking-wide text-gray-500">
                                            Customer Reference
                                        </p>
                                        <h3 class="mt-1 text-lg font-black text-gray-900">
                                            Reference Image
                                        </h3>
                                    </div>

                                    <a href="{{ $referenceImage }}" target="_blank" rel="noopener">
                                        <img
                                            src="{{ $referenceImage }}"
                                            alt="Booking reference image"
                                            class="max-h-[420px] w-full rounded-3xl object-cover"
                                        >
                                    </a>
                                </div>
                            @endif
                        </div>

                        {{-- =================================================
                             RIGHT SIDEBAR
                        ================================================== --}}
                        <aside class="space-y-5">

                            {{-- Customer --}}
                            <div class="rounded-3xl border border-gray-200 bg-white p-5">
                                <div class="flex items-center gap-3">
                                    <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-orange-100 text-lg font-black text-orange-700">
                                        {{ strtoupper(substr($customerName, 0, 1)) }}
                                    </div>

                                    <div class="min-w-0">
                                        <p class="text-xs font-black uppercase tracking-wide text-gray-400">
                                            Customer
                                        </p>

                                        <p class="truncate font-black text-gray-900">
                                            {{ $customerName }}
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-4 space-y-3">
                                    <div class="rounded-2xl bg-gray-50 p-3">
                                        <p class="text-[10px] font-black uppercase text-gray-400">Mobile</p>
                                        <p class="mt-1 break-all text-sm font-bold text-gray-800">
                                            {{ $customerMobile ?: '-' }}
                                        </p>
                                    </div>

                                    <div class="rounded-2xl bg-gray-50 p-3">
                                        <p class="text-[10px] font-black uppercase text-gray-400">Email</p>
                                        <p class="mt-1 break-all text-sm font-bold text-gray-800">
                                            {{ $customerEmail ?: '-' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- Quotation --}}
                            <div class="rounded-3xl border border-gray-200 bg-white p-5">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-xs font-black uppercase tracking-wide text-gray-400">
                                            Quotation
                                        </p>
                                        <h3 class="mt-1 font-black text-gray-900">
                                            {{ $quotation ? 'Quotation Available' : 'Not Created Yet' }}
                                        </h3>
                                    </div>

                                    <div class="grid h-10 w-10 place-items-center rounded-2xl {{ $quotation ? 'bg-green-100' : 'bg-amber-100' }}">
                                        {{ $quotation ? '✓' : '!' }}
                                    </div>
                                </div>

                                @if($quotation)
                                    <div class="mt-4 rounded-2xl border border-green-100 bg-green-50 p-4">
                                        <p class="text-[10px] font-black uppercase tracking-wide text-green-700">
                                            Quotation Amount
                                        </p>

                                        <p class="mt-1 text-2xl font-black text-green-900">
                                            ₹{{ number_format((float) ($quotation->amount ?? 0), 2) }}
                                        </p>

                                        @if(isset($quotation->advance_amount))
                                            <p class="mt-2 text-xs font-bold text-green-700">
                                                Advance: ₹{{ number_format((float) $quotation->advance_amount, 2) }}
                                            </p>
                                        @endif

                                        <p class="mt-2 text-xs font-black text-green-700">
                                            {{ $quotation->quotation_status ?? $quotation->status ?? 'Created' }}
                                        </p>
                                    </div>
                                @else
                                    <div class="mt-4 rounded-2xl border border-amber-100 bg-amber-50 p-4">
                                        <p class="text-sm font-bold leading-5 text-amber-800">
                                            Review the customer requirement, then create and send a quotation.
                                        </p>
                                    </div>
                                @endif

                                <a
                                    href="{{ route('admin.quotations.index') }}"
                                    class="mt-4 inline-flex w-full items-center justify-center rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm font-black text-gray-800 transition hover:bg-gray-100"
                                >
                                    Open Quotations
                                </a>
                            </div>

                            {{-- Quick Status Update --}}
                            <div class="rounded-3xl border border-orange-200 bg-gradient-to-br from-orange-50 to-white p-5">
                                <div class="mb-4">
                                    <p class="text-xs font-black uppercase tracking-wide text-orange-600">
                                        Quick Action
                                    </p>
                                    <h3 class="mt-1 text-lg font-black text-gray-900">
                                        Update Booking Status
                                    </h3>
                                    <p class="mt-1 text-xs font-semibold leading-5 text-gray-500">
                                        Keep the customer workflow current after each operational step.
                                    </p>
                                </div>

                                <form
                                    method="POST"
                                    action="{{ route('admin.event-bookings.update-status', $b) }}"
                                    class="space-y-3"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <select
                                        name="booking_status"
                                        class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-bold text-gray-900 outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                                    >
                                        @foreach($statusOptions as $item)
                                            <option value="{{ $item }}" @selected($statusText === $item)>
                                                {{ $item }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <button
                                        type="submit"
                                        class="w-full rounded-2xl bg-gray-900 px-5 py-3 text-sm font-black text-white shadow-lg transition hover:bg-orange-600"
                                    >
                                        Save Status
                                    </button>
                                </form>
                            </div>
                        </aside>
                    </div>
                </div>
            </details>

        @empty
            <div class="rounded-[2rem] border border-dashed border-orange-200 bg-white p-10 text-center shadow-sm md:p-14">
                <div class="mx-auto grid h-20 w-20 place-items-center rounded-full bg-orange-100 text-4xl">
                    🎉
                </div>

                <h3 class="mt-5 text-2xl font-black text-gray-900">
                    No event bookings found
                </h3>

                <p class="mx-auto mt-2 max-w-md text-sm font-medium leading-6 text-gray-500">
                    There are no bookings matching the selected filters. Clear the filters or wait for new customer booking requests.
                </p>

                @if(($activeFilterCount ?? 0) > 0)
                    <a
                        href="{{ route('admin.event-bookings.index') }}"
                        class="mt-5 inline-flex rounded-2xl bg-orange-600 px-5 py-3 text-sm font-black text-white"
                    >
                        Clear Filters
                    </a>
                @endif
            </div>
        @endforelse
    </section>

    {{-- =========================================================
         PAGINATION
    ========================================================== --}}
    @if($bookings->hasPages())
        <section class="rounded-3xl border border-gray-200 bg-white p-4 shadow-sm">
            {{ $bookings->links() }}
        </section>
    @endif
</div>
@endsection
