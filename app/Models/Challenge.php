<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Challenge extends Model
{
    protected $fillable = [
        'user_id',
        'series_id',
        'is_seven_days',
        'total_days',
        'current_day',
        'is_completed',
        'final_reflections',
        'started_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'series_id' => 'integer',
        'total_days' => 'integer',
        'current_day' => 'integer',
        'is_seven_days' => 'boolean',
        'started_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'is_completed' => 'boolean',
        'final_reflections' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function series()
    {
        return $this->belongsTo(Series::class);
    }

    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class);
    }

    // Check if the challenge is in range (before, after)
    public function getTodayEntryAttribute()
    {
        $day = $this->current_day;
        return $this->journalEntries()->with('content')->where('day_number', $day)->first();
    }
}
