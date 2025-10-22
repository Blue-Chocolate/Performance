<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CriteriaQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'criteria_axis_id',
        'question_text',
        'dropdown_options',
        'points_mapping',
        'attachment_required',
    ];

    protected $casts = [
        'dropdown_options' => 'array',
        'points_mapping' => 'array',
    ];

    public function axis()
    {
        return $this->belongsTo(CriteriaAxis::class, 'criteria_axis_id');
    }
}
