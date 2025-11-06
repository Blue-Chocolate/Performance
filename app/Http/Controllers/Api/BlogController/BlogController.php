<?php

namespace App\Http\Controllers\Api\BlogController;

use App\Http\Controllers\Controller;
use App\Repositories\BlogRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    public function __construct(
        protected BlogRepository $repo
    ) {}

    /**
     * GET /api/blogs?page={n}&limit={n}
     */
    public function index(Request $request)
    {
        $limit = (int) $request->query('limit', 10);
        $limit = max(1, min(100, $limit));

        $blogs = $this->repo->getAll($limit);

        $formatted = $blogs->getCollection()->map(function ($b) {
            return [
                'id' => (string) $b->id,
                'title' => $b->title,
                'description' => Str::limit(strip_tags($b->description), 200),
                'published_date' => optional($b->published_at ?? $b->created_at)->toDateString(),
                'image' => $b->image ? url('storage/' . $b->image) : null,
            ];
        });

        return response()->json([
            'success' => true,
            'blogs' => $formatted,
            'pagination' => [
                'current_page' => $blogs->currentPage(),
                'limit' => $blogs->perPage(),
                'total' => $blogs->total(),
                'last_page' => $blogs->lastPage(),
                'has_more' => $blogs->hasMorePages(),
            ],
        ]);
    }

    /**
     * GET /api/blogs/{blog_id}
     */
    public function show($id)
    {
        $blog = $this->repo->findById($id);

        if (!$blog) {
            return response()->json([
                'success' => false,
                'message' => 'Blog not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'blog' => [
                'id' => (string) $blog->id,
                'title' => $blog->title,
                'description' => $blog->description,
                'content' => $blog->content,
                'author' => $blog->author ?? 'Unknown',
                'published_date' => optional($blog->published_at ?? $blog->created_at)->toDateString(),
                'image' => $blog->image ? url('storage/' . $blog->image) : null,
            ],
        ]);
    }
}
