<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FlowerProduct;
use App\Models\PoojaPacket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class PoojaPacketController extends Controller
{
    // Values are kept compatible with the existing package_type database column.
    private const DURATIONS = [
        'Monthly' => 1,
        'Two Month' => 2,
        'Three Month' => 3,
        'Six Month' => 6,
        'One Year' => 12,
    ];

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', '');
        $packageType = (string) $request->query('package_type', '');

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
        if (array_key_exists($packageType, self::DURATIONS)) {
            $query->where('package_type', $packageType);
        }

        $packets = $query->latest()->paginate(20)->withQueryString();
        $stats = [
            'total' => PoojaPacket::count(),
            'active' => PoojaPacket::where('status', 'Active')->count(),
            'inactive' => PoojaPacket::where('status', 'Inactive')->count(),
        ];

        return view('admin.pooja-packets.index', compact(
            'packets', 'stats', 'search', 'status', 'packageType'
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
        $newImage = null;

        try {
            if ($request->hasFile('image')) {
                $newImage = $this->uploadImage($request);
                $data['image'] = $newImage;
            }
            PoojaPacket::create($data);
        } catch (Throwable $exception) {
            $this->deleteImage($newImage);
            throw $exception;
        }

        return redirect()->route('admin.pooja-packets.index')
            ->with('success', 'Pooja package added successfully.');
    }

    public function edit(PoojaPacket $poojaPacket)
    {
        $packet = $poojaPacket;
        // Also return inactive flowers, so existing rows do not disappear on edit.
        $flowers = $this->getFlowersForForm();

        return view('admin.pooja-packets.form', compact('packet', 'flowers'));
    }

    public function update(Request $request, PoojaPacket $poojaPacket)
    {
        $data = $this->validated($request);
        $oldImage = $poojaPacket->image;
        $newImage = null;

        try {
            if ($request->hasFile('image')) {
                $newImage = $this->uploadImage($request);
                $data['image'] = $newImage;
            }
            $poojaPacket->update($data);
        } catch (Throwable $exception) {
            // On DB failure, retain the existing image and clean up new upload.
            $this->deleteImage($newImage);
            throw $exception;
        }

        if ($newImage && $oldImage !== $newImage) {
            $this->deleteImage($oldImage);
        }

        return redirect()->route('admin.pooja-packets.index')
            ->with('success', 'Pooja package updated successfully.');
    }

    public function destroy(PoojaPacket $poojaPacket)
    {
        $oldImage = $poojaPacket->image;
        $poojaPacket->delete();
        $this->deleteImage($oldImage);

        return redirect()->route('admin.pooja-packets.index')
            ->with('success', 'Pooja package deleted successfully.');
    }

    private function getFlowersForForm()
    {
        // Important: do NOT filter by 'Active' here. Inactive products can still
        // be referenced by subscriptions created in the past.
        return FlowerProduct::query()->orderBy('flower_name')->get();
    }

    private function validated(Request $request): array
    {
        $types = implode(',', array_keys(self::DURATIONS));
        $data = $request->validate([
            'packet_name' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'description' => ['nullable', 'string'],
            'package_type' => ['required', 'in:' . $types],
            'status' => ['required', 'in:Active,Inactive'],
            'mrp_price' => ['nullable', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'flower_ids' => ['nullable', 'array'],
            'flower_ids.*' => ['nullable', 'integer', 'exists:flower_products,id'],
            'flower_names' => ['nullable', 'array'],
            'flower_names.*' => ['nullable', 'string', 'max:255'],
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

        $ids = $request->input('flower_ids', []);
        $names = $request->input('flower_names', []);
        $units = $request->input('flower_units', []);
        $quantities = $request->input('quantities', []);
        $prices = $request->input('prices', []);
        $mrpPrices = $request->input('flower_mrp_prices', []);
        $salePrices = $request->input('flower_sale_prices', []);

        // Protect against malformed payloads, including old or manually posted forms.
        $ids = is_array($ids) ? $ids : [];
        $names = is_array($names) ? $names : [];
        $units = is_array($units) ? $units : [];
        $quantities = is_array($quantities) ? $quantities : [];
        $prices = is_array($prices) ? $prices : [];
        $mrpPrices = is_array($mrpPrices) ? $mrpPrices : [];
        $salePrices = is_array($salePrices) ? $salePrices : [];

        $flowerIds = array_values(array_filter($ids, fn ($id) => $id !== '' && $id !== null));
        $flowers = FlowerProduct::whereIn('id', $flowerIds)->get()->keyBy('id');

        $included = [];
        $mrpTotal = 0.0;
        $saleTotal = 0.0;
        $rowKeys = array_unique(array_merge(array_keys($ids), array_keys($names)));

        foreach ($rowKeys as $i) {
            $id = $ids[$i] ?? null;
            $customName = trim((string) ($names[$i] ?? ''));
            $flower = $id ? $flowers->get((int) $id) : null;
            $name = $flower ? (string) $flower->flower_name : $customName;

            // Allow one empty row in new forms; avoid saving empty lines.
            if ($name === '') {
                continue;
            }

            $unit = trim((string) ($units[$i] ?? ($flower->unit ?? '')));
            $rawQuantity = $quantities[$i] ?? '';
            $rawPrice = $prices[$i] ?? '';

            // Legacy packages can have just a flower name, without quantities
            // or product IDs. They must remain editable without deleting rows.
            if ($flower && ($unit === '' || $rawQuantity === '' || $rawQuantity === null)) {
                throw ValidationException::withMessages([
                    'flower_ids' => "Enter a unit and quantity for {$name}.",
                ]);
            }

            $quantity = ($rawQuantity === '' || $rawQuantity === null) ? 0.0 : (float) $rawQuantity;
            $price = ($rawPrice === '' || $rawPrice === null)
                ? (float) ($flower->price ?? 0)
                : (float) $rawPrice;
            $mrp = $this->optionalNumber($mrpPrices[$i] ?? null, $price);
            $sale = $this->optionalNumber($salePrices[$i] ?? null, $price);
            $lineMrp = round($quantity * $mrp, 2);
            $lineSale = round($quantity * $sale, 2);
            $mrpTotal += $lineMrp;
            $saleTotal += $lineSale;

            $included[] = [
                'flower_id' => $flower ? (int) $flower->id : null,
                'flower_name' => $name,
                'unit' => $unit,
                'quantity' => $quantity,
                'price' => round($price, 2),
                'mrp_price' => round($mrp, 2),
                'sale_price' => round($sale, 2),
                'line_mrp_total' => $lineMrp,
                'line_sale_total' => $lineSale,
            ];
        }

        if (count($included) === 0) {
            throw ValidationException::withMessages([
                'flower_ids' => 'Select a flower or enter at least one manual flower name.',
            ]);
        }

        $data['included_flowers'] = $included;
        $data['duration_months'] = self::DURATIONS[$data['package_type']];

        // The subscription selling price is independent from raw flower costs.
        // This lets admins set promotional 579/1099/1599/3099/5999 prices.
        $data['mrp_price'] = $this->optionalNumber($data['mrp_price'] ?? null, $mrpTotal);
        $data['sale_price'] = $this->optionalNumber($data['sale_price'] ?? null, $saleTotal);
        $data['mrp_price'] = round($data['mrp_price'], 2);
        $data['sale_price'] = round($data['sale_price'], 2);

        // Preserve the existing API shape. Here monthly_price historically
        // mirrors the plan's selling price, regardless of its duration.
        $data['monthly_price'] = $data['sale_price'];
        $data['weekly_price'] = null;
        $data['daily_quantity'] = null;

        unset(
            $data['flower_ids'], $data['flower_names'], $data['flower_units'],
            $data['quantities'], $data['prices'], $data['flower_mrp_prices'],
            $data['flower_sale_prices'], $data['image']
        );

        return $data;
    }

    private function optionalNumber($value, float $fallback): float
    {
        return ($value === null || $value === '') ? $fallback : (float) $value;
    }

    private function uploadImage(Request $request): string
    {
        $file = $request->file('image');
        $path = $file?->store('pooja-packets', 'public');

        if (!$path) {
            throw ValidationException::withMessages([
                'image' => 'Image upload failed. Please check storage permissions.',
            ]);
        }

        return $path;
    }

    /** Only delete files owned by the Pooja Packet image folder. */
    private function deleteImage(?string $stored): void
    {
        if (!$stored) {
            return;
        }

        $path = str_replace('\\', '/', trim($stored));
        if (Str::startsWith($path, ['http://', 'https://'])) {
            // External URLs must never result in deleting local assets.
            return;
        }

        $path = ltrim($path, '/');
        foreach (['storage/app/public/', 'app/public/', 'public/storage/', 'public/'] as $prefix) {
            if (Str::startsWith($path, $prefix)) {
                $path = Str::after($path, $prefix);
                break;
            }
        }
        if (Str::startsWith($path, 'storage/')) {
            $path = Str::after($path, 'storage/');
        }

        if (Str::startsWith($path, 'pooja-packets/') && !Str::contains($path, '..')) {
            Storage::disk('public')->delete($path);
            return;
        }

        // Safe handling of older assets stored in public/uploads/pooja-packets.
        if (Str::startsWith($path, 'uploads/pooja-packets/') && !Str::contains($path, '..')) {
            $legacyPath = public_path($path);
            if (is_file($legacyPath)) {
                @unlink($legacyPath);
            }
        }
    }
}
