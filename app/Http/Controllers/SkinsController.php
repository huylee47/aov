<?php

namespace App\Http\Controllers;

use App\Models\Skins;
use App\Models\Heros;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SkinsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($heroId)
    {
        $hero = Heros::find($heroId);
        
        if (!$hero) {
            return response()->json([
                'success' => false,
                'message' => 'Hero không tìm thấy'
            ], 404);
        }

        $skins = $hero->skins;
        
        $skinsData = $skins->map(function ($skin) {
            return [
                'id' => $skin->id,
                'name' => $skin->name,
                'hero_id' => $skin->hero_id,
                'image_path' => $skin->image,
                'image_url' => asset('storage/' . $skin->image),
                'created_at' => $skin->created_at
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $skinsData
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'hero_id' => 'required|exists:heros,id',
                'skins' => 'required|array|min:1',
                'skins.*.file' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                'skins.*.name' => 'required|string|max:255'
            ]);

            $heroId = $request->hero_id;
            $newSkins = [];

            // Process each uploaded skin
            foreach ($request->input('skins') as $index => $skinData) {
                $skinFile = $request->file("skins.{$index}.file");
                
                if ($skinFile) {
                    $filename = 'heroes/skins/' . Str::uuid() . '.' . $skinFile->getClientOriginalExtension();
                    Storage::disk('public')->put($filename, file_get_contents($skinFile));
                    
                    // Create skin in database
                    $skin = Skins::create([
                        'name' => $skinData['name'],
                        'hero_id' => $heroId,
                        'image' => $filename
                    ]);
                    
                    $skinDataResponse = [
                        'id' => $skin->id,
                        'name' => $skin->name,
                        'hero_id' => $skin->hero_id,
                        'image_path' => $skin->image,
                        'image_url' => asset('storage/' . $skin->image),
                        'created_at' => $skin->created_at
                    ];
                    
                    $newSkins[] = $skinDataResponse;
                }
            }

            // Get total skins count
            $totalSkins = Skins::where('hero_id', $heroId)->count();

            return response()->json([
                'success' => true,
                'message' => count($newSkins) . ' skin(s) đã được thêm thành công!',
                'skins' => $newSkins,
                'total_skins' => $totalSkins
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($heroId, $skinId)
    {
        try {
            $skin = Skins::where('hero_id', $heroId)->where('id', $skinId)->first();

            if (!$skin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Skin không tìm thấy'
                ], 404);
            }

            // Delete image file
            if ($skin->image) {
                Storage::disk('public')->delete($skin->image);
            }

            // Delete from database
            $skin->delete();

            return response()->json([
                'success' => true,
                'message' => 'Skin đã được xóa thành công!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show skins management page for a hero
     */
    public function manage($heroId)
    {
        // Get hero info from database
        $hero = Heros::find($heroId);
        
        if (!$hero) {
            abort(404, 'Hero không tìm thấy');
        }

        $heroData = [
            'id' => $hero->id,
            'name' => $hero->name,
            'avatar_path' => $hero->avatar,
            'avatar_url' => $hero->avatar ? asset('storage/' . $hero->avatar) : null,
        ];

        return view('skins.manage', ['hero' => $heroData, 'heroId' => $heroId]);
    }

    /**
     * Get all skins from all heroes for selection
     */
    public function getAllSkins()
    {
        $skins = Skins::with('hero')->get();
        
        $skinsData = $skins->map(function ($skin) {
            return [
                'id' => $skin->id,
                'name' => $skin->name,
                'hero_id' => $skin->hero_id,
                'hero_name' => $skin->hero->name,
                'image_path' => $skin->image,
                'image_url' => asset('storage/' . $skin->image),
                'created_at' => $skin->created_at
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $skinsData
        ]);
    }
}