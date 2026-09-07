@extends('layouts.app')

@section('title', 'หน้าหลัก — นักเรียน')
@section('page-title', 'หน้าหลัก')

@section('content')
@php
    $student = auth()->user()->student;
    $activeSemester = \App\Models\Semester::where('is_active', true)->first();
    $selectedSemesterId = session('selected_semester_id', $activeSemester?->semester_id);
    $selectedSemesterObj = \App\Models\Semester::find($selectedSemesterId);
    $semesterText = $selectedSemesterObj ? "ภาคเรียนที่ {$selectedSemesterObj->term} ปีการศึกษา {$selectedSemesterObj->academic_year}" : "ปีการศึกษา " . (now()->year + 543);

    $recordsQuery = $student->behaviorRecords()->where('semester_id', $selectedSemesterId);
    $attendancesQuery = $student->attendances()->where('semester_id', $selectedSemesterId);
    $appealsQuery = $student->appeals()->whereHas('behaviorRecord', fn($q) => $q->where('semester_id', $selectedSemesterId));

    $score = $student->BehaviorScore;
    $scoreColor = $score >= 80 ? 'var(--green)' : ($score >= 60 ? 'var(--orange)' : 'var(--red)');
    $scoreClass = $score >= 80 ? '' : ($score >= 60 ? ' medium' : ' low');

    $attTotal = (clone $attendancesQuery)->count();
    $attPresent = (clone $attendancesQuery)->where('Status', 'มา')->count();
    $attLate = (clone $attendancesQuery)->where('Status', 'สาย')->count();
    $attAbsent = (clone $attendancesQuery)->where('Status', 'ขาด')->count();
    $attRate = $attTotal > 0 ? round(($attPresent / $attTotal) * 100) : 100;
    $attColor = $attRate >= 80 ? 'green' : ($attRate >= 60 ? 'gold' : 'red');
@endphp

<div class="page-header">
    <h2>{{ $student->FullName }}</h2>
    <p>ห้อง {{ $student->classroom_display }} &nbsp;&bull;&nbsp; ครูประจำชั้น: {{ $student->advisory_teacher->user->FullName ?? 'ยังไม่มีข้อมูล' }} &nbsp;&bull;&nbsp; {{ $semesterText }}</p>
</div>

