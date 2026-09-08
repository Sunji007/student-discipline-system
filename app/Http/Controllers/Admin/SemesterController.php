<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Semester;
use Illuminate\Http\Request;

class SemesterController extends Controller
{
    /**
     * Store a newly created academic year / semester.
     */
    public function store(Request $request)
    {
        $request->validate([
            'academic_year' => 'required|integer|min:2500|max:2700',
            'term'          => 'required|integer|in:1,2,3',
            'is_active'     => 'nullable|boolean',
        ]);

        $year = (int)$request->academic_year;
        $term = (int)$request->term;
        $makeActive = $request->boolean('is_active');

        // Check duplicate
        $existing = Semester::where('academic_year', $year)
            ->where('term', $term)
            ->first();

        if ($existing) {
            return redirect()->back()->with('error', "ปีการศึกษา {$year} ภาคเรียนที่ {$term} มีอยู่ในระบบแล้ว");
        }

        // If this is the only semester or user requested active
        $hasAnyActive = Semester::where('is_active', true)->exists();
        if ($makeActive || !$hasAnyActive) {
            Semester::where('is_active', true)->update(['is_active' => false]);
            $isActive = true;
        } else {
            $isActive = false;
        }

        $semester = Semester::create([
            'academic_year' => $year,
            'term'          => $term,
            'is_active'     => $isActive,
        ]);

        if ($isActive) {
            session(['selected_semester_id' => $semester->semester_id]);
        }

        $activeText = $isActive ? ' และตั้งเป็นภาคเรียนปัจจุบันเรียบร้อยแล้ว' : '';
        return redirect()->back()->with('success', "เพิ่มปีการศึกษา {$year} ภาคเรียนที่ {$term} สำเร็จ{$activeText}");
    }

    /**
     * Set the designated semester as the active one.
     */
    public function setActive(Semester $semester)
    {
        Semester::where('is_active', true)->update(['is_active' => false]);

        $semester->update(['is_active' => true]);

        session(['selected_semester_id' => $semester->semester_id]);

        return redirect()->back()->with('success', "ตั้งปีการศึกษา {$semester->academic_year} ภาคเรียนที่ {$semester->term} เป็นภาคเรียนปัจจุบันเรียบร้อยแล้ว");
    }

    /**
     * Remove the specified semester if unused.
     */
    public function destroy(Semester $semester)
    {
        if ($semester->is_active) {
            return redirect()->back()->with('error', 'ไม่สามารถลบภาคเรียนที่เป็นภาคเรียนปัจจุบันได้ กรุณาสลับภาคเรียนอื่นเป็นปัจจุบันก่อน');
        }

        $attendanceCount = $semester->attendances()->count();
        $behaviorCount   = $semester->behaviorRecords()->count();
        $prayerCount     = $semester->prayerRecords()->count();

        $totalRecords = $attendanceCount + $behaviorCount + $prayerCount;

        if ($totalRecords > 0) {
            return redirect()->back()->with('error', "ไม่สามารถลบได้เนื่องจากมีข้อมูลบันทึกอยู่ในภาคเรียนนี้ (การเข้าแถว: {$attendanceCount}, พฤติกรรม: {$behaviorCount}, ละหมาด: {$prayerCount})");
        }

        $year = $semester->academic_year;
        $term = $semester->term;
        $semester->delete();

        return redirect()->back()->with('success', "ลบปีการศึกษา {$year} ภาคเรียนที่ {$term} เรียบร้อยแล้ว");
    }
}
