<!DOCTYPE html>
<html dir="rtl" lang="ar" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $settings['center_name'] }} — {{ $settings['teacher_name'] }}</title>
    
    @php
        $settingService = app(\App\Services\SettingService::class);
        $faviconUrl = $settingService->url('site_favicon');
        $logoUrl = $settingService->url('center_logo');
        $teacherImg = $settingService->url('teacher_image', asset('images/teacher_mohammed_elgandy.jpg'));
        $waNumber = preg_replace('/[^0-9]/', '', $settings['center_whatsapp'] ?: $settings['center_phone']);
    @endphp

    @if($faviconUrl)
        <link rel="icon" type="image/png" href="{{ $faviconUrl }}">
        <link rel="shortcut icon" type="image/x-icon" href="{{ $faviconUrl }}">
        <link rel="apple-touch-icon" href="{{ $faviconUrl }}">
    @endif

    {{-- Tailwind CSS & Google Fonts --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alexandria:wght@300;400;500;600;700;800;900&family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    {{-- AOS Animation Library --}}
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"IBM Plex Sans Arabic"', 'sans-serif'],
                        heading: ['"Alexandria"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            teal: '#0D3B4C',
                            'teal-dark': '#082531',
                            'teal-deep': '#061B24',
                            coral: '#FF5E36',
                            'coral-hover': '#F2481F',
                            mint: '#10B981',
                            amber: '#F59E0B',
                            bg: '#F8FAFC',
                            slate: '#0F172A',
                            muted: '#64748B'
                        },
                        navy: {
                            800: '#0D3B4C',
                            850: '#092935',
                            900: '#082531',
                            950: '#061B24',
                        },
                        academic: {
                            blue: '#FF5E36',
                            dark: '#0D3B4C',
                            emerald: '#10B981',
                            amber: '#F59E0B',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'IBM Plex Sans Arabic', sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
        }
        h1, h2, h3, h4, h5, h6, .font-heading {
            font-family: 'Alexandria', sans-serif;
        }
        .academic-grid-pattern {
            background-image: radial-gradient(rgba(13, 59, 76, 0.08) 1.2px, transparent 1.2px);
            background-size: 24px 24px;
        }
        .academic-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.35rem 0.85rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: -0.01em;
        }
        .clean-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1.5rem;
            box-shadow: 0 1px 3px 0 rgba(15, 23, 42, 0.04);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .clean-card:hover {
            transform: translateY(-3px);
            border-color: #cbd5e1;
            box-shadow: 0 12px 24px -6px rgba(13, 59, 76, 0.09);
        }
        .hero-banner {
            background: linear-gradient(135deg, #082531 0%, #0D3B4C 55%, #144E64 100%);
        }
        .teacher-portrait-frame {
            position: relative;
        }
        .teacher-portrait-frame::after {
            content: '';
            position: absolute;
            inset: -6px;
            border-radius: 1.75rem;
            border: 2px solid rgba(255, 94, 54, 0.4);
            pointer-events-none;
            z-index: 0;
        }
        @media (min-width: 640px) {
            .teacher-portrait-frame::after {
                inset: -8px;
                border-radius: 2rem;
            }
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 7px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>
<body class="antialiased selection:bg-brand-coral selection:text-white overflow-x-hidden">

    {{-- ─── HEADER / NAVIGATION (Responsive Navbar with Mobile Drawer) ─── --}}
    <header class="fixed top-0 inset-x-0 z-50 bg-white/90 backdrop-blur-md border-b border-slate-200/80 transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-18 sm:h-20 flex items-center justify-between">
            
            {{-- Brand / Logo --}}
            <a href="#" class="flex items-center gap-2.5 sm:gap-3.5 group">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $settings['center_name'] }}" class="w-9 h-9 sm:w-11 sm:h-11 object-contain rounded-xl border border-slate-200 p-0.5 bg-white shadow-sm group-hover:scale-105 transition shrink-0">
                @else
                    <div class="w-9 h-9 sm:w-11 sm:h-11 bg-brand-teal text-white rounded-xl flex items-center justify-center font-black text-base sm:text-lg shadow-sm border border-brand-teal-dark group-hover:bg-brand-coral transition shrink-0">
                        📖
                    </div>
                @endif
                <div>
                    <span class="font-heading font-extrabold text-sm sm:text-base lg:text-lg text-brand-slate block group-hover:text-brand-coral transition leading-tight line-clamp-1">
                        {{ $settings['center_name'] }}
                    </span>
                    <span class="text-[10px] sm:text-xs text-brand-muted font-semibold flex items-center gap-1 mt-0.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-brand-mint inline-block shrink-0"></span>
                        العام الأكاديمي {{ $settings['academic_year'] }}
                    </span>
                </div>
            </a>

            {{-- Desktop Main Menu --}}
            <nav class="hidden lg:flex items-center gap-7 text-sm font-bold text-brand-muted">
                <a href="#about-teacher" class="hover:text-brand-coral transition py-1 text-brand-slate font-extrabold flex items-center gap-1.5">
                    <span>عن المعلم</span>
                </a>
                <a href="#features" class="hover:text-brand-coral transition py-1">المميزات</a>
                <a href="#stages" class="hover:text-brand-coral transition py-1">المراحل الدراسية</a>
                <a href="#groups" class="hover:text-brand-coral transition py-1">المجموعات</a>
                <a href="#enroll" class="hover:text-brand-coral transition py-1">استمارة التقديم</a>
            </nav>

            {{-- Action Buttons & Mobile Hamburger --}}
            <div class="flex items-center gap-2 sm:gap-2.5">
                <a href="{{ route('parent.login') }}" class="px-3 sm:px-4 py-2 bg-brand-teal/10 hover:bg-brand-teal/15 border border-brand-teal/20 rounded-xl text-xs sm:text-sm font-bold text-brand-teal transition flex items-center gap-1.5 shadow-sm">
                    <span>👨‍👩‍👦</span>
                    <span class="hidden xs:inline sm:inline">بوابة ولي الأمر</span>
                    <span class="inline xs:hidden sm:hidden">البوابة</span>
                </a>
                <a href="/admin" class="hidden sm:flex px-3.5 sm:px-4 py-2 bg-brand-teal hover:bg-brand-teal-dark border border-brand-teal-dark rounded-xl text-xs sm:text-sm font-bold text-white transition items-center gap-1.5 shadow-md shadow-brand-teal/20">
                    <span>لوحة التحكم</span>
                    <span class="text-xs opacity-75">➔</span>
                </a>

                {{-- Hamburger Button for Mobile --}}
                <button id="mobileMenuBtn" aria-label="القائمة الرئيسية" class="lg:hidden p-2 rounded-xl text-slate-700 hover:bg-slate-100 border border-slate-200 focus:outline-none transition">
                    <svg id="hamburgerIcon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                    <svg id="closeIcon" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mobile Drawer Menu --}}
        <div id="mobileMenu" class="hidden lg:hidden border-t border-slate-200 bg-white px-4 pt-3 pb-5 space-y-2 shadow-xl transition-all">
            <a href="#about-teacher" onclick="closeMobileMenu()" class="block px-3 py-2.5 rounded-xl font-bold text-slate-800 hover:bg-slate-100 transition text-sm">
                 عن {{ $settings['teacher_name'] }}
            </a>
            <a href="#features" onclick="closeMobileMenu()" class="block px-3 py-2.5 rounded-xl font-bold text-slate-700 hover:bg-slate-100 transition text-sm">
                مميزات المنظومة التعليمية
            </a>
            <a href="#stages" onclick="closeMobileMenu()" class="block px-3 py-2.5 rounded-xl font-bold text-slate-700 hover:bg-slate-100 transition text-sm">
                المراحل والصفوف الدراسية
            </a>
            <a href="#groups" onclick="closeMobileMenu()" class="block px-3 py-2.5 rounded-xl font-bold text-slate-700 hover:bg-slate-100 transition text-sm">
                 جدول مواعيد المجموعات
            </a>
            <a href="#enroll" onclick="closeMobileMenu()" class="block px-3 py-2.5 rounded-xl font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 transition text-sm text-center">
                تقديم طلب وتسجيل جديد
            </a>
            <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-2">
                <a href="{{ route('parent.login') }}" class="flex-1 py-2 text-center bg-slate-100 rounded-xl text-xs font-bold text-slate-800">
                    بوابة ولي الأمر
                </a>
                <a href="/admin" class="flex-1 py-2 text-center bg-slate-900 rounded-xl text-xs font-bold text-white">
                    لوحة التحكم
                </a>
            </div>
        </div>
    </header>

    {{-- ─── HERO SECTION (Fully Responsive Two-Column Layout) ─── --}}
    <section class="relative pt-28 pb-14 sm:pt-36 sm:pb-20 md:pt-40 md:pb-24 hero-banner text-white overflow-hidden">
        <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#ffffff_1px,transparent_1px)] [background-size:20px_20px] sm:[background-size:24px_24px] pointer-events-none"></div>
        <div class="absolute top-0 right-0 w-80 sm:w-96 h-80 sm:h-96 bg-brand-coral/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-80 sm:w-96 h-80 sm:h-96 bg-brand-mint/15 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 sm:gap-12 items-center">
                
                {{-- Text Content (Right) --}}
                <div class="lg:col-span-7 text-center lg:text-right">
                    <div data-aos="fade-down" class="inline-flex items-center gap-2 px-3.5 py-1.5 bg-white/10 border border-white/20 rounded-full text-white font-bold text-xs sm:text-sm mb-4 sm:mb-6 backdrop-blur-md shadow-sm">
                        <span class="w-2 h-2 rounded-full bg-brand-coral animate-pulse shrink-0"></span>
                        <span>{{ $settings['hero_badge_text'] }}</span>
                    </div>

                    <h1 data-aos="fade-up" data-aos-delay="100" class="font-heading text-2xl sm:text-4xl md:text-5xl lg:text-6xl font-black text-white leading-tight mb-4 sm:mb-6">
                        {{ $settings['hero_title_prefix'] }} <br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#FFA285] via-brand-coral to-[#FF784B]">{{ $settings['teacher_name'] }}</span>
                    </h1>

                    <p data-aos="fade-up" data-aos-delay="200" class="text-slate-200 text-sm sm:text-base md:text-lg font-normal leading-relaxed max-w-2xl mx-auto lg:mx-0 mb-6 sm:mb-8">
                        {{ $settings['hero_description'] }}
                    </p>

                    <div data-aos="fade-up" data-aos-delay="300" class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-3 max-w-md mx-auto lg:mx-0">
                        <a href="#enroll" class="w-full sm:w-auto px-8 py-4 bg-gradient-to-r from-brand-coral to-[#FF7552] hover:from-brand-coral-hover hover:to-brand-coral text-white font-heading font-bold rounded-2xl text-xs sm:text-sm shadow-lg shadow-brand-coral/30 hover:shadow-xl hover:shadow-brand-coral/40 transition transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                            <span> حجز مقعد دراسي جديد</span>
                            <span>←</span>
                        </a>
                        <a href="#about-teacher" class="w-full sm:w-auto px-6 py-4 bg-white/10 hover:bg-white/20 border border-white/25 text-white font-heading font-bold rounded-2xl text-xs sm:text-sm transition backdrop-blur-md flex items-center justify-center gap-2">
                            <span> سيرة ومنهجية المعلم</span>
                        </a>
                    </div>

                    {{-- Quick Trust Signals --}}
                    <div data-aos="fade-up" data-aos-delay="400" class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 mt-8 pt-6 sm:pt-8 border-t border-white/10 text-xs sm:text-sm font-semibold text-slate-300 text-right">
                        <div class="flex items-center gap-2 justify-center lg:justify-start">
                            <span class="text-brand-mint font-bold text-base">✓</span>
                            <span>خبرة {{ $settings['teacher_experience_years'] }} عاماً</span>
                        </div>
                        <div class="flex items-center gap-2 justify-center lg:justify-start">
                            <span class="text-brand-coral font-bold text-base">✓</span>
                            <span>{{ $settings['trust_stat_1'] }}</span>
                        </div>
                        <div class="flex items-center gap-2 justify-center lg:justify-start">
                            <span class="text-amber-400 font-bold text-base">✓</span>
                            <span>{{ $settings['trust_stat_2'] }}</span>
                        </div>
                    </div>
                </div>

                {{-- Teacher Hero Card / Portrait (Left) --}}
                <div class="lg:col-span-5 mt-4 lg:mt-0" data-aos="fade-left" data-aos-delay="200">
                    <div class="relative max-w-sm sm:max-w-md mx-auto">
                        <div class="teacher-portrait-frame relative z-10 rounded-2xl sm:rounded-3xl overflow-hidden bg-slate-900 border-2 border-white/20 shadow-2xl">
                            <img src="{{ $teacherImg }}" alt="{{ $settings['teacher_name'] }}" class="w-full h-[320px] sm:h-[390px] lg:h-[430px] object-cover object-top filter contrast-105">
                            
                            {{-- Overlay Card at Bottom of Portrait --}}
                            <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-slate-950 via-slate-950/80 to-transparent p-4 sm:p-6 text-right">
                                <span class="inline-block px-2.5 py-0.5 bg-sky-600/90 text-white rounded-full text-[10px] sm:text-xs font-bold mb-1 sm:mb-2">
                                    {{ $settings['teacher_title'] }}
                                </span>
                                <h3 class="font-heading font-extrabold text-lg sm:text-2xl text-white">{{ $settings['teacher_name'] }}</h3>
                                @if(!empty($settings['teacher_quote']))
                                    <p class="text-[11px] sm:text-xs text-slate-300 mt-1 font-medium italic line-clamp-2">
                                        "{{ $settings['teacher_quote'] }}"
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{--  ACADEMIC HIGHLIGHTS (4 Responsive Pillars) --}}
            <div data-aos="fade-up" data-aos-delay="400" class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6 mt-12 sm:mt-16 max-w-5xl mx-auto">
                <div class="bg-white/5 border border-white/10 p-3.5 sm:p-5 rounded-xl sm:rounded-2xl text-center backdrop-blur-sm">
                    <div class="text-xl sm:text-2xl mb-1 sm:mb-2"></div>
                    <span class="block text-base sm:text-xl lg:text-2xl font-heading font-black text-white mb-0.5 sm:mb-1">{{ $settings['hero_pillar_1_title'] }}</span>
                    <span class="text-[11px] sm:text-xs text-slate-300 font-medium">{{ $settings['hero_pillar_1_desc'] }}</span>
                </div>
                <div class="bg-white/5 border border-white/10 p-3.5 sm:p-5 rounded-xl sm:rounded-2xl text-center backdrop-blur-sm">
                    <div class="text-xl sm:text-2xl mb-1 sm:mb-2"></div>
                    <span class="block text-base sm:text-xl lg:text-2xl font-heading font-black text-emerald-300 mb-0.5 sm:mb-1">{{ $settings['hero_pillar_2_title'] }}</span>
                    <span class="text-[11px] sm:text-xs text-slate-300 font-medium">{{ $settings['hero_pillar_2_desc'] }}</span>
                </div>
                <div class="bg-white/5 border border-white/10 p-3.5 sm:p-5 rounded-xl sm:rounded-2xl text-center backdrop-blur-sm">
                    <div class="text-xl sm:text-2xl mb-1 sm:mb-2"></div>
                    <span class="block text-base sm:text-xl lg:text-2xl font-heading font-black text-sky-300 mb-0.5 sm:mb-1">{{ $settings['hero_pillar_3_title'] }}</span>
                    <span class="text-[11px] sm:text-xs text-slate-300 font-medium">{{ $settings['hero_pillar_3_desc'] }}</span>
                </div>
                <div class="bg-white/5 border border-white/10 p-3.5 sm:p-5 rounded-xl sm:rounded-2xl text-center backdrop-blur-sm">
                    <div class="text-xl sm:text-2xl mb-1 sm:mb-2"></div>
                    <span class="block text-base sm:text-xl lg:text-2xl font-heading font-black text-amber-300 mb-0.5 sm:mb-1">{{ $settings['hero_pillar_4_title'] }}</span>
                    <span class="text-[11px] sm:text-xs text-slate-300 font-medium">{{ $settings['hero_pillar_4_desc'] }}</span>
                </div>
            </div>
        </div>
    </section>

    {{-- ─── MASTER EDUCATOR SHOWCASE SECTION (Comprehensive Teacher Bio) ─── --}}
    <section id="about-teacher" class="py-14 sm:py-20 lg:py-24 bg-white border-b border-slate-200 relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            
            <div class="text-center max-w-2xl mx-auto mb-10 sm:mb-16" data-aos="fade-up">
                <span class="academic-badge bg-sky-100 text-sky-800 border border-sky-200 mb-2 sm:mb-3">السيرة المهنية والأكاديمية</span>
                <h2 class="font-heading text-xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 leading-tight">
                    عن {{ $settings['teacher_name'] }}
                </h2>
                <p class="text-slate-600 text-xs sm:text-sm md:text-base mt-1.5">
                    {{ $settings['teacher_title'] }} — <span class="font-bold text-sky-700">{{ $settings['teacher_subject'] }}</span>
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 sm:gap-12 items-center">
                
                {{-- Teacher Photo with Credentials --}}
                <div class="lg:col-span-5 max-w-sm sm:max-w-md mx-auto w-full" data-aos="fade-right">
                    <div class="relative">
                        <div class="rounded-2xl sm:rounded-3xl overflow-hidden border border-slate-200 shadow-lg bg-slate-100">
                            <img src="{{ $teacherImg }}" alt="{{ $settings['teacher_name'] }}" class="w-full h-[340px] sm:h-[420px] lg:h-[480px] object-cover object-top">
                        </div>

                        {{-- Stats Overhang --}}
                        <div class="grid grid-cols-2 gap-2.5 sm:gap-3 mt-3 sm:mt-4">
                            <div class="bg-slate-900 text-white p-3 sm:p-4 rounded-xl sm:rounded-2xl text-center">
                                <span class="block font-heading font-black text-xl sm:text-2xl text-sky-400">{{ $settings['teacher_experience_years'] }} عاماً</span>
                                <span class="text-[10px] sm:text-xs text-slate-300">من الخبرة والتدريس المتخصص</span>
                            </div>
                            <div class="bg-slate-900 text-white p-3 sm:p-4 rounded-xl sm:rounded-2xl text-center">
                                <span class="block font-heading font-black text-xl sm:text-2xl text-emerald-400">{{ $settings['teacher_students_count'] }}</span>
                                <span class="text-[10px] sm:text-xs text-slate-300">طالب تم تأهيلهم للتفوق</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Biography & Teaching Methodology --}}
                <div class="lg:col-span-7 space-y-4 sm:space-y-6 text-right" data-aos="fade-left">
                    <div class="space-y-2 sm:space-y-3">
                        <h3 class="font-heading font-bold text-lg sm:text-2xl lg:text-3xl text-slate-900 leading-snug">
                            {{ $settings['teacher_bio_heading'] }}
                        </h3>
                        <p class="text-slate-600 text-xs sm:text-base leading-relaxed">
                            {{ $settings['teacher_bio'] }}
                        </p>
                    </div>

                    {{-- Methodology 4 Points --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 pt-1">
                        <div class="p-4 rounded-2xl border border-slate-200 bg-white shadow-sm">
                            <div class="w-9 h-9 bg-brand-teal/10 text-brand-teal rounded-xl flex items-center justify-center font-black text-sm mb-2 border border-brand-teal/15">
                                1
                            </div>
                            <h4 class="font-heading font-bold text-brand-slate text-xs sm:text-sm mb-1">{{ $settings['methodology_1_title'] }}</h4>
                            <p class="text-[11px] sm:text-xs text-brand-muted leading-relaxed">{{ $settings['methodology_1_desc'] }}</p>
                        </div>

                        <div class="p-4 rounded-2xl border border-slate-200 bg-white shadow-sm">
                            <div class="w-9 h-9 bg-brand-coral/10 text-brand-coral rounded-xl flex items-center justify-center font-black text-sm mb-2 border border-brand-coral/15">
                                2
                            </div>
                            <h4 class="font-heading font-bold text-brand-slate text-xs sm:text-sm mb-1">{{ $settings['methodology_2_title'] }}</h4>
                            <p class="text-[11px] sm:text-xs text-brand-muted leading-relaxed">{{ $settings['methodology_2_desc'] }}</p>
                        </div>

                        <div class="p-4 rounded-2xl border border-slate-200 bg-white shadow-sm">
                            <div class="w-9 h-9 bg-amber-50 text-amber-800 rounded-xl flex items-center justify-center font-black text-sm mb-2 border border-amber-200">
                                3
                            </div>
                            <h4 class="font-heading font-bold text-brand-slate text-xs sm:text-sm mb-1">{{ $settings['methodology_3_title'] }}</h4>
                            <p class="text-[11px] sm:text-xs text-brand-muted leading-relaxed">{{ $settings['methodology_3_desc'] }}</p>
                        </div>

                        <div class="p-4 rounded-2xl border border-slate-200 bg-white shadow-sm">
                            <div class="w-9 h-9 bg-emerald-50 text-emerald-800 rounded-xl flex items-center justify-center font-black text-sm mb-2 border border-emerald-200">
                                4
                            </div>
                            <h4 class="font-heading font-bold text-brand-slate text-xs sm:text-sm mb-1">{{ $settings['methodology_4_title'] }}</h4>
                            <p class="text-[11px] sm:text-xs text-brand-muted leading-relaxed">{{ $settings['methodology_4_desc'] }}</p>
                        </div>
                    </div>

                    {{-- Quote Banner --}}
                    @if(!empty($settings['teacher_quote']))
                        <div class="p-5 rounded-2xl bg-gradient-to-br from-brand-teal to-brand-teal-dark text-white border border-brand-teal-dark shadow-md flex items-start gap-4">
                            <span class="text-3xl text-brand-coral font-serif leading-none">“</span>
                            <div>
                                <p class="text-xs sm:text-sm font-medium text-slate-200 leading-relaxed">
                                    {{ $settings['teacher_quote'] }}
                                </p>
                                <span class="block text-xs text-brand-coral font-bold mt-2">— {{ $settings['teacher_name'] }}</span>
                            </div>
                        </div>
                    @endif

                    {{-- Contact Triggers --}}
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 pt-2">
                        <a href="#enroll" class="px-7 py-3.5 bg-gradient-to-r from-brand-coral to-[#FF7552] hover:from-brand-coral-hover hover:to-brand-coral text-white font-heading font-bold rounded-2xl text-xs sm:text-sm shadow-md shadow-brand-coral/25 transition text-center">
                            انضم لطلاب الأستاذ الآن ➔
                        </a>
                        @if($waNumber)
                            <a href="https://wa.me/{{ $waNumber }}" target="_blank" class="px-5 py-3.5 bg-white hover:bg-slate-50 text-brand-slate border border-slate-200 font-bold rounded-2xl text-xs sm:text-sm transition flex items-center justify-center gap-2 shadow-sm">
                                <span>💬</span>
                                <span>استفسار مباشر عبر واتساب</span>
                            </a>
                        @endif
                    </div>

                </div>

            </div>
        </div>
    </section>

    {{-- ─── PILLARS & FEATURES ─── --}}
    <section id="features" class="py-14 sm:py-20 academic-grid-pattern border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-10 sm:mb-14" data-aos="fade-up">
                <span class="academic-badge bg-sky-100 text-sky-800 border border-sky-200 mb-2 sm:mb-3">ركائز المنظومة</span>
                <h2 class="font-heading text-xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 leading-tight">
                    لماذا يثق بنا مئات الطلاب وأولياء الأمور؟
                </h2>
                <p class="text-slate-600 text-xs sm:text-sm md:text-base mt-2">
                    نهتم ببناء الأساس العلمي والتطبيقي للطالب مع معايير متابعة صارمة تضمن الالتزام والتفوق.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 lg:gap-8">
                {{-- Feature 1 --}}
                <div class="clean-card p-6 sm:p-8 rounded-2xl flex flex-col justify-between" data-aos="fade-up" data-aos-delay="100">
                    <div>
                        <div class="w-11 h-11 sm:w-12 sm:h-12 bg-sky-50 text-sky-700 rounded-xl flex items-center justify-center text-xl sm:text-2xl mb-4 sm:mb-5 border border-sky-100 font-bold">
                            📖
                        </div>
                        <h3 class="font-heading font-bold text-base sm:text-lg text-slate-900 mb-2">{{ $settings['feature_1_title'] }}</h3>
                        <p class="text-slate-600 text-xs sm:text-sm leading-relaxed">
                            {{ $settings['feature_1_desc'] }}
                        </p>
                    </div>
                    <div class="mt-5 pt-3 border-t border-slate-100 flex items-center gap-2 text-xs font-bold text-sky-700">
                        <span>{{ $settings['feature_1_tag'] }}</span>
                    </div>
                </div>

                {{-- Feature 2 --}}
                <div class="clean-card p-6 sm:p-8 rounded-2xl flex flex-col justify-between" data-aos="fade-up" data-aos-delay="200">
                    <div>
                        <div class="w-11 h-11 sm:w-12 sm:h-12 bg-emerald-50 text-emerald-700 rounded-xl flex items-center justify-center text-xl sm:text-2xl mb-4 sm:mb-5 border border-emerald-100 font-bold">
                            
                        </div>
                        <h3 class="font-heading font-bold text-base sm:text-lg text-slate-900 mb-2">{{ $settings['feature_2_title'] }}</h3>
                        <p class="text-slate-600 text-xs sm:text-sm leading-relaxed">
                            {{ $settings['feature_2_desc'] }}
                        </p>
                    </div>
                    <div class="mt-5 pt-3 border-t border-slate-100 flex items-center gap-2 text-xs font-bold text-emerald-700">
                        <span>{{ $settings['feature_2_tag'] }}</span>
                    </div>
                </div>

                {{-- Feature 3 --}}
                <div class="clean-card p-6 sm:p-8 rounded-2xl flex flex-col justify-between md:col-span-2 lg:col-span-1" data-aos="fade-up" data-aos-delay="300">
                    <div>
                        <div class="w-11 h-11 sm:w-12 sm:h-12 bg-amber-50 text-amber-700 rounded-xl flex items-center justify-center text-xl sm:text-2xl mb-4 sm:mb-5 border border-amber-100 font-bold">
                            
                        </div>
                        <h3 class="font-heading font-bold text-base sm:text-lg text-slate-900 mb-2">{{ $settings['feature_3_title'] }}</h3>
                        <p class="text-slate-600 text-xs sm:text-sm leading-relaxed">
                            {{ $settings['feature_3_desc'] }}
                        </p>
                    </div>
                    <div class="mt-5 pt-3 border-t border-slate-100 flex items-center gap-2 text-xs font-bold text-amber-700">
                        <span>{{ $settings['feature_3_tag'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ─── EDUCATIONAL STAGES ─── --}}
    <section id="stages" class="py-14 sm:py-20 bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-10 sm:mb-14" data-aos="fade-up">
                <span class="academic-badge bg-brand-teal/10 text-brand-teal border border-brand-teal/20 mb-2 sm:mb-3">الصفوف والمراحل</span>
                <h2 class="font-heading text-xl sm:text-3xl lg:text-4xl font-black text-brand-slate">المراحل الدراسية المتاحة</h2>
                <p class="text-brand-muted text-xs sm:text-sm md:text-base mt-2">اختر المرحلة الدراسية المناسبة للاطلاع على المجموعات وحجز المقعد.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                @forelse($stages as $index => $stage)
                    <div class="clean-card p-6 sm:p-7 rounded-3xl flex flex-col justify-between" data-aos="fade-up" data-aos-delay="{{ ($index + 1) * 100 }}">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 bg-brand-teal/10 text-brand-teal rounded-2xl flex items-center justify-center font-black text-xl border border-brand-teal/15">
                                    📚
                                </div>
                                <span class="text-xs font-bold px-3 py-1 rounded-full bg-brand-coral/10 text-brand-coral border border-brand-coral/20">
                                    {{ $stage->students_count }} طالب مقيد
                                </span>
                            </div>
                            <h3 class="font-heading font-black text-lg sm:text-xl text-brand-slate mb-2">{{ $stage->name }}</h3>
                            <p class="text-brand-muted text-xs leading-relaxed mb-6">
                                برنامج دراسي شامل يتضمن شرح المنهج، حل الواجبات، واختبارات أسبوعية لقياس التقدم.
                            </p>
                        </div>

                        <a href="#enroll" onclick="selectStage('{{ $stage->id }}')" class="w-full py-3 bg-slate-50 hover:bg-brand-teal text-brand-slate hover:text-white font-bold rounded-2xl text-xs transition-all duration-300 text-center block border border-slate-200 hover:border-brand-teal shadow-sm">
                            تسجيل طلب في هذه المرحلة ➔
                        </a>
                    </div>
                @empty
                    <div class="col-span-3 text-center py-10 text-slate-400 text-sm">
                        لا توجد مراحل مسجلة حالياً
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- ─── GROUPS TIMETABLE ─── --}}
    <section id="groups" class="py-14 sm:py-20 academic-grid-pattern border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-10 sm:mb-14" data-aos="fade-up">
                <span class="academic-badge bg-brand-coral/10 text-brand-coral border border-brand-coral/20 mb-2 sm:mb-3">الجداول والمواعيد</span>
                <h2 class="font-heading text-xl sm:text-3xl lg:text-4xl font-black text-brand-slate">مواعيد المجموعات الدراسية</h2>
                <p class="text-brand-muted text-xs sm:text-sm md:text-base mt-2">مواعيد منظمة بأعداد محددة في القاعة لضمان راحة وتركيز كل طالب.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                @forelse($groups as $index => $group)
                    <div class="clean-card p-6 sm:p-7 rounded-3xl flex flex-col justify-between" data-aos="fade-up" data-aos-delay="{{ ($index + 1) * 100 }}">
                        <div class="space-y-3 sm:space-y-4">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-[11px] sm:text-xs font-bold px-3 py-1 rounded-xl bg-slate-100 text-brand-slate border border-slate-200">
                                    {{ $group->educationalStage?->name }}
                                </span>
                                <span class="text-[11px] sm:text-xs font-bold text-emerald-800 bg-emerald-50 px-3 py-1 rounded-xl border border-emerald-200 whitespace-nowrap">
                                    {{ number_format($group->price_per_month) }} {{ $settings['currency_symbol'] }} / شهرياً
                                </span>
                            </div>

                            <h3 class="font-heading font-black text-base sm:text-lg text-brand-slate pt-1">{{ $group->name }}</h3>
                            <p class="text-xs font-semibold text-brand-muted">المادة: <span class="font-bold text-brand-teal">{{ $group->subject?->name ?? $settings['teacher_subject'] }}</span></p>

                            <div class="space-y-2 pt-2 border-t border-slate-100">
                                <span class="text-xs font-bold text-slate-600 block"> المواعيد الأسبوعية:</span>
                                @forelse($group->schedules as $sched)
                                    <div class="flex items-center justify-between text-xs font-medium text-brand-slate bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                                        <span class="font-bold">
                                            @switch($sched->day_of_week)
                                                @case('sat') السبت @break
                                                @case('sun') الأحد @break
                                                @case('mon') الإثنين @break
                                                @case('tue') الثلاثاء @break
                                                @case('wed') الأربعاء @break
                                                @case('thu') الخميس @break
                                                @case('fri') الجمعة @break
                                                @default {{ $sched->day_of_week }}
                                            @endswitch
                                        </span>
                                        <span class="font-mono text-brand-teal font-black" dir="ltr">{{ \Carbon\Carbon::parse($sched->start_time)->format('h:i A') }}</span>
                                    </div>
                                @empty
                                    <span class="text-xs text-slate-400 italic block">يتم الإعلان عن المواعيد قريباً</span>
                                @endforelse
                            </div>
                        </div>

                        <div class="pt-5">
                            <a href="#enroll" onclick="selectGroup('{{ $group->stage_id }}', '{{ $group->id }}')" class="w-full py-3 bg-brand-teal hover:bg-brand-teal-dark text-white font-bold rounded-2xl text-xs transition shadow-md shadow-brand-teal/20 text-center block">
                                اختيار هذه المجموعة للتسجيل ➔
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 text-center py-10 text-slate-400 text-sm">
                        لا توجد مجموعات نشطة حالياً
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- ─── ENROLLMENT APPLICATION FORM (Touch-Friendly Responsive Form) ─── --}}
    <section id="enroll" class="py-14 sm:py-20 bg-white border-b border-slate-200">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white border border-slate-200 p-6 sm:p-9 lg:p-11 rounded-3xl sm:rounded-[2.5rem] shadow-xl shadow-brand-teal/5" data-aos="fade-up">
                
                <div class="text-center max-w-xl mx-auto mb-7 sm:mb-9">
                    <span class="academic-badge bg-brand-coral/10 text-brand-coral border border-brand-coral/20 mb-2">استمارة التقديم</span>
                    <h2 class="font-heading text-xl sm:text-3xl font-black text-brand-slate mb-1.5">طلب الالتحاق وحجز المقعد</h2>
                    <p class="text-brand-muted text-xs sm:text-sm leading-relaxed">سجل بيانات الطالب وسنتواصل معك هاتفياً لتأكيد المقعد وتحديد موعد الحضور.</p>
                </div>

                <form id="enrollmentForm" class="space-y-4 sm:space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-brand-slate mb-1.5">اسم الطالب (ثلاثي أو رباعي) *</label>
                            <input type="text" name="name" required placeholder="مثال: أحمد محمد علي" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs sm:text-sm font-semibold text-brand-slate focus:outline-none focus:border-brand-teal focus:bg-white focus:ring-4 focus:ring-brand-teal/10 transition">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-brand-slate mb-1.5">النوع *</label>
                            <select name="gender" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs sm:text-sm font-semibold text-brand-slate focus:outline-none focus:border-brand-teal focus:bg-white focus:ring-4 focus:ring-brand-teal/10 transition">
                                <option value="male">ذكر (طالب)</option>
                                <option value="female">أنثى (طالبة)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-brand-slate mb-1.5">المرحلة الدراسية *</label>
                            <select id="stageSelect" name="stage_id" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs sm:text-sm font-semibold text-brand-slate focus:outline-none focus:border-brand-teal focus:bg-white focus:ring-4 focus:ring-brand-teal/10 transition">
                                <option value="">-- اختر المرحلة الدراسية --</option>
                                @foreach($stages as $stage)
                                    <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-brand-slate mb-1.5">المجموعة المرغوبة (اختياري)</label>
                            <select id="groupSelect" name="group_id" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs sm:text-sm font-semibold text-brand-slate focus:outline-none focus:border-brand-teal focus:bg-white focus:ring-4 focus:ring-brand-teal/10 transition">
                                <option value="">-- اختر المجموعة بعد تحديد المرحلة --</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-brand-slate mb-1.5">رقم هاتف ولي الأمر *</label>
                            <input type="tel" name="parent_phone" required placeholder="01xxxxxxxxx" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs sm:text-sm font-semibold text-brand-slate focus:outline-none focus:border-brand-teal focus:bg-white focus:ring-4 focus:ring-brand-teal/10 transition" dir="ltr">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-brand-slate mb-1.5">رقم هاتف الطالب (إن وجد)</label>
                            <input type="tel" name="phone" placeholder="01xxxxxxxxx" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs sm:text-sm font-semibold text-brand-slate focus:outline-none focus:border-brand-teal focus:bg-white focus:ring-4 focus:ring-brand-teal/10 transition" dir="ltr">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-brand-slate mb-1.5">العنوان / المنطقة السكنية</label>
                        <input type="text" name="address" placeholder="المدينة / الشارع / المنطقة..." class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs sm:text-sm font-semibold text-brand-slate focus:outline-none focus:border-brand-teal focus:bg-white focus:ring-4 focus:ring-brand-teal/10 transition">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-brand-slate mb-1.5">ملاحظات إضافية أو استفسار</label>
                        <textarea name="notes" rows="2" placeholder="أي تفاصيل أو رغبة في مواعيد معينة..." class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs sm:text-sm font-semibold text-brand-slate focus:outline-none focus:border-brand-teal focus:bg-white focus:ring-4 focus:ring-brand-teal/10 transition"></textarea>
                    </div>

                    <div id="formResponse" class="hidden p-4 rounded-2xl text-xs font-bold"></div>

                    <button type="submit" class="w-full py-4 bg-gradient-to-r from-brand-coral to-[#FF7552] hover:from-brand-coral-hover hover:to-brand-coral text-white font-heading font-bold rounded-2xl text-xs sm:text-sm shadow-lg shadow-brand-coral/25 hover:shadow-xl hover:shadow-brand-coral/35 transition flex items-center justify-center gap-2 mt-3">
                        <span>إرسال طلب التسجيل</span>
                        <span>←</span>
                    </button>
                </form>

            </div>
        </div>
    </section>

    {{-- ─── FOOTER (Responsive Multi-column / Stack) ─── --}}
    <footer class="bg-slate-900 border-t border-slate-800 py-10 sm:py-12 text-slate-400 text-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-5 sm:gap-6 text-center md:text-right">
            <div class="flex items-center gap-3.5">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" class="w-9 h-9 sm:w-10 sm:h-10 object-contain rounded-xl bg-white p-0.5 border border-slate-700 shrink-0">
                @endif
                <div>
                    <p class="text-white font-heading font-bold text-sm">{{ $settings['center_name'] }}</p>
                    <p class="text-slate-400 text-xs mt-0.5">{{ $settings['center_address'] }} — هاتف: <span class="font-mono text-sky-400" dir="ltr">{{ $settings['center_phone'] }}</span></p>
                </div>
            </div>

            {{-- Social Media Links --}}
            @if(!empty($settings['facebook_url']) || !empty($settings['youtube_url']) || !empty($settings['telegram_url']) || !empty($waNumber))
                <div class="flex items-center gap-3">
                    @if(!empty($settings['facebook_url']))
                        <a href="{{ $settings['facebook_url'] }}" target="_blank" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-blue-600 text-white flex items-center justify-center transition" title="فيسبوك">
                            <span class="font-bold text-sm">f</span>
                        </a>
                    @endif
                    @if(!empty($settings['youtube_url']))
                        <a href="{{ $settings['youtube_url'] }}" target="_blank" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-red-600 text-white flex items-center justify-center transition" title="يوتيوب">
                            <span class="font-bold text-sm">▶</span>
                        </a>
                    @endif
                    @if(!empty($settings['telegram_url']))
                        <a href="{{ $settings['telegram_url'] }}" target="_blank" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-sky-500 text-white flex items-center justify-center transition" title="تليجرام">
                            <span class="font-bold text-sm">✈</span>
                        </a>
                    @endif
                    @if($waNumber)
                        <a href="https://wa.me/{{ $waNumber }}" target="_blank" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-emerald-600 text-white flex items-center justify-center transition" title="واتساب">
                            <span class="font-bold text-sm">💬</span>
                        </a>
                    @endif
                </div>
            @endif

            <p class="font-medium text-center md:text-left text-slate-400">
                {{ $settings['footer_copyright_text'] }}  {{ date('Y') }} — {{ $settings['center_name'] }}
            </p>
        </div>
    </footer>

    {{-- Scripts & Responsiveness Controller --}}
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 700,
            once: true,
            easing: 'ease-out-cubic',
            disable: 'mobile' // Smoother scroll on mobile devices
        });

        // Mobile Menu Drawer Handler
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileMenu = document.getElementById('mobileMenu');
        const hamburgerIcon = document.getElementById('hamburgerIcon');
        const closeIcon = document.getElementById('closeIcon');

        function toggleMobileMenu() {
            const isHidden = mobileMenu.classList.contains('hidden');
            if (isHidden) {
                mobileMenu.classList.remove('hidden');
                hamburgerIcon.classList.add('hidden');
                closeIcon.classList.remove('hidden');
            } else {
                mobileMenu.classList.add('hidden');
                hamburgerIcon.classList.remove('hidden');
                closeIcon.classList.add('hidden');
            }
        }

        function closeMobileMenu() {
            mobileMenu.classList.add('hidden');
            hamburgerIcon.classList.remove('hidden');
            closeIcon.classList.add('hidden');
        }

        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', toggleMobileMenu);
        }

        // Dynamic Group Select based on Stage
        const stageSelect = document.getElementById('stageSelect');
        const groupSelect = document.getElementById('groupSelect');

        function selectStage(stageId) {
            stageSelect.value = stageId;
            stageSelect.dispatchEvent(new Event('change'));
        }

        stageSelect.addEventListener('change', function() {
            const stageId = this.value;
            groupSelect.innerHTML = '<option value="">-- جار التحميل... --</option>';
            
            if(!stageId) {
                groupSelect.innerHTML = '<option value="">-- اختر المجموعة بعد تحديد المرحلة --</option>';
                return;
            }

            fetch(`/api/stages/${stageId}/groups`)
                .then(res => res.json())
                .then(data => {
                    groupSelect.innerHTML = '<option value="">-- اختر المجموعة (اختياري) --</option>';
                    data.forEach(g => {
                        groupSelect.innerHTML += `<option value="${g.id}">${g.name} (${g.price_per_month} {{ $settings['currency_symbol'] }}/شهرياً)</option>`;
                    });
                })
                .catch(() => {
                    groupSelect.innerHTML = '<option value="">-- تعذر تحميل المجموعات --</option>';
                });
        });

        function selectGroup(stageId, groupId) {
            stageSelect.value = stageId;
            stageSelect.dispatchEvent(new Event('change'));
            setTimeout(() => {
                groupSelect.value = groupId;
            }, 300);
        }

        // AJAX Form Submission
        document.getElementById('enrollmentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const responseDiv = document.getElementById('formResponse');
            responseDiv.classList.add('hidden');
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'جار إرسال الطلب... ⏳';

            const formData = new FormData(this);
            const csrfToken = document.querySelector('input[name="_token"]')?.value || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            fetch('{{ route("enroll.submit") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok) {
                    let errMsg = data.message || 'حدث خطأ أثناء إرسال الطلب';
                    if (data.errors) {
                        errMsg = Object.values(data.errors).flat().join(' - ');
                    }
                    throw new Error(errMsg);
                }
                return data;
            })
            .then(data => {
                responseDiv.className = 'p-4 rounded-xl text-xs font-bold bg-emerald-50 border border-emerald-200 text-emerald-800';
                responseDiv.innerText = data.message || 'تم إرسال طلبك بنجاح وسنتواصل معك قريباً!';
                responseDiv.classList.remove('hidden');
                this.reset();
            })
            .catch(err => {
                responseDiv.className = 'p-4 rounded-xl text-xs font-bold bg-rose-50 border border-rose-200 text-rose-800';
                responseDiv.innerText = err.message || 'حدث خطأ أثناء إرسال الطلب، يرجى التأكد من البيانات والمحاولة مجدداً.';
                responseDiv.classList.remove('hidden');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            });
        });
    </script>
</body>
</html>
