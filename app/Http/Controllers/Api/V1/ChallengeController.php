<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Challenge;
use App\Models\Content;
use App\Models\JournalEntry;
use App\Models\License;
use App\Models\Series;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ChallengeController extends Controller
{
    /**
     * Activate a challenge for a given series.
     * User must have an active license for that series first.
     */
    public function activate(Request $request)
    {
        $request->validate([
            'series_id' => 'required|integer|exists:series,id',
            'is_seven_days' => 'nullable|boolean',
        ]);

        $user = $request->user();
        $isSevenDays = $request->boolean('is_seven_days');
        $totalDays = $isSevenDays ? 7 : 40;

        // Check if user has an activated license for this series
        $hasLicense = License::where('activated_by', $user->id)
            ->whereHas('series', fn($q) => $q->where('id', $request->series_id))
            ->where('is_activated', true)
            ->exists();

        // Strict Check: User MUST have a license. Cannot bypass with 'confirmed' anymore.
        if (!$hasLicense) {
            return response()->json([
                'message' => 'Anda belum mengaktifkan Jar ' . Series::find($request->series_id)->name . ".\n\nSilahkan hubungi Distributor TLQ anda (08995295781) untuk mendapatkan kode aktivasi.",
            ], 403); 
        }

        // Check if challenge already exists
        $challenge = Challenge::where('user_id', $user->id)
            ->where('series_id', $request->series_id)
            ->first();

        if ($challenge) {
            $daysLabel = $challenge->is_seven_days ? '7' : '40';
            return response()->json([
                'message' => "Tantangan {$daysLabel} hari untuk seri ini sudah aktif!",
                'challenge' => $challenge->load('series'),
            ], 200);
        }

        // Create the challenge
        $challenge = Challenge::create([
            'user_id' => $user->id,
            'series_id' => $request->series_id,
            'is_seven_days' => $isSevenDays,
            'total_days' => $totalDays,
            'current_day' => 1,
            'is_completed' => false,
        ]);

        $daysLabel = $isSevenDays ? '7' : '40';
        return response()->json([
            'message' => "Alhamdulillah! Tantangan {$daysLabel} hari berhasil dimulai!",
            'challenge' => $challenge->load('series'),
        ], 201);
    }

    /**
     * Get the user's active challenges.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Eager load everything needed for the dashboard in ONE go
        $challenges = Challenge::where('user_id', $user->id)
            ->with(['series', 'journalEntries.content'])
            ->orderBy('is_completed', 'asc')
            ->orderBy('updated_at', 'desc')
            ->get();

        foreach ($challenges as $c) {
            // Since we eager loaded journalEntries, this will find it in the collection 
            // without hitting the database again if we use collection methods
            $day = $c->current_day;
            $entry = $c->journalEntries->firstWhere('day_number', $day);
            
            $c->today_entry = $entry;
            
            // Check if user has an activated license for this series
            $c->has_license = License::where('activated_by', $user->id)
                ->where('series_id', $c->series_id)
                ->where('is_activated', true)
                ->exists();

            $c->setAttribute('debt_days', $this->getDebtDays($c));
        }

        return response()->json(['challenges' => $challenges]);
    }

    private function getDebtDays(Challenge $c)
    {
        $startedAt = $c->started_at ?? $c->created_at;
        if (!$startedAt) return 0;

        // Use calendar days (Start of Day comparison)
        $targetDay = $startedAt->copy()->startOfDay()->diffInDays(now()->startOfDay()) + 1;
        
        // If challenge is completed or final reflections are done, debt is effectively 0
        if ($c->is_completed) return 0;
        
        // Debt = Target Day - current_day (what they SHOULD be on vs what they ARE on)
        return (int) max(0, $targetDay - $c->current_day);
    }


    /**
     * Roll (get a random ayah) for the current day's journal.
     * This locks the content for the day.
     */
    public function rollContent(Request $request, Challenge $challenge)
    {
        // Use loose comparison to avoid int vs string type mismatch
        if ($challenge->user_id != $request->user()->id) {
            return response()->json(['message' => 'Unauthorized Access'], 403);
        }

        if ($challenge->is_completed) {
            return response()->json(['message' => 'Tantangan ini sudah selesai! MasyaAllah.'], 422);
        }

        // Check: User must have an ACTIVATED license for this series
        $license = License::where('activated_by', $request->user()->id)
            ->where('series_id', (string)$challenge->series_id) // Cast to string to be safe
            ->where('is_activated', true)
            ->first();

        if (!$license) {
            return response()->json([
                'message' => 'Akses Terkunci. Anda harus mengaktivasi Jar seri ini terlebih dahulu untuk mendapatkan ayat harian.'
            ], 403);
        }

        // Device check removed — ownership check above is sufficient protection.

        // 1. Check if there's any INCOMPLETE entry first
        $incompleteEntry = JournalEntry::where('challenge_id', $challenge->id)
            ->where('is_completed', false)
            ->first();

        if ($incompleteEntry) {
            return response()->json([
                'message' => 'Selesaikan dulu catatan Pagi & Sore Hari ke-' . $incompleteEntry->day_number . ' sebelum lanjut!',
                'entry' => $incompleteEntry->load('content.series'),
            ], 200);
        }

        $isCatchUp = $request->boolean('is_catch_up');

        // 2. Enforce "1 Day 1 Ayat" for Normal Rolls (Istiqomah)
        if (!$isCatchUp) {
            $todayNormalExists = JournalEntry::where('challenge_id', $challenge->id)
                ->where('entry_date', now()->toDateString())
                ->where('is_catch_up', false)
                ->exists();

            if ($todayNormalExists) {
                $lastEntry = JournalEntry::where('challenge_id', $challenge->id)
                    ->where('entry_date', now()->toDateString())
                    ->where('is_catch_up', false)
                    ->first();

                return response()->json([
                    'message' => 'Alhamdulillah, jatah Istiqomah hari ini sudah diambil. Silahkan kembali besok, atau gunakan menu "Kejar Ketertinggalan" jika ada.',
                    'entry' => $lastEntry->load('content.series'),
                    'challenge' => $challenge->fresh()->setAttribute('debt_days', $this->getDebtDays($challenge)),
                    'already_done_today' => true
                ], 200);
            }
        } else {
            // Validate if catch-up is allowed (must have debt)
            $debt = $this->getDebtDays($challenge);

            if ($debt <= 0) {
                return response()->json([
                    'message' => 'Luar biasa! Progres Anda sudah sesuai jadwal (Istiqomah). Tidak perlu mengejar ketertinggalan.'
                ], 422);
            }
        }

        try {
            $day = $challenge->current_day;

            // Pick content: Preferred provided content_id (for offline sync), else random
            if ($request->has('content_id')) {
                $content = Content::find($request->content_id);
                // Ensure content belongs to the right series
                if ($content && $content->series_id != $challenge->series_id) {
                    $content = null;
                }
            } else {
                $content = Content::where('series_id', $challenge->series_id)
                    ->inRandomOrder()
                    ->first();
            }

            if (!$content) {
                return response()->json(['message' => 'Konten tidak ditemukan atau tidak tersedia.'], 404);
            }

            // check if entry for this day already exists (can happen if user rolled but didn't finish)
            $entry = JournalEntry::where('challenge_id', $challenge->id)
                ->where('day_number', $day)
                ->first();

            if (!$entry) {
                $entry = JournalEntry::create([
                    'user_id'    => $request->user()->id,
                    'challenge_id' => $challenge->id,
                    'content_id' => $content->id,
                    'day_number' => $day,
                    'entry_date' => now()->toDateString(),
                    'is_catch_up' => $isCatchUp,
                ]);
            } else {
                // If exists, just update its date to today if it was caught up but not finished?
                // Actually, let's just stick to the original entry date to avoid confusion
            }

            return response()->json([
                'message' => 'Ayat hari ini siap! Bismillah, selamat menghidupkan Al-Quran.',
                'entry' => $entry->load('content.series'),
                'challenge' => $challenge->fresh()->setAttribute('debt_days', $this->getDebtDays($challenge)),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil ayat: ' . $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    /**
     * Save the Before section (Pagi).
     */
    public function saveBefore(Request $request, JournalEntry $entry)
    {
        if ($entry->user_id != $request->user()->id) {
            return response()->json(['message' => 'Unauthorized Access'], 403);
        }

        $request->validate([
            'before_pesan'    => 'required|string',
            'before_perasaan' => 'required|string',
            'before_action'   => 'required|string',
        ]);

        $entry->update([
            'before_pesan'    => $request->before_pesan,
            'before_perasaan' => $request->before_perasaan,
            'before_action'   => $request->before_action,
        ]);

        $challenge = $entry->challenge;
        return response()->json([
            'message' => 'Catatan pagi tersimpan 😊',
            'entry'   => $entry->load('content'),
            'challenge' => $challenge->fresh()->setAttribute('debt_days', $this->getDebtDays($challenge)),
        ]);
    }

    /**
     * Save the After section (Sore).
     */
    public function saveAfter(Request $request, JournalEntry $entry)
    {
        if ($entry->user_id != $request->user()->id) {
            return response()->json(['message' => 'Unauthorized Access'], 403);
        }

        if ($entry->is_completed) {
            return response()->json([
                'message' => 'Catatan hari ini sudah lengkap!',
                'entry' => $entry->load('content'),
                'challenge' => $entry->challenge->fresh(),
            ]);
        }

        if (!$entry->has_before) {
            return response()->json([
                'message' => 'Isi dulu catatan Pagi (Before) sebelum mengisi catatan Sore (After)!',
            ], 422);
        }

        $request->validate([
            'after_berhasil'  => 'required|string',
            'after_perubahan' => 'required|string',
            'after_perasaan'  => 'required|string',
        ]);

        $entry->update([
            'after_berhasil'  => $request->after_berhasil,
            'after_perubahan' => $request->after_perubahan,
            'after_perasaan'  => $request->after_perasaan,
            'is_completed'    => true,
        ]);

        // Advance to next day in the challenge
        $challenge = $entry->challenge;
        if ($challenge->current_day < $challenge->total_days) {
            $challenge->increment('current_day');
        } else {
            $challenge->update(['is_completed' => true]);
        }

        return response()->json([
            'message' => 'Catatan Sore (After) tersimpan! MasyaAllah, satu hari lagi terlampauhi!',
            'entry'   => $entry->load('content'),
            'challenge' => $challenge->fresh()->setAttribute('debt_days', $this->getDebtDays($challenge)),
        ]);
    }

    /**
     * Get all journal entries for a challenge (history).
     */
    public function history(Request $request, Challenge $challenge)
    {
        if ($challenge->user_id != $request->user()->id) {
            return response()->json(['message' => 'Unauthorized Access'], 403);
        }

        $entries = $challenge->journalEntries()
            ->with('content')
            ->orderByDesc('id') // Always newest first
            ->get();

        return response()->json([
            'entries' => $entries,
            'challenge' => $challenge->setAttribute('debt_days', $this->getDebtDays($challenge))
        ]);
    }

    /**
     * Save final reflections after completing challenge.
     */
    public function saveFinalReflections(Request $request, Challenge $challenge)
    {
        if ($challenge->user_id != $request->user()->id) {
            return response()->json(['message' => 'Unauthorized Access'], 403);
        }

        $request->validate([
            'reflections' => 'required|array',
        ]);

        $challenge->update([
            'final_reflections' => $request->reflections,
        ]);

        return response()->json([
            'message' => 'MasyaAllah! Catatan perubahanmu telah disimpan. Teruslah menghidupkan Al-Quran!',
            'challenge' => $challenge->fresh(),
        ]);
    }
}
