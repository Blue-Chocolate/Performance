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
        'options',
        'points_mapping',
        'attachment_required',
        'path',
        'max_points',
        'weight',
    ];

    protected $casts = [
        'options' => 'array',
        'points_mapping' => 'array',
    ];

    /**
     * العلاقة مع المحور
     */
    public function axis()
    {
        return $this->belongsTo(CriteriaAxis::class, 'criteria_axis_id');
    }

    /**
     * العلاقة مع الإجابات
     */
    public function answers()
    {
        return $this->hasMany(Answer::class, 'question_id');
    }
}
