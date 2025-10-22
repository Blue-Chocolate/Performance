<?php

namespace App\Repositories;

use App\Models\PerformanceCertificate;
use App\Models\CriteriaQuestion;
use App\Models\Answer;
use Illuminate\Support\Facades\DB;

class PerformanceRepository
{
    // جلب الأسئلة بناءً على المسار
    public function getQuestionsByPath(string $path)
    {
        return CriteriaQuestion::where('path', $path)
            ->with('axis')
            ->get();
    }

    // حفظ الإجابات الخاصة بكل محور (مع ملف واحد فقط)
    public function saveAxesAnswers($certificateId, array $axesData)
    {
        DB::transaction(function () use ($certificateId, $axesData) {
            $totalScore = 0;

            foreach ($axesData as $axisData) {
                $attachmentPath = $axisData['attachment_path'] ?? null;

                foreach ($axisData['answers'] as $answer) {
                    $question = CriteriaQuestion::findOrFail($answer['question_id']);

                    $pointsMap = $question->points_mapping;
                    $points = $pointsMap[$answer['selected_option']] ?? 0;

                    Answer::create([
                        'certificate_id' => $certificateId,
                        'axis_id' => $axisData['axis_id'],
                        'question_id' => $question->id,
                        'selected_option' => $answer['selected_option'],
                        'points' => $points,
                        'attachment_path' => $attachmentPath, // نفس الملف للمحور كله
                    ]);

                    $totalScore += $points;
                }
            }

            $certificate = PerformanceCertificate::findOrFail($certificateId);
            $certificate->final_score = $totalScore;
            $certificate->final_rank = $this->calculateRank($totalScore);
            $certificate->save();
        });
    }

    private function calculateRank($score)
    {
        if ($score >= 90) return 'diamond';
        if ($score >= 75) return 'gold';
        if ($score >= 60) return 'silver';
        return 'bronze';
    }
}
