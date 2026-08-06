@php($social = config('website.social', []))
<footer class="site-footer">
    <div class="footer-flower-line"></div>
    <div class="container footer-grid">
        <div class="footer-brand-column">
            <a href="{{ route('website.home') }}" class="footer-logo-wrap"><img src="{{ asset('website/images/fulawala-logo-white.png') }}" alt="Fulawala"></a>
            <p>{{ config('website.tagline') }}</p>
            <div class="social-row">
                @foreach($social as $platform => $url)
                    @if($url)<a href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ ucfirst($platform) }}">{{ strtoupper(substr($platform, 0, 1)) }}</a>@endif
                @endforeach
            </div>
        </div>
        <div><h3>Explore</h3><ul class="footer-links"><li><a href="{{ route('website.about') }}">About Fulawala</a></li><li><a href="{{ route('website.flowers') }}">Fresh Flowers</a></li><li><a href="{{ route('website.pooja-packets') }}">Pooja Packets</a></li><li><a href="{{ route('website.gallery') }}">Gallery</a></li></ul></div>
        <div><h3>Services</h3><ul class="footer-links"><li><a href="{{ route('website.subscriptions') }}">Flower Subscription</a></li><li><a href="{{ route('website.events') }}">Event Decoration</a></li><li><a href="{{ route('website.contact') }}">Custom Requirement</a></li><li><a href="{{ route('website.contact') }}">Delivery Enquiry</a></li></ul></div>
        <div><h3>Contact</h3><ul class="footer-contact"><li><span>📍</span><span>{{ config('website.address') }}</span></li>@if(config('website.phone'))<li><span>☎</span><a href="tel:{{ preg_replace('/\s+/', '', config('website.phone')) }}">{{ config('website.phone') }}</a></li>@endif<li><span>✉</span><a href="mailto:{{ config('website.email') }}">{{ config('website.email') }}</a></li><li><span>◷</span><span>{{ config('website.business_hours') }}</span></li></ul></div>
    </div>
    <div class="footer-bottom"><div class="container footer-bottom-inner"><p>© {{ date('Y') }} Fulawala. All rights reserved.</p><div><a href="{{ route('website.privacy') }}">Privacy Policy</a><a href="{{ route('website.terms') }}">Terms & Conditions</a></div></div></div>
</footer>
