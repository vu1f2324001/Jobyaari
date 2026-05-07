<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminCkeditorUploadController
{
    public function upload(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'upload' => ['required', 'image', 'max:5120'],
        ]);

        $file = $payload['upload'];

        // Store in 'storage/app/public/uploads/content' and get back the relative path
        $path = $file->store('uploads/content', 'public');

        // CKEditor 5 typical upload response shape: { url: "..." }
        // Using a relative URL ensures the image loads correctly on any domain/port
        return response()->json([
            'url' => '/storage/' . $path,
        ]);
    }
}
