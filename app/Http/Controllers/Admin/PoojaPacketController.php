<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FlowerProduct;
use App\Models\PoojaPacket;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PoojaPacketController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->get('search', ''));
        $status = $request->get('status', '');

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

        $packets = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'total' => PoojaPacket::count(),
            'active' => PoojaPacket::where('status', 'Active')->count(),
            'inactive' => PoojaPacket::where('status', 'Inactive')->count(),
        ];

        return view('admin.pooja-packets.index', compact('packets', 'stats', 'search', 'status'));
    }

    public function create()
    {
        $packet = new PoojaPacket();
        $flowers = $this->flowersForForm($packet);

        return view('admin.pooja-packets.form', compact('packet', 'flowers'));
    }

    public function store(Request $request)
    {
        PoojaPacket::create($this->validated($request));

        return redirect()
            ->route('admin.pooja-packets.index')
            ->with('success', 'Pooja packet added successfully.');
    }

    public function edit(PoojaPacket $poojaPacket)
    {
        $packet = $poojaPacket;
        $flowers = $this->flowersForForm($packet);

        return view('admin.pooja-packets.form', compact('packet', 'flowers'));
    }

    public function update(Request $request, PoojaPacket $poojaPacket)
    {
        $poojaPacket->update($this->validated($request));

        return redirect()
            ->route('admin.pooja-packets.index')
            ->with('success', 'Pooja packet updated successfully.');
    }

    public function destroy(PoojaPacket $poojaPacket)
    {
        $poojaPacket->delete();

        return back()->with('success', 'Pooja packet deleted successfully.');
    }

    private function flowersForForm(PoojaPacket $packet)
    {
        $selectedFlowerIds = collect($packet->included_flowers ?? [])
            ->map(fn ($item) => is_array($item) ? ($item['flower_id'] ?? null) : null)
            ->filter()
            ->unique()
            ->values();

        return FlowerProduct::query()
            ->where(function ($query) use ($selectedFlowerIds) {
                $query->where('status', 'Active');

                if ($selectedFlowerIds->isNotEmpty()) {
                    $query->orWhereIn('id', $selectedFlowerIds);
                }
            })
            ->orderBy('flower_name')
            ->get();
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'packet_name' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'monthly_price' => ['required', 'numeric', 'min:0'],
            'weekly_price' => ['nullable', 'numeric', 'min:0'],
            'daily_quantity' => ['nullable', 'string', 'max:100'],
            'package_type' => ['nullable', 'string', 'max:100'],
            'duration_months' => ['required', 'integer', 'in:1,2,3,6,12'],
            'status' => ['required', 'in:Active,Inactive'],

            'flower_ids' => ['required', 'array', 'min:1'],
            'flower_ids.*' => ['nullable', 'integer', 'exists:flower_products,id'],
            'flower_units' => ['nullable', 'array'],
            'flower_units.*' => ['nullable', 'string', 'max:50'],
            'quantities' => ['nullable', 'array'],
            'quantities.*' => ['nullable', 'numeric', 'min:0'],
            'mrp_prices' => ['nullable', 'array'],
            'mrp_prices.*' => ['nullable', 'numeric', 'min:0'],
            'sale_prices' => ['nullable', 'array'],
            'sale_prices.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        $flowerIds = $request->input('flower_ids', []);
        $units = $request->input('flower_units', []);
        $quantities = $request->input('quantities', []);
        $mrpPrices = $request->input('mrp_prices', []);
        $salePrices = $request->input('sale_prices', []);

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
            $salePrice = $salePrices[$index] ?? null;

            if ($quantity === null || $quantity === '' || $salePrice === null || $salePrice === '') {
                throw ValidationException::withMessages([
                    'flower_ids' => 'Quantity and sale price are required for every selected flower.',
                ]);
            }

            $includedFlowers[] = [
                'flower_id' => (int) $flower->id,
                'flower_name' => $flower->flower_name,
                'unit' => $units[$index] ?? $flower->unit,
                'quantity' => (float) $quantity,
                'mrp_price' => (float) ($mrpPrices[$index] ?? 0),
                'sale_price' => (float) $salePrice,
            ];
        }

        if (empty($includedFlowers)) {
            throw ValidationException::withMessages([
                'flower_ids' => 'Please select at least one valid flower.',
            ]);
        }

        $data['included_flowers'] = $includedFlowers;

        unset(
            $data['flower_ids'],
            $data['flower_units'],
            $data['quantities'],
            $data['mrp_prices'],
            $data['sale_prices']
        );

        return $data;
    }
}