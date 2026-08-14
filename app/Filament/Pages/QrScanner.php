<?php

namespace App\Filament\Pages;

use App\Models\Attendance;
use App\Models\GroupSession;
use App\Models\Student;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class QrScanner extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-qr-code';

    protected static ?string $navigationLabel = 'ماسح الـ QR لحضور الطلاب';

    protected static ?string $title = 'تسجيل الحضور السريع بماسح الـ QR';

    protected static ?string $navigationGroup = 'الإدارة الأكاديمية';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.qr-scanner';

    public ?int $selected_session_id = null;
    public string $scanned_code = '';
    public string $mode = 'auto'; // 'auto' أو 'manual'

    public static function canAccess(): bool
    {
        return (bool) Auth::user();
    }

    public function processScan(): void
    {
        $code = trim($this->scanned_code);

        if (empty($code)) {
            Notification::make()
                ->title('تنبيه!')
                ->body('يرجى إدخال كود الطالب أو مسح الـ QR Code.')
                ->warning()
                ->send();
            return;
        }

        // 1. البحث عن الطالب بواسطة الكود أو المعرف
        $student = Student::where('qr_code', $code)->first();
        if (! $student && is_numeric($code)) {
            $student = Student::find((int) $code);
        }

        if (! $student) {
            Notification::make()
                ->title('خطأ في المسح ❌')
                ->body("رمز الـ QR الخاص بالطالب غير موجود بالنظام ({$code}).")
                ->danger()
                ->send();
            $this->scanned_code = '';
            return;
        }

        $targetSession = null;

        if ($this->mode === 'auto') {
            // البحث عن الجلسة الجارية الخاصة بفرق/مجموعات الطالب اليوم
            $studentGroupIds = $student->groups()->pluck('groups.id');

            $targetSession = GroupSession::with('group')
                ->whereIn('group_id', $studentGroupIds)
                ->whereDate('date', now()->toDateString())
                ->orderBy('date', 'desc')
                ->first();

            if (! $targetSession) {
                // محاولة البحث عن أحدث جلسة اليوم بشكل عام للمجموعة إذا لم يُحدد تاريخ بدقة
                $targetSession = GroupSession::with('group')
                    ->whereIn('group_id', $studentGroupIds)
                    ->orderBy('date', 'desc')
                    ->first();
            }

            if (! $targetSession) {
                Notification::make()
                    ->title('لم يتم العثور على حصة ⚠️')
                    ->body("الطالب ({$student->name}) مسجل ولكن لا توجد حصة مسجلة لمجموعاته اليوم.")
                    ->warning()
                    ->send();
                $this->scanned_code = '';
                return;
            }

            $this->selected_session_id = $targetSession->id;
        } else {
            // الوضع اليدوي: استخدام الجلسة المحددة من القائمة
            if (! $this->selected_session_id) {
                Notification::make()
                    ->title('تنبيه!')
                    ->body('يرجى اختيار الجلسة / الحصة الدراسية أولاً من القائمة.')
                    ->warning()
                    ->send();
                return;
            }

            $targetSession = GroupSession::with('group')->find($this->selected_session_id);
        }

        if (! $targetSession) {
            Notification::make()->title('الجلسة غير موجودة')->danger()->send();
            return;
        }

        // تسجيل الحضور
        Attendance::updateOrCreate(
            [
                'group_session_id' => $targetSession->id,
                'student_id' => $student->id,
            ],
            [
                'status' => 'present',
            ]
        );

        if ($targetSession->status === 'scheduled') {
            $targetSession->update(['status' => 'held']);
        }

        Notification::make()
            ->title("تم تسجيل حضور: {$student->name} ✅")
            ->body("المجموعة: {$targetSession->group?->name} — الوقت: " . now()->format('h:i A'))
            ->success()
            ->send();

        $this->scanned_code = '';
    }
}
