<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Challenge;
use App\Models\Content;
use App\Models\JournalEntry;
use App\Models\License;
use App\Models\Series;
use Illuminate\Http\Request;

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

        // If 'confirmed' flag is not true and user has no license, ask for confirmation
        if (!$hasLicense && !$request->boolean('confirmed')) {
            return response()->json([
                'message' => 'Anda belum mempunyai Jar ' . Series::find($request->series_id)->name . ".\n\nYakin ingin tetap menantang diri sendiri selama " . $totalDays . " hari dengan materi ini?\n\nJika ingin aktivasi, silahkan hubungi Distributor TLQ anda (08995295781) untuk mendapatkan kode aktivasi.",
                'needs_confirmation' => true,
            ], 200); 
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
        $challenges = Challenge::where('user_id', $user->id)
            ->with('series')
            ->get()
            ->map(function ($c) use ($user) {
                $entry = $c->today_entry;
                $c->today_entry = $entry;
                
                // Check if user has an activated license for this series
                $c->has_license = License::where('activated_by', $user->id)
                    ->where('series_id', $c->series_id)
                    ->where('is_activated', true)
                    ->exists();
                    
                return $c;
            });

        return response()->json(['challenges' => $challenges]);
    }

    /**
     * Roll (get a random ayah) for the current day's journal.
     * This locks the content for the day.
     */
    public function rollContent(Request $request, Challenge $challenge)
    {
        if ($challenge->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'device_id' => 'required|string',
        ]);

        // STRICT CHECK: User must have an ACTIVATED license for this series on THIS device
        $license = License::where('activated_by', $request->user()->id)
            ->where('series_id', $challenge->series_id)
            ->where('is_activated', true)
            ->first();

        if (!$license) {
            return response()->json([
                'message' => 'Akses Terkunci. Anda harus mengaktivasi Jar seri ini terlebih dahulu untuk mendapatkan ayat harian.'
            ], 403);
        }

        if ($license->device_id !== $request->device_id) {
            return response()->json([
                'message' => 'Akses Ditolak. Lisensi Jar ini terdaftar di perangkat lain. Silakan logout dari perangkat lama anda atau hubungi Distributor.'
            ], 403);
        }

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

        // 2. Check if a NEW roll/entry was already performed TODAY (Calendar Day Lock)
        // Note: entry_date is set to the date the content was rolled
        $todayRollExists = JournalEntry::where('challenge_id', $challenge->id)
            ->where('entry_date', now()->toDateString())
            ->exists();

        if ($todayRollExists) {
            $lastEntry = JournalEntry::where('challenge_id', $challenge->id)
                ->orderBy('day_number', 'desc')
                ->first();

            return response()->json([
                'message' => 'MasyaAllah! Kamu sudah menyelesaikan tugasmu hari ini. Silakan kembali besok pagi untuk tantangan berikutnya!',
                'entry' => $lastEntry->load('content.series'),
                'already_done_today' => true
            ], 200);
        }

        $day = $challenge->current_day;

        // Pick a random content from this series
        $content = Content::where('series_id', $challenge->series_id)
            ->inRandomOrder()
            ->first();

        if (!$content) {
            return response()->json(['message' => 'Tidak ada konten tersedia di seri ini.'], 404);
        }

        $entry = JournalEntry::create([
            'user_id'    => $request->user()->id,
            'challenge_id' => $challenge->id,
            'content_id' => $content->id,
            'day_number' => $day,
            'entry_date' => now()->toDateString(),
        ]);

        return response()->json([
            'message' => 'Ayat hari ini siap! Bismillah, selamat menghidupkan Al-Quran.',
            'entry' => $entry->load('content.series'),
        ], 201);
    }

    /**
     * Save the Before section (Pagi).
     */
    public function saveBefore(Request $request, JournalEntry $entry)
    {
        if ($entry->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
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

        return response()->json([
            'message' => 'Catatan Pagi (Before) berhasil disimpan!',
            'entry' => $entry->load('content'),
        ]);
    }

    /**
     * Save the After section (Sore).
     */
    public function saveAfter(Request $request, JournalEntry $entry)
    {
        if ($entry->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
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
            'challenge' => $challenge->fresh(),
        ]);
    }

    /**
     * Get all journal entries for a challenge (history).
     */
    public function history(Request $request, Challenge $challenge)
    {
        if ($challenge->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $entries = $challenge->journalEntries()
            ->with('content')
            ->orderBy('day_number')
            ->get();

        return response()->json(['entries' => $entries]);
    }

    /**
     * Save final reflections after completing challenge.
     */
    public function saveFinalReflections(Request $request, Challenge $challenge)
    {
        if ($challenge->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
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
