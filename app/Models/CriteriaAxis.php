<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CriteriaAxis extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'path',
    ];

    /**
     * العلاقة مع الأسئلة
     */
    public function questions()
    {
        return $this->hasMany(CriteriaQuestion::class, 'criteria_axis_id');
    }
}
