<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StrategicPath;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StrategicPathController extends Controller
{
    public function index()
    {
        return response()->json(StrategicPath::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
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

        $data = $this->calculatePoints($validated);

        // ✅ حفظ كل مرفق باسمه
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

        $strategic = StrategicPath::create($data);

        return response()->json($strategic, 201);
    }

    private function calculatePoints($data)
    {
        $pointsMapQ1 = [
            '3 أشهر قبل' => 10,
            'بعد 3 أشهر' => 8,
            'بعد 5 أشهر' => 6,
            'بعد 6 أشهر' => 5,
            'بعد 7 أشهر' => 4,
            'بعد 8 أشهر' => 3,
            'بعد 9 أشهر' => 2,
            'بعد 10 أشهر' => 1,
        ];
        $data['report_time_points'] = $pointsMapQ1[$data['report_time']] ?? 0;

        $pointsMapQ2 = [
            'أقل من 65' => 0,
            'من 65 - 75' => 8,
            'من 76 - 85' => 12,
            'من 86 - 100' => 15,
        ];
        $data['gov_evaluation_points'] = $pointsMapQ2[$data['gov_evaluation']] ?? 0;

        $pointsMapQ3 = [
            '%65 نﻣ لﻗأ' => 0,
            '%75 - 65 نﻣ' => 5,
            '%85 - 76 نﻣ' => 10,
            '%100 - 86 نﻣ' => 15,
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

        if ($total >= 86) $data['final_rank'] = 'ماسي';
        elseif ($total >= 76) $data['final_rank'] = 'ذهبي';
        elseif ($total >= 66) $data['final_rank'] = 'برونزي';
        else $data['final_rank'] = 'فضي';

        return $data;
    }

    public function show($id)
    {
        return response()->json(StrategicPath::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $strategic = StrategicPath::findOrFail($id);

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

        $data = $this->calculatePoints(array_merge($strategic->toArray(), $validated));

        foreach ([
            'report_time_attachment',
            'gov_evaluation_attachment',
            'strategic_performance_attachment',
            'sustainability_attachment',
            'impact_attachment',
        ] as $field) {
            if ($request->hasFile($field)) {
                if ($strategic->$field && Storage::disk('public')->exists($strategic->$field)) {
                    Storage::disk('public')->delete($strategic->$field);
                }
                $data[$field] = $request->file($field)->store('uploads/strategic', 'public');
            }
        }

        $strategic->update($data);

        return response()->json($strategic);
    }

    public function destroy($id)
    {
        $strategic = StrategicPath::findOrFail($id);

        foreach ([
            'report_time_attachment',
            'gov_evaluation_attachment',
            'strategic_performance_attachment',
            'sustainability_attachment',
            'impact_attachment',
        ] as $field) {
            if ($strategic->$field && Storage::disk('public')->exists($strategic->$field)) {
                Storage::disk('public')->delete($strategic->$field);
            }
        }

        $strategic->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
