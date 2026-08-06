@php($phone = config('website.phone'))
<div class="announcement-bar">
    <div class="container announcement-inner">
        <p><span>🌼</span> Fresh flowers and pooja essentials delivered with care.</p>
        <div class="announcement-links">
            @if($phone)<a href="tel:{{ preg_replace('/\s+/', '', $phone) }}">{{ $phone }}</a>@endif
            <a href="mailto:{{ config('website.email') }}">{{ config('website.email') }}</a>
        </div>
    </div>
</div>
<header class="site-header" data-site-header>
    <div class="container navbar-wrap">
        <a href="{{ route('website.home') }}" class="brand" aria-label="Fulawala home"><img src="{{ asset('website/images/fulawala-logo.png') }}" alt="Fulawala logo"></a>
        <button class="mobile-menu-toggle" type="button" aria-expanded="false" aria-controls="main-navigation"><span></span><span></span><span></span><span class="sr-only">Open navigation</span></button>
        <nav id="main-navigation" class="main-navigation" aria-label="Main navigation">
            <a class="{{ request()->routeIs('website.home') ? 'active' : '' }}" href="{{ route('website.home') }}">Home</a>
            <a class="{{ request()->routeIs('website.about') ? 'active' : '' }}" href="{{ route('website.about') }}">About</a>
            <a class="{{ request()->routeIs('website.flowers') ? 'active' : '' }}" href="{{ route('website.flowers') }}">Flowers</a>
            <a class="{{ request()->routeIs('website.pooja-packets') ? 'active' : '' }}" href="{{ route('website.pooja-packets') }}">Pooja Packets</a>
            <a class="{{ request()->routeIs('website.subscriptions') ? 'active' : '' }}" href="{{ route('website.subscriptions') }}">Subscriptions</a>
            <a class="{{ request()->routeIs('website.events') ? 'active' : '' }}" href="{{ route('website.events') }}">Events</a>
            <a class="{{ request()->routeIs('website.gallery') ? 'active' : '' }}" href="{{ route('website.gallery') }}">Gallery</a>
            <a class="nav-cta {{ request()->routeIs('website.contact') ? 'active' : '' }}" href="{{ route('website.contact') }}">Contact Us</a>
        </nav>
    </div>
</header>
