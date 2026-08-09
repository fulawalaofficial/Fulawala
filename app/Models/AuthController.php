<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new customer and save device information.
     */
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate(
            array_merge(
                [
                    'name' => [
                        'required',
                        'string',
                        'max:255',
                    ],
                    'mobile' => [
                        'nullable',
                        'string',
                        'max:20',
                    ],
                    'email' => [
                        'required',
                        'email',
                        'max:255',
                        'unique:users,email',
                    ],
                    'password' => [
                        'required',
                        'string',
                        'min:6',
                    ],
                    'address' => [
                        'nullable',
                        'string',
                    ],
                ],
                $this->deviceValidationRules()
            )
        );

        $result = DB::transaction(function () use (
            $data,
            $request
        ): array {
            $user = User::create([
                'name' => $data['name'],
                'mobile' => $data['mobile'] ?? null,
                'email' => $data['email'],
                'password' => Hash::make(
                    $data['password']
                ),
                'role' => 'customer',
                'status' => 'Active',
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            if (!empty($data['address'])) {
                Address::create([
                    'user_id' => $user->id,
                    'address_type' => 'home',
                    'name' => 'Home',
                    'number' => '',
                    'address' => $data['address'],
                    'city' => '',
                    'state' => '',
                    'pincode' => '',
                    'landmark' => null,
                    'is_default' => true,
                ]);
            }

            $newToken = $user->createToken(
                'mobile:' . substr(
                    $data['device_id'],
                    0,
                    80
                )
            );

            $device = $this->storeLoginDevice(
                user: $user,
                data: $data,
                request: $request,
                sanctumTokenId: (int) $newToken
                    ->accessToken
                    ->getKey()
            );

            return [
                'user' => $user,
                'device' => $device,
                'token' => $newToken->plainTextToken,
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Registration successful.',
            'user' => $result['user'],
            'device' => $result['device'],
            'token' => $result['token'],
        ], 201);
    }

    /**
     * Login customer and save login/device information.
     */
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate(
            array_merge(
                [
                    'email' => [
                        'required',
                        'email',
                    ],
                    'password' => [
                        'required',
                        'string',
                    ],
                ],
                $this->deviceValidationRules()
            )
        );

        $user = User::query()
            ->where('email', $data['email'])
            ->where('role', 'customer')
            ->first();

        if (
            !$user ||
            !Hash::check(
                $data['password'],
                $user->password
            )
        ) {
            throw ValidationException::withMessages([
                'email' => [
                    'Invalid customer credentials.',
                ],
            ]);
        }

        if (
            strcasecmp(
                (string) $user->status,
                'Active'
            ) !== 0
        ) {
            return response()->json([
                'status' => false,
                'message' => 'Your account is inactive. Please contact support.',
            ], 403);
        }

        $result = DB::transaction(function () use (
            $user,
            $data,
            $request
        ): array {
            $user->forceFill([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ])->save();

            $newToken = $user->createToken(
                'mobile:' . substr(
                    $data['device_id'],
                    0,
                    80
                )
            );

            $device = $this->storeLoginDevice(
                user: $user,
                data: $data,
                request: $request,
                sanctumTokenId: (int) $newToken
                    ->accessToken
                    ->getKey()
            );

            return [
                'device' => $device,
                'token' => $newToken->plainTextToken,
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Login successful.',
            'user' => $user->fresh(),
            'device' => $result['device'],
            'token' => $result['token'],
        ]);
    }

    /**
     * Update the FCM token when Firebase refreshes it.
     *
     * Call this API after login whenever the mobile application
     * receives a new FCM token.
     */
    public function updateDeviceToken(
        Request $request
    ): JsonResponse {
        $data = $request->validate([
            'device_id' => [
                'required',
                'string',
                'max:255',
            ],
            'fcm_token' => [
                'nullable',
                'string',
                'max:4096',
            ],
            'notifications_enabled' => [
                'required',
                'boolean',
            ],
            'device_name' => [
                'nullable',
                'string',
                'max:255',
            ],
            'device_model' => [
                'nullable',
                'string',
                'max:255',
            ],
            'platform' => [
                'nullable',
                'string',
                'in:android,ios,web',
            ],
            'os_version' => [
                'nullable',
                'string',
                'max:50',
            ],
            'app_version' => [
                'nullable',
                'string',
                'max:50',
            ],
            'timezone' => [
                'nullable',
                'string',
                'max:100',
            ],
            'locale' => [
                'nullable',
                'string',
                'max:20',
            ],
        ]);

        /** @var User $user */
        $user = $request->user();

        $currentToken = $user->currentAccessToken();

        $device = DB::transaction(function () use (
            $user,
            $data,
            $request,
            $currentToken
        ): UserDevice {
            return $this->storeLoginDevice(
                user: $user,
                data: $data,
                request: $request,
                sanctumTokenId: (int) $currentToken->getKey(),
                markAsNewLogin: false
            );
        });

        return response()->json([
            'status' => true,
            'message' => 'Notification device updated successfully.',
            'device' => $device,
        ]);
    }

    /**
     * Logout only the current device.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->validate([
            'device_id' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        /** @var User|null $user */
        $user = $request->user();

        $currentToken = $user?->currentAccessToken();

        if ($user && $currentToken) {
            $deviceQuery = UserDevice::query()
                ->where('user_id', $user->id)
                ->where(
                    'sanctum_token_id',
                    $currentToken->getKey()
                );

            /*
             * Fallback in case an older device record does not have
             * its Sanctum token ID stored.
             */
            if (
                !$deviceQuery->exists() &&
                $request->filled('device_id')
            ) {
                $deviceQuery = UserDevice::query()
                    ->where('user_id', $user->id)
                    ->where(
                        'device_id',
                        $request->string('device_id')
                            ->toString()
                    );
            }

            $deviceQuery->update([
                'sanctum_token_id' => null,
                'fcm_token' => null,
                'fcm_token_hash' => null,
                'notifications_enabled' => false,
                'is_active' => false,
                'last_seen_at' => now(),
                'logged_out_at' => now(),
                'updated_at' => now(),
            ]);

            $currentToken->delete();
        }

        return response()->json([
            'status' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * Save or update the customer's device.
     */
    private function storeLoginDevice(
        User $user,
        array $data,
        Request $request,
        int $sanctumTokenId,
        bool $markAsNewLogin = true
    ): UserDevice {
        $deviceId = $data['device_id'];

        $fcmTokenProvided = array_key_exists(
            'fcm_token',
            $data
        );

        $fcmToken = $fcmTokenProvided
            ? $data['fcm_token']
            : null;

        $fcmTokenHash = !empty($fcmToken)
            ? hash('sha256', $fcmToken)
            : null;

        /*
         * Find the previous login for this same user/device.
         */
        $existingDevice = UserDevice::query()
            ->where('user_id', $user->id)
            ->where('device_id', $deviceId)
            ->first();

        /*
         * Delete the previous Sanctum token for this device.
         *
         * This ensures one active API login token per device.
         */
        if (
            $existingDevice?->sanctum_token_id &&
            (int) $existingDevice->sanctum_token_id !==
                $sanctumTokenId
        ) {
            DB::table('personal_access_tokens')
                ->where(
                    'id',
                    $existingDevice->sanctum_token_id
                )
                ->delete();
        }

        /*
         * Find conflicting records.
         *
         * This can happen when:
         * 1. Another customer logs in on the same phone.
         * 2. Firebase assigns the same active token to a new login.
         */
        $conflictingDevices = UserDevice::query()
            ->where(function ($query) use (
                $deviceId,
                $fcmTokenHash
            ): void {
                $query->where(
                    'device_id',
                    $deviceId
                );

                if ($fcmTokenHash) {
                    $query->orWhere(
                        'fcm_token_hash',
                        $fcmTokenHash
                    );
                }
            })
            ->where(function ($query) use (
                $user,
                $deviceId
            ): void {
                $query
                    ->where(
                        'user_id',
                        '!=',
                        $user->id
                    )
                    ->orWhere(
                        'device_id',
                        '!=',
                        $deviceId
                    );
            })
            ->get([
                'id',
                'sanctum_token_id',
            ]);

        /*
         * Revoke old Sanctum tokens belonging to conflicting
         * device records.
         */
        $oldSanctumTokenIds = $conflictingDevices
            ->pluck('sanctum_token_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values();

        if ($oldSanctumTokenIds->isNotEmpty()) {
            DB::table('personal_access_tokens')
                ->whereIn(
                    'id',
                    $oldSanctumTokenIds->all()
                )
                ->delete();
        }

        if ($conflictingDevices->isNotEmpty()) {
            UserDevice::query()
                ->whereIn(
                    'id',
                    $conflictingDevices
                        ->pluck('id')
                        ->all()
                )
                ->update([
                    'sanctum_token_id' => null,
                    'fcm_token' => null,
                    'fcm_token_hash' => null,
                    'notifications_enabled' => false,
                    'is_active' => false,
                    'logged_out_at' => now(),
                    'updated_at' => now(),
                ]);
        }

        $values = [
            'sanctum_token_id' => $sanctumTokenId,
            'last_ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'is_active' => true,
            'last_seen_at' => now(),
            'logged_out_at' => null,
        ];

        if ($markAsNewLogin) {
            $values['logged_in_at'] = now();
        }

        if ($fcmTokenProvided) {
            $values['fcm_token'] = $fcmToken;
            $values['fcm_token_hash'] =
                $fcmTokenHash;
        }

        $optionalFields = [
            'device_name',
            'device_model',
            'platform',
            'os_version',
            'app_version',
            'timezone',
            'locale',
            'notifications_enabled',
        ];

        foreach ($optionalFields as $field) {
            if (array_key_exists($field, $data)) {
                $values[$field] = $data[$field];
            }
        }

        return $user->devices()->updateOrCreate(
            [
                'device_id' => $deviceId,
            ],
            $values
        );
    }

    /**
     * Device fields expected during registration and login.
     */
    private function deviceValidationRules(): array
    {
        return [
            'device_id' => [
                'required',
                'string',
                'max:255',
            ],
            'fcm_token' => [
                'nullable',
                'string',
                'max:4096',
            ],
            'device_name' => [
                'nullable',
                'string',
                'max:255',
            ],
            'device_model' => [
                'nullable',
                'string',
                'max:255',
            ],
            'platform' => [
                'required',
                'string',
                'in:android,ios,web',
            ],
            'os_version' => [
                'nullable',
                'string',
                'max:50',
            ],
            'app_version' => [
                'nullable',
                'string',
                'max:50',
            ],
            'timezone' => [
                'nullable',
                'string',
                'max:100',
            ],
            'locale' => [
                'nullable',
                'string',
                'max:20',
            ],
            'notifications_enabled' => [
                'nullable',
                'boolean',
            ],
        ];
    }
}