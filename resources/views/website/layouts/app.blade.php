<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $siteName = config('website.name', 'Fulawala');
        $pageTitle = trim($__env->yieldContent('title', 'Fresh Flowers & Pooja Essentials'));
        $metaDescription = trim($__env->yieldContent('meta_description', config('website.description')));
    @endphp
    <title>{{ $pageTitle }} | {{ $siteName }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $pageTitle }} | {{ $siteName }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:image" content="{{ asset('website/images/fulawala-logo.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('website/images/fulawala-icon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('website/css/style.css') }}">
    @stack('styles')
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'LocalBusiness',
        'name' => $siteName,
        'description' => config('website.description'),
        'email' => config('website.email'),
        'telephone' => config('website.phone'),
        'address' => ['@type' => 'PostalAddress', 'addressRegion' => config('website.address'), 'addressCountry' => 'IN'],
        'url' => url('/'),
        'logo' => asset('website/images/fulawala-logo.png'),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>
</head>
<body>
    <a class="skip-link" href="#main-content">Skip to content</a>
    @include('website.partials.header')

    @if(session('success'))
        <div class="flash-wrap" role="status">
            <div class="container">
                <div class="flash flash-success"><span class="flash-icon">✓</span><span>{{ session('success') }}</span><button type="button" class="flash-close" aria-label="Close">×</button></div>
            </div>
        </div>
    @endif

    <main id="main-content">@yield('content')</main>
    @include('website.partials.footer')
    <button class="back-to-top" type="button" aria-label="Back to top">↑</button>
    <script src="{{ asset('website/js/app.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
