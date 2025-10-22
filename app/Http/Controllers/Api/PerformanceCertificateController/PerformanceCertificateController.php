<?php 

namespace App\Http\Controllers\Api\PerformanceCertificateController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\PerformanceCertificateRepository;
use App\Models\PerformanceCertificate;
use Illuminate\Support\Facades\Validator;

class PerformanceCertificateController extends Controller
{
    protected $repo;

    public function __construct(PerformanceCertificateRepository $repo)
    {
        $this->repo = $repo;
    }

    // ➊ إنشاء شهادة جديدة
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'organization_name' => 'required|string|max:255',
            'executive_name'    => 'required|string|max:255',
            'email'             => 'required|email',
            'phone'             => 'required|string|max:20',
            'license_number'    => 'required|string|max:50',
            'path'              => 'required|in:strategic,operational,hr',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $certificate = $this->repo->createCertificate($request->all());

        return response()->json([
            'message' => 'تم تسجيل الجهة بنجاح',
            'data' => ['certificate_id' => $certificate->id]
        ]);
    }

    // ➋ جلب الأسئلة الخاصة بالمسار
    public function getQuestionsByPath(string $path)
    {
        $axes = $this->repo->getQuestionsByPath($path);
        return response()->json(['data' => $axes]);
    }

    // ➌ استلام الإجابات + رفع الملفات + حساب النقاط
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
            $path = null;

            if ($request->hasFile("axes.$index.attachment")) {
                $path = $request->file("axes.$index.attachment")->store('attachments', 'public');
            }

            $axesData[] = [
                'axis_id' => $axis['axis_id'],
                'attachment_path' => $path,
                'answers' => $axis['answers'],
            ];
        }

        $this->repo->saveAxesAnswers($certificateId, $axesData);

        $certificate = PerformanceCertificate::findOrFail($certificateId);
        $this->repo->autoCalculateFinal($certificate);

        return response()->json([
            'message' => 'تم إرسال الإجابات والملفات بنجاح ✅',
        ]);
    }
}
