<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $query = Teacher::query()->with(['user', 'department']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('FirstName', 'like', '%' . $search . '%')
                       ->orWhere('LastName', 'like', '%' . $search . '%')
                       ->orWhere(\DB::raw("CONCAT(FirstName, ' ', LastName)"), 'like', '%' . $search . '%');
                })
                ->orWhere('TeacherID', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('department')) {
            $query->where('department_id', $request->department);
        }

        $teachers = $query->paginate(15);
        $departments = \App\Models\Department::orderBy('code')->get();

        return view('admin.teachers.index', compact('teachers', 'departments'));
    }

    public function create()
    {
        $lastTeacher = Teacher::get()
            ->filter(fn($t) => is_numeric($t->TeacherID) && str_starts_with($t->TeacherID, '20'))
            ->sortByDesc(fn($t) => (int)$t->TeacherID)
            ->first();
        $nextTeacherId = $lastTeacher ? ((int)$lastTeacher->TeacherID + 1) : 20001;
        $departments = \App\Models\Department::orderBy('code')->get();

        return view('admin.teachers.create', compact('nextTeacherId', 'departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'TeacherID'      => 'required|string|max:10|unique:teachers,TeacherID|regex:/^\d+$/',
            'CitizenID'      => 'required|string|digits:13|unique:users,CitizenID',
            'FirstName'      => 'required|string|max:50',
            'LastName'       => 'required|string|max:50',
            'department_id'  => 'nullable|exists:departments,department_id',
            'advisory_rooms'   => [
                'nullable',
                'array',
                function ($attribute, $value, $fail) {
                    $cleaned = array_filter(array_map(function($room) {
                        return preg_replace('/^ม\./', '', trim($room));
                    }, $value));
                    if (count($cleaned) !== count(array_unique($cleaned))) {
                        $fail('ห้องเรียนที่ปรึกษาต้องไม่ซ้ำกัน');
                    }
                }
            ],
            'advisory_rooms.*' => 'nullable|string|max:15|regex:/^(ม\.)?\s*\d+\s*[\/\-]\s*\d+$/',
        ], [
            'TeacherID.unique' => 'รหัสครูนี้มีอยู่ในระบบแล้ว',
            'TeacherID.regex'  => 'รหัสครูต้องระบุในรูปแบบ ตัวเลขกลุ่มสาระและระดับชั้นห้องที่ปรึกษา (เช่น 02401)',
            'CitizenID.required' => 'กรุณากรอกรหัสบัตรประชาชน',
            'CitizenID.digits'   => 'รหัสบัตรประชาชนต้องเป็นตัวเลข 13 หลัก',
            'CitizenID.unique'   => 'รหัสบัตรประชาชนนี้ถูกใช้งานแล้ว',
            'advisory_rooms.*.regex' => 'รูปแบบห้องเรียนไม่ถูกต้อง (ระบุ ชั้น/ห้อง เช่น 4/1, 1/2)',
        ]);

        if ($request->filled('prefix') && isset($validated['FirstName'])) {
            $validated['FirstName'] = $request->prefix . $validated['FirstName'];
        }

        $tchUsername = $validated['TeacherID'];

        // Create User Account first
        $user = User::create([
            'UserID'    => (string) Str::uuid(),
            'Username'  => $tchUsername,
            'CitizenID' => $validated['CitizenID'],
            'Password'  => Hash::make("Teacher" . $tchUsername), // e.g. Teacher20001
            'FirstName' => $validated['FirstName'],
            'LastName'  => $validated['LastName'],
            'Role'      => 'ครู',
            'Status'    => 'ปกติ',
        ]);

        $teacher = Teacher::create([
            'TeacherID'     => $validated['TeacherID'],
            'UserID'        => $user->UserID,
            'department_id' => $validated['department_id'] ?? null,
        ]);

        $rooms = array_filter(array_map('trim', $request->input('advisory_rooms', [])));
        $rooms = array_map(function($room) {
            return preg_replace('/^ม\./', '', $room);
        }, $rooms);
        $rooms = array_unique($rooms);

        foreach ($rooms as $room) {
            $teacher->advisoryRooms()->create([
                'Classroom' => $room
            ]);
        }

        return redirect()->route('admin.teachers.index')
            ->with('success', 'เพิ่มข้อมูลครูเรียบร้อยแล้ว (ชื่อผู้ใช้คือ ' . $tchUsername . ' รหัสผ่านเริ่มต้นคือ Teacher' . substr($tchUsername, 3) . ')');
    }

    public function edit($id)
    {
        $teacher = Teacher::findOrFail($id);
        $departments = \App\Models\Department::orderBy('code')->get();
        return view('admin.teachers.edit', compact('teacher', 'departments'));
    }

    public function update(Request $request, $id)
    {
        $teacher = Teacher::findOrFail($id);

        $validated = $request->validate([
            'FirstName'        => 'required|string|max:50',
            'LastName'         => 'required|string|max:50',
            'CitizenID'        => 'required|string|digits:13|unique:users,CitizenID,' . $teacher->UserID . ',UserID',
            'department_id'    => 'nullable|exists:departments,department_id',
            'advisory_rooms'   => [
                'nullable',
                'array',
                function ($attribute, $value, $fail) {
                    $cleaned = array_filter(array_map(function($room) {
                        return preg_replace('/^ม\./', '', trim($room));
                    }, $value));
                    if (count($cleaned) !== count(array_unique($cleaned))) {
                        $fail('ห้องเรียนที่ปรึกษาต้องไม่ซ้ำกัน');
                    }
                }
            ],
            'advisory_rooms.*' => 'nullable|string|max:15|regex:/^(ม\.)?\s*\d+\s*[\/\-]\s*\d+$/',
        ], [
            'CitizenID.required' => 'กรุณากรอกรหัสบัตรประชาชน',
            'CitizenID.digits'   => 'รหัสบัตรประชาชนต้องเป็นตัวเลข 13 หลัก',
            'CitizenID.unique'   => 'รหัสบัตรประชาชนนี้ถูกใช้งานแล้ว',
            'advisory_rooms.*.regex' => 'รูปแบบห้องเรียนไม่ถูกต้อง (ระบุ ชั้น/ห้อง เช่น 4/1, 1/2)',
        ]);

        if ($request->filled('prefix') && isset($validated['FirstName'])) {
            $validated['FirstName'] = $request->prefix . $validated['FirstName'];
        }

        $teacher->department_id = $validated['department_id'] ?? null;
        $teacher->save();

        // Delete old advisory rooms
        $teacher->advisoryRooms()->delete();

        // Save new advisory rooms
        $rooms = array_filter(array_map('trim', $request->input('advisory_rooms', [])));
        $rooms = array_map(function($room) {
            return preg_replace('/^ม\./', '', $room);
        }, $rooms);
        $rooms = array_unique($rooms);

        foreach ($rooms as $room) {
            $teacher->advisoryRooms()->create([
                'Classroom' => $room
            ]);
        }

        if ($teacher->user) {
            $teacher->user->FirstName = $validated['FirstName'];
            $teacher->user->LastName  = $validated['LastName'];
            $teacher->user->CitizenID = $validated['CitizenID'];
            $teacher->user->save();
        }

        return redirect()->route('admin.teachers.index')
            ->with('success', 'แก้ไขข้อมูลครูเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $teacher = Teacher::findOrFail($id);

        // Delete associated user account
        if ($teacher->user) {
            $teacher->user->delete();
        } else {
            $teacher->delete();
        }

        return redirect()->route('admin.teachers.index')
            ->with('success', 'ลบข้อมูลครูเรียบร้อยแล้ว');
    }

    public function getNextId(Request $request)
    {
        $department = $request->query('department');
        $classroom = $request->query('classroom');

        if (!$department || !$classroom) {
            return response()->json(['TeacherID' => '']);
        }

        $dept = null;
        if (is_numeric($department)) {
            $dept = \App\Models\Department::find($department);
        } else {
            $dept = \App\Models\Department::where('short_name', $department)->first();
        }

        $deptCode = $dept ? $dept->code : '00';

        $classroomClean = preg_replace('/^ม\./', '', trim($classroom));
        $match = [];
        preg_match('/(\d+)\s*[\/\-]\s*(\d+)/', $classroomClean, $match);
        
        $roomCode = '000';
        if (count($match) >= 3) {
            $grade = $match[1];
            $room = str_pad($match[2], 2, '0', STR_PAD_LEFT);
            $roomCode = $grade . $room;
        }

        $prefix = $deptCode . $roomCode;

        $teachers = Teacher::where('TeacherID', 'like', $prefix . '%')->get();

        $sequences = [0];
        foreach ($teachers as $t) {
            $id = $t->TeacherID;
            if (strlen($id) === 5) {
                $sequences[] = 1;
            } elseif (strlen($id) === 7 && str_starts_with($id, $prefix)) {
                $seqVal = (int) substr($id, 5, 2);
                $sequences[] = $seqVal;
            }
        }

        $nextSeq = max($sequences) + 1;
        $nextTeacherId = $prefix . str_pad($nextSeq, 2, '0', STR_PAD_LEFT);

        return response()->json(['TeacherID' => $nextTeacherId]);
    }
}
