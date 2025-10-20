<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'sector',
        'established_at',
        'email',
        'phone',
        'address',
        'final_score',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function axisResponses()
    {
        return $this->hasMany(AxisResponse::class);
    }
    public function axes()
{
    return $this->hasMany(Axis::class);
}
public function axesResponses()
{
    return $this->hasMany(AxisResponse::class);
}

}
