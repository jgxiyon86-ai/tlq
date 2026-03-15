<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\License;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    /**
     * Get a random content (shake feature) for a specific license.
     */
    public function shake(Request $request)
    {
        $request->validate([
            'license_key' => 'required|string',
            'device_id'   => 'required|string',
        ]);

        // Verify the user owns this license and is on the correct device
        $license = $request->user()->licenses()
            ->where('license_key', $request->license_key)
            ->first();

        if (!$license) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke Jar ini.'], 403);
        }

        if ($license->device_id !== $request->device_id) {
            return response()->json(['message' => 'Akses Ditolak. Jar ini terdaftar di perangkat lain.'], 403);
        }

        $content = Content::where('series_id', $license->series_id)
            ->inRandomOrder()
            ->first();

        if (!$content) {
            return response()->json(['message' => 'Konten belum tersedia untuk series ini.'], 404);
        }

        return response()->json($content);
    }
}
