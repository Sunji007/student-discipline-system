<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentImportController extends Controller
{
    public function show()
    {
        // Pluck existing StudentIDs and CitizenIDs for instant client-side duplicate checking
        $existingStudents = Student::with('user:UserID,Username,CitizenID,FirstName,LastName')
            ->get()
            ->map(function ($s) {
                return [
                    'student_id' => $s->StudentID,
                    'citizen_id' => $s->user?->CitizenID ?? '',
                    'name'       => $s->FullName,
                    'grade'      => $s->GradeLevel,
                    'room'       => $s->Classroom,
                ];
            });

        return view('admin.students.import', compact('existingStudents'));
    }

    public function template()
    {
        $filename = 'student_import_template.xlsx';

        $columns = [
            'รหัสนักเรียน'     => 16,
            'รหัสบัตรประชาชน' => 20,
            'คำนำหน้า'        => 12,
            'ชื่อจริง'          => 20,
            'นามสกุล'         => 20,
            'เพศ'             => 10,
            'ระดับชั้น'        => 12,
            'ห้องเรียน'        => 12,
            'เบอร์โทรศัพท์'     => 16,
        ];

        $sampleRows = [
            ['06000', '3950100069001', 'ด.ช.', 'อับดุลเลาะ', 'แวนาแว', 'ชาย', 'ม.1', '1/1', '0812345678'],
            ['06001', '3950100069002', 'ด.ญ.', 'ซารีฟา', 'ดอเลาะ', 'หญิง', 'ม.1', '1/1', '0812345679'],
            ['06002', '3950100069003', 'นาย', 'ฟุรกอน', 'มะยูโซ๊ะ', 'ชาย', 'ม.4', '4/1', '0812345680'],
        ];

        $xlsxContent = \App\Services\SimpleXlsxGenerator::create('รายชื่อนักเรียน', [], $columns, $sampleRows);

        return response($xlsxContent, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Content-Length'      => strlen($xlsxContent),
            'Cache-Control'       => 'max-age=0, must-revalidate',
            'Pragma'              => 'public',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'mode'       => 'required|in:update_and_insert,insert_only,update_only',
            'students'   => 'required|array|min:1',
            'students.*.student_id' => 'required|string|max:20',
            'students.*.first_name' => 'required|string|max:100',
            'students.*.last_name'  => 'required|string|max:100',
            'students.*.grade'      => 'required|string|max:10',
            'students.*.room'       => 'required|string|max:10',
        ]);

        $mode = $request->input('mode', 'update_and_insert');
        $rows = $request->input('students', []);

        $insertedCount = 0;
        $updatedCount  = 0;
        $skippedCount  = 0;

        DB::beginTransaction();
        try {
            foreach ($rows as $r) {
                $studentId = trim($r['student_id'] ?? '');
                if (empty($studentId)) {
                    $studentId = Student::generateNextStudentId();
                }
                $citizenId = isset($r['citizen_id']) ? trim($r['citizen_id']) : null;
                $prefix    = isset($r['prefix']) ? trim($r['prefix']) : '';
                $firstName = trim($r['first_name']);
                $lastName  = trim($r['last_name']);
                $gender    = isset($r['gender']) ? trim($r['gender']) : null;
                $grade     = trim($r['grade']);
                $room      = trim($r['room']);
                $phone     = isset($r['phone']) ? trim($r['phone']) : null;

                // Normalize Grade & Room (e.g. "ม.1", "1/1")
                if (!str_starts_with($grade, 'ม.') && is_numeric($grade)) {
                    $grade = 'ม.' . $grade;
                }
                $cleanRoom = preg_replace('/^ม\./', '', $room);

                // Find existing student by StudentID or CitizenID
                $existingStudent = Student::where('StudentID', $studentId)
                    ->orWhere(function ($q) use ($citizenId) {
                        if (!empty($citizenId)) {
                            $q->whereHas('user', fn($sub) => $sub->where('CitizenID', $citizenId));
                        }
                    })
                    ->first();

                if ($existingStudent) {
                    if ($mode === 'insert_only') {
                        $skippedCount++;
                        continue;
                    }

                    // Update existing student
                    $existingStudent->update([
                        'FirstName'  => $prefix ? ($prefix . ' ' . $firstName) : $firstName,
                        'LastName'   => $lastName,
                        'GradeLevel' => $grade,
                        'Classroom'  => $cleanRoom,
                        'Gender'     => $gender ?: $existingStudent->Gender,
                    ]);

                    if ($existingStudent->user) {
                        $existingStudent->user->update([
                            'FirstName' => $prefix ? ($prefix . ' ' . $firstName) : $firstName,
                            'LastName'  => $lastName,
                            'CitizenID' => $citizenId ?: $existingStudent->user->CitizenID,
                            'Phone'     => $phone ?: $existingStudent->user->Phone,
                            'Status'    => 'ปกติ',
                        ]);
                    }

                    $updatedCount++;
                } else {
                    if ($mode === 'update_only') {
                        $skippedCount++;
                        continue;
                    }

                    // Generate default 13-digit citizen ID if blank
                    if (empty($citizenId)) {
                        $numericId = preg_replace('/[^0-9]/', '', $studentId);
                        $citizenId = '39501' . str_pad($numericId ?: '6900', 8, '0', STR_PAD_LEFT);
                    }

                    // Create User account
                    $user = User::create([
                        'UserID'    => (string) Str::uuid(),
                        'Username'  => $studentId,
                        'CitizenID' => $citizenId,
                        'Password'  => Hash::make("Student{$studentId}"),
                        'FirstName' => $prefix ? ($prefix . ' ' . $firstName) : $firstName,
                        'LastName'  => $lastName,
                        'Role'      => 'นักเรียน',
                        'Email'     => "student_{$studentId}@yru.ac.th",
                        'Phone'     => $phone,
                        'Status'    => 'ปกติ',
                    ]);

                    // Create Student record
                    Student::create([
                        'StudentID'     => $studentId,
                        'UserID'        => $user->UserID,
                        'FirstName'     => $prefix ? ($prefix . ' ' . $firstName) : $firstName,
                        'LastName'      => $lastName,
                        'GradeLevel'    => $grade,
                        'Classroom'     => $cleanRoom,
                        'BehaviorScore' => 100,
                        'RiskStatus'    => 'ปกติ',
                        'Gender'        => $gender ?: 'ชาย',
                    ]);

                    $insertedCount++;
                }
            }

            DB::commit();

            $msg = "นำเข้าข้อมูลนักเรียนสำเร็จ! ";
            $parts = [];
            if ($insertedCount > 0) $parts[] = "เพิ่มนักเรียนใหม่: {$insertedCount} คน";
            if ($updatedCount > 0)  $parts[] = "อัปเดตข้อมูลเดิม: {$updatedCount} คน";
            if ($skippedCount > 0)  $parts[] = "ข้าม (ไม่เปลี่ยนแปลง): {$skippedCount} คน";
            $msg .= "(" . implode(', ', $parts) . ")";

            return response()->json([
                'success'  => true,
                'message'  => $msg,
                'redirect' => route('admin.students.index'),
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการนำเข้าข้อมูล: ' . $e->getMessage(),
            ], 500);
        }
    }
}
