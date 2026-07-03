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
        $user = $request->user();

        if (!$user) {
            return $this->authError();
        }

        $addresses = Address::where('user_id', $user->id)
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Addresses fetched successfully.',
            'data' => $addresses,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return $this->authError();
        }

        $data = $this->validateAddress($request);

        return DB::transaction(function () use ($user, $data) {
            $hasAddress = Address::where('user_id', $user->id)->exists();

            $makeDefault = !$hasAddress || !empty($data['is_default']);

            if ($makeDefault) {
                Address::where('user_id', $user->id)->update([
                    'is_default' => false,
                ]);
            }

            $data['user_id'] = $user->id;
            $data['is_default'] = $makeDefault;

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
        $user = $request->user();

        if (!$user) {
            return $this->authError();
        }

        if (!$this->isOwner($user->id, $address)) {
            return $this->notFoundError();
        }

        return response()->json([
            'status' => true,
            'message' => 'Address fetched successfully.',
            'data' => $address,
        ]);
    }

    public function update(Request $request, Address $address)
    {
        $user = $request->user();

        if (!$user) {
            return $this->authError();
        }

        if (!$this->isOwner($user->id, $address)) {
            return $this->notFoundError();
        }

        $data = $this->validateAddress($request, true);

        return DB::transaction(function () use ($user, $address, $data) {
            if (array_key_exists('is_default', $data)) {
                if ($data['is_default'] === true) {
                    Address::where('user_id', $user->id)->update([
                        'is_default' => false,
                    ]);

                    $data['is_default'] = true;
                } else {
                    $otherDefaultExists = Address::where('user_id', $user->id)
                        ->where('id', '!=', $address->id)
                        ->where('is_default', true)
                        ->exists();

                    $data['is_default'] = $otherDefaultExists ? false : true;
                }
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
        $user = $request->user();

        if (!$user) {
            return $this->authError();
        }

        if (!$this->isOwner($user->id, $address)) {
            return $this->notFoundError();
        }

        return DB::transaction(function () use ($user, $address) {
            Address::where('user_id', $user->id)->update([
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
        $user = $request->user();

        if (!$user) {
            return $this->authError();
        }

        if (!$this->isOwner($user->id, $address)) {
            return $this->notFoundError();
        }

        return DB::transaction(function () use ($user, $address) {
            $wasDefault = (bool) $address->is_default;

            $address->delete();

            if ($wasDefault) {
                $nextAddress = Address::where('user_id', $user->id)
                    ->orderByDesc('id')
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
        });
    }

    private function validateAddress(Request $request, bool $isUpdate = false): array
    {
        $required = $isUpdate ? 'sometimes|required' : 'required';

        $data = $request->validate([
            'address_type' => [
                $required,
                Rule::in(['home', 'apartment', 'temple', 'office', 'other']),
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

        if ($request->has('is_default')) {
            $data['is_default'] = $request->boolean('is_default');
        }

        return $data;
    }

    private function isOwner(int $userId, Address $address): bool
    {
        return (int) $address->user_id === (int) $userId;
    }

    private function authError()
    {
        return response()->json([
            'status' => false,
            'message' => 'Unauthenticated. Please send valid Bearer Token.',
        ], 401);
    }

    private function notFoundError()
    {
        return response()->json([
            'status' => false,
            'message' => 'Address not found.',
        ], 404);
    }
}