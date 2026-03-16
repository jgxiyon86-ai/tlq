<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    protected $fillable = [
        'user_id',
        'challenge_id',
        'content_id',
        'day_number',
        'entry_date',
        'before_pesan',
        'before_perasaan',
        'before_action',
        'after_berhasil',
        'after_perasaan',
        'is_completed',
        'is_catch_up',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'challenge_id' => 'integer',
        'entry_date' => 'date',
        'is_completed' => 'boolean',
        'is_catch_up' => 'boolean',
    ];

    public function challenge()
    {
        return $this->belongsTo(Challenge::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function content()
    {
        return $this->belongsTo(Content::class);
    }

    // True if user has filled the before section
    public function getHasBeforeAttribute(): bool
    {
        return !is_null($this->before_pesan);
    }

    // True if user has filled the after section
    public function getHasAfterAttribute(): bool
    {
        return !is_null($this->after_berhasil);
    }
}
