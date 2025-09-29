<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Models\Property;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class MediaController extends Controller
{
    /**
     * Display media library listing
     */
    public function index(Request $request)
    {
        $query = Media::query();

        // Apply filters
        if ($request->filled('collection')) {
            $query->where('collection_name', $request->collection);
        }

        if ($request->filled('model_type')) {
            $query->where('model_type', $request->model_type);
        }

        if ($request->filled('mime_type')) {
            $mimeTypes = match($request->mime_type) {
                'images' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
                'documents' => ['application/pdf', 'application/msword', 'application/vnd.ms-excel'],
                'videos' => ['video/mp4', 'video/mpeg', 'video/quicktime'],
                default => [$request->mime_type]
            };
            $query->whereIn('mime_type', $mimeTypes);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('file_name', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Get media with pagination
        $media = $query->paginate(24);

        // Get statistics
        $stats = [
            'total_files' => Media::count(),
            'total_size' => Media::sum('size'),
            'images_count' => Media::whereIn('mime_type', ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])->count(),
            'documents_count' => Media::whereIn('mime_type', ['application/pdf', 'application/msword'])->count(),
        ];

        // Get unique collections
        $collections = Media::distinct('collection_name')->pluck('collection_name');

        // Get unique model types
        $modelTypes = Media::distinct('model_type')->pluck('model_type')->map(function($type) {
            return [
                'value' => $type,
                'label' => class_basename($type)
            ];
        });

        return view('admin.media.index', compact('media', 'stats', 'collections', 'modelTypes'));
    }

    /**
     * Show media details
     */
    public function show(Media $media)
    {
        // Get the model that owns this media
        $model = null;
        if ($media->model_type && $media->model_id) {
            $modelClass = $media->model_type;
            if (class_exists($modelClass)) {
                $model = $modelClass::find($media->model_id);
            }
        }

        // Get custom properties
        $customProperties = $media->custom_properties ?? [];

        return view('admin.media.show', compact('media', 'model', 'customProperties'));
    }

    /**
     * Delete media file
     */
    public function destroy(Media $media)
    {
        $this->authorize('delete media');

        try {
            $media->delete();
            
            return redirect()->route('admin.media.index')
                ->with('success', 'Media file deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('admin.media.index')
                ->with('error', 'Failed to delete media file: ' . $e->getMessage());
        }
    }

    /**
     * Bulk delete media files
     */
    public function bulkDelete(Request $request)
    {
        $this->authorize('delete media');

        $request->validate([
            'media_ids' => 'required|array',
            'media_ids.*' => 'exists:media,id'
        ]);

        try {
            Media::whereIn('id', $request->media_ids)->delete();
            
            return response()->json([
                'success' => 'Selected media files deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete media files: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download media file
     */
    public function download(Media $media)
    {
        $this->authorize('view media');

        try {
            return response()->download($media->getPath(), $media->file_name);
        } catch (\Exception $e) {
            return redirect()->route('admin.media.index')
                ->with('error', 'Failed to download file: ' . $e->getMessage());
        }
    }



    /**
     * Upload new media files
     */
    public function upload(Request $request)
    {
        $this->authorize('create media');

        $request->validate([
            'files' => 'required|array',
            'files.*' => 'file|max:10240', // 10MB max
            'model_type' => 'nullable|string',
            'model_id' => 'nullable|integer',
            'collection' => 'nullable|string'
        ]);

        $uploaded = [];
        $errors = [];

        foreach ($request->file('files') as $file) {
            try {
                // Determine the model
                $model = null;
                if ($request->model_type && $request->model_id) {
                    $modelClass = $request->model_type;
                    if (class_exists($modelClass)) {
                        $model = $modelClass::find($request->model_id);
                    }
                }

                // If no model specified, attach to current user
                if (!$model) {
                    $model = auth()->user();
                }

                // Add media to collection
                $collection = $request->collection ?? 'default';
                $media = $model->addMedia($file)->toMediaCollection($collection);
                
                $uploaded[] = $file->getClientOriginalName();
            } catch (\Exception $e) {
                $errors[] = $file->getClientOriginalName() . ': ' . $e->getMessage();
            }
        }

        $message = '';
        if (count($uploaded) > 0) {
            $message .= 'Uploaded: ' . implode(', ', $uploaded) . '. ';
        }
        if (count($errors) > 0) {
            $message .= 'Errors: ' . implode(', ', $errors);
        }

        return redirect()->route('admin.media.index')
            ->with(count($errors) > 0 ? 'warning' : 'success', $message);
    }

    /**
     * Get media statistics by collection
     */
    public function statistics()
    {
        $this->authorize('view media');

        $stats = [
            'by_collection' => Media::selectRaw('collection_name, COUNT(*) as count, SUM(size) as total_size')
                ->groupBy('collection_name')
                ->get(),
            
            'by_type' => Media::selectRaw("
                CASE 
                    WHEN mime_type LIKE 'image/%' THEN 'Images'
                    WHEN mime_type LIKE 'video/%' THEN 'Videos'
                    WHEN mime_type LIKE 'application/pdf' THEN 'PDFs'
                    ELSE 'Others'
                END as type,
                COUNT(*) as count,
                SUM(size) as total_size
            ")
                ->groupBy(DB::raw("
                    CASE 
                        WHEN mime_type LIKE 'image/%' THEN 'Images'
                        WHEN mime_type LIKE 'video/%' THEN 'Videos'
                        WHEN mime_type LIKE 'application/pdf' THEN 'PDFs'
                        ELSE 'Others'
                    END
                "))
                ->get(),
            
            'by_model' => Media::selectRaw('model_type, COUNT(*) as count, SUM(size) as total_size')
                ->groupBy('model_type')
                ->get()
                ->map(function($item) {
                    $item->model_name = class_basename($item->model_type);
                    return $item;
                }),
            
            'recent_uploads' => Media::orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
            
            'largest_files' => Media::orderBy('size', 'desc')
                ->limit(10)
                ->get()
        ];

        return view('admin.media.statistics', compact('stats'));
    }

    /**
     * Clean up orphaned media
     */
    public function cleanup()
    {
        $this->authorize('delete media');

        // Find orphaned media (where model doesn't exist)
        $orphaned = Media::all()->filter(function($media) {
            if (!$media->model_type || !$media->model_id) {
                return true;
            }
            
            $modelClass = $media->model_type;
            if (!class_exists($modelClass)) {
                return true;
            }
            
            return !$modelClass::find($media->model_id);
        });

        $count = $orphaned->count();
        
        foreach ($orphaned as $media) {
            $media->delete();
        }

        return redirect()->route('admin.media.index')
            ->with('success', "Cleaned up {$count} orphaned media files.");
    }
}