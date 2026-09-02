<?php

namespace Tests\Feature;

use App\Models\EducationalStage;
use App\Models\Group;
use App\Models\Student;
use App\Models\Tenant;
use App\Services\TenantContext;
use App\Services\TenantExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;
use ZipArchive;

class AuditLogAndExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_activity_log_is_automatically_scoped_to_tenant(): void
    {
        $tenant = Tenant::create([
            'name' => 'سنتر الإبداع',
            'slug' => 'al-ebdaa',
            'email' => 'ebdaa@test.com',
            'phone' => '01000000002',
            'is_active' => true,
        ]);

        app(TenantContext::class)->set($tenant);

        $stage = EducationalStage::create(['name' => 'إعدادي']);

        // إنشاء طالب -> يجب أن يطلق نشاطاً في activity_log
        $student = Student::create([
            'name' => 'طالب تجريبي للاختبار',
            'phone' => '01012345678',
            'parent_phone' => '01087654321',
            'stage_id' => $stage->id,
        ]);

        $activity = Activity::latest()->first();

        $this->assertNotNull($activity);
        $this->assertEquals($tenant->id, $activity->tenant_id, 'Activity Log يجب أن يحمل tenant_id الخاص بالسنتر تلقائياً');
        $this->assertEquals('تم إضافة طالب جديد', $activity->description);
    }

    public function test_tenant_data_export_generates_valid_zip_with_csv_files(): void
    {
        Storage::fake('local');

        $tenant = Tenant::create([
            'name' => 'سنتر المستقبل',
            'slug' => 'al-mostaqbal',
            'email' => 'mostaqbal@test.com',
            'phone' => '01000000003',
            'is_active' => true,
        ]);

        app(TenantContext::class)->set($tenant);

        $stage = EducationalStage::create(['name' => 'ثانوي']);
        $subject = \App\Models\Subject::create(['name' => 'رياضيات']);

        Group::create([
            'name' => 'مجموعة التفوق',
            'stage_id' => $stage->id,
            'subject_id' => $subject->id,
            'price_per_month' => 400,
            'capacity' => 20,
            'status' => 'active',
        ]);

        Student::create([
            'name' => 'أحمد علي',
            'phone' => '01011111112',
            'parent_phone' => '01022222223',
            'stage_id' => $stage->id,
        ]);

        $exportService = app(TenantExportService::class);
        $zipRelativePath = $exportService->exportAll($tenant);

        $zipFullPath = storage_path("app/{$zipRelativePath}");
        $this->assertFileExists($zipFullPath);

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($zipFullPath));
        $this->assertNotFalse($zip->locateName('students.csv'));
        $this->assertNotFalse($zip->locateName('groups.csv'));
        $this->assertNotFalse($zip->locateName('payments.csv'));
        $zip->close();
    }
}
