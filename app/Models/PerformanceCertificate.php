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
    ];

    public function answers()
    {
        return $this->hasMany(Answer::class, 'certificate_id');
    }
}
