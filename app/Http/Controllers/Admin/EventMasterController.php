<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventMaster;
use App\Models\EventMedia;
use App\Models\EventPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EventMasterController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->get('search', ''));
        $status = trim((string) $request->get('status', ''));

        $events = EventMaster::query()
            ->withCount(['photos', 'videos', 'plans'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('short_description', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when(
                in_array($status, ['active', 'inactive'], true),
                fn ($q) => $q->where('status', $status)
            )
            ->orderBy('sort_order')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => EventMaster::count(),
            'active' => EventMaster::where('status', 'active')->count(),
            'inactive' => EventMaster::where('status', 'inactive')->count(),
            'plans' => EventPlan::count(),
        ];

        return view('admin.event-masters.index', compact(
            'events',
            'stats',
            'search',
            'status'
        ));
    }

    public function create(): View
    {
        return view('admin.event-masters.create', [
            'eventMaster' => new EventMaster(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateEvent($request);
        $eventData = $this->eventPayload($validated);
        $uploadedPaths = [];

        try {
            DB::transaction(function () use ($request, &$eventData, &$uploadedPaths): void {
                if ($request->hasFile('cover_image')) {
                    $path = $request->file('cover_image')->store('events/covers', 'public');
                    $uploadedPaths[] = $path;
                    $eventData['cover_image'] = $path;
                }

                $event = EventMaster::create($eventData);

                $this->saveMedia($request, $event, $uploadedPaths);
                $this->syncPlans($request, $event);
            });
        } catch (\Throwable $e) {
            $this->cleanupUploadedFiles($uploadedPaths);
            report($e);

            return back()
                ->withInput()
                ->with('error', 'Event could not be saved. Please check the files and try again.');
        }

        return redirect()
            ->route('admin.event-masters.index')
            ->with('success', 'Event master created successfully.');
    }

    public function edit(EventMaster $eventMaster): View
    {
        $eventMaster->load(['media', 'plans']);

        return view('admin.event-masters.edit', compact('eventMaster'));
    }

    public function update(Request $request, EventMaster $eventMaster): RedirectResponse
    {
        $validated = $this->validateEvent($request, $eventMaster->id);
        $eventData = $this->eventPayload($validated);
        $uploadedPaths = [];
        $filesToDeleteAfterCommit = [];

        try {
            DB::transaction(function () use (
                $request,
                $eventMaster,
                &$eventData,
                &$uploadedPaths,
                &$filesToDeleteAfterCommit
            ): void {
                if ($request->boolean('remove_cover') && $eventMaster->cover_image) {
                    $filesToDeleteAfterCommit[] = $eventMaster->cover_image;
                    $eventData['cover_image'] = null;
                }

                if ($request->hasFile('cover_image')) {
                    if ($eventMaster->cover_image) {
                        $filesToDeleteAfterCommit[] = $eventMaster->cover_image;
                    }

                    $path = $request->file('cover_image')->store('events/covers', 'public');
                    $uploadedPaths[] = $path;
                    $eventData['cover_image'] = $path;
                }

                $eventMaster->update($eventData);

                $removeIds = collect((array) $request->input('remove_media', []))
                    ->map(fn ($id) => (int) $id)
                    ->filter()
                    ->unique();

                if ($removeIds->isNotEmpty()) {
                    $mediaToRemove = $eventMaster->media()
                        ->whereIn('id', $removeIds->all())
                        ->get();

                    foreach ($mediaToRemove as $media) {
                        if ($media->path) {
                            $filesToDeleteAfterCommit[] = $media->path;
                        }
                        $media->delete();
                    }
                }

                $this->saveMedia($request, $eventMaster, $uploadedPaths);
                $this->syncPlans($request, $eventMaster);
            });
        } catch (\Throwable $e) {
            $this->cleanupUploadedFiles($uploadedPaths);
            report($e);

            return back()
                ->withInput()
                ->with('error', 'Event could not be updated. Please check the files and try again.');
        }

        $this->cleanupUploadedFiles(array_values(array_unique($filesToDeleteAfterCommit)));

        return redirect()
            ->route('admin.event-masters.edit', $eventMaster)
            ->with('success', 'Event master updated successfully.');
    }

    public function destroy(EventMaster $eventMaster): RedirectResponse
    {
        $eventMaster->load('media');

        $paths = collect([$eventMaster->cover_image])
            ->merge($eventMaster->media->pluck('path'))
            ->filter()
            ->unique()
            ->values()
            ->all();

        DB::transaction(function () use ($eventMaster): void {
            $eventMaster->delete();
        });

        $this->cleanupUploadedFiles($paths);

        return redirect()
            ->route('admin.event-masters.index')
            ->with('success', 'Event master deleted successfully.');
    }

    public function destroyMedia(EventMaster $eventMaster, EventMedia $media): RedirectResponse
    {
        abort_unless($media->event_master_id === $eventMaster->id, 404);

        $path = $media->path;
        $media->delete();

        if ($path) {
            Storage::disk('public')->delete($path);
        }

        return back()->with('success', 'Event media removed successfully.');
    }

    private function validateEvent(Request $request, ?int $eventId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => [
                'nullable',
                'string',
                'max:180',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('event_masters', 'slug')->ignore($eventId),
            ],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:10000'],
            'starting_price' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],

            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'remove_cover' => ['nullable', 'boolean'],

            'photos' => ['nullable', 'array', 'max:20'],
            'photos.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],

            'videos' => ['nullable', 'array', 'max:5'],
            'videos.*' => ['file', 'mimes:mp4,mov,webm,m4v', 'max:51200'],

            'video_urls' => ['nullable', 'array', 'max:10'],
            'video_urls.*' => [
                'nullable',
                'string',
                'max:1000',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $url = trim((string) $value);

                    if ($url === '') {
                        return;
                    }

                    if (!filter_var($url, FILTER_VALIDATE_URL)) {
                        $fail('Each video link must be a valid URL.');
                        return;
                    }

                    $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
                    if (!in_array($scheme, ['http', 'https'], true)) {
                        $fail('Video links must start with http:// or https://.');
                    }
                },
            ],

            'remove_media' => ['nullable', 'array'],
            'remove_media.*' => ['integer'],

            'plans' => ['nullable', 'array', 'max:20'],
            'plans.*.id' => ['nullable', 'integer'],
            'plans.*.name' => ['nullable', 'string', 'max:120'],
            'plans.*.price' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'plans.*.description' => ['nullable', 'string', 'max:3000'],
            'plans.*.features_text' => ['nullable', 'string', 'max:5000'],
            'plans.*.status' => ['nullable', Rule::in(['active', 'inactive'])],
            'plans.*.sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ], [
            'cover_image.max' => 'Cover image must be 8 MB or smaller.',
            'photos.*.max' => 'Each gallery photo must be 8 MB or smaller.',
            'videos.*.max' => 'Each video file must be 50 MB or smaller.',
            'videos.*.mimes' => 'Video must be MP4, MOV, WEBM or M4V.',
        ]);
    }

    private function eventPayload(array $validated): array
    {
        $payload = Arr::only($validated, [
            'name',
            'slug',
            'short_description',
            'description',
            'starting_price',
            'status',
            'sort_order',
        ]);

        $payload['slug'] = trim((string) ($payload['slug'] ?? '')) ?: null;
        $payload['short_description'] = trim((string) ($payload['short_description'] ?? '')) ?: null;
        $payload['description'] = trim((string) ($payload['description'] ?? '')) ?: null;
        $payload['starting_price'] = ($payload['starting_price'] ?? '') !== ''
            ? $payload['starting_price']
            : null;
        $payload['sort_order'] = (int) ($payload['sort_order'] ?? 0);

        return $payload;
    }

    private function saveMedia(Request $request, EventMaster $event, array &$uploadedPaths): void
    {
        $nextSort = ((int) $event->media()->max('sort_order')) + 1;

        foreach ((array) $request->file('photos', []) as $photo) {
            if (!$photo || !$photo->isValid()) {
                continue;
            }

            $path = $photo->store('events/photos', 'public');
            $uploadedPaths[] = $path;

            $event->media()->create([
                'media_type' => 'photo',
                'path' => $path,
                'title' => pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME),
                'sort_order' => $nextSort++,
            ]);
        }

        foreach ((array) $request->file('videos', []) as $video) {
            if (!$video || !$video->isValid()) {
                continue;
            }

            $path = $video->store('events/videos', 'public');
            $uploadedPaths[] = $path;

            $event->media()->create([
                'media_type' => 'video',
                'path' => $path,
                'title' => pathinfo($video->getClientOriginalName(), PATHINFO_FILENAME),
                'sort_order' => $nextSort++,
            ]);
        }

        foreach ((array) $request->input('video_urls', []) as $url) {
            $url = $this->normalizeExternalUrl((string) $url);

            if ($url === '') {
                continue;
            }

            $alreadyExists = $event->media()
                ->where('media_type', 'video')
                ->where('external_url', $url)
                ->exists();

            if ($alreadyExists) {
                continue;
            }

            $event->media()->create([
                'media_type' => 'video',
                'external_url' => $url,
                'title' => $this->externalVideoTitle($url),
                'sort_order' => $nextSort++,
            ]);
        }
    }

    private function syncPlans(Request $request, EventMaster $event): void
    {
        $submittedIds = [];

        foreach ((array) $request->input('plans', []) as $index => $planData) {
            $name = trim((string) ($planData['name'] ?? ''));

            if ($name === '') {
                continue;
            }

            $features = collect(
                preg_split('/\r\n|\r|\n/', (string) ($planData['features_text'] ?? ''))
            )
                ->map(fn ($item) => trim((string) $item))
                ->filter()
                ->values()
                ->all();

            $payload = [
                'name' => $name,
                'price' => ($planData['price'] ?? '') !== '' ? $planData['price'] : null,
                'description' => trim((string) ($planData['description'] ?? '')) ?: null,
                'features' => $features,
                'status' => $planData['status'] ?? 'active',
                'sort_order' => (int) ($planData['sort_order'] ?? $index),
            ];

            $planId = isset($planData['id']) ? (int) $planData['id'] : 0;

            if ($planId > 0) {
                $plan = $event->plans()->whereKey($planId)->first();

                if ($plan) {
                    $plan->update($payload);
                    $submittedIds[] = $plan->id;
                    continue;
                }
            }

            $plan = $event->plans()->create($payload);
            $submittedIds[] = $plan->id;
        }

        $deleteQuery = $event->plans();

        if ($submittedIds) {
            $deleteQuery->whereNotIn('id', $submittedIds);
        }

        $deleteQuery->delete();
    }

    private function normalizeExternalUrl(string $url): string
    {
        $url = trim($url);

        if ($url === '') {
            return '';
        }

        // Remove a trailing slash only; keep query parameters because some providers require them.
        return rtrim($url, '/');
    }

    private function externalVideoTitle(string $url): string
    {
        $host = preg_replace('/^www\./', '', strtolower((string) parse_url($url, PHP_URL_HOST)));

        return match (true) {
            str_contains((string) $host, 'youtube.com'),
            $host === 'youtu.be' => 'YouTube video',
            str_contains((string) $host, 'vimeo.com') => 'Vimeo video',
            default => 'External video',
        };
    }

    private function cleanupUploadedFiles(array $paths): void
    {
        foreach ($paths as $path) {
            if ($path) {
                Storage::disk('public')->delete($path);
            }
        }
    }
}
