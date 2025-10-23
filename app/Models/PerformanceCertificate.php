<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerformanceCertificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_name',
        'executive_name',
        'email',
        'phone',
        'license_number',
        'path',
        'final_score',
        'final_rank',
        'weight',
        'final_points',

    ];

    /**
     * العلاقة مع الإجابات
     */
    public function answers()
    {
        return $this->hasMany(Answer::class, 'certificate_id');
    }

    /**
     * جلب المحاور الخاصة بالمسار
     */
    public function criteriaAxes()
    {
        return CriteriaAxis::where('path', $this->path)->get();
    }
}
