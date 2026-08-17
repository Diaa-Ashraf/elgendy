<x-filament-panels::page>
    <div class="space-y-6">
        {{-- كارت الإرشادات السريعة --}}
        <div class="p-6 bg-gradient-to-r from-amber-500/10 via-primary-500/10 to-blue-500/10 border border-primary-200 dark:border-primary-800 rounded-2xl">
            <div class="flex items-start gap-4">
                <div class="p-3 bg-primary-500 text-white rounded-xl shadow-lg shadow-primary-500/30">
                    <x-heroicon-o-information-circle class="w-6 h-6" />
                </div>
                <div class="space-y-1 text-sm">
                    <h3 class="font-extrabold text-base text-gray-900 dark:text-white">
                        تعليمات استيراد بيانات الطلاب من ملف Excel ⚡
                    </h3>
                    <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
                        • قم بتحميل <strong>قالب Excel الجاهز</strong> من الزر العلوي واملأ بيانات الطلاب بدون تغيير أسماء الأعمدة.<br>
                        • الأعمدة الإلزامية هي: <strong>اسم الطالب</strong>، <strong>رقم هاتف ولي الأمر</strong>، و<strong>المرحلة الدراسية</strong>.<br>
                        • تتم المعالجة في الخلفية تلقائياً دون تجميد النظام أو حدوث Timeout.<br>
                        • في حال كان الطالب مسجلاً مسبقاً بنفس الاسم ورقم ولي الأمر، سيتم تحديث مجموعاته وتخطي التكرار بأمان.
                    </p>
                </div>
            </div>
        </div>

        {{-- جدول متابعة عمليات الاستيراد مع البولينج الحي --}}
        {{ $this->table }}
    </div>
</x-filament-panels::page>
