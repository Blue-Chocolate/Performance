<?php

namespace App\Repositories;

use App\Models\Blog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BlogRepository
{
    /**
     * Get paginated list of blogs
     */
    public function getAll(int $limit = 10): LengthAwarePaginator
    {
        return Blog::query()
            ->select('id', 'title', 'description', 'image', 'published_at')
            ->orderBy('published_at', 'desc')
            ->paginate($limit);
    }

    /**
     * Find blog by ID
     */
    public function findById(int $id): ?Blog
    {
        return Blog::find($id);
    }
}
