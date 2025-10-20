<x-filament-panels::page class="space-y-6">
    <div class="text-2xl font-bold">{{ $organization->name }}</div>

    <div class="grid grid-cols-2 gap-6">
        <div>
            <span class="font-medium text-gray-700">Sector:</span>
            <p>{{ $organization->sector ?? 'N/A' }}</p>
        </div>
        <div>
            <span class="font-medium text-gray-700">Final Score:</span>
            <p>{{ $organization->final_score ?? 'Not graded yet' }}</p>
        </div>
    </div>

    <hr class="my-6">

    <h2 class="text-lg font-semibold">Axes Responses</h2>

    @php
        // الأسئلة الخاصة بكل محور
        $axisQuestions = [
            1 => [
                'هل لدى المنظمة خطة استراتيجية واضحة وطويلة المدى؟',
                'هل هناك إشراف وتوجيه فعّال لتحقيق النجاح في التنفيذ؟',
                'هل يتم تدريب وتطوير الموظفين بشكل دوري لتحسين أدائهم؟',
                'هل يتم اتخاذ قرارات استراتيجية بناءً على بيانات دقيقة وتحليل علمي؟'
            ],
            2 => [
                'هل لدى المنظمة خطة مالية مستدامة تضمن استمرار التمويل؟',
                'هل مصادر التمويل متنوعة (تبرعات، منح، رعاة، إلخ)؟',
                'هل يتم إعداد تقارير مالية بشكل دوري وتدقيقها بانتظام؟',
                'هل لدى المنظمة سياسات واضحة للشفافية والمساءلة المالية؟'
            ],
            3 => [
                'هل يتم إشراك المستفيدين في تقييم المشاريع والبرامج؟',
                'هل لدى المنظمة نظام لتقييم الأثر الاجتماعي؟',
                'هل تعتمد المنظمة على تمويل مستدام أو قادرة على تأمين مصادر دخل متجددة؟',
                'هل يتم تطوير الشراكات مع منظمات أخرى لتحقيق الأهداف المشتركة؟'
            ],
            4 => [
                'هل لدى المنظمة خطة استراتيجية مكتوبة مع توثيق للأهداف قصيرة وطويلة المدى؟',
                'هل لدى المنظمة تقارير تحليل أداء استراتيجية لمراجعة وتحديث الخطط؟',
                'هل تم تحديد مؤشرات أداء رئيسية (KPIs) لقياس النجاح؟',
                'هل تتبنى المنظمة الابتكار الاجتماعي في حلولها وبرامجها؟'
            ],
        ];

        // أسماء الملفات لكل محور
        $axisAttachments = [
            1 => [
                'خطة استراتيجية مكتوبة مع توثيق للأهداف قصيرة وطويلة المدى.',
                'تقارير تحليل الأداء الاستراتيجية الخاصة بمراجعة وتحديث الاستراتيجية.',
                'مؤشرات الأداء الرئيسية (KPIs) التي تحدد كيفية قياس النجاح.'
            ],
            2 => [
                'تقارير القيادة واستراتيجيات الإدارة.',
                'خطط الإدارة العملياتية وتقارير مراقبة الأداء التشغيلي.',
                'سجلات التدريب والتطوير المهني.'
            ],
            3 => [
                'التقارير المالية (لآخر عامين).',
                'شهادات تدقيق مالي أو تقارير تدقيق مستقل.',
                'التقرير السنوي للمنظمة.'
            ],
            4 => [
                'دراسات حالة تُوضح تأثير البرامج المجتمعية.',
                'استطلاعات رضا المستفيدين وتقارير التغذية الراجعة.',
                'شهادات من المستفيدين أو شركاء استراتيجيين تثبت تأثير المنظمة.'
            ],
        ];
    @endphp

    @forelse ($organization->axesResponses as $response)
        <div class="p-4 border rounded-xl mb-4 bg-gray-50 shadow-sm">
            <h3 class="font-semibold text-gray-800 text-lg mb-3">
                {{ $response->axis->title }}
            </h3>

            {{-- الأسئلة --}}
            <ul class="space-y-3 text-gray-700">
                @foreach (range(1,4) as $i)
                    @php
                        $question = $axisQuestions[$response->axis_id][$i - 1] ?? "Question {$i}";
                        $answer = $response["q{$i}"];
                    @endphp

                    <li class="flex flex-col bg-white p-3 rounded-lg shadow-sm border border-gray-100">
                        <span class="font-medium text-gray-800">{{ $question }}</span>
                        <span class="mt-1 text-sm {{ $answer ? 'text-green-600' : 'text-red-500' }}">
                            {{ $answer ? '✅ نعم' : '❌ لا' }}
                        </span>
                    </li>
                @endforeach
            </ul>

            {{-- المرفقات --}}
            <div class="mt-5 space-y-2">
                @foreach (['attachment_1', 'attachment_2', 'attachment_3'] as $index => $file)
                    @if ($response->$file)
                        @php
                            $attachmentName = $axisAttachments[$response->axis_id][$index] ?? "📎 مرفق رقم " . ($index + 1);
                        @endphp

                        <a href="{{ \Illuminate\Support\Facades\Storage::url($response->$file) }}" 
                           class="text-primary-600 block hover:underline" target="_blank">
                            📎 {{ $attachmentName }}
                        </a>
                    @endif
                @endforeach
            </div>

            {{-- تقييم المدير --}}
            <div class="mt-5">
                <form wire:submit.prevent="updateScore({{ $response->id }})" class="flex items-center space-x-2">
                    <label class="text-sm font-medium">تقييم المسؤول:</label>
                    <input type="number" 
                           wire:model.defer="responses.{{ $response->id }}.admin_score"
                           class="border rounded p-1 w-24"
                           min="0" max="100" />
                    <button type="submit" 
                            class="px-3 py-1 bg-primary-600 text-white rounded hover:bg-primary-700 transition">
                        حفظ
                    </button>
                </form>

                @if ($response->admin_score)
                    <p class="mt-2 text-sm text-green-600">
                        التقييم الحالي: {{ $response->admin_score }}/100
                    </p>
                @endif
            </div>
        </div>
    @empty
        <p class="text-gray-500">لا توجد استجابات بعد.</p>
    @endforelse
</x-filament-panels::page>
