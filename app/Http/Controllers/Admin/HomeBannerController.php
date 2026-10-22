<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeBanner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class HomeBannerController extends Controller
{
    public function index(): View
    {
        $banners = HomeBanner::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(20);

        return view('admin.home-banners.index', compact('banners'));
    }

    public function create(): View
    {
        return view('admin.home-banners.create', [
            'banner' => new HomeBanner([
                'is_active' => true,
                'sort_order' => 0,
            ]),
            'targets' => HomeBanner::TARGETS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, true);
        $path = $request->file('image')->store('home-banners', 'public');
        unset($data['image']);
        $data['image_path'] = $path;
        $data['is_active'] = $request->boolean('is_active');

        try {
            HomeBanner::create($data);
        } catch (Throwable $e) {
            Storage::disk('public')->delete($path);
            throw $e;
        }

        return redirect()->route('admin.home-banners.index')
            ->with('success', 'Banner created. Active banners appear in the app automatically.');
    }

    public function edit(HomeBanner $homeBanner): View
    {
        return view('admin.home-banners.edit', [
            'banner' => $homeBanner,
            'targets' => HomeBanner::TARGETS,
        ]);
    }

    public function update(Request $request, HomeBanner $homeBanner): RedirectResponse
    {
        $data = $this->validated($request, false);
        unset($data['image']);
        $data['is_active'] = $request->boolean('is_active');
        $oldPath = $homeBanner->image_path;
        $newPath = null;

        if ($request->hasFile('image')) {
            $newPath = $request->file('image')->store('home-banners', 'public');
            $data['image_path'] = $newPath;
        }

        try {
            $homeBanner->update($data);
        } catch (Throwable $e) {
            if ($newPath) {
                Storage::disk('public')->delete($newPath);
            }
            throw $e;
        }

        if ($newPath && $oldPath && $oldPath !== $newPath) {
            Storage::disk('public')->delete($oldPath);
        }

        return redirect()->route('admin.home-banners.index')
            ->with('success', 'Banner updated successfully.');
    }

    public function destroy(HomeBanner $homeBanner): RedirectResponse
    {
        $path = $homeBanner->image_path;
        $homeBanner->delete();

        if ($path) {
            Storage::disk('public')->delete($path);
        }

        return redirect()->route('admin.home-banners.index')
            ->with('success', 'Banner deleted successfully.');
    }

    private function validated(Request $request, bool $creating): array
    {
        $endDateRules = ['nullable', 'date'];
        if ($request->filled('starts_at')) {
            $endDateRules[] = 'after_or_equal:starts_at';
        }

        return $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'image' => [
                $creating ? 'required' : 'nullable',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:5120',
            ],
            'target_screen' => ['nullable', Rule::in(array_keys(HomeBanner::TARGETS))],
            'sort_order' => ['required', 'integer', 'between:0,9999'],
            'is_active' => ['required', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => $endDateRules,
        ]);
    }
}
