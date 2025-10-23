<?php

namespace App\Repositories;

use App\Models\{
    PerformanceCertificate,
    CriteriaAxis,
    CriteriaQuestion,
    Answer
};
use Illuminate\Support\Facades\DB;

class PerformanceCertificateRepository
{
    /**
     * Create a new performance certificate record
     */
    public function createCertificate(array $data): PerformanceCertificate
    {
        return PerformanceCertificate::create([
            'organization_name' => $data['organization_name'],
            'executive_name' => $data['executive_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'license_number' => $data['license_number'],
            'path' => $data['path'],
        ]);
    }

    /**
     * Get all axes + questions by path
     */
    public function getQuestionsByPath(string $path)
    {
        return CriteriaAxis::where('path', $path)
            ->with('questions:id,criteria_axis_id,question_text,options,points_mapping,attachment_required')
            ->get();
    }

    /**
     * Save all answers grouped by axis (supports one file per axis)
     */
    public function saveAxesAnswers(int $certificateId, array $axesData)
    {
        DB::transaction(function () use ($certificateId, $axesData) {
            $totalScore = 0;

            foreach ($axesData as $axis) {
                $attachmentPath = $axis['attachment_path'] ?? null;

                foreach ($axis['answers'] as $answer) {
                    $question = CriteriaQuestion::findOrFail($answer['question_id']);
                    $points = $this->calculatePoints($question, $answer['selected_option']);

                    Answer::create([
                        'certificate_id' => $certificateId,
                        'question_id' => $question->id,
                        'selected_option' => $answer['selected_option'],
                        'points' => $points,
                        'attachment_path' => $attachmentPath,
                    ]);

                    $totalScore += $points;
                }
            }

            $certificate = PerformanceCertificate::findOrFail($certificateId);
            $certificate->update([
                'final_score' => $totalScore,
                'final_rank' => $this->calculateRank($totalScore, $certificate->path),
            ]);
        });
    }

    /**
     * Calculate points for a selected option from JSON mapping
     */
    private function calculatePoints(CriteriaQuestion $question, string $selectedOption): float
    {
        if (empty($question->points_mapping)) {
            return 0;
        }

        $mapping = json_decode($question->points_mapping, true);

        if (!is_array($mapping)) {
            return 0;
        }

        return (float)($mapping[$selectedOption] ?? 0);
    }

    /**
     * Determine rank based on total score and path
     */
    private function calculateRank(float $score, string $path): string
    {
        $maxScore = match ($path) {
            'strategic' => 150,
            'operational' => 136, // Based on previous context
            'hr' => 100, // Placeholder; adjust as needed
            default => 100,
        };
        $normalizedScore = ($score / $maxScore) * 100;

        return match (true) {
            $normalizedScore >= 86 => 'diamond',
            $normalizedScore >= 76 => 'gold',
            $normalizedScore >= 66 => 'silver',
            $normalizedScore >= 55 => 'bronze',
            default => 'bronze',
        };
    }
}