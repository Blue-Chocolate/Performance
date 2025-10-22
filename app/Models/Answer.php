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
        'attachment_path',
    ];

    public function certificate()
    {
        return $this->belongsTo(PerformanceCertificate::class);
    }

    public function question()
    {
        return $this->belongsTo(CriteriaQuestion::class);
    }
}
