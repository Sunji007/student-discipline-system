<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Teacher;
use App\Models\TeacherAdvisoryRoom;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\Semester;
use App\Models\RolePermission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

class TeacherClassroomTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        User::flushPermissionsCache();
        \Illuminate\Support\Facades\Cache::flush();

        RolePermission::create([
            'PermissionID' => (string) Str::uuid(),
            'Role' => 'ครู',
            'ModuleName' => 'attendance',
            'CanAccess' => 1
        ]);
        RolePermission::create([
            'PermissionID' => (string) Str::uuid(),
            'Role' => 'ครู',
            'ModuleName' => 'dashboard',
            'CanAccess' => 1
        ]);

        Semester::create([
            'academic_year' => 2569,
            'term' => 1,
            'is_active' => true,
        ]);
    }

    private function createStudentWithUser(string $studentId, string $first, string $last, string $grade, string $room, int $score = 100, string $risk = 'ปกติ'): Student
    {
        $user = User::factory()->create([
            'Role' => 'นักเรียน',
            'FirstName' => $first,
            'LastName' => $last,
        ]);

        return Student::create([
            'StudentID'     => $studentId,
            'UserID'        => $user->UserID,
            'FirstName'     => $first,
            'LastName'      => $last,
            'GradeLevel'    => $grade,
            'Classroom'     => $room,
            'BehaviorScore' => $score,
            'RiskStatus'    => $risk,
        ]);
    }

    public function test_multi_room_teacher_shows_all_rooms_by_default()
    {
        $teacherUser = User::factory()->create(['Role' => 'ครู']);
        $teacher = Teacher::create([
            'TeacherID' => '20099',
            'UserID' => $teacherUser->UserID,
        ]);
        TeacherAdvisoryRoom::create(['TeacherID' => $teacher->TeacherID, 'Classroom' => '1/1']);
        TeacherAdvisoryRoom::create(['TeacherID' => $teacher->TeacherID, 'Classroom' => '1/2']);

        $this->createStudentWithUser('69101', 'นักเรียน', 'หนึ่ง', 'ม.1', '1/1', 100, 'ปกติ');
        $this->createStudentWithUser('69102', 'นักเรียน', 'สอง', 'ม.1', '1/2', 60, 'ตักเตือน');

        $response = $this->actingAs($teacherUser)
            ->get(route('teacher.classroom.index'));

        $response->assertStatus(200);
        $response->assertSee('นักเรียน หนึ่ง');
        $response->assertSee('นักเรียน สอง');
        $response->assertSee('ทุกห้องที่คุณปรึกษา');
    }

    public function test_multi_room_teacher_can_filter_by_status()
    {
        $teacherUser = User::factory()->create(['Role' => 'ครู']);
        $teacher = Teacher::create([
            'TeacherID' => '20098',
            'UserID' => $teacherUser->UserID,
        ]);
        TeacherAdvisoryRoom::create(['TeacherID' => $teacher->TeacherID, 'Classroom' => '1/1']);
        TeacherAdvisoryRoom::create(['TeacherID' => $teacher->TeacherID, 'Classroom' => '1/2']);

        $this->createStudentWithUser('69201', 'เด็กดี', 'รักเรียน', 'ม.1', '1/1', 100, 'ปกติ');
        $this->createStudentWithUser('69202', 'เด็กเกเร', 'ขาดบ่อย', 'ม.1', '1/2', 50, 'ทัณฑ์บน');

        // Filter risk: should find student2 in room 1/2 across all advisory rooms
        $responseRisk = $this->actingAs($teacherUser)
            ->get(route('teacher.classroom.index', ['status' => 'risk']));

        $responseRisk->assertStatus(200);
        $responseRisk->assertSee('เด็กเกเร');
        $responseRisk->assertDontSee('เด็กดี');

        // Filter normal: should find student1
        $responseNormal = $this->actingAs($teacherUser)
            ->get(route('teacher.classroom.index', ['status' => 'ปกติ']));

        $responseNormal->assertStatus(200);
        $responseNormal->assertSee('เด็กดี');
        $responseNormal->assertDontSee('เด็กเกเร');
    }

    public function test_attendance_id_does_not_change_on_subsequent_updates()
    {
        $teacherUser = User::factory()->create(['Role' => 'ครู']);
        $teacher = Teacher::create([
            'TeacherID' => '20097',
            'UserID' => $teacherUser->UserID,
        ]);
        TeacherAdvisoryRoom::create(['TeacherID' => $teacher->TeacherID, 'Classroom' => '1/1']);

        $student = $this->createStudentWithUser('69301', 'สมใจ', 'มาเรียน', 'ม.1', '1/1');

        // First attendance entry
        $res1 = $this->actingAs($teacherUser)->post(route('teacher.attendance.store'), [
            'date' => '2026-10-02',
            'attendance' => [$student->StudentID => 'มา'],
        ]);
        $res1->assertSessionHasNoErrors();

        $record = Attendance::where('StudentID', $student->StudentID)->where('Date', '2026-10-02')->first();
        $this->assertNotNull($record);
        $originalId = $record->AttendanceID;

        // Second attendance entry (update to 'สาย')
        $res2 = $this->actingAs($teacherUser)->post(route('teacher.attendance.store'), [
            'date' => '2026-10-02',
            'attendance' => [$student->StudentID => 'สาย'],
        ]);
        $res2->assertSessionHasNoErrors();

        $updatedRecord = Attendance::where('StudentID', $student->StudentID)->where('Date', '2026-10-02')->first();
        $this->assertEquals($originalId, $updatedRecord->AttendanceID);
        $this->assertEquals('สาย', $updatedRecord->Status);
    }
}
