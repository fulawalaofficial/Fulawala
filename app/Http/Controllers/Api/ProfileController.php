<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class ProfileController extends Controller
{
    /**
     * Return authenticated user's profile.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return $this->unauthenticatedResponse();
        }

        $freshUser = $user->fresh(['addresses']);

        return response()->json([
            'status' => true,
            'message' => 'Profile fetched successfully.',
            'data' => $this->profilePayload($freshUser),
        ]);
    }

    /**
     * Return authenticated user's profile-photo information.
     */
    public function getPhoto(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return $this->unauthenticatedResponse();
        }

        $freshUser = $user->fresh();
        $photoPath = $freshUser?->profile_photo;

        return response()->json([
            'status' => true,
            'message' => $photoPath
                ? 'Profile photo fetched successfully.'
                : 'No profile photo is available.',
            'data' => [
                'profile_photo' => $photoPath,
                'profile_photo_url' => $this->publicPhotoUrl($photoPath),
                'photo_exists' => $this->photoExists($photoPath),
            ],
        ]);
    }

    /**
     * Public endpoint used by the mobile app to display profile images.
     *
     * Example:
     * https://fulawala.com/api/profile-images/user-12-uuid.jpg
     *
     * This route is intentionally PUBLIC because React Native's <Image>
     * does not automatically send the Sanctum Authorization header.
     */
    public function showPhotoFile(string $filename): BinaryFileResponse
    {
        $safeFilename = basename($filename);

        if (
            !$safeFilename ||
            $safeFilename !== $filename ||
            in_array($safeFilename, ['.', '..'], true)
        ) {
            abort(404, 'Profile photo not found.');
        }

        /*
         * Current storage:
         * storage/app/public/profile-photos/{filename}
         */
        $storagePath = 'profile-photos/' . $safeFilename;

        if (Storage::disk('public')->exists($storagePath)) {
            $absolutePath = Storage::disk('public')->path($storagePath);

            return response()->file($absolutePath, [
                'Cache-Control' => 'public, max-age=604800',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        }

        /*
         * Backward compatibility:
         * public/uploads/profile-photos/{filename}
         */
        $oldPublicPath = public_path(
            'uploads/profile-photos/' . $safeFilename
        );

        if (is_file($oldPublicPath)) {
            return response()->file($oldPublicPath, [
                'Cache-Control' => 'public, max-age=604800',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        }

        abort(404, 'Profile photo file does not exist.');
    }

    /**
     * Upload or replace authenticated user's profile photo.
     */
    public function updatePhoto(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return $this->unauthenticatedResponse();
        }

        $request->validate([
            'profile_photo' => [
                'required',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ]);

        $uploadedFile = $request->file('profile_photo');

        if (!$uploadedFile || !$uploadedFile->isValid()) {
            return response()->json([
                'status' => false,
                'message' => 'The uploaded profile photo is invalid.',
            ], 422);
        }

        $oldPhotoPath = $user->profile_photo;
        $newPhotoPath = null;
        $databaseSaved = false;

        try {
            $extension = match ($uploadedFile->getMimeType()) {
                'image/jpeg', 'image/jpg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                default => strtolower(
                    $uploadedFile->guessExtension()
                    ?: $uploadedFile->getClientOriginalExtension()
                    ?: 'jpg'
                ),
            };

            if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unsupported profile-photo format.',
                ], 422);
            }

            if ($extension === 'jpeg') {
                $extension = 'jpg';
            }

            $filename = sprintf(
                'user-%d-%s.%s',
                $user->id,
                Str::uuid()->toString(),
                $extension
            );

            /*
             * Save to:
             * storage/app/public/profile-photos
             */
            $newPhotoPath = $uploadedFile->storeAs(
                'profile-photos',
                $filename,
                'public'
            );

            if (!$newPhotoPath) {
                throw new \RuntimeException(
                    'Unable to save the uploaded profile photo.'
                );
            }

            if (!Storage::disk('public')->exists($newPhotoPath)) {
                throw new \RuntimeException(
                    'Uploaded profile photo was not found after saving.'
                );
            }

            $user->forceFill([
                'profile_photo' => $newPhotoPath,
            ])->saveOrFail();

            $databaseSaved = true;

            if ($oldPhotoPath && $oldPhotoPath !== $newPhotoPath) {
                $this->deletePhysicalPhoto($oldPhotoPath);
            }

            $freshUser = $user->fresh(['addresses']);

            return response()->json([
                'status' => true,
                'message' => 'Profile photo updated successfully.',
                'data' => $this->profilePayload($freshUser),
            ]);
        } catch (Throwable $exception) {
            if (
                !$databaseSaved &&
                $newPhotoPath &&
                Storage::disk('public')->exists($newPhotoPath)
            ) {
                Storage::disk('public')->delete($newPhotoPath);
            }

            Log::error('Profile photo upload failed.', [
                'user_id' => $user->id,
                'profile_photo_path' => $newPhotoPath,
                'original_filename' => $uploadedFile->getClientOriginalName(),
                'mime_type' => $uploadedFile->getMimeType(),
                'file_size' => $uploadedFile->getSize(),
                'exception_message' => $exception->getMessage(),
                'exception_file' => $exception->getFile(),
                'exception_line' => $exception->getLine(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Unable to upload the profile photo.',
                'error' => config('app.debug')
                    ? $exception->getMessage()
                    : null,
            ], 500);
        }
    }

    /**
     * Delete authenticated user's profile photo.
     */
    public function deletePhoto(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return $this->unauthenticatedResponse();
        }

        $oldPhotoPath = $user->profile_photo;

        if (!$oldPhotoPath) {
            $freshUser = $user->fresh(['addresses']);

            return response()->json([
                'status' => true,
                'message' => 'No profile photo is available to delete.',
                'data' => $this->profilePayload($freshUser),
            ]);
        }

        try {
            $user->forceFill([
                'profile_photo' => null,
            ])->saveOrFail();

            $this->deletePhysicalPhoto($oldPhotoPath);

            $freshUser = $user->fresh(['addresses']);

            return response()->json([
                'status' => true,
                'message' => 'Profile photo deleted successfully.',
                'data' => $this->profilePayload($freshUser),
            ]);
        } catch (Throwable $exception) {
            Log::error('Profile photo deletion failed.', [
                'user_id' => $user->id,
                'profile_photo' => $oldPhotoPath,
                'exception_message' => $exception->getMessage(),
                'exception_file' => $exception->getFile(),
                'exception_line' => $exception->getLine(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Unable to delete the profile photo.',
                'error' => config('app.debug')
                    ? $exception->getMessage()
                    : null,
            ], 500);
        }
    }

    /**
     * Convert the Eloquent user model to API data and ALWAYS append
     * a usable public profile_photo_url.
     *
     * This avoids requiring a User-model accessor.
     */
    private function profilePayload(?Model $user): ?array
    {
        if (!$user) {
            return null;
        }

        $data = $user->toArray();

        $data['profile_photo_url'] = $this->publicPhotoUrl(
            $user->getAttribute('profile_photo')
        );

        return $data;
    }

    /**
     * Build the URL for the PUBLIC Laravel image route.
     */
    private function publicPhotoUrl(?string $photoPath): ?string
    {
        $filename = $this->extractFilename($photoPath);

        if (!$filename) {
            return null;
        }

        return route('profile.images.show', [
            'filename' => $filename,
        ]);
    }

    /**
     * Check whether a profile-photo file exists.
     */
    private function photoExists(?string $photoPath): bool
    {
        $filename = $this->extractFilename($photoPath);

        if (!$filename) {
            return false;
        }

        if (
            Storage::disk('public')->exists(
                'profile-photos/' . $filename
            )
        ) {
            return true;
        }

        return is_file(
            public_path('uploads/profile-photos/' . $filename)
        );
    }

    /**
     * Delete both current and previous photo formats.
     */
    private function deletePhysicalPhoto(?string $photoPath): void
    {
        $filename = $this->extractFilename($photoPath);

        if (!$filename) {
            return;
        }

        try {
            $storagePath = 'profile-photos/' . $filename;

            if (Storage::disk('public')->exists($storagePath)) {
                Storage::disk('public')->delete($storagePath);
            }

            $oldPublicPath = public_path(
                'uploads/profile-photos/' . $filename
            );

            if (is_file($oldPublicPath)) {
                @unlink($oldPublicPath);
            }
        } catch (Throwable $exception) {
            Log::warning('Physical profile photo deletion failed.', [
                'profile_photo' => $photoPath,
                'exception_message' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Extract a safe filename from a saved path or URL.
     */
    private function extractFilename(?string $photoPath): ?string
    {
        if (!$photoPath) {
            return null;
        }

        $parsedPath = parse_url($photoPath, PHP_URL_PATH);
        $filename = basename($parsedPath ?: $photoPath);

        if (
            !$filename ||
            in_array($filename, ['.', '..'], true)
        ) {
            return null;
        }

        return $filename;
    }

    private function unauthenticatedResponse(): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => 'Unauthenticated.',
        ], 401);
    }
}
