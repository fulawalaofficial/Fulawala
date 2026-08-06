<section class="page-hero">
    <div class="page-hero-orb orb-one"></div><div class="page-hero-orb orb-two"></div>
    <div class="container page-hero-content">
        <nav class="breadcrumbs" aria-label="Breadcrumb"><a href="{{ route('website.home') }}">Home</a><span>›</span><span>{{ $title }}</span></nav>
        <span class="eyebrow light">{{ $eyebrow ?? 'Fulawala' }}</span>
        <h1>{{ $title }}</h1>
        @isset($description)<p>{{ $description }}</p>@endisset
    </div>
</section>
