<?php

namespace App\Http\Controllers\Api\PodcastController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Podcast;
use Illuminate\Support\Facades\DB;

class PodcastController extends Controller
{
   public function index() {
    $podcasts = Podcast::query()
        ->select(
            'id',
            'title',
            'short_description',
            DB::raw("DATE_FORMAT(published_at, '%Y-%m-%d') as published_at"),
            DB::raw('cover_image as image')
        )
        ->latest('published_at')
        ->get();

    return response()->json([
        'success'  => true,
        'podcasts' => $podcasts,
    ]);
}

    public function store(Request $request) {
        $request->validate([
            'title' => 'required|string',
            'audio' => 'required|mimes:mp3,wav|max:20000',
        ]);

        $path = $request->file('audio')->store('podcasts');

        $podcast = Podcast::create([
            'title' => $request->title,
            'description' => $request->description,
            'audio_path' => $path,
        ]);

        return response()->json($podcast, 201);
    }

    public function show(string $id)
{
    // Fetch or fail with 404
    $podcast = Podcast::findOrFail($id);

    // Build the exact response contract your frontend expects
    return response()->json([
        'success' => true, // Indicates the call worked
        'podcast' => [
            'id' => (string) $podcast->id, // Cast to string to match the TS type
            'title' => $podcast->title,
            'short_description' => $podcast->short_description,
            'description' => $podcast->description,
            // Use ISO 8601 so clients can parse reliably
            'published_at' => optional($podcast->published_at)->toIso8601String(),
            'image' => $podcast->image,
            'video_url' => $podcast->video_url,
            'audio_url' => $podcast->audio_url,
        ],
    ], 200);
}
}

