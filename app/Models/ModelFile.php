<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelFile extends Model
{
    use HasFactory;

    protected $table = 'models';

    protected $fillable = [
        'name',
        'description',
        'file_path',
    ];
}