{{-- Score Card & Behavior Criteria --}}
<div class="responsive-grid-student">
    <div>
        <a href="{{ route('student.behavior-records.index') }}" style="text-decoration:none; color:inherit; display:block;" title="คลิกเพื่อดูประวัติพฤติกรรม">
            <div class="card" style="text-align:center; padding:2rem 1.5rem; cursor:pointer; transition:transform 0.2s ease, box-shadow 0.2s ease;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='var(--shadow-md)';" onmouseout="this.style.transform='none'; this.style.boxShadow='none';">
                <div style="font-size:0.75rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:var(--text-muted); margin-bottom:0.75rem;">คะแนนพฤติกรรม</div>
                <div style="font-size:4rem; font-weight:700; color:{{ $scoreColor }}; line-height:1;">
                    {{ $score }}
                </div>
                <div style="font-size:0.82rem; color:var(--text-muted); margin-top:0.25rem;">จาก 100 คะแนน</div>
                <div style="margin:1rem 0;">
                    <div style="height:8px; background:#e8e3db; border-radius:4px; overflow:hidden;">
                        <div style="height:100%; width:{{ $score }}%; background:{{ $scoreColor }}; border-radius:4px; transition:width 1s;"></div>
                    </div>
                </div>
                @php
                    $studentRiskStatus = in_array($student->RiskStatus, ['เฝ้าระวัง', 'ตักเตือน']) ? 'ตักเตือน' : (in_array($student->RiskStatus, ['วิกฤต', 'ทัณฑ์บน']) ? 'ทัณฑ์บน' : 'ปกติ');
                    $riskBadgeClass = match($studentRiskStatus) {
                        'ปกติ' => 'badge-green',
                        'ตักเตือน' => 'badge-orange',
                        'ทัณฑ์บน' => 'badge-red',
                        default => 'badge-green'
                    };
                @endphp
                <span class="badge {{ $riskBadgeClass }}"
                      style="font-size:0.8rem; padding:0.3rem 0.75rem;">
                    {{ $studentRiskStatus }}
                </span>
            </div>
        </a>
    </div>

    <div class="responsive-grid-2">
        <a href="{{ route('student.behavior-records.index') }}" class="stat-card navy" style="text-decoration:none; cursor:pointer;" title="ดูรายการพฤติกรรมทั้งหมด">
            <div class="stat-icon navy"><i class="fas fa-clipboard-list"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $recordsQuery->count() }}</div>
                <div class="stat-label">รายการพฤติกรรมทั้งหมด</div>
            </div>
        </a>
        <a href="{{ route('student.behavior-records.index', ['type' => 'ตัดคะแนน']) }}" class="stat-card red" style="text-decoration:none; cursor:pointer;" title="ดูรายการตัดคะแนน">
            <div class="stat-icon red"><i class="fas fa-arrow-down"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ (clone $recordsQuery)->whereHas('rule', fn($q) => $q->where('RuleType', 'ตัดคะแนน'))->count() }}</div>
                <div class="stat-label">รายการตัดคะแนน</div>
            </div>
        </a>
        <a href="{{ route('student.attendance.index') }}" class="stat-card {{ $attColor }}" style="text-decoration:none; cursor:pointer;" title="ดูสถิติการเข้าแถว">
            <div class="stat-icon {{ $attColor }}"><i class="fas fa-calendar-check"></i></div>
            <div class="stat-info" style="min-width:0;">
                <div class="stat-value">{{ $attRate }}%</div>
                <div class="stat-label">อัตราการเข้าแถว</div>
                <div style="font-size:0.75rem; color:var(--text-muted); margin-top:0.35rem; display:flex; align-items:center; gap:0.35rem; flex-wrap:wrap;">
                    @if($attTotal > 0)
                        <span style="color:var(--green); font-weight:600;">เข้า {{ $attPresent }}</span>
                        <span style="color:#d1d5db;">•</span>
                        <span style="color:{{ $attLate > 0 ? 'var(--orange)' : 'var(--text-muted)' }}; font-weight:{{ $attLate > 0 ? '600' : '400' }};">สาย {{ $attLate }}</span>
                        <span style="color:#d1d5db;">•</span>
                        <span style="color:{{ $attAbsent > 0 ? 'var(--red)' : 'var(--text-muted)' }}; font-weight:{{ $attAbsent > 0 ? '600' : '400' }};">ขาด {{ $attAbsent }}</span>
                    @else
                        <span>ยังไม่มีข้อมูลเช็กชื่อ</span>
                    @endif
                </div>
            </div>
        </a>
        <a href="{{ route('student.appeals.index') }}" class="stat-card gold" style="text-decoration:none; cursor:pointer;" title="ดูคำอุทธรณ์รอพิจารณา">
            <div class="stat-icon gold"><i class="fas fa-balance-scale"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $appealsQuery->where(['Status' => 'รอตรวจสอบ'])->count() }}</div>
                <div class="stat-label">คำอุทธรณ์รอพิจารณา</div>
            </div>
        </a>
    </div>
</div>

