<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PhotoGridController extends Controller
{
    public function index()
    {
        return view('photo');
    }
    
    public function saveGrid(Request $request)
    {
        try {
            $gridData = $request->validate([
                'rows' => 'required|integer|min:2|max:20',
                'cols' => 'required|integer|min:2|max:20',
                'images' => 'required|array'
            ]);
            
            $savedImages = [];
            
            // Process and save each image
            foreach ($gridData['images'] as $cellIndex => $imageData) {
                if (isset($imageData['data']) && $imageData['data']) {
                    // Remove the data:image/jpeg;base64, prefix
                    $base64 = preg_replace('#^data:image/\w+;base64,#i', '', $imageData['data']);
                    $imageContent = base64_decode($base64);
                    
                    // Generate unique filename
                    $filename = 'grid_images/' . Str::uuid() . '.jpg';
                    
                    // Save to storage
                    Storage::disk('public')->put($filename, $imageContent);
                    
                    $savedImages[$cellIndex] = [
                        'original_name' => $imageData['name'],
                        'stored_path' => $filename,
                        'url' => asset('storage/' . $filename)
                    ];
                }
            }
            
            // You can save to database here if needed
            $gridRecord = [
                'id' => Str::uuid(),
                'rows' => $gridData['rows'],
                'cols' => $gridData['cols'],
                'images' => $savedImages,
                'created_at' => now()
            ];
            
            // For now, save to session or you can create a database table
            session(['saved_grid' => $gridRecord]);
            
            return response()->json([
                'success' => true,
                'message' => 'Grid đã được lưu thành công!',
                'grid_id' => $gridRecord['id'],
                'data' => $gridRecord
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function getGrid($id = null)
    {
        if ($id) {
            // Get specific grid (you can implement database lookup here)
            $grid = session('saved_grid');
            if ($grid && $grid['id'] === $id) {
                return response()->json([
                    'success' => true,
                    'data' => $grid
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Grid không tìm thấy'
            ], 404);
        }
        
        // Get latest grid
        $grid = session('saved_grid');
        if ($grid) {
            return response()->json([
                'success' => true,
                'data' => $grid
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Chưa có grid nào được lưu'
        ], 404);
    }
    
    public function listGrids()
    {
        // This is a simple implementation using session
        // In a real app, you would query the database
        $grid = session('saved_grid');
        $grids = $grid ? [$grid] : [];
        
        return response()->json([
            'success' => true,
            'data' => $grids
        ]);
    }
    
    public function deleteGrid($id)
    {
        try {
            $grid = session('saved_grid');
            
            if ($grid && $grid['id'] === $id) {
                // Delete associated images
                foreach ($grid['images'] as $imageData) {
                    if (isset($imageData['stored_path'])) {
                        Storage::disk('public')->delete($imageData['stored_path']);
                    }
                }
                
                // Remove from session
                session()->forget('saved_grid');
                
                return response()->json([
                    'success' => true,
                    'message' => 'Grid đã được xóa thành công!'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Grid không tìm thấy'
            ], 404);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }
} 