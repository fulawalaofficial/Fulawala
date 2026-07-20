<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AddressController extends Controller
{
    /**
     * Return only the authenticated customer's addresses.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $addresses = $user->addresses()
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Addresses fetched successfully.',
            'data' => $addresses,
        ]);
    }

    /**
     * Create an address for the authenticated customer.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $data = $this->validatedData($request);

        $address = DB::transaction(function () use ($user, $data) {
            $makeDefault = (bool) ($data['is_default'] ?? false);

            /*
             * Automatically make the first address the default address.
             */
            if (!$user->addresses()->exists()) {
                $makeDefault = true;
            }

            if ($makeDefault) {
                $user->addresses()->update([
                    'is_default' => false,
                ]);
            }

            return $user->addresses()->create([
                'address_type' => $data['address_type'],
                'name' => $data['name'],
                'number' => $data['number'] ?? null,
                'address' => $data['address'],
                'city' => $data['city'],
                'state' => $data['state'],
                'pincode' => $data['pincode'],
                'landmark' => $data['landmark'] ?? null,
                'is_default' => $makeDefault,
            ]);
        });

        return response()->json([
            'status' => true,
            'message' => 'Address created successfully.',
            'data' => $address->fresh(),
        ], 201);
    }

    /**
     * Update an address owned by the authenticated customer.
     */
    public function update(
        Request $request,
        Address $address
    ): JsonResponse {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        /*
         * Never allow a customer to update another customer's address.
         */
        $ownedAddress = $user->addresses()
            ->whereKey($address->getKey())
            ->first();

        if (!$ownedAddress) {
            return response()->json([
                'status' => false,
                'message' => 'Address not found.',
            ], 404);
        }

        $data = $this->validatedData($request);

        DB::transaction(function () use ($user, $ownedAddress, $data) {
            $makeDefault = (bool) ($data['is_default'] ?? false);

            if ($makeDefault) {
                $user->addresses()
                    ->where('id', '!=', $ownedAddress->getKey())
                    ->update([
                        'is_default' => false,
                    ]);
            }

            $ownedAddress->update([
                'address_type' => $data['address_type'],
                'name' => $data['name'],
                'number' => $data['number'] ?? null,
                'address' => $data['address'],
                'city' => $data['city'],
                'state' => $data['state'],
                'pincode' => $data['pincode'],
                'landmark' => $data['landmark'] ?? null,
                'is_default' => $makeDefault,
            ]);
        });

        return response()->json([
            'status' => true,
            'message' => 'Address updated successfully.',
            'data' => $ownedAddress->fresh(),
        ]);
    }

    /**
     * Delete an address owned by the authenticated customer.
     */
    public function destroy(
        Request $request,
        Address $address
    ): JsonResponse {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $ownedAddress = $user->addresses()
            ->whereKey($address->getKey())
            ->first();

        if (!$ownedAddress) {
            return response()->json([
                'status' => false,
                'message' => 'Address not found.',
            ], 404);
        }

        DB::transaction(function () use ($user, $ownedAddress) {
            $wasDefault = (bool) $ownedAddress->is_default;
            $ownedAddress->delete();

            if ($wasDefault) {
                $nextAddress = $user->addresses()
                    ->orderByDesc('id')
                    ->first();

                if ($nextAddress) {
                    $nextAddress->update([
                        'is_default' => true,
                    ]);
                }
            }
        });

        return response()->json([
            'status' => true,
            'message' => 'Address deleted successfully.',
        ]);
    }

    /**
     * Validate the address request payload.
     *
     * @throws ValidationException
     */
    private function validatedData(Request $request): array
    {
        return $request->validate([
            'address_type' => [
                'required',
                'string',
                'max:50',
            ],
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'number' => [
                'nullable',
                'string',
                'max:100',
            ],
            'address' => [
                'required',
                'string',
                'max:1000',
            ],
            'city' => [
                'required',
                'string',
                'max:150',
            ],
            'state' => [
                'required',
                'string',
                'max:150',
            ],
            'pincode' => [
                'required',
                'digits:6',
            ],
            'landmark' => [
                'nullable',
                'string',
                'max:255',
            ],
            'is_default' => [
                'sometimes',
                'boolean',
            ],
        ]);
    }
}
