<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>إنشاء حساب مدرس جديد — ابدأ تجربتك المجانية لمدة 7 أيام</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alexandria:wght@400;500;600;700;800;900&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'IBM Plex Sans Arabic', sans-serif; }
        h1, h2, h3, .font-heading { font-family: 'Alexandria', sans-serif; }
        .saas-pattern {
            background-color: #030712;
            background-image: 
                radial-gradient(at 100% 0%, rgba(56, 189, 248, 0.12) 0px, transparent 50%),
                radial-gradient(at 0% 100%, rgba(99, 102, 241, 0.12) 0px, transparent 50%);
        }
    </style>
</head>
<body class="saas-pattern text-slate-100 min-h-screen flex flex-col justify-between p-4 sm:p-6 selection:bg-sky-500 selection:text-white">

    {{-- رأس الصفحة --}}
    <div class="max-w-xl mx-auto w-full pt-2 flex justify-between items-center relative z-10">
        <a href="{{ route('platform.pricing') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-400 hover:text-white transition bg-slate-900/80 px-3.5 py-2 rounded-xl border border-slate-800">
            <span>←</span>
            <span>باقات الاشتراك</span>
        </a>
        <span class="text-xs text-sky-400 font-bold flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            تفعيل فوري — أسبوع مجاني 🎁
        </span>
    </div>

    {{-- بطاقة التسجيل --}}
    <div class="w-full max-w-xl mx-auto bg-slate-900/90 backdrop-blur-xl border border-slate-800 rounded-3xl p-6 sm:p-9 shadow-2xl relative z-10 my-8">
        <div class="text-center mb-8">
            <div class="w-14 h-14 bg-gradient-to-tr from-sky-500 to-indigo-600 text-white rounded-2xl flex items-center justify-center mx-auto text-2xl mb-3 shadow-lg shadow-sky-500/20">
                🚀
            </div>
            <h1 class="font-heading font-extrabold text-2xl sm:text-3xl text-white mb-2">امتلك منظومتك التعليمية الآن</h1>
            <p class="text-xs sm:text-sm text-slate-400 font-medium">
                سجل بياناتك وسيتم إنشاء لوحة إدارتك وبواباتك الخاصة فوراً في ثوانٍ معدودة.
            </p>
        </div>

        @if($errors->any())
            <div class="mb-6 p-4 bg-rose-500/10 border border-rose-500/30 text-rose-300 rounded-2xl text-xs font-bold space-y-1">
                @foreach($errors->all() as $error)
                    <div>⚠️ {{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('platform.register.submit') }}" method="POST" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">اسم المدرس / الأستاذ</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="أ. محمد أحمد" class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-xs sm:text-sm font-semibold text-white focus:outline-none focus:border-sky-500 transition placeholder:text-slate-600">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">اسم السنتر أو الأكاديمية</label>
                    <input type="text" name="center_name" value="{{ old('center_name') }}" required placeholder="سنتر الأوائل التعليمي" class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-xs sm:text-sm font-semibold text-white focus:outline-none focus:border-sky-500 transition placeholder:text-slate-600">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 mb-1.5">
                    الرابط المخصص لمنظومتك (Slug بالإنجليزية)
                </label>
                <div class="flex items-center rounded-xl bg-slate-950 border border-slate-800 px-3 focus-within:border-sky-500 transition">
                    <span class="text-xs text-slate-500 font-mono pl-2" dir="ltr">/t/</span>
                    <input type="text" name="slug" value="{{ old('slug') }}" required placeholder="mr-mohammed" class="w-full py-3 bg-transparent text-xs sm:text-sm font-bold font-mono text-sky-400 focus:outline-none placeholder:text-slate-600" dir="ltr">
                </div>
                <span class="text-[11px] text-slate-500 mt-1 block">سيكون رابط بوابتك: yourdomain.com/t/mr-mohammed</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">البريد الإلكتروني للوحة الإدارة</label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="teacher@example.com" class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-xs sm:text-sm font-semibold text-white focus:outline-none focus:border-sky-500 transition placeholder:text-slate-600" dir="ltr">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">رقم هاتف المدرس / الواتساب</label>
                    <input type="tel" name="phone" value="{{ old('phone') }}" required placeholder="01xxxxxxxxx" class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-xs sm:text-sm font-semibold text-white focus:outline-none focus:border-sky-500 transition placeholder:text-slate-600" dir="ltr">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">تعيين كلمة المرور</label>
                    <input type="password" name="password" required placeholder="••••••••" class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-xs sm:text-sm font-semibold text-white focus:outline-none focus:border-sky-500 transition placeholder:text-slate-600" dir="ltr">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">تأكيد كلمة المرور</label>
                    <input type="password" name="password_confirmation" required placeholder="••••••••" class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-xs sm:text-sm font-semibold text-white focus:outline-none focus:border-sky-500 transition placeholder:text-slate-600" dir="ltr">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 mb-1.5">اختر باقة البداية</label>
                <select name="plan_id" class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-xs sm:text-sm font-semibold text-white focus:outline-none focus:border-sky-500 transition">
                    @foreach($plans as $plan)
                        <option value="{{ $plan->id }}" {{ (isset($selectedPlanId) && $selectedPlanId == $plan->id) || $plan->is_popular ? 'selected' : '' }}>
                            باقة {{ $plan->name }} ({{ number_format($plan->price_monthly, 0) }} ج.م/شهر) — حتى {{ $plan->max_students }} طالب
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="pt-3">
                <button type="submit" class="w-full py-4 bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-400 hover:to-indigo-500 text-white rounded-2xl font-black text-sm transition shadow-xl shadow-sky-500/25 flex items-center justify-center gap-2">
                    <span>إنشاء الحساب وبدء التجربة المجانية</span>
                    <span>➔</span>
                </button>
            </div>

            <div class="text-center text-[11px] text-slate-500 pt-2">
                🔒 لا يلزم إدخال أي بطاقة دفع للتسجيل. 7 أيام تجربة كاملة المزايا مجاناً.
            </div>
        </form>
    </div>

    {{-- التذييل --}}
    <div class="text-center text-xs text-slate-500 py-3">
        جميع الحقوق محفوظة © {{ date('Y') }} — المنظومة السحابية
    </div>

</body>
</html>
