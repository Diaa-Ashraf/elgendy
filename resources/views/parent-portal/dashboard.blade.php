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
            box-shadow: 0 12px 24px -8px rgba(13, 59, 76, 0.08);
            border-color: #CBD5E1;
        }
    </style>
</head>
<body class="bg-brand-bg text-brand-slate min-h-screen pb-16 selection:bg-brand-coral selection:text-white" x-data="{ showPayModal: false, activeMethod: 'vodafone_cash' }">

    {{-- الشريط العلوي الفخم بأسلوب Aegean Teal Glass --}}
    <header class="bg-brand-teal border-b border-brand-teal-dark/50 sticky top-0 z-50 px-4 py-3.5 shadow-md shadow-brand-teal/10 backdrop-blur-md">
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white/10 text-white rounded-2xl flex items-center justify-center font-black text-sm border border-white/20 shadow-inner">
                    🎓
                </div>
                <div>
                    <h1 class="font-heading font-bold text-base text-white leading-snug">{{ $student->name }}</h1>
                    <p class="text-[11px] font-semibold text-slate-300 flex items-center gap-1.5">
                        <span>{{ $student->educationalStage?->name ?? 'غير محدد' }}</span>
                        <span class="text-white/40">•</span>
                        <span class="font-mono text-white/90 bg-white/15 px-1.5 py-0.5 rounded-md text-[10px]">#{{ $student->id }}</span>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                @if($paymentSettings['enabled'])
                    <button @click="showPayModal = true" class="px-3.5 py-2 bg-gradient-to-r from-brand-coral to-[#FF784B] hover:from-brand-coral-hover hover:to-brand-coral text-white rounded-xl text-xs font-bold shadow-md shadow-brand-coral/25 transition flex items-center gap-1.5">
                        <span>💳</span>
                        <span>سداد أونلاين</span>
                    </button>
                @endif
                <a href="{{ route('home') }}" class="px-3 py-2 bg-white/10 hover:bg-white/20 text-white border border-white/15 rounded-xl text-xs font-bold transition">
                    الموقع
                </a>
                <a href="{{ route('parent.logout') }}" class="px-3 py-2 bg-rose-500/20 border border-rose-400/30 text-rose-100 hover:bg-rose-500/30 rounded-xl text-xs font-bold transition">
                    خروج
                </a>
            </div>
        </div>
    </header>

    <div class="max-w-4xl mx-auto p-4 sm:p-6 space-y-6">

        {{-- رسائل التنبيه والنجاح --}}
        @if(session('payment_success'))
            <div class="bg-emerald-50 border border-emerald-200 p-4 sm:p-5 rounded-2xl text-emerald-900 shadow-sm flex items-start gap-3">
                <span class="text-xl">✅</span>
                <div>
                    <h3 class="font-heading font-bold text-sm text-emerald-950 mb-0.5">تم استلام طلب السداد بنجاح!</h3>
                    <p class="text-xs font-medium leading-relaxed text-emerald-800">{{ session('payment_success') }}</p>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-rose-50 border border-rose-200 p-4 rounded-2xl text-rose-900 text-xs space-y-1">
                <div class="font-heading font-bold text-sm mb-1 text-rose-950">يرجى تصحيح الأخطاء التالية:</div>
                @foreach($errors->all() as $err)
                    <div>• {{ $err }}</div>
                @endforeach
            </div>
        @endif

        {{-- ─── 1. كروت الإحصائيات الأكاديمية والمالية (Bento Metric Cards) ─── --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            {{-- كارت الرصيد --}}
            <div class="bento-card p-5 shadow-sm flex flex-col justify-between relative overflow-hidden group">
                <div class="absolute top-0 left-0 w-24 h-24 {{ $ledger['balance'] >= 0 ? 'bg-brand-mint/5' : 'bg-rose-500/5' }} rounded-full blur-xl pointer-events-none"></div>
                <div>
                    <span class="text-xs font-bold text-brand-muted block mb-1">الرصيد المالي الحالي</span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-heading font-black {{ $ledger['balance'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ number_format(abs($ledger['balance']), 0) }}
                        </span>
                        <span class="text-xs font-bold text-slate-500">ج.م {{ $ledger['balance'] >= 0 ? '(فائض/مقدم)' : '(مستحق الدفع)' }}</span>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                    <span class="inline-flex items-center gap-1 text-[11px] font-bold px-3 py-1 rounded-full {{ $ledger['balance'] >= 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $ledger['balance'] >= 0 ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                        {{ $ledger['balance'] >= 0 ? 'مسدد بالكامل' : 'متبقي مديونية' }}
                    </span>
                    @if($paymentSettings['enabled'] && $ledger['balance'] < 0)
                        <button @click="showPayModal = true" class="text-xs font-bold text-brand-coral hover:text-brand-coral-hover transition">
                            سداد الآن ➔
                        </button>
                    @endif
                </div>
            </div>

            {{-- كارت المجموعات --}}
            <div class="bento-card p-5 shadow-sm flex flex-col justify-between">
                <div>
                    <span class="text-xs font-bold text-brand-muted block mb-1">المجموعات المقيد بها</span>
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-3xl font-heading font-black text-brand-teal">
                            {{ $student->groups->count() }}
                        </span>
                        <span class="text-xs font-bold text-slate-500">مجموعات دراسية</span>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100">
                    <span class="text-[11px] font-bold text-brand-teal bg-brand-teal/5 px-2.5 py-1 rounded-lg inline-block border border-brand-teal/10">
                        حالة القيد: دراسة منتظمة
                    </span>
                </div>
            </div>

            {{-- كارت الحضور --}}
            <div class="bento-card p-5 shadow-sm flex flex-col justify-between">
                <div>
                    <span class="text-xs font-bold text-brand-muted block mb-1">نسبة الحضور والالتزام</span>
                    @php
                        $totalSessions = $attendances->count();
                        $presentCount = $attendances->where('status', 'present')->count();
                        $rate = $totalSessions > 0 ? round(($presentCount / $totalSessions) * 100) : 100;
                    @endphp
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-3xl font-heading font-black text-emerald-600">
                            {{ $rate }}%
                        </span>
                        <span class="text-xs font-bold text-slate-500">معدل الحضور</span>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100">
                    <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                        <div class="bg-gradient-to-r from-emerald-400 to-brand-mint h-full rounded-full" style="width: {{ $rate }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ─── 2. كشف الحساب والمدفوعات التفصيلي ─── --}}
        <div class="bento-card p-5 sm:p-7 shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-brand-teal/10 text-brand-teal flex items-center justify-center font-bold text-sm">💰</span>
                    <h2 class="font-heading font-black text-base text-brand-slate">
                        كشف الحساب المالي وسجل السداد
                    </h2>
                </div>
                @if($paymentSettings['enabled'])
                    <button @click="showPayModal = true" class="px-3.5 py-1.5 bg-brand-teal/10 hover:bg-brand-teal/20 text-brand-teal border border-brand-teal/20 rounded-xl text-xs font-bold transition">
                        تحويل فودافون كاش / انستاباي
                    </button>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div class="p-4 bg-amber-50/70 rounded-2xl border border-amber-200/80">
                    <span class="text-xs font-bold text-amber-900 block mb-1">إجمالي المستحقات (الاشتراكات والملازم)</span>
                    <span class="text-xl font-heading font-black text-amber-800">{{ number_format($ledger['total_dues'] ?? 0) }} ج.م</span>
                </div>
                <div class="p-4 bg-emerald-50/70 rounded-2xl border border-emerald-200/80">
                    <span class="text-xs font-bold text-emerald-900 block mb-1">إجمالي المبالغ المسددة المعتمدة</span>
                    <span class="text-xl font-heading font-black text-emerald-700">{{ number_format($ledger['total_paid'] ?? 0) }} ج.م</span>
                </div>
            </div>

            {{-- قائمة طلبات السداد الإلكتروني المرفوعة وحالتها --}}
            @if($onlinePaymentRequests->isNotEmpty())
                <div class="mt-5 pt-4 border-t border-slate-100">
                    <span class="text-xs font-bold text-brand-slate block mb-3">آخر طلبات السداد الإلكتروني المرفوعة:</span>
                    <div class="space-y-2.5">
                        @foreach($onlinePaymentRequests as $req)
                            <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between text-xs">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-brand-slate text-sm">{{ number_format($req->amount) }} ج.م</span>
                                        <span class="text-[11px] font-semibold text-slate-500">({{ $req->payment_method === 'instapay' ? 'انستاباي' : 'فودافون كاش' }})</span>
                                        @if($req->group)
                                            <span class="text-[10px] text-brand-teal bg-white px-2 py-0.5 rounded-md border border-slate-200 font-bold">{{ $req->group->name }}</span>
                                        @endif
                                    </div>
                                    <span class="text-[11px] text-slate-400 block">{{ $req->created_at->format('Y-m-d h:i A') }}</span>
                                </div>
                                <div class="text-left">
                                    @if($req->status === 'pending')
                                        <span class="px-3 py-1 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl font-bold text-[11px]">قيد المراجعة والاعتماد</span>
                                    @elseif($req->status === 'approved')
                                        <span class="px-3 py-1 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl font-bold text-[11px]">معتمد ومسجل بالحساب</span>
                                    @else
                                        <div>
                                            <span class="px-3 py-1 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl font-bold text-[11px]">مرفوض</span>
                                            @if($req->rejection_reason)
                                                <p class="text-[10px] text-rose-600 mt-1 max-w-[200px] truncate" title="{{ $req->rejection_reason }}">{{ $req->rejection_reason }}</p>
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
        <div class="bento-card p-5 sm:p-7 shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-brand-teal/10 text-brand-teal flex items-center justify-center font-bold text-sm">🗓️</span>
                    <h2 class="font-heading font-black text-base text-brand-slate">
                        جدول الحصص والمجموعات الدراسية
                    </h2>
                </div>
                <span class="text-xs font-bold text-brand-teal bg-brand-teal/10 px-3 py-1 rounded-xl border border-brand-teal/20">
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
                        <div class="bg-slate-50/70 border border-slate-200/80 rounded-2xl p-4 flex flex-col justify-between hover:border-brand-teal/40 transition space-y-3">
                            <div>
                                <div class="flex items-start justify-between gap-2 mb-2">
                                    <div>
                                        <h3 class="font-heading font-bold text-brand-slate text-sm leading-snug">{{ $grp->name }}</h3>
                                        @if($grp->subject)
                                            <span class="text-[11px] font-bold text-brand-teal block mt-0.5">
                                                المادة: {{ $grp->subject->name }}
                                            </span>
                                        @endif
                                    </div>
                                    <span class="text-[10px] font-bold px-2.5 py-1 rounded-lg bg-white text-brand-slate border border-slate-200 shrink-0 shadow-sm">
                                        {{ $grp->monthly_fee ? number_format($grp->monthly_fee) . ' ج.م / شهر' : 'اشتراك شهري' }}
                                    </span>
                                </div>
                            </div>

                            {{-- مواعيد الحصص الأسبوعية لهذه المجموعة --}}
                            <div class="pt-3 border-t border-slate-200/60">
                                <span class="text-[11px] font-bold text-slate-500 block mb-2">مواعيد الحصص الأسبوعية:</span>
                                @if($grp->schedules && $grp->schedules->isNotEmpty())
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        @foreach($grp->schedules as $sch)
                                            <div class="flex items-center justify-between bg-white border border-slate-200 px-3 py-2 rounded-xl text-xs shadow-sm">
                                                <div class="font-bold text-brand-slate">
                                                    <span>{{ $dayNames[$sch->day_of_week] ?? $sch->day_of_week }}</span>
                                                </div>
                                                <div class="text-left font-mono font-bold text-brand-teal">
                                                    {{ \Carbon\Carbon::parse($sch->time)->format('g:i A') }}
                                                    @if($sch->room)
                                                        <span class="text-[10px] text-slate-400 font-normal block">({{ $sch->room }})</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-xs text-slate-500 italic bg-white p-2.5 rounded-xl text-center border border-slate-200">
                                        يتم تحديد وتأكيد مواعيد هذه المجموعة قريباً من الإدارة
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-6 bg-slate-50 border border-slate-200 rounded-2xl text-center text-slate-500 text-xs font-bold">
                    لم يتم تعيين الطالب في أي مجموعة دراسية بعد. يرجى مراجعة إدارة السنتر لتحديد المجموعة والجدول.
                </div>
            @endif
        </div>

        {{-- ─── 4. سجل الحضور والغياب ─── --}}
        <div class="bento-card p-5 sm:p-7 shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-sm">📋</span>
                    <h2 class="font-heading font-black text-base text-brand-slate">
                        سجل الحضور والغياب
                    </h2>
                </div>
                <span class="text-xs font-bold text-slate-500">آخر 15 حصة</span>
            </div>

            <div class="space-y-2.5">
                @forelse($attendances as $att)
                    <div class="flex items-center justify-between p-3.5 bg-slate-50/70 border border-slate-200 rounded-2xl text-xs hover:border-slate-300 transition">
                        <div>
                            <span class="font-bold block text-brand-slate text-sm mb-0.5">{{ $att->groupSession?->group?->name ?? 'حصة عامة' }}</span>
                            <span class="text-[11px] font-semibold text-slate-400">تاريخ الحصة: {{ \Carbon\Carbon::parse($att->groupSession?->date)->format('Y-m-d') }}</span>
                        </div>
                        <div>
                            @if($att->status === 'present')
                                <span class="px-3 py-1 bg-emerald-100/70 border border-emerald-200 text-emerald-800 rounded-xl font-bold text-xs">حضر</span>
                            @elseif($att->status === 'late')
                                <span class="px-3 py-1 bg-amber-100/70 border border-amber-200 text-amber-800 rounded-xl font-bold text-xs">متأخر</span>
                            @else
                                <span class="px-3 py-1 bg-rose-100/70 border border-rose-200 text-rose-800 rounded-xl font-bold text-xs">غائب</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 text-center py-6">لا توجد سجلات حضور مسجلة حالياً</p>
                @endforelse
            </div>
        </div>

        {{-- ─── 5. الاختبارات الإلكترونية أونلاين ─── --}}
        <div class="bento-card p-5 sm:p-7 shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-brand-coral/10 text-brand-coral flex items-center justify-center font-bold text-sm">📝</span>
                    <h2 class="font-heading font-black text-base text-brand-slate">
                        الاختبارات الإلكترونية والكويزات
                    </h2>
                </div>
                <span class="text-xs font-bold text-brand-coral bg-brand-coral/10 px-3 py-1 rounded-xl border border-brand-coral/20">{{ count($onlineExams) }} اختبار متاح</span>
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
                                <span class="text-[10px] font-bold text-brand-teal bg-white px-2 py-0.5 rounded border border-slate-200">{{ $onlineExam->subject?->name }}</span>
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
                                <a href="{{ route('parent.exams.show', ['id' => $onlineExam->id]) }}" class="px-4 py-2 bg-gradient-to-r from-emerald-600 to-brand-mint hover:from-emerald-700 hover:to-emerald-600 text-white rounded-xl text-xs font-bold shadow-sm transition">
                                    بدء الاختبار
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 text-center py-6">لا توجد اختبارات إلكترونية منشورة لمرحلتك الدراسية حالياً</p>
                @endforelse
            </div>
        </div>

        {{-- ─── 6. الواجبات والتكليفات المنزلية ─── --}}
        <div class="bento-card p-5 sm:p-7 shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center font-bold text-sm">📚</span>
                    <h2 class="font-heading font-black text-base text-brand-slate">
                        الواجبات والتكليفات المنزلية
                    </h2>
                </div>
                <span class="text-xs font-bold text-slate-500">{{ count($homeworks ?? []) }} واجب متاح</span>
            </div>

            <div class="space-y-3">
                @forelse($homeworks ?? [] as $hw)
                    @php
                        $sub = $hw->student_submission;
                        $hasSubmitted = $sub && $sub->isSubmitted();
                        $isGraded = $sub && $sub->isGraded();
                    @endphp

                    <div class="p-4 bg-slate-50/70 border border-slate-200 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:border-slate-300 transition">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-brand-slate text-sm">{{ $hw->title }}</span>
                                <span class="text-[10px] font-bold text-amber-800 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">{{ $hw->subject?->name }}</span>
                                @if($hw->group)
                                    <span class="text-[10px] font-bold text-slate-600 bg-white px-2 py-0.5 rounded border border-slate-200">{{ $hw->group->name }}</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-3 text-[11px] text-slate-500 font-medium">
                                <span>آخر موعد: {{ $hw->due_date->format('Y-m-d h:i A') }}</span>
                                <span>•</span>
                                <span>الدرجة: {{ $hw->total_marks }} درجة</span>
                                <span>•</span>
                                <span class="{{ $hw->isOverdue() ? 'text-rose-600 font-bold' : 'text-emerald-600 font-bold' }}">
                                    {{ $hw->isOverdue() ? 'انتهى الموعد' : 'متاح للتسليم' }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 self-end sm:self-center">
                            @if($isGraded)
                                <div class="text-left ml-2">
                                    <span class="text-xs font-black text-emerald-600">
                                        الدرجة: {{ $sub->score }} / {{ $hw->total_marks }} ({{ $sub->score_percentage }}%)
                                    </span>
                                </div>
                                <a href="{{ route('parent.homeworks.show', ['id' => $hw->id]) }}" class="px-3.5 py-1.5 bg-white border border-slate-300 text-brand-slate hover:bg-slate-50 rounded-xl text-xs font-bold transition shadow-sm">
                                    عرض التقييم والملاحظات ➔
                                </a>
                            @elseif($hasSubmitted)
                                <span class="text-xs font-bold text-amber-800 bg-amber-50 border border-amber-200 px-3 py-1.5 rounded-xl">
                                    تم التسليم (قيد التصحيح)
                                </span>
                                <a href="{{ route('parent.homeworks.show', ['id' => $hw->id]) }}" class="px-3.5 py-1.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-bold transition shadow-sm">
                                    عرض التفاصيل
                                </a>
                            @else
                                <a href="{{ route('parent.homeworks.show', ['id' => $hw->id]) }}" class="px-4 py-2 bg-gradient-to-r from-brand-coral to-[#FF784B] hover:from-brand-coral-hover hover:to-brand-coral text-white rounded-xl text-xs font-bold shadow-md shadow-brand-coral/20 transition">
                                    حل وتسليم الواجب
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 text-center py-6">لا توجد واجبات منزلية منشورة حالياً</p>
                @endforelse
            </div>
        </div>

        {{-- ─── 7. نتائج الامتحانات والاختبارات المرصودة ─── --}}
        <div class="bento-card p-5 sm:p-7 shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-brand-teal/10 text-brand-teal flex items-center justify-center font-bold text-sm">🏆</span>
                    <h2 class="font-heading font-black text-base text-brand-slate">
                        سجل درجات الامتحانات والتقييمات
                    </h2>
                </div>
            </div>

            <div class="space-y-2.5">
                @forelse($examResults as $res)
                    <div class="flex items-center justify-between p-3.5 bg-slate-50/70 border border-slate-200 rounded-2xl text-xs hover:border-slate-300 transition">
                        <div>
                            <span class="font-bold block text-brand-slate text-sm mb-0.5">{{ $res->exam?->title ?? 'اختبار' }}</span>
                            <span class="text-[11px] font-semibold text-slate-400">التاريخ: {{ \Carbon\Carbon::parse($res->exam?->date)->format('Y-m-d') }}</span>
                        </div>
                        <div class="text-left bg-white border border-slate-200 px-4 py-2 rounded-xl shadow-sm">
                            <span class="font-black text-brand-teal text-sm">{{ $res->marks_obtained }}</span>
                            <span class="text-[10px] text-slate-400 font-bold">/ {{ $res->exam?->total_marks }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 text-center py-6">لا توجد نتائج امتحانات مرصودة بعد</p>
                @endforelse
            </div>
        </div>

        {{-- ─── 8. الملازم والمطبوعات المستلمة ─── --}}
        <div class="bento-card p-5 sm:p-7 shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-sm">📖</span>
                    <h2 class="font-heading font-black text-base text-brand-slate">
                        الملازم والكتب المستلمة
                    </h2>
                </div>
            </div>

            <div class="space-y-2.5">
                @forelse($materials as $mat)
                    <div class="flex items-center justify-between p-3.5 bg-slate-50/70 border border-slate-200 rounded-2xl text-xs hover:border-slate-300 transition">
                        <div>
                            <span class="font-bold block text-brand-slate text-sm mb-0.5">{{ $mat->studyMaterial?->title ?? 'ملزمة' }}</span>
                            <span class="text-[11px] font-semibold text-slate-400">تاريخ الاستلام: {{ \Carbon\Carbon::parse($mat->delivered_at)->format('Y-m-d') }}</span>
                        </div>
                        <div class="text-left">
                            <span class="font-bold block text-sm {{ $mat->payment_status === 'paid' ? 'text-emerald-700' : 'text-amber-800' }}">
                                {{ number_format($mat->price) }} ج.م
                            </span>
                            <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full inline-block mt-0.5 {{ $mat->payment_status === 'paid' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-amber-50 text-amber-800 border border-amber-200' }}">
                                {{ $mat->payment_status === 'paid' ? 'مسدد' : 'آجل' }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 text-center py-6">لم يتم تسليم ملازم بعد</p>
                @endforelse
            </div>
        </div>

    </div>

    {{-- ─── 9. نافذة سداد الرسوم المنبثقة (Modal) ─── --}}
    @if($paymentSettings['enabled'])
    <div x-show="showPayModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-brand-slate/60 backdrop-blur-md flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white border border-slate-200 rounded-3xl max-w-lg w-full p-6 sm:p-8 text-right shadow-2xl relative" @click.outside="showPayModal = false">
            <button @click="showPayModal = false" class="absolute top-5 left-5 text-slate-400 hover:text-brand-slate text-xl font-bold w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center transition">
                ✕
            </button>

            <div class="flex items-center gap-3 mb-5">
                <div class="w-12 h-12 rounded-2xl bg-brand-teal/10 text-brand-teal flex items-center justify-center text-xl font-bold border border-brand-teal/20">
                    💳
                </div>
                <div>
                    <h3 class="text-base font-heading font-black text-brand-slate">سداد الرسوم والاشتراكات أونلاين</h3>
                    <p class="text-xs text-brand-muted font-medium">تحويل فوري عبر انستاباي أو فودافون كاش</p>
                </div>
            </div>

            {{-- اختيار طريقة التحويل --}}
            <div class="grid grid-cols-2 gap-2.5 mb-5">
                <button type="button" @click="activeMethod = 'vodafone_cash'" :class="activeMethod === 'vodafone_cash' ? 'border-brand-coral bg-brand-coral/10 text-brand-coral font-bold shadow-sm' : 'border-slate-200 bg-slate-50 text-slate-600 font-medium'" class="p-3 rounded-2xl border text-xs flex flex-col items-center justify-center gap-1.5 transition">
                    <span class="font-bold">📱 فودافون كاش / محفظة</span>
                </button>
                <button type="button" @click="activeMethod = 'instapay'" :class="activeMethod === 'instapay' ? 'border-brand-teal bg-brand-teal/10 text-brand-teal font-bold shadow-sm' : 'border-slate-200 bg-slate-50 text-slate-600 font-medium'" class="p-3 rounded-2xl border text-xs flex flex-col items-center justify-center gap-1.5 transition">
                    <span class="font-bold">⚡ انستاباي (InstaPay)</span>
                </button>
            </div>

            {{-- بيانات التحويل بحسب الطريقة المختارة --}}
            <div class="bg-slate-50 border border-slate-200 p-4 rounded-2xl mb-5 space-y-3">
                <div x-show="activeMethod === 'vodafone_cash'">
                    <span class="text-xs font-bold text-slate-700 block mb-1.5">رقم فودافون كاش / المحفظة للتحويل:</span>
                    <div class="flex items-center justify-between bg-white border border-slate-200 px-3.5 py-2.5 rounded-xl shadow-sm">
                        <span class="font-mono text-base font-bold text-brand-slate" id="vfNumber">{{ $paymentSettings['vodafone_cash'] ?: 'غير محدد' }}</span>
                        <button type="button" onclick="navigator.clipboard.writeText('{{ $paymentSettings['vodafone_cash'] }}'); alert('تم نسخ الرقم بنجاح');" class="px-3 py-1.5 bg-brand-coral text-white text-xs font-bold rounded-lg shadow-sm hover:bg-brand-coral-hover transition">
                            نسخ الرقم
                        </button>
                    </div>
                </div>

                <div x-show="activeMethod === 'instapay'">
                    <span class="text-xs font-bold text-slate-700 block mb-1.5">عنوان حساب انستاباي (InstaPay Address):</span>
                    <div class="flex items-center justify-between bg-white border border-slate-200 px-3.5 py-2.5 rounded-xl shadow-sm">
                        <span class="font-mono text-sm font-bold text-brand-teal">{{ $paymentSettings['instapay_username'] ?: 'غير محدد' }}</span>
                        <button type="button" onclick="navigator.clipboard.writeText('{{ $paymentSettings['instapay_username'] }}'); alert('تم نسخ عنوان انستاباي');" class="px-3 py-1.5 bg-brand-teal text-white text-xs font-bold rounded-lg shadow-sm hover:bg-brand-teal-dark transition">
                            نسخ الحساب
                        </button>
                    </div>
                    @if($paymentSettings['instapay_qr'])
                        <div class="mt-3 text-center">
                            <span class="text-xs text-slate-500 block mb-1 font-bold">أو امسح رمز الـ QR مباشرة عبر تطبيق InstaPay:</span>
                            <img src="{{ $paymentSettings['instapay_qr'] }}" alt="InstaPay QR" class="w-36 h-36 mx-auto rounded-xl border border-slate-200 p-1 bg-white shadow-sm">
                        </div>
                    @endif
                </div>

                @if($paymentSettings['instructions'])
                    <p class="text-xs text-amber-800 font-medium pt-2 border-t border-slate-200/80 leading-relaxed">
                        ملاحظة: {{ $paymentSettings['instructions'] }}
                    </p>
                @endif
            </div>

            {{-- نموذج رفع الإيصال --}}
            <form action="{{ route('parent.payment.submit') }}" method="POST" enctype="multipart/form-data" class="space-y-3" x-data="{ isSubmitting: false }" @submit="isSubmitting = true">
                @csrf
                <input type="hidden" name="payment_method" :value="activeMethod">

                <div class="grid grid-cols-2 gap-2.5">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">المبلغ المحول (ج.م) *</label>
                        <input type="number" step="0.5" name="amount" required placeholder="مثال: 300" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs text-brand-slate focus:outline-none focus:border-brand-teal font-bold">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">المجموعة (اختياري)</label>
                        <select name="group_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs text-brand-slate focus:outline-none focus:border-brand-teal">
                            <option value="">سداد عام بحساب الطالب</option>
                            @foreach($student->groups as $grp)
                                <option value="{{ $grp->id }}">{{ $grp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2.5">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">رقم الهاتف المحول منه</label>
                        <input type="text" name="sender_phone" placeholder="01xxxxxxxxx" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs text-brand-slate focus:outline-none focus:border-brand-teal font-mono">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">رقم العملية / المرجع (إن وجد)</label>
                        <input type="text" name="transaction_reference" placeholder="Ref No." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs text-brand-slate focus:outline-none focus:border-brand-teal font-mono">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">صورة إشعار التحويل / الإيصال (Screenshot) *</label>
                    <input type="file" name="receipt" accept="image/*" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-600 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-brand-teal file:text-white hover:file:bg-brand-teal-dark cursor-pointer">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">ملاحظات إضافية (اختياري)</label>
                    <input type="text" name="notes" placeholder="مثال: رسوم شهر 9 + ملزمة الوحدة الأولى" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs text-brand-slate focus:outline-none focus:border-brand-teal">
                </div>

                <button type="submit" :disabled="isSubmitting" class="w-full mt-3 py-3 bg-gradient-to-r from-brand-coral to-[#FF7552] hover:from-brand-coral-hover hover:to-brand-coral text-white rounded-xl text-xs font-bold shadow-lg shadow-brand-coral/20 transition flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-show="!isSubmitting">إرسال إشعار السداد للتأكيد</span>
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
