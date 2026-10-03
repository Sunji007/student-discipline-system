<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Student;
use App\Models\PrayerRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

class PrayerStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_total_active_sessions_counts_distinct_periods_not_all_student_rows()
    {
        $user = User::factory()->create(['Role' => 'นักเรียน']);

        // Create 2 students
        $student1 = Student::create([
            'StudentID'     => '06000',
            'UserID'        => $user->UserID,
            'FirstName'     => 'สมชาย',
            'LastName'      => 'ใจดี',
            'GradeLevel'    => 'ม.1',
            'Classroom'     => '1/1',
            'BehaviorScore' => 100,
            'RiskStatus'    => 'ปกติ',
        ]);

        $user2 = User::factory()->create(['Role' => 'นักเรียน']);
        $student2 = Student::create([
            'StudentID'     => '06001',
            'UserID'        => $user2->UserID,
            'FirstName'     => 'สมหญิง',
            'LastName'      => 'รักเรียน',
            'GradeLevel'    => 'ม.1',
            'Classroom'     => '1/1',
            'BehaviorScore' => 100,
            'RiskStatus'    => 'ปกติ',
        ]);

        // Create records on 2026-09-01 for 2 periods (ซุฮรี and อัศรี) for both students
        // Total rows in prayer_records = 4 rows, but distinct sessions = 2 sessions
        PrayerRecord::create([
            'PrayerRecordID' => (string) Str::uuid(),
            'StudentID'      => $student1->StudentID,
            'RecordDate'     => '2026-09-01',
            'RecordTime'     => '12:30:00',
            'Period'         => 'ซุฮรี',
            'Status'         => 'ละหมาดแล้ว',
            'RecordedBy'     => $user->UserID,
        ]);

        PrayerRecord::create([
            'PrayerRecordID' => (string) Str::uuid(),
            'StudentID'      => $student2->StudentID,
            'RecordDate'     => '2026-09-01',
            'RecordTime'     => '12:30:00',
            'Period'         => 'ซุฮรี',
            'Status'         => 'ละหมาดแล้ว',
            'RecordedBy'     => $user->UserID,
        ]);

        PrayerRecord::create([
            'PrayerRecordID' => (string) Str::uuid(),
            'StudentID'      => $student1->StudentID,
            'RecordDate'     => '2026-09-01',
            'RecordTime'     => '15:30:00',
            'Period'         => 'อัศรี',
            'Status'         => 'ละหมาดแล้ว',
            'RecordedBy'     => $user->UserID,
        ]);

        PrayerRecord::create([
            'PrayerRecordID' => (string) Str::uuid(),
            'StudentID'      => $student2->StudentID,
            'RecordDate'     => '2026-09-01',
            'RecordTime'     => '15:30:00',
            'Period'         => 'อัศรี',
            'Status'         => 'ละหมาดแล้ว',
            'RecordedBy'     => $user->UserID,
        ]);

        $status = $student1->getPrayerMonthlyStatus(9, 2026);

        // There were 2 distinct sessions, student attended both
        $this->assertEquals(2, $status['total_sessions'], 'Total sessions must be distinct (RecordDate, Period), not total rows');
        $this->assertEquals(2, $status['prayed_count']);
        $this->assertEquals(0, $status['absent_count']);
        $this->assertEquals(100.0, $status['percentage']);
        $this->assertEquals('pass', $status['status']);
    }
}
