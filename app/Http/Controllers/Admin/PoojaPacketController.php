<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FlowerProduct;
use App\Models\PoojaPacket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PoojaPacketController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->get('search', ''));
        $status = $request->get('status', '');
        $packageType = $request->get('package_type', '');

        $query = PoojaPacket::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('packet_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('package_type', 'like', "%{$search}%");
            });
        }

        if (in_array($status, ['Active', 'Inactive'])) {
            $query->where('status', $status);
        }

        if (in_array($packageType, ['Monthly', 'Three Month', 'Six Month', 'One Year'])) {
            $query->where('package_type', $packageType);
        }

        $packets = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'total' => PoojaPacket::count(),
            'active' => PoojaPacket::where('status', 'Active')->count(),
            'inactive' => PoojaPacket::where('status', 'Inactive')->count(),
        ];

        return view('admin.pooja-packets.index', compact(
            'packets',
            'stats',
            'search',
            'status',
            'packageType'
        ));
    }

    public function create()
    {
        $packet = new PoojaPacket();

        $flowers = FlowerProduct::query()
            ->where('status', 'Active')
            ->orderBy('flower_name')
            ->get();

        return view('admin.pooja-packets.form', compact('packet', 'flowers'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['image'] = $this->uploadImage($request);

        PoojaPacket::create($data);

        return redirect()
            ->route('admin.pooja-packets.index')
            ->with('success', 'Pooja packet added successfully.');
    }

    public function edit(PoojaPacket $poojaPacket)
    {
        $packet = $poojaPacket;

        $selectedFlowerIds = collect($packet->included_flowers ?? [])
            ->map(function ($item) {
                return is_array($item) ? ($item['flower_id'] ?? null) : null;
            })
            ->filter()
            ->unique()
            ->values();

        $flowers = FlowerProduct::query()
            ->where(function ($query) use ($selectedFlowerIds) {
                $query->where('status', 'Active');

                if ($selectedFlowerIds->isNotEmpty()) {
                    $query->orWhereIn('id', $selectedFlowerIds);
                }
            })
            ->orderBy('flower_name')
            ->get();

        return view('admin.pooja-packets.form', compact('packet', 'flowers'));
    }

    public function update(Request $request, PoojaPacket $poojaPacket)
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            $data['image'] = $this->uploadImage($request, $poojaPacket);
        }

        $poojaPacket->update($data);

        return redirect()
            ->route('admin.pooja-packets.index')
            ->with('success', 'Pooja packet updated successfully.');
    }

    public function destroy(PoojaPacket $poojaPacket)
    {
        if ($poojaPacket->image && File::exists(public_path($poojaPacket->image))) {
            File::delete(public_path($poojaPacket->image));
        }

        $poojaPacket->delete();

        return back()->with('success', 'Pooja packet deleted successfully.');
    }

    private function validated(Request $request)
    {
        $data = $request->validate([
            'packet_name' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'description' => ['nullable', 'string'],

            'package_type' => ['required', 'in:Monthly,Three Month,Six Month,One Year'],
            'mrp_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:Active,Inactive'],

            'flower_ids' => ['required', 'array', 'min:1'],
            'flower_ids.*' => ['nullable', 'integer', 'exists:flower_products,id'],

            'flower_units' => ['nullable', 'array'],
            'flower_units.*' => ['nullable', 'string', 'max:50'],

            'quantities' => ['nullable', 'array'],
            'quantities.*' => ['nullable', 'numeric', 'min:0'],

            'prices' => ['nullable', 'array'],
            'prices.*' => ['nullable', 'numeric', 'min:0'],

            'flower_mrp_prices' => ['nullable', 'array'],
            'flower_mrp_prices.*' => ['nullable', 'numeric', 'min:0'],

            'flower_sale_prices' => ['nullable', 'array'],
            'flower_sale_prices.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        $durationMap = [
            'Monthly' => 1,
            'Three Month' => 3,
            'Six Month' => 6,
            'One Year' => 12,
        ];

        $flowerIds = $request->input('flower_ids', []);
        $units = $request->input('flower_units', []);
        $quantities = $request->input('quantities', []);
        $prices = $request->input('prices', []);
        $mrpPrices = $request->input('flower_mrp_prices', []);
        $salePrices = $request->input('flower_sale_prices', []);

        $validFlowerIds = array_values(array_filter($flowerIds));

        if (empty($validFlowerIds)) {
            throw ValidationException::withMessages([
                'flower_ids' => 'Please select at least one flower.',
            ]);
        }

        $flowers = FlowerProduct::whereIn('id', $validFlowerIds)
            ->get()
            ->keyBy('id');

        $includedFlowers = [];

        foreach ($flowerIds as $index => $flowerId) {
            if (!$flowerId) {
                continue;
            }

            $flower = $flowers->get($flowerId);

            if (!$flower) {
                continue;
            }

            $quantity = $quantities[$index] ?? null;
            $unit = $units[$index] ?? null;
            $price = $prices[$index] ?? null;
            $flowerMrp = $mrpPrices[$index] ?? null;
            $flowerSale = $salePrices[$index] ?? null;

            if ($quantity === null || $quantity === '' || $unit === null || $unit === '' || $price === null || $price === '') {
                throw ValidationException::withMessages([
                    'flower_ids' => 'Flower unit, quantity and price are required for every selected flower.',
                ]);
            }

            $includedFlowers[] = [
                'flower_id' => (int) $flower->id,
                'flower_name' => $flower->flower_name,
                'unit' => $unit,
                'quantity' => (float) $quantity,
                'price' => (float) $price,
                'mrp_price' => (float) ($flowerMrp ?: $price),
                'sale_price' => (float) ($flowerSale ?: $price),
            ];
        }

        if (empty($includedFlowers)) {
            throw ValidationException::withMessages([
                'flower_ids' => 'Please select at least one valid flower.',
            ]);
        }

        $data['included_flowers'] = $includedFlowers;
        $data['duration_months'] = $durationMap[$data['package_type']] ?? 1;

        // Keep old monthly_price column safe for existing mobile app/API.
        $data['monthly_price'] = $data['sale_price'];
        $data['weekly_price'] = null;
        $data['daily_quantity'] = null;

        unset(
            $data['image'],
            $data['flower_ids'],
            $data['flower_units'],
            $data['quantities'],
            $data['prices'],
            $data['flower_mrp_prices'],
            $data['flower_sale_prices']
        );

        return $data;
    }

    private function uploadImage(Request $request, PoojaPacket $packet = null)
    {
        if (!$request->hasFile('image')) {
            return $packet ? $packet->image : null;
        }

        if ($packet && $packet->image && File::exists(public_path($packet->image))) {
            File::delete(public_path($packet->image));
        }

        $folder = public_path('uploads/pooja-packets');

        if (!File::exists($folder)) {
            File::makeDirectory($folder, 0755, true);
        }

        $file = $request->file('image');
        $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();

        $file->move($folder, $fileName);

        return 'uploads/pooja-packets/' . $fileName;
    }
}