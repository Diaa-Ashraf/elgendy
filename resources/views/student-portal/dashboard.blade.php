<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>بوابة الطالب — {{ $student->name }} | {{ $currentTenant->name }}</title>
    @if(!empty($currentTenant->favicon))
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $currentTenant->favicon) }}">
    @endif
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style> 
        body { font-family: 'Cairo', sans-serif; } 
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen pb-16 selection:bg-sky-500 selection:text-white">

    {{-- الشريط العلوي --}}
    <header class="bg-slate-900/90 border-b border-slate-800/80 backdrop-blur-md sticky top-0 z-50 px-4 py-3.5">
        <div class="max-w-5xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 bg-gradient-to-tr from-sky-600 to-indigo-600 text-white rounded-xl flex items-center justify-center font-bold text-xl shadow-lg shadow-sky-500/20 border border-sky-400/30">
                    🎓
                </div>
                <div>
                    <h1 class="font-black text-sm sm:text-base text-white leading-snug">{{ $student->name }}</h1>
                    <p class="text-[11px] font-bold text-sky-400">
                        {{ $student->educationalStage?->name ?? 'المرحلة الدراسية' }} — <span class="font-mono text-slate-400">كود: #{{ $student->id }}</span>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('tenant.home', ['tenant' => $currentTenant->slug]) }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-bold transition flex items-center gap-1">
                    <span>🌐</span> <span class="hidden sm:inline">الرئيسية</span>
                </a>
                <a href="{{ route('tenant.student.logout', ['tenant' => $currentTenant->slug]) }}" class="px-3 py-1.5 bg-rose-950/80 border border-rose-800/80 text-rose-300 hover:bg-rose-900 rounded-xl text-xs font-bold transition">
                    خروج 🚪
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-5xl mx-auto p-4 sm:p-6 space-y-6">

        {{-- الترحيب والبطاقة التعريفية --}}
        <div class="bg-gradient-to-r from-sky-950/80 via-slate-900 to-indigo-950/80 border border-sky-500/20 rounded-3xl p-6 relative overflow-hidden shadow-2xl">
            <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-sky-500/10 border border-sky-500/30 rounded-full text-xs font-bold text-sky-300 mb-2.5">
                        <span class="w-2 h-2 rounded-full bg-sky-400 animate-pulse"></span>
                        أهلاً بك يا بطل 🚀
                    </span>
                    <h2 class="text-xl sm:text-2xl font-black text-white">لوحة متابعة الطالب الأكاديمية</h2>
                    <p class="text-xs sm:text-sm text-slate-300 font-medium mt-1">
                        تابع مجموعاتك الدراسية، أداء اختباراتك الدورية، ونتائج امتحانات الأونلاين.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="px-4 py-2 bg-slate-900/90 border border-slate-700/80 rounded-2xl text-center">
                        <div class="text-xs text-slate-400 font-bold">المجموعات</div>
                        <div class="text-lg font-black text-sky-400">{{ $student->groups->count() }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- شبكة المحتوى الأساسية --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            {{-- 1. المجموعات والمواعيد --}}
            <div class="md:col-span-2 space-y-6">
                <div class="bg-slate-900/90 border border-slate-800/80 rounded-3xl p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-extrabold text-sm sm:text-base text-white flex items-center gap-2">
                            <span>📚</span> مجموعاتي الدراسية
                        </h3>
                        <span class="text-xs text-slate-400 font-semibold">{{ $student->groups->count() }} مجموعة</span>
                    </div>

                    @if($student->groups->isEmpty())
                        <div class="text-center py-8 text-slate-500 text-xs">
                            لم يتم إلحاقك بأي مجموعة دراسية حتى الآن. يرجى مراجعة إدارة السنتر.
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($student->groups as $group)
                                <div class="bg-slate-800/60 border border-slate-700/60 rounded-2xl p-4 flex items-center justify-between hover:border-sky-500/40 transition">
                                    <div>
                                        <div class="font-bold text-sm text-white">{{ $group->name }}</div>
                                        <div class="text-xs text-sky-400 font-semibold mt-0.5">
                                            المادة: {{ $group->subject?->name ?? 'مادة دراسية' }}
                                        </div>
                                    </div>
                                    <span class="px-3 py-1 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-bold rounded-xl">
                                        نشط ✅
                                    </span>
                                </div>
                            @endforeach
                        </div>
                </div>

                {{-- 2. الواجبات المنزلية --}}
                <div class="bg-slate-900/90 border border-slate-800/80 rounded-3xl p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-extrabold text-sm sm:text-base text-white flex items-center gap-2">
                            <span>📋</span> الواجبات المنزلية
                        </h3>
                        <span class="text-xs text-amber-400 font-semibold">{{ $homeworks->count() }} واجب</span>
                    </div>

                    @if($homeworks->isEmpty())
                        <div class="text-center py-8 text-slate-500 text-xs">
                            لا توجد واجبات منزلية مطلوبة منك حالياً 🎉
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($homeworks as $hw)
                                @php
                                    $sub = $hw->student_submission;
                                    $isSubmitted = $sub && $sub->isSubmitted();
                                    $isGraded = $sub && $sub->isGraded();
                                    $isOverdue = $hw->isOverdue();
                                @endphp
                                <div class="bg-slate-800/60 border border-slate-700/60 rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:border-amber-500/40 transition">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-sm text-white">{{ $hw->title }}</span>
                                            <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold {{ $hw->type === 'questions' ? 'bg-sky-500/10 text-sky-400' : ($hw->type === 'file_upload' ? 'bg-amber-500/10 text-amber-400' : 'bg-purple-500/10 text-purple-400') }}">
                                                {{ $hw->type === 'questions' ? 'أسئلة 📝' : ($hw->type === 'file_upload' ? 'ملف 📎' : 'مختلط') }}
                                            </span>
                                        </div>
                                        <div class="text-xs text-slate-400 mt-1 flex flex-wrap items-center gap-3">
                                            <span>📚 {{ $hw->subject?->name ?? 'مادة' }}</span>
                                            <span>⏰ الموعد: {{ $hw->due_date->format('Y-m-d h:i A') }}</span>
                                            <span>📊 الدرجة: {{ $hw->total_marks }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @if($isGraded)
                                            <a href="{{ route('tenant.student.homeworks.show', ['tenant' => $currentTenant->slug, 'id' => $hw->id]) }}" class="px-3.5 py-1.5 bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 hover:bg-emerald-500/20 text-xs font-bold rounded-xl transition inline-block">
                                                الدرجة: {{ $sub->score }} / {{ $hw->total_marks }} 🎖️
                                            </a>
                                        @elseif($isSubmitted)
                                            <a href="{{ route('tenant.student.homeworks.show', ['tenant' => $currentTenant->slug, 'id' => $hw->id]) }}" class="px-3.5 py-1.5 bg-amber-500/10 border border-amber-500/30 text-amber-300 hover:bg-amber-500/20 text-xs font-bold rounded-xl transition inline-block">
                                                تم التسليم (قيد المراجعة) ⏳
                                            </a>
                                        @elseif($isOverdue && ! $hw->allow_late_submission)
                                            <span class="px-3 py-1 bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs font-bold rounded-xl">
                                                انتهى الموعد 🔒
                                            </span>
                                        @else
                                            <a href="{{ route('tenant.student.homeworks.show', ['tenant' => $currentTenant->slug, 'id' => $hw->id]) }}" class="px-4 py-1.5 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-400 hover:to-orange-500 text-white text-xs font-bold rounded-xl transition shadow-sm inline-block">
                                                حل وتسليم الواجب 🚀
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- 3. الامتحانات الأونلاين المتاحة --}}
                <div class="bg-slate-900/90 border border-slate-800/80 rounded-3xl p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-extrabold text-sm sm:text-base text-white flex items-center gap-2">
                            <span>📝</span> الاختبارات الإلكترونية
                        </h3>
                        <span class="text-xs text-sky-400 font-semibold">امتحانات أونلاين</span>
                    </div>

                    @php
                        $onlineExams = \App\Models\Exam::where('stage_id', $student->stage_id)
                            ->where('is_online', true)
                            ->with(['subject', 'onlineAttempts' => function($q) use ($student) {
                                $q->where('student_id', $student->id);
                            }])
                            ->orderBy('id', 'desc')
                            ->take(5)
                            ->get();
                    @endphp

                    @if($onlineExams->isEmpty())
                        <div class="text-center py-8 text-slate-500 text-xs">
                            لا توجد اختبارات إلكترونية منشورة لمرحلتك الدراسية حالياً.
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($onlineExams as $exam)
                                @php
                                    $attempt = $exam->onlineAttempts->first();
                                    $isCompleted = $attempt && $attempt->status === 'completed';
                                @endphp
                                <div class="bg-slate-800/60 border border-slate-700/60 rounded-2xl p-4 flex items-center justify-between hover:border-indigo-500/40 transition">
                                    <div>
                                        <div class="font-bold text-sm text-white">{{ $exam->title ?? $exam->name ?? 'اختبار إلكتروني' }}</div>
                                        <div class="text-xs text-slate-400 mt-1 flex items-center gap-3">
                                            <span>⏱️ المدة: {{ $exam->duration_minutes ?? '30' }} دقيقة</span>
                                            <span>📊 الدرجة: {{ $exam->total_marks ?? '10' }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        @if($isCompleted)
                                            <a href="{{ route('tenant.student.exams.result', ['tenant' => $currentTenant->slug, 'id' => $exam->id]) }}" class="px-3.5 py-1.5 bg-indigo-500/10 border border-indigo-500/30 text-indigo-300 hover:bg-indigo-500/20 text-xs font-bold rounded-xl transition inline-block">
                                                النتيجة: {{ $attempt->total_score }} / {{ $attempt->max_possible_score }} 🎖️
                                            </a>
                                        @else
                                            <a href="{{ route('tenant.student.exams.show', ['tenant' => $currentTenant->slug, 'id' => $exam->id]) }}" class="px-4 py-1.5 bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-400 hover:to-indigo-500 text-white text-xs font-bold rounded-xl transition shadow-sm inline-block">
                                                بدء الامتحان 🚀
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- 2. العمود الجانبي: سجل الحضور السريع والبيانات --}}
            <div class="space-y-6">
                {{-- بطاقة الطالب --}}
                <div class="bg-slate-900/90 border border-slate-800/80 rounded-3xl p-5 shadow-sm">
                    <h3 class="font-extrabold text-sm text-white mb-3">بيانات الطالب</h3>
                    <div class="space-y-2.5 text-xs">
                        <div class="flex justify-between py-1.5 border-b border-slate-800/60">
                            <span class="text-slate-400 font-medium">كود الطالب</span>
                            <span class="text-white font-mono font-bold">#{{ $student->id }}</span>
                        </div>
                        <div class="flex justify-between py-1.5 border-b border-slate-800/60">
                            <span class="text-slate-400 font-medium">كود الـ QR</span>
                            <span class="text-sky-400 font-mono font-bold">{{ $student->qr_code }}</span>
                        </div>
                        <div class="flex justify-between py-1.5 border-b border-slate-800/60">
                            <span class="text-slate-400 font-medium">المرحلة</span>
                            <span class="text-white font-semibold">{{ $student->educationalStage?->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between py-1.5">
                            <span class="text-slate-400 font-medium">هاتف ولي الأمر</span>
                            <span class="text-slate-300 font-mono">{{ $student->parent_phone }}</span>
                        </div>
                    </div>
                </div>

                {{-- نصائح وملاحظات --}}
                <div class="bg-gradient-to-br from-indigo-950/40 to-slate-900 border border-indigo-500/20 rounded-3xl p-5 shadow-sm text-xs space-y-2">
                    <div class="font-bold text-indigo-300 flex items-center gap-1.5">
                        <span>💡</span> تعليمات هامة:
                    </div>
                    <p class="text-slate-400 leading-relaxed">
                        احرص على أداء الامتحانات في موعدها المخصص، وتأكد من استقرار اتصال الإنترنت قبل بدء أي اختبار أونلاين.
                    </p>
                </div>
            </div>

        </div>

    </main>

    {{-- التذييل --}}
    <footer class="text-center text-xs font-medium text-slate-500 py-4">
        {{ $currentTenant->name }} © {{ date('Y') }} — منظومة التعليم الذكية
    </footer>

</body>
</html>
