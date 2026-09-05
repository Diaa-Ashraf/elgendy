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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alexandria:wght@400;600;700;800&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style> 
        body { font-family: 'IBM Plex Sans Arabic', sans-serif; } 
        h1, h2, h3, h4, .font-heading { font-family: 'Alexandria', sans-serif; }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen pb-16 selection:bg-sky-600 selection:text-white">

    <header class="bg-slate-900 border-b border-slate-800 sticky top-0 z-50 px-4 py-3.5 shadow-sm">
        <div class="max-w-3xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('parent.dashboard') }}" class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                    <span>←</span> رجوع للرئيسية
                </a>
            </div>
            <div class="text-xs font-semibold text-slate-400">
                الطالب: <span class="text-white font-bold">{{ $student->name }}</span>
            </div>
        </div>
    </header>

    <main class="max-w-3xl mx-auto p-4 sm:p-6 space-y-6 mt-4">

        @if(session('success'))
            <div class="bg-emerald-950/80 border border-emerald-700 p-4 rounded-xl text-emerald-200 text-xs font-bold text-center">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-rose-950/80 border border-rose-800 p-4 rounded-xl text-rose-200 text-xs font-bold text-center">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-rose-950/80 border border-rose-800 p-4 rounded-xl text-rose-200 text-xs font-bold">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- بطاقة تفاصيل الواجب --}}
        <div class="bg-slate-800/90 border border-slate-700/80 rounded-2xl p-6 sm:p-8 shadow-sm text-center space-y-6">
            <div>
                <span class="inline-block px-3 py-1 bg-amber-950/60 border border-amber-800 text-amber-300 rounded-full text-xs font-bold mb-2">
                    {{ $homework->subject?->name }} • {{ $homework->educationalStage?->name }}
                    @if($homework->group)
                        • {{ $homework->group->name }}
                    @endif
                </span>
                <h1 class="text-xl sm:text-2xl font-heading font-bold text-white">{{ $homework->title }}</h1>
                @if($homework->description)
                    <p class="text-xs text-slate-400 mt-2 max-w-xl mx-auto leading-relaxed">
                        {{ $homework->description }}
                    </p>
                @endif
            </div>

            {{-- إحصائيات ومعلومات الواجب --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-right">
                <div class="bg-slate-900/90 border border-slate-700 p-3.5 rounded-xl">
                    <span class="text-[11px] font-semibold text-slate-400 block mb-1">آخر موعد:</span>
                    <span class="text-xs font-bold text-white">{{ $homework->due_date->format('Y-m-d h:i A') }}</span>
                </div>

                <div class="bg-slate-900/90 border border-slate-700 p-3.5 rounded-xl">
                    <span class="text-[11px] font-semibold text-slate-400 block mb-1">الدرجة الكلية:</span>
                    <span class="text-xs font-bold text-white">{{ $homework->total_marks }} درجة</span>
                </div>

                <div class="bg-slate-900/90 border border-slate-700 p-3.5 rounded-xl col-span-2 sm:col-span-1">
                    <span class="text-[11px] font-semibold text-slate-400 block mb-1">طبيعة الحل:</span>
                    <span class="text-xs font-bold text-sky-300">
                        {{ match($homework->type) { 'questions' => 'أسئلة إلكترونية', 'file_upload' => 'رفع ملف/صورة', 'mixed' => 'أسئلة + رفع ملف', default => $homework->type } }}
                    </span>
                </div>
            </div>

            @if($homework->attachment)
                <div class="pt-2">
                    <a href="{{ asset('storage/' . $homework->attachment) }}" target="_blank" class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-800 border border-slate-600 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-bold transition">
                        تحميل ملف الواجب المرفق (PDF / ورقة الأسئلة)
                    </a>
                </div>
            @endif
        </div>

        {{-- بطاقة حالة التسليم والدرجة الحالية --}}
        @if($submission)
            <div class="bg-slate-800/90 border border-slate-700/80 rounded-2xl p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-700 pb-3">
                    <span class="font-heading font-bold text-sm text-white flex items-center gap-2">
                        حالة تسليمك للواجب
                    </span>
                    <span class="text-xs font-bold px-3 py-1 rounded-xl {{ $submission->status === 'graded' ? 'bg-emerald-950/80 border border-emerald-800 text-emerald-300' : 'bg-amber-950/80 border border-amber-800 text-amber-300' }}">
                        {{ $submission->formatted_status }}
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div class="bg-slate-900/90 p-3.5 rounded-xl border border-slate-700">
                        <span class="text-slate-400 block mb-1">وقت التسليم:</span>
                        <span class="font-bold text-white">{{ $submission->submitted_at?->format('Y-m-d h:i A') }}</span>
                        @if($submission->is_late)
                            <span class="text-[10px] text-rose-400 font-bold block mt-0.5">(تسليم متأخر بعد الموعد)</span>
                        @endif
                    </div>

                    <div class="bg-slate-900/90 p-3.5 rounded-xl border border-slate-700">
                        <span class="text-slate-400 block mb-1">الدرجة المحتسبة:</span>
                        <span class="font-bold text-sm {{ $submission->score !== null ? 'text-emerald-400' : 'text-amber-300' }}">
                            {{ $submission->score !== null ? "{$submission->score} / {$homework->total_marks} ({$submission->score_percentage}%)" : 'قيد التصحيح والاعتماد' }}
                        </span>
                    </div>

                    @if($submission->attachment)
                        <div class="bg-slate-900/90 p-3.5 rounded-xl border border-slate-700 col-span-full">
                            <span class="text-slate-400 block mb-1">الملف الذي قمت برفعه:</span>
                            <a href="{{ asset('storage/' . $submission->attachment) }}" target="_blank" class="text-sky-400 hover:text-sky-300 underline font-bold flex items-center gap-1 mt-1">
                                عرض الملف المرفوع
                            </a>
                        </div>
                    @endif
                </div>

                @if($submission->teacher_feedback)
                    <div class="p-4 bg-slate-900/90 border border-slate-700 rounded-xl text-xs space-y-1">
                        <span class="font-bold text-sky-300">ملاحظات وتوجيهات المعلم:</span>
                        <p class="text-slate-200 mt-1 leading-relaxed">{{ $submission->teacher_feedback }}</p>
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
                    <div class="bg-slate-800/90 border border-slate-700/80 rounded-2xl p-6 shadow-sm space-y-6">
                        <h3 class="font-heading font-bold text-base text-white border-b border-slate-700 pb-3">
                            أجب عن أسئلة الواجب:
                        </h3>

                        @foreach($homework->questions as $index => $question)
                            <div class="bg-slate-900/90 border border-slate-700 rounded-xl p-5 space-y-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="font-bold text-sm text-white leading-relaxed">
                                        <span class="text-sky-400 ml-1">س{{ $index + 1 }}:</span> {{ $question->question_text }}
                                    </div>
                                    <span class="px-2.5 py-1 bg-slate-800 border border-slate-700 text-sky-300 text-[11px] font-bold rounded-lg shrink-0">
                                        {{ $question->pivot->marks ?? 1 }} درجة
                                    </span>
                                </div>

                                @if($question->question_image)
                                    <div class="my-3 text-center">
                                        <img src="{{ asset('storage/' . $question->question_image) }}" alt="Question Image" class="max-h-60 rounded-lg border border-slate-700 inline-block object-contain">
                                    </div>
                                @endif

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-2">
                                    @foreach($question->options as $opt)
                                        <label class="flex items-center gap-3 p-3 bg-slate-800/90 hover:bg-slate-750 border border-slate-700 rounded-xl cursor-pointer transition">
                                            <input type="{{ $question->type === 'multiple_choice' ? 'checkbox' : 'radio' }}" 
                                                   name="answers[{{ $question->id }}]{{ $question->type === 'multiple_choice' ? '[]' : '' }}" 
                                                   value="{{ $opt['key'] }}"
                                                   class="w-4 h-4 text-sky-600 focus:ring-sky-500 border-slate-600 rounded bg-slate-900">
                                            <span class="text-xs font-bold text-slate-300">
                                                <span class="text-sky-400 font-mono ml-1">({{ $opt['key'] }})</span> {{ $opt['text'] }}
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
                    <div class="bg-slate-800/90 border border-slate-700/80 rounded-2xl p-6 shadow-sm space-y-4">
                        <h3 class="font-heading font-bold text-base text-white">
                            رفع ملف أو صورة الحل
                        </h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            قم بحل الواجب في كشكولك وتصويره أو إعداد ملف PDF ثم رفعه هنا لتصحيحه من قِبل المعلم:
                        </p>

                        <div class="border-2 border-dashed border-slate-700 hover:border-sky-500 rounded-xl p-6 text-center transition bg-slate-900/50">
                            <input type="file" name="attachment" id="attachment" accept=".pdf,image/*" class="w-full text-xs text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-700 file:text-white hover:file:bg-slate-600 cursor-pointer">
                            <p class="text-[11px] text-slate-500 mt-2">الحد الأقصى المسموح: 10 ميجابايت (ملفات PDF أو صور JPG / PNG)</p>
                        </div>
                    </div>
                @endif

                {{-- ملاحظات الطالب --}}
                <div class="bg-slate-800/90 border border-slate-700/80 rounded-2xl p-6 shadow-sm space-y-3">
                    <label for="notes" class="font-bold text-xs text-slate-300">ملاحظات أو أسئلة للمعلم (اختياري):</label>
                    <textarea name="notes" id="notes" rows="2" placeholder="اكتب أي ملاحظة أو استفسار تود إيصاله للمعلم مع هذا الواجب..." class="w-full bg-slate-900 border border-slate-700 rounded-xl p-3 text-xs text-white placeholder:text-slate-500 focus:outline-none focus:border-sky-500"></textarea>
                </div>

                <div class="text-left">
                    <button type="submit" class="px-8 py-3 bg-sky-700 hover:bg-sky-600 text-white font-bold rounded-xl text-sm transition shadow-sm">
                        {{ $submission ? 'إعادة تسليم وتحديث الواجب' : 'تسليم الواجب الآن' }}
                    </button>
                </div>
            </form>
        @else
            <div class="p-6 bg-slate-800/80 border border-slate-700 rounded-2xl text-center text-slate-400 text-xs font-bold">
                لا يمكن تسليم هذا الواجب حالياً (إما لأنه تم اعتماده وتصحيحه، أو أن موعد التسليم انتهى والمعلم أغلق إمكانية التسليم المتأخر).
            </div>
        @endif

    </main>

</body>
</html>