{{-- Behavior Criteria Legend --}}
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header-bar" style="border-bottom:1px solid #f0ece4; padding:0.85rem 1.25rem; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.5rem;">
        <h3 style="font-size:0.95rem; font-weight:700; color:var(--navy); margin:0; display:flex; align-items:center; gap:0.4rem;">
            <i class="fas fa-info-circle" style="color:var(--gold);"></i> รายละเอียดเกณฑ์คะแนนพฤติกรรม
        </h3>
        <span style="font-size:0.8rem; color:var(--text-muted);">
            สถานะปัจจุบันของคุณ: 
            <strong style="color:{{ $scoreColor }};">{{ $studentRiskStatus }} ({{ $score }} คะแนน)</strong>
        </span>
    </div>
    <div style="padding:1rem 1.25rem;">
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap:0.85rem;">
            <!-- ระดับปกติ -->
            <div style="display:flex; align-items:flex-start; gap:0.75rem; background:#f0fdf4; border:1px solid {{ $score >= 80 ? '#22c55e' : '#bbf7d0' }}; border-radius:10px; padding:0.85rem 1rem; {{ $score >= 80 ? 'box-shadow:0 0 0 2px rgba(34,197,94,0.2);' : '' }}">
                <div style="font-size:1.35rem; line-height:1.2; flex-shrink:0;">🟢</div>
                <div style="font-size:0.85rem; line-height:1.5;">
                    <div style="display:flex; align-items:center; gap:0.4rem; flex-wrap:wrap;">
                        <strong style="color:#166534; font-size:0.9rem;">ระดับปกติ (Normal):</strong>
                        @if($score >= 80)
                            <span class="badge badge-green" style="font-size:0.7rem; padding:0.15rem 0.45rem;">ระดับของคุณ</span>
                        @endif
                    </div>
                    <div style="color:#15803d; margin-top:0.25rem;">
                        คะแนนคงเหลือตั้งแต่ <strong>80 ถึง 100 คะแนน</strong>
                    </div>
                </div>
            </div>

            <!-- ระดับตักเตือน -->
            <div style="display:flex; align-items:flex-start; gap:0.75rem; background:#fefce8; border:1px solid {{ ($score >= 60 && $score < 80) ? '#eab308' : '#fef08a' }}; border-radius:10px; padding:0.85rem 1rem; {{ ($score >= 60 && $score < 80) ? 'box-shadow:0 0 0 2px rgba(234,179,8,0.2);' : '' }}">
                <div style="font-size:1.35rem; line-height:1.2; flex-shrink:0;">🟡</div>
                <div style="font-size:0.85rem; line-height:1.5;">
                    <div style="display:flex; align-items:center; gap:0.4rem; flex-wrap:wrap;">
                        <strong style="color:#854d0e; font-size:0.9rem;">ระดับตักเตือน (Warning):</strong>
                        @if($score >= 60 && $score < 80)
                            <span class="badge badge-orange" style="font-size:0.7rem; padding:0.15rem 0.45rem;">ระดับของคุณ</span>
                        @endif
                    </div>
                    <div style="color:#a16207; margin-top:0.25rem;">
                        คะแนนคงเหลือตั้งแต่ <strong>60 ถึง 79 คะแนน</strong>
                        <span style="font-size:0.8rem; display:block; margin-top:0.15rem; color:#854d0e;">(ควรเริ่มว่ากล่าวตักเตือนและส่งข้อความแจ้งเตือนผู้ปกครอง)</span>
                    </div>
                </div>
            </div>

            <!-- ระดับทัณฑ์บน -->
            <div style="display:flex; align-items:flex-start; gap:0.75rem; background:#fef2f2; border:1px solid {{ $score < 60 ? '#ef4444' : '#fecaca' }}; border-radius:10px; padding:0.85rem 1rem; {{ $score < 60 ? 'box-shadow:0 0 0 2px rgba(239,68,68,0.2);' : '' }}">
                <div style="font-size:1.35rem; line-height:1.2; flex-shrink:0;">🔴</div>
                <div style="font-size:0.85rem; line-height:1.5;">
                    <div style="display:flex; align-items:center; gap:0.4rem; flex-wrap:wrap;">
                        <strong style="color:#991b1b; font-size:0.9rem;">ระดับทัณฑ์บน (Probation):</strong>
                        @if($score < 60)
                            <span class="badge badge-red" style="font-size:0.7rem; padding:0.15rem 0.45rem;">ระดับของคุณ</span>
                        @endif
                    </div>
                    <div style="color:#b91c1c; margin-top:0.25rem;">
                        คะแนนคงเหลือ <strong>ต่ำกว่า 60 คะแนน</strong>
                        <span style="font-size:0.8rem; display:block; margin-top:0.15rem; color:#991b1b;">(ระบบจะจัดกลุ่มนี้เป็นกลุ่มความเสี่ยงสูงเพื่อเตรียมมาตรการแนะแนวหรือทำทัณฑ์บน)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Prayer Status Card --}}
@php
    $currentMonth = now()->month;
    $currentYear = now()->year;
    $prayerStatus = $student->getPrayerMonthlyStatus($currentMonth, $currentYear);
