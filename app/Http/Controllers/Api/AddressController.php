<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class AddressController extends Controller
{
    /**
     * Return only the authenticated customer's addresses.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return $this->unauthenticated();
        }

        $addresses = Address::query()
            ->where('user_id', $user->getKey())
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
     * Return one address owned by the authenticated customer.
     */
    public function show(Request $request, Address $address): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return $this->unauthenticated();
        }

        $ownedAddress = $this->findOwnedAddress(
            (int) $user->getKey(),
            $address->getKey(),
        );

        if (!$ownedAddress) {
            return $this->notFound();
        }

        return response()->json([
            'status' => true,
            'message' => 'Address fetched successfully.',
            'data' => $ownedAddress,
        ]);
    }

    /**
     * Create an address for the authenticated customer.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return $this->unauthenticated();
        }

        $data = $this->validatedData($request);

        try {
            $address = DB::transaction(function () use ($user, $data): Address {
                $userId = (int) $user->getKey();
                $query = Address::query()->where('user_id', $userId);

                $makeDefault = (bool) ($data['is_default'] ?? false);

                // The first address must always become the default address.
                if (!$query->exists()) {
                    $makeDefault = true;
                }

                if ($makeDefault) {
                    Address::query()
                        ->where('user_id', $userId)
                        ->update(['is_default' => false]);
                }

                $values = [
                    'user_id' => $userId,
                    'address_type' => $data['address_type'],
                    'name' => $data['name'],
                    'number' => $this->nullableString($data['number'] ?? null),
                    'address' => $data['address'],
                    'city' => $data['city'],
                    'state' => $data['state'],
                    'pincode' => $data['pincode'],
                    'landmark' => $this->nullableString($data['landmark'] ?? null),
                    'is_default' => $makeDefault,
                ];

                /*
                 * Compatibility protection:
                 * old production databases may not have the GPS columns yet.
                 * In that case, address creation still succeeds. After running
                 * the included migration, coordinates are saved automatically.
                 */
                if ($this->coordinatesSupported()) {
                    $values['latitude'] = $data['latitude'] ?? null;
                    $values['longitude'] = $data['longitude'] ?? null;
                }

                return Address::query()->create($values);
            });

            return response()->json([
                'status' => true,
                'message' => 'Address created successfully.',
                'data' => $address->fresh(),
            ], 201);
        } catch (Throwable $error) {
            return $this->serverFailure($error, 'create');
        }
    }

    /**
     * Update an address owned by the authenticated customer.
     */
    public function update(Request $request, Address $address): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return $this->unauthenticated();
        }

        $userId = (int) $user->getKey();
        $ownedAddress = $this->findOwnedAddress($userId, $address->getKey());

        if (!$ownedAddress) {
            return $this->notFound();
        }

        $data = $this->validatedData($request);

        try {
            DB::transaction(function () use ($userId, $ownedAddress, $data): void {
                $makeDefault = array_key_exists('is_default', $data)
                    ? (bool) $data['is_default']
                    : (bool) $ownedAddress->is_default;

                $hasAnotherAddress = Address::query()
                    ->where('user_id', $userId)
                    ->whereKeyNot($ownedAddress->getKey())
                    ->exists();

                if (!$hasAnotherAddress) {
                    $makeDefault = true;
                }

                if ($makeDefault) {
                    Address::query()
                        ->where('user_id', $userId)
                        ->whereKeyNot($ownedAddress->getKey())
                        ->update(['is_default' => false]);
                }

                $values = [
                    'address_type' => $data['address_type'],
                    'name' => $data['name'],
                    'number' => $this->nullableString($data['number'] ?? null),
                    'address' => $data['address'],
                    'city' => $data['city'],
                    'state' => $data['state'],
                    'pincode' => $data['pincode'],
                    'landmark' => $this->nullableString($data['landmark'] ?? null),
                    'is_default' => $makeDefault,
                ];

                if ($this->coordinatesSupported()) {
                    $values['latitude'] = array_key_exists('latitude', $data)
                        ? $data['latitude']
                        : $ownedAddress->latitude;
                    $values['longitude'] = array_key_exists('longitude', $data)
                        ? $data['longitude']
                        : $ownedAddress->longitude;
                }

                $ownedAddress->update($values);

                if (!$makeDefault) {
                    $defaultExists = Address::query()
                        ->where('user_id', $userId)
                        ->where('is_default', true)
                        ->exists();

                    if (!$defaultExists) {
                        Address::query()
                            ->where('user_id', $userId)
                            ->whereKeyNot($ownedAddress->getKey())
                            ->latest('id')
                            ->first()
                            ?->update(['is_default' => true]);
                    }
                }
            });

            return response()->json([
                'status' => true,
                'message' => 'Address updated successfully.',
                'data' => $ownedAddress->fresh(),
            ]);
        } catch (Throwable $error) {
            return $this->serverFailure($error, 'update');
        }
    }

    /**
     * Mark one owned address as the default address.
     */
    public function makeDefault(Request $request, Address $address): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return $this->unauthenticated();
        }

        $userId = (int) $user->getKey();
        $ownedAddress = $this->findOwnedAddress($userId, $address->getKey());

        if (!$ownedAddress) {
            return $this->notFound();
        }

        try {
            DB::transaction(function () use ($userId, $ownedAddress): void {
                Address::query()
                    ->where('user_id', $userId)
                    ->whereKeyNot($ownedAddress->getKey())
                    ->update(['is_default' => false]);

                $ownedAddress->update([
                    'is_default' => true,
                ]);
            });

            return response()->json([
                'status' => true,
                'message' => 'Default address updated successfully.',
                'data' => $ownedAddress->fresh(),
            ]);
        } catch (Throwable $error) {
            return $this->serverFailure($error, 'make-default');
        }
    }

    /**
     * Delete an address owned by the authenticated customer.
     */
    public function destroy(Request $request, Address $address): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return $this->unauthenticated();
        }

        $userId = (int) $user->getKey();
        $ownedAddress = $this->findOwnedAddress($userId, $address->getKey());

        if (!$ownedAddress) {
            return $this->notFound();
        }

        try {
            DB::transaction(function () use ($userId, $ownedAddress): void {
                $wasDefault = (bool) $ownedAddress->is_default;

                $ownedAddress->delete();

                if ($wasDefault) {
                    Address::query()
                        ->where('user_id', $userId)
                        ->latest('id')
                        ->first()
                        ?->update(['is_default' => true]);
                }
            });

            return response()->json([
                'status' => true,
                'message' => 'Address deleted successfully.',
            ]);
        } catch (Throwable $error) {
            return $this->serverFailure($error, 'delete');
        }
    }

    /**
     * Validate the address request payload.
     *
     * @throws ValidationException
     */
    private function validatedData(Request $request): array
    {
        return $request->validate([
            'address_type' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'number' => ['nullable', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:1000'],
            'city' => ['required', 'string', 'max:150'],
            'state' => ['required', 'string', 'max:150'],
            'pincode' => ['required', 'digits:6'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'is_default' => ['sometimes', 'boolean'],
            'latitude' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
        ]);
    }

    private function findOwnedAddress(int $userId, mixed $addressId): ?Address
    {
        return Address::query()
            ->where('user_id', $userId)
            ->whereKey($addressId)
            ->first();
    }

    private function coordinatesSupported(): bool
    {
        static $supported;

        if ($supported !== null) {
            return $supported;
        }

        return $supported = Schema::hasColumn('addresses', 'latitude')
            && Schema::hasColumn('addresses', 'longitude');
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function unauthenticated(): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => 'Unauthenticated.',
        ], 401);
    }

    private function notFound(): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => 'Address not found.',
        ], 404);
    }

    private function serverFailure(Throwable $error, string $action): JsonResponse
    {
        $reference = (string) Str::uuid();

        Log::error('Address API database/server failure.', [
            'reference' => $reference,
            'action' => $action,
            'exception' => get_class($error),
            'message' => $error->getMessage(),
            'sql_state' => $error instanceof QueryException
                ? $error->errorInfo[0] ?? null
                : null,
        ]);

        $response = [
            'status' => false,
            'message' => 'Unable to save the address because of a server database error.',
            'error_reference' => $reference,
        ];

        if (config('app.debug')) {
            $response['debug'] = $error->getMessage();
        }

        return response()->json($response, 500);
    }
}
