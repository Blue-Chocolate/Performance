<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    protected $fillable = ['user_id','name','sector','established_at','email','phone','address','final_score'];

    public function axisResponses()
    {
        return $this->hasMany(AxisResponse::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
