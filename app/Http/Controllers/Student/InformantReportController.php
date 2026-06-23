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
        $students = Student::orderBy('FullName')->get();
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
            'Title' => 'required|string|max:100|min:5',
            'Category' => 'required|string|max:50',
            'Description' => 'required|string|min:15',
            'StudentID' => 'nullable|exists:students,StudentID',
            'evidence' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'IsAnonymous' => 'nullable|boolean',
        ]);

        $role = strtolower(auth()->user()->Role);
        $isAnonymous = $request->has('IsAnonymous') && $request->input('IsAnonymous') == 1;

        $evidencePath = null;
        if ($request->hasFile('evidence')) {
            $evidencePath = $request->file('evidence')
                ->store('informant_reports/evidence', 'public');
        }

        InformantReport::create([
            'Title' => $validated['Title'],
            'Category' => $validated['Category'],
            'Description' => $validated['Description'],
            'IsAnonymous' => $isAnonymous,
            'ReporterName' => $isAnonymous ? null : auth()->user()->FullName,
            'ReporterID' => auth()->id(), // Still store ReporterID for user dashboard indexing
            'StudentID' => $validated['StudentID'],
            'EvidencePath' => $evidencePath,
            'Status' => 'เรื่องใหม่',
        ]);

        $redirectRoute = match($role) {
            'student' => 'student.informant-reports.index',
            'teacher' => 'teacher.informant-reports.index',
            'parent' => 'parent.informant-reports.index',
            default => 'home'
        };

        return redirect()->route($redirectRoute)
            ->with('success', 'ส่งข้อมูลแจ้งเบาะแสพฤติกรรมเรียบร้อยแล้ว ขอบคุณที่ร่วมช่วยดูแลสอดส่องความปลอดภัย');
    }
}
