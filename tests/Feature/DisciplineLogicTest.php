<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Student;
use App\Models\Appeal;
use App\Models\BehaviorRecord;
use App\Models\BehaviorRule;
use App\Models\Semester;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

class DisciplineLogicTest extends TestCase
{
    use RefreshDatabase;

    public function test_appeal_cannot_be_resolved_twice()
    {
        $disciplineUser = User::factory()->create(['Role' => 'ฝ่ายปกครอง']);
        $studentUser = User::factory()->create(['Role' => 'นักเรียน']);
        
        $student = Student::create([
            'StudentID'     => '6910101',
            'UserID'        => $studentUser->UserID,
            'FirstName'     => 'สมชาย',
            'LastName'      => 'ใจดี',
            'GradeLevel'    => 'ม.1',
            'Classroom'     => '1/1',
            'BehaviorScore' => 80,
            'RiskStatus'    => 'ปกติ',
        ]);

        $rule = BehaviorRule::create([
            'RuleID'        => (string) Str::uuid(),
            'RuleType'      => 'ตัดคะแนน',
            'Category'      => 'วินัย',
            'RuleName'      => 'มาสาย',
            'ScoreModifier' => -5,
        ]);

        $record = BehaviorRecord::create([
            'RecordID'    => (string) Str::uuid(),
            'StudentID'   => $student->StudentID,
            'RuleID'      => $rule->RuleID,
            'RecordedBy'  => $disciplineUser->UserID,
            'RecordDate'  => now()->toDateString(),
            'Status'      => 'อยู่ในระหว่างยื่นอุทธรณ์',
        ]);

        $appeal = Appeal::create([
            'AppealID'   => (string) Str::uuid(),
            'RecordID'   => $record->RecordID,
            'StudentID'  => $student->StudentID,
            'Reason'     => 'ติดธุระจำเป็น',
            'Status'     => 'รอตรวจสอบ',
            'AppealDate' => now(),
        ]);

        \App\Models\RolePermission::create([
            'PermissionID' => (string) Str::uuid(),
            'Role'         => 'ฝ่ายปกครอง',
            'ModuleName'   => 'appeals',
            'CanAccess'    => 1,
        ]);

        // First resolution: success
        $response1 = $this->actingAs($disciplineUser)
            ->patch(route('discipline.appeals.resolve', $appeal->AppealID), [
                'action' => 'คืนคะแนน',
                'restored_points' => 5,
            ]);

        $response1->assertRedirect(route('discipline.appeals.index'));
        $appeal->refresh();
        $student->refresh();
        $this->assertEquals('คืนคะแนน', $appeal->Status);
        $this->assertEquals(85, $student->BehaviorScore);

        // Second resolution attempt: rejected
        $response2 = $this->actingAs($disciplineUser)
            ->patch(route('discipline.appeals.resolve', $appeal->AppealID), [
                'action' => 'คืนคะแนน',
                'restored_points' => 5,
            ]);

        $response2->assertSessionHas('error');
        $student->refresh();
        // Points should NOT be added again
        $this->assertEquals(85, $student->BehaviorScore);
    }

    public function test_activating_new_academic_year_triggers_promotion()
    {
        $adminUser = User::factory()->create(['Role' => 'ผู้ดูแลระบบ']);

        // Year 2569
        $sem2569 = Semester::create([
            'academic_year' => 2569,
            'term'          => 1,
            'is_active'     => true,
        ]);

        $studentUser = User::factory()->create(['Role' => 'นักเรียน']);
        $student = Student::create([
            'StudentID'     => '6910102',
            'UserID'        => $studentUser->UserID,
            'FirstName'     => 'สมหญิง',
            'LastName'      => 'รักเรียน',
            'GradeLevel'    => 'ม.1',
            'Classroom'     => '1/1',
            'BehaviorScore' => 100,
            'RiskStatus'    => 'ปกติ',
        ]);

        // Admin creates and activates year 2570 term 1
        $response = $this->actingAs($adminUser)
            ->post(route('admin.semesters.store'), [
                'academic_year' => 2570,
                'term'          => 1,
                'is_active'     => '1',
            ]);

        $response->assertRedirect();
        $student->refresh();

        // Student should be promoted to ม.2
        $this->assertEquals('ม.2', $student->GradeLevel);
        $this->assertEquals('2/1', $student->Classroom);
    }

    public function test_clearing_cache_and_reactivating_year_does_not_double_promote()
    {
        $adminUser = User::factory()->create(['Role' => 'ผู้ดูแลระบบ']);

        // Year 2569
        $sem2569 = Semester::create([
            'academic_year' => 2569,
            'term'          => 1,
            'is_active'     => true,
        ]);

        $studentUser = User::factory()->create(['Role' => 'นักเรียน']);
        $student = Student::create([
            'StudentID'     => '6910103',
            'UserID'        => $studentUser->UserID,
            'FirstName'     => 'สมศักดิ์',
            'LastName'      => 'คงที่',
            'GradeLevel'    => 'ม.1',
            'Classroom'     => '1/1',
            'BehaviorScore' => 100,
            'RiskStatus'    => 'ปกติ',
        ]);

        // First: Admin creates and activates year 2570 term 1
        $this->actingAs($adminUser)->post(route('admin.semesters.store'), [
            'academic_year' => 2570,
            'term'          => 1,
            'is_active'     => '1',
        ]);

        $student->refresh();
        $this->assertEquals('ม.2', $student->GradeLevel);
        $this->assertEquals('2/1', $student->Classroom);

        // Simulate cache being cleared completely!
        \Illuminate\Support\Facades\Artisan::call('cache:clear');

        // Admin re-activates year 2570 term 1
        $sem2570 = Semester::where('academic_year', 2570)->where('term', 1)->first();
        $this->actingAs($adminUser)->post(route('admin.semesters.set-active', $sem2570->semester_id));

        $student->refresh();
        // Student MUST STILL BE ม.2, NOT ม.3!
        $this->assertEquals('ม.2', $student->GradeLevel);
        $this->assertEquals('2/1', $student->Classroom);
    }

    public function test_auto_promote_command_exits_cleanly_when_already_promoted()
    {
        // Year 2569 is active
        Semester::create([
            'academic_year' => 2569,
            'term'          => 1,
            'is_active'     => true,
        ]);

        // Ensure year 2569 is marked as promoted
        \App\Models\StudentPromotion::firstOrCreate(
            ['academic_year' => 2569],
            ['promoted_at' => now()]
        );

        // Run command: students:auto-promote
        $this->artisan('students:auto-promote')
            ->expectsOutputToContain('ปีนี้ประมวลผลแล้ว')
            ->assertExitCode(0);
    }
}
