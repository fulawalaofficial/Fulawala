@extends('website.layouts.app')
@section('title', 'Flower & Event Gallery')
@section('meta_description', 'Explore Fulawala flower, pooja packet, subscription and event decoration inspiration.')
@section('content')
@include('website.partials.page-hero', ['title'=>'Gallery','eyebrow'=>'Fresh inspiration','description'=>'A glimpse of flower combinations, pooja preparation and celebration styling.'])
<section class="section gallery-section"><div class="container"><div class="gallery-filter reveal-on-scroll" role="group" aria-label="Gallery filters"><button class="active" type="button" data-filter="all">All</button><button type="button" data-filter="flowers">Flowers</button><button type="button" data-filter="pooja">Pooja</button><button type="button" data-filter="events">Events</button></div><div class="gallery-grid">
<figure class="gallery-item tall reveal-on-scroll" data-category="flowers"><img src="{{ asset('website/images/gallery-flowers-1.svg') }}" alt="Fresh flowers"><figcaption><span>Fresh flowers</span><strong>Morning collection</strong></figcaption></figure>
<figure class="gallery-item reveal-on-scroll" data-category="pooja"><img src="{{ asset('website/images/gallery-pooja.svg') }}" alt="Pooja flower packet"><figcaption><span>Pooja</span><strong>Ready with care</strong></figcaption></figure>
<figure class="gallery-item wide reveal-on-scroll" data-category="events"><img src="{{ asset('website/images/gallery-event-1.svg') }}" alt="Floral event stage"><figcaption><span>Events</span><strong>Elegant celebration decor</strong></figcaption></figure>
<figure class="gallery-item reveal-on-scroll" data-category="flowers"><img src="{{ asset('website/images/gallery-flowers-2.svg') }}" alt="Rose arrangement"><figcaption><span>Flowers</span><strong>Rose arrangement</strong></figcaption></figure>
<figure class="gallery-item tall reveal-on-scroll" data-category="events"><img src="{{ asset('website/images/gallery-event-2.svg') }}" alt="Floral entrance"><figcaption><span>Events</span><strong>Floral entrance</strong></figcaption></figure>
<figure class="gallery-item reveal-on-scroll" data-category="pooja"><img src="{{ asset('website/images/pooja-basket.svg') }}" alt="Pooja basket"><figcaption><span>Pooja</span><strong>Traditional selection</strong></figcaption></figure>
<figure class="gallery-item wide reveal-on-scroll" data-category="flowers"><img src="{{ asset('website/images/hero-bouquet.svg') }}" alt="Flower bouquet"><figcaption><span>Flowers</span><strong>Celebration bouquet</strong></figcaption></figure>
<figure class="gallery-item reveal-on-scroll" data-category="events"><img src="{{ asset('website/images/event-decoration.svg') }}" alt="Floral backdrop"><figcaption><span>Events</span><strong>Floral backdrop</strong></figcaption></figure>
</div></div></section>
<section class="cta-band"><div class="container cta-band-inner reveal-on-scroll"><div><span class="eyebrow light">Inspired by something?</span><h2>Share your idea and let us create a custom plan.</h2></div><a href="{{ route('website.contact') }}" class="btn btn-light">Discuss Your Requirement <span>→</span></a></div></section>
@endsection
