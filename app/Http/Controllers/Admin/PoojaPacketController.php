<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FlowerProduct;
use App\Models\PoojaPacket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
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

        if ($status === 'Active' || $status === 'Inactive') {
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

        $flowers = $this->getFlowersForForm();

        return view('admin.pooja-packets.form', compact('packet', 'flowers'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            $data['image'] = $this->uploadImage($request);
        }

        PoojaPacket::create($data);

        return redirect()
            ->route('admin.pooja-packets.index')
            ->with('success', 'Pooja package added successfully.');
    }

    public function edit(PoojaPacket $poojaPacket)
    {
        $packet = $poojaPacket;

        $flowers = $this->getFlowersForForm();

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
            ->with('success', 'Pooja package updated successfully.');
    }

    public function destroy(PoojaPacket $poojaPacket)
    {
        if ($poojaPacket->image) {
            $oldImage = public_path($poojaPacket->image);

            if (file_exists($oldImage)) {
                unlink($oldImage);
            }
        }

        $poojaPacket->delete();

        return back()->with('success', 'Pooja package deleted successfully.');
    }

    private function getFlowersForForm()
    {
        $query = FlowerProduct::query();

        if (Schema::hasColumn('flower_products', 'status')) {
            $query->where('status', 'Active');
        }

        return $query->orderBy('flower_name')->get();
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
            'flower_ids.*' => ['nullable', 'integer'],

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

        $flowers = FlowerProduct::whereIn('id', $validFlowerIds)->get()->keyBy('id');

        $includedFlowers = [];

        foreach ($flowerIds as $index => $flowerId) {
            if (!$flowerId) {
                continue;
            }

            $flower = $flowers->get($flowerId);

            if (!$flower) {
                continue;
            }

            $unit = $units[$index] ?? '';
            $quantity = $quantities[$index] ?? '';
            $price = $prices[$index] ?? '';
            $flowerMrp = $mrpPrices[$index] ?? '';
            $flowerSale = $salePrices[$index] ?? '';

            if ($unit === '' || $quantity === '' || $price === '') {
                throw ValidationException::withMessages([
                    'flower_ids' => 'Flower unit, quantity and price are required.',
                ]);
            }

            $includedFlowers[] = [
                'flower_id' => (int) $flower->id,
                'flower_name' => $flower->flower_name,
                'unit' => $unit,
                'quantity' => (float) $quantity,
                'price' => (float) $price,
                'mrp_price' => (float) ($flowerMrp !== '' ? $flowerMrp : $price),
                'sale_price' => (float) ($flowerSale !== '' ? $flowerSale : $price),
            ];
        }

        if (empty($includedFlowers)) {
            throw ValidationException::withMessages([
                'flower_ids' => 'Please select at least one valid flower.',
            ]);
        }

        $data['included_flowers'] = $includedFlowers;
        $data['duration_months'] = $durationMap[$data['package_type']] ?? 1;

        // Existing mobile app compatibility
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

        if ($packet && $packet->image) {
            $oldImage = public_path($packet->image);

            if (file_exists($oldImage)) {
                unlink($oldImage);
            }
        }

        $folder = public_path('uploads/pooja-packets');

        if (!file_exists($folder)) {
            mkdir($folder, 0755, true);
        }

        $file = $request->file('image');
        $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

        $file->move($folder, $fileName);

        return 'uploads/pooja-packets/' . $fileName;
    }
}