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
    public function createCertificate(array $data): PerformanceCertificate
    {
        return PerformanceCertificate::create([
            'organization_name' => $data['organization_name'],
            'executive_name'    => $data['executive_name'],
            'email'             => $data['email'],
            'phone'             => $data['phone'],
            'license_number'    => $data['license_number'],
            'path'              => $data['path'],
        ]);
    }

    public function getQuestionsByPath(string $path)
    {
        return CriteriaAxis::where('path', $path)
            ->with('questions:id,criteria_axis_id,question_text,dropdown_options,attachment_required')
            ->get();
    }

    /**
     * المستخدم يجاوب، والسيستم يحسب النقاط بناءً على الـ mapping تلقائيًا.
     */
    public function saveAnswers(int $certificateId, array $answers)
    {
        DB::transaction(function () use ($certificateId, $answers) {
            foreach ($answers as $ans) {
                $question = CriteriaQuestion::find($ans['question_id']);
                $points = $this->calculatePoints($question, $ans['selected_option']);

                Answer::create([
                    'certificate_id'  => $certificateId,
                    'question_id'     => $ans['question_id'],
                    'selected_option' => $ans['selected_option'],
                    'points'          => $points,
                    'attachment_path' => $ans['attachment_path'] ?? null,
                ]);
            }
        });
    }

    /**
     * تحديد النقاط المقابلة للإجابة
     */
    private function calculatePoints(CriteriaQuestion $question, string $selectedOption): float
    {
        $mapping = $question->points_mapping ?? [];
        return $mapping[$selectedOption] ?? 0;
    }

    /**
     * حساب النتيجة النهائية والتصنيف تلقائيًا (بدون عرضها للمستخدم)
     */
    public function autoCalculateFinal(PerformanceCertificate $certificate)
    {
        $totalPoints = $certificate->answers()->sum('points');

        $rank = match (true) {
            $totalPoints >= 90 => 'diamond',
            $totalPoints >= 75 => 'gold',
            $totalPoints >= 50 => 'silver',
            default            => 'bronze',
        };

        $certificate->update([
            'final_score' => $totalPoints,
            'final_rank'  => $rank,
        ]);
    }
}
