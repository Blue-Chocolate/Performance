<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CriteriaQuestion extends Model
{
    protected $fillable = [
        'criteria_axis_id',
        'question_text',
        'options',
        'points_mapping',
        'attachment_required',
        'path',
        'weight',
    ];

    protected $casts = [
        'options' => 'array',
        'points_mapping' => 'array',
        'attachment_required' => 'boolean',
        'weight' => 'decimal:2',
    ];

    /**
     * Get the options attribute and ensure it's an array
     */
    public function getOptionsAttribute($value)
    {
        if (is_string($value)) {
            return json_decode($value, true) ?? [];
        }
        return $value ?? [];
    }

    /**
     * Get the points_mapping attribute and ensure it's an array
     */
    public function getPointsMappingAttribute($value)
    {
        if (is_string($value)) {
            return json_decode($value, true) ?? [];
        }
        return $value ?? [];
    }

    public function axis(): BelongsTo
    {
        return $this->belongsTo(CriteriaAxis::class, 'criteria_axis_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class, 'question_id');
    }
}