<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\License;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    /**
     * List all licenses owned by the authenticated user.
     */
    public function index(Request $request)
    {
        $licenses = $request->user()->licenses()->with('series')->get();
        return response()->json($licenses);
    }

    /**
     * Activate a new license jar.
     */
    public function activate(Request $request)
    {
        $request->validate([
            'license_key' => 'required|string',
        ]);

        $license = License::where('license_key', $request->license_key)->first();

        if (!$license) {
            return response()->json(['message' => 'Kode lisensi tidak ditemukan.'], 404);
        }

        if ($license->is_activated) {
            $owner = $license->user ? $license->user->email : 'Pengguna lain';
            return response()->json([
                'message' => "Lisensi ini sudah aktif oleh: {$owner}.\n\nJika ini adalah Jar Anda, silakan minta pemilik lama untuk melepaskan lisensinya terlebih dahulu melaui menu Profil > Lisensi Saya."
            ], 422);
        }

        $license->update([
            'is_activated' => true,
            'activated_by' => $request->user()->id,
            'activated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Jar berhasil diaktifkan!',
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
        ]);

        return response()->json(['message' => 'Lisensi berhasil dilepaskan. Sekarang dapat digunakan oleh pengguna lain.']);
    }
}
