<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تفاصيل الواجب — {{ $homework->title }}</title>
    @php
        $faviconUrl = app(\App\Services\SettingService::class)->url('site_favicon');
    @endphp
    @if($faviconUrl)
        <link rel="icon" href="{{ $faviconUrl }}">
        <link rel="shortcut icon" href="{{ $faviconUrl }}">
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
    <style> 
        body { font-family: 'IBM Plex Sans Arabic', sans-serif; } 
        h1, h2, h3, h4, .font-heading { font-family: 'Alexandria', sans-serif; }
    </style>
</head>
<body class="bg-brand-bg text-brand-slate min-h-screen pb-16 selection:bg-brand-coral selection:text-white">

    <header class="bg-brand-teal border-b border-brand-teal-dark/50 sticky top-0 z-50 px-4 py-3.5 shadow-md shadow-brand-teal/10">
        <div class="max-w-3xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('parent.dashboard') }}" class="px-3.5 py-2 bg-white/10 hover:bg-white/20 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 border border-white/15">
                    <span>←</span> رجوع للرئيسية
                </a>
            </div>
            <div class="text-xs font-semibold text-slate-300">
                الطالب: <span class="text-white font-bold">{{ $student->name }}</span>
            </div>
        </div>
    </header>

    <main class="max-w-3xl mx-auto p-4 sm:p-6 space-y-6 mt-4">

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 p-4 rounded-2xl text-emerald-800 text-xs font-bold text-center">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-rose-50 border border-rose-200 p-4 rounded-2xl text-rose-800 text-xs font-bold text-center">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-rose-50 border border-rose-200 p-4 rounded-2xl text-rose-800 text-xs font-bold">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- بطاقة تفاصيل الواجب --}}
        <div class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 shadow-sm text-center space-y-6">
            <div>
                <span class="inline-block px-3.5 py-1 bg-amber-50 border border-amber-200 text-amber-800 rounded-full text-xs font-bold mb-2">
                    {{ $homework->subject?->name }} • {{ $homework->educationalStage?->name }}
                    @if($homework->group)
                        • {{ $homework->group->name }}
                    @endif
                </span>
                <h1 class="text-xl sm:text-2xl font-heading font-black text-brand-slate">{{ $homework->title }}</h1>
                @if($homework->description)
                    <p class="text-xs text-brand-muted mt-2 max-w-xl mx-auto leading-relaxed">
                        {{ $homework->description }}
                    </p>
                @endif
            </div>

            {{-- إحصائيات ومعلومات الواجب --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-right">
                <div class="bg-slate-50 border border-slate-200 p-4 rounded-2xl">
                    <span class="text-[11px] font-bold text-slate-500 block mb-1">آخر موعد:</span>
                    <span class="text-xs font-bold text-brand-slate">{{ $homework->due_date->format('Y-m-d h:i A') }}</span>
                </div>

                <div class="bg-slate-50 border border-slate-200 p-4 rounded-2xl">
                    <span class="text-[11px] font-bold text-slate-500 block mb-1">الدرجة الكلية:</span>
                    <span class="text-xs font-bold text-brand-slate">{{ $homework->total_marks }} درجة</span>
                </div>

                <div class="bg-slate-50 border border-slate-200 p-4 rounded-2xl col-span-2 sm:col-span-1">
                    <span class="text-[11px] font-bold text-slate-500 block mb-1">طبيعة الحل:</span>
                    <span class="text-xs font-bold text-brand-teal">
                        {{ match($homework->type) { 'questions' => 'أسئلة إلكترونية', 'file_upload' => 'رفع ملف/صورة', 'mixed' => 'أسئلة + رفع ملف', default => $homework->type } }}
                    </span>
                </div>
            </div>

            @if($homework->attachment)
                <div class="pt-2">
                    <a href="{{ asset('storage/' . $homework->attachment) }}" target="_blank" class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-100 border border-slate-300 hover:bg-slate-200 text-brand-slate rounded-2xl text-xs font-bold transition shadow-sm">
                        <span>📥</span> تحميل ملف الواجب المرفق (PDF / ورقة الأسئلة)
                    </a>
                </div>
            @endif
        </div>

        {{-- بطاقة حالة التسليم والدرجة الحالية --}}
        @if($submission)
            <div class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-7 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <span class="font-heading font-black text-sm text-brand-slate flex items-center gap-2">
                        حالة تسليمك للواجب
                    </span>
                    <span class="text-xs font-bold px-3 py-1 rounded-full {{ $submission->status === 'graded' ? 'bg-emerald-50 border border-emerald-200 text-emerald-800' : 'bg-amber-50 border border-amber-200 text-amber-800' }}">
                        {{ $submission->formatted_status }}
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200">
                        <span class="text-slate-500 block mb-1 font-bold">وقت التسليم:</span>
                        <span class="font-bold text-brand-slate">{{ $submission->submitted_at?->format('Y-m-d h:i A') }}</span>
                        @if($submission->is_late)
                            <span class="text-[10px] text-rose-600 font-bold block mt-0.5">(تسليم متأخر بعد الموعد)</span>
                        @endif
                    </div>

                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200">
                        <span class="text-slate-500 block mb-1 font-bold">الدرجة المحتسبة:</span>
                        <span class="font-black text-sm {{ $submission->score !== null ? 'text-emerald-600' : 'text-amber-800' }}">
                            {{ $submission->score !== null ? "{$submission->score} / {$homework->total_marks} ({$submission->score_percentage}%)" : 'قيد التصحيح والاعتماد' }}
                        </span>
                    </div>

                    @if($submission->attachment)
                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200 col-span-full">
                            <span class="text-slate-500 block mb-1 font-bold">الملف الذي قمت برفعه:</span>
                            <a href="{{ asset('storage/' . $submission->attachment) }}" target="_blank" class="text-brand-coral hover:text-brand-coral-hover underline font-bold flex items-center gap-1 mt-1">
                                عرض الملف المرفوع
                            </a>
                        </div>
                    @endif
                </div>

                @if($submission->teacher_feedback)
                    <div class="p-4 bg-brand-teal/5 border border-brand-teal/15 rounded-2xl text-xs space-y-1">
                        <span class="font-bold text-brand-teal">ملاحظات وتوجيهات المعلم:</span>
                        <p class="text-brand-slate mt-1 leading-relaxed">{{ $submission->teacher_feedback }}</p>
                    </div>
                @endif
            </div>
        @endif

        {{-- نموذج حل وتسليم الواجب --}}
        @php
            $canSubmit = $homework->canAcceptSubmissions() && (! $submission || ! $submission->isGraded());
        @endphp

        @if($canSubmit)
            <form action="{{ route('parent.homeworks.submit', ['id' => $homework->id]) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                {{-- إذا الواجب به أسئلة إلكترونية --}}
                @if(in_array($homework->type, ['questions', 'mixed']) && $homework->questions->isNotEmpty())
                    <div class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-7 shadow-sm space-y-6">
                        <h3 class="font-heading font-black text-base text-brand-slate border-b border-slate-100 pb-3">
                            أجب عن أسئلة الواجب:
                        </h3>

                        @foreach($homework->questions as $index => $question)
                            <div class="bg-slate-50/70 border border-slate-200 rounded-2xl p-5 space-y-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="font-bold text-sm text-brand-slate leading-relaxed">
                                        <span class="text-brand-coral ml-1 font-black">س{{ $index + 1 }}:</span> {{ $question->question_text }}
                                    </div>
                                    <span class="px-3 py-1 bg-white border border-slate-200 text-brand-teal text-[11px] font-bold rounded-xl shrink-0 shadow-sm">
                                        {{ $question->pivot->marks ?? 1 }} درجة
                                    </span>
                                </div>

                                @if($question->question_image)
                                    <div class="my-3 text-center">
                                        <img src="{{ asset('storage/' . $question->question_image) }}" alt="Question Image" class="max-h-60 rounded-xl border border-slate-200 inline-block object-contain">
                                    </div>
                                @endif

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-2">
                                    @foreach($question->options as $opt)
                                        <label class="flex items-center gap-3 p-3.5 bg-white hover:bg-slate-50 border border-slate-200 rounded-2xl cursor-pointer transition shadow-sm">
                                            <input type="{{ $question->type === 'multiple_choice' ? 'checkbox' : 'radio' }}" 
                                                   name="answers[{{ $question->id }}]{{ $question->type === 'multiple_choice' ? '[]' : '' }}" 
                                                   value="{{ $opt['key'] }}"
                                                   class="w-4 h-4 text-brand-coral focus:ring-brand-coral border-slate-300 rounded">
                                            <span class="text-xs font-bold text-brand-slate">
                                                <span class="text-brand-teal font-mono ml-1 font-black">({{ $opt['key'] }})</span> {{ $opt['text'] }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- إذا الواجب يتطلب رفع ملف --}}
                @if(in_array($homework->type, ['file_upload', 'mixed']))
                    <div class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-7 shadow-sm space-y-4">
                        <h3 class="font-heading font-black text-base text-brand-slate">
                            رفع ملف أو صورة الحل
                        </h3>
                        <p class="text-xs text-brand-muted leading-relaxed">
                            قم بحل الواجب في كشكولك وتصويره أو إعداد ملف PDF ثم رفعه هنا لتصحيحه من قِبل المعلم:
                        </p>

                        <div class="border-2 border-dashed border-slate-300 hover:border-brand-teal rounded-2xl p-6 text-center transition bg-slate-50">
                            <input type="file" name="attachment" id="attachment" accept=".pdf,image/*" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-brand-teal file:text-white hover:file:bg-brand-teal-dark cursor-pointer">
                            <p class="text-[11px] text-slate-400 mt-2 font-medium">الحد الأقصى المسموح: 10 ميجابايت (ملفات PDF أو صور JPG / PNG)</p>
                        </div>
                    </div>
                @endif

                {{-- ملاحظات الطالب --}}
                <div class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-7 shadow-sm space-y-3">
                    <label for="notes" class="font-bold text-xs text-brand-slate">ملاحظات أو أسئلة للمعلم (اختياري):</label>
                    <textarea name="notes" id="notes" rows="2" placeholder="اكتب أي ملاحظة أو استفسار تود إيصاله للمعلم مع هذا الواجب..." class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-3.5 text-xs text-brand-slate placeholder:text-slate-400 focus:outline-none focus:border-brand-teal"></textarea>
                </div>

                <div class="text-left">
                    <button type="submit" class="px-8 py-3.5 bg-gradient-to-r from-brand-coral to-[#FF7552] hover:from-brand-coral-hover hover:to-brand-coral text-white font-heading font-bold rounded-2xl text-xs sm:text-sm shadow-md shadow-brand-coral/25 transition">
                        {{ $submission ? 'إعادة تسليم وتحديث الواجب' : 'تسليم الواجب الآن' }}
                    </button>
                </div>
            </form>
        @else
            <div class="p-6 bg-slate-50 border border-slate-200 rounded-3xl text-center text-slate-500 text-xs font-bold">
                لا يمكن تسليم هذا الواجب حالياً (إما لأنه تم اعتماده وتصحيحه، أو أن موعد التسليم انتهى والمعلم أغلق إمكانية التسليم المتأخر).
            </div>
        @endif

    </main>

</body>
</html>
