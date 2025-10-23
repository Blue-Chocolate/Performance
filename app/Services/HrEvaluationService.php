<?php

namespace App\Services;

use App\Repositories\HrEvaluationRepository;
use App\Models\PerformanceCertificate;
use App\Models\CriteriaQuestion;
use App\Models\Answer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HrEvaluationService
{
    public function __construct(
        private HrEvaluationRepository $repo
    ) {}

    /**
     * Create a new HR evaluation certificate
     */
    public function createCertificate(array $data): PerformanceCertificate
    {
        return DB::transaction(fn() => PerformanceCertificate::create([
            'organization_name' => $data['organization_name'],
            'executive_name'    => $data['executive_name'],
            'email'             => $data['email'],
            'phone'             => $data['phone'],
            'license_number'    => $data['license_number'],
            'path'              => $data['path'],
        ]));
    }

    /**
     * Submit a full HR evaluation (all axes + questions + attachments)
     */
    public function submitEvaluation(int $certificateId, array $axesData)
    {
        return $this->repo->saveEvaluation($certificateId, $axesData);
    }

    /**
     * Upload HR evidence (attachment) for a specific question
     */
    public function uploadEvidence(int $certificateId, int $questionId, $file)
    {
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs("hr_evaluations/$certificateId", $fileName, 'public');

        $answer = Answer::where('certificate_id', $certificateId)
            ->where('question_id', $questionId)
            ->first();

        if ($answer) {
            $answer->update(['attachment_path' => $filePath]);
        }

        return [
            'path' => $filePath,
            'url'  => Storage::url($filePath),
        ];
    }

    /**
     * Recalculate the HR evaluation score and update rank
     */
    public function recalculateScore(int $certificateId)
    {
        $answers = Answer::where('certificate_id', $certificateId)->get();
        $totalScore = $answers->sum('points');

        $certificate = PerformanceCertificate::findOrFail($certificateId);
        $maxScore = $answers->map(function ($answer) {
            $question = CriteriaQuestion::find($answer->question_id);
            return $question ? $this->getMaxPossiblePoints($question) : 0;
        })->sum();

        $percentage = $maxScore > 0 ? ($totalScore / $maxScore) * 100 : 0;
        $rank = $this->calculateRank($percentage);

        $certificate->update([
            'final_score' => round($percentage, 2),
            'final_rank'  => $rank,
        ]);

        return [
            'total_score' => $totalScore,
            'percentage'  => round($percentage, 2),
            'final_rank'  => $rank,
        ];
    }

    /**
     * Get full HR evaluation details (with answers, axes, etc.)
     */
    public function getEvaluationDetails(int $certificateId)
    {
        return PerformanceCertificate::with([
            'answers.question.axis'
        ])->findOrFail($certificateId);
    }

    /**
     * Helper: max possible points (reuse repo logic style)
     */
    private function getMaxPossiblePoints(CriteriaQuestion $question): float
    {
        if (empty($question->points_mapping)) {
            return 0;
        }

        $mapping = json_decode($question->points_mapping, true);
        $max = max($mapping);

        return $max * ($question->weight ?? 1);
    }

    /**
     * Helper: rank calculation (same thresholds as repo)
     */
    private function calculateRank(float $percentage): string
    {
        return match (true) {
            $percentage >= 90 => 'diamond',
            $percentage >= 75 => 'gold',
            $percentage >= 60 => 'silver',
            default           => 'bronze',
        };
    }
}
