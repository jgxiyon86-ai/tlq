<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\License;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    /**
     * Get all contents for a specific series. (Used for offline caching)
     */
    public function index(Request $request)
    {
        $request->validate([
            'series_id' => 'required|exists:series,id',
        ]);

        $contents = Content::where('series_id', $request->series_id)->get();

        return response()->json([
            'contents' => $contents
        ]);
    }

    /**
     * Get a random content (shake feature) for a specific license.
     */
    public function shake(Request $request)
    {
        $request->validate([
            'license_key' => 'required|string',
            'device_id'   => 'required|string',
        ]);

        // Verify the user owns this license and it's activated
        $license = $request->user()->licenses()
            ->where('license_key', $request->license_key)
            ->where('is_activated', true)
            ->first();

        if (!$license) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke Jar ini atau lisensi belum diaktifkan.'], 403);
        }

        // Note: device_id check removed — ownership + activation is sufficient protection


        $content = Content::where('series_id', $license->series_id)
            ->inRandomOrder()
            ->first();

        if (!$content) {
            return response()->json(['message' => 'Konten belum tersedia untuk series ini.'], 404);
        }

        return response()->json($content);
    }
}
