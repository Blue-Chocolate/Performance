<?php

namespace App\Http\Controllers\Api\PerformanceController;

use App\Http\Controllers\Controller;
use App\Repositories\PerformanceRepository;
use Illuminate\Http\Request;

class PerformanceController extends Controller
{
    protected $repo;

    public function __construct(PerformanceRepository $repo)
    {
        $this->repo = $repo;
    }

    // 1️⃣ جلب الأسئلة حسب المسار
    public function getQuestions(Request $request)
    {
        $path = $request->query('path');

        if (!$path) {
            return response()->json(['error' => 'path is required'], 400);
        }

        $questions = $this->repo->getQuestionsByPath($path);

        return response()->json($questions);
    }

    // 2️⃣ استلام الإجابات وتخزينها وحساب النقاط
    public function submitAnswers(Request $request, $id)
{
    $validated = $request->validate([
        'answers' => 'required|array',
        'answers.*.question_id' => 'required|exists:criteria_questions,id',
        'answers.*.selected_option' => 'required|string',
        'answers.*.attachment_path' => 'nullable|string',
    ]);

    $this->repo->storeAnswers($id, $validated['answers']);

    return response()->json(['message' => 'Answers submitted and score calculated successfully']);
}
}
