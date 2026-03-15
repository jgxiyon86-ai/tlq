<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManualPage extends Model
{
    protected $fillable = ['series_id', 'page_number', 'title', 'content', 'image_url'];

    public function series()
    {
        return $this->belongsTo(Series::class);
    }
}
