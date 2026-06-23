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
        $query = InformantReport::query();

        if ($request->filled('status')) {
            $query->where('Status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('Description', 'like', '%' . $request->search . '%');
        }

        $reports = $query->orderBy('ReportDate', 'desc')->paginate(15);

        $counts = [
            'เรื่องใหม่'        => InformantReport::where('Status', 'เรื่องใหม่')->count(),
            'กำลังตรวจสอบ'     => InformantReport::where('Status', 'กำลังตรวจสอบ')->count(),
            'ปิดเรื่องแล้ว'    => InformantReport::where('Status', 'ปิดเรื่องแล้ว')->count(),
        ];

        return view('discipline.informant-reports.index', compact('reports', 'counts'));
    }

    public function show(InformantReport $informantReport)
    {
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