<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\LicenseTransferRequest;
use App\Mail\LicenseTransferMail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class LicenseController extends Controller
{
    /**
     * List all licenses owned by the authenticated user.
     */
    public function index(Request $request)
    {
        $licenses = $request->user()->licenses()->with(['series', 'user'])->get();
        return response()->json($licenses);
    }

    /**
     * Activate a new license jar.
     */
    public function activate(Request $request)
    {
        $request->validate([
            'license_key' => 'required|string',
            'device_id'   => 'required|string',
        ]);

        $license = License::where('license_key', $request->license_key)->first();

        if (!$license) {
            return response()->json(['message' => 'Kode lisensi tidak ditemukan.'], 404);
        }

        if ($license->is_activated) {
            // Check if it's the SAME user and SAME device
            if ($license->activated_by === $request->user()->id && $license->device_id === $request->device_id) {
                return response()->json([
                    'message' => 'Lisensi ini sudah aktif di perangkat ini.',
                    'license' => $license->load('series'),
                ]);
            }

            $owner = $license->user ? $license->user->email : 'Pengguna lain';
            return response()->json([
                'message' => "Lisensi ini sudah aktif oleh: {$owner}.\n\nSatu lisensi hanya bisa digunakan di 1 HP dlm 1 akun.",
                'can_request_transfer' => true,
                'owner_email' => $owner,
            ], 422);
        }

        $license->update([
            'is_activated' => true,
            'activated_by' => $request->user()->id,
            'activated_at' => now(),
            'device_id'    => $request->device_id,
        ]);

        return response()->json([
            'message' => 'Jar berhasil diaktifkan dan dikunci ke perangkat ini!',
            'license' => $license->load('series'),
        ]);
    }

    /**
     * Release a license so it can be used by another user.
     */
    public function release(Request $request)
    {
        $request->validate([
            'license_key' => 'required|string',
        ]);

        $license = License::where('license_key', $request->license_key)
            ->where('activated_by', $request->user()->id)
            ->first();

        if (!$license) {
            return response()->json(['message' => 'Lisensi tidak ditemukan atau Anda bukan pemiliknya.'], 404);
        }

        $license->update([
            'is_activated' => false,
            'activated_by' => null,
            'activated_at' => null,
            'device_id'    => null,
        ]);

        return response()->json(['message' => 'Lisensi berhasil dilepaskan. Sekarang dapat digunakan oleh pengguna lain.']);
    }

    /**
     * Request transfer of a license from another user.
     */
    public function requestTransfer(Request $request)
    {
        $request->validate([
            'license_key' => 'required|string',
        ]);

        $license = License::where('license_key', $request->license_key)->first();

        if (!$license || !$license->is_activated || !$license->activated_by) {
            return response()->json(['message' => 'Lisensi tidak valid untuk transfer.'], 422);
        }

        if ($license->activated_by === $request->user()->id) {
            return response()->json(['message' => 'Anda sudah memiliki lisensi ini.'], 422);
        }

        // Create transfer request
        $transferRequest = LicenseTransferRequest::create([
            'license_id' => $license->id,
            'requester_id' => $request->user()->id,
            'owner_id' => $license->activated_by,
            'token' => Str::random(64),
            'expires_at' => now()->addDays(3),
        ]);

        // Send email to owner
        Mail::to($license->user->email)->send(new LicenseTransferMail($transferRequest));

        return response()->json([
            'message' => 'Permintaan transfer telah dikirim ke email pemilik sebelumnya. Silakan tunggu persetujuan mereka.'
        ]);
    }

    /**
     * Handle transfer action (approve/reject) from email link.
     */
    public function handleTransferAction(Request $request, $token, $action)
    {
        $transferRequest = LicenseTransferRequest::where('token', $token)
            ->where('status', 'pending')
            ->first();

        if (!$transferRequest || ($transferRequest->expires_at && $transferRequest->expires_at->isPast())) {
            return view('emails.transfer_response', [
                'status' => 'error',
                'title' => 'Link Kedaluwarsa',
                'message' => 'Link ini sudah tidak valid atau telah kedaluwarsa.'
            ]);
        }

        if ($action === 'approve') {
            $transferRequest->update(['status' => 'approved']);
            
            // Reassign the license
            $license = $transferRequest->license;
            $license->update([
                'is_activated' => false, 
                'activated_by' => null,
                'activated_at' => null,
                'device_id'    => null,
            ]);

            return view('emails.transfer_response', [
                'status' => 'success',
                'title' => 'Transfer Berhasil',
                'message' => 'Lisensi berhasil dilepaskan. Peminta sekarang dapat mengaktifkan Jar tersebut di akun mereka.'
            ]);
        }

        if ($action === 'reject') {
            $transferRequest->update(['status' => 'rejected']);
            return view('emails.transfer_response', [
                'status' => 'info',
                'title' => 'Transfer Ditolak',
                'message' => 'Permintaan transfer telah ditolak. Lisensi tetap berada di akun Anda.'
            ]);
        }

        return "Aksi tidak dikenal.";
    }

    /**
     * Direct transfer from owner to another email.
     */
    public function transfer(Request $request)
    {
        $request->validate([
            'license_key' => 'required|string',
            'target_email' => 'required|email|exists:users,email',
        ]);

        $license = License::where('license_key', $request->license_key)
            ->where('activated_by', $request->user()->id)
            ->first();

        if (!$license) {
            return response()->json(['message' => 'Lisensi tidak ditemukan atau Anda bukan pemiliknya.'], 404);
        }

        $targetUser = \App\Models\User::where('email', $request->target_email)->first();

        if ($targetUser->id === $request->user()->id) {
            return response()->json(['message' => 'Anda tidak bisa mentransfer ke akun sendiri.'], 422);
        }

        // Release first to clear device locking
        $license->update([
            'is_activated' => false,
            'activated_by' => $targetUser->id,
            'activated_at' => null, // Will be set when target user activates
            'device_id'    => null, // Will be locked to target device upon activation
        ]);

        return response()->json([
            'message' => "Jar berhasil dipindahkan ke akun {$request->target_email}. Pemilik baru harus mengaktifkannya di HP mereka."
        ]);
    }
}
