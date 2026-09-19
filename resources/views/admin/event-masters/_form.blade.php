@if($errors->any())
    <div class="rounded-2xl border border-red-200 bg-red-50 p-5 text-red-800">
        <p class="font-black">Please fix these fields:</p>
        <ul class="mt-2 list-disc space-y-1 pl-5 text-sm font-semibold">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if(session('error'))
    <div class="rounded-2xl border border-red-200 bg-red-50 p-5 font-semibold text-red-800">
        ⚠️ {{ session('error') }}
    </div>
@endif

<div class="rounded-[2rem] border border-orange-100 bg-white p-4 shadow-sm md:p-5">
    <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
        @foreach([
            ['no' => '1', 'title' => 'Event Details', 'text' => 'Name & description'],
            ['no' => '2', 'title' => 'Plans', 'text' => 'Packages & prices'],
            ['no' => '3', 'title' => 'Media', 'text' => 'Photos & videos'],
            ['no' => '4', 'title' => 'Publish', 'text' => 'Status & order'],
        ] as $step)
            <div class="rounded-2xl bg-orange-50 p-3">
                <div class="flex items-center gap-3">
                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-orange-600 text-sm font-black text-white">{{ $step['no'] }}</span>
                    <div>
                        <p class="text-sm font-black text-gray-900">{{ $step['title'] }}</p>
                        <p class="text-[11px] font-semibold text-gray-500">{{ $step['text'] }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
    <div class="space-y-6 xl:col-span-2">
        {{-- BASIC INFORMATION --}}
        <section class="rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm md:p-6">
            <div>
                <p class="text-xs font-black uppercase tracking-wider text-orange-600">Step 1</p>
                <h2 class="mt-1 text-xl font-black text-gray-900">Basic Event Information</h2>
                <p class="mt-1 text-sm text-gray-500">This information is shown to customers when they browse event packages.</p>
            </div>

            <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-black text-gray-700">Event Name *</label>
                    <input required name="name" value="{{ old('name', $eventMaster->name) }}" placeholder="Example: Premium Wedding Decoration" class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-black text-gray-700">Slug</label>
                    <input name="slug" value="{{ old('slug', $eventMaster->slug) }}" placeholder="Leave blank for auto slug" class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100">
                    <p class="mt-1 text-xs font-semibold text-gray-500">Recommended: leave this blank. It will be generated automatically.</p>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-black text-gray-700">Starting Price</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 font-black text-gray-500">₹</span>
                        <input type="number" step="0.01" min="0" name="starting_price" value="{{ old('starting_price', $eventMaster->starting_price) }}" placeholder="25000" class="w-full rounded-2xl border border-gray-200 bg-gray-50 py-3 pl-9 pr-4 outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100">
                    </div>
                </div>

                <div class="md:col-span-2">
                    <div class="mb-2 flex items-center justify-between">
                        <label class="block text-sm font-black text-gray-700">Short Description</label>
                        <span class="text-xs font-semibold text-gray-400"><span id="short-description-count">0</span>/500</span>
                    </div>
                    <textarea id="short-description" name="short_description" rows="3" maxlength="500" placeholder="Short summary shown on event cards..." class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100">{{ old('short_description', $eventMaster->short_description) }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-black text-gray-700">Full Description</label>
                    <textarea name="description" rows="8" placeholder="Describe decoration style, flower types, stage, entry setup, service coverage, setup time and important notes..." class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100">{{ old('description', $eventMaster->description) }}</textarea>
                </div>
            </div>
        </section>

        {{-- PLANS --}}
        <section class="rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm md:p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-wider text-orange-600">Step 2</p>
                    <h2 class="mt-1 text-xl font-black text-gray-900">Event Plans</h2>
                    <p class="mt-1 text-sm text-gray-500">Add Basic, Premium, Luxury or your own package. Put one feature per line.</p>
                </div>
                <button type="button" id="add-plan" class="rounded-2xl bg-orange-100 px-4 py-2.5 text-sm font-black text-orange-700">+ Add Another Plan</button>
            </div>

            @php
                $oldPlans = old('plans');
                if ($oldPlans === null) {
                    $oldPlans = $eventMaster->exists
                        ? $eventMaster->plans->map(fn($p) => [
                            'id' => $p->id,
                            'name' => $p->name,
                            'price' => $p->price,
                            'description' => $p->description,
                            'features_text' => implode("\n", $p->features ?? []),
                            'status' => $p->status,
                            'sort_order' => $p->sort_order,
                        ])->toArray()
                        : [[
                            'name' => '',
                            'price' => '',
                            'description' => '',
                            'features_text' => '',
                            'status' => 'active',
                            'sort_order' => 0,
                        ]];
                }
            @endphp

            <div id="plans-wrap" class="mt-5 space-y-4">
                @foreach($oldPlans as $i => $plan)
                    <div class="plan-row rounded-3xl border border-orange-100 bg-orange-50/50 p-4">
                        <input type="hidden" data-field="id" name="plans[{{ $i }}][id]" value="{{ $plan['id'] ?? '' }}">

                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="font-black text-gray-800">Plan <span class="plan-number">{{ $i + 1 }}</span></p>
                                <p class="text-xs font-semibold text-gray-500">Leave plan name empty if you do not want to save this row.</p>
                            </div>
                            <button type="button" class="remove-plan rounded-xl bg-red-50 px-3 py-1.5 text-xs font-black text-red-700">Remove</button>
                        </div>

                        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-black uppercase text-gray-500">Plan Name</label>
                                <input data-field="name" name="plans[{{ $i }}][name]" value="{{ $plan['name'] ?? '' }}" placeholder="Premium Plan" class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 outline-none focus:border-orange-400">
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-black uppercase text-gray-500">Price</label>
                                <input data-field="price" type="number" step="0.01" min="0" name="plans[{{ $i }}][price]" value="{{ $plan['price'] ?? '' }}" placeholder="50000" class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 outline-none focus:border-orange-400">
                            </div>

                            <div class="md:col-span-2">
                                <label class="mb-1 block text-xs font-black uppercase text-gray-500">Plan Description</label>
                                <textarea data-field="description" name="plans[{{ $i }}][description]" rows="3" placeholder="What is included in this plan?" class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 outline-none focus:border-orange-400">{{ $plan['description'] ?? '' }}</textarea>
                            </div>

                            <div class="md:col-span-2">
                                <label class="mb-1 block text-xs font-black uppercase text-gray-500">Features — one per line</label>
                                <textarea data-field="features_text" name="plans[{{ $i }}][features_text]" rows="5" placeholder="Fresh flower stage decoration&#10;Welcome gate&#10;Car decoration" class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 outline-none focus:border-orange-400">{{ $plan['features_text'] ?? '' }}</textarea>
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-black uppercase text-gray-500">Status</label>
                                <select data-field="status" name="plans[{{ $i }}][status]" class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 outline-none focus:border-orange-400">
                                    <option value="active" @selected(($plan['status'] ?? 'active') === 'active')>Active</option>
                                    <option value="inactive" @selected(($plan['status'] ?? '') === 'inactive')>Inactive</option>
                                </select>
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-black uppercase text-gray-500">Sort Order</label>
                                <input data-field="sort_order" type="number" min="0" name="plans[{{ $i }}][sort_order]" value="{{ $plan['sort_order'] ?? $i }}" class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 outline-none focus:border-orange-400">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- MEDIA --}}
        <section class="rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm md:p-6">
            <div>
                <p class="text-xs font-black uppercase tracking-wider text-orange-600">Step 3</p>
                <h2 class="mt-1 text-xl font-black text-gray-900">Photos & Videos</h2>
                <p class="mt-1 text-sm text-gray-500">Upload from your computer or paste a YouTube, Vimeo or direct video link.</p>
            </div>

            <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="rounded-3xl border-2 border-dashed border-orange-200 bg-orange-50 p-5">
                    <div class="flex items-center gap-3">
                        <div class="grid h-11 w-11 place-items-center rounded-2xl bg-white text-2xl shadow-sm">🖼️</div>
                        <div>
                            <label for="gallery-photos" class="block font-black text-gray-800">Gallery Photos</label>
                            <p class="text-xs font-semibold text-gray-500">JPG, PNG, WEBP · max 8 MB each · up to 20</p>
                        </div>
                    </div>
                    <input id="gallery-photos" type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple class="mt-4 block w-full text-sm">
                    <div id="photo-selection-summary" class="mt-3 hidden rounded-2xl bg-white px-3 py-2 text-xs font-bold text-gray-600"></div>
                </div>

                <div class="rounded-3xl border-2 border-dashed border-blue-200 bg-blue-50 p-5">
                    <div class="flex items-center gap-3">
                        <div class="grid h-11 w-11 place-items-center rounded-2xl bg-white text-2xl shadow-sm">🎬</div>
                        <div>
                            <label for="video-files" class="block font-black text-gray-800">Video Files</label>
                            <p class="text-xs font-semibold text-gray-500">MP4, MOV, WEBM, M4V · max 50 MB each · up to 5</p>
                        </div>
                    </div>
                    <input id="video-files" type="file" name="videos[]" accept="video/mp4,video/webm,video/quicktime,.m4v" multiple class="mt-4 block w-full text-sm">
                    <div id="video-selection-summary" class="mt-3 hidden rounded-2xl bg-white px-3 py-2 text-xs font-bold text-gray-600"></div>
                </div>
            </div>

            <div id="new-media-preview" class="mt-4 hidden">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="font-black text-gray-900">Preview before saving</h3>
                    <span class="text-xs font-bold text-gray-500">These files are not uploaded until you press Save.</span>
                </div>
                <div id="new-media-preview-grid" class="grid grid-cols-2 gap-3 md:grid-cols-4"></div>
            </div>

            <div class="mt-6 rounded-3xl border border-blue-100 bg-blue-50/60 p-4 md:p-5">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h3 class="font-black text-gray-900">Add Video by URL</h3>
                        <p class="mt-1 text-sm font-medium text-gray-600">Recommended for long videos. Paste a YouTube, YouTube Shorts, Vimeo, or direct MP4/WebM link.</p>
                    </div>
                    <button type="button" id="add-video-url" class="shrink-0 rounded-xl bg-white px-4 py-2 text-sm font-black text-blue-700 shadow-sm">+ Add URL</button>
                </div>

                <div id="video-url-wrap" class="mt-4 space-y-3">
                    @foreach((array) old('video_urls', ['']) as $videoUrl)
                        <div class="video-url-row rounded-2xl border border-blue-100 bg-white p-3">
                            <div class="flex gap-2">
                                <input type="url" name="video_urls[]" value="{{ $videoUrl }}" placeholder="https://www.youtube.com/watch?v=..." class="video-url-input flex-1 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-100">
                                <button type="button" class="remove-video-url rounded-xl border border-red-200 bg-red-50 px-4 font-black text-red-700">×</button>
                            </div>
                            <div class="video-url-preview mt-3 hidden"></div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-3 grid gap-2 text-xs font-semibold text-gray-600 sm:grid-cols-3">
                    <div class="rounded-xl bg-white px-3 py-2">✓ YouTube watch / Shorts / youtu.be</div>
                    <div class="rounded-xl bg-white px-3 py-2">✓ Vimeo links</div>
                    <div class="rounded-xl bg-white px-3 py-2">✓ Direct .mp4 / .webm links</div>
                </div>
            </div>

            @if($eventMaster->exists && $eventMaster->media->count())
                <div class="mt-6">
                    <div class="mb-3 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <h3 class="font-black text-gray-900">Saved Media</h3>
                            <p class="text-sm text-gray-500">Check “Remove on Save” only for items you want to delete.</p>
                        </div>
                        <p class="text-xs font-black text-gray-400">{{ $eventMaster->media->count() }} item(s)</p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($eventMaster->media as $media)
                            <div class="overflow-hidden rounded-3xl border border-gray-200 bg-gray-50">
                                <div class="relative aspect-video bg-gray-900">
                                    @if($media->media_type === 'photo')
                                        <img src="{{ $media->media_url }}" class="h-full w-full object-cover" alt="{{ $media->title ?: 'Event photo' }}">
                                    @elseif($media->source_type === 'upload' || $media->is_direct_video)
                                        <video src="{{ $media->media_url }}" class="h-full w-full object-cover" controls preload="metadata"></video>
                                    @elseif($media->embed_url)
                                        <iframe
                                            src="{{ $media->embed_url }}"
                                            class="h-full w-full"
                                            title="{{ $media->title ?: 'External event video' }}"
                                            loading="lazy"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                            allowfullscreen
                                        ></iframe>
                                    @else
                                        <div class="grid h-full place-items-center p-4 text-center">
                                            <div>
                                                <div class="text-4xl">🔗</div>
                                                <p class="mt-2 text-xs font-black text-white">External video link</p>
                                                <a href="{{ $media->media_url }}" target="_blank" rel="noopener noreferrer" class="mt-2 inline-flex rounded-xl bg-white/10 px-3 py-2 text-xs font-black text-white">Open Link</a>
                                            </div>
                                        </div>
                                    @endif

                                    <span class="absolute left-2 top-2 rounded-full bg-black/70 px-2.5 py-1 text-[10px] font-black uppercase text-white">
                                        {{ str_replace('_', ' ', $media->source_type) }}
                                    </span>
                                </div>

                                <div class="p-3">
                                    <p class="truncate text-sm font-black text-gray-800">{{ $media->title ?: ucfirst($media->media_type) . ' #' . $media->id }}</p>

                                    @if($media->external_url)
                                        <p class="mt-1 truncate text-[11px] font-semibold text-blue-600">{{ $media->external_url }}</p>
                                    @endif

                                    <label class="mt-3 flex cursor-pointer items-center justify-between rounded-xl border border-red-100 bg-red-50 px-3 py-2 text-xs font-black text-red-700">
                                        <span>Remove on Save</span>
                                        <input type="checkbox" name="remove_media[]" value="{{ $media->id }}" class="h-4 w-4 rounded border-red-300">
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>
    </div>

    <aside class="space-y-6">
        {{-- COVER IMAGE --}}
        <section class="rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm">
            <div>
                <p class="text-xs font-black uppercase tracking-wider text-orange-600">Main Image</p>
                <h2 class="mt-1 text-xl font-black text-gray-900">Cover Image</h2>
                <p class="mt-1 text-xs font-semibold text-gray-500">Recommended 1600 × 900 landscape.</p>
            </div>

            <div id="cover-preview-wrap" class="mt-4 {{ $eventMaster->cover_image_url ? '' : 'hidden' }}">
                <img id="cover-preview-image" src="{{ $eventMaster->cover_image_url ?: '' }}" class="h-56 w-full rounded-3xl object-cover" alt="Cover preview">
            </div>

            @if($eventMaster->cover_image_url)
                <label class="mt-3 flex cursor-pointer items-center gap-2 rounded-xl bg-red-50 px-3 py-2 text-sm font-bold text-red-700">
                    <input type="checkbox" name="remove_cover" value="1"> Remove current cover when saving
                </label>
            @endif

            <label for="cover-image-input" class="mt-4 block cursor-pointer rounded-2xl border-2 border-dashed border-orange-200 bg-orange-50 p-4 text-center">
                <span class="block text-2xl">📷</span>
                <span class="mt-1 block text-sm font-black text-orange-700">Choose Cover Photo</span>
                <span class="block text-xs font-semibold text-gray-500">JPG, PNG or WEBP · max 8 MB</span>
            </label>
            <input id="cover-image-input" type="file" name="cover_image" accept="image/jpeg,image/png,image/webp" class="sr-only">
            <p id="cover-file-name" class="mt-2 truncate text-center text-xs font-bold text-gray-500"></p>
        </section>

        {{-- PUBLISHING --}}
        <section class="rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm">
            <div>
                <p class="text-xs font-black uppercase tracking-wider text-orange-600">Step 4</p>
                <h2 class="mt-1 text-xl font-black text-gray-900">Publishing</h2>
            </div>

            <div class="mt-4 space-y-4">
                <div>
                    <label class="mb-2 block text-sm font-black text-gray-700">Status *</label>
                    <select name="status" required class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 outline-none focus:border-orange-400">
                        <option value="active" @selected(old('status', $eventMaster->status ?: 'active') === 'active')>Active — visible to users</option>
                        <option value="inactive" @selected(old('status', $eventMaster->status) === 'inactive')>Inactive — hidden from users</option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-black text-gray-700">Sort Order</label>
                    <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $eventMaster->sort_order ?? 0) }}" class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 outline-none focus:border-orange-400">
                    <p class="mt-1 text-xs font-semibold text-gray-500">Lower number appears first. Example: 0, 1, 2.</p>
                </div>
            </div>
        </section>

        {{-- UPLOAD HELP --}}
        <section class="rounded-[2rem] border border-blue-100 bg-blue-50 p-5 shadow-sm">
            <h2 class="text-lg font-black text-blue-900">Media Upload Help</h2>
            <div class="mt-3 space-y-3 text-sm font-semibold leading-6 text-blue-900/80">
                <p>1. Use <strong>photos</strong> for stage, mandap, entrance and decoration samples.</p>
                <p>2. For short clips, upload MP4. For long videos, use a <strong>YouTube/Vimeo URL</strong>.</p>
                <p>3. If a large file fails before Laravel shows an error, increase PHP <code>upload_max_filesize</code> and <code>post_max_size</code>.</p>
                <p>4. Run <code>php artisan storage:link</code> once so uploaded files are publicly visible.</p>
            </div>
        </section>
    </aside>
