<?php

namespace App\Http\Controllers\Api\ReleaseController;

use App\Http\Controllers\Controller;
use App\Models\Release;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReleaseController extends Controller
{
    /**
     * Get all releases (public)
     */
    public function index(Request $request)
    {
        // Clamp per_page between 1 and 100
        $perPage = (int) $request->query('per_page', 10);
        $perPage = max(1, min(100, $perPage));

        // Base query
        $query = Release::query()
            ->select('id', 'title', 'description', 'image', 'created_at')
            ->orderBy('created_at', 'desc');

        // Paginate results
        $paginator = $query->paginate($perPage)->appends($request->query());

        // Transform items to desired format
        $items = $paginator->getCollection()->map(function (Release $r) {
            return [
                'id' => (string) $r->id,
                'title' => (string) $r->title,
                'short_description' => $r->description
                    ? Str::limit(trim(strip_tags($r->description)), 160)
                    : '',
                // If you have a published_at column, replace created_at with published_at
                'published_date' => optional($r->created_at)->toDateString(),
                // Prepend full URL if relative path
                'image' => $r->image ? url('storage/' . $r->image) : null,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $items,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'has_more' => $paginator->hasMorePages(),
            ],
        ]);
    }

    /**
     * Store a new release (admin only)
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf|max:20480',
            'excel' => 'nullable|file|mimes:xlsx,xls|max:20480',
            'powerbi' => 'nullable|file|mimes:pbix|max:51200',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        $pdfPath = $request->hasFile('file')
            ? $request->file('file')->store('releases', 'public')
            : null;

        $excelPath = $request->hasFile('excel')
            ? $request->file('excel')->store('releases', 'public')
            : null;

        $powerbiPath = $request->hasFile('powerbi')
            ? $request->file('powerbi')->store('releases', 'public')
            : null;

        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('releases/images', 'public')
            : null;

        $release = Release::create([
            'title' => $request->title,
            'description' => $request->description,
            'file_path' => $pdfPath,
            'excel_path' => $excelPath,
            'powerbi_path' => $powerbiPath,
            'image' => $imagePath,
        ]);

        return response()->json([
            'message' => 'Release created successfully',
            'data' => $release,
        ]);
    }

    /**
     * Download file (requires authentication)
     */
    public function download($id, $type = 'pdf')
    {
        if (!Auth::guard('sanctum')->check() && !Auth::check()) {
            return response()->json([
                'error' => 'Unauthorized. Please login first.',
                'redirect' => '/login',
            ], 401);
        }

        $release = Release::find($id);
        if (!$release) {
            return response()->json(['error' => 'Release not found.'], 404);
        }

        // Determine file path
        $path = match ($type) {
            'pdf' => $release->file_path,
            'excel' => $release->excel_path,
            'powerbi' => $release->powerbi_path,
            default => null,
        };

        if (!$path) {
            return response()->json(['error' => 'File type not found.'], 404);
        }

        $fullPath = storage_path('app/public/' . $path);

        if (!file_exists($fullPath)) {
            return response()->json(['error' => 'File not found.'], 404);
        }

        $fileName = $release->title . '.' . match ($type) {
            'pdf' => 'pdf',
            'excel' => 'xlsx',
            'powerbi' => 'pbix',
        };

        return response()->download($fullPath, $fileName);
    }
    public function show($id)
{
    $release = Release::select(
        'id',
        'title',
        'description',
        'image',
        'file_path',
        'excel_path',
        'powerbi_path',
        'created_at'
    )->find($id);

    if (!$release) {
        return response()->json([
            'success' => false,
            'message' => 'Release not found.'
        ], 404);
    }

    return response()->json([
        'success' => true,
        'data' => [
            'id' => (string) $release->id,
            'title' => (string) $release->title,
            'description' => (string) $release->description,
            'published_date' => optional($release->created_at)->toDateString(),
            'image' => $release->image ? url('storage/' . $release->image) : null,
            'files' => [
                'pdf' => $release->file_path ? url('storage/' . $release->file_path) : null,
                'excel' => $release->excel_path ? url('storage/' . $release->excel_path) : null,
                'powerbi' => $release->powerbi_path ? url('storage/' . $release->powerbi_path) : null,
            ],
        ],
    ]);
}
}
