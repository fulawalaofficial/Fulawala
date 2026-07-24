<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class ProfileController extends Controller
{
    /**
     * Get authenticated user profile.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return $this->unauthenticatedResponse();
        }

        $freshUser = $user->fresh(['addresses'])
            ?? $user->load('addresses');

        return response()->json([
            'status' => true,
            'message' => 'Profile fetched successfully.',
            'data' => $freshUser,
        ]);
    }

    /**
     * Get the authenticated user's profile-photo information.
     */
    public function getPhoto(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return $this->unauthenticatedResponse();
        }

        $freshUser = $user->fresh() ?? $user;

        return response()->json([
            'status' => true,
            'message' => $freshUser->profile_photo
                ? 'Profile photo fetched successfully.'
                : 'No profile photo is available.',
            'data' => [
                'profile_photo' => $freshUser->profile_photo,
                'profile_photo_url' => $freshUser->profile_photo_url,
                'photo_exists' => $this->physicalPhotoExists(
                    $freshUser->profile_photo
                ),
            ],
        ]);
    }

    /**
     * Serve a profile-photo file through Laravel.
     *
     * This route can remain public because filenames contain UUID values.
     */
    public function photoFile(string $filename): BinaryFileResponse
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
         * New format:
         * public/uploads/profile-photos/filename.jpg
         */
        $publicFile = public_path(
            'uploads/profile-photos/' . $safeFilename
        );

        if (is_file($publicFile)) {
            return response()->file($publicFile, [
                'Cache-Control' => 'public, max-age=86400',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        }

        /*
         * Old format:
         * storage/app/public/profile-photos/filename.jpg
         */
        $legacyRelativePath = 'profile-photos/' . $safeFilename;

        if (Storage::disk('public')->exists($legacyRelativePath)) {
            $legacyFile = Storage::disk('public')->path(
                $legacyRelativePath
            );

            return response()->file($legacyFile, [
                'Cache-Control' => 'public, max-age=86400',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        }

        abort(404, 'Profile photo not found.');
    }

    /**
     * Upload or replace profile photo.
     */
    public function updatePhoto(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return $this->unauthenticatedResponse();
        }

        $validated = $request->validate([
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

        $newRelativePath = null;
        $newAbsolutePath = null;
        $databaseSaved = false;

        try {
            $this->createUploadDirectory($uploadDirectory);

            /*
             * Generate extension from the detected MIME type instead of
             * relying only on the client-provided filename.
             */
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

            $allowedExtensions = [
                'jpg',
                'jpeg',
                'png',
                'webp',
            ];

            if (!in_array($extension, $allowedExtensions, true)) {
                throw new RuntimeException(
                    'Unsupported profile-photo extension.'
                );
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

            $uploadedFile->move(
                $uploadDirectory,
                $filename
            );

            $newRelativePath = 'uploads/profile-photos/' . $filename;
            $newAbsolutePath = public_path($newRelativePath);

            if (!is_file($newAbsolutePath)) {
                throw new RuntimeException(
                    'The uploaded file was not found after saving.'
                );
            }

            /*
             * Make the image readable by the web server.
             */
            @chmod($newAbsolutePath, 0644);

            $user->forceFill([
                'profile_photo' => $newRelativePath,
            ])->saveOrFail();

            $databaseSaved = true;

            /*
             * Delete the old photo only after the new database value
             * has been saved successfully.
             */
            if ($oldPhotoPath !== $newRelativePath) {
                $this->deletePhysicalPhoto($oldPhotoPath);
            }

            $freshUser = $user->fresh(['addresses'])
                ?? $user->load('addresses');

            return response()->json([
                'status' => true,
                'message' => 'Profile photo updated successfully.',
                'data' => $freshUser,
                'profile_photo_url' => $freshUser->profile_photo_url,
            ]);
        } catch (Throwable $exception) {
            /*
             * Delete the newly uploaded file only when the database
             * was not successfully updated.
             */
            if (
                !$databaseSaved &&
                $newAbsolutePath &&
                is_file($newAbsolutePath)
            ) {
                @unlink($newAbsolutePath);
            }

            Log::error('Profile photo upload failed.', [
                'user_id' => $user->id,
                'new_relative_path' => $newRelativePath,
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
            $freshUser = $user->fresh(['addresses'])
                ?? $user->load('addresses');

            return response()->json([
                'status' => true,
                'message' => 'No profile photo is available to delete.',
                'data' => $freshUser,
            ]);
        }

        try {
            /*
             * First remove the database value.
             */
            $user->forceFill([
                'profile_photo' => null,
            ])->saveOrFail();

            /*
             * Then delete the physical file.
             */
            $this->deletePhysicalPhoto($oldPhotoPath);

            $freshUser = $user->fresh(['addresses'])
                ?? $user->load('addresses');

            return response()->json([
                'status' => true,
                'message' => 'Profile photo deleted successfully.',
                'data' => $freshUser,
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
     * Create profile-photo directory when it does not exist.
     */
    private function createUploadDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            $created = mkdir($directory, 0755, true);

            if (!$created && !is_dir($directory)) {
                throw new RuntimeException(
                    'Unable to create the profile-photo upload directory.'
                );
            }
        }

        if (!is_writable($directory)) {
            throw new RuntimeException(
                'The profile-photo upload directory is not writable.'
            );
        }
    }

    /**
     * Check whether the physical photo exists.
     */
    private function physicalPhotoExists(?string $photoPath): bool
    {
        $filename = $this->extractPhotoFilename($photoPath);

        if (!$filename) {
            return false;
        }

        $publicFile = public_path(
            'uploads/profile-photos/' . $filename
        );

        if (is_file($publicFile)) {
            return true;
        }

        return Storage::disk('public')->exists(
            'profile-photos/' . $filename
        );
    }

    /**
     * Delete both new and legacy profile-photo formats.
     */
    private function deletePhysicalPhoto(?string $photoPath): void
    {
        $filename = $this->extractPhotoFilename($photoPath);

        if (!$filename) {
            return;
        }

        try {
            $publicFile = public_path(
                'uploads/profile-photos/' . $filename
            );

            if (is_file($publicFile)) {
                @unlink($publicFile);
            }

            $legacyStorageFile = 'profile-photos/' . $filename;

            if (Storage::disk('public')->exists($legacyStorageFile)) {
                Storage::disk('public')->delete($legacyStorageFile);
            }
        } catch (Throwable $exception) {
            /*
             * Do not fail the API response after the database was
             * already updated. Log the cleanup problem instead.
             */
            Log::warning('Unable to delete physical profile photo.', [
                'profile_photo' => $photoPath,
                'exception_message' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Safely extract a filename from a path or URL.
     */
    private function extractPhotoFilename(?string $photoPath): ?string
    {
        if (!$photoPath) {
            return null;
        }

        $urlPath = parse_url($photoPath, PHP_URL_PATH);
        $filename = basename($urlPath ?: $photoPath);

        if (
            !$filename ||
            in_array($filename, ['.', '..'], true)
        ) {
            return null;
        }

        return $filename;
    }

    /**
     * Unauthenticated JSON response.
     */
    private function unauthenticatedResponse(): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => 'Unauthenticated.',
        ], 401);
    }
}