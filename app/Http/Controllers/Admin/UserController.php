<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Teacher;
use App\Models\DisciplineStaff;
use App\Models\Student;
use App\Models\ParentGuardian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('role')) {
            $query->where('Role', $request->role);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('FirstName', 'like', '%' . $search . '%')
                  ->orWhere('LastName', 'like', '%' . $search . '%')
                  ->orWhere('Username', 'like', '%' . $search . '%')
                  ->orWhere(\DB::raw("CONCAT(FirstName, ' ', LastName)"), 'like', '%' . $search . '%');
            });
        }

        $users = $query->latest()->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    private function getNextUsernameAndId($role)
    {
        switch ($role) {
            case 'นักเรียน':
                $last = User::get()
                    ->filter(fn($u) => preg_match('/^10\d+$/', $u->Username))
                    ->sortByDesc(fn($u) => (int)$u->Username)
                    ->first();
                $nextNum = $last ? ((int)$last->Username + 1) : 10007; // Legacy max is 10006
                while (
                    User::where('Username', (string)$nextNum)->exists() ||
                    Student::where('StudentID', (string)$nextNum)->exists()
                ) {
                    $nextNum++;
                }
                return [
                    'username' => (string) $nextNum,
                    'role_id' => (string) $nextNum,
                ];

            case 'ผู้ปกครอง':
                $last = User::get()
                    ->filter(fn($u) => preg_match('/^50\d+$/', $u->Username))
                    ->sortByDesc(fn($u) => (int)$u->Username)
                    ->first();
                $nextNum = $last ? ((int)$last->Username + 1) : 50001;
                while (
                    User::where('Username', (string)$nextNum)->exists() ||
                    ParentGuardian::where('ParentID', (string)$nextNum)->exists()
                ) {
                    $nextNum++;
                }
                return [
                    'username' => (string) $nextNum,
                    'role_id' => (string) $nextNum,
                ];

            case 'ครู':
                $last = User::get()
                    ->filter(fn($u) => preg_match('/^20\d+$/', $u->Username))
                    ->sortByDesc(fn($u) => (int)$u->Username)
                    ->first();
                $nextNum = $last ? ((int)$last->Username + 1) : 20001;
                while (
                    User::where('Username', (string)$nextNum)->exists() ||
                    Teacher::where('TeacherID', (string)$nextNum)->exists()
                ) {
                    $nextNum++;
                }
                return [
                    'username' => (string) $nextNum,
                    'role_id' => (string) $nextNum,
                ];

            case 'ฝ่ายปกครอง':
                $last = User::get()
                    ->filter(fn($u) => preg_match('/^30\d+$/', $u->Username))
                    ->sortByDesc(fn($u) => (int)$u->Username)
                    ->first();
                $nextNum = $last ? ((int)$last->Username + 1) : 30001;
                while (
                    User::where('Username', (string)$nextNum)->exists() ||
                    DisciplineStaff::where('StaffID', (string)$nextNum)->exists()
                ) {
                    $nextNum++;
                }
                return [
                    'username' => (string) $nextNum,
                    'role_id' => (string) $nextNum,
                ];

            case 'ผู้ดูแลระบบ':
                $last = User::get()
                    ->filter(fn($u) => preg_match('/^40\d+$/', $u->Username))
                    ->sortByDesc(fn($u) => (int)$u->Username)
                    ->first();
                $nextNum = $last ? ((int)$last->Username + 1) : 40001;
                while (User::where('Username', (string)$nextNum)->exists()) {
                    $nextNum++;
                }
                return [
                    'username' => (string) $nextNum,
                    'role_id' => (string) $nextNum,
                ];
        }
        return ['username' => '', 'role_id' => ''];
    }

    public function create()
    {
        $nextIds = [
            'ผู้ดูแลระบบ' => $this->getNextUsernameAndId('ผู้ดูแลระบบ'),
            'ฝ่ายปกครอง' => $this->getNextUsernameAndId('ฝ่ายปกครอง'),
            'ครู' => $this->getNextUsernameAndId('ครู'),
            'นักเรียน' => $this->getNextUsernameAndId('นักเรียน'),
            'ผู้ปกครอง' => $this->getNextUsernameAndId('ผู้ปกครอง'),
        ];
        $departments = \App\Models\Department::orderBy('code')->get();
        return view('admin.users.create', compact('nextIds', 'departments'));
    }

    public function store(Request $request)
    {
        if ($request->input('Role') === 'ผู้ปกครอง') {
            $request->merge(['Username' => $request->input('CitizenID')]);
        } elseif (in_array($request->input('Role'), ['ครู', 'ฝ่ายปกครอง'])) {
            if (!$request->filled('Username') && $request->filled('FirstName_EN')) {
                $cleanEn = strtolower(preg_replace('/[^a-zA-Z]/', '', $request->input('FirstName_EN')));
                $citizenId = $request->input('CitizenID') ?? '';
                $phone = preg_replace('/\D/', '', $request->input('Phone') ?? '');
                $numSuffix = strlen($citizenId) >= 4 ? substr($citizenId, -4) : (strlen($phone) >= 4 ? substr($phone, -4) : '01');
                $request->merge(['Username' => $cleanEn . $numSuffix]);
            }
            if (!$request->filled('Password') && $request->filled('CitizenID')) {
                $request->merge(['Password' => $request->input('CitizenID')]);
            }
        }

        $validated = $request->validate([
            'Username'       => 'required|string|max:50|unique:users,Username',
            'CitizenID'      => 'required|string|digits:13|unique:users,CitizenID',
            'Password'       => 'required|string|min:8',
            'FirstName'      => 'required|string|max:50',
            'LastName'       => 'required|string|max:50',
            'FirstName_EN'   => 'required|string|max:50|regex:/^[a-zA-Z ]*$/',
            'LastName_EN'    => 'required|string|max:50|regex:/^[a-zA-Z ]*$/',
            'Phone'          => 'nullable|string|max:20|regex:/^\d{3}-\d{3}-\d{4}$/|unique:users,Phone',
            'Email'          => 'required|email:rfc,dns|max:100|unique:users,Email',
            'Role'           => 'required|in:ฝ่ายปกครอง,ผู้ดูแลระบบ,ครู,นักเรียน,ผู้ปกครอง',
            'Status'         => 'required|in:ปกติ,ระงับการใช้งาน',
            'AdditionalInfo' => 'nullable|string|max:255',
            // ฟิลด์เพิ่มเติมตาม Role
            'TeacherID'      => 'nullable|required_if:Role,ครู|string|max:10|unique:teachers,TeacherID',
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
            'Position'       => 'nullable|string|max:100',
            'Level'          => 'nullable|in:บันทึกได้,อนุมัติผล/ตั้งค่า',
            'StudentID'      => 'nullable|string|max:10',
            'GradeLevel'     => 'nullable|string|max:10',
            'Classroom'      => 'nullable|string|max:10',
        ], [
            'Username.unique'      => 'รหัสประจำตัว (Username) นี้ถูกใช้งานแล้ว',
            'CitizenID.required'   => 'กรุณากรอกรหัสบัตรประชาชน',
            'CitizenID.digits'     => 'รหัสบัตรประชาชนต้องเป็นตัวเลข 13 หลัก',
            'CitizenID.unique'     => 'รหัสบัตรประชาชนนี้ถูกใช้งานแล้ว',
            'FirstName.required'   => 'กรุณากรอกชื่อจริง',
            'LastName.required'    => 'กรุณากรอกนามสกุล',
            'FirstName_EN.required' => 'กรุณากรอกชื่อจริงภาษาอังกฤษ',
            'FirstName_EN.regex'   => 'ชื่อภาษาอังกฤษต้องเป็นตัวอักษรภาษาอังกฤษเท่านั้น',
            'LastName_EN.required'  => 'กรุณากรอกนามสกุลภาษาอังกฤษ',
            'LastName_EN.regex'    => 'นามสกุลภาษาอังกฤษต้องเป็นตัวอักษรภาษาอังกฤษเท่านั้น',
            'advisory_rooms.*.regex' => 'รูปแบบห้องเรียนไม่ถูกต้อง (ระบุ ชั้น/ห้อง เช่น 4/1, 1/2)',
            'Phone.regex'          => 'เบอร์โทรศัพท์ต้องอยู่ในรูปแบบ 111-111-1111',
            'Phone.unique'         => 'เบอร์โทรศัพท์นี้ถูกใช้งานในระบบแล้ว',
            'Email.required'       => 'กรุณากรอกอีเมล',
            'Email.email'          => 'รูปแบบอีเมลไม่ถูกต้อง หรือโดเมนอีเมลไม่มีอยู่จริง (เช่น @gmail.com)',
            'Email.unique'         => 'อีเมลนี้ถูกใช้งานแล้ว',
        ]);

        if ($request->filled('prefix') && isset($validated['FirstName'])) {
            $validated['FirstName'] = $request->prefix . $validated['FirstName'];
        }

        // Enforce Username naming conventions based on Role
        $username = $validated['Username'];
        $role = $validated['Role'];
        if ($role === 'นักเรียน' && !preg_match('/^(std\d+|\d+)$/', $username)) {
            return back()->withErrors(['Username' => 'รหัสประจำตัวนักเรียนต้องประกอบด้วยตัวเลขเท่านั้น'])->withInput();
        }
        if ($role === 'ผู้ปกครอง' && !preg_match('/^(prt\d+|\d+)$/', $username)) {
            return back()->withErrors(['Username' => 'รหัสประจำตัวผู้ปกครองต้องประกอบด้วยตัวเลขเท่านั้น'])->withInput();
        }
        if ($role === 'ครู' && !preg_match('/^[a-zA-Z0-9._-]+$/', $username)) {
            return back()->withErrors(['Username' => 'รหัสประจำตัวครูต้องเป็นตัวอักษรภาษาอังกฤษ ตัวเลข หรือเครื่องหมาย . - _ เท่านั้น'])->withInput();
        }
        if ($role === 'ฝ่ายปกครอง' && !preg_match('/^[a-zA-Z0-9._-]+$/', $username)) {
            return back()->withErrors(['Username' => 'รหัสประจำตัวฝ่ายปกครองต้องเป็นตัวอักษรภาษาอังกฤษ ตัวเลข หรือเครื่องหมาย . - _ เท่านั้น'])->withInput();
        }
        if ($role === 'ผู้ดูแลระบบ' && !preg_match('/^(adm\d+|\d+|[a-zA-Z0-9._-]+)$/', $username)) {
            return back()->withErrors(['Username' => 'รหัสประจำตัวผู้ดูแลระบบไม่ถูกต้อง'])->withInput();
        }

        $user = User::create([
            'UserID'         => Str::uuid(),
            'Username'       => $validated['Username'],
            'CitizenID'      => $validated['CitizenID'],
            'Password'       => Hash::make($validated['Password']),
            'FirstName'      => $validated['FirstName'],
            'LastName'       => $validated['LastName'],
            'FirstName_EN'   => $validated['FirstName_EN'] ?? null,
            'LastName_EN'    => $validated['LastName_EN'] ?? null,
            'Phone'          => $validated['Phone'] ?? null,
            'Email'          => $validated['Email'] ?? null,
            'Role'           => $validated['Role'],
            'Status'         => $validated['Status'],
            'AdditionalInfo' => $validated['AdditionalInfo'] ?? null,
        ]);

        $classroom = $validated['Classroom'] ?? null;
        if ($classroom && !str_contains($classroom, '/')) {
            $classroom = ($validated['GradeLevel'] ?? '') . '/' . $classroom;
        }

        // สร้าง profile ตาม Role
        match ($validated['Role']) {
            'ครู' => (function() use ($user, $validated, $request) {
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
                return $teacher;
            })(),
            'ฝ่ายปกครอง' => DisciplineStaff::create([
                'StaffID'  => Str::uuid(),
                'UserID'   => $user->UserID,
                'Position' => $validated['Position'] ?? null,
                'Level'    => $validated['Level'] ?? 'บันทึกได้',
            ]),
            'นักเรียน' => Student::create([
                'StudentID'    => $validated['StudentID'],
                'UserID'       => $user->UserID,
                'FirstName'    => $validated['FirstName'],
                'LastName'     => $validated['LastName'],
                'GradeLevel'   => $validated['GradeLevel'] ?? null,
                'Classroom'    => $classroom,
                'BehaviorScore'=> 100,
                'RiskStatus'   => 'ปกติ',
            ]),
            default => null,
        };

        return redirect()->route('admin.users.index')
            ->with('success', 'เพิ่มผู้ใช้งานเรียบร้อยแล้ว');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {

        $validated = $request->validate([
            'FirstName'      => 'required|string|max:50',
            'LastName'       => 'required|string|max:50',
            'FirstName_EN'   => 'required|string|max:50|regex:/^[a-zA-Z ]*$/',
            'LastName_EN'    => 'required|string|max:50|regex:/^[a-zA-Z ]*$/',
            'Phone'          => 'nullable|string|max:20|regex:/^\d{3}-\d{3}-\d{4}$/|unique:users,Phone,' . $user->UserID . ',UserID',
            'Email'          => 'required|email:rfc,dns|max:100|unique:users,Email,' . $user->UserID . ',UserID',
            'CitizenID'      => 'required|string|digits:13|unique:users,CitizenID,' . $user->UserID . ',UserID',
            'Role'           => 'required|in:ฝ่ายปกครอง,ผู้ดูแลระบบ,ครู,นักเรียน,ผู้ปกครอง',
            'Status'         => 'required|in:ปกติ,ระงับการใช้งาน',
            'AdditionalInfo' => 'nullable|string|max:255',
            'Password'       => 'nullable|string|min:8',
            // Profile fields
            'Department'     => 'nullable|string|max:100',
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
            'Position'       => 'nullable|string|max:100',
            'Level'          => 'nullable|in:บันทึกได้,อนุมัติผล/ตั้งค่า',
            'StudentID'      => 'nullable|string|max:10',
            'GradeLevel'     => 'nullable|string|max:10',
            'Classroom'      => 'nullable|string|max:10',
        ], [
            'FirstName.required'  => 'กรุณากรอกชื่อจริง',
            'LastName.required'   => 'กรุณากรอกนามสกุล',
            'CitizenID.required'  => 'กรุณากรอกรหัสบัตรประชาชน',
            'CitizenID.digits'    => 'รหัสบัตรประชาชนต้องเป็นตัวเลข 13 หลัก',
            'CitizenID.unique'    => 'รหัสบัตรประชาชนนี้ถูกใช้งานแล้ว',
            'advisory_rooms.*.regex' => 'รูปแบบห้องเรียนไม่ถูกต้อง (ระบุ ชั้น/ห้อง เช่น 4/1, 1/2)',
            'FirstName_EN.required' => 'กรุณากรอกชื่อจริงภาษาอังกฤษ',
            'FirstName_EN.regex'  => 'ชื่อภาษาอังกฤษต้องเป็นตัวอักษรภาษาอังกฤษเท่านั้น',
            'LastName_EN.required' => 'กรุณากรอกนามสกุลภาษาอังกฤษ',
            'LastName_EN.regex'   => 'นามสกุลภาษาอังกฤษต้องเป็นตัวอักษรภาษาอังกฤษเท่านั้น',
            'Phone.regex'         => 'เบอร์โทรศัพท์ต้องอยู่ในรูปแบบ 111-111-1111',
            'Phone.unique'        => 'เบอร์โทรศัพท์นี้ถูกใช้งานในระบบแล้ว',
            'Email.required'      => 'กรุณากรอกอีเมล',
            'Email.email'         => 'รูปแบบอีเมลไม่ถูกต้อง หรือโดเมนอีเมลไม่มีอยู่จริง (เช่น @gmail.com)',
            'Email.unique'        => 'อีเมลนี้ถูกใช้งานแล้ว',
            'Password.min'         => 'รหัสผ่านต้องมีความยาวอย่างน้อย 8 ตัวอักษร',
            'Password.letters'     => 'รหัสผ่านต้องมีตัวอักษรภาษาอังกฤษอย่างน้อย 1 ตัว',
            'Password.mixed'       => 'รหัสผ่านต้องมีทั้งตัวพิมพ์เล็กและตัวพิมพ์ใหญ่ (A-Z และ a-z)',
            'Password.numbers'     => 'รหัสผ่านต้องมีตัวเลขอย่างน้อย 1 ตัว (0-9)',
            'Password.symbols'     => 'รหัสผ่านต้องมีอักขระพิเศษอย่างน้อย 1 ตัว (เช่น @, #, $, !)',
        ]);

        if ($request->filled('prefix') && isset($validated['FirstName'])) {
            $validated['FirstName'] = $request->prefix . $validated['FirstName'];
        }

        $classroom = $validated['Classroom'] ?? null;
        if ($classroom && !str_contains($classroom, '/')) {
            $classroom = ($validated['GradeLevel'] ?? '') . '/' . $classroom;
        }

        $user->FirstName      = $validated['FirstName'];
        $user->LastName       = $validated['LastName'];
        $user->FirstName_EN   = $validated['FirstName_EN'] ?? $user->FirstName_EN;
        $user->LastName_EN    = $validated['LastName_EN'] ?? $user->LastName_EN;
        $user->Phone          = $validated['Phone'] ?? $user->Phone;
        $user->Email          = $validated['Email'] ?? $user->Email;
        $user->CitizenID      = $validated['CitizenID'];
        $user->Role           = $validated['Role'];
        $user->Status         = $validated['Status'];
        $user->AdditionalInfo = $validated['AdditionalInfo'] ?? null;

        if (!empty($validated['Password'])) {
            $user->Password = Hash::make($validated['Password']);
        }

        if ($validated['Role'] === 'ผู้ปกครอง') {
            $exists = User::where('Username', $validated['CitizenID'])
                ->where('UserID', '!=', $user->UserID)
                ->exists();
            if ($exists) {
                return back()->withErrors(['CitizenID' => 'เลขบัตรประชาชนนี้ถูกใช้งานเป็นชื่อผู้ใช้งานในระบบแล้ว'])->withInput();
            }
            $user->Username = $validated['CitizenID'];
        }

        $user->save();

        $additionalRoles = $request->input('additional_roles', []);

        // Update or create role-specific profile
        if (in_array($user->Role, ['ครู', 'ฝ่ายปกครอง', 'ผู้ดูแลระบบ']) || in_array('ครู', $additionalRoles)) {
            $teacher = $user->teacher()->updateOrCreate(
                ['UserID' => $user->UserID],
                [
                    'TeacherID'  => optional($user->teacher)->TeacherID ?? substr($user->Username, 0, 10),
                    'Department' => $request->has('Department') ? ($validated['Department'] ?? null) : optional($user->teacher)->Department,
                ]
            );
            if ($request->has('advisory_rooms')) {
                $teacher->advisoryRooms()->delete();
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
            }
        } else {
            $user->teacher()->delete();
        }

        if ($user->Role === 'ฝ่ายปกครอง' || in_array('ฝ่ายปกครอง', $additionalRoles)) {
            $user->disciplineOfficer()->updateOrCreate(
                ['UserID' => $user->UserID],
                [
                    'StaffID'  => optional($user->disciplineOfficer)->StaffID ?? (string) Str::uuid(),
                    'Position' => $request->has('Position') ? ($validated['Position'] ?? null) : (optional($user->disciplineOfficer)->Position ?? 'เจ้าหน้าที่ฝ่ายปกครอง'),
                    'Level'    => $request->has('Level') ? ($validated['Level'] ?? 'บันทึกได้') : (optional($user->disciplineOfficer)->Level ?? 'บันทึกได้'),
                ]
            );
        } else {
            if ($user->Role !== 'ผู้ดูแลระบบ') {
                $user->disciplineOfficer()->delete();
            }
        }

        if ($user->Role === 'ผู้ปกครอง' || in_array('ผู้ปกครอง', $additionalRoles)) {
            $user->parentGuardian()->updateOrCreate(
                ['UserID' => $user->UserID],
                [
                    'ParentID'     => optional($user->parentGuardian)->ParentID ?? (string) Str::uuid(),
                    'FirstName'    => $validated['FirstName'],
                    'LastName'     => $validated['LastName'],
                    'Phone'        => $validated['Phone'] ?? optional($user->parentGuardian)->Phone,
                    'Relationship' => optional($user->parentGuardian)->Relationship ?? 3,
                ]
            );
        } else {
            if ($user->Role !== 'ผู้ปกครอง' && $user->parentStudents()->count() === 0) {
                $user->parentGuardian()->delete();
            }
        }

        if ($user->Role === 'นักเรียน') {
            $user->student()->updateOrCreate(
                ['UserID' => $user->UserID],
                [
                    'StudentID'    => $validated['StudentID'] ?? (optional($user->student)->StudentID ?? $user->Username),
                    'FirstName'    => $validated['FirstName'],
                    'LastName'     => $validated['LastName'],
                    'GradeLevel'   => $validated['GradeLevel'] ?? optional($user->student)->GradeLevel,
                    'Classroom'    => $classroom ?? optional($user->student)->Classroom,
                    'BehaviorScore'=> optional($user->student)->BehaviorScore ?? 100,
                    'RiskStatus'   => optional($user->student)->RiskStatus ?? 'ปกติ',
                ]
            );
        } else {
            $user->student()->delete();
        }
        if ($user->Role !== 'นักเรียน') {
            $user->student()->delete();
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'แก้ไขข้อมูลผู้ใช้เรียบร้อยแล้ว');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'ลบผู้ใช้งานเรียบร้อยแล้ว');
    }

    public function checkCitizenID(Request $request)
    {
        $request->validate([
            'CitizenID' => 'required|string',
            'UserID'    => 'nullable|string'
        ]);

        $query = \App\Models\User::where('CitizenID', $request->CitizenID);
        if ($request->filled('UserID')) {
            $query->where('UserID', '!=', $request->UserID);
        }

        $user = $query->first();

        return response()->json([
            'exists'  => $user !== null,
            'message' => $user ? 'รหัสบัตรประชาชนนี้ถูกใช้งานแล้วโดย ' . $user->FullName : ''
        ]);
    }

    public function checkPhone(Request $request)
    {
        $request->validate([
            'Phone'  => 'required|string',
            'UserID' => 'nullable|string'
        ]);

        $query = \App\Models\User::where('Phone', $request->Phone);
        if ($request->filled('UserID')) {
            $query->where('UserID', '!=', $request->UserID);
        }

        $user = $query->first();

        return response()->json([
            'exists'  => $user !== null,
            'message' => $user ? 'เบอร์โทรศัพท์นี้ถูกใช้งานแล้วโดย ' . $user->FullName : ''
        ]);
    }

    public function checkEmail(Request $request)
    {
        $request->validate([
            'Email'  => 'required|string',
            'UserID' => 'nullable|string'
        ]);

        $query = \App\Models\User::where('Email', $request->Email);
        if ($request->filled('UserID')) {
            $query->where('UserID', '!=', $request->UserID);
        }

        $user = $query->first();

        return response()->json([
            'exists'  => $user !== null,
            'message' => $user ? 'อีเมลนี้ถูกใช้งานแล้วโดย ' . $user->FullName : ''
        ]);
    }
}