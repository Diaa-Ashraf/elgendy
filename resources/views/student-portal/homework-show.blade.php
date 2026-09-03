<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>الواجب: {{ $homework->title }} — {{ $student->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style> 
        body { font-family: 'Cairo', sans-serif; } 
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen pb-16 selection:bg-amber-500 selection:text-white">

    {{-- الشريط العلوي --}}
    <header class="bg-slate-900/90 border-b border-slate-800/80 backdrop-blur-md sticky top-0 z-50 px-4 py-3.5">
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('tenant.student.dashboard', ['tenant' => $currentTenant->slug]) }}" class="w-10 h-10 bg-slate-800 hover:bg-slate-700 text-white rounded-xl flex items-center justify-center font-bold transition">
                    ←
                </a>
                <div>
                    <h1 class="font-black text-sm sm:text-base text-white">{{ $homework->title }}</h1>
                    <p class="text-xs text-amber-400 font-bold">
                        {{ $homework->subject?->name }} — الدرجة الكلية: {{ $homework->total_marks }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span class="px-3 py-1 bg-slate-800 border border-slate-700 rounded-xl text-xs font-bold text-slate-300">
                    ⏰ الموعد: {{ $homework->due_date->format('Y-m-d h:i A') }}
                </span>
            </div>
        </div>
    </header>

    <main class="max-w-4xl mx-auto p-4 sm:p-6 space-y-6">

        {{-- رسائل التنبيه والنجاح --}}
        @if(session('success'))
            <div class="p-4 bg-emerald-500/10 border border-emerald-500/30 rounded-2xl text-emerald-300 text-sm font-bold flex items-center gap-2">
                <span>✅</span> {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 bg-rose-500/10 border border-rose-500/30 rounded-2xl text-rose-300 text-sm font-bold">
                @foreach($errors->all() as $err)
                    <p>⚠️ {{ $err }}</p>
                @endforeach
            </div>
        @endif

        {{-- بطاقة تفاصيل الواجب والتعليمات --}}
        <div class="bg-slate-900/90 border border-slate-800/80 rounded-3xl p-6 shadow-sm space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-800 pb-4">
                <div>
                    <span class="text-xs text-slate-400 font-medium">نوع الواجب</span>
                    <div class="font-bold text-sm text-white mt-0.5">
                        {{ $homework->type === 'questions' ? 'أسئلة اختيارية أونلاين 📝' : ($homework->type === 'file_upload' ? 'رفع ملف إجابة 📎' : 'مختلط (أسئلة + ملف)') }}
                    </div>
                </div>

                @if($homework->attachment)
                    <div>
                        <a href="{{ asset('storage/' . $homework->attachment) }}" target="_blank" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                            <span>📥</span> تحميل ملف الواجب المرفق (PDF)
                        </a>
                    </div>
                @endif
            </div>

            @if($homework->description)
                <div class="text-sm text-slate-300 leading-relaxed prose prose-invert max-w-none">
                    {!! $homework->description !!}
                </div>
            @endif
        </div>

        {{-- حالة التسليم السابقة إن وُجدت --}}
        @if($submission)
            <div class="bg-gradient-to-r from-slate-900 to-indigo-950/60 border border-indigo-500/30 rounded-3xl p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="font-extrabold text-base text-white flex items-center gap-2">
                        <span>📊</span> حالة تسليمك للواجب
                    </h3>
                    <span class="px-3 py-1 rounded-xl text-xs font-bold {{ $submission->status === 'graded' ? 'bg-emerald-500/10 border border-emerald-500/30 text-emerald-400' : 'bg-amber-500/10 border border-amber-500/30 text-amber-400' }}">
                        {{ $submission->formatted_status }}
                    </span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-xs">
                    <div class="bg-slate-800/60 p-3 rounded-xl border border-slate-700/60">
                        <span class="text-slate-400">وقت التسليم:</span>
                        <div class="font-bold text-white mt-1">{{ $submission->submitted_at?->format('Y-m-d h:i A') }}</div>
                    </div>

                    <div class="bg-slate-800/60 p-3 rounded-xl border border-slate-700/60">
                        <span class="text-slate-400">الدرجة المحتسبة:</span>
                        <div class="font-black text-amber-400 text-sm mt-1">
                            {{ $submission->score !== null ? "{$submission->score} / {$homework->total_marks}" : 'قيد التصحيح ⏳' }}
                        </div>
                    </div>

                    @if($submission->attachment)
                        <div class="bg-slate-800/60 p-3 rounded-xl border border-slate-700/60">
                            <span class="text-slate-400">الملف الذي رفعته:</span>
                            <div class="mt-1">
                                <a href="{{ asset('storage/' . $submission->attachment) }}" target="_blank" class="text-sky-400 underline font-bold">
                                    عرض الملف المرفوع 📎
                                </a>
                            </div>
                        </div>
                    @endif
                </div>

                @if($submission->teacher_feedback)
                    <div class="p-4 bg-slate-800/90 border border-indigo-500/30 rounded-2xl text-xs space-y-1">
                        <span class="font-bold text-indigo-300">💬 ملاحظات وتوجيهات المعلم:</span>
                        <p class="text-slate-200 mt-1 leading-relaxed">{{ $submission->teacher_feedback }}</p>
                    </div>
                @endif
            </div>
        @endif

        {{-- نموذج تسليم الواجب --}}
        @php
            $canSubmit = $homework->canAcceptSubmissions() && (! $submission || ! $submission->isGraded());
        @endphp

        @if($canSubmit)
            <form action="{{ route('tenant.student.homeworks.submit', ['tenant' => $currentTenant->slug, 'id' => $homework->id]) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                {{-- إذا الواجب به أسئلة --}}
                @if(in_array($homework->type, ['questions', 'mixed']) && $homework->questions->isNotEmpty())
                    <div class="bg-slate-900/90 border border-slate-800/80 rounded-3xl p-6 shadow-sm space-y-6">
                        <h3 class="font-extrabold text-base text-white flex items-center gap-2 border-b border-slate-800 pb-3">
                            <span>📝</span> أجب عن الأسئلة التالية:
                        </h3>

                        @foreach($homework->questions as $index => $question)
                            <div class="bg-slate-800/50 border border-slate-700/60 rounded-2xl p-5 space-y-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="font-bold text-sm text-white">
                                        <span class="text-amber-400">س{{ $index + 1 }}:</span> {{ $question->question_text }}
                                    </div>
                                    <span class="px-2.5 py-1 bg-slate-900 border border-slate-700 text-slate-300 text-[11px] font-bold rounded-lg shrink-0">
                                        {{ $question->pivot->marks ?? 1 }} درجة
                                    </span>
                                </div>

                                @if($question->question_image)
                                    <div class="my-3">
                                        <img src="{{ asset('storage/' . $question->question_image) }}" alt="Question Image" class="max-h-60 rounded-xl border border-slate-700 object-contain">
                                    </div>
                                @endif

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-2">
                                    @foreach($question->options as $opt)
                                        <label class="flex items-center gap-3 p-3 bg-slate-900/80 hover:bg-slate-800/80 border border-slate-700/80 rounded-xl cursor-pointer transition">
                                            <input type="{{ $question->type === 'multiple_choice' ? 'checkbox' : 'radio' }}" 
                                                   name="answers[{{ $question->id }}]{{ $question->type === 'multiple_choice' ? '[]' : '' }}" 
                                                   value="{{ $opt['key'] }}"
                                                   class="w-4 h-4 text-amber-500 focus:ring-amber-500 focus:ring-offset-slate-900 border-slate-600 rounded bg-slate-800">
                                            <span class="text-xs font-bold text-slate-300">
                                                <span class="text-amber-400 font-mono ml-1">({{ $opt['key'] }})</span> {{ $opt['text'] }}
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
                    <div class="bg-slate-900/90 border border-slate-800/80 rounded-3xl p-6 shadow-sm space-y-4">
                        <h3 class="font-extrabold text-base text-white flex items-center gap-2">
                            <span>📎</span> رفع ملف الحل (PDF أو صورة)
                        </h3>
                        <p class="text-xs text-slate-400">
                            قم بحل الواجب في كشكولك وتصويره أو إعداد ملف PDF ثم رفعه هنا:
                        </p>

                        <div class="border-2 border-dashed border-slate-700 hover:border-amber-500/60 rounded-2xl p-6 text-center transition">
                            <input type="file" name="attachment" id="attachment" accept=".pdf,image/*" class="w-full text-xs text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-amber-500 file:text-slate-950 hover:file:bg-amber-400 cursor-pointer">
                            <p class="text-[11px] text-slate-500 mt-2">الحد الأقصى: 10 ميجابايت (PDF, PNG, JPG)</p>
                        </div>
                    </div>
                @endif

                {{-- ملاحظات الطالب --}}
                <div class="bg-slate-900/90 border border-slate-800/80 rounded-3xl p-6 shadow-sm space-y-3">
                    <label for="notes" class="font-bold text-xs text-slate-300">ملاحظاتك للمدرس (اختياري):</label>
                    <textarea name="notes" id="notes" rows="2" placeholder="اكتب أي ملاحظة أو استفسار حول الواجب..." class="w-full bg-slate-800/80 border border-slate-700 rounded-xl p-3 text-xs text-white placeholder:text-slate-500 focus:outline-none focus:border-amber-500"></textarea>
                </div>

                <div class="text-left">
                    <button type="submit" class="px-8 py-3.5 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-400 hover:to-orange-500 text-slate-950 font-black rounded-2xl text-sm transition shadow-lg shadow-amber-500/20">
                        تسليم الواجب الآن 🚀
                    </button>
                </div>
            </form>
        @else
            <div class="p-6 bg-slate-900/80 border border-slate-800 rounded-3xl text-center text-slate-400 text-xs font-bold">
                ⚠️ لا يمكن تسليم هذا الواجب حالياً (إما لأنه تم تصحيحه بالفعل أو انتهى موعد التسليم والمدرس أغلق الواجب).
            </div>
        @endif

    </main>

    <footer class="text-center text-xs font-medium text-slate-500 py-4">
        {{ $currentTenant->name }} © {{ date('Y') }} — منظومة التعليم الذكية
    </footer>

</body>
</html>
