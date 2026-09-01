<?php

namespace App\Http\Controllers\Discipline;

use App\Http\Controllers\Controller;
use App\Models\InformantReport;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InformantReportController extends Controller
{
    public function index(Request $request)
    {
        $selectedSemesterId = $this->getSelectedSemesterId();
        $hasSemesterCol = \Illuminate\Support\Facades\Schema::hasColumn('informant_reports', 'semester_id');

        $query = InformantReport::query();

        if ($hasSemesterCol) {
            $query->where(function($q) use ($selectedSemesterId) {
                $q->where('semester_id', $selectedSemesterId)
                  ->orWhereNull('semester_id');
            });
        }

        if ($request->filled('status')) {
            $query->where('Status', $request->status);
        } else {
            $query->where('Status', '!=', 'จัดเก็บเข้าคลัง');
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('Description', 'like', '%' . $request->search . '%')
                  ->orWhere('Title', 'like', '%' . $request->search . '%')
                  ->orWhere('ReportID', 'like', '%' . $request->search . '%')
                  ->orWhere('StudentID', 'like', '%' . $request->search . '%');
            });
        }

        $reports = $query->with(['student', 'reporter.student'])
            ->orderBy('created_at', 'desc')
            ->orderBy('ReportID', 'desc')
            ->paginate(15);

        $counts = [
            'เรื่องใหม่'     => InformantReport::when($hasSemesterCol, fn($q) => $q->where(fn($q2) => $q2->where('semester_id', $selectedSemesterId)->orWhereNull('semester_id')))->where('Status', 'เรื่องใหม่')->count(),
            'กำลังตรวจสอบ'  => InformantReport::when($hasSemesterCol, fn($q) => $q->where(fn($q2) => $q2->where('semester_id', $selectedSemesterId)->orWhereNull('semester_id')))->where('Status', 'กำลังตรวจสอบ')->count(),
            'ปิดเรื่องแล้ว' => InformantReport::when($hasSemesterCol, fn($q) => $q->where(fn($q2) => $q2->where('semester_id', $selectedSemesterId)->orWhereNull('semester_id')))->where('Status', 'ปิดเรื่องแล้ว')->count(),
        ];

        return view('discipline.informant-reports.index', compact('reports', 'counts'));
    }

    public function show(InformantReport $informantReport)
    {
        $informantReport->load(['student', 'reporter.student']);
        return view('discipline.informant-reports.show', compact('informantReport'));
    }

    // รับเรื่อง → เปลี่ยนเป็น "กำลังตรวจสอบ"
    public function accept(InformantReport $informantReport)
    {
        abort_if($informantReport->Status !== 'เรื่องใหม่', 422);

        $informantReport->update(['Status' => 'กำลังตรวจสอบ']);

        return back()->with('success', 'รับเรื่องแจ้งเบาะแสเรียบร้อยแล้ว กำลังดำเนินการตรวจสอบ');
    }

    // ปิดเรื่อง
    public function close(Request $request, InformantReport $informantReport)
    {
        abort_if($informantReport->Status === 'ปิดเรื่องแล้ว', 422);

        $informantReport->update([
            'Status' => 'ปิดเรื่องแล้ว',
            'Remarks' => $request->input('Remarks'),
        ]);

        return back()->with('success', 'ปิดเรื่องแจ้งเบาะแสเรียบร้อยแล้ว');
    }

    // ย้ายเรื่องเข้าคลังประวัติ (Archive)
    public function archive($id)
    {
        $informantReport = InformantReport::findOrFail($id);
        $informantReport->update(['Status' => 'จัดเก็บเข้าคลัง']);

        return back()->with('success', 'ย้ายเรื่องแจ้งเบาะแสเข้าคลังประวัติเรียบร้อยแล้ว');
    }

    // นำเรื่องออกจากคลังประวัติ (Unarchive)
    public function unarchive($id)
    {
        $informantReport = InformantReport::findOrFail($id);
        $informantReport->update(['Status' => 'ปิดเรื่องแล้ว']);

        return back()->with('success', 'นำเรื่องออกจากคลังประวัติเรียบร้อยแล้ว');
    }

    // ย้ายเรื่องที่ปิดแล้วทั้งหมดเข้าคลังประวัติ (Bulk Archive)
    public function bulkArchive()
    {
        $count = InformantReport::where('Status', '!=', 'จัดเก็บเข้าคลัง')->update(['Status' => 'จัดเก็บเข้าคลัง']);

        return back()->with('success', "ย้ายเรื่องแจ้งเบาะแสเข้าคลังประวัติเรียบร้อยแล้ว จำนวน {$count} เรื่อง");
    }

    // สร้างเรื่องใหม่ (ฝ่ายปกครองเพิ่มเองได้)
    public function create()
    {
        return view('discipline.informant-reports.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'Description' => 'required|string|min:10',
            'evidence'    => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $evidencePath = null;
        if ($request->hasFile('evidence')) {
            $evidencePath = $request->file('evidence')
                ->store('informant-reports/evidence', 'public');
        }

        InformantReport::create([
            'ReportID'     => Str::uuid(),
            'Description'  => $validated['Description'],
            'EvidencePath' => $evidencePath,
            'ReportDate'   => now(),
            'Status'       => 'เรื่องใหม่',
        ]);

        return redirect()->route('discipline.informant-reports.index')
            ->with('success', 'บันทึกเรื่องแจ้งเบาะแสเรียบร้อยแล้ว');
    }

    public function destroy(InformantReport $informantReport)
    {
        $informantReport->delete();
        return redirect()->route('discipline.informant-reports.index')
            ->with('success', 'ลบเรื่องเรียบร้อยแล้ว');
    }
}