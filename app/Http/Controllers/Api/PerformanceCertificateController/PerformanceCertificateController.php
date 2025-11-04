<?php

namespace App\Http\Controllers\Api\PerformanceCertificateController;

use App\Http\Controllers\Controller;
use App\Repositories\PerformanceCertificateRepository;
use App\Models\PerformanceCertificate;
use Illuminate\Http\Request;
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
     * ➋ Get questions by path (with axes structure)
     */
    public function getQuestionsByPath(string $path)
    {
        if (!in_array($path, ['strategic', 'operational', 'hr'])) {
            return response()->json(['error' => 'Invalid path'], 400);
        }

        $axes = $this->repo->getQuestionsByPath($path);
        
        // Add debugging info for each question
        $axes->transform(function ($axis) {
            $axis->questions->transform(function ($question) {
                // Ensure options are properly decoded
                $options = $question->options;
                if (is_string($options)) {
                    $options = json_decode($options, true);
                }
                
                $question->options_debug = [
                    'type' => gettype($question->options),
                    'count' => is_array($options) ? count($options) : 0,
                    'values' => $options,
                ];
                
                return $question;
            });
            return $axis;
        });
        
        return response()->json(['data' => $axes]);
    }

    /**
     * ➌ Submit answers (dynamic validation & processing)
     */
    public function submitAnswers(Request $request, int $certificateId)
    {
        // 🔍 Get certificate to know which path
        $certificate = PerformanceCertificate::findOrFail($certificateId);
        
        // 🎯 Dynamic validation based on path
        $validator = $this->buildDynamicValidator($request, $certificate->path);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // 💾 Process answers with path-specific logic
        $result = $this->repo->saveAnswersWithAttachments($certificateId, $request->all(), $certificate->path);

        return response()->json([
            'message' => 'تم إرسال الإجابات والملفات بنجاح ✅',
            'data' => [
                'final_score' => $result['final_score'],
                'final_rank' => $result['final_rank'],
            ]
        ]);
    }

    /**
     * ➍ Show certificate details with all answers
     */
    public function show(int $id)
    {
        $certificate = PerformanceCertificate::with([
            'answers.question.axis',
            'answers' => function($query) {
                $query->orderBy('question_id');
            }
        ])->findOrFail($id);

        return response()->json(['certificate' => $certificate]);
    }

    /**
     * ➎ Update answers (for corrections/edits)
     */
    public function updateAnswers(Request $request, int $certificateId)
    {
        $certificate = PerformanceCertificate::findOrFail($certificateId);
        
        $validator = $this->buildDynamicValidator($request, $certificate->path);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $result = $this->repo->updateAnswersWithAttachments($certificateId, $request->all(), $certificate->path);

        return response()->json([
            'message' => 'تم تحديث الإجابات بنجاح ✅',
            'data' => [
                'final_score' => $result['final_score'],
                'final_rank' => $result['final_rank'],
            ]
        ]);
    }

    /**
     * ➏ Delete certificate with all related data
     */
    public function destroy(int $id)
    {
        $certificate = PerformanceCertificate::with('answers')->findOrFail($id);
        $this->repo->deleteCertificateWithFiles($certificate);

        return response()->json(['message' => 'تم الحذف بنجاح ✅']);
    }

    /**
     * 🎯 Build dynamic validator based on path
     */
    private function buildDynamicValidator(Request $request, string $path)
    {
        $rules = [
            'answers' => 'required|array|min:1',
            'answers.*.question_id' => 'required|integer|exists:criteria_questions,id',
            'answers.*.selected_option' => 'required|string',
            'answers.*.attachment' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ];

        return Validator::make($request->all(), $rules);
    }
}