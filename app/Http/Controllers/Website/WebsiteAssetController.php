<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class WebsiteAssetController extends Controller
{
    /**
     * Serve Fulawala website assets through Laravel when the hosting
     * document root is public_html instead of laravel_app/public.
     */
    public function show(string $path): BinaryFileResponse
    {
        $relativePath = ltrim(str_replace(["\\", "\0"], ['/', ''], $path), '/');
        $extension = strtolower(pathinfo($relativePath, PATHINFO_EXTENSION));

        $allowedExtensions = [
            'css',
            'js',
            'png',
            'jpg',
            'jpeg',
            'webp',
            'svg',
            'gif',
            'ico',
        ];

        abort_unless(in_array($extension, $allowedExtensions, true), Response::HTTP_NOT_FOUND);

        $assetRoot = realpath(public_path('website'));
        $assetFile = $assetRoot
            ? realpath($assetRoot . DIRECTORY_SEPARATOR . $relativePath)
            : false;

        $validFile = $assetRoot
            && $assetFile
            && is_file($assetFile)
            && strpos($assetFile, $assetRoot . DIRECTORY_SEPARATOR) === 0;

        abort_unless($validFile, Response::HTTP_NOT_FOUND);

        $mimeTypes = [
            'css' => 'text/css; charset=UTF-8',
            'js' => 'application/javascript; charset=UTF-8',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            'gif' => 'image/gif',
            'ico' => 'image/x-icon',
        ];

        return response()->file($assetFile, [
            'Content-Type' => $mimeTypes[$extension],
            'Cache-Control' => 'public, max-age=2592000',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
