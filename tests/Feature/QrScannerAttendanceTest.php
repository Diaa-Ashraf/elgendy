<?php

namespace Tests\Feature;

use App\Filament\Pages\QrScanner;
use App\Models\Attendance;
use App\Models\EducationalStage;
use App\Models\Group;
use App\Models\GroupSession;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class QrScannerAttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_qr_scanner_records_attendance_and_sets_up_manual_whatsapp(): void
    {
        $user = User::factory()->create();

        $stage = EducationalStage::create(['name' => 'الثانوية العامة']);
        $subject = Subject::create(['name' => 'اللغة العربية']);

        $group = Group::create([
            'name' => 'مجموعة الأوائل',
            'stage_id' => $stage->id,
            'subject_id' => $subject->id,
            'price_per_month' => 300,
            'status' => 'active',
        ]);

        $session = GroupSession::create([
            'group_id' => $group->id,
            'date' => now()->toDateString(),
            'status' => 'scheduled',
            'topic' => 'حصة النحو الأولى',
        ]);

        $student = Student::create([
            'name' => 'محمد أحمد إبراهيم',
            'phone' => '01012345678',
            'parent_phone' => '01123456789',
            'qr_code' => 'STD-XYZ999',
            'stage_id' => $stage->id,
        ]);

        $student->groups()->attach($group->id, ['joined_at' => now(), 'status' => 'active']);

        Livewire::actingAs($user)
            ->test(QrScanner::class)
            ->set('scanned_code', 'STD-XYZ999')
            ->call('processScan')
            ->assertDispatched('scan-success');

        // Verify attendance recorded
        $this->assertDatabaseHas('attendances', [
            'group_session_id' => $session->id,
            'student_id' => $student->id,
            'status' => 'present',
        ]);

        $attendance = Attendance::where('student_id', $student->id)->first();
        $this->assertNotNull($attendance);
        $this->assertNull($attendance->whatsapp_sent_at);

        // Test marking WhatsApp sent
        Livewire::actingAs($user)
            ->test(QrScanner::class)
            ->call('markWhatsAppSent', $attendance->id);

        $attendance->refresh();
        $this->assertNotNull($attendance->whatsapp_sent_at);
    }
}
