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

        $this->normalizeRequestAliases($request);
        $data = $this->validatedData($request, false);
        $data = $this->normalizeCoordinates($data);

        try {
            $address = DB::transaction(function () use ($user, $data): Address {
                $userId = (int) $user->getKey();

                $makeDefault = (bool) ($data['is_default'] ?? false);

                /*
                 * First saved address is always default.
                 */
                if (!Address::query()->where('user_id', $userId)->exists()) {
                    $makeDefault = true;
                }

                if ($makeDefault) {
                    Address::query()
                        ->where('user_id', $userId)
                        ->update(['is_default' => false]);
                }

                $values = [
                    'user_id' => $userId,
                    'address_type' => trim((string) $data['address_type']),
                    'name' => trim((string) $data['name']),
                    'number' => $this->nullableString($data['number'] ?? null),
                    'address' => trim((string) $data['address']),
                    'city' => trim((string) $data['city']),
                    'state' => trim((string) $data['state']),
                    'pincode' => trim((string) $data['pincode']),
                    'landmark' => $this->nullableString($data['landmark'] ?? null),
                    'is_default' => $makeDefault,
                ];

                /*
                 * Production-safety compatibility:
                 * address creation/update still works if an older database
                 * has not received the GPS migration yet.
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
     *
     * Both PUT and PATCH are supported. PATCH may send only the fields that
     * changed; omitted fields keep their existing values.
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

        $this->normalizeRequestAliases($request);
        $data = $this->validatedData($request, true);
        $data = $this->normalizeCoordinates($data);

        try {
            DB::transaction(function () use ($userId, $ownedAddress, $data): void {
                $makeDefault = array_key_exists('is_default', $data)
                    ? (bool) $data['is_default']
                    : (bool) $ownedAddress->is_default;

                $hasAnotherAddress = Address::query()
                    ->where('user_id', $userId)
                    ->where('id', '!=', $ownedAddress->getKey())
                    ->exists();

                /*
                 * A customer with exactly one address cannot end up with
                 * no default address.
                 */
                if (!$hasAnotherAddress) {
                    $makeDefault = true;
                }

                if ($makeDefault) {
                    Address::query()
                        ->where('user_id', $userId)
                        ->where('id', '!=', $ownedAddress->getKey())
                        ->update(['is_default' => false]);
                }

                $values = [];

                foreach ([
                    'address_type',
                    'name',
                    'address',
                    'city',
                    'state',
                    'pincode',
                ] as $field) {
                    if (array_key_exists($field, $data)) {
                        $values[$field] = trim((string) $data[$field]);
                    }
                }

                if (array_key_exists('number', $data)) {
                    $values['number'] = $this->nullableString($data['number']);
                }

                if (array_key_exists('landmark', $data)) {
                    $values['landmark'] = $this->nullableString($data['landmark']);
                }

                $values['is_default'] = $makeDefault;

                if ($this->coordinatesSupported()) {
                    if (array_key_exists('latitude', $data)) {
                        $values['latitude'] = $data['latitude'];
                    }

                    if (array_key_exists('longitude', $data)) {
                        $values['longitude'] = $data['longitude'];
                    }
                }

                $ownedAddress->update($values);

                /*
                 * If the current default address was explicitly unset, choose
                 * another saved address as default.
                 */
                if (!$makeDefault) {
                    $defaultExists = Address::query()
                        ->where('user_id', $userId)
                        ->where('is_default', true)
                        ->exists();

                    if (!$defaultExists) {
                        Address::query()
                            ->where('user_id', $userId)
                            ->where('id', '!=', $ownedAddress->getKey())
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
                    ->where('id', '!=', $ownedAddress->getKey())
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
     * Accept older/mobile payload aliases so released app builds continue
     * working while the canonical API remains latitude/longitude.
     */
    private function normalizeRequestAliases(Request $request): void
    {
        $merge = [];

        if (!$request->exists('latitude')) {
            foreach (['lat', 'current_latitude'] as $alias) {
                if ($request->exists($alias)) {
                    $merge['latitude'] = $request->input($alias);
                    break;
                }
            }
        }

        if (!$request->exists('longitude')) {
            foreach (['lng', 'lon', 'long', 'current_longitude'] as $alias) {
                if ($request->exists($alias)) {
                    $merge['longitude'] = $request->input($alias);
                    break;
                }
            }
        }

        if (!$request->exists('landmark') && $request->exists('land_mark')) {
            $merge['landmark'] = $request->input('land_mark');
        }

        if (!$request->exists('pincode') && $request->exists('postal_code')) {
            $merge['pincode'] = $request->input('postal_code');
        }

        if (!$request->exists('address') && $request->exists('full_address')) {
            $merge['address'] = $request->input('full_address');
        }

        if ($merge !== []) {
            $request->merge($merge);
        }
    }

    /**
     * Validate the address request payload.
     *
     * @throws ValidationException
     */
    private function validatedData(Request $request, bool $forUpdate): array
    {
        $presence = $forUpdate ? 'sometimes' : 'required';

        /*
         * Coordinates are a pair. Sending just latitude or just longitude
         * usually means the mobile payload is incomplete.
         */
        $hasLatitude = $request->exists('latitude');
        $hasLongitude = $request->exists('longitude');

        if ($hasLatitude xor $hasLongitude) {
            throw ValidationException::withMessages([
                'latitude' => [
                    'Latitude and longitude must be sent together.',
                ],
                'longitude' => [
                    'Latitude and longitude must be sent together.',
                ],
            ]);
        }

        return $request->validate([
            'address_type' => [$presence, 'string', 'max:50'],
            'name' => [$presence, 'string', 'max:255'],
            'number' => ['sometimes', 'nullable', 'string', 'max:100'],
            'address' => [$presence, 'string', 'max:1000'],
            'city' => [$presence, 'string', 'max:150'],
            'state' => [$presence, 'string', 'max:150'],
            'pincode' => [$presence, 'digits:6'],
            'landmark' => ['sometimes', 'nullable', 'string', 'max:255'],
            'is_default' => ['sometimes', 'boolean'],
            'latitude' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
        ]);
    }

    /**
     * Treat 0,0 as a missing GPS placeholder. It should not be shown to the
     * delivery team as a real customer location.
     */
    private function normalizeCoordinates(array $data): array
    {
        if (
            array_key_exists('latitude', $data)
            && array_key_exists('longitude', $data)
            && $data['latitude'] !== null
            && $data['longitude'] !== null
        ) {
            $latitude = (float) $data['latitude'];
            $longitude = (float) $data['longitude'];

            if ($latitude === 0.0 && $longitude === 0.0) {
                $data['latitude'] = null;
                $data['longitude'] = null;
            } else {
                $data['latitude'] = $latitude;
                $data['longitude'] = $longitude;
            }
        }

        return $data;
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
            'message' => 'Address operation failed because of a server/database error.',
            'error_reference' => $reference,
        ];

        if (config('app.debug')) {
            $response['debug'] = $error->getMessage();
        }

        return response()->json($response, 500);
    }
}
