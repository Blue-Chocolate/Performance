<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Axis extends Model
{
    protected $fillable = ['title','description','weight'];

    public function axisResponses()
    {
        return $this->hasMany(AxisResponse::class);
    }
}

