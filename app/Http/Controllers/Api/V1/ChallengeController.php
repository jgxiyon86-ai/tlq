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

        // Check if a challenge of the SAME TYPE for this series is still walking (active)
        $challenge = Challenge::where('user_id', $user->id)
            ->where('series_id', $request->series_id)
            ->where('is_seven_days', $isSevenDays)
            ->where('is_completed', false)
            ->first();

        if ($challenge) {
            $daysLabel = $challenge->is_seven_days ? '7' : '40';
            return response()->json([
                'message' => "Tantangan {$daysLabel} hari untuk seri ini masih berjalan! Selesaikan atau hapus tantangan aktif sebelum memulai yang baru.",
                'challenge' => $challenge->load('series'),
            ], 200);
        }

        $startDate = $request->has('started_at') ? \Carbon\Carbon::parse($request->started_at) : now();

        // Create the challenge with Transaction
        try {
            return \DB::transaction(function () use ($user, $request, $isSevenDays, $totalDays, $startDate) {
                $challenge = Challenge::create([
                    'user_id' => $user->id,
                    'series_id' => $request->series_id,
                    'is_seven_days' => $isSevenDays,
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

                $daysLabel = $isSevenDays ? '7' : '40';
                return response()->json([
                    'message' => "Alhamdulillah! Kalender {$daysLabel} hari Anda sudah disiapkan. Selamat menghidupkan Al-Quran!",
                    'challenge' => $challenge->load(['series', 'journalEntries.content']),
                ], 201);
            });
        } catch (\Exception $e) {
            \Log::error("Gagal aktivasi tantangan: " . $e->getMessage());
            return response()->json([
                'message' => 'Gagal menyiapkan kalender. Pastikan server sudah melakukan migrate database terbaru.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the user's active challenges.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Only return challenges for series the user currently has an ACTIVE license for
        $activeSeriesIds = $user->licenses()->where('is_activated', true)->pluck('series_id')->toArray();

        // Load challenges (even completed ones, so they stay in history if needed)
        // BUT only if user owns the license for that series
        $challenges = Challenge::where('user_id', $user->id)
            ->whereIn('series_id', $activeSeriesIds)
            ->with(['series', 'journalEntries.content'])
            ->orderBy('is_completed', 'asc')
            ->orderBy('updated_at', 'desc')
            ->get();

        foreach ($challenges as $c) {
            $this->ensureJournalEntriesExist($c);
            
            // Find entry for TODAY based on calendar date
            $today = now()->toDateString();
            $entry = $c->journalEntries->first(function($e) use ($today) {
                return \Carbon\Carbon::parse($e->entry_date)->toDateString() === $today;
            });
            
            // Fallback: If no entry matches today's exact date string (timezone shift),
            // and the challenge is active, try to find the entry that SHOULD be today
            if (!$entry && !$c->is_completed) {
                $startedAt = $c->started_at ?? $c->created_at;
                $dayNumber = (int)($startedAt->copy()->startOfDay()->diffInDays(now()->startOfDay()) + 1);
                if ($dayNumber > 0 && $dayNumber <= $c->total_days) {
                    $entry = $c->journalEntries->firstWhere('day_number', $dayNumber);
                }
            }

            $c->today_entry = $entry ? $entry->load('content') : null;
            
            // We NO LONGER override current_day display here.
            // current_day must reflect the ACTUAL progress level from DB.
            // The calendar lag will be shown via 'debt_days'.

            // DISCIPLINE MODE: Strict deadline (No leeway)
            // If total_days = 7, and started on 11th, deadline is 11 + 7 = 18th. 
            // On the 18th, it's considered finished/expired if not completed.
            $startDate = $c->started_at ?? $c->created_at;
            $deadline = $startDate->copy()->startOfDay()->addDays((int)$c->total_days);
            
            if (now()->startOfDay()->greaterThanOrEqualTo($deadline) && !$c->is_completed) {
                // If the user hasn't finished, it's marked as done (failed to complete on time)
                $c->update(['is_completed' => true]);
                $c->load('series');
            }

            $c->setAttribute('debt_days', $this->getDebtDays($c));
            
            // Calculate how many entries were actually filled
            $c->setAttribute('completed_entries_count', $c->journalEntries->where('is_completed', true)->count());
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
        
        // Debt = What level you SHOULD be on - What level you ARE on
        // Example: Started 13th. Today 18th (Target Day 6). Progress Level 2.
        // Debt = 6 - 2 = 4 days missed (2,3,4,5).
        $maxTarget = (int)$c->total_days;
        $effectiveTarget = min($targetDay, $maxTarget);
        
        return (int) max(0, $effectiveTarget - $c->current_day);
    }


    /**
     * Roll (get a random ayah) for the current day's journal.
     * This locks the content for the day.
     */
    public function rollContent(Request $request, Challenge $challenge)
    {
        if ($challenge->user_id != $request->user()->id) {
            return response()->json(['message' => 'Unauthorized Access'], 403);
        }

        // Find the specific entry for today (Istiqomah) or a specific day_number (Catch up)
        $date = now()->toDateString();
        $dayNumber = $request->input('day_number'); // If provided (from history), use it
        
        $entryQuery = JournalEntry::where('challenge_id', $challenge->id);
        if ($dayNumber) {
            $entryQuery->where('day_number', $dayNumber);
        } else {
            $entryQuery->where('entry_date', $date);
        }
        
        $entry = $entryQuery->first();

        // Fallback: If no entry matches today's exact date string (timezone shift),
        // and it's not a manual dayNumber request, try to find the entry that SHOULD be today
        if (!$entry && !$dayNumber) {
            $startedAt = $challenge->started_at ?? $challenge->created_at;
            $calculatedDay = (int)($startedAt->copy()->startOfDay()->diffInDays(now()->startOfDay()) + 1);
            if ($calculatedDay > 0 && $calculatedDay <= $challenge->total_days) {
                $entry = JournalEntry::where('challenge_id', $challenge->id)
                    ->where('day_number', $calculatedDay)
                    ->first();
            }
        }

        if (!$entry) {
            return response()->json(['message' => 'Slot jurnal tidak ditemukan untuk hari ini.'], 404);
        }

        if ($entry->content_id) {
            return response()->json([
                'message' => 'Ayat untuk hari ini sudah terbuka.',
                'entry' => $entry->load('content.series'),
            ], 200);
        }

        // MOMEN TAKDIR: Pick random content only when requested
        $content = Content::where('series_id', $challenge->series_id)
            ->inRandomOrder()
            ->first();

        if (!$content) {
            return response()->json(['message' => 'Konten tidak ditemukan.'], 404);
        }

        $entry->update(['content_id' => $content->id]);

        return response()->json([
            'message' => 'Ayat hari ini siap! Bismillah.',
            'entry' => $entry->load('content.series'),
            'challenge' => $challenge->fresh()->setAttribute('debt_days', $this->getDebtDays($challenge)),
        ], 201);
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
            'before_pesan'    => 'nullable|string',
            'before_perasaan' => 'nullable|string',
            'before_action'   => 'nullable|string',
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

        // Allow editing even if is_completed is already true (to support edits on day 1)

        if (!$entry->has_before) {
            return response()->json([
                'message' => 'Isi dulu catatan Pagi (Before) sebelum mengisi catatan Sore (After)!',
            ], 422);
        }

        $request->validate([
            'after_berhasil'  => 'nullable|string',
            'after_perubahan' => 'nullable|string',
            'after_perasaan'  => 'nullable|string',
        ]);

        $wasCompleted = $entry->is_completed;
        $isAllFilled = !empty($request->after_berhasil) && !empty($request->after_perubahan) && !empty($request->after_perasaan);

        $entry->update([
            'after_berhasil'  => $request->after_berhasil,
            'after_perubahan' => $request->after_perubahan,
            'after_perasaan'  => $request->after_perasaan,
            'is_completed'    => $wasCompleted || $isAllFilled,
        ]);

        // Advance to next day only if this is the FIRST time it's being completed
        $challenge = $entry->challenge;
        if (!$wasCompleted) {
            if ($challenge->current_day < $challenge->total_days) {
                $challenge->increment('current_day');
            }
            // MODIFIED: We no longer mark challenge as is_completed here immediately.
            // Completion will happen at the Final Reflections (Closing) step.
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

        $this->ensureJournalEntriesExist($challenge);

        $entries = $challenge->journalEntries()
            ->with('content')
            ->orderByDesc('id') // Always newest first
            ->get();

        // Detect Today's Entry for the APK (Timezone resilient)
        $today = now()->toDateString();
        $todayEntry = $entries->first(function($e) use ($today) {
            return \Carbon\Carbon::parse($e->entry_date)->toDateString() === $today;
        });
        
        if (!$todayEntry && !$challenge->is_completed) {
            $startedAt = $challenge->started_at ?? $challenge->created_at;
            $dayNumber = (int)($startedAt->copy()->startOfDay()->diffInDays(now()->startOfDay()) + 1);
            if ($dayNumber > 0 && $dayNumber <= $challenge->total_days) {
                $todayEntry = $entries->firstWhere('day_number', $dayNumber);
            }
        }

        return response()->json([
            'entries' => $entries,
            'today_entry' => $todayEntry,
            'challenge' => $challenge->load('series')->setAttribute('debt_days', $this->getDebtDays($challenge))
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
            'is_completed'      => true,
        ]);

        return response()->json([
            'message' => 'MasyaAllah! Catatan perubahanmu telah disimpan. Teruslah menghidupkan Al-Quran!',
            'challenge' => $challenge->fresh(),
        ]);
    }

    /**
     * Delete a challenge (only allowed if still on Day 1).
     */
    public function destroy(Request $request, Challenge $challenge)
    {
        if ($challenge->user_id != $request->user()->id) {
            return response()->json(['message' => 'Unauthorized Access'], 403);
        }

        // REMOVED RESTRICTION: Allow deleting challenge at any day to help user reset / start over
        /*
        if ($challenge->current_day > 1) {
            return response()->json([
                'message' => 'Tantangan sudah berjalan lebih dari 1 hari dan tidak bisa dihapus.'
            ], 403);
        }
        */

        $challenge->delete();

        return response()->json([
            'message' => 'Tantangan telah dihapus.'
        ]);
    }

    private function ensureJournalEntriesExist(Challenge $c)
    {
        // Check if all 1..total_days exist. If not, create them.
        $total = (int)$c->total_days;
        $existingCount = $c->journalEntries->count();
        
        if ($existingCount < $total) {
            $startedAt = $c->started_at ?? $c->created_at;
            for ($i = 1; $i <= $total; $i++) {
                $exists = $c->journalEntries->contains('day_number', $i);
                if (!$exists) {
                    JournalEntry::create([
                        'user_id' => $c->user_id,
                        'challenge_id' => $c->id,
                        'day_number' => $i,
                        'entry_date' => $startedAt->copy()->addDays($i-1)->toDateString(),
                    ]);
                }
            }
            // Refresh relationship after creation
            $c->unsetRelation('journalEntries');
            $c->load('journalEntries.content');
        }
    }
}
