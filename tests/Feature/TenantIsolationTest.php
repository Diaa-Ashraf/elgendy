<?php

namespace Tests\Feature;

use App\Models\EducationalStage;
use App\Models\Group;
use App\Models\Student;
use App\Models\Tenant;
use App\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_students_and_groups_are_strictly_isolated_between_tenants(): void
    {
        // 1. إنشاء اثنين Tenants
        $tenantA = Tenant::create([
            'name' => 'سنتر الأستاذ أحمد',
            'slug' => 'mr-ahmed',
            'email' => 'ahmed@test.com',
            'phone' => '01011111111',
            'is_active' => true,
        ]);

        $tenantB = Tenant::create([
            'name' => 'سنتر الأستاذ محمد',
            'slug' => 'mr-mohamed',
            'email' => 'mohamed@test.com',
            'phone' => '01022222222',
            'is_active' => true,
        ]);

        // 2. إنشاء بيانات للمدرس الأول (Tenant A)
        app(TenantContext::class)->set($tenantA);

        $stageA = EducationalStage::create(['name' => 'ثانوية عامة - سنتر أحمد']);
        $subjectA = \App\Models\Subject::create(['name' => 'فيزياء']);
        $groupA = Group::create([
            'name' => 'مجموعة العباقرة A',
            'stage_id' => $stageA->id,
            'subject_id' => $subjectA->id,
            'price_per_month' => 300,
            'capacity' => 30,
            'status' => 'active',
        ]);
        $studentA = Student::create([
            'name' => 'طالب سنتر أحمد',
            'phone' => '01099999991',
            'parent_phone' => '01088888881',
            'stage_id' => $stageA->id,
        ]);

        // 3. إنشاء بيانات للمدرس الثاني (Tenant B)
        app(TenantContext::class)->set($tenantB);

        $stageB = EducationalStage::create(['name' => 'ثانوية عامة - سنتر محمد']);
        $subjectB = \App\Models\Subject::create(['name' => 'كيمياء']);
        $groupB = Group::create([
            'name' => 'مجموعة الأوائل B',
            'stage_id' => $stageB->id,
            'subject_id' => $subjectB->id,
            'price_per_month' => 350,
            'capacity' => 25,
            'status' => 'active',
        ]);
        $studentB = Student::create([
            'name' => 'طالب سنتر محمد',
            'phone' => '01099999992',
            'parent_phone' => '01088888882',
            'stage_id' => $stageB->id,
        ]);

        // 4. التحقق من العزل في سياق Tenant A: يجب أن يرى فقط بياناته
        app(TenantContext::class)->set($tenantA);

        $studentsSeenByA = Student::all();
        $groupsSeenByA = Group::all();

        $this->assertCount(1, $studentsSeenByA);
        $this->assertEquals($studentA->id, $studentsSeenByA->first()->id);
        $this->assertEquals('طالب سنتر أحمد', $studentsSeenByA->first()->name);

        $this->assertCount(1, $groupsSeenByA);
        $this->assertEquals($groupA->id, $groupsSeenByA->first()->id);

        // 5. التحقق من العزل في سياق Tenant B: يجب أن يرى فقط بياناته
        app(TenantContext::class)->set($tenantB);

        $studentsSeenByB = Student::all();
        $groupsSeenByB = Group::all();

        $this->assertCount(1, $studentsSeenByB);
        $this->assertEquals($studentB->id, $studentsSeenByB->first()->id);
        $this->assertEquals('طالب سنتر محمد', $studentsSeenByB->first()->name);

        $this->assertCount(1, $groupsSeenByB);
        $this->assertEquals($groupB->id, $groupsSeenByB->first()->id);
    }
}
