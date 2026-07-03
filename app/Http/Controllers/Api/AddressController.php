<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Throwable;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        try {
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
        } catch (Throwable $e) {
            return $this->serverError($e);
        }
    }

    public function store(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return $this->authError();
            }

            $data = $request->validate([
                'address_type' => ['required', Rule::in(['home', 'apartment', 'temple', 'office', 'other'])],
                'name' => ['required', 'string', 'max:255'],
                'number' => ['required', 'string', 'max:100'],
                'address' => ['required', 'string', 'max:1000'],
                'city' => ['required', 'string', 'max:255'],
                'state' => ['required', 'string', 'max:255'],
                'pincode' => ['required', 'string', 'max:255'],
                'landmark' => ['nullable', 'string', 'max:255'],
                'is_default' => ['nullable', 'boolean'],
            ]);

            return DB::transaction(function () use ($user, $request, $data) {
                $hasAddress = Address::where('user_id', $user->id)->exists();

                $makeDefault = !$hasAddress || $request->boolean('is_default');

                if ($makeDefault) {
                    Address::where('user_id', $user->id)->update([
                        'is_default' => 0,
                    ]);
                }

                $address = Address::create([
                    'user_id' => $user->id,
                    'address_type' => $data['address_type'],
                    'name' => $data['name'],
                    'number' => $data['number'],
                    'address' => $data['address'],
                    'city' => $data['city'],
                    'state' => $data['state'],
                    'pincode' => $data['pincode'],
                    'landmark' => $data['landmark'] ?? null,
                    'is_default' => $makeDefault ? 1 : 0,
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Address created successfully.',
                    'data' => $address,
                ], 201);
            });
        } catch (Throwable $e) {
            return $this->serverError($e);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return $this->authError();
            }

            $address = Address::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$address) {
                return $this->notFoundError();
            }

            return response()->json([
                'status' => true,
                'message' => 'Address fetched successfully.',
                'data' => $address,
            ]);
        } catch (Throwable $e) {
            return $this->serverError($e);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return $this->authError();
            }

            $address = Address::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$address) {
                return $this->notFoundError();
            }

            $data = $request->validate([
                'address_type' => ['sometimes', 'required', Rule::in(['home', 'apartment', 'temple', 'office', 'other'])],
                'name' => ['sometimes', 'required', 'string', 'max:255'],
                'number' => ['sometimes', 'required', 'string', 'max:100'],
                'address' => ['sometimes', 'required', 'string', 'max:1000'],
                'city' => ['sometimes', 'required', 'string', 'max:255'],
                'state' => ['sometimes', 'required', 'string', 'max:255'],
                'pincode' => ['sometimes', 'required', 'string', 'max:255'],
                'landmark' => ['nullable', 'string', 'max:255'],
                'is_default' => ['nullable', 'boolean'],
            ]);

            return DB::transaction(function () use ($user, $request, $address, $data) {
                if ($request->has('is_default') && $request->boolean('is_default')) {
                    Address::where('user_id', $user->id)->update([
                        'is_default' => 0,
                    ]);

                    $data['is_default'] = 1;
                }

                $address->update($data);

                return response()->json([
                    'status' => true,
                    'message' => 'Address updated successfully.',
                    'data' => $address->fresh(),
                ]);
            });
        } catch (Throwable $e) {
            return $this->serverError($e);
        }
    }

    public function makeDefault(Request $request, $id)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return $this->authError();
            }

            $address = Address::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$address) {
                return $this->notFoundError();
            }

            return DB::transaction(function () use ($user, $address) {
                Address::where('user_id', $user->id)->update([
                    'is_default' => 0,
                ]);

                $address->update([
                    'is_default' => 1,
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Default address updated successfully.',
                    'data' => $address->fresh(),
                ]);
            });
        } catch (Throwable $e) {
            return $this->serverError($e);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return $this->authError();
            }

            $address = Address::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$address) {
                return $this->notFoundError();
            }

            $address->delete();

            return response()->json([
                'status' => true,
                'message' => 'Address deleted successfully.',
            ]);
        } catch (Throwable $e) {
            return $this->serverError($e);
        }
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

    private function serverError(Throwable $e)
    {
        Log::error('Address API Error', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);

        return response()->json([
            'status' => false,
            'message' => 'Server Error',
            'error' => config('app.debug') ? $e->getMessage() : 'Check Laravel log file.',
            'line' => config('app.debug') ? $e->getLine() : null,
        ], 500);
    }
}