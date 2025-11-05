<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Organization;
use App\Models\Axis;
use App\Models\AxisResponse;

class OrganizationAndAxesSeeder extends Seeder
{
    public function run(): void
    {
        // 1️⃣ إنشاء مستخدم افتراضي (هيكون المالك للمنظمة)
        $user = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'phone' => '+2010032323',
                'email'=> 'haasklany@gmail.com',
                'password' => bcrypt('password'),
            ]
        );

        // 2️⃣ إنشاء منظمة
        $organization = Organization::updateOrCreate(
            ['id' => 1],
            [
                'name' => 'جمعية الأمل للتنمية',
                'user_id' => $user->id,
            ]
        );

        // 3️⃣ إنشاء المحاور الأربعة
        $axes = [
            [
                'id' => 1,
                'title' => 'القيادة والإدارة',
                'description' => 'يتعلق هذا المحور بإدارة العمليات الداخلية وكيفية تنفيذ الاستراتيجية داخل المنظمة، بالإضافة إلى دور القيادة في تحقيق الأهداف.',
                'weight' => 25.00,
                'organization_id' => $organization->id,
            ],
            [
                'id' => 2,
                'title' => 'القدرة المالية',
                'description' => 'يتعلق هذا المحور بالقدرة المالية للمنظمة واستدامتها على المدى الطويل.',
                'weight' => 25.00,
                'organization_id' => $organization->id,
            ],
            [
                'id' => 3,
                'title' => 'التأثير المجتمعي',
                'description' => 'يتم تقييم تأثير البرامج والأنشطة على المجتمع المستهدف ومدى الابتكار في الحلول المقدمة.',
                'weight' => 25.00,
                'organization_id' => $organization->id,
            ],
            [
                'id' => 4,
                'title' => 'الابتكار الاجتماعي',
                'description' => 'يتعلق هذا المحور بتقييم استراتيجية المنظمة وكيفية تحقيق الابتكار الاجتماعي داخل المنظمة لتحقيق الأهداف طويلة المدى.',
                'weight' => 25.00,
                'organization_id' => $organization->id,
            ],
        ];

        foreach ($axes as $axisData) {
            Axis::updateOrCreate(['id' => $axisData['id']], $axisData);
        }

        // 4️⃣ إنشاء Axis Responses (بيانات تجريبية)
        $responses = [
            [
                'axis_id' => 1,
                'q1' => true, 'q2' => true, 'q3' => false, 'q4' => true,
                'attachment_1' => 'axes_attachments/1/1/doc1.pdf',
                'admin_score' => 88.5,
            ],
            [
                'axis_id' => 2,
                'q1' => true, 'q2' => false, 'q3' => true, 'q4' => true,
                'attachment_2' => 'axes_attachments/1/2/report2.pdf',
                'admin_score' => 76.0,
            ],
            [
                'axis_id' => 3,
                'q1' => false, 'q2' => true, 'q3' => true, 'q4' => true,
                'attachment_3' => 'axes_attachments/1/3/image3.png',
                'admin_score' => 90.0,
            ],
            [
                'axis_id' => 4,
                'q1' => true, 'q2' => true, 'q3' => true, 'q4' => true,
                'admin_score' => 95.0,
            ],
        ];

        foreach ($responses as $resp) {
            AxisResponse::updateOrCreate(
                [
                    'organization_id' => $organization->id,
                    'axis_id' => $resp['axis_id'],
                ],
                $resp
            );
        }

        $this->command->info('✅ Organization, Axes, and AxisResponses seeded successfully!');
    }
}
