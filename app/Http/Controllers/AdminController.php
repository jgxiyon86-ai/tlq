<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use App\Models\Series;
use App\Models\License;
use App\Models\User;
use App\Models\Challenge;
use App\Models\JournalEntry;
use App\Models\LicenseTransferRequest;
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
            $start = $date->copy()->startOfDay();
            $end = $date->copy()->endOfDay();
            $chartUserData[] = User::whereBetween('created_at', [$start, $end])->count();
        }

        // Chart Data Aktivitas Jurnal (7 hari terakhir)
        $chartActLabels = [];
        $chartActData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $chartActLabels[] = $date->format('d M');
            $start = $date->copy()->startOfDay();
            $end = $date->copy()->endOfDay();
            $chartActData[] = JournalEntry::whereBetween('created_at', [$start, $end])->count();
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

    public function monitoringChallenges(Request $request)
    {
        $searchQuery = $request->input('q');

        // Stats
        $totalActive = Challenge::where('is_completed', false)->count();
        $totalCompleted = Challenge::where('is_completed', true)->count();
        $completionRate = ($totalActive + $totalCompleted) > 0 
            ? round(($totalCompleted / ($totalActive + $totalCompleted)) * 100, 1) 
            : 0;

        $anomaliesCount = Challenge::where('is_completed', false)
            ->where('updated_at', '<', Carbon::now()->subDays(3))
            ->count();

        $liveUsersCount = JournalEntry::where('updated_at', '>', Carbon::now()->subMinutes(5))
            ->distinct('user_id')
            ->count();

        // 1. Data Jemaah Aktif (Challenges) with Filter
        $activeChallengesQuery = Challenge::with(['user', 'series'])
            ->where('is_completed', false);

        if ($searchQuery) {
            $activeChallengesQuery->whereHas('user', function($q) use ($searchQuery) {
                $q->where('name', 'like', "%{$searchQuery}%")
                  ->orWhere('email', 'like', "%{$searchQuery}%");
            });
        }

        $activeChallengesList = $activeChallengesQuery->latest()
            ->paginate(15, ['*'], 'challenges_page')
            ->appends(['q' => $searchQuery]);

        // Run discipline check for the items on the current page
        foreach ($activeChallengesList as $c) {
            $startDate = $c->started_at ?? $c->created_at;
            if (!$startDate) continue;
            
            $deadline = $startDate->copy()->startOfDay()->addDays((int)$c->total_days);
            if (now()->startOfDay()->greaterThanOrEqualTo($deadline) && !$c->is_completed) {
                $c->update(['is_completed' => true]);
                // We don't refresh the list now to avoid pagination issues, 
                // but setting the attribute helps the current view
                $c->is_completed = true;
            }
        }

        // 2. Riwayat Perubahan (Journal Entries) with Filter
        $journalsQuery = JournalEntry::with(['user', 'content']);

        if ($searchQuery) {
            $journalsQuery->whereHas('user', function($q) use ($searchQuery) {
                $q->where('name', 'like', "%{$searchQuery}%");
            })->orWhereHas('content', function($q) use ($searchQuery) {
                $q->where('surah_ayah', 'like', "%{$searchQuery}%")
                  ->orWhere('content_text', 'like', "%{$searchQuery}%");
            });
        }

        $recentJournalEntries = $journalsQuery->latest()
            ->paginate(15, ['*'], 'journals_page')
            ->appends(['q' => $searchQuery]);

        $users = User::orderBy('name')->get();
        $series = Series::all();

        return view('admin.monitoring_challenges', compact(
            'activeChallengesList', 
            'recentJournalEntries', 
            'totalActive',
            'completionRate',
            'liveUsersCount',
            'anomaliesCount',
            'searchQuery',
            'users',
            'series'
        ));
    }

    public function storeChallenge(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'series_id' => 'required|exists:series,id',
            'total_days' => 'required|integer|in:7,40',
            'started_at' => 'required|date',
        ]);

        $user = User::find($request->user_id);
        $totalDays = $request->total_days;
        $startDate = Carbon::parse($request->started_at);

        // Delete existing challenge of the SAME TYPE if any (Bypass for testing)
        Challenge::where('user_id', $user->id)
            ->where('series_id', $request->series_id)
            ->where('is_seven_days', $totalDays == 7)
            ->delete();

        try {
            \DB::transaction(function () use ($user, $request, $totalDays, $startDate) {
                $challenge = Challenge::create([
                    'user_id' => $user->id,
                    'series_id' => $request->series_id,
                    'is_seven_days' => $totalDays == 7,
                    'total_days' => (int)$totalDays,
                    'current_day' => 1,
                    'is_completed' => false,
                    'started_at' => $startDate,
                ]);

                for ($i = 1; $i <= $totalDays; $i++) {
                    JournalEntry::create([
                        'user_id' => $user->id,
                        'challenge_id' => $challenge->id,
                        'content_id' => null,
                        'day_number' => $i,
                        'entry_date' => $startDate->copy()->addDays($i-1)->toDateString(),
                        'is_completed' => false,
                    ]);
                }
            });
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membuat tantangan. Pastikan database sudah dimigrasi (Content ID nullable). Error: ' . $e->getMessage());
        }

        return back()->with('success', 'Tantangan baru berhasil dibuat untuk ' . $user->name);
    }

    public function destroyChallenge(Challenge $challenge)
    {
        $challenge->delete();
        return back()->with('success', 'Tantangan berhasil dihapus.');
    }

    public function monitoringLicenses()
    {
        // Stats for Licenses
        $totalLicenses = License::count();
        $activatedLicenses = License::where('is_activated', true)->count();
        $activationRate = $totalLicenses > 0 ? round(($activatedLicenses / $totalLicenses) * 100, 1) : 0;
        
        $pendingTransfers = LicenseTransferRequest::where('status', 'pending')->count();

        $transferRequests = LicenseTransferRequest::with(['license.series', 'requester', 'owner'])
            ->latest()
            ->paginate(25, ['*'], 'transfers_page');

        $recentActivations = License::with(['user', 'series'])
            ->where('is_activated', true)
            ->latest('activated_at')
            ->limit(10)
            ->get();

        return view('admin.monitoring_licenses', compact(
            'transferRequests',
            'totalLicenses',
            'activatedLicenses',
            'activationRate',
            'pendingTransfers',
            'recentActivations'
        ));
    }

    public function licenses(Request $request)
    {
        $series = Series::all();
        $query = License::with(['series', 'user'])->latest();

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
