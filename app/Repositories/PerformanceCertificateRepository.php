<?php

namespace App\Repositories;

use App\Models\{
    PerformanceCertificate,
    CriteriaAxis,
    CriteriaQuestion,
    Answer
};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PerformanceCertificateRepository
{
    /**
     * Create a new performance certificate
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
            ->with(['questions' => function($query) {
                $query->select('id', 'criteria_axis_id', 'question_text', 'options', 'points_mapping', 'attachment_required', 'weight')
                      ->orderBy('id');
            }])
            ->orderBy('id')
            ->get();
    }

    /**
     * Get questions for validation (flattened)
     */
    public function getQuestionsForValidation(string $path)
    {
        return CriteriaQuestion::where('path', $path)
            ->orderBy('id')
            ->get();
    }

    /**
     * 💾 Save answers with attachments (unified logic for all paths)
     */
    public function saveAnswersWithAttachments(int $certificateId, array $data, string $path): array
    {
        return DB::transaction(function () use ($certificateId, $data, $path) {
            $totalScore = 0;
            $answersData = $data['answers'];

            foreach ($answersData as $answerInput) {
                $question = CriteriaQuestion::findOrFail($answerInput['question_id']);
                
                // 🧹 Clean the selected option (remove quotes, trim whitespace)
                $selectedOption = $answerInput['selected_option'];
                $selectedOption = trim($selectedOption, '"\'');  // Remove quotes
                $selectedOption = trim($selectedOption);         // Remove whitespace
                
                // ✅ Validate that selected option exists in question options
                if (!$this->isValidOption($question, $selectedOption)) {
                    throw new \Exception("Invalid option selected for question {$question->id}");
                }

                // 📎 Check if attachment is required but missing
                if ($question->attachment_required && !isset($answerInput['attachment'])) {
                    throw new \Exception("Attachment is required for question {$question->id}");
                }

                // 📊 Calculate points using path-specific logic
                $points = $this->calculatePoints($question, $selectedOption, $path);
                $finalPoints = $this->applyWeight($points, $question, $path);

                // 📎 Handle attachment upload
                $attachmentPath = null;
                if (isset($answerInput['attachment']) && $answerInput['attachment']) {
                    $file = $answerInput['attachment'];
                    $attachmentPath = $file->store("attachments/{$path}/{$certificateId}", 'public');
                }

                // 💾 Store answer
                Answer::create([
                    'certificate_id' => $certificateId,
                    'question_id' => $question->id,
                    'selected_option' => $selectedOption,
                    'points' => $points,
                    'final_points' => $finalPoints,
                    'attachment_path' => $attachmentPath,
                ]);

                $totalScore += $finalPoints;
            }

            // 🏆 Calculate rank and update certificate
            $rank = $this->calculateRank($totalScore, $path);
            
            $certificate = PerformanceCertificate::findOrFail($certificateId);
            $certificate->update([
                'final_score' => $totalScore,
                'final_rank' => $rank,
            ]);

            return [
                'final_score' => $totalScore,
                'final_rank' => $rank,
            ];
        });
    }

    /**
     * 🔄 Update existing answers with attachments
     */
    public function updateAnswersWithAttachments(int $certificateId, array $data, string $path): array
    {
        return DB::transaction(function () use ($certificateId, $data, $path) {
            // 🗑️ Delete old answers and their attachments
            $certificate = PerformanceCertificate::with('answers')->findOrFail($certificateId);
            
            foreach ($certificate->answers as $answer) {
                if ($answer->attachment_path && Storage::disk('public')->exists($answer->attachment_path)) {
                    Storage::disk('public')->delete($answer->attachment_path);
                }
            }
            
            Answer::where('certificate_id', $certificateId)->delete();

            // 💾 Save new answers
            return $this->saveAnswersWithAttachments($certificateId, $data, $path);
        });
    }

    /**
     * 🗑️ Delete certificate with all files
     */
    public function deleteCertificateWithFiles(PerformanceCertificate $certificate): void
    {
        DB::transaction(function () use ($certificate) {
            // Delete all attachment files
            foreach ($certificate->answers as $answer) {
                if ($answer->attachment_path && Storage::disk('public')->exists($answer->attachment_path)) {
                    Storage::disk('public')->delete($answer->attachment_path);
                }
            }

            // Delete certificate (cascade will delete answers)
            $certificate->delete();
        });
    }

    /**
     * ✅ Validate selected option exists in question
     */
    private function isValidOption(CriteriaQuestion $question, string $selectedOption): bool
    {
        $options = $question->options;
        
        // Handle if options is still a JSON string
        if (is_string($options)) {
            $options = json_decode($options, true);
        }
        
        // Ensure it's an array
        if (!is_array($options)) {
            \Log::error('Options is not an array', [
                'question_id' => $question->id,
                'options_type' => gettype($options),
                'options_value' => $options,
            ]);
            return false;
        }
        
        // Normalize selected option (trim whitespace)
        $normalizedSelected = trim($selectedOption);
        
        // Check exact match first
        if (in_array($normalizedSelected, $options)) {
            return true;
        }
        
        // Try normalized comparison (trim all options)
        $normalizedOptions = array_map('trim', $options);
        if (in_array($normalizedSelected, $normalizedOptions)) {
            return true;
        }
        
        // Log for debugging
        \Log::error('Invalid option selected', [
            'question_id' => $question->id,
            'selected_option' => $selectedOption,
            'selected_hex' => bin2hex($selectedOption),
            'available_options' => $options,
            'normalized_options' => $normalizedOptions,
        ]);
        
        return false;
    }

    /**
     * 📊 Calculate base points from mapping (path-agnostic)
     */
    private function calculatePoints(CriteriaQuestion $question, string $selectedOption, string $path): float
    {
        $mapping = $question->points_mapping;
        
        // Handle if mapping is still a JSON string
        if (is_string($mapping)) {
            $mapping = json_decode($mapping, true);
        }

        if (empty($mapping) || !is_array($mapping)) {
            return 0;
        }

        // Path-specific calculation logic (if needed in future)
        switch ($path) {
            case 'strategic':
                return (float) ($mapping[$selectedOption] ?? 0);
            
            case 'operational':
                return (float) ($mapping[$selectedOption] ?? 0);
            
            case 'hr':
                // HR uses percentage-based points (out of 100)
                return (float) ($mapping[$selectedOption] ?? 0);
            
            default:
                return (float) ($mapping[$selectedOption] ?? 0);
        }
    }

    /**
     * ⚖️ Apply weight to base points (path-specific)
     */
    private function applyWeight(float $points, CriteriaQuestion $question, string $path): float
    {
        switch ($path) {
            case 'strategic':
                // Strategic: Use max_points or weight
                $weight = $question->weight ?? 1.0;
                return $points * $weight;
            
            case 'operational':
                // Operational: Apply weight multiplier
                $weight = $question->weight ?? 1.0;
                return $points * $weight;
            
            case 'hr':
                // HR: Axis weight × question weight × (points/100)
                $axisWeight = $question->axis->weight ?? 100;
                $questionWeight = $question->weight ?? 1.0;
                return ($points / 100) * $axisWeight * $questionWeight;
            
            default:
                return $points;
        }
    }

    /**
     * 🏆 Calculate rank based on total score and path
     */
    private function calculateRank(float $score, string $path): string
    {
        $maxScore = $this->getMaxScore($path);
        $normalizedScore = ($score / $maxScore) * 100;

        return match (true) {
            $normalizedScore >= 86 => 'diamond',
            $normalizedScore >= 76 => 'gold',
            $normalizedScore >= 66 => 'silver',
            $normalizedScore >= 55 => 'bronze',
            default => 'bronze',
        };
    }

    /**
     * 📈 Get maximum possible score for each path
     */
    private function getMaxScore(string $path): float
    {
        switch ($path) {
            case 'strategic':
                // Sum of all max_points in strategic questions
                return CriteriaQuestion::where('path', 'strategic')
                    ->get()
                    ->sum(function($q) {
                        $mapping = $q->points_mapping;
                        $maxPoints = is_array($mapping) ? max($mapping) : 0;
                        return $maxPoints * ($q->weight ?? 1.0);
                    });
            
            case 'operational':
                // Sum of weighted max points
                return CriteriaQuestion::where('path', 'operational')
                    ->get()
                    ->sum(function($q) {
                        $mapping = $q->points_mapping;
                        $maxPoints = is_array($mapping) ? max($mapping) : 0;
                        return $maxPoints * ($q->weight ?? 1.0);
                    });
            
            case 'hr':
                // Sum of all axis weights
                return CriteriaAxis::where('path', 'hr')->sum('weight');
            
            default:
                return 100;
        }
    }
}