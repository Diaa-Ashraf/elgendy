<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>بوابة ولي الأمر — {{ $student->name }}</title>
    @php
        $faviconUrl = app(\App\Services\SettingService::class)->url('site_favicon');
    @endphp
    @if($faviconUrl)
        <link rel="icon" type="image/png" href="{{ $faviconUrl }}">
        <link rel="shortcut icon" type="image/x-icon" href="{{ $faviconUrl }}">
        <link rel="apple-touch-icon" href="{{ $faviconUrl }}">
    @endif
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style> body { font-family: 'Cairo', sans-serif; } </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen pb-16 selection:bg-indigo-500 selection:text-white" x-data="{ showPayModal: false, activeMethod: 'vodafone_cash' }">

    {{-- الشريط العلوي --}}
    <header class="bg-slate-900/90 border-b border-slate-800/80 backdrop-blur-md sticky top-0 z-50 px-4 py-4">
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-600/20 text-indigo-400 rounded-xl flex items-center justify-center font-bold text-lg border border-indigo-500/30">
                    👨‍🎓
                </div>
                <div>
                    <h1 class="font-extrabold text-base text-white leading-snug">{{ $student->name }}</h1>
                    <p class="text-[11px] font-bold text-indigo-400">
                        {{ $student->educationalStage?->name ?? 'غير محدد' }} — <span class="font-mono text-slate-400">كود: #{{ $student->id }}</span>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                @if($paymentSettings['enabled'])
                    <button @click="showPayModal = true" class="px-3.5 py-1.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white rounded-xl text-xs font-black shadow-lg shadow-emerald-950 flex items-center gap-1.5 transition">
                        <span>💳</span> سداد أونلاين
                    </button>
                @endif
                <a href="{{ route('home') }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-bold transition">
                    الموقع 🌐
                </a>
                <a href="{{ route('parent.logout') }}" class="px-3 py-1.5 bg-rose-950/80 border border-rose-800/80 text-rose-300 hover:bg-rose-900 rounded-xl text-xs font-bold transition">
                    خروج 🚪
                </a>
            </div>
        </div>
    </header>

    <div class="max-w-4xl mx-auto p-4 sm:p-6 space-y-6">

        {{-- رسائل التنبيه والنجاح --}}
        @if(session('payment_success'))
            <div class="bg-gradient-to-r from-emerald-950 to-teal-950 border-2 border-emerald-500/50 p-4 sm:p-5 rounded-2xl text-emerald-200 flex items-start gap-3 shadow-2xl animate-fade-in">
                <span class="text-2xl">🎉</span>
                <div>
                    <h3 class="font-black text-sm text-white mb-1">تم استلام طلب السداد بنجاح!</h3>
                    <p class="text-xs font-semibold leading-relaxed">{{ session('payment_success') }}</p>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-rose-950/90 border border-rose-800 p-4 rounded-2xl text-rose-200 text-xs space-y-1">
                <div class="font-bold text-sm mb-1">يرجى تصحيح الأخطاء التالية:</div>
                @foreach($errors->all() as $err)
                    <div>• {{ $err }}</div>
                @endforeach
            </div>
        @endif

        {{-- ─── 1. كروت الإحصائيات الفخمة ─── --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl relative overflow-hidden shadow-lg flex flex-col justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 block mb-1">الرصيد المالي الحالي</span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-black {{ $ledger['balance'] >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                            {{ number_format(abs($ledger['balance']), 0) }} ج.م
                        </span>
                        <span class="text-xs font-bold text-slate-400">{{ $ledger['balance'] >= 0 ? '(فائض/مقدم)' : '(مستحق الدفع)' }}</span>
                    </div>
                </div>
                <div class="mt-3 flex items-center justify-between">
                    <span class="inline-block text-[10px] font-extrabold px-2.5 py-0.5 rounded-full {{ $ledger['balance'] >= 0 ? 'bg-emerald-950 text-emerald-300 border border-emerald-800' : 'bg-rose-950 text-rose-300 border border-rose-800' }}">
                        {{ $ledger['balance'] >= 0 ? 'مسدد بالكامل ✅' : 'متبقي مديونية ⚠️' }}
                    </span>
                    @if($paymentSettings['enabled'] && $ledger['balance'] < 0)
                        <button @click="showPayModal = true" class="text-xs font-black text-emerald-400 hover:text-emerald-300 underline">
                            سداد الآن ↗
                        </button>
                    @endif
                </div>
            </div>

            <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl shadow-lg">
                <span class="text-xs font-bold text-slate-400 block mb-1">المجموعات المسجلة</span>
                <span class="text-2xl font-black text-indigo-400 block">
                    {{ $student->groups->count() }} مجموعات
                </span>
                <span class="text-[11px] font-semibold text-slate-500 mt-1 block">دراسة منتظمة</span>
            </div>

            <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl shadow-lg">
                <span class="text-xs font-bold text-slate-400 block mb-1">نسبة الحضور</span>
                @php
                    $totalSessions = $attendances->count();
                    $presentCount = $attendances->where('status', 'present')->count();
                    $rate = $totalSessions > 0 ? round(($presentCount / $totalSessions) * 100) : 100;
                @endphp
                <span class="text-2xl font-black text-purple-400 block">
                    {{ $rate }}%
                </span>
                <span class="text-[11px] font-semibold text-slate-500 mt-1 block">مستوى الالتزام بالحضور</span>
            </div>
        </div>

        {{-- ─── 2. كشف الحساب والمدفوعات التفصيلي ─── --}}
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 sm:p-6 shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3 mb-4">
                <h2 class="font-black text-base text-white flex items-center gap-2">
                    <span>💳</span> كشف الحساب المالي وسجل السداد
                </h2>
                @if($paymentSettings['enabled'])
                    <button @click="showPayModal = true" class="px-3 py-1.5 bg-indigo-600/30 hover:bg-indigo-600/50 text-indigo-300 border border-indigo-500/40 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                        <span>📲</span> تحويل فودافون كاش / انستاباي
                    </button>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div class="p-4 bg-slate-950 rounded-2xl border border-slate-800">
                    <span class="text-xs font-bold text-slate-400 block mb-1">إجمالي المستحقات (الاشتراكات والملازم)</span>
                    <span class="text-lg font-black text-amber-400">{{ number_format($ledger['total_dues'] ?? 0) }} ج.م</span>
                </div>
                <div class="p-4 bg-slate-950 rounded-2xl border border-slate-800">
                    <span class="text-xs font-bold text-slate-400 block mb-1">إجمالي المبالغ المسددة المعتمدة</span>
                    <span class="text-lg font-black text-emerald-400">{{ number_format($ledger['total_paid'] ?? 0) }} ج.م</span>
                </div>
            </div>

            {{-- قائمة طلبات السداد الإلكتروني المرفوعة وحالتها --}}
            @if($onlinePaymentRequests->isNotEmpty())
                <div class="mt-4 pt-4 border-t border-slate-800/80">
                    <span class="text-xs font-black text-slate-300 block mb-2.5">📱 آخر طلبات السداد الإلكتروني المرفوعة:</span>
                    <div class="space-y-2">
                        @foreach($onlinePaymentRequests as $req)
                            <div class="p-3 bg-slate-950 border border-slate-800 rounded-xl flex items-center justify-between text-xs">
                                <div class="space-y-0.5">
                                    <div class="flex items-center gap-2">
                                        <span class="font-extrabold text-white">{{ number_format($req->amount) }} ج.م</span>
                                        <span class="text-[10px] text-slate-400 font-bold">({{ $req->payment_method === 'instapay' ? 'انستاباي ⚡' : 'فودافون كاش 📱' }})</span>
                                        @if($req->group)
                                            <span class="text-[10px] text-indigo-400 bg-indigo-950 px-2 py-0.5 rounded border border-indigo-900">{{ $req->group->name }}</span>
                                        @endif
                                    </div>
                                    <span class="text-[10px] text-slate-500">{{ $req->created_at->format('Y-m-d h:i A') }}</span>
                                </div>
                                <div class="text-left">
                                    @if($req->status === 'pending')
                                        <span class="px-2.5 py-1 bg-amber-950 border border-amber-800 text-amber-300 rounded-lg font-bold text-[11px]">قيد المراجعة والاعتماد ⏳</span>
                                    @elseif($req->status === 'approved')
                                        <span class="px-2.5 py-1 bg-emerald-950 border border-emerald-800 text-emerald-300 rounded-lg font-bold text-[11px]">معتمد ومسجل بالحساب ✅</span>
                                    @else
                                        <div>
                                            <span class="px-2.5 py-1 bg-rose-950 border border-rose-800 text-rose-300 rounded-lg font-bold text-[11px]">مرفوض ❌</span>
                                            @if($req->rejection_reason)
                                                <p class="text-[10px] text-rose-400 mt-1 max-w-[200px] truncate" title="{{ $req->rejection_reason }}">{{ $req->rejection_reason }}</p>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- ─── 3. جدول مواعيد الحصص الأسبوعي للطالب ─── --}}
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 sm:p-6 shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3 mb-4">
                <h2 class="font-black text-base text-white flex items-center gap-2">
                    <span>🗓️</span> جدول الحصص والمجموعات الدراسية
                </h2>
                <span class="text-xs font-semibold text-indigo-400 bg-indigo-950/80 px-2.5 py-1 rounded-xl border border-indigo-900">
                    {{ $student->groups->count() }} مجموعات مقيد بها
                </span>
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

            @if($student->groups->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($student->groups as $grp)
                        <div class="bg-slate-950 border border-slate-800/90 rounded-2xl p-4 flex flex-col justify-between hover:border-slate-700 transition space-y-3">
                            <div>
                                <div class="flex items-start justify-between gap-2 mb-2">
                                    <div>
                                        <h3 class="font-extrabold text-white text-sm leading-snug">{{ $grp->name }}</h3>
                                        @if($grp->subject)
                                            <span class="text-[11px] font-bold text-indigo-400 block mt-0.5">
                                                المادة: {{ $grp->subject->name }}
                                            </span>
                                        @endif
                                    </div>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-lg bg-indigo-950 text-indigo-300 border border-indigo-900 shrink-0">
                                        {{ $grp->monthly_fee ? number_format($grp->monthly_fee) . ' ج.م / شهر' : 'اشتراك شهري' }}
                                    </span>
                                </div>
                            </div>

                            {{-- مواعيد الحصص الأسبوعية لهذه المجموعة --}}
                            <div class="pt-2.5 border-t border-slate-800/80">
                                <span class="text-[11px] font-bold text-slate-400 block mb-2">مواعيد الحصص الأسبوعية:</span>
                                @if($grp->schedules && $grp->schedules->isNotEmpty())
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        @foreach($grp->schedules as $sch)
                                            <div class="flex items-center justify-between bg-slate-900/90 border border-slate-800 px-3 py-2 rounded-xl text-xs">
                                                <div class="flex items-center gap-1.5 font-extrabold text-white">
                                                    <span class="text-indigo-400">📅</span>
                                                    <span>{{ $dayNames[$sch->day_of_week] ?? $sch->day_of_week }}</span>
                                                </div>
                                                <div class="text-left font-mono font-bold text-emerald-400">
                                                    ⏰ {{ \Carbon\Carbon::parse($sch->time)->format('g:i A') }}
                                                    @if($sch->room)
                                                        <span class="text-[10px] text-slate-400 font-normal block">({{ $sch->room }})</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-xs text-slate-500 italic bg-slate-900/50 p-2.5 rounded-xl text-center">
                                        يتم تحديد وتأكيد مواعيد هذه المجموعة قريباً من الإدارة
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-6 bg-slate-950 border border-slate-800/80 rounded-2xl text-center text-slate-400 text-xs font-bold">
                    لم يتم تعيين الطالب في أي مجموعة دراسية بعد. يرجى مراجعة إدارة السنتر لتحديد المجموعة والجدول.
                </div>
            @endif
        </div>

        {{-- ─── 4. سجل الحضور والغياب ─── --}}
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 sm:p-6 shadow-xl">
            <h2 class="font-black text-base text-white mb-4 flex items-center justify-between border-b border-slate-800 pb-3">
                <span class="flex items-center gap-2">
                    <span>📅</span> سجل الحضور والغياب
                </span>
                <span class="text-xs font-semibold text-slate-400">آخر 15 حصة</span>
            </h2>

            <div class="space-y-2.5">
                @forelse($attendances as $att)
                    <div class="flex items-center justify-between p-3.5 bg-slate-950 border border-slate-800/80 rounded-2xl text-xs hover:border-slate-700 transition">
                        <div>
                            <span class="font-bold block text-white text-sm mb-0.5">{{ $att->groupSession?->group?->name ?? 'حصة عامة' }}</span>
                            <span class="text-[11px] font-semibold text-slate-400">تاريخ الحصة: {{ \Carbon\Carbon::parse($att->groupSession?->date)->format('Y-m-d') }}</span>
                        </div>
                        <div>
                            @if($att->status === 'present')
                                <span class="px-3 py-1 bg-emerald-950 border border-emerald-800 text-emerald-300 rounded-xl font-black text-xs">حضر ✅</span>
                            @elseif($att->status === 'late')
                                <span class="px-3 py-1 bg-amber-950 border border-amber-800 text-amber-300 rounded-xl font-black text-xs">متأخر ⏰</span>
                            @else
                                <span class="px-3 py-1 bg-rose-950 border border-rose-800 text-rose-300 rounded-xl font-black text-xs">غائب ❌</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-500 text-center py-6">لا توجد سجلات حضور مسجلة حالياً</p>
                @endforelse
            </div>
        </div>

        {{-- ─── 4. الاختبارات الإلكترونية أونلاين ─── --}}
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 sm:p-6 shadow-xl">
            <h2 class="font-black text-base text-white mb-4 flex items-center justify-between border-b border-slate-800 pb-3">
                <span class="flex items-center gap-2">
                    <span class="text-indigo-400">📝</span> الاختبارات الإلكترونية والكويزات (Online Quizzes)
                </span>
                <span class="text-xs font-semibold text-slate-400">{{ count($onlineExams) }} اختبار متاح</span>
            </h2>

            <div class="space-y-3">
                @forelse($onlineExams as $onlineExam)
                    @php
                        $userAttempt = $onlineExam->onlineAttempts->first();
                        $isCompleted = $userAttempt && $userAttempt->status === 'completed';
                    @endphp

                    <div class="p-4 bg-slate-950 border border-slate-800/90 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:border-indigo-500/50 transition">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="font-extrabold text-white text-sm">{{ $onlineExam->title }}</span>
                                <span class="text-[10px] font-bold text-indigo-400 bg-indigo-950 px-2 py-0.5 rounded border border-indigo-900">{{ $onlineExam->subject?->name }}</span>
                            </div>
                            <div class="flex items-center gap-3 text-[11px] text-slate-400 font-semibold">
                                <span>⏱️ {{ $onlineExam->duration_minutes ? $onlineExam->duration_minutes . ' دقيقة' : 'مفتوح' }}</span>
                                <span>•</span>
                                <span>🎯 النجاح من: {{ $onlineExam->pass_percentage }}%</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 self-end sm:self-center">
                            @if($isCompleted)
                                <div class="text-left ml-2">
                                    <span class="text-xs font-black {{ $userAttempt->passed ? 'text-emerald-400' : 'text-rose-400' }}">
                                        {{ $userAttempt->total_score }} / {{ $userAttempt->max_possible_score }} ({{ $userAttempt->percentage }}%)
                                    </span>
                                </div>
                                <a href="{{ route('parent.exams.result', ['id' => $onlineExam->id]) }}" class="px-3.5 py-1.5 bg-indigo-950 border border-indigo-700 text-indigo-300 hover:bg-indigo-900 rounded-xl text-xs font-black transition">
                                    عرض النتيجة والتفسير ↗
                                </a>
                            @else
                                <a href="{{ route('parent.exams.show', ['id' => $onlineExam->id]) }}" class="px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white rounded-xl text-xs font-black shadow-md shadow-emerald-950 transition flex items-center gap-1">
                                    <span>بدء الاختبار</span> 🚀
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-500 text-center py-6">لا توجد اختبارات إلكترونية منشورة لمرحلتك الدراسية حالياً</p>
                @endforelse
            </div>
        </div>

        {{-- ─── 4.5 الواجبات والتكليفات المنزلية ─── --}}
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 sm:p-6 shadow-xl">
            <h2 class="font-black text-base text-white mb-4 flex items-center justify-between border-b border-slate-800 pb-3">
                <span class="flex items-center gap-2">
                    <span class="text-amber-400">📚</span> الواجبات والتكليفات المنزلية (Homeworks)
                </span>
                <span class="text-xs font-semibold text-slate-400">{{ count($homeworks ?? []) }} واجب متاح</span>
            </h2>

            <div class="space-y-3">
                @forelse($homeworks ?? [] as $hw)
                    @php
                        $sub = $hw->student_submission;
                        $hasSubmitted = $sub && $sub->isSubmitted();
                        $isGraded = $sub && $sub->isGraded();
                    @endphp

                    <div class="p-4 bg-slate-950 border border-slate-800/90 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:border-amber-500/50 transition">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="font-extrabold text-white text-sm">{{ $hw->title }}</span>
                                <span class="text-[10px] font-bold text-amber-400 bg-amber-950 px-2 py-0.5 rounded border border-amber-900">{{ $hw->subject?->name }}</span>
                                @if($hw->group)
                                    <span class="text-[10px] font-bold text-slate-400 bg-slate-900 px-2 py-0.5 rounded border border-slate-800">{{ $hw->group->name }}</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-3 text-[11px] text-slate-400 font-semibold">
                                <span>📅 آخر موعد: {{ $hw->due_date->format('Y-m-d h:i A') }}</span>
                                <span>•</span>
                                <span>🎯 الدرجة: {{ $hw->total_marks }} درجة</span>
                                <span>•</span>
                                <span class="{{ $hw->isOverdue() ? 'text-rose-400 font-bold' : 'text-emerald-400' }}">
                                    {{ $hw->isOverdue() ? 'انتهى الموعد' : 'متاح للتسليم' }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 self-end sm:self-center">
                            @if($isGraded)
                                <div class="text-left ml-2">
                                    <span class="text-xs font-black text-emerald-400">
                                        الدرجة: {{ $sub->score }} / {{ $hw->total_marks }} ({{ $sub->score_percentage }}%)
                                    </span>
                                </div>
                                <a href="{{ route('parent.homeworks.show', ['id' => $hw->id]) }}" class="px-3.5 py-1.5 bg-indigo-950 border border-indigo-700 text-indigo-300 hover:bg-indigo-900 rounded-xl text-xs font-black transition">
                                    عرض التقييم والملاحظات ↗
                                </a>
                            @elseif($hasSubmitted)
                                <span class="text-xs font-bold text-amber-400 bg-amber-950/80 border border-amber-800/80 px-3 py-1.5 rounded-xl">
                                    تم التسليم (قيد التصحيح ⏳)
                                </span>
                                <a href="{{ route('parent.homeworks.show', ['id' => $hw->id]) }}" class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-bold transition">
                                    عرض التفاصيل
                                </a>
                            @else
                                <a href="{{ route('parent.homeworks.show', ['id' => $hw->id]) }}" class="px-4 py-2 bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-500 hover:to-orange-500 text-white rounded-xl text-xs font-black shadow-md shadow-amber-950 transition flex items-center gap-1">
                                    <span>حل وتسليم الواجب</span> 📝
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-500 text-center py-6">لا توجد واجبات منزلية منشورة حالياً</p>
                @endforelse
            </div>
        </div>

        {{-- ─── 5. نتائج الامتحانات والاختبارات المرصودة ─── --}}
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 sm:p-6 shadow-xl">
            <h2 class="font-black text-base text-white mb-4 flex items-center justify-between border-b border-slate-800 pb-3">
                <span class="flex items-center gap-2">
                    <span>⭐</span> سجل درجات الامتحانات والتقييمات
                </span>
            </h2>

            <div class="space-y-2.5">
                @forelse($examResults as $res)
                    <div class="flex items-center justify-between p-3.5 bg-slate-950 border border-slate-800/80 rounded-2xl text-xs hover:border-slate-700 transition">
                        <div>
                            <span class="font-bold block text-white text-sm mb-0.5">{{ $res->exam?->title ?? 'اختبار' }}</span>
                            <span class="text-[11px] font-semibold text-slate-400">التاريخ: {{ \Carbon\Carbon::parse($res->exam?->date)->format('Y-m-d') }}</span>
                        </div>
                        <div class="text-left bg-indigo-950/80 border border-indigo-800/80 px-3.5 py-1.5 rounded-xl">
                            <span class="font-black text-indigo-300 text-sm">{{ $res->marks_obtained }}</span>
                            <span class="text-[10px] text-slate-400 font-bold">/ {{ $res->exam?->total_marks }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-500 text-center py-6">لا توجد نتائج امتحانات مرصودة بعد</p>
                @endforelse
            </div>
        </div>

        {{-- ─── 5. الملازم والمطبوعات المستلمة ─── --}}
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 sm:p-6 shadow-xl">
            <h2 class="font-black text-base text-white mb-4 flex items-center justify-between border-b border-slate-800 pb-3">
                <span class="flex items-center gap-2">
                    <span>📚</span> الملازم والكتب المستلمة
                </span>
            </h2>

            <div class="space-y-2.5">
                @forelse($materials as $mat)
                    <div class="flex items-center justify-between p-3.5 bg-slate-950 border border-slate-800/80 rounded-2xl text-xs hover:border-slate-700 transition">
                        <div>
                            <span class="font-bold block text-white text-sm mb-0.5">{{ $mat->studyMaterial?->title ?? 'ملزمة' }}</span>
                            <span class="text-[11px] font-semibold text-slate-400">تاريخ الاستلام: {{ \Carbon\Carbon::parse($mat->delivered_at)->format('Y-m-d') }}</span>
                        </div>
                        <div class="text-left">
                            <span class="font-black block text-sm {{ $mat->payment_status === 'paid' ? 'text-emerald-400' : 'text-amber-400' }}">
                                {{ number_format($mat->price) }} ج.م
                            </span>
                            <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full inline-block mt-0.5 {{ $mat->payment_status === 'paid' ? 'bg-emerald-950 text-emerald-300 border border-emerald-800' : 'bg-amber-950 text-amber-300 border border-amber-800' }}">
                                {{ $mat->payment_status === 'paid' ? 'مسدد ✅' : 'آجل ⚠️' }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-500 text-center py-6">لم يتم تسليم ملازم بعد</p>
                @endforelse
            </div>
        </div>

    </div>

    {{-- ─── 6. نافذة سداد الرسوم المنبثقة الفخمة (Modal) ─── --}}
    @if($paymentSettings['enabled'])
    <div x-show="showPayModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-lg w-full p-6 text-right shadow-2xl relative" @click.outside="showPayModal = false">
            <button @click="showPayModal = false" class="absolute top-5 left-5 text-slate-400 hover:text-white text-xl font-bold">
                ✕
            </button>

            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 flex items-center justify-center text-xl">
                    💳
                </div>
                <div>
                    <h3 class="text-base font-black text-white">سداد الرسوم والاشتراكات أونلاين</h3>
                    <p class="text-[11px] text-slate-400 font-semibold">تحويل فوري عبر انستاباي أو فودافون كاش</p>
                </div>
            </div>

            {{-- اختيار طريقة التحويل --}}
            <div class="grid grid-cols-2 gap-2 mb-4">
                <button type="button" @click="activeMethod = 'vodafone_cash'" :class="activeMethod === 'vodafone_cash' ? 'border-rose-500 bg-rose-950/50 text-rose-200 font-extrabold' : 'border-slate-800 bg-slate-950 text-slate-400 font-semibold'" class="p-3 rounded-2xl border text-xs flex flex-col items-center justify-center gap-1 transition">
                    <span class="text-lg">📱</span>
                    <span>فودافون كاش / محفظة</span>
                </button>
                <button type="button" @click="activeMethod = 'instapay'" :class="activeMethod === 'instapay' ? 'border-indigo-500 bg-indigo-950/50 text-indigo-200 font-extrabold' : 'border-slate-800 bg-slate-950 text-slate-400 font-semibold'" class="p-3 rounded-2xl border text-xs flex flex-col items-center justify-center gap-1 transition">
                    <span class="text-lg">⚡</span>
                    <span>انستاباي (InstaPay)</span>
                </button>
            </div>

            {{-- بيانات التحويل بحسب الطريقة المختارة --}}
            <div class="bg-slate-950 border border-slate-800 p-4 rounded-2xl mb-4 space-y-2.5">
                <div x-show="activeMethod === 'vodafone_cash'">
                    <span class="text-[11px] font-bold text-slate-400 block mb-1">رقم فودافون كاش / المحفظة للتحويل:</span>
                    <div class="flex items-center justify-between bg-slate-900 border border-slate-800 px-3 py-2 rounded-xl">
                        <span class="font-mono text-base font-black text-rose-400" id="vfNumber">{{ $paymentSettings['vodafone_cash'] ?: 'غير محدد' }}</span>
                        <button type="button" onclick="navigator.clipboard.writeText('{{ $paymentSettings['vodafone_cash'] }}'); alert('تم نسخ الرقم بنجاح ✅');" class="px-2.5 py-1 bg-rose-900/60 hover:bg-rose-900 text-rose-200 text-xs font-bold rounded-lg transition">
                            نسخ الرقم 📋
                        </button>
                    </div>
                </div>

                <div x-show="activeMethod === 'instapay'">
                    <span class="text-[11px] font-bold text-slate-400 block mb-1">عنوان حساب انستاباي (InstaPay Address):</span>
                    <div class="flex items-center justify-between bg-slate-900 border border-slate-800 px-3 py-2 rounded-xl">
                        <span class="font-mono text-sm font-black text-indigo-400">{{ $paymentSettings['instapay_username'] ?: 'غير محدد' }}</span>
                        <button type="button" onclick="navigator.clipboard.writeText('{{ $paymentSettings['instapay_username'] }}'); alert('تم نسخ عنوان انستاباي ✅');" class="px-2.5 py-1 bg-indigo-900/60 hover:bg-indigo-900 text-indigo-200 text-xs font-bold rounded-lg transition">
                            نسخ الحساب 📋
                        </button>
                    </div>
                    @if($paymentSettings['instapay_qr'])
                        <div class="mt-3 text-center">
                            <span class="text-[10px] text-slate-400 block mb-1 font-bold">أو امسح رمز الـ QR مباشرة عبر تطبيق InstaPay:</span>
                            <img src="{{ $paymentSettings['instapay_qr'] }}" alt="InstaPay QR" class="w-36 h-36 mx-auto rounded-xl border border-indigo-500/30 p-1 bg-white">
                        </div>
                    @endif
                </div>

                @if($paymentSettings['instructions'])
                    <p class="text-[11px] text-amber-300/90 font-semibold pt-1 border-t border-slate-900 leading-relaxed">
                        💡 {{ $paymentSettings['instructions'] }}
                    </p>
                @endif
            </div>

            {{-- نموذج رفع الإيصال --}}
            <form action="{{ route('parent.payment.submit') }}" method="POST" enctype="multipart/form-data" class="space-y-3" x-data="{ isSubmitting: false }" @submit="isSubmitting = true">
                @csrf
                <input type="hidden" name="payment_method" :value="activeMethod">

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-300 mb-1">المبلغ المحول (ج.م) *</label>
                        <input type="number" step="0.5" name="amount" required placeholder="مثال: 300" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500 font-bold">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-300 mb-1">المجموعة (اختياري)</label>
                        <select name="group_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500">
                            <option value="">سداد عام بحساب الطالب</option>
                            @foreach($student->groups as $grp)
                                <option value="{{ $grp->id }}">{{ $grp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-300 mb-1">رقم الهاتف المحول منه</label>
                        <input type="text" name="sender_phone" placeholder="01xxxxxxxxx" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500 font-mono">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-300 mb-1">رقم العملية / المرجع (إن وجد)</label>
                        <input type="text" name="transaction_reference" placeholder="Ref No." class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500 font-mono">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-300 mb-1">صورة إشعار التحويل / الإيصال (Screenshot) *</label>
                    <input type="file" name="receipt" accept="image/*" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-300 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-600 file:text-white hover:file:bg-indigo-500 cursor-pointer">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-300 mb-1">ملاحظات إضافية (اختياري)</label>
                    <input type="text" name="notes" placeholder="مثال: رسوم شهر 9 + ملزمة الوحدة الأولى" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500">
                </div>

                <button type="submit" :disabled="isSubmitting" class="w-full mt-2 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white rounded-2xl text-xs font-black shadow-lg shadow-emerald-950 transition flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-show="!isSubmitting">🚀 إرسال إشعار السداد للتأكيد</span>
                    <span x-show="isSubmitting" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        جاري إرسال وتأكيد الطلب...
                    </span>
                </button>
            </form>
        </div>
    </div>
    @endif

</body>
</html>
