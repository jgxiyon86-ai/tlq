<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\ManualPage;
use App\Models\Series;

class ManualController extends Controller
{
    /**
     * Get manual pages (book mode) for a specific series.
     */
    public function getBySeries($series_id)
    {
        $pages = ManualPage::where('series_id', $series_id)
            ->orderBy('page_number')
            ->get();

        if ($pages->isEmpty()) {
            return response()->json(['message' => 'Halaman panduan belum tersedia.'], 404);
        }

        return response()->json($pages);
    }
}
