<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CriteriaAxis;
use App\Models\CriteriaQuestion;

class OperationalQuestionsSeeder extends Seeder
{
    public function run(): void
    {
        // 👇 إنشاء المحور الأساسي
        $axis = CriteriaAxis::create([
            'name' => 'الأداء التشغيلي',
            'description' => 'نموذج الأداء التشغيلي للجمعية',
            'path' => 'operational',
        ]);

        // 🟦 السؤال الأول
        CriteriaQuestion::create([
            'criteria_axis_id' => $axis->id,
            'question_text' => 'ما هو موعد نشر التقرير السنوي للجمعية للعام السابق؟',
            'options' => json_encode([
                'قبل شهر 3',
                'بعد شهر 3',
                'بعد شهر 5',
                'بعد شهر 6',
                'بعد شهر 7',
                'بعد شهر 8',
                'بعد شهر 9',
                'بعد شهر 10',
            ]),
            'points_mapping' => json_encode([
                'قبل شهر 3' => 10,
                'بعد شهر 3' => 8,
                'بعد شهر 5' => 6,
                'بعد شهر 6' => 5,
                'بعد شهر 7' => 4,
                'بعد شهر 8' => 3,
                'بعد شهر 9' => 2,
                'بعد شهر 10' => 1,
            ]),
            'attachment_required' => false,
            'path' => 'operational',
            'weight' => 1.0,
        ]);

        // 🟦 السؤال الثاني
        CriteriaQuestion::create([
            'criteria_axis_id' => $axis->id,
            'question_text' => 'كم كانت درجة آخر تقييم للحوكمة الصادر للجمعية؟',
            'options' => json_encode([
                'أقل من 65',
                'من 65 - 75',
                'من 76 - 85',
                'من 86 - 100',
            ]),
            'points_mapping' => json_encode([
                'أقل من 65' => 0,
                'من 65 - 75' => 5,
                'من 76 - 85' => 20,
                'من 86 - 100' => 30,
            ]),
            'attachment_required' => false,
            'path' => 'operational',
            'weight' => 1.2,
        ]);

        // 🟦 السؤال الثالث
        CriteriaQuestion::create([
            'criteria_axis_id' => $axis->id,
            'question_text' => 'درجة الأداء التشغيلي (Q3)',
            'options' => json_encode([
                'أقل من 65%',
                'من 65 - 75%',
                'من 76 - 89%',
                'من 90 - 100%',
            ]),
            'points_mapping' => json_encode([
                'أقل من 65%' => 30,
                'من 65 - 75%' => 40,
                'من 76 - 89%' => 50,
                'من 90 - 100%' => 60,
            ]),
            'attachment_required' => false,
            'path' => 'operational',
            'weight' => 1.5,
        ]);
    }
}
