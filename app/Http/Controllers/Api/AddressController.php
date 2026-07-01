<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        $addresses = Address::where('user_id', $request->user()->id)
            ->orderByDesc('is_default')
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Addresses fetched successfully.',
            'data' => $addresses,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateAddress($request);

        return DB::transaction(function () use ($request, $data) {
            $userId = $request->user()->id;

            $hasAddress = Address::where('user_id', $userId)->exists();

            if (($data['is_default'] ?? false) || !$hasAddress) {
                Address::where('user_id', $userId)->update([
                    'is_default' => false,
                ]);

                $data['is_default'] = true;
            }

            $data['user_id'] = $userId;

            $address = Address::create($data);

            return response()->json([
                'status' => true,
                'message' => 'Address created successfully.',
                'data' => $address,
            ], 201);
        });
    }

    public function show(Request $request, Address $address)
    {
        $this->checkOwner($request, $address);

        return response()->json([
            'status' => true,
            'message' => 'Address fetched successfully.',
            'data' => $address,
        ]);
    }

    public function update(Request $request, Address $address)
    {
        $this->checkOwner($request, $address);

        $data = $this->validateAddress($request, true);

        return DB::transaction(function () use ($request, $address, $data) {
            if (($data['is_default'] ?? false) === true) {
                Address::where('user_id', $request->user()->id)->update([
                    'is_default' => false,
                ]);

                $data['is_default'] = true;
            }

            $address->update($data);

            return response()->json([
                'status' => true,
                'message' => 'Address updated successfully.',
                'data' => $address->fresh(),
            ]);
        });
    }

    public function makeDefault(Request $request, Address $address)
    {
        $this->checkOwner($request, $address);

        return DB::transaction(function () use ($request, $address) {
            Address::where('user_id', $request->user()->id)->update([
                'is_default' => false,
            ]);

            $address->update([
                'is_default' => true,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Default address updated successfully.',
                'data' => $address->fresh(),
            ]);
        });
    }

    public function destroy(Request $request, Address $address)
    {
        $this->checkOwner($request, $address);

        $wasDefault = $address->is_default;

        $address->delete();

        if ($wasDefault) {
            $nextAddress = Address::where('user_id', $request->user()->id)
                ->latest()
                ->first();

            if ($nextAddress) {
                $nextAddress->update([
                    'is_default' => true,
                ]);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Address deleted successfully.',
        ]);
    }

    private function validateAddress(Request $request, bool $isUpdate = false): array
    {
        $required = $isUpdate ? 'sometimes' : 'required';

        return $request->validate([
            'address_type' => [
                $required,
                Rule::in(['home', 'apartment', 'temple']),
            ],

            'name' => [$required, 'string', 'max:255'],
            'number' => [$required, 'string', 'max:100'],

            'address' => [$required, 'string', 'max:1000'],
            'city' => [$required, 'string', 'max:100'],
            'state' => [$required, 'string', 'max:100'],
            'pincode' => [$required, 'string', 'max:20'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'is_default' => ['nullable', 'boolean'],
        ]);
    }

    private function checkOwner(Request $request, Address $address): void
    {
        if ($address->user_id !== $request->user()->id) {
            abort(404, 'Address not found.');
        }
    }
}