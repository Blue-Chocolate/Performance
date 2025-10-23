<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StrategicPath extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_time',
        'report_time_points',
        'report_time_attachment',
        'gov_evaluation',
        'gov_evaluation_points',
        'gov_evaluation_attachment',
        'strategic_performance',
        'strategic_performance_points',
        'strategic_performance_attachment',
        'sustainability_report',
        'sustainability_points',
        'sustainability_attachment',
        'impact_report',
        'impact_points',
        'impact_attachment',
        'total_points',
        'final_rank',
    ];

    protected $appends = [
        'report_time_attachment_url',
        'gov_evaluation_attachment_url',
        'strategic_performance_attachment_url',
        'sustainability_attachment_url',
        'impact_attachment_url',
    ];

    public function getReportTimeAttachmentUrlAttribute()
    {
        return $this->report_time_attachment ? asset('storage/' . $this->report_time_attachment) : null;
    }

    public function getGovEvaluationAttachmentUrlAttribute()
    {
        return $this->gov_evaluation_attachment ? asset('storage/' . $this->gov_evaluation_attachment) : null;
    }

    public function getStrategicPerformanceAttachmentUrlAttribute()
    {
        return $this->strategic_performance_attachment ? asset('storage/' . $this->strategic_performance_attachment) : null;
    }

    public function getSustainabilityAttachmentUrlAttribute()
    {
        return $this->sustainability_attachment ? asset('storage/' . $this->sustainability_attachment) : null;
    }

    public function getImpactAttachmentUrlAttribute()
    {
        return $this->impact_attachment ? asset('storage/' . $this->impact_attachment) : null;
    }
}
