@extends('admin.layout')

@section('title','Dashboard')

@section('content')

<div class="space-y-8">

    <!-- Premium Header -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-orange-600 via-orange-500 to-amber-400 p-8 shadow-xl">
        <div class="absolute inset-0 opacity-20">
            <div class="absolute -top-16 -right-16 h-56 w-56 rounded-full bg-white"></div>
            <div class="absolute bottom-0 left-1/3 h-32 w-32 rounded-full bg-white"></div>
            <div class="absolute top-10 left-10 h-20 w-20 rounded-full border border-white"></div>
        </div>

        <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <div class="mb-3 inline-flex items-center gap-2 rounded-full bg-white/20 px-4 py-2 text-sm font-bold text-white backdrop-blur">
                    <span>🌸</span>
                    <span>Fulawala Admin Dashboard</span>
                </div>

                <h1 class="text-3xl font-black text-white sm:text-4xl">
                    Welcome Back, Admin
                </h1>

                <p class="mt-3 max-w-2xl text-orange-50">
                    Manage orders, subscriptions, customers, deliveries, payments and event bookings from one clean premium dashboard.
                </p>
            </div>

            <div class="rounded-2xl bg-white/20 px-6 py-4 text-white backdrop-blur">
                <p class="text-sm text-orange-50">Today</p>
                <p class="text-xl font-black">{{ now()->format('d M Y') }}</p>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
        @foreach($cards as $card)
            @php
                $styles = [
                    'orange' => [
                        'box' => 'from-orange-50 to-white border-orange-100',
                        'icon' => 'bg-orange-100 text-orange-700',
                        'text' => 'text-orange-700',
                    ],
                    'emerald' => [
                        'box' => 'from-emerald-50 to-white border-emerald-100',
                        'icon' => 'bg-emerald-100 text-emerald-700',
                        'text' => 'text-emerald-700',
                    ],
                    'purple' => [
                        'box' => 'from-purple-50 to-white border-purple-100',
                        'icon' => 'bg-purple-100 text-purple-700',
                        'text' => 'text-purple-700',
                    ],
                    'blue' => [
                        'box' => 'from-blue-50 to-white border-blue-100',
                        'icon' => 'bg-blue-100 text-blue-700',
                        'text' => 'text-blue-700',
                    ],
                    'amber' => [
                        'box' => 'from-amber-50 to-white border-amber-100',
                        'icon' => 'bg-amber-100 text-amber-700',
                        'text' => 'text-amber-700',
                    ],
                    'pink' => [
                        'box' => 'from-pink-50 to-white border-pink-100',
                        'icon' => 'bg-pink-100 text-pink-700',
                        'text' => 'text-pink-700',
                    ],
                ];

                $style = $styles[$card['color']] ?? $styles['orange'];
            @endphp

            <div class="group relative overflow-hidden rounded-3xl border bg-gradient-to-br {{ $style['box'] }} p-6 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl">
                <div class="absolute -right-8 -top-8 h-28 w-28 rounded-full bg-white/70"></div>

                <div class="relative z-10 flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-bold text-gray-500">
                            {{ $card['label'] }}
                        </p>

                        <p class="mt-3 text-3xl font-black {{ $style['text'] }}">
                            {{ $card['value'] }}
                        </p>

                        <p class="mt-2 text-sm text-gray-500">
                            {{ $card['description'] }}
                        </p>
                    </div>

                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl {{ $style['icon'] }} text-2xl shadow-sm">
                        {{ $card['icon'] }}
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Main Content Grid -->
    <div class="grid gap-6 xl:grid-cols-3">

        <!-- Business Overview -->
        <div class="xl:col-span-2 rounded-3xl border border-orange-100 bg-white p-6 shadow-sm">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-black text-gray-900">
                        Business Overview
                    </h2>
                    <p class="text-sm text-gray-500">
                        Quick performance summary of your platform.
                    </p>
                </div>

                <div class="rounded-full bg-orange-50 px-4 py-2 text-sm font-bold text-orange-700">
                    Live Summary
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-2xl border border-gray-100 bg-gray-50 p-5">
                    <p class="text-sm font-bold text-gray-500">Total Orders</p>
                    <p class="mt-2 text-3xl font-black text-gray-900">{{ $summary['totalOrders'] }}</p>
                    <p class="mt-1 text-sm text-gray-500">All custom flower orders</p>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-gray-50 p-5">
                    <p class="text-sm font-bold text-gray-500">Total Events</p>
                    <p class="mt-2 text-3xl font-black text-gray-900">{{ $summary['totalEvents'] }}</p>
                    <p class="mt-1 text-sm text-gray-500">All event decoration bookings</p>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-gray-50 p-5">
                    <p class="text-sm font-bold text-gray-500">Payment Records</p>
                    <p class="mt-2 text-3xl font-black text-gray-900">{{ $summary['totalPayments'] }}</p>
                    <p class="mt-1 text-sm text-gray-500">Total payment entries</p>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-gray-50 p-5">
                    <p class="text-sm font-bold text-gray-500">Paid Payments</p>
                    <p class="mt-2 text-3xl font-black text-emerald-700">{{ $summary['paidPayments'] }}</p>
                    <p class="mt-1 text-sm text-gray-500">Successfully completed payments</p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="rounded-3xl border border-orange-100 bg-white p-6 shadow-sm">
            <div class="mb-6">
                <h2 class="text-xl font-black text-gray-900">
                    Quick Actions
                </h2>
                <p class="text-sm text-gray-500">
                    Open important admin sections faster.
                </p>
            </div>

            <div class="space-y-3">
                @foreach($quickActions as $action)
                    <a href="{{ $action['url'] }}"
                       class="group flex items-center gap-4 rounded-2xl border border-gray-100 bg-gray-50 p-4 transition hover:border-orange-200 hover:bg-orange-50">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-2xl shadow-sm">
                            {{ $action['icon'] }}
                        </div>

                        <div class="flex-1">
                            <p class="font-black text-gray-900 group-hover:text-orange-700">
                                {{ $action['title'] }}
                            </p>
                            <p class="text-sm text-gray-500">
                                {{ $action['description'] }}
                            </p>
                        </div>

                        <span class="text-gray-400 transition group-hover:translate-x-1 group-hover:text-orange-600">
                            →
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Bottom Help Box -->
    <div class="rounded-3xl border border-orange-100 bg-gradient-to-r from-orange-50 to-amber-50 p-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h3 class="text-lg font-black text-gray-900">
                    Admin Management Tip
                </h3>
                <p class="mt-1 text-sm text-gray-600">
                    Check pending events, today deliveries and payments daily to keep your flower delivery business running smoothly.
                </p>
            </div>

            <div class="rounded-2xl bg-white px-5 py-3 text-sm font-bold text-orange-700 shadow-sm">
                🌸 Premium Dashboard Active
            </div>
        </div>
    </div>

</div>

@endsection