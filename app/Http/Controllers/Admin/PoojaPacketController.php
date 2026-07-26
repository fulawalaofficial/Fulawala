<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FlowerProduct;
use App\Models\PoojaPacket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PoojaPacketController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
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

        if (in_array($status, ['Active', 'Inactive'], true)) {
            $query->where('status', $status);
        }

        $allowedPackageTypes = [
            'Monthly',
            'Three Month',
            'Six Month',
            'One Year',
        ];

        if (in_array($packageType, $allowedPackageTypes, true)) {
            $query->where('package_type', $packageType);
        }

        $packets = $query
            ->latest()
            ->paginate(20)
            ->withQueryString();

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

        return view(
            'admin.pooja-packets.form',
            compact('packet', 'flowers')
        );
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

        return view(
            'admin.pooja-packets.form',
            compact('packet', 'flowers')
        );
    }

    public function update(
        Request $request,
        PoojaPacket $poojaPacket
    ) {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            $data['image'] = $this->uploadImage(
                $request,
                $poojaPacket
            );
        }

        $poojaPacket->update($data);

        return redirect()
            ->route('admin.pooja-packets.index')
            ->with('success', 'Pooja package updated successfully.');
    }

    public function destroy(PoojaPacket $poojaPacket)
    {
        $this->deleteImage($poojaPacket->image);

        $poojaPacket->delete();

        return redirect()
            ->route('admin.pooja-packets.index')
            ->with('success', 'Pooja package deleted successfully.');
    }

    private function getFlowersForForm()
    {
        $query = FlowerProduct::query();

        if (Schema::hasColumn('flower_products', 'status')) {
            $query->where('status', 'Active');
        }

        return $query
            ->orderBy('flower_name')
            ->get();
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'packet_name' => [
                'required',
                'string',
                'max:255',
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'package_type' => [
                'required',
                'in:Monthly,Three Month,Six Month,One Year',
            ],

            'status' => [
                'required',
                'in:Active,Inactive',
            ],

            'flower_ids' => [
                'required',
                'array',
                'min:1',
            ],

            'flower_ids.*' => [
                'nullable',
                'integer',
            ],

            'flower_units' => [
                'nullable',
                'array',
            ],

            'flower_units.*' => [
                'nullable',
                'string',
                'max:50',
            ],

            'quantities' => [
                'nullable',
                'array',
            ],

            'quantities.*' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'prices' => [
                'nullable',
                'array',
            ],

            'prices.*' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'flower_mrp_prices' => [
                'nullable',
                'array',
            ],

            'flower_mrp_prices.*' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'flower_sale_prices' => [
                'nullable',
                'array',
            ],

            'flower_sale_prices.*' => [
                'nullable',
                'numeric',
                'min:0',
            ],
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

        $validFlowerIds = array_values(
            array_filter(
                $flowerIds,
                fn ($flowerId) => !empty($flowerId)
            )
        );

        if (empty($validFlowerIds)) {
            throw ValidationException::withMessages([
                'flower_ids' => 'Please select at least one flower.',
            ]);
        }

        $flowers = FlowerProduct::whereIn('id', $validFlowerIds)
            ->get()
            ->keyBy('id');

        $includedFlowers = [];
        $packageMrpTotal = 0;
        $packageSaleTotal = 0;

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

            if (
                $unit === '' ||
                $unit === null ||
                $quantity === '' ||
                $quantity === null ||
                $price === '' ||
                $price === null
            ) {
                throw ValidationException::withMessages([
                    'flower_ids' =>
                        'Flower unit, quantity and price are required.',
                ]);
            }

            $quantity = (float) $quantity;
            $price = (float) $price;

            $flowerMrp = (
                $flowerMrp !== '' &&
                $flowerMrp !== null
            )
                ? (float) $flowerMrp
                : $price;

            $flowerSale = (
                $flowerSale !== '' &&
                $flowerSale !== null
            )
                ? (float) $flowerSale
                : $price;

            $lineMrpTotal = $quantity * $flowerMrp;
            $lineSaleTotal = $quantity * $flowerSale;

            $packageMrpTotal += $lineMrpTotal;
            $packageSaleTotal += $lineSaleTotal;

            $includedFlowers[] = [
                'flower_id' => (int) $flower->id,
                'flower_name' => $flower->flower_name,
                'unit' => $unit,
                'quantity' => $quantity,
                'price' => $price,
                'mrp_price' => $flowerMrp,
                'sale_price' => $flowerSale,
                'line_mrp_total' => round($lineMrpTotal, 2),
                'line_sale_total' => round($lineSaleTotal, 2),
            ];
        }

        if (empty($includedFlowers)) {
            throw ValidationException::withMessages([
                'flower_ids' =>
                    'Please select at least one valid flower.',
            ]);
        }

        $data['included_flowers'] = $includedFlowers;

        $data['duration_months'] =
            $durationMap[$data['package_type']] ?? 1;

        $data['mrp_price'] = round($packageMrpTotal, 2);
        $data['sale_price'] = round($packageSaleTotal, 2);

        /*
         * Keep old API/mobile application fields compatible.
         */
        $data['monthly_price'] = round($packageSaleTotal, 2);
        $data['weekly_price'] = null;
        $data['daily_quantity'] = null;

        unset(
            $data['flower_ids'],
            $data['flower_units'],
            $data['quantities'],
            $data['prices'],
            $data['flower_mrp_prices'],
            $data['flower_sale_prices']
        );

        return $data;
    }

    /**
     * Upload image into:
     *
     * storage/app/public/pooja-packets
     */
    private function uploadImage(
        Request $request,
        ?PoojaPacket $packet = null
    ): ?string {
        if (!$request->hasFile('image')) {
            return $packet?->image;
        }

        /*
         * Store the new file first.
         * Do not delete the old image until the new upload succeeds.
         */
        $newImagePath = $request
            ->file('image')
            ->store('pooja-packets', 'public');

        if (!$newImagePath) {
            throw ValidationException::withMessages([
                'image' => 'Image upload failed. Please try again.',
            ]);
        }

        if ($packet && $packet->image) {
            $this->deleteImage($packet->image);
        }

        /*
         * Database value example:
         *
         * pooja-packets/AbCdEf123.jpg
         */
        return $newImagePath;
    }

    private function deleteImage(?string $image): void
    {
        if (!$image) {
            return;
        }

        $image = str_replace('\\', '/', trim($image));

        /*
         * Convert a complete URL into a relative path.
         */
        if (Str::startsWith($image, ['http://', 'https://'])) {
            $imagePath = parse_url($image, PHP_URL_PATH);

            if (!$imagePath) {
                return;
            }

            $image = ltrim($imagePath, '/');
        } else {
            $image = ltrim($image, '/');
        }

        /*
         * New Laravel storage image.
         */
        if (Str::startsWith($image, 'storage/')) {
            $image = Str::after($image, 'storage/');
        }

        if (Str::startsWith($image, 'public/')) {
            $image = Str::after($image, 'public/');
        }

        if (Storage::disk('public')->exists($image)) {
            Storage::disk('public')->delete($image);

            return;
        }

        /*
         * Delete old images that were stored inside public/uploads.
         */
        $legacyPath = public_path($image);

        if (is_file($legacyPath)) {
            @unlink($legacyPath);
        }
    }
}