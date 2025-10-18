<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AxisResponse extends Model
{
    protected $fillable = [
       'organization_id','axis_id','q1','q2','q3','q4',
       'attachment_1','attachment_2','attachment_3','admin_score'
    ];

    public function organization() { return $this->belongsTo(Organization::class); }
    public function axis() { return $this->belongsTo(Axis::class); }
}
