<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Spatie\Browsershot\Browsershot;

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

    public function exportGrid(Request $request)
    {
        try {
            $data = $request->validate([
                'gridHtml' => 'required|string',
                'width' => 'integer|min:100|max:5000',
                'height' => 'integer|min:100|max:5000'
            ]);

            $width = $data['width'] ?? 1200;
            $height = $data['height'] ?? 800;

            // Create a complete HTML page for Browsershot
            $html = "
            <!DOCTYPE html>
            <html lang='vi'>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>Photo Grid Export</title>
                <style>
                    body { 
                        margin: 0; 
                        padding: 20px; 
                        font-family: Arial, sans-serif;
                        background: white;
                    }
                    .photo-grid {
                        display: grid;
                        gap: 5px;
                        row-gap: 5px;
                        column-gap: 0px;
                        max-width: 100%;
                        margin: 0 auto;
                    }
                    .photo-cell {
                        aspect-ratio: 1/1.4;
                        border: 3px solid #333;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        background-color: #f8f9fa;
                        overflow: hidden;
                        position: relative;
                    }
                    .photo-cell img {
                        width: 100%;
                        height: 100%;
                        object-fit: cover;
                        border: none;
                        display: block;
                    }
                    .large-cell {
                        grid-column: span 4;
                        aspect-ratio: 3.9/1.4;
                        margin-right: 10px;
                        margin-bottom: 25px;
                    }
                    .large-cell img {
                        width: 100%;
                        height: 100%;
                        object-fit: cover;
                        border: none;
                        display: block;
                    }
                    .remove-btn {
                        display: none !important;
                    }
                </style>
            </head>
            <body>
                {$data['gridHtml']}
            </body>
            </html>";

            // Generate filename
            $filename = 'photo-grid-' . date('Y-m-d-H-i-s') . '.png';
            $filepath = storage_path('app/public/exports/' . $filename);

            // Create exports directory if it doesn't exist
            $exportDir = storage_path('app/public/exports');
            if (!file_exists($exportDir)) {
                mkdir($exportDir, 0755, true);
            }

            // Use Browsershot to generate PNG
            Browsershot::html($html)
                ->windowSize($width, $height)
                ->deviceScaleFactor(1)
                ->format('png')
                ->quality(90)
                ->timeout(60)
                ->setChromePath(null) // Use system Chrome if available
                ->noSandbox()
                ->dismissDialogs()
                ->blockUrls(['*.png', '*.jpg', '*.jpeg', '*.gif', '*.svg']) // Block external images for speed
                ->save($filepath);

            if (file_exists($filepath)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Export thành công!',
                    'download_url' => asset('storage/exports/' . $filename),
                    'filename' => $filename
                ]);
            } else {
                throw new \Exception('Failed to generate image file');
            }

        } catch (\Exception $e) {
            Log::error('Export Grid Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Lỗi export: ' . $e->getMessage()
            ], 500);
        }
    }
} 