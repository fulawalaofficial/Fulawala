<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddressController extends Controller
{
    /**
     * Return all addresses for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $addresses = Address::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('is_default')
            ->latest('id')
            ->get();

        return response()->json([
            'data' => $addresses,
        ]);
    }

    /**
     * Save a new address from the Event Booking modal.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'address_type' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:120'],
            'number' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:1000'],
            'city' => ['required', 'string', 'max:120'],
            'state' => ['required', 'string', 'max:120'],
            'pincode' => ['required', 'string', 'regex:/^[0-9]{6}$/'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'is_default' => ['nullable', 'boolean'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $address = DB::transaction(function () use ($request, $data) {
            $userId = $request->user()->id;

            $hasAddress = Address::query()
                ->where('user_id', $userId)
                ->exists();

            $makeDefault = !$hasAddress || (bool) ($data['is_default'] ?? false);

            if ($makeDefault) {
                Address::query()
                    ->where('user_id', $userId)
                    ->update(['is_default' => false]);
            }

            return Address::create([
                ...$data,
                'user_id' => $userId,
                'is_default' => $makeDefault,
            ]);
        });

        return response()->json([
            'message' => 'Address saved successfully.',
            'data' => $address->fresh(),
        ], 201);
    }
}
