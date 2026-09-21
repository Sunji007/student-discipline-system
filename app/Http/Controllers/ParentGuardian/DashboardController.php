<?php

namespace App\Http\Controllers\ParentGuardian;

use App\Http\Controllers\Controller;
use App\Models\Message;

class DashboardController extends Controller
{
    public function index()
    {
        $students = auth()->user()->parentStudents;

        if ($students->isEmpty()) {
            return view('parent.dashboard', ['student' => null, 'students' => collect()]);
        }

        $selectedStudentId = session('selected_student_id', $students->first()->StudentID);
        $student = $students->firstWhere('StudentID', $selectedStudentId) ?? $students->first();

        // Ensure session variable is synced
        if (session('selected_student_id') !== $student->StudentID) {
            session(['selected_student_id' => $student->StudentID]);
        }

        $selectedSemesterId = $this->getSelectedSemesterId();

        $recentRecords = $student->behaviorRecords()
            ->with('rule')
            ->where('semester_id', $selectedSemesterId)
            ->orderBy('RecordDate', 'desc')
            ->take(5)
            ->get();

        $recentAttendance = $student->attendances()
            ->where('semester_id', $selectedSemesterId)
            ->orderBy('Date', 'desc')
            ->take(7)
            ->get();

        $unreadMessages = Message::where('ReceiverID', auth()->user()->UserID)
            ->where('IsRead', false)
            ->count();

        return view('parent.dashboard', compact(
            'student',
            'students',
            'recentRecords',
            'recentAttendance',
            'unreadMessages'
        ));
    }

    public function switchStudent(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'student_id' => 'required|string|exists:students,StudentID'
        ]);

        $students = auth()->user()->parentStudents;
        if ($students->contains('StudentID', $request->student_id)) {
            session(['selected_student_id' => $request->student_id]);
        }

        // If previous URL has student_id parameter, replace it so it reflects the new child
        $backUrl = url()->previous();
        if ($backUrl) {
            $parsed = parse_url($backUrl);
            if (isset($parsed['query'])) {
                parse_str($parsed['query'], $queryParams);
                if (isset($queryParams['student_id'])) {
                    $queryParams['student_id'] = $request->student_id;
                    $newQuery = http_build_query($queryParams);
                    $cleanUrl = ($parsed['scheme'] ?? 'http') . '://' . ($parsed['host'] ?? '') . (isset($parsed['port']) ? ':' . $parsed['port'] : '') . ($parsed['path'] ?? '') . '?' . $newQuery;
                    return redirect($cleanUrl);
                }
            }
        }

        return redirect()->back();
    }
}