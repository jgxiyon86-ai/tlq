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
        ]);

        // Verify the user owns this license
        $license = $request->user()->licenses()
            ->where('license_key', $request->license_key)
            ->first();

        if (!$license) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke Jar ini.'], 403);
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