</div>

<script>
(function () {
    const plansWrap = document.getElementById('plans-wrap');
    const addPlan = document.getElementById('add-plan');
    const form = document.getElementById('event-master-form');
    const submitButton = document.getElementById('event-submit-button');

    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function humanSize(bytes) {
        const units = ['B', 'KB', 'MB', 'GB'];
        let value = Number(bytes || 0);
        let index = 0;

        while (value >= 1024 && index < units.length - 1) {
            value /= 1024;
            index++;
        }

        return `${value.toFixed(index === 0 ? 0 : 1)} ${units[index]}`;
    }

    function reindexPlans() {
        if (!plansWrap) return;

        [...plansWrap.querySelectorAll('.plan-row')].forEach((row, index) => {
            const number = row.querySelector('.plan-number');
            if (number) number.textContent = index + 1;

            row.querySelectorAll('[data-field]').forEach((input) => {
                input.name = `plans[${index}][${input.dataset.field}]`;
            });
        });
    }

    addPlan?.addEventListener('click', function () {
        const index = plansWrap.querySelectorAll('.plan-row').length;
        const row = document.createElement('div');
        row.className = 'plan-row rounded-3xl border border-orange-100 bg-orange-50/50 p-4';
        row.innerHTML = `
            <input type="hidden" data-field="id" name="plans[${index}][id]" value="">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="font-black text-gray-800">Plan <span class="plan-number">${index + 1}</span></p>
                    <p class="text-xs font-semibold text-gray-500">Leave plan name empty if you do not want to save this row.</p>
                </div>
                <button type="button" class="remove-plan rounded-xl bg-red-50 px-3 py-1.5 text-xs font-black text-red-700">Remove</button>
            </div>
            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div><label class="mb-1 block text-xs font-black uppercase text-gray-500">Plan Name</label><input data-field="name" name="plans[${index}][name]" placeholder="Premium Plan" class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 outline-none focus:border-orange-400"></div>
                <div><label class="mb-1 block text-xs font-black uppercase text-gray-500">Price</label><input data-field="price" type="number" step="0.01" min="0" name="plans[${index}][price]" placeholder="50000" class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 outline-none focus:border-orange-400"></div>
                <div class="md:col-span-2"><label class="mb-1 block text-xs font-black uppercase text-gray-500">Plan Description</label><textarea data-field="description" name="plans[${index}][description]" rows="3" placeholder="What is included in this plan?" class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 outline-none focus:border-orange-400"></textarea></div>
                <div class="md:col-span-2"><label class="mb-1 block text-xs font-black uppercase text-gray-500">Features — one per line</label><textarea data-field="features_text" name="plans[${index}][features_text]" rows="5" placeholder="Fresh flower stage decoration\nWelcome gate\nCar decoration" class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 outline-none focus:border-orange-400"></textarea></div>
                <div><label class="mb-1 block text-xs font-black uppercase text-gray-500">Status</label><select data-field="status" name="plans[${index}][status]" class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 outline-none focus:border-orange-400"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                <div><label class="mb-1 block text-xs font-black uppercase text-gray-500">Sort Order</label><input data-field="sort_order" type="number" min="0" name="plans[${index}][sort_order]" value="${index}" class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 outline-none focus:border-orange-400"></div>
            </div>`;
        plansWrap.appendChild(row);
        reindexPlans();
    });

    plansWrap?.addEventListener('click', function (event) {
        if (event.target.classList.contains('remove-plan')) {
            event.target.closest('.plan-row')?.remove();
            reindexPlans();
        }
    });

    const shortDescription = document.getElementById('short-description');
    const shortDescriptionCount = document.getElementById('short-description-count');

    function updateShortCount() {
        if (shortDescriptionCount) {
            shortDescriptionCount.textContent = shortDescription?.value.length ?? 0;
        }
    }

    shortDescription?.addEventListener('input', updateShortCount);
    updateShortCount();

    const coverInput = document.getElementById('cover-image-input');
    const coverPreviewWrap = document.getElementById('cover-preview-wrap');
    const coverPreviewImage = document.getElementById('cover-preview-image');
    const coverFileName = document.getElementById('cover-file-name');

    coverInput?.addEventListener('change', function () {
        const file = this.files?.[0];
        if (!file) return;

        if (coverFileName) coverFileName.textContent = `${file.name} · ${humanSize(file.size)}`;
        if (coverPreviewImage) coverPreviewImage.src = URL.createObjectURL(file);
        coverPreviewWrap?.classList.remove('hidden');
    });

    const photosInput = document.getElementById('gallery-photos');
    const videosInput = document.getElementById('video-files');
    const previewSection = document.getElementById('new-media-preview');
    const previewGrid = document.getElementById('new-media-preview-grid');
    const photoSummary = document.getElementById('photo-selection-summary');
    const videoSummary = document.getElementById('video-selection-summary');

    function renderSelectedMedia() {
        if (!previewGrid) return;

        previewGrid.innerHTML = '';
        const photos = [...(photosInput?.files || [])];
        const videos = [...(videosInput?.files || [])];

        if (photoSummary) {
            if (photos.length) {
                const total = photos.reduce((sum, file) => sum + file.size, 0);
                photoSummary.textContent = `${photos.length} photo(s) selected · ${humanSize(total)} total`;
                photoSummary.classList.remove('hidden');
            } else {
                photoSummary.classList.add('hidden');
            }
        }

        if (videoSummary) {
            if (videos.length) {
                const total = videos.reduce((sum, file) => sum + file.size, 0);
                videoSummary.textContent = `${videos.length} video(s) selected · ${humanSize(total)} total`;
                videoSummary.classList.remove('hidden');
            } else {
                videoSummary.classList.add('hidden');
            }
        }

        photos.forEach((file) => {
            const card = document.createElement('div');
            card.className = 'overflow-hidden rounded-2xl border border-orange-100 bg-white';
            card.innerHTML = `
                <img src="${URL.createObjectURL(file)}" class="aspect-video w-full object-cover" alt="Selected photo preview">
                <div class="p-2"><p class="truncate text-xs font-black text-gray-700">${escapeHtml(file.name)}</p><p class="text-[10px] font-semibold text-gray-400">${humanSize(file.size)}</p></div>`;
            previewGrid.appendChild(card);
        });

        videos.forEach((file) => {
            const card = document.createElement('div');
            card.className = 'overflow-hidden rounded-2xl border border-blue-100 bg-white';
            card.innerHTML = `
                <video src="${URL.createObjectURL(file)}" class="aspect-video w-full bg-black object-cover" controls preload="metadata"></video>
                <div class="p-2"><p class="truncate text-xs font-black text-gray-700">${escapeHtml(file.name)}</p><p class="text-[10px] font-semibold text-gray-400">${humanSize(file.size)}</p></div>`;
            previewGrid.appendChild(card);
        });

        previewSection?.classList.toggle('hidden', photos.length + videos.length === 0);
    }

    photosInput?.addEventListener('change', renderSelectedMedia);
    videosInput?.addEventListener('change', renderSelectedMedia);

    const videoWrap = document.getElementById('video-url-wrap');

    function youtubeId(url) {
        try {
            const u = new URL(url);
            const host = u.hostname.replace(/^www\./, '').toLowerCase();
            if (host === 'youtu.be') return u.pathname.split('/').filter(Boolean)[0] || null;
            if (!['youtube.com', 'm.youtube.com', 'music.youtube.com'].includes(host)) return null;
            if (u.searchParams.get('v')) return u.searchParams.get('v');
            const parts = u.pathname.split('/').filter(Boolean);
            if (['embed', 'shorts', 'live'].includes(parts[0]) && parts[1]) return parts[1];
        } catch (_) {}
        return null;
    }

    function vimeoId(url) {
        try {
            const u = new URL(url);
            const host = u.hostname.replace(/^www\./, '').toLowerCase();
            if (!['vimeo.com', 'player.vimeo.com'].includes(host)) return null;
            const parts = u.pathname.split('/').filter(Boolean).reverse();
            return parts.find((part) => /^\d+$/.test(part)) || null;
        } catch (_) {}
        return null;
    }

    function isDirectVideoUrl(url) {
        try {
            const pathname = new URL(url).pathname.toLowerCase();
            return ['.mp4', '.webm', '.mov', '.m4v', '.ogg'].some((ext) => pathname.endsWith(ext));
        } catch (_) {
            return false;
        }
    }

    function updateUrlPreview(row) {
        const input = row.querySelector('.video-url-input');
        const preview = row.querySelector('.video-url-preview');
        if (!input || !preview) return;

        const value = input.value.trim();
        preview.innerHTML = '';

        if (!value) {
            preview.classList.add('hidden');
            return;
        }

        const yid = youtubeId(value);
        const vid = vimeoId(value);

        if (yid) {
            preview.innerHTML = `
                <div class="flex items-center gap-3 rounded-xl bg-red-50 p-3">
                    <img src="https://img.youtube.com/vi/${encodeURIComponent(yid)}/mqdefault.jpg" class="h-16 w-24 rounded-lg object-cover" alt="YouTube preview">
                    <div><p class="text-xs font-black text-red-700">YouTube link detected</p><p class="mt-1 text-[11px] font-semibold text-gray-500">This video will be embedded in the Event Master page.</p></div>
                </div>`;
        } else if (vid) {
            preview.innerHTML = '<div class="rounded-xl bg-blue-50 p-3 text-xs font-black text-blue-700">Vimeo link detected. It will be embedded automatically after saving.</div>';
        } else if (isDirectVideoUrl(value)) {
            preview.innerHTML = '<div class="rounded-xl bg-green-50 p-3 text-xs font-black text-green-700">Direct video file link detected.</div>';
        } else {
            preview.innerHTML = '<div class="rounded-xl bg-yellow-50 p-3 text-xs font-black text-yellow-700">External URL detected. It will be saved as a clickable video link if it cannot be embedded.</div>';
        }

        preview.classList.remove('hidden');
    }

    document.getElementById('add-video-url')?.addEventListener('click', function () {
        const row = document.createElement('div');
        row.className = 'video-url-row rounded-2xl border border-blue-100 bg-white p-3';
        row.innerHTML = `
            <div class="flex gap-2">
                <input type="url" name="video_urls[]" placeholder="https://www.youtube.com/watch?v=..." class="video-url-input flex-1 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-100">
                <button type="button" class="remove-video-url rounded-xl border border-red-200 bg-red-50 px-4 font-black text-red-700">×</button>
            </div>
            <div class="video-url-preview mt-3 hidden"></div>`;
        videoWrap.appendChild(row);
        row.querySelector('input')?.focus();
    });

    videoWrap?.addEventListener('click', function (event) {
        if (!event.target.classList.contains('remove-video-url')) return;

        const rows = videoWrap.querySelectorAll('.video-url-row');
        const row = event.target.closest('.video-url-row');

        if (rows.length > 1) {
            row?.remove();
        } else if (row) {
            const input = row.querySelector('input');
            const preview = row.querySelector('.video-url-preview');
            if (input) input.value = '';
            if (preview) {
                preview.innerHTML = '';
                preview.classList.add('hidden');
            }
        }
    });

    videoWrap?.addEventListener('input', function (event) {
        if (event.target.classList.contains('video-url-input')) {
            updateUrlPreview(event.target.closest('.video-url-row'));
        }
    });

    [...(videoWrap?.querySelectorAll('.video-url-row') || [])].forEach(updateUrlPreview);

    form?.addEventListener('submit', function () {
        if (!submitButton) return;
        submitButton.disabled = true;
        submitButton.dataset.originalText = submitButton.textContent.trim();
        submitButton.textContent = 'Saving... Please wait';
    });
})();
</script>
