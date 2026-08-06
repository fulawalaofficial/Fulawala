@extends('admin.layout')

@section('title', 'Today Delivery Operations')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIINfQ3BT5sMZkYhLwUTaK0gQmG6w7IfbI=" crossorigin="">
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css">

<style>
    .delivery-page { --orange: #ea580c; --orange-dark: #c2410c; --ink: #0f172a; --muted: #64748b; }
    .delivery-page * { box-sizing: border-box; }
    .delivery-shell { max-width: 1540px; margin: 0 auto; padding: 24px; }
    .hero-panel { position: relative; overflow: hidden; border-radius: 26px; padding: 28px; color: #fff; background: linear-gradient(135deg, #9a3412 0%, #ea580c 58%, #fb923c 100%); box-shadow: 0 24px 60px rgba(194, 65, 12, .22); }
    .hero-panel:before, .hero-panel:after { content: ''; position: absolute; border-radius: 999px; background: rgba(255,255,255,.09); }
    .hero-panel:before { width: 280px; height: 280px; right: -70px; top: -150px; }
    .hero-panel:after { width: 190px; height: 190px; right: 180px; bottom: -140px; }
    .hero-content { position: relative; z-index: 2; display: flex; align-items: center; justify-content: space-between; gap: 24px; flex-wrap: wrap; }
    .eyebrow { margin: 0 0 7px; font-size: 12px; font-weight: 900; letter-spacing: .16em; text-transform: uppercase; color: #ffedd5; }
    .hero-title { margin: 0; font-size: clamp(28px, 4vw, 42px); line-height: 1.05; font-weight: 950; }
    .hero-copy { max-width: 760px; margin: 10px 0 0; color: #fff7ed; font-size: 15px; line-height: 1.7; }
    .hero-date { min-width: 220px; padding: 16px 18px; border: 1px solid rgba(255,255,255,.25); border-radius: 18px; background: rgba(255,255,255,.12); backdrop-filter: blur(8px); }
    .hero-date small { display:block; color:#ffedd5; font-weight:800; text-transform:uppercase; letter-spacing:.08em; }
    .hero-date strong { display:block; margin-top:4px; font-size:20px; }
    .filter-panel, .stats-grid, .content-panel { margin-top: 20px; }
    .filter-panel { border: 1px solid #e2e8f0; border-radius: 22px; padding: 18px; background: #fff; box-shadow: 0 14px 36px rgba(15,23,42,.06); }
    .filter-grid { display: grid; grid-template-columns: 1.1fr 2fr 1fr 1fr auto; gap: 13px; align-items: end; }
    .field label { display:block; margin-bottom:7px; color:#334155; font-size:12px; font-weight:900; text-transform:uppercase; letter-spacing:.06em; }
    .field input, .field select { width:100%; min-height:46px; padding:0 13px; border:1px solid #cbd5e1; border-radius:13px; background:#fff; color:#0f172a; font-weight:650; outline:none; transition:.2s; }
    .field input:focus, .field select:focus { border-color:#fb923c; box-shadow:0 0 0 4px rgba(251,146,60,.15); }
    .btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; min-height:44px; padding:0 15px; border:0; border-radius:12px; font-weight:850; text-decoration:none; cursor:pointer; transition:.18s; white-space:nowrap; }
    .btn:hover { transform: translateY(-1px); }
    .btn-primary { background:#ea580c; color:#fff; box-shadow:0 10px 22px rgba(234,88,12,.22); }
    .btn-primary:hover { background:#c2410c; }
    .btn-soft { background:#fff7ed; color:#c2410c; border:1px solid #fed7aa; }
    .btn-white { background:#fff; color:#0f172a; border:1px solid #e2e8f0; }
    .btn-green { background:#ecfdf5; color:#047857; border:1px solid #a7f3d0; }
    .btn-blue { background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; }
    .stats-grid { display:grid; grid-template-columns:repeat(4, minmax(0, 1fr)); gap:15px; }
    .stat-card { padding:19px; border:1px solid #e2e8f0; border-radius:20px; background:#fff; box-shadow:0 12px 30px rgba(15,23,42,.055); }
    .stat-top { display:flex; align-items:center; justify-content:space-between; gap:12px; }
    .stat-icon { width:46px; height:46px; display:grid; place-items:center; border-radius:14px; font-size:21px; background:#fff7ed; }
    .stat-value { margin-top:12px; font-size:30px; line-height:1; font-weight:950; color:#0f172a; }
    .stat-label { margin-top:7px; color:#64748b; font-size:13px; font-weight:750; }
    .progress { height:7px; margin-top:14px; overflow:hidden; border-radius:99px; background:#f1f5f9; }
    .progress > span { display:block; height:100%; border-radius:99px; background:linear-gradient(90deg,#fb923c,#ea580c); }
    .content-panel { border:1px solid #e2e8f0; border-radius:24px; background:#fff; box-shadow:0 16px 38px rgba(15,23,42,.06); overflow:hidden; }
    .tab-bar { display:flex; gap:8px; padding:14px; border-bottom:1px solid #e2e8f0; background:#f8fafc; overflow-x:auto; }
    .tab-button { border:0; border-radius:13px; padding:12px 17px; background:transparent; color:#64748b; font-weight:900; cursor:pointer; white-space:nowrap; }
    .tab-button.active { background:#fff; color:#c2410c; box-shadow:0 6px 16px rgba(15,23,42,.08); }
    .tab-content { display:none; padding:18px; }
    .tab-content.active { display:block; }
    .section-head { display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom:15px; flex-wrap:wrap; }
    .section-head h2 { margin:0; color:#0f172a; font-size:22px; font-weight:950; }
    .section-head p { margin:5px 0 0; color:#64748b; font-size:13px; }
    .delivery-list { display:grid; gap:14px; }
    .delivery-card { border:1px solid #e2e8f0; border-radius:19px; padding:17px; background:#fff; transition:.18s; }
    .delivery-card:hover { border-color:#fed7aa; box-shadow:0 12px 28px rgba(15,23,42,.07); }
    .delivery-card-grid { display:grid; grid-template-columns:1.2fr 1.65fr 1fr 1.05fr; gap:18px; align-items:start; }
    .customer-row { display:flex; gap:12px; align-items:flex-start; }
    .avatar { flex:0 0 auto; width:48px; height:48px; display:grid; place-items:center; overflow:hidden; border-radius:15px; background:linear-gradient(135deg,#ffedd5,#fed7aa); color:#9a3412; font-weight:950; font-size:18px; }
    .avatar img { width:100%; height:100%; object-fit:cover; }
    .customer-name { margin:0; color:#0f172a; font-size:16px; font-weight:950; }
    .meta { margin-top:5px; color:#64748b; font-size:13px; line-height:1.55; }
    .label { margin-bottom:7px; color:#94a3b8; font-size:11px; font-weight:950; letter-spacing:.08em; text-transform:uppercase; }
    .address-text { color:#334155; font-size:13px; line-height:1.65; }
    .item-list { margin:8px 0 0; padding:0; list-style:none; color:#475569; font-size:12px; }
    .item-list li { margin-top:4px; }
    .amount { color:#0f172a; font-size:20px; font-weight:950; }
    .badge { display:inline-flex; align-items:center; padding:6px 9px; border-radius:999px; font-size:11px; font-weight:900; }
    .badge-green { background:#dcfce7; color:#166534; }
    .badge-orange { background:#ffedd5; color:#9a3412; }
    .badge-red { background:#fee2e2; color:#991b1b; }
    .badge-blue { background:#dbeafe; color:#1e40af; }
    .card-actions { display:flex; gap:8px; flex-wrap:wrap; margin-top:14px; padding-top:13px; border-top:1px dashed #e2e8f0; }
    .status-form { display:flex; gap:8px; align-items:center; margin-top:9px; }
    .status-form select { min-width:160px; height:40px; border:1px solid #cbd5e1; border-radius:11px; padding:0 9px; font-weight:750; color:#334155; }
    .status-form button { min-height:40px; }
    .empty-state { padding:55px 20px; text-align:center; border:1px dashed #cbd5e1; border-radius:18px; background:#f8fafc; }
    .empty-state .icon { font-size:42px; }
    .empty-state h3 { margin:12px 0 6px; font-size:19px; color:#0f172a; }
    .empty-state p { margin:0; color:#64748b; }
    .pagination-wrap { margin-top:18px; }
    .map-modal { position:fixed; inset:0; z-index:9999; display:none; align-items:center; justify-content:center; padding:20px; background:rgba(15,23,42,.66); backdrop-filter:blur(5px); }
    .map-modal.open { display:flex; }
    .map-dialog { width:min(1180px, 100%); max-height:92vh; overflow:hidden; border-radius:24px; background:#fff; box-shadow:0 30px 90px rgba(2,6,23,.35); }
    .map-dialog-head { display:flex; align-items:center; justify-content:space-between; gap:16px; padding:17px 20px; border-bottom:1px solid #e2e8f0; }
    .map-dialog-head h3 { margin:0; font-size:19px; color:#0f172a; }
    .map-close { width:40px; height:40px; border:0; border-radius:12px; background:#f1f5f9; color:#0f172a; font-size:22px; cursor:pointer; }
    .map-layout { display:grid; grid-template-columns:minmax(0, 1fr) 310px; min-height:560px; }
    #deliveryMap { min-height:560px; background:#e2e8f0; }
    .map-side { padding:19px; border-left:1px solid #e2e8f0; overflow-y:auto; }
    .map-side h4 { margin:0 0 6px; color:#0f172a; font-size:18px; }
    .map-side p { margin:0; color:#64748b; line-height:1.65; font-size:13px; }
    .route-info { margin-top:15px; padding:14px; border-radius:15px; background:#fff7ed; color:#9a3412; font-weight:800; font-size:13px; }
    .map-actions { display:grid; gap:9px; margin-top:17px; }
    .map-status { margin-top:13px; padding:11px 12px; border-radius:12px; background:#f8fafc; color:#475569; font-size:12px; line-height:1.5; }
    .leaflet-routing-container { max-height:220px; overflow:auto; font-size:11px; }
    @media (max-width: 1180px) {
        .filter-grid { grid-template-columns:repeat(2, minmax(0,1fr)); }
        .filter-actions { grid-column:1/-1; }
        .delivery-card-grid { grid-template-columns:1fr 1fr; }
    }
    @media (max-width: 800px) {
        .delivery-shell { padding:14px; }
        .hero-panel { padding:22px; border-radius:20px; }
        .stats-grid { grid-template-columns:1fr 1fr; }
        .delivery-card-grid { grid-template-columns:1fr; }
        .map-layout { grid-template-columns:1fr; }
        #deliveryMap { min-height:390px; }
        .map-side { border-left:0; border-top:1px solid #e2e8f0; }
    }
    @media (max-width: 560px) {
        .filter-grid, .stats-grid { grid-template-columns:1fr; }
        .tab-content { padding:12px; }
        .delivery-card { padding:14px; }
        .status-form { align-items:stretch; flex-direction:column; }
        .status-form select { width:100%; }
    }
</style>

@php
    $customCompletion = $stats['custom_total'] > 0
        ? round(($stats['custom_delivered'] / $stats['custom_total']) * 100)
        : 0;

    $subscriptionCompletion = $stats['subscription_total'] > 0
        ? round(($stats['subscription_delivered'] / $stats['subscription_total']) * 100)
        : 0;

    $totalDeliveries = $stats['custom_total'] + $stats['subscription_total'];
    $totalDelivered = $stats['custom_delivered'] + $stats['subscription_delivered'];
@endphp

<div class="delivery-page">
    <div class="delivery-shell">
        @if(session('success'))
            <div style="margin-bottom:16px;padding:14px 16px;border:1px solid #a7f3d0;border-radius:14px;background:#ecfdf5;color:#047857;font-weight:800;">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div style="margin-bottom:16px;padding:14px 16px;border:1px solid #fecaca;border-radius:14px;background:#fef2f2;color:#b91c1c;font-weight:800;">
                {{ $errors->first() }}
            </div>
        @endif

        <section class="hero-panel">
            <div class="hero-content">
                <div>
                    <p class="eyebrow">Fulawala Delivery Control</p>
                    <h1 class="hero-title">Today Delivery Operations</h1>
                    <p class="hero-copy">
                        Manage custom flower orders and subscription deliveries from one screen.
                        Call the customer, update delivery status, and navigate from your live location to the customer address.
                    </p>
                </div>
                <div class="hero-date">
                    <small>Selected delivery date</small>
                    <strong>{{ \Illuminate\Support\Carbon::parse($selectedDate)->format('d M Y') }}</strong>
                </div>
            </div>
        </section>

        <form method="GET" action="{{ route('admin.today-deliveries.index') }}" class="filter-panel">
            <div class="filter-grid">
                <div class="field">
                    <label for="date">Delivery date</label>
                    <input id="date" type="date" name="date" value="{{ $selectedDate }}">
                </div>
                <div class="field">
                    <label for="search">Search customer, phone, order ID, address</label>
                    <input id="search" type="search" name="search" value="{{ $search }}" placeholder="Example: 98xxxxxx or Bhubaneswar">
                </div>
                <div class="field">
                    <label for="custom_status">Custom order status</label>
                    <select id="custom_status" name="custom_status">
                        <option value="">All statuses</option>
                        @foreach($customStatuses as $status)
                            <option value="{{ $status }}" @selected($customStatus === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="subscription_status">Subscription status</label>
                    <select id="subscription_status" name="subscription_status">
                        <option value="">All statuses</option>
                        @foreach($subscriptionStatuses as $status)
                            <option value="{{ $status }}" @selected($subscriptionStatus === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-actions" style="display:flex;gap:9px;flex-wrap:wrap;">
                    <button class="btn btn-primary" type="submit">Apply Filter</button>
                    <a class="btn btn-white" href="{{ route('admin.today-deliveries.index') }}">Today</a>
                </div>
            </div>
        </form>

        <section class="stats-grid">
            <article class="stat-card">
                <div class="stat-top"><span class="label">All deliveries</span><span class="stat-icon">📦</span></div>
                <div class="stat-value">{{ $totalDeliveries }}</div>
                <div class="stat-label">Custom + subscription deliveries</div>
                <div class="progress"><span style="width:{{ $totalDeliveries > 0 ? round(($totalDelivered / $totalDeliveries) * 100) : 0 }}%"></span></div>
            </article>
            <article class="stat-card">
                <div class="stat-top"><span class="label">Custom orders</span><span class="stat-icon">💐</span></div>
                <div class="stat-value">{{ $stats['custom_total'] }}</div>
                <div class="stat-label">{{ $stats['custom_delivered'] }} delivered</div>
                <div class="progress"><span style="width:{{ $customCompletion }}%"></span></div>
            </article>
            <article class="stat-card">
                <div class="stat-top"><span class="label">Subscriptions</span><span class="stat-icon">🪔</span></div>
                <div class="stat-value">{{ $stats['subscription_total'] }}</div>
                <div class="stat-label">{{ $stats['subscription_delivered'] }} delivered</div>
                <div class="progress"><span style="width:{{ $subscriptionCompletion }}%"></span></div>
            </article>
            <article class="stat-card">
                <div class="stat-top"><span class="label">Remaining</span><span class="stat-icon">🛵</span></div>
                <div class="stat-value">{{ max(0, $totalDeliveries - $totalDelivered) }}</div>
                <div class="stat-label">Pending or in progress</div>
                <div class="progress"><span style="width:{{ $totalDeliveries > 0 ? round((max(0, $totalDeliveries - $totalDelivered) / $totalDeliveries) * 100) : 0 }}%"></span></div>
            </article>
        </section>

        <section class="content-panel">
            <div class="tab-bar">
                <button type="button" class="tab-button active" data-tab-target="custom-orders-tab">
                    Custom Orders ({{ $customOrders->total() }})
                </button>
                <button type="button" class="tab-button" data-tab-target="subscription-deliveries-tab">
                    Subscription Deliveries ({{ $subscriptionDeliveries->total() }})
                </button>
            </div>

            <div id="custom-orders-tab" class="tab-content active">
                <div class="section-head">
                    <div>
                        <h2>Custom Flower Orders</h2>
                        <p>One-time flower orders scheduled for {{ \Illuminate\Support\Carbon::parse($selectedDate)->format('d M Y') }}.</p>
                    </div>
                </div>

                @if($customOrders->count())
                    <div class="delivery-list">
                        @foreach($customOrders as $order)
                            @php
                                $customer = $order->user;
                                $address = $order->address;
                                $fullAddress = $address?->full_address ?: 'Address not available';
                                $initial = strtoupper(substr($customer?->name ?: $address?->name ?: 'C', 0, 1));
                                $addressPayload = [
                                    'addressId' => $address?->id,
                                    'customer' => $customer?->name ?: $address?->name ?: 'Customer',
                                    'phone' => $customer?->mobile ?: $address?->number,
                                    'address' => $fullAddress,
                                    'latitude' => $address?->latitude,
                                    'longitude' => $address?->longitude,
                                    'reference' => 'Custom Order #' . $order->id,
                                ];
                                $statusClass = match($order->order_status) {
                                    'Delivered' => 'badge-green',
                                    'Cancelled' => 'badge-red',
                                    'Out for Delivery' => 'badge-blue',
                                    default => 'badge-orange',
                                };
                                $paymentClass = strtolower((string) $order->payment_status) === 'paid' ? 'badge-green' : 'badge-orange';
                            @endphp

                            <article class="delivery-card">
                                <div class="delivery-card-grid">
                                    <div>
                                        <div class="label">Customer</div>
                                        <div class="customer-row">
                                            <div class="avatar">
                                                @if($customer?->profile_photo_url)
                                                    <img src="{{ $customer->profile_photo_url }}" alt="{{ $customer->name }}">
                                                @else
                                                    {{ $initial }}
                                                @endif
                                            </div>
                                            <div>
                                                <h3 class="customer-name">{{ $customer?->name ?: $address?->name ?: 'Customer' }}</h3>
                                                <div class="meta">Order #{{ $order->id }}</div>
                                                <div class="meta">📞 {{ $customer?->mobile ?: $address?->number ?: 'No phone' }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <div class="label">Delivery address</div>
                                        <div class="address-text">{{ $fullAddress }}</div>
                                        @if($address?->address_type)
                                            <span class="badge badge-blue" style="margin-top:8px;">{{ ucfirst($address->address_type) }}</span>
                                        @endif
                                        @if($order->items->count())
                                            <ul class="item-list">
                                                @foreach($order->items->take(4) as $item)
                                                    <li>• {{ $item->flower?->name ?: 'Flower item' }} × {{ $item->quantity }} {{ $item->unit }}</li>
                                                @endforeach
                                                @if($order->items->count() > 4)
                                                    <li>+ {{ $order->items->count() - 4 }} more item(s)</li>
                                                @endif
                                            </ul>
                                        @endif
                                    </div>

                                    <div>
                                        <div class="label">Schedule and amount</div>
                                        <div class="meta" style="margin-top:0;">🕒 {{ $order->delivery_slot ?: 'Time not set' }}</div>
                                        <div class="amount" style="margin-top:10px;">₹{{ number_format((float) $order->total_amount, 2) }}</div>
                                        <span class="badge {{ $paymentClass }}" style="margin-top:7px;">{{ $order->payment_status ?: 'Payment pending' }}</span>
                                    </div>

                                    <div>
                                        <div class="label">Delivery status</div>
                                        <span class="badge {{ $statusClass }}">{{ $order->order_status }}</span>
                                        <form class="status-form" method="POST" action="{{ route('admin.custom-orders.update-status', $order) }}">
                                            @csrf
                                            @method('PATCH')
                                            <select name="order_status" aria-label="Custom order status">
                                                @foreach($customStatuses as $status)
                                                    <option value="{{ $status }}" @selected($order->order_status === $status)>{{ $status }}</option>
                                                @endforeach
                                            </select>
                                            <button class="btn btn-primary" type="submit">Save</button>
                                        </form>
                                    </div>
                                </div>

                                <div class="card-actions">
                                    @if($customer?->mobile || $address?->number)
                                        <a class="btn btn-green" href="tel:{{ preg_replace('/\D+/', '', $customer?->mobile ?: $address?->number) }}">Call Customer</a>
                                    @endif
                                    <button type="button" class="btn btn-blue js-open-map"
                                            data-location="{{ e(json_encode($addressPayload)) }}">View Route Map</button>
                                    <a class="btn btn-soft" target="_blank" rel="noopener"
                                       href="https://www.google.com/maps/search/?api=1&query={{ urlencode($fullAddress) }}">Open Address</a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                    <div class="pagination-wrap">{{ $customOrders->links() }}</div>
                @else
                    <div class="empty-state">
                        <div class="icon">💐</div>
                        <h3>No custom orders found</h3>
                        <p>There are no matching custom flower deliveries for this date and filter.</p>
                    </div>
                @endif
            </div>

            <div id="subscription-deliveries-tab" class="tab-content">
                <div class="section-head">
                    <div>
                        <h2>Subscription Deliveries</h2>
                        <p>Recurring pooja packet deliveries generated for {{ \Illuminate\Support\Carbon::parse($selectedDate)->format('d M Y') }}.</p>
                    </div>
                    @if($selectedDate === now()->toDateString())
                        <form method="POST" action="{{ route('admin.daily-deliveries.generate-today') }}">
                            @csrf
                            <button class="btn btn-primary" type="submit">Generate Today Deliveries</button>
                        </form>
                    @endif
                </div>

                @if($subscriptionDeliveries->count())
                    <div class="delivery-list">
                        @foreach($subscriptionDeliveries as $delivery)
                            @php
                                $subscription = $delivery->subscription;
                                $customer = $subscription?->user;
                                $address = $subscription?->address;
                                $packet = $subscription?->packet;
                                $fullAddress = $address?->full_address ?: 'Address not available';
                                $initial = strtoupper(substr($customer?->name ?: $address?->name ?: 'C', 0, 1));
                                $addressPayload = [
                                    'addressId' => $address?->id,
                                    'customer' => $customer?->name ?: $address?->name ?: 'Customer',
                                    'phone' => $customer?->mobile ?: $address?->number,
                                    'address' => $fullAddress,
                                    'latitude' => $address?->latitude,
                                    'longitude' => $address?->longitude,
                                    'reference' => 'Subscription Delivery #' . $delivery->id,
                                ];
                                $statusClass = match($delivery->delivery_status) {
                                    'Delivered' => 'badge-green',
                                    'Failed', 'Cancelled' => 'badge-red',
                                    'Out for Delivery' => 'badge-blue',
                                    default => 'badge-orange',
                                };
                            @endphp

                            <article class="delivery-card">
                                <div class="delivery-card-grid">
                                    <div>
                                        <div class="label">Customer</div>
                                        <div class="customer-row">
                                            <div class="avatar">
                                                @if($customer?->profile_photo_url)
                                                    <img src="{{ $customer->profile_photo_url }}" alt="{{ $customer->name }}">
                                                @else
                                                    {{ $initial }}
                                                @endif
                                            </div>
                                            <div>
                                                <h3 class="customer-name">{{ $customer?->name ?: $address?->name ?: 'Customer' }}</h3>
                                                <div class="meta">Delivery #{{ $delivery->id }}</div>
                                                <div class="meta">📞 {{ $customer?->mobile ?: $address?->number ?: 'No phone' }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <div class="label">Delivery address</div>
                                        <div class="address-text">{{ $fullAddress }}</div>
                                        <div class="meta" style="margin-top:8px;">
                                            Package: <strong>{{ $packet?->packet_name ?: 'Pooja Packet' }}</strong>
                                        </div>
                                        @if($packet?->daily_quantity)
                                            <div class="meta">Daily quantity: {{ $packet->daily_quantity }}</div>
                                        @endif
                                    </div>

                                    <div>
                                        <div class="label">Schedule and staff</div>
                                        <div class="meta" style="margin-top:0;">🕒 {{ $delivery->fixed_delivery_time ?: 'Time not set' }}</div>
                                        <div class="meta">Delivery boy: {{ $delivery->deliveryBoy?->name ?: 'Not assigned' }}</div>
                                        @if($delivery->deliveryBoy?->phone)
                                            <div class="meta">Staff phone: {{ $delivery->deliveryBoy->phone }}</div>
                                        @endif
                                    </div>

                                    <div>
                                        <div class="label">Delivery status</div>
                                        <span class="badge {{ $statusClass }}">{{ $delivery->delivery_status }}</span>
                                        <form class="status-form" method="POST" action="{{ route('admin.daily-deliveries.update-status', $delivery) }}">
                                            @csrf
                                            @method('PATCH')
                                            <select name="delivery_status" aria-label="Subscription delivery status">
                                                @foreach($subscriptionStatuses as $status)
                                                    <option value="{{ $status }}" @selected($delivery->delivery_status === $status)>{{ $status }}</option>
                                                @endforeach
                                            </select>
                                            <button class="btn btn-primary" type="submit">Save</button>
                                        </form>
                                        @if($delivery->failed_reason)
                                            <div class="meta" style="color:#b91c1c;">Reason: {{ $delivery->failed_reason }}</div>
                                        @endif
                                    </div>
                                </div>

                                <div class="card-actions">
                                    @if($customer?->mobile || $address?->number)
                                        <a class="btn btn-green" href="tel:{{ preg_replace('/\D+/', '', $customer?->mobile ?: $address?->number) }}">Call Customer</a>
                                    @endif
                                    <button type="button" class="btn btn-blue js-open-map"
                                            data-location="{{ e(json_encode($addressPayload)) }}">View Route Map</button>
                                    <a class="btn btn-soft" target="_blank" rel="noopener"
                                       href="https://www.google.com/maps/search/?api=1&query={{ urlencode($fullAddress) }}">Open Address</a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                    <div class="pagination-wrap">{{ $subscriptionDeliveries->links() }}</div>
                @else
                    <div class="empty-state">
                        <div class="icon">🪔</div>
                        <h3>No subscription deliveries found</h3>
                        <p>Generate today deliveries or change the selected date and filters.</p>
                    </div>
                @endif
            </div>
        </section>
    </div>
</div>

<div id="deliveryMapModal" class="map-modal" aria-hidden="true">
    <div class="map-dialog" role="dialog" aria-modal="true" aria-labelledby="mapModalTitle">
        <div class="map-dialog-head">
            <div>
                <div class="label">Live navigation</div>
                <h3 id="mapModalTitle">Route to customer</h3>
            </div>
            <button type="button" class="map-close" id="closeMapModal" aria-label="Close map">×</button>
        </div>
        <div class="map-layout">
            <div id="deliveryMap"></div>
            <aside class="map-side">
                <h4 id="mapCustomerName">Customer</h4>
                <p id="mapReference"></p>
                <p id="mapAddress" style="margin-top:10px;"></p>
                <div id="mapRouteInfo" class="route-info">Waiting for your current location…</div>
                <div class="map-actions">
                    <a id="googleMapsButton" class="btn btn-primary" target="_blank" rel="noopener">Start Google Maps Navigation</a>
                    <a id="callCustomerButton" class="btn btn-green">Call Customer</a>
                    <button type="button" id="retryLocationButton" class="btn btn-white">Retry My Location</button>
                </div>
                <div id="mapStatus" class="map-status">
                    Please allow browser location permission. Customer coordinates will be saved automatically after address lookup.
                </div>
            </aside>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>
<script>
(function () {
    const modal = document.getElementById('deliveryMapModal');
    const closeButton = document.getElementById('closeMapModal');
    const retryLocationButton = document.getElementById('retryLocationButton');
    const googleMapsButton = document.getElementById('googleMapsButton');
    const callCustomerButton = document.getElementById('callCustomerButton');
    const mapCustomerName = document.getElementById('mapCustomerName');
    const mapReference = document.getElementById('mapReference');
    const mapAddress = document.getElementById('mapAddress');
    const mapRouteInfo = document.getElementById('mapRouteInfo');
    const mapStatus = document.getElementById('mapStatus');

    let map = null;
    let routeControl = null;
    let currentPayload = null;
    let destinationPoint = null;
    let currentPoint = null;

    document.querySelectorAll('[data-tab-target]').forEach((button) => {
        button.addEventListener('click', () => {
            document.querySelectorAll('[data-tab-target]').forEach((item) => item.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach((content) => content.classList.remove('active'));
            button.classList.add('active');
            document.getElementById(button.dataset.tabTarget)?.classList.add('active');
        });
    });

    document.querySelectorAll('.js-open-map').forEach((button) => {
        button.addEventListener('click', async () => {
            try {
                currentPayload = JSON.parse(button.dataset.location || '{}');
            } catch (error) {
                currentPayload = {};
            }

            openModal();
            fillCustomerDetails();
            initialiseMap();
            await resolveDestination();
            requestCurrentLocation();
        });
    });

    closeButton?.addEventListener('click', closeModal);
    retryLocationButton?.addEventListener('click', requestCurrentLocation);
    modal?.addEventListener('click', (event) => {
        if (event.target === modal) closeModal();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal?.classList.contains('open')) closeModal();
    });

    function openModal() {
        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        clearRoute();
    }

    function fillCustomerDetails() {
        mapCustomerName.textContent = currentPayload.customer || 'Customer';
        mapReference.textContent = currentPayload.reference || '';
        mapAddress.textContent = currentPayload.address || 'Address not available';
        mapStatus.textContent = 'Preparing customer location and requesting your live location…';
        mapRouteInfo.textContent = 'Waiting for route information…';

        if (currentPayload.phone) {
            callCustomerButton.href = 'tel:' + String(currentPayload.phone).replace(/\D+/g, '');
            callCustomerButton.style.display = 'inline-flex';
        } else {
            callCustomerButton.style.display = 'none';
        }
    }

    function initialiseMap() {
        if (!map) {
            map = L.map('deliveryMap', { zoomControl: true }).setView([20.2961, 85.8245], 12);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);
        }

        clearRoute();
        window.setTimeout(() => map.invalidateSize(), 180);
    }

    async function resolveDestination() {
        const latitude = Number(currentPayload.latitude);
        const longitude = Number(currentPayload.longitude);

        if (Number.isFinite(latitude) && Number.isFinite(longitude) && latitude !== 0 && longitude !== 0) {
            destinationPoint = L.latLng(latitude, longitude);
            showDestinationMarker();
            mapStatus.textContent = 'Customer coordinates loaded from the saved address.';
            updateGoogleMapsLink();
            return;
        }

        if (!currentPayload.address || currentPayload.address === 'Address not available') {
            mapStatus.textContent = 'Customer address is unavailable. Please update the customer address first.';
            return;
        }

        mapStatus.textContent = 'Finding the customer address on the map…';

        try {
            const query = encodeURIComponent(currentPayload.address + ', India');
            const response = await fetch('https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&countrycodes=in&q=' + query, {
                headers: { 'Accept': 'application/json' }
            });

            if (!response.ok) throw new Error('Address lookup failed');

            const results = await response.json();
            if (!Array.isArray(results) || !results.length) {
                throw new Error('Address not found');
            }

            destinationPoint = L.latLng(Number(results[0].lat), Number(results[0].lon));
            currentPayload.latitude = destinationPoint.lat;
            currentPayload.longitude = destinationPoint.lng;
            showDestinationMarker();
            updateGoogleMapsLink();
            mapStatus.textContent = 'Customer address found. Saving coordinates for faster future navigation…';
            await saveCoordinates(destinationPoint.lat, destinationPoint.lng);
        } catch (error) {
            destinationPoint = null;
            mapStatus.textContent = 'Exact map point could not be found. Use “Start Google Maps Navigation” to search the written address.';
            updateGoogleMapsLink();
        }
    }

    function showDestinationMarker() {
        if (!destinationPoint) return;
        L.marker(destinationPoint)
            .addTo(map)
            .bindPopup('<strong>' + escapeHtml(currentPayload.customer || 'Customer') + '</strong><br>' + escapeHtml(currentPayload.address || ''))
            .openPopup();
        map.setView(destinationPoint, 15);
    }

    function requestCurrentLocation() {
        if (!navigator.geolocation) {
            mapStatus.textContent = 'This browser does not support live location. Open the route in Google Maps instead.';
            updateGoogleMapsLink();
            return;
        }

        mapStatus.textContent = 'Requesting your current location…';

        navigator.geolocation.getCurrentPosition(
            (position) => {
                currentPoint = L.latLng(position.coords.latitude, position.coords.longitude);
                L.circleMarker(currentPoint, {
                    radius: 9,
                    weight: 4,
                    fillOpacity: 1
                }).addTo(map).bindPopup('My current location');
                mapStatus.textContent = 'Your live location is ready. Building the best route…';
                updateGoogleMapsLink();
                buildRoute();
            },
            (error) => {
                currentPoint = null;
                const messages = {
                    1: 'Location permission was denied. Allow location in the browser address bar and retry.',
                    2: 'Your current location is unavailable. Check GPS or internet and retry.',
                    3: 'Location request timed out. Please retry.'
                };
                mapStatus.textContent = messages[error.code] || 'Could not get your current location.';
                updateGoogleMapsLink();
            },
            { enableHighAccuracy: true, timeout: 15000, maximumAge: 30000 }
        );
    }

    function buildRoute() {
        clearRoute(false);

        if (!currentPoint || !destinationPoint) {
            mapRouteInfo.textContent = 'Route details are unavailable until both locations are ready.';
            return;
        }

        if (L.Routing && L.Routing.control) {
            routeControl = L.Routing.control({
                waypoints: [currentPoint, destinationPoint],
                routeWhileDragging: false,
                addWaypoints: false,
                fitSelectedRoutes: true,
                showAlternatives: false,
                lineOptions: { styles: [{ weight: 6, opacity: .8 }] },
                createMarker: () => null
            }).on('routesfound', function (event) {
                const route = event.routes[0];
                const km = (route.summary.totalDistance / 1000).toFixed(1);
                const minutes = Math.max(1, Math.round(route.summary.totalTime / 60));
                mapRouteInfo.textContent = km + ' km • approximately ' + minutes + ' minutes';
                mapStatus.textContent = 'Route ready. Use Google Maps navigation for turn-by-turn guidance.';
            }).on('routingerror', function () {
                drawFallbackLine();
            }).addTo(map);
        } else {
            drawFallbackLine();
        }
    }

    function drawFallbackLine() {
        if (!currentPoint || !destinationPoint) return;
        L.polyline([currentPoint, destinationPoint], { weight: 5, dashArray: '8 8' }).addTo(map);
        map.fitBounds(L.latLngBounds([currentPoint, destinationPoint]), { padding: [40, 40] });
        const km = currentPoint.distanceTo(destinationPoint) / 1000;
        mapRouteInfo.textContent = km.toFixed(1) + ' km straight-line distance. Open Google Maps for the road route.';
        mapStatus.textContent = 'A direct line is shown because the route service is unavailable.';
    }

    function clearRoute(resetPoints = true) {
        if (routeControl && map) {
            map.removeControl(routeControl);
            routeControl = null;
        }

        if (map) {
            map.eachLayer((layer) => {
                if (!(layer instanceof L.TileLayer)) map.removeLayer(layer);
            });
        }

        if (resetPoints) {
            currentPoint = null;
            destinationPoint = null;
        }
    }

    function updateGoogleMapsLink() {
        const destination = destinationPoint
            ? destinationPoint.lat + ',' + destinationPoint.lng
            : (currentPayload.address || '');
        let url = 'https://www.google.com/maps/dir/?api=1&travelmode=driving&destination=' + encodeURIComponent(destination);
        if (currentPoint) {
            url += '&origin=' + encodeURIComponent(currentPoint.lat + ',' + currentPoint.lng);
        }
        googleMapsButton.href = url;
    }

    async function saveCoordinates(latitude, longitude) {
        if (!currentPayload.addressId) {
            mapStatus.textContent = 'Coordinates found, but this delivery has no saved address record.';
            return;
        }

        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        try {
            const response = await fetch(
                '{{ url('/admin/today-deliveries/addresses') }}/' + currentPayload.addressId + '/coordinates',
                {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token || ''
                    },
                    body: JSON.stringify({ latitude, longitude })
                }
            );

            if (!response.ok) throw new Error('Coordinate save failed');
            mapStatus.textContent = 'Customer coordinates saved. Building route from your current location…';
        } catch (error) {
            mapStatus.textContent = 'Customer location found, but coordinates could not be saved. Navigation will still work.';
        }
    }

    function escapeHtml(value) {
        return String(value || '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }
})();
</script>
@endsection
