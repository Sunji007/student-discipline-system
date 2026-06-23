@extends('layouts.app')

@section('title', 'แดชบอร์ดนักเรียน')
@section('page-title', 'แดชบอร์ดของฉัน')

@section('content')
@php
    $student = auth()->user()->student;
    $score = $student->BehaviorScore;
    $scoreColor = $score >= 80 ? 'var(--green)' : ($score >= 60 ? 'var(--orange)' : 'var(--red)');
    $scoreClass = $score >= 80 ? '' : ($score >= 60 ? ' medium' : ' low');
@endphp

<div class="page-header">
    <h2>{{ $student->FullName }}</h2>
    <p>ห้อง {{ $student->classroom_display }} &nbsp;&bull;&nbsp; ครูประจำชั้น: {{ $student->advisory_teacher->user->FullName ?? 'ยังไม่มีข้อมูล' }} &nbsp;&bull;&nbsp; ปีการศึกษา {{ now()->year + 543 }}</p>
</div>

{{-- Score Card --}}
<div class="responsive-grid-student">
    <div class="card" style="text-align:center; padding:2rem 1.5rem;">
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
        <span class="badge {{ $student->RiskStatus === 'ปกติ' ? 'badge-green' : ($student->RiskStatus === 'เฝ้าระวัง' ? 'badge-orange' : 'badge-red') }}"
              style="font-size:0.8rem; padding:0.3rem 0.75rem;">
            {{ $student->RiskStatus }}
        </span>
    </div>

    <div class="responsive-grid-2">
        <div class="stat-card navy">
            <div class="stat-icon navy"><i class="fas fa-clipboard-list"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $student->behaviorRecords()->count() }}</div>
                <div class="stat-label">บันทึกทั้งหมด</div>
            </div>
        </div>
        <div class="stat-card red">
            <div class="stat-icon red"><i class="fas fa-arrow-down"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $student->behaviorRecords()->whereHas('rule', fn($q) => $q->where(['RuleType' => 'ตัดคะแนน']))->where(['Status' => 'อนุมัติแล้ว'])->count() }}</div>
                <div class="stat-label">รายการตัดคะแนน</div>
            </div>
        </div>
        <div class="stat-card green">
            <div class="stat-icon green"><i class="fas fa-calendar-check"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $student->attendances()->where(['Status' => 'มา'])->count() }}</div>
                <div class="stat-label">มาเรียนปกติ</div>
            </div>
        </div>
        <div class="stat-card gold">
            <div class="stat-icon gold"><i class="fas fa-balance-scale"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $student->appeals()->where(['Status' => 'รอตรวจสอบ'])->count() }}</div>
                <div class="stat-label">คำร้องรอพิจารณา</div>
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
                เกณฑ์การละหมาดประจำเดือน {{ now()->locale('th')->isoFormat('MMMM YYYY') }}
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
            <a href="{{ route('student.appeals.create') }}" class="btn btn-gold btn-sm">
                <i class="fas fa-balance-scale"></i> ยื่นคำร้องโต้แย้ง
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
                @forelse($student->behaviorRecords()->with('rule')->take(8)->get() as $r)
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
                        {{ \Carbon\Carbon::parse($r->RecordDate)->format('d/m/Y') }}
                    </td>
                    <td>
                        @php
                            $sc = match($r->Status) {
                                'รออนุมัติ' => 'badge-gold',
                                'อนุมัติแล้ว' => 'badge-green',
                                default => 'badge-orange',
                            };
                        @endphp
                        <span class="badge {{ $sc }}">{{ $r->Status }}</span>
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