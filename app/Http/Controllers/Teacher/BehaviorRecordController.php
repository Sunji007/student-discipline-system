<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\BehaviorRecord;
use App\Models\BehaviorRule;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BehaviorRecordController extends Controller
{
    public function index()
    {
        $teacher  = auth()->user()->teacher;
        $records  = BehaviorRecord::with(['student', 'rule'])
            ->where('RecordedBy', auth()->user()->UserID)
            ->where('semester_id', $this->getSelectedSemesterId())
            ->orderBy('RecordDate', 'desc')
            ->paginate(20);

        return view('teacher.behavior-records.index', compact('records'));
    }

    public function create(Request $request)
    {
        $teacher  = auth()->user()->teacher;
        $rooms    = $teacher?->advisory_rooms ?? [];
        $students = Student::inAdvisoryRoom($rooms)
            ->orderBy('FirstName')
            ->orderBy('LastName')
            ->get();
        $rules    = BehaviorRule::orderBy('RuleType')->get();
        $preselectedStudentId = $request->get('student_id');

        return view('teacher.behavior-records.create', compact('students', 'rules', 'rooms', 'preselectedStudentId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'StudentID'   => 'required|exists:students,StudentID',
            'RuleID'      => 'required|exists:behavior_rules,RuleID',
            'Description' => 'nullable|string',
            'RecordDate'  => 'required|date',
            'Penalty'     => 'nullable|string|max:100',
            'Photo'       => 'nullable|array',
            'Photo.*'     => 'image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $teacher  = auth()->user()->teacher;
        $rooms    = $teacher?->advisory_rooms ?? [];
        $allowedStudentIds = Student::inAdvisoryRoom($rooms)->pluck('StudentID')->toArray();

        if (!in_array($validated['StudentID'], $allowedStudentIds)) {
            return redirect()->back()
                ->withErrors(['StudentID' => 'ครูประจำชั้นสามารถบันทึกพฤติกรรมเฉพาะนักเรียนในห้องเรียนที่ตนเองดูแลเท่านั้น'])
                ->withInput();
        }

        $photoPaths = [];
        if ($request->hasFile('Photo')) {
            foreach ($request->file('Photo') as $photoFile) {
                $photoPaths[] = $photoFile->store('behavior_records', 'public');
            }
        }
        $photoJson = !empty($photoPaths) ? json_encode($photoPaths) : null;

        BehaviorRecord::create([
            'RecordID'    => Str::uuid(),
            'RecordedBy'  => auth()->user()->UserID,
            'Status'      => 'รออนุมัติ',
            'semester_id' => $this->getSelectedSemesterId(),
            'StudentID'   => $validated['StudentID'],
            'RuleID'      => $validated['RuleID'],
            'Description' => $validated['Description'] ?? null,
            'RecordDate'  => $validated['RecordDate'],
            'Penalty'     => $validated['Penalty'] ?? null,
            'Photo'       => $photoJson,
        ]);

        return redirect()->route('teacher.behavior-records.index')
            ->with('success', 'บันทึกพฤติกรรมเรียบร้อย รอฝ่ายปกครองอนุมัติ');
    }

    public function show($id)
    {
        $behaviorRecord = BehaviorRecord::findOrFail($id);

        $teacher  = auth()->user()->teacher;
        $rooms    = $teacher?->advisory_rooms ?? [];
        $allowedStudentIds = Student::inAdvisoryRoom($rooms)->pluck('StudentID')->toArray();

        $isRecorder = $behaviorRecord->RecordedBy === auth()->user()->UserID;
        $isAdvisoryStudent = in_array($behaviorRecord->StudentID, $allowedStudentIds);

        abort_if(!$isRecorder && !$isAdvisoryStudent, 403, 'ไม่มีสิทธิ์เข้าถึงข้อมูลบันทึกนี้');

        $behaviorRecord->load(['student', 'rule', 'appeal']);
        return view('teacher.behavior-records.show', compact('behaviorRecord'));
    }
}