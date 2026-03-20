<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'target_type',
        'target_id',
        'old_data',
        'new_data',
        'ip_address'
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper Static untuk mencatat log dengan sekali baris
     */
    public static function log($action, $target = null, $old = null, $new = null)
    {
        return self::create([
            'user_id'     => auth()->id(),
            'action'      => $action,
            'target_type' => $target ? get_class($target) : null,
            'target_id'   => $target ? $target->id : null,
            'old_data'    => $old,
            'new_data'    => $new,
            'ip_address'  => request()->ip(),
        ]);
    }
}
