<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
    // ═══════════════════════════════════════════════════════════════
    // DASHBOARD
    // ═══════════════════════════════════════════════════════════════
    public function dashboard()
    {
        $series = Series::withCount('licenses')->get();
        $totalUsers       = User::count();
        $totalLicenses    = License::count();
        $activeChallenges = Challenge::where('is_completed', false)->count();
        $totalJournalEntries = JournalEntry::count();

        $chartUserLabels = []; $chartUserData = [];
        $chartActLabels  = []; $chartActData  = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $chartUserLabels[] = $date->format('d M');
            $chartActLabels[]  = $date->format('d M');
            $start = $date->copy()->startOfDay();
            $end   = $date->copy()->endOfDay();
            $chartUserData[] = User::whereBetween('created_at', [$start, $end])->count();
            $chartActData[]  = JournalEntry::whereBetween('created_at', [$start, $end])->count();
        }

        return view('admin.dashboard', compact(
            'series', 'totalUsers', 'totalLicenses', 'activeChallenges', 'totalJournalEntries',
            'chartUserLabels', 'chartUserData', 'chartActLabels', 'chartActData'
        ));
    }

    // ═══════════════════════════════════════════════════════════════
    // MONITORING CHALLENGES  (+ AJAX search)
    // ═══════════════════════════════════════════════════════════════
    public function monitoringChallenges(Request $request)
    {
        $searchQuery = $request->input('q');

        $totalActive    = Challenge::where('is_completed', false)->count();
        $totalCompleted = Challenge::where('is_completed', true)->count();
        $completionRate = ($totalActive + $totalCompleted) > 0
            ? round(($totalCompleted / ($totalActive + $totalCompleted)) * 100, 1) : 0;

        $anomaliesCount  = Challenge::where('is_completed', false)
            ->where('updated_at', '<', Carbon::now()->subDays(3))->count();
        $liveUsersCount  = JournalEntry::where('updated_at', '>', Carbon::now()->subMinutes(5))
            ->distinct('user_id')->count();

        // --- User Aktif (Challenges) — filter by search, AJAX-ready -----------
        $activeChallengesQuery = Challenge::with(['user', 'series'])
            ->where('is_completed', false);

        if ($searchQuery) {
            $activeChallengesQuery->whereHas('user', fn($q) =>
                $q->where('name', 'like', "%{$searchQuery}%")
                  ->orWhere('email', 'like', "%{$searchQuery}%")
            );
        }

        $activeChallengesList = $activeChallengesQuery->latest()
            ->paginate(15, ['*'], 'challenges_page')
            ->appends(['q' => $searchQuery]);

        foreach ($activeChallengesList as $c) {
            $startDate = $c->started_at ?? $c->created_at;
            if (!$startDate) continue;
            $deadline = $startDate->copy()->startOfDay()->addDays((int)$c->total_days);
            if (now()->startOfDay()->greaterThanOrEqualTo($deadline) && !$c->is_completed) {
                $c->update(['is_completed' => true]);
                $c->is_completed = true;
            }
        }

        // --- Completed Challenges -------------------------------------------------
        $completedChallengesQuery = Challenge::with(['user', 'series'])
            ->where('is_completed', true);

        if ($searchQuery) {
            $completedChallengesQuery->whereHas('user', fn($q) =>
                $q->where('name', 'like', "%{$searchQuery}%")
                  ->orWhere('email', 'like', "%{$searchQuery}%")
            );
        }
        $completedChallengesList = $completedChallengesQuery->latest()
            ->paginate(10, ['*'], 'completed_page')
            ->appends(['q' => $searchQuery]);

        // --- Journal Istiqomah -------------------------------------------------
        $journalsQuery = JournalEntry::with(['user', 'content']);
        if ($searchQuery) {
            $journalsQuery->whereHas('user', fn($q) =>
                $q->where('name', 'like', "%{$searchQuery}%")
            )->orWhereHas('content', fn($q) =>
                $q->where('surah_ayah', 'like', "%{$searchQuery}%")
                  ->orWhere('content_text', 'like', "%{$searchQuery}%")
            );
        }
        $recentJournalEntries = $journalsQuery->latest()
            ->paginate(15, ['*'], 'journals_page')
            ->appends(['q' => $searchQuery]);

        $users  = User::orderBy('name')->get();
        $series = Series::all();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.partials.monitoring_users', compact(
                    'activeChallengesList', 'completedChallengesList'
                ))->render(),
            ]);
        }

        return view('admin.monitoring_challenges', compact(
            'activeChallengesList', 'completedChallengesList',
            'recentJournalEntries', 'totalActive', 'completionRate',
            'liveUsersCount', 'anomaliesCount', 'searchQuery',
            'users', 'series'
        ));
    }

    // ── AJAX: Get challenges for one user (lazy load) ────────────────────────
    public function userChallenges(User $user)
    {
        $challenges = Challenge::with('series')
            ->where('user_id', $user->id)
            ->orderBy('is_completed', 'asc')
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json(['challenges' => $challenges]);
    }

    // ── AJAX: Get journal entries for one challenge (lazy load) ──────────────
    public function challengeJournals(Challenge $challenge)
    {
        $entries = JournalEntry::with('content')
            ->where('challenge_id', $challenge->id)
            ->orderBy('day_number')
            ->get();

        return response()->json(['entries' => $entries]);
    }

    // ── AJAX: Search active users ─────────────────────────────────────────────
    public function searchUsers(Request $request)
    {
        if (!auth()->user()->canMonitorChallenges()) return response()->json([]);
        $q = $request->input('q', '');
        $users = User::where('name', 'like', "%{$q}%")
            ->orWhere('email', 'like', "%{$q}%")
            ->withCount('challenges')
            ->limit(20)
            ->get(['id', 'name', 'email', 'avatar', 'role']);

        return response()->json($users);
    }

    // ═══════════════════════════════════════════════════════════════
    // CREATE / DELETE CHALLENGE
    // ═══════════════════════════════════════════════════════════════
    public function storeChallenge(Request $request)
    {
        $request->validate([
            'user_id'    => 'required|exists:users,id',
            'series_id'  => 'required|exists:series,id',
            'total_days' => 'required|integer|in:7,40',
            'started_at' => 'required|date',
        ]);

        $user      = User::find($request->user_id);
        $totalDays = $request->total_days;
        $startDate = Carbon::parse($request->started_at);

        Challenge::where('user_id', $user->id)
            ->where('series_id', $request->series_id)
            ->where('is_seven_days', $totalDays == 7)
            ->delete();

        try {
            DB::transaction(function () use ($user, $request, $totalDays, $startDate) {
                $challenge = Challenge::create([
                    'user_id'      => $user->id,
                    'series_id'    => $request->series_id,
                    'is_seven_days'=> $totalDays == 7,
                    'total_days'   => (int)$totalDays,
                    'current_day'  => 1,
                    'is_completed' => false,
                    'started_at'   => $startDate,
                ]);
                for ($i = 1; $i <= $totalDays; $i++) {
                    JournalEntry::create([
                        'user_id'      => $user->id,
                        'challenge_id' => $challenge->id,
                        'content_id'   => null,
                        'day_number'   => $i,
                        'entry_date'   => $startDate->copy()->addDays($i - 1)->toDateString(),
                        'is_completed' => false,
                    ]);
                }
            });
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membuat tantangan. Error: ' . $e->getMessage());
        }

        return back()->with('success', 'Tantangan baru berhasil dibuat untuk ' . $user->name);
    }

    public function destroyChallenge(Challenge $challenge)
    {
        $challenge->delete();
        return back()->with('success', 'Tantangan berhasil dihapus.');
    }

    // ═══════════════════════════════════════════════════════════════
    // MONITORING LICENSES
    // ═══════════════════════════════════════════════════════════════
    public function monitoringLicenses()
    {
        $totalLicenses     = License::count();
        $activatedLicenses = License::where('is_activated', true)->count();
        $activationRate    = $totalLicenses > 0
            ? round(($activatedLicenses / $totalLicenses) * 100, 1) : 0;
        $pendingTransfers  = LicenseTransferRequest::where('status', 'pending')->count();

        $transferRequests  = LicenseTransferRequest::with(['license.series', 'requester', 'owner'])
            ->latest()->paginate(25, ['*'], 'transfers_page');
        $recentActivations = License::with(['user', 'series'])
            ->where('is_activated', true)->latest('activated_at')->limit(10)->get();

        return view('admin.monitoring_licenses', compact(
            'transferRequests', 'totalLicenses', 'activatedLicenses',
            'activationRate', 'pendingTransfers', 'recentActivations'
        ));
    }

    // ═══════════════════════════════════════════════════════════════
    // LICENSES
    // ═══════════════════════════════════════════════════════════════
    public function licenses(Request $request)
    {
        $series  = Series::all();
        $query   = License::with(['series', 'user'])->latest();
        if ($request->has('series_id') && $request->series_id != '') {
            $query->where('series_id', $request->series_id);
        }
        $licenses = $query->paginate($request->input('per_page', 20))->appends($request->all());
        return view('admin.licenses', compact('series', 'licenses'));
    }

    public function generateLicenses(Request $request)
    {
        $request->validate([
            'series_id' => 'required|exists:series,id',
            'count'     => 'required|integer|min:1|max:50',
        ]);
        for ($i = 0; $i < $request->count; $i++) {
            License::create([
                'license_key' => strtoupper(Str::random(10)),
                'series_id'   => $request->series_id,
                'is_activated'=> false,
            ]);
        }
        return back()->with('success', $request->count . ' lisensi baru berhasil dibuat!');
    }

    public function printLicense($id)
    {
        $license = License::with('series')->findOrFail($id);
        $license->increment('print_count');
        return view('admin.print-qr', compact('license'));
    }

    public function bulkPrint(Request $request)
    {
        $request->validate(['license_ids' => 'required|array', 'license_ids.*' => 'exists:licenses,id']);
        $licenses = License::with('series')->whereIn('id', $request->license_ids)->get();
        foreach ($licenses as $license) { $license->increment('print_count'); }
        return view('admin.print-bulk-qr', compact('licenses'));
    }

    // ═══════════════════════════════════════════════════════════════
    // USER MANAGEMENT  (Super Admin only)
    // ═══════════════════════════════════════════════════════════════
    public function users(Request $request)
    {
        $this->requireSuperAdmin();
        $q     = $request->input('q', '');
        $query = User::withCount(['challenges', 'licenses']);
        if ($q) {
            $query->where('name', 'like', "%$q%")->orWhere('email', 'like', "%$q%");
        }
        $users = $query->orderBy('name')->paginate(25)->appends(['q' => $q]);
        return view('admin.users.index', compact('users', 'q'));
    }

    public function profilePassword()
    {
        return view('admin.profile.password');
    }

    public function updateProfilePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        auth()->user()->update([
            'password' => \Hash::make($request->password)
        ]);

        return back()->with('success', 'Alhamdulillah, password antum berhasil diubah.');
    }

    public function securityLogs()
    {
        $this->requireSuperAdmin();
        $logs = \DB::table('login_attempts')->latest()->paginate(20);
        $blockedUsers = User::where('is_blocked', true)->get();
        
        return view('admin.security.logs', compact('logs', 'blockedUsers'));
    }

    public function unblockUser(User $user)
    {
        $this->requireSuperAdmin();
        $user->update([
           'is_blocked' => false,
           'failed_login_count' => 0
        ]);
        
        return back()->with('success', "Alhamdulillah, akun {$user->name} berhasil dipulihkan.");
    }

    public function promoteAdmin(Request $request, User $user)
    {
        $this->requireSuperAdmin();
        $request->validate([
            'role'                 => 'required|in:admin,super_admin,user',
            'can_manage_licenses'  => 'boolean',
            'can_manage_contents'  => 'boolean',
            'can_manage_guides'    => 'boolean',
            'can_monitor_challenges' => 'boolean',
        ]);

        $user->update([
            'role'                 => $request->role === 'user' ? null : $request->role,
            'is_admin'             => $request->role !== 'user',
            'can_manage_licenses'  => $request->boolean('can_manage_licenses'),
            'can_manage_contents'  => $request->boolean('can_manage_contents'),
            'can_manage_guides'    => $request->boolean('can_manage_guides'),
            'can_monitor_challenges' => $request->boolean('can_monitor_challenges'),
        ]);

        return back()->with('success', "Role {$user->name} telah diperbarui.");
    }

    public function updateUserPassword(Request $request, User $user)
    {
        $this->requireSuperAdmin();
        $request->validate(['password' => 'required|min:8|confirmed']);
        $user->update(['password' => Hash::make($request->password)]);
        return back()->with('success', "Password {$user->name} berhasil diubah.");
    }

    // ── Private helper ─────────────────────────────────────────────
    private function requireSuperAdmin()
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Hanya Super Admin yang bisa mengakses fitur ini.');
        }
    }
}
