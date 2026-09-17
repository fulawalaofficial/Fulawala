<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetCodeMail;
use App\Models\Address;
use App\Models\PasswordResetOtp;
use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new Flower Delivery customer.
     */
    public function register(
        Request $request
    ): JsonResponse {
        $data = $request->validate(
            array_merge(
                [
                    'name' => [
                        'required',
                        'string',
                        'max:255',
                    ],
                    'mobile' => [
                        'required',
                        'string',
                        'regex:/^[0-9]{10}$/',
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
                        'max:128',
                        Password::min(8)
                            ->letters()
                            ->numbers()
                            ->symbols(),
                        'confirmed',
                    ],
                    'terms_accepted' => [
                        'required',
                        'accepted',
                    ],
                    'address' => [
                        'nullable',
                        'string',
                    ],
                ],
                $this->deviceValidationRules()
            )
        );

        $result = DB::transaction(
            function () use (
                $data,
                $request
            ): array {
                $user = User::create([
                    'name' =>
                        $data['name'],
                    'mobile' =>
                        $data['mobile'],
                    'email' =>
                        strtolower(
                            $data['email']
                        ),
                    'password' =>
                        Hash::make(
                            $data['password']
                        ),
                    'role' => 'customer',
                    'status' => 'Active',
                    'last_login_at' =>
                        now(),
                    'last_login_ip' =>
                        $request->ip(),
                ]);

                if (
                    !empty(
                        $data['address']
                    )
                ) {
                    Address::create([
                        'user_id' =>
                            $user->id,
                        'address_type' =>
                            'home',
                        'name' => 'Home',
                        'number' => '',
                        'address' =>
                            $data['address'],
                        'city' => '',
                        'state' => '',
                        'pincode' => '',
                        'landmark' => null,
                        'is_default' =>
                            true,
                    ]);
                }

                $newToken =
                    $user->createToken(
                        'mobile:' .
                            substr(
                                $data[
                                    'device_id'
                                ],
                                0,
                                80
                            )
                    );

                $device =
                    $this
                        ->storeLoginDevice(
                            user: $user,
                            data: $data,
                            request:
                                $request,
                            sanctumTokenId:
                                (int)
                                $newToken
                                    ->accessToken
                                    ->getKey()
                        );

                return [
                    'user' => $user,
                    'device' => $device,
                    'token' =>
                        $newToken
                            ->plainTextToken,
                ];
            }
        );

        return response()->json(
            [
                'status' => true,
                'message' =>
                    'Registration successful.',
                'user' =>
                    $result['user'],
                'device' =>
                    $result['device'],
                'token' =>
                    $result['token'],
            ],
            201
        );
    }

    /**
     * Login customer.
     */
    public function login(
        Request $request
    ): JsonResponse {
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
            ->where(
                'email',
                strtolower(
                    $data['email']
                )
            )
            ->where(
                'role',
                'customer'
            )
            ->first();

        if (
            !$user ||
            !Hash::check(
                $data['password'],
                $user->password
            )
        ) {
            throw ValidationException
                ::withMessages([
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
            return response()->json(
                [
                    'status' => false,
                    'message' =>
                        'Your account is inactive. Please contact support.',
                ],
                403
            );
        }

        $result = DB::transaction(
            function () use (
                $user,
                $data,
                $request
            ): array {
                $user->forceFill([
                    'last_login_at' =>
                        now(),
                    'last_login_ip' =>
                        $request->ip(),
                ])->save();

                $newToken =
                    $user->createToken(
                        'mobile:' .
                            substr(
                                $data[
                                    'device_id'
                                ],
                                0,
                                80
                            )
                    );

                $device =
                    $this
                        ->storeLoginDevice(
                            user: $user,
                            data: $data,
                            request:
                                $request,
                            sanctumTokenId:
                                (int)
                                $newToken
                                    ->accessToken
                                    ->getKey()
                        );

                return [
                    'device' =>
                        $device,
                    'token' =>
                        $newToken
                            ->plainTextToken,
                ];
            }
        );

        return response()->json([
            'status' => true,
            'message' =>
                'Login successful.',
            'user' =>
                $user->fresh(),
            'device' =>
                $result['device'],
            'token' =>
                $result['token'],
        ]);
    }

    /**
     * Send a short-lived password reset code.
     *
     * The response is deliberately generic so callers cannot use this
     * endpoint to discover which email addresses are registered.
     */
    public function forgotPassword(
        Request $request
    ): JsonResponse {
        $data = $request->validate([
            'email' => [
                'required',
                'email',
                'max:255',
            ],
        ]);

        $email = strtolower(
            trim((string) $data['email'])
        );

        $user = User::query()
            ->where('email', $email)
            ->where('role', 'customer')
            ->first();

        if ($user) {
            $code = (string) random_int(
                100000,
                999999
            );

            DB::transaction(
                function () use (
                    $email,
                    $code
                ): void {
                    PasswordResetOtp::query()
                        ->where('email', $email)
                        ->delete();

                    PasswordResetOtp::create([
                        'email' => $email,
                        'otp_hash' => Hash::make(
                            $code
                        ),
                        'attempts' => 0,
                        'expires_at' => now()
                            ->addMinutes(10),
                    ]);
                }
            );

            Mail::to($user->email)->send(
                new PasswordResetCodeMail(
                    code: $code,
                    expiresInMinutes: 10
                )
            );
        }

        return response()->json([
            'status' => true,
            'message' =>
                'If this email belongs to a customer account, a password reset code has been sent.',
        ]);
    }

    /**
     * Reset the password with the emailed one-time code.
     */
    public function resetPassword(
        Request $request
    ): JsonResponse {
        $data = $request->validate([
            'email' => [
                'required',
                'email',
                'max:255',
            ],
            'otp' => [
                'required',
                'digits:6',
            ],
            'password' => [
                'required',
                'string',
                'max:128',
                Password::min(8)
                    ->letters()
                    ->numbers()
                    ->symbols(),
                'confirmed',
            ],
        ]);

        $email = strtolower(
            trim((string) $data['email'])
        );

        $user = User::query()
            ->where('email', $email)
            ->where('role', 'customer')
            ->first();

        $reset = PasswordResetOtp::query()
            ->where('email', $email)
            ->latest('id')
            ->first();

        $invalidReset =
            !$user ||
            !$reset ||
            $reset->expires_at?->isPast() ||
            $reset->attempts >= 5;

        if ($invalidReset) {
            if ($reset) {
                $reset->delete();
            }

            throw ValidationException
                ::withMessages([
                    'otp' => [
                        'Invalid or expired reset code. Please request a new code.',
                    ],
                ]);
        }

        if (
            !Hash::check(
                (string) $data['otp'],
                $reset->otp_hash
            )
        ) {
            $reset->increment('attempts');

            if (
                ((int) $reset->attempts + 1) >= 5
            ) {
                $reset->delete();
            }

            throw ValidationException
                ::withMessages([
                    'otp' => [
                        'Invalid or expired reset code. Please request a new code.',
                    ],
                ]);
        }

        if (
            Hash::check(
                $data['password'],
                $user->password
            )
        ) {
            throw ValidationException
                ::withMessages([
                    'password' => [
                        'Please choose a password different from your current password.',
                    ],
                ]);
        }

        DB::transaction(
            function () use (
                $user,
                $email,
                $data
            ): void {
                $user->forceFill([
                    'password' => Hash::make(
                        $data['password']
                    ),
                ])->save();

                // Log out every old session after a password reset.
                $user->tokens()->delete();

                UserDevice::query()
                    ->where(
                        'user_id',
                        $user->id
                    )
                    ->update([
                        'sanctum_token_id' => null,
                        'fcm_token' => null,
                        'fcm_token_hash' => null,
                        'notifications_enabled' => false,
                        'is_active' => false,
                        'last_seen_at' => now(),
                        'logged_out_at' => now(),
                        'updated_at' => now(),
                    ]);

                PasswordResetOtp::query()
                    ->where('email', $email)
                    ->delete();
            }
        );

        return response()->json([
            'status' => true,
            'message' =>
                'Password reset successful. Please sign in with your new password.',
        ]);
    }

    /**
     * Update Firebase/device token.
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

        $currentToken =
            $user->currentAccessToken();

        $device = DB::transaction(
            function () use (
                $user,
                $data,
                $request,
                $currentToken
            ): UserDevice {
                return $this
                    ->storeLoginDevice(
                        user: $user,
                        data: $data,
                        request: $request,
                        sanctumTokenId:
                            (int)
                            $currentToken
                                ->getKey(),
                        markAsNewLogin:
                            false
                    );
            }
        );

        return response()->json([
            'status' => true,
            'message' =>
                'Notification device updated successfully.',
            'device' => $device,
        ]);
    }

    /**
     * Logout current device.
     */
    public function logout(
        Request $request
    ): JsonResponse {
        $request->validate([
            'device_id' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        /** @var User|null $user */
        $user = $request->user();

        $currentToken =
            $user?->currentAccessToken();

        if ($user && $currentToken) {
            $deviceQuery =
                UserDevice::query()
                    ->where(
                        'user_id',
                        $user->id
                    )
                    ->where(
                        'sanctum_token_id',
                        $currentToken
                            ->getKey()
                    );

            if (
                !$deviceQuery->exists() &&
                $request->filled(
                    'device_id'
                )
            ) {
                $deviceQuery =
                    UserDevice::query()
                        ->where(
                            'user_id',
                            $user->id
                        )
                        ->where(
                            'device_id',
                            $request
                                ->string(
                                    'device_id'
                                )
                                ->toString()
                        );
            }

            $deviceQuery->update([
                'sanctum_token_id' =>
                    null,
                'fcm_token' => null,
                'fcm_token_hash' =>
                    null,
                'notifications_enabled' =>
                    false,
                'is_active' => false,
                'last_seen_at' => now(),
                'logged_out_at' => now(),
                'updated_at' => now(),
            ]);

            $currentToken->delete();
        }

        return response()->json([
            'status' => true,
            'message' =>
                'Logged out successfully.',
        ]);
    }

    /**
     * Save/update device information.
     */
    private function storeLoginDevice(
        User $user,
        array $data,
        Request $request,
        int $sanctumTokenId,
        bool $markAsNewLogin = true
    ): UserDevice {
        $deviceId =
            $data['device_id'];

        $fcmTokenProvided =
            array_key_exists(
                'fcm_token',
                $data
            );

        $fcmToken =
            $fcmTokenProvided
                ? $data['fcm_token']
                : null;

        $fcmTokenHash =
            !empty($fcmToken)
                ? hash(
                    'sha256',
                    $fcmToken
                )
                : null;

        $existingDevice =
            UserDevice::query()
                ->where(
                    'user_id',
                    $user->id
                )
                ->where(
                    'device_id',
                    $deviceId
                )
                ->first();

        if (
            $existingDevice
                ?->sanctum_token_id &&
            (int)
            $existingDevice
                ->sanctum_token_id !==
                $sanctumTokenId
        ) {
            DB::table(
                'personal_access_tokens'
            )
                ->where(
                    'id',
                    $existingDevice
                        ->sanctum_token_id
                )
                ->delete();
        }

        $conflictingDevices =
            UserDevice::query()
                ->where(
                    function (
                        $query
                    ) use (
                        $deviceId,
                        $fcmTokenHash
                    ): void {
                        $query->where(
                            'device_id',
                            $deviceId
                        );

                        if (
                            $fcmTokenHash
                        ) {
                            $query
                                ->orWhere(
                                    'fcm_token_hash',
                                    $fcmTokenHash
                                );
                        }
                    }
                )
                ->where(
                    function (
                        $query
                    ) use (
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
                    }
                )
                ->get([
                    'id',
                    'sanctum_token_id',
                ]);

        $oldSanctumTokenIds =
            $conflictingDevices
                ->pluck(
                    'sanctum_token_id'
                )
                ->filter()
                ->map(
                    fn ($id) =>
                        (int) $id
                )
                ->values();

        if (
            $oldSanctumTokenIds
                ->isNotEmpty()
        ) {
            DB::table(
                'personal_access_tokens'
            )
                ->whereIn(
                    'id',
                    $oldSanctumTokenIds
                        ->all()
                )
                ->delete();
        }

        if (
            $conflictingDevices
                ->isNotEmpty()
        ) {
            UserDevice::query()
                ->whereIn(
                    'id',
                    $conflictingDevices
                        ->pluck('id')
                        ->all()
                )
                ->update([
                    'sanctum_token_id' =>
                        null,
                    'fcm_token' => null,
                    'fcm_token_hash' =>
                        null,
                    'notifications_enabled' =>
                        false,
                    'is_active' => false,
                    'logged_out_at' =>
                        now(),
                    'updated_at' =>
                        now(),
                ]);
        }

        $values = [
            'sanctum_token_id' =>
                $sanctumTokenId,
            'last_ip_address' =>
                $request->ip(),
            'user_agent' =>
                $request->userAgent(),
            'is_active' => true,
            'last_seen_at' => now(),
            'logged_out_at' => null,
        ];

        if ($markAsNewLogin) {
            $values[
                'logged_in_at'
            ] = now();
        }

        if ($fcmTokenProvided) {
            $values['fcm_token'] =
                $fcmToken;
            $values[
                'fcm_token_hash'
            ] = $fcmTokenHash;
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

        foreach (
            $optionalFields
            as $field
        ) {
            if (
                array_key_exists(
                    $field,
                    $data
                )
            ) {
                $values[$field] =
                    $data[$field];
            }
        }

        return $user
            ->devices()
            ->updateOrCreate(
                [
                    'device_id' =>
                        $deviceId,
                ],
                $values
            );
    }

    /**
     * Required/optional device fields.
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
