<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Flower Admin')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-orange-50 text-gray-800">
<div class="min-h-screen flex">
    @auth
    <aside class="w-72 bg-white border-r border-orange-200 p-5 hidden md:block">
        <h1 class="text-2xl font-black text-orange-700 mb-6">🌸 Flower Admin</h1>
        @php
            $links = [
                ['Dashboard','admin.dashboard'], ['Pooja Packets','admin.pooja-packets.index'], ['Flowers','admin.flowers.index'],
                ['Custom Orders','admin.custom-orders.index'], ['Subscriptions','admin.subscriptions.index'], ['Daily Deliveries','admin.daily-deliveries.index'],
                ['Event Bookings','admin.event-bookings.index'], ['Quotations','admin.quotations.index'], ['Staff','admin.staff.index'],
                ['Payments','admin.payments.index'], ['Customers','admin.customers.index'], ['Reports','admin.reports.index'], ['Settings','admin.settings.index'],
            ];
        @endphp
        <nav class="space-y-1">
            @foreach($links as [$label, $route])
                <a class="block px-3 py-2 rounded-lg hover:bg-orange-100 {{ request()->routeIs($route) ? 'bg-orange-100 text-orange-700 font-bold' : '' }}" href="{{ route($route) }}">{{ $label }}</a>
            @endforeach
        </nav>
        <form method="POST" action="{{ route('admin.logout') }}" class="mt-6">@csrf<button class="w-full bg-gray-900 text-white rounded-lg py-2">Logout</button></form>
    </aside>
    @endauth
    <main class="flex-1 p-4 md:p-8">
        @if(session('success'))<div class="bg-green-100 text-green-800 px-4 py-3 rounded-lg mb-4">{{ session('success') }}</div>@endif
        @yield('content')
    </main>
</div>
</body>
</html>
