<?php

namespace App\Http\Controllers;

use App\Models\Heros;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HerosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('heroes.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('heroes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            $avatarPath = null;
            if ($request->hasFile('avatar')) {
                $avatar = $request->file('avatar');
                $filename = 'heroes/avatars/' . Str::uuid() . '.' . $avatar->getClientOriginalExtension();
                Storage::disk('public')->put($filename, file_get_contents($avatar));
                $avatarPath = $filename;
            }

            // Create hero in database
            $hero = Heros::create([
                'name' => $request->name,
                'avatar' => $avatarPath
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Hero đã được thêm thành công!',
                'hero' => [
                    'id' => $hero->id,
                    'name' => $hero->name,
                    'avatar_path' => $hero->avatar,
                    'avatar_url' => $hero->avatar ? asset('storage/' . $hero->avatar) : null,
                    'created_at' => $hero->created_at
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $hero = Heros::find($id);
        
        if (!$hero) {
            return response()->json([
                'success' => false,
                'message' => 'Hero không tìm thấy'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $hero->id,
                'name' => $hero->name,
                'avatar_path' => $hero->avatar,
                'avatar_url' => $hero->avatar ? asset('storage/' . $hero->avatar) : null,
                'created_at' => $hero->created_at
            ]
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $hero = Heros::find($id);
        
        if (!$hero) {
            abort(404);
        }

        $heroData = [
            'id' => $hero->id,
            'name' => $hero->name,
            'avatar_path' => $hero->avatar,
            'avatar_url' => $hero->avatar ? asset('storage/' . $hero->avatar) : null,
        ];

        return view('heroes.edit', ['hero' => $heroData]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            $hero = Heros::find($id);
            
            if (!$hero) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hero không tìm thấy'
                ], 404);
            }

            // Update avatar if new one is uploaded
            if ($request->hasFile('avatar')) {
                // Delete old avatar
                if ($hero->avatar) {
                    Storage::disk('public')->delete($hero->avatar);
                }

                $avatar = $request->file('avatar');
                $filename = 'heroes/avatars/' . Str::uuid() . '.' . $avatar->getClientOriginalExtension();
                Storage::disk('public')->put($filename, file_get_contents($avatar));
                $hero->avatar = $filename;
            }

            // Update name
            $hero->name = $request->name;
            $hero->save();

            return response()->json([
                'success' => true,
                'message' => 'Hero đã được cập nhật thành công!',
                'hero' => [
                    'id' => $hero->id,
                    'name' => $hero->name,
                    'avatar_path' => $hero->avatar,
                    'avatar_url' => $hero->avatar ? asset('storage/' . $hero->avatar) : null,
                    'updated_at' => $hero->updated_at
                ]
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
    public function destroy($id)
    {
        try {
            $hero = Heros::find($id);
            
            if (!$hero) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hero không tìm thấy'
                ], 404);
            }

            // Delete avatar file
            if ($hero->avatar) {
                Storage::disk('public')->delete($hero->avatar);
            }

            // Delete hero (skins will be deleted automatically due to foreign key cascade)
            $hero->delete();

            return response()->json([
                'success' => true,
                'message' => 'Hero đã được xóa thành công!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all heroes as JSON
     */
    public function list()
    {
        $heroes = Heros::with('skins')->get();
        
        $heroesData = $heroes->map(function ($hero) {
            return [
                'id' => $hero->id,
                'name' => $hero->name,
                'avatar_path' => $hero->avatar,
                'avatar_url' => $hero->avatar ? asset('storage/' . $hero->avatar) : null,
                'created_at' => $hero->created_at,
                'skins_count' => $hero->skins->count()
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $heroesData
        ]);
    }
}