<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Podcast extends Model
{
    protected $fillable = ['title', 'description', 'audio_path', 'video_path'];
}
