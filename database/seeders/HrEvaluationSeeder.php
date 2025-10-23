<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CriteriaAxis;
use App\Models\CriteriaQuestion;

class HrEvaluationSeeder extends Seeder
{
    public function run(): void
    {
        // 🧹 Remove old HR questions
        CriteriaQuestion::where('path', 'hr')->delete();

        // 🧭 Define all axes and their questions
        $axes = [
            [
                'name' => 'والتخطيط ميالتنظي الهيكل',
                'description' => 'تقييم تنظيم الهيكل التنظيمي للجمعية',
                'weight' => 10,
                'questions' => [
                    [
                        'question_text' => 'هل ميتنظي هيكل الجمعية لدى معتمد ومحدث؟',
                        'options' => ['موجود ومُوثق بالكامل مطبق', 'جزئيًا مطبق', 'غير مطبق'],
                        'points_mapping' => [
                            'موجود ومُوثق بالكامل مطبق' => 100,
                            'جزئيًا مطبق' => 60,
                            'غير مطبق' => 0,
                        ],
                        'attachment_required' => true,
                        'weight' => 1.0,
                    ],
                ],
            ],
            [
                'name' => 'والتوظيف االستقطاب',
                'description' => 'تقييم أنظمة الاستقطاب والتوظيف',
                'weight' => 20,
                'questions' => [
                    [
                        'question_text' => 'هل يوجد نظام استقطاب وتوظيف واضح؟',
                        'options' => ['نعم', 'جزئيًا', 'لا'],
                        'points_mapping' => [
                            'نعم' => 100,
                            'جزئيًا' => 50,
                            'لا' => 0,
                        ],
                        'attachment_required' => false,
                        'weight' => 1.0,
                    ],
                ],
            ],
            [
                'name' => 'والتدريب تطوير الموارد',
                'description' => 'تقييم خطط التدريب وتطوير الموارد البشرية',
                'weight' => 15,
                'questions' => [
                    [
                        'question_text' => 'هل يوجد خطة تدريب سنوية؟',
                        'options' => ['موجودة ومنفذة', 'موجودة غير منفذة', 'غير موجودة'],
                        'points_mapping' => [
                            'موجودة ومنفذة' => 100,
                            'موجودة غير منفذة' => 40,
                            'غير موجودة' => 0,
                        ],
                        'attachment_required' => true,
                        'weight' => 1.0,
                    ],
                ],
            ],
            [
                'name' => 'والأداء تقييم',
                'description' => 'تقييم أنظمة تقييم الأداء',
                'weight' => 15,
                'questions' => [
                    [
                        'question_text' => 'هل يتم تقييم الأداء دوريًا؟',
                        'options' => ['نعم دوريًا', 'نعم غير دوري', 'لا'],
                        'points_mapping' => [
                            'نعم دوريًا' => 100,
                            'نعم غير دوري' => 60,
                            'لا' => 0,
                        ],
                        'attachment_required' => false,
                        'weight' => 1.0,
                    ],
                ],
            ],
            [
                'name' => 'والتعويضات الحوافز',
                'description' => 'تقييم أنظمة التعويضات والحوافز',
                'weight' => 15,
                'questions' => [
                    [
                        'question_text' => 'هل يوجد نظام حوافز واضح؟',
                        'options' => ['موجود ومنصف', 'جزئيًا موجود', 'غير موجود'],
                        'points_mapping' => [
                            'موجود ومنصف' => 100,
                            'جزئيًا موجود' => 50,
                            'غير موجود' => 0,
                        ],
                        'attachment_required' => true,
                        'weight' => 1.0,
                    ],
                ],
            ],
            [
                'name' => 'والثقافة تنمية التنظيمية',
                'description' => 'تقييم برامج تنمية الثقافة التنظيمية',
                'weight' => 15,
                'questions' => [
                    [
                        'question_text' => 'هل يوجد برنامج تنمية تنظيمية؟',
                        'options' => ['موجود ونشط', 'موجود غير نشط', 'غير موجود'],
                        'points_mapping' => [
                            'موجود ونشط' => 100,
                            'موجود غير نشط' => 40,
                            'غير موجود' => 0,
                        ],
                        'attachment_required' => false,
                        'weight' => 1.0,
                    ],
                ],
            ],
            [
                'name' => 'والعالقات التواصل',
                'description' => 'تقييم أنظمة التواصل والعلاقات الداخلية',
                'weight' => 10,
                'questions' => [
                    [
                        'question_text' => 'هل يوجد نظام تواصل داخلي فعال؟',
                        'options' => ['نعم', 'جزئيًا', 'لا'],
                        'points_mapping' => [
                            'نعم' => 100,
                            'جزئيًا' => 50,
                            'لا' => 0,
                        ],
                        'attachment_required' => true,
                        'weight' => 1.0,
                    ],
                ],
            ],
        ];

        // 💾 Loop through and insert
        foreach ($axes as $axisData) {
            $axis = CriteriaAxis::firstOrCreate(
                ['name' => $axisData['name'], 'path' => 'hr'],
                [
                    'description' => $axisData['description'],
                    'weight' => $axisData['weight'],
                ]
            );

            foreach ($axisData['questions'] as $q) {
                CriteriaQuestion::create([
                    'criteria_axis_id' => $axis->id,
                    'question_text' => $q['question_text'],
                    'options' => json_encode($q['options']),
                    'points_mapping' => json_encode($q['points_mapping']),
                    'attachment_required' => $q['attachment_required'],
                    'path' => 'hr',
                    'weight' => $q['weight'],
                ]);
            }
        }
    }
}
