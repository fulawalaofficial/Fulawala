<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Flower Admin')</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    boxShadow: {
                        soft: '0 15px 40px rgba(15, 23, 42, 0.08)',
                    }
                }
            }
        }
    </script>

    <style>
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #fff7ed;
        }

        ::-webkit-scrollbar-thumb {
            background: #fb923c;
            border-radius: 999px;
        }

        .sidebar-gradient {
            background:
                radial-gradient(circle at top left, rgba(251, 146, 60, 0.16), transparent 35%),
                linear-gradient(180deg, #ffffff 0%, #fff7ed 100%);
        }

        .glass-header {
            background: rgba(255, 255, 255, 0.82);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }
    </style>
</head>

<body class="bg-[#fff7ed] text-slate-800 antialiased">

@php
    $pageTitle = trim($__env->yieldContent('title', 'Dashboard'));

    $links = [
        [
            'label' => 'Dashboard',
            'route' => 'admin.dashboard',
            'active' => 'admin.dashboard',
            'icon' => '🏠',
            'desc' => 'Overview',
        ],
        [
            'label' => 'Pooja Packets',
            'route' => 'admin.pooja-packets.index',
            'active' => 'admin.pooja-packets.*',
            'icon' => '🪔',
            'desc' => 'Packages',
        ],
        [
            'label' => 'Flowers',
            'route' => 'admin.flowers.index',
            'active' => 'admin.flowers.*',
            'icon' => '🌸',
            'desc' => 'Products',
        ],
        [
            'label' => 'Custom Orders',
            'route' => 'admin.custom-orders.index',
            'active' => 'admin.custom-orders.*',
            'icon' => '🛒',
            'desc' => 'Orders',
        ],
        [
            'label' => 'Subscriptions',
            'route' => 'admin.subscriptions.index',
            'active' => 'admin.subscriptions.*',
            'icon' => '📦',
            'desc' => 'Plans',
        ],
        [
            'label' => 'Daily Deliveries',
            'route' => 'admin.daily-deliveries.index',
            'active' => 'admin.daily-deliveries.*',
            'icon' => '🚚',
            'desc' => 'Delivery',
        ],
        [
            'label' => 'Event Bookings',
            'route' => 'admin.event-bookings.index',
            'active' => 'admin.event-bookings.*',
            'icon' => '🎉',
            'desc' => 'Events',
        ],
        [
            'label' => 'Quotations',
            'route' => 'admin.quotations.index',
            'active' => 'admin.quotations.*',
            'icon' => '🧾',
            'desc' => 'Quotes',
        ],
        [
            'label' => 'Staff',
            'route' => 'admin.staff.index',
            'active' => 'admin.staff.*',
            'icon' => '👥',
            'desc' => 'Team',
        ],
        [
            'label' => 'Payments',
            'route' => 'admin.payments.index',
            'active' => 'admin.payments.*',
            'icon' => '💳',
            'desc' => 'Money',
        ],
        [
            'label' => 'Customers',
            'route' => 'admin.customers.index',
            'active' => 'admin.customers.*',
            'icon' => '🙋',
            'desc' => 'Users',
        ],
        [
            'label' => 'Reports',
            'route' => 'admin.reports.index',
            'active' => 'admin.reports.*',
            'icon' => '📊',
            'desc' => 'Analytics',
        ],
        [
            'label' => 'Settings',
            'route' => 'admin.settings.index',
            'active' => 'admin.settings.*',
            'icon' => '⚙️',
            'desc' => 'Config',
        ],
    ];
@endphp

<div class="min-h-screen flex">

    @auth
        <div id="mobileSidebarOverlay"
             class="fixed inset-0 bg-slate-900/50 z-40 hidden md:hidden"></div>

        <aside id="sidebar"
               class="sidebar-gradient fixed md:sticky top-0 left-0 z-50 w-80 h-screen border-r border-orange-100 shadow-soft transform -translate-x-full md:translate-x-0 transition-transform duration-300 overflow-y-auto">

            <div class="p-5">

                <div class="flex items-center justify-between mb-6">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-orange-500 to-red-500 text-white flex items-center justify-center text-2xl shadow-lg shadow-orange-200">
                            🌸
                        </div>

                        <div>
                            <h1 class="text-xl font-black text-slate-900 leading-tight">Flower Admin</h1>
                            <p class="text-xs font-bold text-orange-600">Fulawala Management</p>
                        </div>
                    </a>

                    <button type="button"
                            id="closeSidebar"
                            class="md:hidden w-10 h-10 rounded-xl bg-white border border-orange-100 text-slate-700 font-black">
                        ✕
                    </button>
                </div>

                <div class="bg-gradient-to-br from-orange-500 to-red-500 rounded-3xl p-5 text-white shadow-lg shadow-orange-200 mb-6">
                    <p class="text-sm text-orange-100 font-semibold">Welcome back</p>
                    <h2 class="text-xl font-black mt-1">
                        {{ auth()->user()->name ?? 'Admin' }}
                    </h2>
                    <p class="text-xs text-orange-100 mt-2">
                        Manage flowers, packets, orders and subscriptions.
                    </p>
                </div>

                <div class="mb-3 px-2">
                    <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Main Menu</p>
                </div>

                <nav class="space-y-1.5">
                    @foreach($links as $item)
                        @php
                            $isActive = request()->routeIs($item['active']);
                        @endphp

                        <a href="{{ route($item['route']) }}"
                           class="group flex items-center gap-3 px-4 py-3 rounded-2xl transition-all duration-200
                           {{ $isActive
                                ? 'bg-gradient-to-r from-orange-500 to-red-500 text-white shadow-lg shadow-orange-200'
                                : 'text-slate-600 hover:bg-white hover:text-orange-700 hover:shadow-sm'
                           }}">

                            <span class="w-10 h-10 rounded-xl flex items-center justify-center text-lg
                                {{ $isActive ? 'bg-white/20' : 'bg-orange-50 group-hover:bg-orange-100' }}">
                                {{ $item['icon'] }}
                            </span>

                            <span class="flex-1">
                                <span class="block text-sm font-black">{{ $item['label'] }}</span>
                                <span class="block text-xs {{ $isActive ? 'text-orange-100' : 'text-slate-400' }}">
                                    {{ $item['desc'] }}
                                </span>
                            </span>

                            @if($isActive)
                                <span class="text-white text-lg">›</span>
                            @endif
                        </a>
                    @endforeach
                </nav>

                <form method="POST" action="{{ route('admin.logout') }}" class="mt-7">
                    @csrf

                    <button type="submit"
                            class="w-full flex items-center justify-center gap-2 bg-slate-900 hover:bg-slate-800 text-white rounded-2xl py-3 font-black shadow-lg shadow-slate-200 transition">
                        <span>🚪</span>
                        <span>Logout</span>
                    </button>
                </form>

                <div class="mt-6 p-4 rounded-2xl bg-white border border-orange-100">
                    <p class="text-xs font-bold text-slate-400 uppercase">Support</p>
                    <p class="text-sm font-bold text-slate-700 mt-1">Need help?</p>
                    <p class="text-xs text-slate-500 mt-1">Check your admin settings or reports section.</p>
                </div>
            </div>
        </aside>
    @endauth

    <div class="flex-1 min-w-0">

        @auth
            <header class="glass-header sticky top-0 z-30 border-b border-orange-100">
                <div class="px-4 md:px-8 py-4">

                    <div class="flex items-center justify-between gap-4">

                        <div class="flex items-center gap-3 min-w-0">
                            <button type="button"
                                    id="openSidebar"
                                    class="md:hidden w-11 h-11 rounded-2xl bg-white border border-orange-100 text-slate-800 font-black shadow-sm">
                                ☰
                            </button>

                            <div class="min-w-0">
                                <p class="text-xs font-black text-orange-600 uppercase tracking-wider">
                                    Admin Panel
                                </p>
                                <h2 class="text-xl md:text-2xl font-black text-slate-900 truncate">
                                    {{ $pageTitle }}
                                </h2>
                            </div>
                        </div>

                        <div class="hidden lg:flex items-center flex-1 max-w-xl mx-6">
                            <div class="w-full relative">
                                <span class="absolute left-4 top-3 text-slate-400">🔍</span>
                                <input type="text"
                                       placeholder="Search menu, orders, packets..."
                                       class="w-full bg-white border border-orange-100 rounded-2xl pl-11 pr-4 py-3 text-sm focus:ring-2 focus:ring-orange-200 focus:border-orange-400 outline-none shadow-sm">
                            </div>
                        </div>

                        <div class="flex items-center gap-3">

                            <a href="{{ route('admin.pooja-packets.create') }}"
                               class="hidden sm:inline-flex items-center gap-2 bg-orange-600 hover:bg-orange-700 text-white px-4 py-3 rounded-2xl font-black shadow-lg shadow-orange-200 transition">
                                <span>+</span>
                                <span>Add Packet</span>
                            </a>

                            <div class="hidden sm:flex items-center gap-3 bg-white border border-orange-100 rounded-2xl px-4 py-2 shadow-sm">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-orange-500 to-red-500 text-white flex items-center justify-center font-black">
                                    {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                                </div>

                                <div>
                                    <p class="text-sm font-black text-slate-800 leading-tight">
                                        {{ auth()->user()->name ?? 'Admin' }}
                                    </p>
                                    <p class="text-xs text-slate-400 font-semibold">
                                        Administrator
                                    </p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </header>
        @endauth

        <main class="{{ auth()->check() ? 'p-4 md:p-8' : 'p-4 md:p-8' }}">

            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-5 py-4 rounded-2xl mb-5 font-bold shadow-sm">
                    ✅ {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-2xl mb-5 font-bold shadow-sm">
                    ⚠️ {{ session('error') }}
                </div>
            @endif

            @yield('content')

        </main>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.getElementById('sidebar');
        const openSidebar = document.getElementById('openSidebar');
        const closeSidebar = document.getElementById('closeSidebar');
        const overlay = document.getElementById('mobileSidebarOverlay');

        function showSidebar() {
            if (!sidebar || !overlay) return;
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
        }

        function hideSidebar() {
            if (!sidebar || !overlay) return;
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        }

        if (openSidebar) {
            openSidebar.addEventListener('click', showSidebar);
        }

        if (closeSidebar) {
            closeSidebar.addEventListener('click', hideSidebar);
        }

        if (overlay) {
            overlay.addEventListener('click', hideSidebar);
        }
    });
</script>

@stack('scripts')

</body>
</html>