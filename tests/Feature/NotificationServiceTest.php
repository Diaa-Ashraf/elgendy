<?php

namespace Tests\Feature;

use App\Models\EducationalStage;
use App\Models\ParentNotification;
use App\Models\Student;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_send_system_notification_inserts_database_notifications(): void
    {
        $user = User::factory()->create();

        NotificationService::sendSystemNotification(
            'عنوان الإشعار التجريبي',
            'محتوى الإشعار التجريبي',
            'success'
        );

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
        ]);
    }

    public function test_notify_parent_creates_parent_notification(): void
    {
        $stage = EducationalStage::create(['name' => 'المرحلة الثانوية']);

        $student = Student::create([
            'name' => 'طالب تجريبي',
            'phone' => '01011112222',
            'parent_phone' => '01033334444',
            'qr_code' => 'STD-TEST01',
            'stage_id' => $stage->id,
        ]);

        $notif = NotificationService::notifyParent(
            $student->id,
            'attendance',
            'حضور الحصة',
            'تم تسجيل الحضور بنجاح'
        );

        $this->assertNotNull($notif);
        $this->assertDatabaseHas('parent_notifications', [
            'student_id' => $student->id,
            'type' => 'attendance',
            'title' => 'حضور الحصة',
            'is_read' => 0,
        ]);
    }

    public function test_notify_attendance_creates_both_system_and_parent_notifications(): void
    {
        $user = User::factory()->create();
        $stage = EducationalStage::create(['name' => 'المرحلة الإعدادية']);

        $student = Student::create([
            'name' => 'طالب اختبار الحضور',
            'phone' => '01055556666',
            'parent_phone' => '01077778888',
            'qr_code' => 'STD-ATT01',
            'stage_id' => $stage->id,
        ]);

        NotificationService::notifyAttendance(
            $student->name,
            'مجموعة النخبة',
            now()->format('Y-m-d H:i'),
            'present',
            $student->id
        );

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
        ]);

        $this->assertDatabaseHas('parent_notifications', [
            'student_id' => $student->id,
            'type' => 'attendance',
        ]);
    }
}
