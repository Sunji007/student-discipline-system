<?php

namespace App\Http\Controllers\Discipline;

use App\Http\Controllers\Controller;
use App\Models\BehaviorRecord;
use App\Models\BehaviorRule;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class BehaviorRecordController extends Controller
{
    public function index(Request $request)
    {
        $query = BehaviorRecord::with(['student', 'rule', 'recorder'])
            ->where('semester_id', $this->getSelectedSemesterId());

        if ($request->filled('status')) {
            $query->where('Status', $request->status);
        }

        if ($request->filled('category')) {
            $cat = $request->category;
            if ($cat === 'ความผิดร้ายแรง') {
                $query->whereHas('rule', fn($q) =>
                    $q->where('RuleType', 'ตัดคะแนน')
                      ->where(function($sub) {
                          $sub->where(DB::raw('ABS(ScoreModifier)'), '>=', 20)
                              ->orWhere('Category', 'like', '%ร้ายแรง%')
                              ->orWhere('Category', 'สารเสพติดและของต้องห้าม');
                      })
                );
            } elseif ($cat === 'ความผิดไม่ร้ายแรง') {
                $query->whereHas('rule', fn($q) =>
                    $q->where('RuleType', 'ตัดคะแนน')
                      ->where('Category', 'not like', '%ร้ายแรง%')
                      ->where('Category', '!=', 'สารเสพติดและของต้องห้าม')
                      ->where(DB::raw('ABS(ScoreModifier)'), '<', 20)
                );
            } else {
                $query->whereHas('rule', fn($q) => $q->where('Category', $cat));
            }
        }

        if ($request->filled('search')) {
            $query->whereHas('student', fn($q) =>
                $q->where('FirstName', 'like', '%' . $request->search . '%')
                  ->orWhere('LastName', 'like', '%' . $request->search . '%')
                  ->orWhere('StudentID', 'like', '%' . $request->search . '%')
            );
        }

        $records = $query->orderBy('RecordDate', 'desc')->paginate(20);

        $categories = BehaviorRule::whereNotNull('Category')
            ->where('Category', '!=', '')
            ->distinct()
            ->pluck('Category');

        return view('discipline.behavior-records.index', compact('records', 'categories'));
    }

    public function create()
    {
        $students = Student::orderBy('Classroom')->orderBy('FirstName')->orderBy('LastName')->get();
        $rules    = BehaviorRule::orderBy('RuleType')->orderBy('Category')->get();

        return view('discipline.behavior-records.create', compact('students', 'rules'));
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

        $photoPaths = [];
        if ($request->hasFile('Photo')) {
            foreach ($request->file('Photo') as $photoFile) {
                $photoPaths[] = $photoFile->store('behavior_records', 'public');
            }
        }
        $photoJson = !empty($photoPaths) ? json_encode($photoPaths) : null;

        DB::transaction(function() use ($validated, $photoJson) {
            $rule = BehaviorRule::findOrFail($validated['RuleID']);
            $student = Student::where('StudentID', $validated['StudentID'])->lockForUpdate()->firstOrFail();

            BehaviorRecord::create([
                'RecordID'    => Str::uuid(),
                'StudentID'   => $validated['StudentID'],
                'RecordedBy'  => auth()->user()->UserID,
                'RuleID'      => $validated['RuleID'],
                'Description' => $validated['Description'] ?? null,
                'RecordDate'  => $validated['RecordDate'],
                'Penalty'     => $validated['Penalty'] ?? null,
                'Status'      => 'รออนุมัติ',
                'semester_id' => $this->getSelectedSemesterId(),
                'Photo'       => $photoJson,
            ]);
        });

        return redirect()->route('discipline.behavior-records.index')
            ->with('success', 'บันทึกพฤติกรรมเรียบร้อยแล้ว (สถานะ: รออนุมัติ)');
    }

    public function show(BehaviorRecord $behaviorRecord)
    {
        $behaviorRecord->load(['student', 'rule', 'recorder', 'appeal']);
        return view('discipline.behavior-records.show', compact('behaviorRecord'));
    }

    // อนุมัติบันทึก → คำนวณคะแนนอัตโนมัติ
    public function approve(BehaviorRecord $record)
    {
        if ($record->Status !== 'รออนุมัติ') {
            return back()->with('error', 'ไม่สามารถอนุมัติรายการนี้ได้');
        }

        DB::transaction(function () use ($record) {
            // เปลี่ยนสถานะ
            $record->update(['Status' => 'อนุมัติ']);

            // ดึง ScoreModifier จากกฎ
            $modifier = $record->rule->ScoreModifier;

            // ถ้าเป็น ตัดคะแนน → modifier ต้องเป็นค่าลบ
            if ($record->rule->RuleType === 'ตัดคะแนน') {
                $modifier = -abs($modifier);
            } else {
                $modifier = abs($modifier);
            }

            // อัปเดตคะแนน + คำนวณ RiskStatus
            $student = $record->student;
            $newScore = max(0, min(100, $student->BehaviorScore + $modifier));

            $riskStatus = match(true) {
                $newScore >= 80 => 'ปกติ',
                $newScore >= 60 => 'ตักเตือน',
                default         => 'ทัณฑ์บน',
            };

            $student->update([
                'BehaviorScore' => $newScore,
                'RiskStatus'    => $riskStatus,
            ]);
        });

        return back()->with('success', 'อนุมัติและปรับคะแนนนักเรียนเรียบร้อยแล้ว');
    }

    // ปฏิเสธ/ยกเลิกรายการบันทึกพฤติกรรม
    public function reject(BehaviorRecord $record)
    {
        if ($record->Status !== 'รออนุมัติ') {
            return back()->with('error', 'ไม่สามารถยกเลิกรายการนี้ได้');
        }

        $record->update(['Status' => 'ยกเลิก']);

        return back()->with('success', 'ปฏิเสธและยกเลิกรายการบันทึกพฤติกรรมเรียบร้อยแล้ว');
    }

    // เอาบันทึกพฤติกรรมออกจากระบบ
    public function destroy(BehaviorRecord $behaviorRecord)
    {
        if ($behaviorRecord->Status !== 'ยกเลิก') {
            return back()->with('error', 'สามารถเอาออกได้เฉพาะรายการที่ยกเลิกแล้วเท่านั้น');
        }

        $behaviorRecord->delete();

        return redirect()->route('discipline.behavior-records.index')
            ->with('success', 'เอารายการบันทึกพฤติกรรมออกจากระบบเรียบร้อยแล้ว');
    }
}