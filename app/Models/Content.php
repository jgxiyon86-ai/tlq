<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    protected $fillable = ['series_id', 'surah_ayah', 'arabic_text', 'translation', 'insight', 'action_plan'];

    public function series()
    {
        return $this->belongsTo(Series::class);
    }
}
