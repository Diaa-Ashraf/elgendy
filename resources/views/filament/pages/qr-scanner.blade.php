<x-filament-panels::page>
    @php
    $stats = $this->getSessionStats();
    $sessionInfo = $this->getCurrentSessionInfo();
    $centerName = app(\App\Services\SettingService::class)->get('center_name', 'منظومة الأستاذ التعليمية');
    @endphp

    {{-- ─── Full-Screen Attendance Station ─── --}}
    <div x-data="{
        fullscreen: false,
        cameraActive: false,
        lastScannedName: '',
        showSuccessFlash: false,
        showErrorFlash: false,
        showDuplicateFlash: false,
        isProcessingScan: false,
        html5QrCode: null,

        toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().then(() => this.fullscreen = true);
            } else {
                document.exitFullscreen().then(() => this.fullscreen = false);
            }
        },

        playSuccessSound() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.frequency.value = 880;
                osc.type = 'sine';
                gain.gain.value = 0.3;
                osc.start();
                osc.stop(ctx.currentTime + 0.15);
                setTimeout(() => {
                    const osc2 = ctx.createOscillator();
                    const gain2 = ctx.createGain();
                    osc2.connect(gain2);
                    gain2.connect(ctx.destination);
                    osc2.frequency.value = 1320;
                    osc2.type = 'sine';
                    gain2.gain.value = 0.3;
                    osc2.start();
                    osc2.stop(ctx.currentTime + 0.2);
                }, 150);
            } catch(e) {}
        },

        playErrorSound() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.frequency.value = 300;
                osc.type = 'square';
                gain.gain.value = 0.2;
                osc.start();
                osc.stop(ctx.currentTime + 0.3);
            } catch(e) {}
        },

        initCamera() {
            if (this.cameraActive) {
                this.stopCamera();
                return;
            }

            const self = this;
            const startScanner = () => {
                self.cameraActive = true;
                setTimeout(() => {
                    const readerElement = document.getElementById('qr-camera-feed');
                    if (!readerElement) {
                        console.error('Reader element not found');
                        self.cameraActive = false;
                        return;
                    }

                    readerElement.innerHTML = '';
                    try {
                        const html5QrCode = new Html5Qrcode('qr-camera-feed');
                        self.html5QrCode = html5QrCode;
                        window.__activeScanner = html5QrCode;

                        const qrCodeSuccessCallback = (decodedText, decodedResult) => {
                            if (self.isProcessingScan) return;
                            self.isProcessingScan = true;

                            @this.set('scanned_code', decodedText);
                            @this.call('processScan').then(() => {
                                setTimeout(() => {
                                    self.isProcessingScan = false;
                                }, 1500);
                            }).catch(() => {
                                self.isProcessingScan = false;
                            });
                        };

                        const config = {
                            fps: 15,
                            qrbox: { width: 250, height: 250 },
                            aspectRatio: 1.0,
                            experimentalFeatures: {
                                useBarCodeDetectorIfSupported: true
                            }
                        };

                        // Start with environment camera or fallback to user camera
                        html5QrCode.start(
                            { facingMode: 'environment' },
                            config,
                            qrCodeSuccessCallback,
                            () => {}
                        ).catch(err => {
                            console.warn('Environment camera failed, trying fallback...', err);
                            html5QrCode.start(
                                { facingMode: 'user' },
                                config,
                                qrCodeSuccessCallback,
                                () => {}
                            ).catch(fallbackErr => {
                                console.error('Camera fallback error:', fallbackErr);
                                alert('تعذر فتح الكاميرا. يرجى التأكد من منح المتصفح صلاحية الوصول للكاميرا.');
                                self.cameraActive = false;
                                self.html5QrCode = null;
                            });
                        });
                    } catch (e) {
                        console.error('Html5Qrcode init error:', e);
                        self.cameraActive = false;
                    }
                }, 300);
            };

            if (typeof Html5Qrcode === 'undefined') {
                const script = document.createElement('script');
                script.src = 'https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js';
                script.onload = () => startScanner();
                document.head.appendChild(script);
            } else {
                startScanner();
            }
        },

        stopCamera() {
            this.cameraActive = false;
            this.isProcessingScan = false;
            if (this.html5QrCode) {
                this.html5QrCode.stop().then(() => {
                    this.html5QrCode.clear();
                    this.html5QrCode = null;
                }).catch(() => {
                    this.html5QrCode = null;
                });
            }
            if (window.__activeScanner) {
                window.__activeScanner.stop().catch(() => {});
                window.__activeScanner = null;
            }
        }
    }"
        x-init="
        $wire.on('scan-success', (e) => {
            lastScannedName = e.name || '';
            showSuccessFlash = true;
            if ($wire.sound_enabled) playSuccessSound();
            setTimeout(() => showSuccessFlash = false, 4000);
            $nextTick(() => { $refs.scanInput?.focus(); });
        });
        $wire.on('scan-error', () => {
            showErrorFlash = true;
            if ($wire.sound_enabled) playErrorSound();
            setTimeout(() => showErrorFlash = false, 3000);
            $nextTick(() => { $refs.scanInput?.focus(); });
        });
        $wire.on('scan-duplicate', () => {
            showDuplicateFlash = true;
            if ($wire.sound_enabled) playErrorSound();
            setTimeout(() => showDuplicateFlash = false, 3000);
            $nextTick(() => { $refs.scanInput?.focus(); });
        });
    "
        class="space-y-6">

        {{-- ─── شريط التحكم العلوي المتطور بتصميم زجاجي عصري ─── --}}
        <div class="relative overflow-hidden bg-slate-900/95 border border-slate-700/80 rounded-3xl p-6 shadow-2xl backdrop-blur-xl">
            <div class="absolute -top-24 -right-24 w-72 h-72 bg-amber-500/15 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -left-24 w-72 h-72 bg-emerald-500/15 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-gradient-to-tr from-amber-500 via-amber-600 to-amber-500 rounded-2xl flex items-center justify-center shadow-lg shadow-amber-500/30 text-white font-black text-2xl ring-2 ring-amber-400/40 shrink-0">
                        ⚡
                    </div>
                    <div>
                        <div class="flex items-center gap-3 flex-wrap">
                            <h1 class="text-xl md:text-2xl font-black text-white tracking-wide">{{ $centerName }}</h1>
                            <span class="px-3 py-1 rounded-full text-xs font-black bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 flex items-center gap-1.5 shadow-sm">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                                محطة حضور نشطة
                            </span>
                        </div>
                        <p class="text-xs text-slate-300 font-semibold mt-1">📡 تسجيل الحضور السريع الذكي عبر الـ QR Code والباركود مع إشعارات فورية لولي الأمر</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 w-full md:w-auto justify-end">
                    {{-- الساعة الذكية --}}
                    <div x-data="{ time: '' }" x-init="setInterval(() => time = new Date().toLocaleTimeString('ar-EG', {hour:'2-digit', minute:'2-digit', second:'2-digit', hour12:true}), 1000)"
                        class="bg-slate-950/90 px-4 py-2.5 rounded-2xl text-sm font-mono font-black text-amber-400 border border-amber-500/30 shadow-inner flex items-center gap-2">
                        <x-heroicon-o-clock class="w-4 h-4 text-amber-500 animate-pulse" />
                        <span x-text="time"></span>
                    </div>

                    {{-- زر الصوت --}}
                    <button wire:click="$toggle('sound_enabled')"
                        class="p-2.5 rounded-2xl transition-all duration-300 border flex items-center justify-center"
                        :class="$wire.sound_enabled ? 'bg-amber-500/20 border-amber-500/40 text-amber-300 shadow-lg shadow-amber-500/10 hover:bg-amber-500/30' : 'bg-slate-800 border-slate-700 text-slate-400 hover:bg-slate-700 hover:text-slate-200'"
                        title="تشغيل/إيقاف المؤثرات الصوتية">
                        <template x-if="$wire.sound_enabled">
                            <x-heroicon-o-speaker-wave class="w-5 h-5 text-amber-400" />
                        </template>
                        <template x-if="!$wire.sound_enabled">
                            <x-heroicon-o-speaker-x-mark class="w-5 h-5" />
                        </template>
                    </button>

                    {{-- Full Screen --}}
                    <button @click="toggleFullscreen()"
                        class="p-2.5 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white transition-all duration-300 border border-slate-700 shadow-md flex items-center justify-center"
                        title="وضع الشاشة الكاملة (Full Screen)">
                        <x-heroicon-o-arrows-pointing-out class="w-5 h-5" />
                    </button>
                </div>
            </div>

            {{-- ─── شريط الجلسة المحددة ─── --}}
            @if($sessionInfo)
            <div class="mt-4 pt-4 border-t border-slate-800 flex flex-wrap items-center justify-between gap-3 text-xs">
                <div class="flex flex-wrap items-center gap-2.5">
                    <span class="px-3 py-1.5 bg-amber-500/15 text-amber-300 border border-amber-500/30 rounded-xl font-bold flex items-center gap-1.5">
                        <span>📚 المجموعة:</span>
                        <strong class="text-white font-black">{{ $sessionInfo['name'] }}</strong>
                    </span>
                    <span class="px-3 py-1.5 bg-slate-800/90 text-slate-300 border border-slate-700 rounded-xl font-bold flex items-center gap-1.5">
                        <span>📖 المادة:</span>
                        <strong class="text-white font-black">{{ $sessionInfo['subject'] }}</strong>
                    </span>
                    <span class="px-3 py-1.5 bg-slate-800/90 text-slate-300 border border-slate-700 rounded-xl font-bold flex items-center gap-1.5">
                        <span>📅 التاريخ:</span>
                        <strong class="text-white font-mono font-bold">{{ $sessionInfo['date'] }}</strong>
                    </span>
                </div>
                <span class="px-3.5 py-1.5 bg-blue-500/15 border border-blue-500/30 text-blue-300 rounded-xl font-black flex items-center gap-1.5">
                    <span>📌</span>
                    <span>{{ $sessionInfo['title'] }}</span>
                </span>
            </div>
            @endif
        </div>

        {{-- ─── تنبيهات الفلاش التفاعلية ─── --}}
        {{-- نجاح --}}
        <div x-show="showSuccessFlash"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform -translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="bg-gradient-to-r from-emerald-600 via-emerald-500 to-teal-600 text-white rounded-2xl p-4 shadow-2xl shadow-emerald-500/30 flex items-center justify-between border border-emerald-400/30"
            style="display: none;">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center text-2xl shadow-inner animate-bounce">
                    ✅
                </div>
                <div>
                    <h3 class="font-black text-lg">تم تسجيل الحضور وإرسال الإشعار الداخلي بنجاح!</h3>
                    <p class="text-sm font-bold text-emerald-100 mt-0.5" x-text="lastScannedName"></p>
                </div>
            </div>
            <span class="text-xs px-3 py-1 bg-white/20 rounded-xl font-black">حاضر ✅</span>
        </div>

        {{-- خطأ كود غير معروف --}}
        <div x-show="showErrorFlash"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="bg-gradient-to-r from-rose-600 via-rose-500 to-red-600 text-white rounded-2xl p-4 shadow-2xl shadow-rose-500/30 flex items-center gap-4 border border-rose-400/30"
            style="display: none;">
            <div class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center text-2xl shadow-inner">
                ❌
            </div>
            <div>
                <h3 class="font-black text-base">رمز الـ QR أو الكود غير مسجل في المنظومة!</h3>
                <p class="text-xs text-rose-100 font-bold">تأكد من كود الطالب أو رقم الكارنيه وحاول مرة أخرى.</p>
            </div>
        </div>

        {{-- مكرر --}}
        <div x-show="showDuplicateFlash"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="bg-gradient-to-r from-amber-600 via-amber-500 to-orange-600 text-white rounded-2xl p-4 shadow-2xl shadow-amber-500/30 flex items-center gap-4 border border-amber-400/30"
            style="display: none;">
            <div class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center text-2xl shadow-inner">
                ⚠️
            </div>
            <div>
                <h3 class="font-black text-base">الطالب مسجل حضور بالفعل في هذه الحصة!</h3>
                <p class="text-xs text-amber-100 font-bold">تم رصد حضور الطالب مسبقاً وتفادي التكرار.</p>
            </div>
        </div>

        {{-- ─── كارت الطالب الممسوح حالياً مع أدوات الواتساب ─── --}}
        @if($lastScannedStudent)
        <div class="relative overflow-hidden bg-slate-900/95 border-2 border-amber-500/50 rounded-3xl p-6 shadow-2xl backdrop-blur-xl">
            <div class="absolute top-0 right-0 w-48 h-48 bg-emerald-500/10 rounded-full blur-2xl pointer-events-none"></div>

            <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-5 border-b border-slate-800 pb-5 mb-5">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-emerald-500/20 to-teal-500/30 border-2 border-emerald-500/50 text-emerald-400 flex items-center justify-center font-black text-2xl shadow-lg shadow-emerald-500/10 shrink-0">
                        🎓
                    </div>
                    <div>
                        <div class="flex items-center gap-2.5 flex-wrap">
                            <h3 class="text-xl font-black text-white tracking-wide">{{ $lastScannedStudent['name'] }}</h3>
                            <span class="px-2.5 py-0.5 bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 rounded-lg text-xs font-black flex items-center gap-1">
                                <span>✅</span>
                                <span>حاضر الآن</span>
                            </span>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 mt-1.5 text-xs">
                            <span class="px-2.5 py-1 bg-amber-500/15 text-amber-300 border border-amber-500/30 rounded-lg font-mono font-black">
                                كود: {{ $lastScannedStudent['code'] }}
                            </span>
                            <span class="px-2.5 py-1 bg-slate-800/90 text-slate-300 border border-slate-700 rounded-lg font-bold">
                                مجموعة: {{ $lastScannedStudent['group_name'] }}
                            </span>
                            <span class="px-2.5 py-1 bg-slate-800/90 text-slate-300 border border-slate-700 rounded-lg font-mono font-bold">
                                وقت الحضور: {{ $lastScannedStudent['time'] }}
                            </span>
                        </div>
                    </div>
                </div>

                @if(!empty($lastScannedStudent['parent_phone']))
                @php
                $waUrl = \App\Services\WhatsAppNotificationService::getWhatsAppUrl($lastScannedStudent['parent_phone'], $manualMessage);
                @endphp
                <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
                    {{-- زر فتح واتساب ويب المجاني المباشر --}}
                    <a href="{{ $waUrl }}"
                        target="_blank"
                        wire:click="markWhatsAppSent({{ $lastAttendanceId }})"
                        class="flex-1 lg:flex-initial px-5 py-3 bg-gradient-to-r from-emerald-600 via-emerald-500 to-green-600 hover:from-emerald-500 hover:to-green-500 text-white rounded-2xl text-xs font-black shadow-lg shadow-emerald-600/30 hover:scale-[1.02] active:scale-95 transition-all flex items-center justify-center gap-2 cursor-pointer">
                        <span class="text-base">💬</span>
                        <span>فتح محادثة واتساب (مجاناً)</span>
                    </a>

                    {{-- زر الإرسال عبر النظام --}}
                    <button wire:click="sendManualWhatsApp"
                        wire:loading.attr="disabled"
                        class="flex-1 lg:flex-initial px-5 py-3 bg-slate-800 hover:bg-slate-700 text-amber-300 hover:text-amber-200 border border-amber-500/40 rounded-2xl text-xs font-black shadow-md hover:scale-[1.02] active:scale-95 transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50">
                        <span wire:loading.remove wire:target="sendManualWhatsApp">📤 إرسال عبر النظام</span>
                        <span wire:loading wire:target="sendManualWhatsApp">جاري الإرسال...</span>
                    </button>
                </div>
                @else
                <div class="px-4 py-2 bg-amber-500/10 border border-amber-500/30 rounded-2xl text-xs text-amber-300 font-bold flex items-center gap-2">
                    <span>⚠️</span>
                    <span>لا يوجد رقم ولي أمر مسجل بملف الطالب</span>
                </div>
                @endif
            </div>

            {{-- حقل تعديل نص الرسالة اليدوية مع تباين فائق ووضوح كامل --}}
            @if(!empty($lastScannedStudent['parent_phone']))
            <div class="space-y-2 bg-slate-950/80 border border-slate-800 rounded-2xl p-4">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <label class="text-xs font-black text-amber-400 flex items-center gap-1.5">
                        <span>💬</span>
                        <span>نص رسالة الواتساب لولي الأمر (يمكنك التعديل عليها قبل الإرسال):</span>
                    </label>
                    <span class="text-xs font-bold text-slate-300 bg-slate-900 px-3 py-1 rounded-xl border border-slate-700">
                        📱 هاتف ولي الأمر: <strong class="text-amber-300 font-mono font-black">{{ $lastScannedStudent['parent_phone'] }}</strong>
                    </span>
                </div>
                <div class="relative mt-2">
                    <textarea wire:model.live="manualMessage"
                        rows="3"
                        style="background-color: #0b1120 !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; font-size: 13.5px !important; font-weight: 600 !important; line-height: 1.6 !important; border: 1.5px solid #334155 !important; border-radius: 14px !important; padding: 12px 14px !important; caret-color: #fbbf24 !important;"
                        class="wa-message-textarea w-full rounded-2xl focus:ring-2 focus:ring-amber-500 focus:outline-none transition-all shadow-inner placeholder:text-slate-500"
                        placeholder="اكتب نص رسالة الواتساب هنا..."></textarea>
                </div>
            </div>
            @endif
        </div>
        @endif

        {{-- ─── الشبكة الرئيسية للشاشة ─── --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- ═══ العمود 1: الإحصائيات والإعدادات والتحكم بالكاميرا ═══ --}}
            <div class="space-y-6">

                {{-- كروت الإحصائيات --}}
                <div class="grid grid-cols-3 gap-3">
                    {{-- حاضرين --}}
                    <div class="bg-gradient-to-br from-emerald-950/60 to-gray-900 border border-emerald-500/40 p-4 rounded-2xl text-center shadow-lg hover:border-emerald-400 transition-all">
                        <p class="text-3xl font-black text-emerald-400 font-mono">{{ $stats['present'] }}</p>
                        <p class="text-[11px] font-black text-emerald-300 mt-1">حاضرين ✅</p>
                    </div>
                    {{-- غايبين --}}
                    <div class="bg-gradient-to-br from-rose-950/60 to-gray-900 border border-rose-500/40 p-4 rounded-2xl text-center shadow-lg hover:border-rose-400 transition-all">
                        <p class="text-3xl font-black text-rose-400 font-mono">{{ $stats['absent'] }}</p>
                        <p class="text-[11px] font-black text-rose-300 mt-1">غائبين ❌</p>
                    </div>
                    {{-- النسبة --}}
                    <div class="bg-gradient-to-br from-amber-950/60 to-gray-900 border border-amber-500/40 p-4 rounded-2xl text-center shadow-lg hover:border-amber-400 transition-all">
                        <p class="text-3xl font-black text-amber-400 font-mono">{{ $stats['rate'] }}%</p>
                        <p class="text-[11px] font-black text-amber-300 mt-1">نسبة الحضور 📊</p>
                    </div>
                </div>

                {{-- وضع التسجيل (تلقائي / يدوي) --}}
                <div class="bg-gray-900 border border-gray-800 p-5 rounded-3xl shadow-xl space-y-4">
                    <div>
                        <label class="block text-xs font-black text-gray-300 mb-2">وضع التحضير</label>
                        <div class="flex bg-gray-950 p-1.5 rounded-2xl border border-gray-800">
                            <button wire:click="$set('mode', 'auto')"
                                class="flex-1 py-2.5 rounded-xl text-xs font-black transition-all duration-300 flex items-center justify-center gap-1.5 {{ $mode === 'auto' ? 'bg-gradient-to-r from-amber-500 to-amber-600 text-gray-950 shadow-lg shadow-amber-500/25' : 'text-gray-400 hover:text-white' }}">
                                <span>⚡ تلقائي ذكي</span>
                            </button>
                            <button wire:click="$set('mode', 'manual')"
                                class="flex-1 py-2.5 rounded-xl text-xs font-black transition-all duration-300 flex items-center justify-center gap-1.5 {{ $mode === 'manual' ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg shadow-blue-500/25' : 'text-gray-400 hover:text-white' }}">
                                <span>📌 اختيار يدوي</span>
                            </button>
                        </div>
                    </div>

                    {{-- اختيار الحصة --}}
                    <div>
                        <label class="block text-xs font-black text-gray-300 mb-2">
                            {{ $mode === 'auto' ? '🔍 الحصة المحددة للمسح' : '📌 اختر الحصة يدوياً' }}
                        </label>
                        <select wire:model.live="selected_session_id"
                            style="background-color: #ffffff !important; color: #000000 !important; font-weight: 800 !important;"
                            class="w-full text-xs rounded-2xl px-3.5 py-3 border border-gray-300 focus:ring-2 focus:ring-amber-500 shadow-inner cursor-pointer">
                            <option value="" style="background-color: #ffffff; color: #000000;">-- {{ $mode === 'auto' ? 'تعرف تلقائي من جدول الطالب' : 'اختر الحصة المحددة' }} --</option>
                            @php
                            $activeSessions = \App\Models\GroupSession::with('group.subject')
                            ->orderBy('date', 'desc')
                            ->take(40)
                            ->get();
                            @endphp
                            @foreach($activeSessions as $session)
                            <option value="{{ $session->id }}" style="background-color: #ffffff; color: #000000;">
                                {{ $session->group?->name }} — {{ $session->group?->subject?->name }} ({{ \Carbon\Carbon::parse($session->date)->format('Y-m-d') }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- زر تشغيل كاميرا الهاتف / الويب --}}
                    <button @click="initCamera()"
                        class="w-full py-3.5 rounded-2xl font-black text-xs transition-all duration-300 flex items-center justify-center gap-2 shadow-xl"
                        :class="cameraActive
                                ? 'bg-gradient-to-r from-rose-600 to-red-600 text-white shadow-rose-600/30 hover:scale-[1.02]'
                                : 'bg-gradient-to-r from-purple-600 to-indigo-600 text-white shadow-purple-600/30 hover:scale-[1.02]'">
                        <x-heroicon-o-camera class="w-5 h-5" />
                        <span x-text="cameraActive ? '⏹️ إيقاف كاميرا الماسح' : '📷 تفعيل كاميرا الهاتف / الويب'"></span>
                    </button>

                    {{-- شاشة الكاميرا مع ليزر المسح --}}
                    <div x-show="cameraActive" x-transition class="relative bg-black rounded-2xl overflow-hidden border border-purple-500/50 shadow-2xl" style="display: none;">
                        <div id="qr-camera-feed" class="w-full" style="min-height: 250px;"></div>
                        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-transparent via-red-500 to-transparent shadow-[0_0_15px_#ef4444] animate-pulse"></div>
                    </div>
                </div>
            </div>

            {{-- ═══ العمود 2: منطقة المسح المركزية التفاعلية مع قارئ الباركود ═══ --}}
            <div class="space-y-6">
                <div class="relative bg-gradient-to-b from-gray-900 via-gray-900 to-slate-950 rounded-3xl border-2 border-dashed border-amber-500/40 p-6 shadow-2xl flex flex-col justify-between overflow-hidden min-h-[420px]">

                    {{-- خلفية ضوئية متحركة --}}
                    <div class="absolute inset-0 bg-[radial-gradient(#f59e0b_1px,transparent_1px)] [background-size:16px_16px] opacity-10 pointer-events-none"></div>

                    {{-- الأيقونة والهدف المركزي --}}
                    <div class="text-center relative z-10 pt-4">
                        <div class="relative w-28 h-28 mx-auto mb-4 flex items-center justify-center">
                            {{-- زوايا الهدف الذهبية --}}
                            <div class="absolute top-0 left-0 w-6 h-6 border-t-4 border-l-4 border-amber-400 rounded-tl-lg"></div>
                            <div class="absolute top-0 right-0 w-6 h-6 border-t-4 border-r-4 border-amber-400 rounded-tr-lg"></div>
                            <div class="absolute bottom-0 left-0 w-6 h-6 border-b-4 border-l-4 border-amber-400 rounded-bl-lg"></div>
                            <div class="absolute bottom-0 right-0 w-6 h-6 border-b-4 border-r-4 border-amber-400 rounded-br-lg"></div>

                            <div class="w-20 h-20 bg-amber-500/10 rounded-2xl flex items-center justify-center border border-amber-500/30 text-amber-400 shadow-inner">
                                <x-heroicon-o-qr-code class="w-12 h-12 animate-pulse" />
                            </div>
                        </div>

                        <h2 class="text-xl font-black text-white">منطقة المسح والتوجيه</h2>
                        <p class="text-xs text-gray-400 font-bold mt-1">وجّه مسدس الباركود أو الكارنيه أو اكتب الكود واضغط Enter</p>
                    </div>

                    {{-- نموذج الإدخال السريع --}}
                    <div class="relative z-10 my-6">
                        @if($currentSession['is_future'] ?? false)
                            <div class="p-3 mb-4 bg-rose-950/80 border-2 border-rose-500/50 rounded-2xl text-center shadow-lg animate-pulse">
                                <span class="text-xs font-black text-rose-200 block">
                                    ⚠️ الحصة المحددة مجدولة بتاريخ مستقبلي ({{ $currentSession['date'] }}).
                                </span>
                                <span class="text-[11px] font-bold text-rose-400 mt-0.5 block">
                                    تسجيل الحضور متاح فقط في يوم الحصة أو بعدها.
                                </span>
                            </div>
                        @endif

                        <form wire:submit="processScan" class="space-y-4">
                            <div class="relative">
                                <input type="text"
                                    x-ref="scanInput"
                                    wire:model="scanned_code"
                                    placeholder="STD-XXXX أو رقم الكود أو الهاتف..."
                                    autofocus
                                    autocomplete="off"
                                    @if($currentSession['is_future'] ?? false) disabled @endif
                                    style="background-color: #030712 !important; color: #fbbf24 !important; -webkit-text-fill-color: #fbbf24 !important; caret-color: #fbbf24 !important; font-weight: 900 !important;"
                                    class="scan-input w-full px-5 py-4 bg-gray-950 border-2 border-amber-500/50 rounded-2xl text-center font-mono font-black text-2xl tracking-widest focus:outline-none focus:ring-4 focus:ring-amber-500/30 focus:border-amber-400 transition-all placeholder:text-gray-500 placeholder:text-sm placeholder:font-normal shadow-inner disabled:opacity-50">

                                <div class="absolute left-4 top-1/2 -translate-y-1/2">
                                    <div wire:loading wire:target="processScan" class="w-6 h-6 border-3 border-amber-400 border-t-transparent rounded-full animate-spin"></div>
                                </div>
                            </div>

                            <button type="submit"
                                wire:loading.attr="disabled"
                                @if($currentSession['is_future'] ?? false) disabled @endif
                                class="w-full py-4 bg-gradient-to-r from-amber-500 via-amber-600 to-amber-500 hover:from-amber-400 hover:to-amber-500 text-gray-950 font-black rounded-2xl text-sm transition-all duration-300 shadow-xl shadow-amber-500/25 hover:scale-[1.02] active:scale-95 disabled:opacity-50 flex items-center justify-center gap-2 cursor-pointer">
                                <x-heroicon-o-check-badge class="w-5 h-5" />
                                <span wire:loading.remove wire:target="processScan">
                                    {{ ($currentSession['is_future'] ?? false) ? 'الحصة في تاريخ مستقبلي (مغلقة)' : 'تسجيل الحضور الفوري (Enter ↵)' }}
                                </span>
                                <span wire:loading wire:target="processScan">جاري التحقق والتسجيل...</span>
                            </button>
                        </form>
                    </div>

                    {{-- تلميحات تشغيلية --}}
                    <div class="relative z-10 text-center pb-2">
                        <span class="inline-block px-4 py-2 bg-gray-950/80 border border-gray-800 rounded-xl text-[11px] text-gray-400 font-bold">
                            💡 النظام يستمع تلقائياً لأي قارئ باركود خارجي ويسجل الحضور فوراً.
                        </span>
                    </div>
                </div>
            </div>

            {{-- ═══ العمود 3: سجل الحضور والغياب والتواصل الفوري ═══ --}}
            <div class="space-y-6">
                <div class="bg-gradient-to-b from-gray-900 via-gray-900 to-slate-950 border border-gray-800 rounded-3xl shadow-2xl overflow-hidden flex flex-col h-[520px]">

                    {{-- تبويبات الحاضرين والغائبين --}}
                    <div class="p-2 bg-gray-950/90 border-b border-gray-800 flex items-center gap-2">
                        <button type="button"
                            wire:click="setActiveTab('attendees')"
                            class="flex-1 py-2.5 px-3 rounded-2xl font-black text-xs transition-all flex items-center justify-center gap-2 cursor-pointer {{ $activeTab === 'attendees' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 shadow-sm' : 'text-gray-400 hover:text-white hover:bg-gray-800/40' }}">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>الحاضرين</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-mono bg-emerald-500/30 text-emerald-200">
                                {{ count($attendance_log) }}
                            </span>
                        </button>

                        <button type="button"
                            wire:click="setActiveTab('absentees')"
                            class="flex-1 py-2.5 px-3 rounded-2xl font-black text-xs transition-all flex items-center justify-center gap-2 cursor-pointer {{ $activeTab === 'absentees' ? 'bg-rose-500/20 text-rose-300 border border-rose-500/40 shadow-sm' : 'text-gray-400 hover:text-white hover:bg-gray-800/40' }}">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                            <span>الغائبين</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-mono bg-rose-500/30 text-rose-200">
                                {{ count($absentees_log) }}
                            </span>
                        </button>
                    </div>

                    {{-- شريط الإجراءات الجماعية (Bulk Actions Bar) --}}
                    <div class="px-4 py-2.5 bg-gray-900/90 border-b border-gray-800/70 flex items-center justify-between gap-2">
                        @if($activeTab === 'attendees')
                            <span class="text-[11px] font-bold text-gray-400">إجراءات الحاضرين:</span>
                            <div class="flex items-center gap-1.5">
                                <button type="button"
                                    wire:click="notifyAllAttendeesPortal"
                                    wire:loading.attr="disabled"
                                    class="px-2.5 py-1 bg-blue-600/20 hover:bg-blue-600/40 text-blue-300 border border-blue-500/30 rounded-xl text-[10px] font-bold transition flex items-center gap-1 cursor-pointer">
                                    <x-heroicon-o-bell class="w-3.5 h-3.5" />
                                    <span>إشعار البوابة للكل</span>
                                </button>
                                <button type="button"
                                    wire:click="sendAllAttendeesWhatsApp"
                                    wire:loading.attr="disabled"
                                    class="px-2.5 py-1 bg-emerald-600/20 hover:bg-emerald-600/40 text-emerald-300 border border-emerald-500/30 rounded-xl text-[10px] font-bold transition flex items-center gap-1 cursor-pointer">
                                    <x-heroicon-o-paper-airplane class="w-3.5 h-3.5" />
                                    <span>واتساب للجميع</span>
                                </button>
                            </div>
                        @else
                            <span class="text-[11px] font-bold text-rose-400">إجراءات الغائبين:</span>
                            <div class="flex items-center gap-1.5">
                                <button type="button"
                                    wire:click="notifyAllAbsenteesPortal"
                                    wire:loading.attr="disabled"
                                    class="px-2.5 py-1 bg-rose-600/20 hover:bg-rose-600/40 text-rose-300 border border-rose-500/30 rounded-xl text-[10px] font-bold transition flex items-center gap-1 cursor-pointer">
                                    <x-heroicon-o-bell-alert class="w-3.5 h-3.5" />
                                    <span>إشعار غياب للبوابة</span>
                                </button>
                                <button type="button"
                                    wire:click="sendAllAbsenteesWhatsApp"
                                    wire:loading.attr="disabled"
                                    class="px-2.5 py-1 bg-amber-600/20 hover:bg-amber-600/40 text-amber-300 border border-amber-500/30 rounded-xl text-[10px] font-bold transition flex items-center gap-1 cursor-pointer">
                                    <x-heroicon-o-paper-airplane class="w-3.5 h-3.5" />
                                    <span>واتساب للغائبين</span>
                                </button>
                            </div>
                        @endif
                    </div>

                    {{-- المحتوى التفاعلي للقوائم --}}
                    <div class="flex-1 overflow-y-auto p-3 space-y-2.5 custom-scrollbar">
                        @if($activeTab === 'attendees')
                            {{-- قائمة الحضور --}}
                            @forelse($attendance_log as $index => $entry)
                            <div class="p-3.5 rounded-2xl flex items-center justify-between transition-all duration-300 {{ $index === 0 ? 'bg-gradient-to-r from-emerald-950/80 via-emerald-900/30 to-gray-900 border-2 border-emerald-500/50 shadow-lg shadow-emerald-500/10 animate-fade-in' : 'bg-gray-950/60 border border-gray-800/80 hover:border-gray-700 hover:bg-gray-800/40' }}">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xs font-black shadow-inner {{ $entry['status'] === 'present' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-amber-500/20 text-amber-400 border border-amber-500/30' }}">
                                        #{{ $index + 1 }}
                                    </div>
                                    <div>
                                        <h4 class="font-black text-sm text-white flex items-center gap-1.5">
                                            <span>{{ $entry['name'] }}</span>
                                            @if($index === 0)
                                            <span class="px-1.5 py-0.5 bg-emerald-500/20 border border-emerald-500/40 text-[9px] text-emerald-300 rounded font-bold animate-pulse">الآن</span>
                                            @endif
                                        </h4>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-[11px] text-amber-400 font-mono font-bold bg-amber-500/10 px-2 py-0.5 rounded-md border border-amber-500/20">
                                                {{ $entry['code'] ?: 'STD' }}
                                            </span>
                                            @if($entry['whatsapp_sent_at'])
                                            <span class="text-[10px] text-emerald-300 bg-emerald-500/15 border border-emerald-500/30 px-1.5 py-0.5 rounded font-bold">
                                                📱 واتساب {{ $entry['whatsapp_sent_at'] }}
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="text-left flex flex-col items-end gap-1.5">
                                    <span class="px-2.5 py-1 rounded-xl text-[11px] font-black {{ $entry['status'] === 'present' ? 'bg-emerald-500/20 border border-emerald-500/40 text-emerald-300' : 'bg-amber-500/20 border border-amber-500/40 text-amber-300' }}">
                                        {{ $entry['status'] === 'present' ? 'حاضر ✅' : 'متأخر ⏰' }}
                                    </span>
                                    <div class="flex items-center gap-2">
                                        @if(!empty($entry['parent_phone']))
                                        @php
                                        $entryMsg = "السلام عليكم ورحمة الله، المكرم ولي أمر الطالب/ة: {$entry['name']} ✅\nتم تسجيل حضور ابنكم بنجاح في الحصة بتاريخ ووقت: {$entry['time']}.\n— {$centerName}";
                                        $entryWaUrl = \App\Services\WhatsAppNotificationService::getWhatsAppUrl($entry['parent_phone'], $entryMsg);
                                        @endphp
                                        <a href="{{ $entryWaUrl }}"
                                            target="_blank"
                                            wire:click="markWhatsAppSent({{ $entry['id'] }})"
                                            class="text-[10px] px-2 py-0.5 bg-emerald-600/20 hover:bg-emerald-600/40 text-emerald-300 border border-emerald-500/30 rounded font-bold transition"
                                            title="إرسال واتساب لولي الأمر">
                                            💬 واتساب
                                        </a>
                                        @endif
                                        <span class="text-[11px] text-gray-400 font-mono font-bold">{{ $entry['time'] }}</span>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="h-full flex flex-col items-center justify-center text-center py-16 px-4">
                                <div class="w-16 h-16 bg-gray-950 rounded-3xl border border-gray-800 flex items-center justify-center text-gray-600 mb-3 shadow-inner">
                                    <x-heroicon-o-user-group class="w-8 h-8 opacity-70" />
                                </div>
                                <p class="text-sm font-black text-gray-300">لم يتم تسجيل أي حضور حتى الآن</p>
                                <p class="text-xs text-gray-500 font-bold mt-1">سجل أول طالب وسيظهر هنا مباشرة وبشكل لحظي ⚡</p>
                            </div>
                            @endforelse
                        @else
                            {{-- قائمة الغائبين --}}
                            @forelse($absentees_log as $index => $abs)
                            <div class="p-3.5 rounded-2xl flex items-center justify-between transition-all duration-300 bg-gray-950/60 border border-gray-800/80 hover:border-rose-500/40 hover:bg-gray-800/40">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xs font-black shadow-inner bg-rose-500/20 text-rose-400 border border-rose-500/30">
                                        #{{ $index + 1 }}
                                    </div>
                                    <div>
                                        <h4 class="font-black text-sm text-white flex items-center gap-1.5">
                                            <span>{{ $abs['name'] }}</span>
                                        </h4>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-[11px] text-rose-400 font-mono font-bold bg-rose-500/10 px-2 py-0.5 rounded-md border border-rose-500/20">
                                                {{ $abs['code'] ?: 'STD' }}
                                            </span>
                                            @if($abs['whatsapp_sent_at'])
                                            <span class="text-[10px] text-emerald-300 bg-emerald-500/15 border border-emerald-500/30 px-1.5 py-0.5 rounded font-bold">
                                                📱 واتساب {{ $abs['whatsapp_sent_at'] }}
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="text-left flex flex-col items-end gap-1.5">
                                    <button type="button"
                                        wire:click="markStudentPresent({{ $abs['student_id'] }})"
                                        class="px-2.5 py-1 rounded-xl text-[11px] font-black bg-emerald-500/20 hover:bg-emerald-500/40 border border-emerald-500/40 text-emerald-300 transition cursor-pointer flex items-center gap-1">
                                        <x-heroicon-o-check class="w-3.5 h-3.5" />
                                        <span>تحضير الآن</span>
                                    </button>
                                    <div class="flex items-center gap-2">
                                        @if(!empty($abs['parent_phone']))
                                        @php
                                        $absMsg = "السلام عليكم ورحمة الله، المكرم ولي أمر الطالب/ة: {$abs['name']} ⚠️\nنحيطكم علماً بغياب الطالب اليوم عن الحصة الدراسية.\n— {$centerName}";
                                        $absWaUrl = \App\Services\WhatsAppNotificationService::getWhatsAppUrl($abs['parent_phone'], $absMsg);
                                        @endphp
                                        <a href="{{ $absWaUrl }}"
                                            target="_blank"
                                            class="text-[10px] px-2 py-0.5 bg-rose-600/20 hover:bg-rose-600/40 text-rose-300 border border-rose-500/30 rounded font-bold transition"
                                            title="إرسال تنبيه غياب بالواتساب">
                                            💬 واتساب
                                        </a>
                                        <span class="text-[11px] text-gray-400 font-mono">{{ $abs['parent_phone'] }}</span>
                                        @else
                                        <span class="text-[10px] text-gray-500">لا يوجد رقم</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="h-full flex flex-col items-center justify-center text-center py-16 px-4">
                                <div class="w-16 h-16 bg-gray-950 rounded-3xl border border-gray-800 flex items-center justify-center text-emerald-500 mb-3 shadow-inner">
                                    <x-heroicon-o-check-circle class="w-8 h-8 opacity-70" />
                                </div>
                                <p class="text-sm font-black text-white">لا يوجد غائبين في هذه الحصة 🎉</p>
                                <p class="text-xs text-gray-400 font-bold mt-1">جميع طلاب المجموعة سجلوا حضورهم بنجاح.</p>
                            </div>
                            @endforelse
                        @endif
                    </div>

                </div>
            </div>

        </div>

    </div>

    {{-- ─── html5-qrcode Library CDN ─── --}}
    @push('scripts')
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    @endpush

    {{-- ─── Custom Animation Styles ─── --}}
    @push('styles')
    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-8px) scale(0.98);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .animate-fade-in {
            animation: fadeIn 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .scan-input {
            background-color: #030712 !important;
            color: #fbbf24 !important;
            -webkit-text-fill-color: #fbbf24 !important;
            caret-color: #fbbf24 !important;
        }

        .scan-input::placeholder {
            color: #64748b !important;
            -webkit-text-fill-color: #64748b !important;
            font-weight: 600 !important;
        }

        .wa-message-textarea {
            background-color: #0b1120 !important;
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            border: 1.5px solid #334155 !important;
            caret-color: #fbbf24 !important;
            font-size: 13.5px !important;
            font-weight: 600 !important;
            line-height: 1.6 !important;
        }

        .wa-message-textarea:focus {
            border-color: #f59e0b !important;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.25) !important;
        }

        .wa-message-textarea::placeholder {
            color: #64748b !important;
            -webkit-text-fill-color: #64748b !important;
        }
    </style>
    @endpush
</x-filament-panels::page>