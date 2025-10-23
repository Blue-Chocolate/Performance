<?php

namespace App\Http\Controllers\Api\PerformanceCertificateController;

use App\Http\Controllers\Controller;
use App\Repositories\PerformanceCertificateRepository;
use App\Models\PerformanceCertificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PerformanceCertificateController extends Controller
{
    protected PerformanceCertificateRepository $repo;

    public function __construct(PerformanceCertificateRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * ➊ Create new certificate (organization registration)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'organization_name' => 'required|string|max:255',
            'executive_name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            'license_number' => 'required|string|max:50',
            'path' => 'required|in:strategic,operational,hr',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $certificate = $this->repo->createCertificate($request->all());

        return response()->json([
            'message' => 'تم تسجيل الجهة بنجاح ✅',
            'data' => ['certificate_id' => $certificate->id]
        ], 201);
    }

    /**
     * ➋ Get questions by path
     */
    public function getQuestionsByPath(string $path)
    {
        $axes = $this->repo->getQuestionsByPath($path);
        return response()->json(['data' => $axes]);
    }

    /**
     * ➌ Submit answers (with attachments per axis or path-specific)
     */
    public function submitAnswers(Request $request, int $certificateId)
    {
        $validator = Validator::make($request->all(), [
            'axes' => 'required|array|min:1',
            'axes.*.axis_id' => 'required|integer|exists:criteria_axes,id',
            'axes.*.answers' => 'required|array|min:1',
            'axes.*.answers.*.question_id' => 'required|integer|exists:criteria_questions,id',
            'axes.*.answers.*.selected_option' => 'required|string',
            'axes.*.attachment' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $axesData = [];
        foreach ($request->axes as $index => $axis) {
            $attachmentPath = null;
            if ($request->hasFile("axes.$index.attachment")) {
                $attachmentPath = $request->file("axes.$index.attachment")->store('attachments', 'public');
            }
            $axesData[] = [
                'axis_id' => $axis['axis_id'],
                'attachment_path' => $attachmentPath,
                'answers' => $axis['answers'],
            ];
        }

        $this->repo->saveAxesAnswers($certificateId, $axesData);

        return response()->json([
            'message' => 'تم إرسال الإجابات والملفات بنجاح ✅',
        ]);
    }

    /**
     * ➍ Submit strategic path answers (with individual attachments)
     */
    public function submitStrategicAnswers(Request $request, int $certificateId)
    {
        $validator = Validator::make($request->all(), [
            'report_time' => 'required|string',
            'gov_evaluation' => 'required|string',
            'strategic_performance' => 'required|string',
            'sustainability_report' => 'required|string',
            'impact_report' => 'required|string',
            'report_time_attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'gov_evaluation_attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'strategic_performance_attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'sustainability_attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'impact_attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $this->calculateStrategicPoints($request->all());
        $certificate = PerformanceCertificate::findOrFail($certificateId);

        foreach ([
            'report_time_attachment',
            'gov_evaluation_attachment',
            'strategic_performance_attachment',
            'sustainability_attachment',
            'impact_attachment',
        ] as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = $request->file($field)->store('uploads/strategic', 'public');
            }
        }

        $certificate->update($data);

        return response()->json([
            'message' => 'تم إرسال الإجابات والملفات بنجاح ✅',
            'data' => ['final_rank' => $certificate->final_rank, 'final_score' => $certificate->final_score]
        ]);
    }

    /**
     * ➎ Show final certificate details (with answers)
     */
    public function show(int $id)
    {
        $certificate = PerformanceCertificate::with('answers.question.axis')->findOrFail($id);
        return response()->json(['certificate' => $certificate]);
    }

    /**
     * ➏ Update strategic path answers
     */
    public function updateStrategicAnswers(Request $request, int $id)
    {
        $certificate = PerformanceCertificate::findOrFail($id);

        $validated = $request->validate([
            'report_time' => 'sometimes|string',
            'gov_evaluation' => 'sometimes|string',
            'strategic_performance' => 'sometimes|string',
            'sustainability_report' => 'sometimes|string',
            'impact_report' => 'sometimes|string',
            'report_time_attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'gov_evaluation_attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'strategic_performance_attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'sustainability_attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'impact_attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $data = $this->calculateStrategicPoints(array_merge($certificate->toArray(), $validated));

        foreach ([
            'report_time_attachment',
            'gov_evaluation_attachment',
            'strategic_performance_attachment',
            'sustainability_attachment',
            'impact_attachment',
        ] as $field) {
            if ($request->hasFile($field)) {
                if ($certificate->$field && Storage::disk('public')->exists($certificate->$field)) {
                    Storage::disk('public')->delete($certificate->$field);
                }
                $data[$field] = $request->file($field)->store('uploads/strategic', 'public');
            }
        }

        $certificate->update($data);

        return response()->json($certificate);
    }

    /**
     * ➐ Delete certificate
     */
    public function destroy(int $id)
    {
        $certificate = PerformanceCertificate::findOrFail($id);

        foreach ([
            'report_time_attachment',
            'gov_evaluation_attachment',
            'strategic_performance_attachment',
            'sustainability_attachment',
            'impact_attachment',
        ] as $field) {
            if ($certificate->$field && Storage::disk('public')->exists($certificate->$field)) {
                Storage::disk('public')->delete($certificate->$field);
            }
        }

        $certificate->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }

    /**
     * Calculate points for strategic path
     */
    private function calculateStrategicPoints(array $data): array
    {
        $pointsMapQ1 = [
            '3 أشهر قبل' => 15,
            'بعد 3 أشهر' => 10,
            'بعد 5 أشهر' => 8,
            'بعد 6 أشهر' => 6,
            'بعد 7 أشهر' => 5,
            'بعد 8 أشهر' => 4,
            'بعد 9 أشهر' => 3,
            'بعد 10 أشهر' => 1,
        ];
        $data['report_time_points'] = $pointsMapQ1[$data['report_time']] ?? 0;

        $pointsMapQ2 = [
            'أقل من 65' => 0,
            'من 65 - 75' => 15,
            'من 76 - 85' => 30,
            'من 86 - 100' => 50,
        ];
        $data['gov_evaluation_points'] = $pointsMapQ2[$data['gov_evaluation']] ?? 0;

        $pointsMapQ3 = [
            'أقل من 65%' => 0,
            'من 65 - 75%' => 20,
            'من 76 - 85%' => 40,
            'من 86 - 100%' => 60,
        ];
        $data['strategic_performance_points'] = $pointsMapQ3[$data['strategic_performance']] ?? 0;

        $pointsMapReport = [
            'لن يعد' => 0,
            'جاري الإعداد' => 5,
            'تم الانتهاء ولم يُنشر' => 10,
            'تم النشر' => 15,
        ];
        $data['sustainability_points'] = $pointsMapReport[$data['sustainability_report']] ?? 0;
        $data['impact_points'] = $pointsMapReport[$data['impact_report']] ?? 0;

        $total = $data['report_time_points']
                + $data['gov_evaluation_points']
                + $data['strategic_performance_points']
                + $data['sustainability_points']
                + $data['impact_points'];

        $data['total_points'] = $total;
        $data['final_rank'] = $this->calculateRank($total, 'strategic');

        return $data;
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
            $normalizedScore >= 86 => 'ماسي',
            $normalizedScore >= 76 => 'ذهبي',
            $normalizedScore >= 66 => 'فضي',
            $normalizedScore >= 55 => 'برونزي',
            default => 'برونزي',
        };
    }
}