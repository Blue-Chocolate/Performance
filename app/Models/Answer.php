<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    use HasFactory;

    protected $fillable = [
        'certificate_id',
        'question_id',
        'selected_option',
        'points',
        'final_points',
        'attachment_path',
    ];
    protected $casts = [
    'points' => 'float',
    'final_points' => 'float',
];

    /**
     * العلاقة مع الشهادة
     */
    public function certificate()
    {
        return $this->belongsTo(PerformanceCertificate::class, 'certificate_id');
    }

    /**
     * العلاقة مع السؤال
     */
    public function question()
    {
        return $this->belongsTo(CriteriaQuestion::class, 'question_id');
    }
}
