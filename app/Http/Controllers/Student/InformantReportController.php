<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\InformantReport;
use App\Models\Student;
use Illuminate\Http\Request;

class InformantReportController extends Controller
{
    public function index()
    {
        $role = strtolower(auth()->user()->Role);
        
        // Find reports submitted by the logged in user
        $reports = InformantReport::with(['student'])
            ->where('ReporterID', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Map layout according to role
        $layoutPrefix = match($role) {
            'student' => 'student',
            'teacher' => 'teacher',
            'parent' => 'parent',
            default => 'student'
        };

        return view('student.informant-reports.index', compact('reports', 'layoutPrefix'));
    }

    public function create()
    {
        $students = Student::orderBy('FirstName')->orderBy('LastName')->get();
        $role = strtolower(auth()->user()->Role);
        $layoutPrefix = match($role) {
            'student' => 'student',
            'teacher' => 'teacher',
            'parent' => 'parent',
            default => 'student'
        };

        return view('student.informant-reports.create', compact('students', 'layoutPrefix'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'Title' => 'required|string|max:100',
            'Category' => 'required|string|max:50',
            'Description' => 'required|string',
            'StudentID' => 'nullable|string|max:1000',
            'evidence' => 'nullable',
            'evidence.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp,heic,heif,gif,bmp|max:20480',
            'IsAnonymous' => 'nullable|boolean',
        ], [
            'Title.required' => 'กรุณากรอกหัวข้อเบาะแส',
            'Category.required' => 'กรุณาเลือกประเภทพฤติกรรม',
            'Description.required' => 'กรุณากรอกรายละเอียดเบาะแส',
            'evidence.*.mimes' => 'ไฟล์หลักฐานต้องเป็นประเภท PDF, JPG, JPEG, PNG หรือ WEBP เท่านั้น',
            'evidence.*.max' => 'ขนาดไฟล์หลักฐานต้องไม่เกิน 20MB แต่ละไฟล์',
        ]);

        if ($request->filled('StudentID')) {
            $rawTokens = array_filter(array_map('trim', preg_split('/[\s,;]+/', $request->input('StudentID'))));
            if (!empty($rawTokens)) {
                $myStudent = \App\Models\Student::where('UserID', auth()->id())->first();
                $myStudentId = $myStudent ? $myStudent->StudentID : null;

                if ($myStudentId && in_array($myStudentId, $rawTokens)) {
                    return back()->withInput()->withErrors([
                        'StudentID' => 'ไม่สามารถระบุรหัสนักเรียนของตนเอง (' . $myStudentId . ') ในรายการแจ้งเบาะแสได้'
                    ]);
                }
            }
        }

        $role = strtolower(auth()->user()->Role);
        $isAnonymous = $request->has('IsAnonymous') && $request->input('IsAnonymous') == 1;

        $evidencePaths = [];
        if ($request->hasFile('evidence')) {
            $files = is_array($request->file('evidence')) ? $request->file('evidence') : [$request->file('evidence')];
            foreach ($files as $file) {
                if ($file && $file->isValid()) {
                    $savedPath = null;
                    try {
                        $savedPath = $file->store('informant_reports/evidence', 'public');
                    } catch (\Throwable $e1) {
                        try {
                            $publicDir = public_path('uploads/informant-evidence');
                            if (!file_exists($publicDir)) {
                                @mkdir($publicDir, 0777, true);
                            }
                            $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
                            $filename = time() . '_' . \Illuminate\Support\Str::random(8) . '.' . $ext;
                            if (@move_uploaded_file($file->getPathname(), $publicDir . '/' . $filename)) {
                                $savedPath = 'uploads/informant-evidence/' . $filename;
                            }
                        } catch (\Throwable $e2) {}
                    }

                    if ($savedPath) {
                        $evidencePaths[] = $savedPath;
                    }
                }
            }
        }

        $evidencePathStr = !empty($evidencePaths) ? (count($evidencePaths) === 1 ? $evidencePaths[0] : json_encode($evidencePaths)) : null;

        InformantReport::create([
            'Title' => $validated['Title'],
            'Category' => $validated['Category'],
            'Description' => $validated['Description'],
            'IsAnonymous' => $request->boolean('IsAnonymous'),
            'ReporterName' => auth()->user()->FullName,
            'ReporterID' => auth()->id(), // Still store ReporterID for user dashboard indexing
            'StudentID' => $request->input('StudentID'),
            'EvidencePath' => $evidencePathStr,
            'Status' => 'เรื่องใหม่',
            'semester_id' => $this->getSelectedSemesterId(),
        ]);

        $redirectRoute = match(true) {
            str_contains($role, 'student') || str_contains($role, 'นักเรียน') => 'student.informant-reports.index',
            str_contains($role, 'teacher') || str_contains($role, 'ครู') => 'teacher.informant-reports.index',
            str_contains($role, 'parent') || str_contains($role, 'ผู้ปกครอง') => 'parent.informant-reports.index',
            str_contains($role, 'discipline') || str_contains($role, 'ฝ่ายปกครอง') => 'discipline.informant-reports.index',
            default => 'student.informant-reports.index'
        };

        return redirect()->route($redirectRoute)
            ->with('success', 'ส่งข้อมูลแจ้งเบาะแสพฤติกรรมเรียบร้อยแล้ว ขอบคุณที่ร่วมช่วยดูแลสอดส่องความปลอดภัย');
    }
}
