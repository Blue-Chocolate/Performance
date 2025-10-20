<?php

namespace App\Http\Controllers\Api\PodcastController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Podcast;

class PodcastController extends Controller
{
    public function index() {
        return Podcast::all();
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

    public function show($id) {
        return Podcast::findOrFail($id);
    }
}

