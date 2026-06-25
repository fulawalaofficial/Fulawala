<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FlowerProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FlowerProductController extends Controller
{
    public function index(Request $request)
    {
        $query = FlowerProduct::query()->latest();

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('flower_name', 'like', '%' . $search . '%')
                    ->orWhere('category', 'like', '%' . $search . '%')
                    ->orWhere('unit', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('stock_status')) {
            $query->where('stock_status', $request->stock_status);
        }

        return view('admin.flowers.index', [
            'flowers' => $query->paginate(12)->withQueryString(),
            'totalFlowers' => FlowerProduct::count(),
            'activeFlowers' => FlowerProduct::where('status', 'Active')->count(),
            'inactiveFlowers' => FlowerProduct::where('status', 'Inactive')->count(),
            'outOfStockFlowers' => FlowerProduct::where('stock_status', 'Out of Stock')->count(),
        ]);
    }

    public function create()
    {
        return view('admin.flowers.form', [
            'flower' => new FlowerProduct(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('flowers', 'public');
        }

        FlowerProduct::create($data);

        return redirect()
            ->route('admin.flowers.index')
            ->with('success', 'Flower added successfully.');
    }

    public function edit(FlowerProduct $flower)
    {
        return view('admin.flowers.form', compact('flower'));
    }

    public function update(Request $request, FlowerProduct $flower)
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            if ($flower->image && Storage::disk('public')->exists($flower->image)) {
                Storage::disk('public')->delete($flower->image);
            }

            $data['image'] = $request->file('image')->store('flowers', 'public');
        } else {
            unset($data['image']);
        }

        $flower->update($data);

        return redirect()
            ->route('admin.flowers.index')
            ->with('success', 'Flower updated successfully.');
    }

    public function destroy(FlowerProduct $flower)
    {
        if ($flower->image && Storage::disk('public')->exists($flower->image)) {
            Storage::disk('public')->delete($flower->image);
        }

        $flower->delete();

        return back()->with('success', 'Flower deleted successfully.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'flower_name' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'category' => ['nullable', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'max:100'],
            'stock_status' => ['required', 'in:In Stock,Out of Stock'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:Active,Inactive'],
        ]);
    }
}