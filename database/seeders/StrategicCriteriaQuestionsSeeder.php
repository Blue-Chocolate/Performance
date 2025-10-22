<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CriteriaQuestion;

class StrategicCriteriaQuestionsSeeder extends Seeder
{
    public function run(): void
    {
        // حذف الأسئلة القديمة الخاصة بالمسار الاستراتيجي (اختياري)
        CriteriaQuestion::where('path', 'strategic')->delete();

        $questions = [
            [
                'criteria_axis_id' => 1,
                'question_text' => 'ما هو موعد نشر التقرير السنوي للجمعية لهذا العام؟',
                'dropdown_options' => [
                    'قبل شهر 3',
                    'بعد شهر 3',
                    'بعد شهر 5',
                    'بعد شهر 6',
                    'بعد شهر 7',
                    'بعد شهر 8',
                    'بعد شهر 9',
                    'بعد شهر 10'
                ],
                'points_mapping' => [
                    'قبل شهر 3' => 10,
                    'بعد شهر 3' => 8,
                    'بعد شهر 5' => 6,
                    'بعد شهر 6' => 5,
                    'بعد شهر 7' => 4,
                    'بعد شهر 8' => 3,
                    'بعد شهر 9' => 2,
                    'بعد شهر 10' => 1
                ],
                'attachment_required' => true,
                'path' => 'strategic',
            ],
            [
                'criteria_axis_id' => 1,
                'question_text' => 'كم كانت درجة آخر تقييم للحوكمة الصادر للجمعية؟',
                'dropdown_options' => ['أقل من 65', 'من 65 - 75', 'من 76 - 85', 'من 86 - 100'],
                'points_mapping' => ['أقل من 65' => 0, 'من 65 - 75' => 8, 'من 76 - 85' => 12, 'من 86 - 100' => 15],
                'attachment_required' => true,
                'path' => 'strategic',
            ],
            [
                'criteria_axis_id' => 1,
                'question_text' => 'كم كانت درجة تقييم الأداء الاستراتيجي في نهاية العام؟',
                'dropdown_options' => ['أقل من 65%', 'من 65 - 75%', 'من 76 - 85%', 'من 86 - 100%'],
                'points_mapping' => ['أقل من 65%' => 20, 'من 65 - 75%' => 30, 'من 76 - 85%' => 40, 'من 86 - 100%' => 50],
                'attachment_required' => true,
                'path' => 'strategic',
            ],
            [
                'criteria_axis_id' => 1,
                'question_text' => 'تقرير الاستدامة: ما حالة التقرير في الجمعية حاليًا؟',
                'dropdown_options' => ['لن يعد', 'جاري الإعداد', 'تم الانتهاء ولم يُنشر', 'تم النشر'],
                'points_mapping' => ['لن يعد' => 0, 'جاري الإعداد' => 5, 'تم الانتهاء ولم يُنشر' => 10, 'تم النشر' => 15],
                'attachment_required' => true,
                'path' => 'strategic',
            ],
            [
                'criteria_axis_id' => 1,
                'question_text' => 'تقرير قياس الأثر: ما حالة التقرير في الجمعية حاليًا؟',
                'dropdown_options' => ['لن يعد', 'جاري الإعداد', 'تم الانتهاء ولم يُنشر', 'تم النشر'],
                'points_mapping' => ['لن يعد' => 0, 'جاري الإعداد' => 2.5, 'تم الانتهاء ولم يُنشر' => 7, 'تم النشر' => 10],
                'attachment_required' => true,
                'path' => 'strategic',
            ],
        ];

        foreach ($questions as $q) {
            CriteriaQuestion::create($q);
        }
    }
}