@endphp
<div class="card" style="margin-bottom: 1.5rem; border-left: 4px solid {{ $prayerStatus['status'] === 'pass' ? '#10b981' : ($prayerStatus['status'] === 'corrected' ? '#3b82f6' : '#ef4444') }};">
    <div style="padding:1.25rem 1.5rem; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem;">
        <div>
            <h4 style="color:var(--navy); margin:0; display:flex; align-items:center; gap:0.5rem;">
                <i class="fas fa-star-and-crescent" style="color:var(--gold);"></i> 
                เกณฑ์การละหมาดประจำเดือน {{ now()->locale('th')->isoFormat('MMMM ') . (now()->year + 543) }}
            </h4>
            <p style="font-size:0.8rem; color:var(--text-muted); margin-top:0.25rem;">
                สถิติการเช็กชื่อในเดือนนี้: ละหมาดแล้ว <strong>{{ $prayerStatus['prayed_count'] }}</strong> ครั้ง | ขาด <strong>{{ $prayerStatus['absent_count'] }}</strong> ครั้ง | ละหมาดไม่ได้ <strong>{{ $prayerStatus['exempt_count'] }}</strong> ครั้ง
            </p>
        </div>
        <div style="display:flex; align-items:center; gap:1.25rem; flex-wrap:wrap;">
            <div style="text-align:center;">
                <span style="font-family:'Outfit', sans-serif; font-size:1.5rem; font-weight:800; color: {{ $prayerStatus['percentage'] >= 80 ? '#10b981' : ($prayerStatus['percentage'] >= 60 ? '#f59e0b' : '#ef4444') }}">
                    {{ $prayerStatus['percentage'] }}%
                </span>
                <span style="font-size:0.75rem; color:var(--text-muted); display:block;">สถิติละหมาด</span>
            </div>
            <div>
                @if($prayerStatus['status'] === 'pass')
                    <span class="badge badge-green" style="padding:0.3rem 0.65rem;"><i class="fas fa-check-circle"></i> ผ่านเกณฑ์</span>
                @elseif($prayerStatus['status'] === 'corrected')
                    <span class="badge badge-primary" style="padding:0.3rem 0.65rem; background: rgba(59, 130, 246, 0.1); color: #1d4ed8; border: 1px solid rgba(59, 130, 246, 0.2);"><i class="fas fa-user-check"></i> แก้ละหมาดแล้ว (ผ่าน)</span>
                @else
                    <span class="badge badge-red" style="padding:0.3rem 0.65rem;"><i class="fas fa-times-circle"></i> ไม่ผ่านเกณฑ์</span>
                @endif
            </div>
            <a href="{{ route('prayer.calendar') }}" class="btn btn-outline btn-sm">
                ดูรายละเอียด
            </a>
        </div>
    </div>
</div>

{{-- Recent behaviors --}}
<div class="card">
    <div class="card-header-bar">
        <h3>ประวัติพฤติกรรมล่าสุด</h3>
        <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
            <a href="{{ route('prayer.calendar') }}" class="btn btn-primary btn-sm" style="background:linear-gradient(135deg, #0d5c3a 0%, #10b981 100%); border-color:transparent; box-shadow:0 4px 10px rgba(13,92,58,0.15);">
                <i class="fas fa-star-and-crescent"></i> ประวัติการละหมาด
            </a>
            <a href="{{ route('student.appeals.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-balance-scale"></i> อุทธรณ์คะแนน
            </a>
            <a href="{{ route('student.behavior-records.index') }}" class="btn btn-outline btn-sm">ดูทั้งหมด</a>
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>พฤติกรรม</th>
                    <th>คะแนน</th>
                    <th>วันที่</th>
                    <th>สถานะ</th>
                </tr>
            </thead>
            <tbody>
                @forelse((clone $recordsQuery)->with('rule')->latest('RecordDate')->take(8)->get() as $r)
                <tr>
                    <td>
                        <div style="font-size:0.875rem;">{{ $r->rule->RuleName }}</div>
                        <div style="font-size:0.75rem; color:var(--text-muted);">{{ $r->rule->Category }}</div>
                    </td>
                    <td>
                        <span style="font-weight:700; color:{{ $r->rule->RuleType === 'ตัดคะแนน' ? 'var(--red)' : 'var(--green)' }}">
                            {{ $r->rule->RuleType === 'ตัดคะแนน' ? '-' : '+' }}{{ abs($r->rule->ScoreModifier) }}
                        </span>
                    </td>
                    <td style="font-size:0.82rem; color:var(--text-muted);">
                        @php $rd = \Carbon\Carbon::parse($r->RecordDate); @endphp
                        {{ $rd->format('d/m/') . ($rd->year + 543) }}
                    </td>
                    <td>
                        @php
                            $displayStatus = match($r->Status) {
                                'อนุมัติแล้ว', 'อนุมัติ' => 'อนุมัติ',
                                default => $r->Status,
                            };
                            $sc = match($displayStatus) {
                                'รออนุมัติ' => 'badge-gold',
                                'อนุมัติ' => 'badge-green',
                                default => 'badge-orange',
                            };
                        @endphp
                        <span class="badge {{ $sc }}">{{ $displayStatus }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align:center; color:var(--text-muted); padding:2rem;">ยังไม่มีประวัติพฤติกรรม</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection