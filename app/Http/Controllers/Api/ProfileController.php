<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ProfileController extends Controller
{
    /**
     * Return the authenticated user's profile.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return $this->unauthenticatedResponse();
        }

        return response()->json([
            'status' => true,
            'message' => 'Profile fetched successfully.',
            'data' => $user->load('addresses'),
        ]);
    }

    /**
     * Return the authenticated user's current profile-photo information.
     */
    public function getPhoto(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return $this->unauthenticatedResponse();
        }

        return response()->json([
            'status' => true,
            'message' => $user->profile_photo
                ? 'Profile photo fetched successfully.'
                : 'No profile photo is available.',
            'data' => [
                'profile_photo' => $user->profile_photo,
                'profile_photo_url' => $user->profile_photo_url,
            ],
        ]);
    }

    /**
     * Upload or replace the authenticated user's profile photo.
     *
     * The image is saved directly in:
     * public/uploads/profile-photos
     *
     * This approach works reliably on shared hosting and does not require
     * the "php artisan storage:link" symbolic link.
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

        $uploadDirectory = public_path('uploads/profile-photos');
        $oldPhotoPath = $user->profile_photo;
        $newAbsolutePath = null;

        try {
            if (!is_dir($uploadDirectory)) {
                $created = mkdir($uploadDirectory, 0755, true);

                if (!$created && !is_dir($uploadDirectory)) {
                    throw new RuntimeException(
                        'Unable to create public/uploads/profile-photos directory.'
                    );
                }
            }

            if (!is_writable($uploadDirectory)) {
                throw new RuntimeException(
                    'The public/uploads/profile-photos directory is not writable.'
                );
            }

            $extension = strtolower(
                $uploadedFile->guessExtension()
                ?: $uploadedFile->getClientOriginalExtension()
                ?: 'jpg'
            );

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($extension, $allowedExtensions, true)) {
                $extension = 'jpg';
            }

            $filename = sprintf(
                'user-%d-%s.%s',
                $user->id,
                Str::uuid()->toString(),
                $extension
            );

            $uploadedFile->move($uploadDirectory, $filename);

            $newRelativePath = 'uploads/profile-photos/' . $filename;
            $newAbsolutePath = public_path($newRelativePath);

            if (!is_file($newAbsolutePath)) {
                throw new RuntimeException(
                    'The uploaded file could not be found after saving.'
                );
            }

            try {
                $user->forceFill([
                    'profile_photo' => $newRelativePath,
                ])->saveOrFail();
            } catch (Throwable $databaseException) {
                if (is_file($newAbsolutePath)) {
                    @unlink($newAbsolutePath);
                }

                throw $databaseException;
            }

            // Delete the previous photo only after the new database value is saved.
            $this->deletePhysicalPhoto($oldPhotoPath);

            $freshUser = $user->fresh(['addresses']);

            return response()->json([
                'status' => true,
                'message' => 'Profile photo updated successfully.',
                'data' => $freshUser,
            ]);
        } catch (Throwable $exception) {
            if (
                $newAbsolutePath &&
                is_file($newAbsolutePath) &&
                $user->profile_photo !== 'uploads/profile-photos/' . basename($newAbsolutePath)
            ) {
                @unlink($newAbsolutePath);
            }

            Log::error('Profile photo upload failed.', [
                'user_id' => $user->id,
                'uploaded_file_name' => $uploadedFile->getClientOriginalName(),
                'uploaded_file_size' => $uploadedFile->getSize(),
                'uploaded_file_mime' => $uploadedFile->getMimeType(),
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
     * Delete the authenticated user's profile photo.
     */
    public function deletePhoto(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return $this->unauthenticatedResponse();
        }

        $oldPhotoPath = $user->profile_photo;

        if (!$oldPhotoPath) {
            return response()->json([
                'status' => true,
                'message' => 'No profile photo is available to delete.',
                'data' => $user->load('addresses'),
            ]);
        }

        try {
            $user->forceFill([
                'profile_photo' => null,
            ])->saveOrFail();

            $this->deletePhysicalPhoto($oldPhotoPath);

            return response()->json([
                'status' => true,
                'message' => 'Profile photo deleted successfully.',
                'data' => $user->fresh(['addresses']),
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
     * Delete both the new public-upload format and the old storage-disk format.
     */
    private function deletePhysicalPhoto(?string $photoPath): void
    {
        if (!$photoPath) {
            return;
        }

        $filename = basename(parse_url($photoPath, PHP_URL_PATH) ?: $photoPath);

        if (!$filename || $filename === '.' || $filename === '..') {
            return;
        }

        $publicFile = public_path('uploads/profile-photos/' . $filename);

        if (is_file($publicFile)) {
            @unlink($publicFile);
        }

        // Backward compatibility with files previously stored on the public disk.
        $legacyStorageFile = 'profile-photos/' . $filename;

        if (Storage::disk('public')->exists($legacyStorageFile)) {
            Storage::disk('public')->delete($legacyStorageFile);
        }
    }

    private function unauthenticatedResponse(): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => 'Unauthenticated.',
        ], 401);
    }
}
