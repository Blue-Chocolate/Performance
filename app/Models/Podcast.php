<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Podcast extends Model
{
    protected $fillable = ['title', 'description','short_description', 'cover_image','audio_path', 'video_path'];
}
