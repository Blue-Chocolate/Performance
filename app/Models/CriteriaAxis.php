<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CriteriaAxis extends Model
{
    protected $fillable = [
        'name',
        'description',
        'path',
        'weight',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(CriteriaQuestion::class);
    }
}