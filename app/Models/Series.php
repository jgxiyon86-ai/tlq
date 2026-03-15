<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Series extends Model
{
    protected $fillable = ['name', 'slug', 'color_hex', 'description'];

    public function contents()
    {
        return $this->hasMany(Content::class);
    }

    public function licenses()
    {
        return $this->hasMany(License::class);
    }

    public function manualPages()
    {
        return $this->hasMany(ManualPage::class)->orderBy('page_number');
    }
}
