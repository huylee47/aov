<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Heros extends Model
{
    protected $table = 'heros';
    protected $fillable = ['avatar','name'];

    public function skins()
    {
        return $this->hasMany(Skins::class, 'hero_id', 'id');
    }
}