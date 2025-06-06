<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skins extends Model
{
    protected $table = 'skins';
    protected $fillable = ['name', 'hero_id', 'image'];
    
    protected $casts = [
        'hero_id' => 'integer',
    ];
    
    /**
     * Get the image URL
     */
    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    public function hero()
    {
        return $this->belongsTo(Heros::class, 'hero_id', 'id');
    }
}