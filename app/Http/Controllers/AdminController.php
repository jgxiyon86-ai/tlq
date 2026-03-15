<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Series;
use App\Models\License;
use App\Models\User;
use App\Models\Challenge;
use App\Models\JournalEntry;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function dashboard()
    {
        $series = Series::withCount('licenses')->get();
        
        // Stats untuk Dashboard
        $totalUsers = User::count();
        $totalLicenses = License::count();
        $activeChallenges = Challenge::where('is_completed', false)->count();
        $totalJournalEntries = JournalEntry::count();

        // Chart Data Pendaftaran User (7 hari terakhir)
        $chartUserLabels = [];
        $chartUserData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $chartUserLabels[] = $date->format('d M');
            $chartUserData[] = User::whereDate('created_at', $date->format('Y-m-d'))->count();
        }

        // Chart Data Aktivitas Jurnal (7 hari terakhir)
        $chartActLabels = [];
        $chartActData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $chartActLabels[] = $date->format('d M');
            $chartActData[] = JournalEntry::whereDate('created_at', $date->format('Y-m-d'))->count();
        }

        // Data Detil untuk Tabel
        $activeChallengesList = Challenge::with(['user', 'series'])
            ->where('is_completed', false)
            ->latest()
            ->limit(10)
            ->get();

        $recentJournalEntries = JournalEntry::with(['user', 'content'])
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'series', 'totalUsers', 'totalLicenses', 'activeChallenges', 'totalJournalEntries',
            'chartUserLabels', 'chartUserData', 'chartActLabels', 'chartActData'
        ));
    }

    public function monitoring()
    {
        $activeChallengesList = Challenge::with(['user', 'series'])
            ->where('is_completed', false)
            ->latest()
            ->paginate(50);

        $recentJournalEntries = JournalEntry::with(['user', 'content'])
            ->latest()
            ->paginate(50);

        return view('admin.monitoring', compact('activeChallengesList', 'recentJournalEntries'));
    }

    public function licenses(Request $request)
    {
        $series = Series::all();
        $query = License::with('series')->latest();

        if ($request->has('series_id') && $request->series_id != '') {
            $query->where('series_id', $request->series_id);
        }

        $perPage = $request->input('per_page', 20);
        $licenses = $query->paginate($perPage)->appends($request->all());
        return view('admin.licenses', compact('series', 'licenses'));
    }

    public function generateLicenses(Request $request)
    {
        $request->validate([
            'series_id' => 'required|exists:series,id',
            'count' => 'required|integer|min:1|max:50'
        ]);

        for ($i = 0; $i < $request->count; $i++) {
            License::create([
                'license_key' => strtoupper(Str::random(10)), // Simple code for QR
                'series_id' => $request->series_id,
                'is_activated' => false
            ]);
        }

        return back()->with('success', $request->count . ' lisensi baru berhasil dibuat!');
    }

    public function printLicense($id)
    {
        $license = License::with('series')->findOrFail($id);
        
        // Return a simple view that shows the QR in a small format
        $license->increment('print_count');
        return view('admin.print-qr', compact('license'));
    }

    public function bulkPrint(Request $request)
    {
        $request->validate([
            'license_ids' => 'required|array',
            'license_ids.*' => 'exists:licenses,id'
        ]);

        $licenses = License::with('series')->whereIn('id', $request->license_ids)->get();
        
        // Increase print count
        foreach ($licenses as $license) {
            $license->increment('print_count');
        }

        return view('admin.print-bulk-qr', compact('licenses'));
    }
}
