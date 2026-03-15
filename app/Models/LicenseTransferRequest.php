<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LicenseTransferRequest extends Model
{
    protected $fillable = [
        'license_id',
        'requester_id',
        'owner_id',
        'status',
        'token',
        'expires_at',
    ];

    public function license()
    {
        return $this->belongsTo(License::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
