@extends('admin.layout')

@section('title', 'Banner Master')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="rounded-3xl bg-white border border-orange-100 shadow-soft p-5 md:p-7">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-xs font-black uppercase tracking-widest text-orange-600">Home page / Mobile app</p>
                <h1 class="text-2xl font-black text-slate-900 mt-1">Banner Master</h1>
                <p class="text-sm text-slate-500 mt-2">Upload slider artwork, choose a page to open, and control display order, visibility and dates.</p>
            </div>
            <a href="{{ route('admin.home-banners.create') }}"
               class="inline-flex items-center justify-center rounded-2xl bg-gradient-to-r from-orange-500 to-red-500 px-6 py-3.5 font-black text-white shadow-lg shadow-orange-200 hover:opacity-90">
                + Add Banner
            </a>
        </div>
        <div class="mt-5 rounded-2xl bg-orange-50 border border-orange-100 px-4 py-3 text-xs font-semibold text-orange-800">
            Recommended landscape image: <strong>1200 × 600 px</strong>, JPG, PNG or WebP, max 5 MB. The app automatically hides inactive, future or expired banners.
        </div>
    </div>

    @if($banners->isEmpty())
        <div class="bg-white border border-orange-100 rounded-3xl p-14 text-center shadow-soft">
            <div class="text-5xl mb-4">🖼️</div>
            <h2 class="text-xl font-black text-slate-900">No banners yet</h2>
            <p class="text-slate-500 mt-2 text-sm">Add your first banner to make it visible in the Fulawala app.</p>
            <a href="{{ route('admin.home-banners.create') }}" class="inline-flex mt-5 px-6 py-3 rounded-xl bg-orange-600 text-white font-bold">Create first banner</a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            @foreach($banners as $banner)
                @php
                    $current = now();
                    $isLive = $banner->is_active
                        && (!$banner->starts_at || $banner->starts_at->lte($current))
                        && (!$banner->ends_at || $banner->ends_at->gte($current));
                @endphp
                <article class="bg-white rounded-3xl overflow-hidden border border-orange-100 shadow-soft">
                    <div class="aspect-[2/1] bg-orange-50 overflow-hidden">
                        <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}" class="w-full h-full object-cover" loading="lazy">
                    </div>
                    <div class="p-5">
                        <div class="flex justify-between items-start gap-2">
                            <h2 class="text-lg font-black text-slate-900 break-words">{{ $banner->title }}</h2>
                            <span class="shrink-0 text-xs rounded-full px-3 py-1 font-black {{ $isLive ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600' }}">
                                {{ $isLive ? 'Live' : ($banner->is_active ? 'Scheduled / expired' : 'Inactive') }}
                            </span>
                        </div>
                        <dl class="mt-4 grid grid-cols-2 gap-3 text-xs">
                            <div><dt class="text-slate-400 font-bold">Position</dt><dd class="text-slate-900 font-black mt-1">{{ $banner->sort_order }}</dd></div>
                            <div><dt class="text-slate-400 font-bold">Tap destination</dt><dd class="text-slate-900 font-black mt-1">{{ \App\Models\HomeBanner::TARGETS[$banner->target_screen] ?? 'Image only' }}</dd></div>
                            <div><dt class="text-slate-400 font-bold">Starts</dt><dd class="text-slate-800 mt-1">{{ $banner->starts_at?->format('d M Y, H:i') ?? 'Immediately' }}</dd></div>
                            <div><dt class="text-slate-400 font-bold">Ends</dt><dd class="text-slate-800 mt-1">{{ $banner->ends_at?->format('d M Y, H:i') ?? 'No expiry' }}</dd></div>
                        </dl>
                        <div class="flex items-center gap-2 mt-5">
                            <a href="{{ route('admin.home-banners.edit', $banner) }}" class="flex-1 text-center rounded-xl py-3 bg-orange-50 text-orange-700 font-black text-sm hover:bg-orange-100">Edit Banner</a>
                            <form method="POST" action="{{ route('admin.home-banners.destroy', $banner) }}"
                                  onsubmit="return confirm('Delete this banner and its uploaded image?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-4 py-3 rounded-xl bg-red-50 text-red-700 font-black text-sm hover:bg-red-100">Delete</button>
                            </form>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
        <div>{{ $banners->links() }}</div>
    @endif
</div>
@endsection
