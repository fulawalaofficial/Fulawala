@php
    $name = data_get($item, 'name') ?? data_get($item, 'flower_name') ?? data_get($item, 'packet_name') ?? data_get($item, 'title') ?? 'Fresh Flower';
    $description = data_get($item, 'short_description') ?? data_get($item, 'description') ?? data_get($item, 'details');
    $price = data_get($item, 'sale_price') ?? data_get($item, 'price') ?? data_get($item, 'monthly_price') ?? data_get($item, 'mrp_price');
    $mrp = data_get($item, 'mrp_price');
    $quantity = data_get($item, 'daily_quantity') ?? data_get($item, 'quantity');
    $packageType = data_get($item, 'package_type_label') ?? data_get($item, 'package_type');
    $image = data_get($item, 'image_url') ?? data_get($item, 'photo_url') ?? data_get($item, 'image') ?? data_get($item, 'photo');
    if ($image && \Illuminate\Support\Str::startsWith($image, ['http://', 'https://'])) $imageUrl = $image;
    elseif ($image && \Illuminate\Support\Str::startsWith($image, ['uploads/', 'images/', 'website/', 'storage/'])) $imageUrl = asset($image);
    elseif ($image) $imageUrl = asset('storage/' . ltrim($image, '/'));
    else $imageUrl = asset($type === 'packet' ? 'website/images/pooja-basket.svg' : 'website/images/flower-fallback.svg');
@endphp
<article class="product-card reveal-on-scroll">
    <div class="product-image-wrap"><img src="{{ $imageUrl }}" alt="{{ $name }}" loading="lazy" onerror="this.src='{{ asset($type === 'packet' ? 'website/images/pooja-basket.svg' : 'website/images/flower-fallback.svg') }}'"><span class="product-badge">{{ $type === 'packet' ? 'Pooja ready' : 'Fresh today' }}</span></div>
    <div class="product-body">
        <div class="product-meta-row">@if($packageType)<span>{{ $packageType }}</span>@elseif($type === 'flower')<span>Fresh flower</span>@endif @if($quantity)<span>{{ $quantity }} daily</span>@endif</div>
        <h3>{{ $name }}</h3>
        <p>{{ $description ? \Illuminate\Support\Str::limit(strip_tags((string) $description), 95) : 'Carefully selected and prepared for a fresh, beautiful experience.' }}</p>
        <div class="product-footer"><div class="price-wrap">@if($price !== null && $price !== '')<strong>₹{{ number_format((float) $price, 0) }}</strong>@if($mrp && (float)$mrp > (float)$price)<del>₹{{ number_format((float)$mrp, 0) }}</del>@endif @else<strong>Ask price</strong>@endif</div><a href="{{ route('website.contact', ['service' => $name]) }}" class="round-link" aria-label="Enquire about {{ $name }}">→</a></div>
    </div>
</article>
