<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>بوابة الطالب — {{ $student->name }}</title>
    @php
        $faviconUrl = app(\App\Services\SettingService::class)->url('site_favicon');
    @endphp
    @if($faviconUrl)
        <link rel="icon" type="image/png" href="{{ $faviconUrl }}">
        <link rel="shortcut icon" type="image/x-icon" href="{{ $faviconUrl }}">
        <link rel="apple-touch-icon" href="{{ $faviconUrl }}">
    @endif
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            teal: '#0D3B4C',
                            'teal-dark': '#082531',
                            'teal-light': '#14536B',
                            coral: '#FF5E36',
                            'coral-hover': '#F2481F',
                            mint: '#10B981',
                            amber: '#F59E0B',
                            bg: '#F8FAFC',
                            slate: '#0F172A',
                            muted: '#64748B'
                        }
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alexandria:wght@400;600;700;800;900&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style> 
        body { font-family: 'IBM Plex Sans Arabic', sans-serif; } 
        h1, h2, h3, h4, .font-heading { font-family: 'Alexandria', sans-serif; }
        .bento-card {
            background: #ffffff;
            border: 1px solid #E2E8F0;
            border-radius: 1.5rem;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .bento-card:hover {
            box-shadow: 0 12px 24px -8px rgba(30, 58, 138, 0.08);
            border-color: #CBD5E1;
        }
    </style>
</head>
<body class="bg-slate-50 text-brand-slate min-h-screen pb-16 selection:bg-blue-600 selection:text-white">

    {{-- الشريط العلوي لبوابة الطالب --}}
    <header class="bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 border-b border-indigo-950 sticky top-0 z-50 px-4 py-3.5 shadow-md shadow-blue-950/20 backdrop-blur-md">
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white/10 text-white rounded-2xl flex items-center justify-center font-black text-sm border border-white/20 shadow-inner">
                    🎓
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="font-heading font-bold text-base text-white leading-snug">{{ $student->name }}</h1>
                        <span class="px-2 py-0.5 bg-blue-500/20 border border-blue-400/30 text-blue-200 text-[10px] font-black rounded-full">بوابة الطالب</span>
                    </div>
                    <p class="text-[11px] font-semibold text-slate-300 flex items-center gap-1.5 mt-0.5">
                        <span>{{ $student->educationalStage?->name ?? 'غير محدد' }}</span>
                        <span class="text-white/40">•</span>
                        <span class="font-mono text-amber-300 bg-white/10 px-1.5 py-0.5 rounded-md text-[10px] font-bold">كود: {{ $student->id }}</span>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('student.certificate.print', ['record' => $student->id]) }}" target="_blank" class="hidden sm:inline-flex px-3.5 py-2 bg-amber-400 hover:bg-amber-500 text-amber-950 rounded-xl text-xs font-bold shadow-md transition items-center gap-1.5">
                    <span>📜 شهادتي</span>
                </a>
                <a href="{{ route('home') }}" class="px-3 py-2 bg-white/10 hover:bg-white/20 text-white border border-white/15 rounded-xl text-xs font-bold transition">
                    الموقع
                </a>
                <a href="{{ route('student.logout') }}" class="px-3 py-2 bg-rose-500/20 border border-rose-400/30 text-rose-100 hover:bg-rose-500/30 rounded-xl text-xs font-bold transition">
                    خروج
                </a>
            </div>
        </div>
    </header>

    <div class="max-w-4xl mx-auto p-4 sm:p-6 space-y-6">

        {{-- رسالة ترحيبية --}}
        <div class="bg-gradient-to-r from-blue-700 via-indigo-600 to-purple-700 text-white p-6 rounded-3xl shadow-xl relative overflow-hidden flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="space-y-1 text-center sm:text-right">
                <span class="px-3 py-1 bg-white/20 rounded-full text-xs font-bold inline-block mb-1"> مرحباً بك في مساحتك التعليمية</span>
                <h2 class="font-heading font-black text-2xl">أهلاً يا بطل، {{ $student->name }} </h2>
                <p class="text-xs text-blue-100 font-medium">تابع اختباراتك أولاً بأول، التزم بمواعيد حصصك، وسلّم واجباتك لتتصدر لوحة الشرف!</p>
            </div>
            <div class="shrink-0 flex items-center gap-2">
                <a href="{{ route('student.certificate.print', ['record' => $student->id]) }}" target="_blank" class="px-4 py-2.5 bg-white text-indigo-900 hover:bg-slate-100 rounded-2xl text-xs font-black shadow-lg transition flex items-center gap-1.5">
                    <span>🎖️ شهادة التقدير</span>
                </a>
            </div>
        </div>

        {{-- ─── 1. كروت مؤشرات الطالب الأكاديمية ─── --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5">
            {{-- الحضور --}}
            <div class="bento-card p-4 text-center shadow-sm">
                <span class="text-2xl block mb-1">✅</span>
                <span class="text-2xl font-heading font-black text-emerald-600">{{ $stats['total_attended'] }}</span>
                <span class="text-[11px] font-bold text-slate-500 block mt-0.5">حصص حضرتها</span>
            </div>

            {{-- الغياب --}}
            <div class="bento-card p-4 text-center shadow-sm">
                <span class="text-2xl block mb-1">⏰</span>
                <span class="text-2xl font-heading font-black text-rose-600">{{ $stats['total_absent'] }}</span>
                <span class="text-[11px] font-bold text-slate-500 block mt-0.5">حصص غائب</span>
            </div>

            {{-- الامتحانات --}}
            <div class="bento-card p-4 text-center shadow-sm">
                <span class="text-2xl block mb-1">📝</span>
                <span class="text-2xl font-heading font-black text-blue-600">{{ $stats['total_exams'] }}</span>
                <span class="text-[11px] font-bold text-slate-500 block mt-0.5">امتحانات مؤداة</span>
            </div>

            {{-- متوسط الدرجات --}}
            <div class="bento-card p-4 text-center shadow-sm">
                <span class="text-2xl block mb-1"></span>
                <span class="text-2xl font-heading font-black text-amber-500">{{ $stats['average_score'] }}%</span>
                <span class="text-[11px] font-bold text-slate-500 block mt-0.5">متوسط التحصيل</span>
            </div>
        </div>

        {{-- ─── 2. الاختبارات الإلكترونية أونلاين ─── --}}
        <div class="bento-card p-5 sm:p-7 shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm">📝</span>
                    <h2 class="font-heading font-black text-base text-brand-slate">
                        الاختبارات الإلكترونية والكويزات
                    </h2>
                </div>
                <span class="text-xs font-bold text-blue-600 bg-blue-50 px-3 py-1 rounded-xl border border-blue-200">{{ count($onlineExams) }} اختبار متاح</span>
            </div>

            <div class="space-y-3">
                @forelse($onlineExams as $onlineExam)
                    @php
                        $userAttempt = $onlineExam->onlineAttempts->first();
                        $isCompleted = $userAttempt && $userAttempt->status === 'completed';
                    @endphp

                    <div class="p-4 bg-slate-50/70 border border-slate-200 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:border-slate-300 transition">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-brand-slate text-sm">{{ $onlineExam->title }}</span>
                                <span class="text-[10px] font-bold text-blue-700 bg-white px-2 py-0.5 rounded border border-slate-200">{{ $onlineExam->subject?->name }}</span>
                            </div>
                            <div class="flex items-center gap-3 text-[11px] text-slate-500 font-medium">
                                <span>المدة: {{ $onlineExam->duration_minutes ? $onlineExam->duration_minutes . ' دقيقة' : 'مفتوح' }}</span>
                                <span>•</span>
                                <span>النجاح من: {{ $onlineExam->pass_percentage }}%</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 self-end sm:self-center">
                            @if($isCompleted)
                                <div class="text-left ml-2">
                                    <span class="text-xs font-black {{ $userAttempt->passed ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $userAttempt->total_score }} / {{ $userAttempt->max_possible_score }} ({{ $userAttempt->percentage }}%)
                                    </span>
                                </div>
                                <a href="{{ route('parent.exams.result', ['id' => $onlineExam->id]) }}" class="px-4 py-2 bg-white border border-slate-300 text-brand-slate hover:bg-slate-50 rounded-xl text-xs font-bold transition shadow-sm">
                                    عرض النتيجة والتفسير ➔
                                </a>
                            @else
                                <a href="{{ route('parent.exams.show', ['id' => $onlineExam->id]) }}" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-500/20 transition">
                                    بدء أداء الاختبار ✍️
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 text-center py-6">لا توجد اختبارات إلكترونية منشورة لمرحلتك الدراسية حالياً</p>
                @endforelse
            </div>
        </div>

        {{-- ─── 3. الواجبات والتكليفات المنزلية ─── --}}
        <div class="bento-card p-5 sm:p-7 shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center font-bold text-sm">📚</span>
                    <h2 class="font-heading font-black text-base text-brand-slate">
                        الواجبات والتكليفات المدرسية
                    </h2>
                </div>
                <span class="text-xs font-bold text-slate-500">{{ count($homeworks ?? []) }} واجب</span>
            </div>

            <div class="space-y-3">
                @forelse($homeworks ?? [] as $hw)
                    @php
                        $sub = $hw->submissions->first();
                        $hasSubmitted = $sub && $sub->status !== 'draft';
                        $isGraded = $sub && $sub->score !== null;
                    @endphp

                    <div class="p-4 bg-slate-50/70 border border-slate-200 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:border-slate-300 transition">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-brand-slate text-sm">{{ $hw->title }}</span>
                                <span class="text-[10px] font-bold text-amber-800 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">{{ $hw->subject?->name }}</span>
                            </div>
                            <div class="flex items-center gap-3 text-[11px] text-slate-500 font-medium">
                                <span>آخر موعد: {{ $hw->due_date ? \Carbon\Carbon::parse($hw->due_date)->format('Y-m-d') : 'مفتوح' }}</span>
                                <span>•</span>
                                <span>الدرجة الكلية: {{ $hw->max_score ?? 10 }}</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 self-end sm:self-center">
                            @if($isGraded)
                                <span class="px-3 py-1.5 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-black">
                                    تم التقييم: {{ $sub->score }}/{{ $hw->max_score }}
                                </span>
                            @elseif($hasSubmitted)
                                <span class="px-3 py-1.5 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl text-xs font-bold">
                                    تم التسليم (قيد التصحيح)
                                </span>
                            @else
                                <a href="{{ route('parent.homeworks.show', ['id' => $hw->id]) }}" class="px-5 py-2.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-slate-950 font-bold rounded-xl text-xs shadow-md transition">
                                    تسليم الواجب الآن 
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 text-center py-6">لا توجد واجبات مطلوبة منك حالياً</p>
                @endforelse
            </div>
        </div>

        {{-- ─── 4. جدول المجموعات والحصص الأسبوعية ─── --}}
        <div class="bento-card p-5 sm:p-7 shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center font-bold text-sm">🗓️</span>
                    <h2 class="font-heading font-black text-base text-brand-slate">
                        جدول حصصي الأسبوعي
                    </h2>
                </div>
            </div>

            @php
                $dayNames = [
                    'sat' => 'السبت',
                    'sun' => 'الأحد',
                    'mon' => 'الإثنين',
                    'tue' => 'الثلاثاء',
                    'wed' => 'الأربعاء',
                    'thu' => 'الخميس',
                    'fri' => 'الجمعة',
                ];
            @endphp

            @if($groups->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($groups as $grp)
                        <div class="bg-slate-50/80 border border-slate-200 rounded-2xl p-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <h3 class="font-heading font-bold text-sm text-brand-slate">{{ $grp->name }}</h3>
                                <span class="text-[11px] font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-200">{{ $grp->subject?->name }}</span>
                            </div>

                            <div class="space-y-1.5 pt-2 border-t border-slate-200/60">
                                @forelse($grp->schedules as $sch)
                                    <div class="flex items-center justify-between bg-white border border-slate-200 px-3 py-2 rounded-xl text-xs">
                                        <span class="font-bold text-slate-700">{{ $dayNames[$sch->day_of_week] ?? $sch->day_of_week }}</span>
                                        <span class="font-mono font-bold text-indigo-700">{{ \Carbon\Carbon::parse($sch->time)->format('g:i A') }} {{ $sch->room ? "({$sch->room})" : '' }}</span>
                                    </div>
                                @empty
                                    <p class="text-xs text-slate-400 italic">يتم تحديد المواعيد قريباً</p>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-xs text-slate-400 text-center py-6">لم يتم تعيينك في أي مجموعة بعد</p>
            @endif
        </div>

        {{-- ─── 5. سجل الحضور والغياب الأخير ─── --}}
        <div class="bento-card p-5 sm:p-7 shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-sm">📋</span>
                    <h2 class="font-heading font-black text-base text-brand-slate">
                        سجل حضوري الأخير
                    </h2>
                </div>
                <span class="text-xs font-bold text-slate-500">آخر 15 حصة</span>
            </div>

            <div class="space-y-2.5">
                @forelse($attendances as $att)
                    <div class="p-3.5 bg-slate-50/70 border border-slate-200 rounded-2xl text-xs flex items-center justify-between">
                        <div>
                            <span class="font-bold block text-brand-slate text-sm mb-0.5">{{ $att->groupSession?->group?->name ?? 'حصة دراسية' }}</span>
                            <span class="text-[11px] font-semibold text-slate-400">تاريخ: {{ \Carbon\Carbon::parse($att->groupSession?->date)->format('Y-m-d') }}</span>
                        </div>
                        <div>
                            @if($att->status === 'present')
                                <span class="px-3 py-1 bg-emerald-100/70 border border-emerald-200 text-emerald-800 rounded-xl font-bold text-xs">حاضر ✅</span>
                            @elseif($att->status === 'late')
                                <span class="px-3 py-1 bg-amber-100/70 border border-amber-200 text-amber-800 rounded-xl font-bold text-xs">متأخر ⏰</span>
                            @else
                                <span class="px-3 py-1 bg-rose-100/70 border border-rose-200 text-rose-800 rounded-xl font-bold text-xs">غائب ❌</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 text-center py-6">لا توجد سجلات حضور مسجلة حتى الآن</p>
                @endforelse
            </div>
        </div>

    </div>

    {{-- التذييل --}}
    <footer class="max-w-4xl mx-auto px-4 text-center text-xs font-semibold text-slate-400 pt-8 pb-4">
        منظومة الأستاذ محمد الغندي التعليمية الذكية © {{ date('Y') }}
    </footer>

</body>
</html>
