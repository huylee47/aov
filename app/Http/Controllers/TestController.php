<?php

namespace App\Http\Controllers;

use App\Models\Heros;
use App\Models\Skins;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function testDatabase()
    {
        // Test creating a hero
        $hero = Heros::create([
            'name' => 'Test Hero',
            'avatar' => 'test-avatar.jpg'
        ]);

        // Test creating skins
        $skin1 = Skins::create([
            'hero_id' => $hero->id,
            'image' => 'test-skin-1.jpg'
        ]);

        $skin2 = Skins::create([
            'hero_id' => $hero->id,
            'image' => 'test-skin-2.jpg'
        ]);

        // Test relationships
        $heroWithSkins = Heros::with('skins')->find($hero->id);

        return response()->json([
            'hero' => $heroWithSkins,
            'skins_count' => $heroWithSkins->skins->count(),
            'database_status' => 'Database working correctly!'
        ]);
    }

    public function cleanupTest()
    {
        // Clean up test data
        Heros::where('name', 'Test Hero')->delete();
        
        return response()->json([
            'message' => 'Test data cleaned up!'
        ]);
    }
} 