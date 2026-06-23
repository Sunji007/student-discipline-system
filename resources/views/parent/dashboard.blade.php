@extends('layouts.app')

@section('title', 'แดชบอร์ดผู้ปกครอง')
@section('page-title', 'แดชบอร์ดผู้ปกครอง')

@section('content')
@if(!$student)
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i>
        ยังไม่มีการเชื่อมโยงข้อมูลบุตรหลาน กรุณาติดต่อผู้ดูแลระบบ
    </div>
@else
@php
    $score = $student->BehaviorScore;
    $scoreColor = $score >= 80 ? 'var(--green)' : ($score >= 60 ? 'var(--orange)' : 'var(--red)');
@endphp

<div class="page-header">
    <h2>ข้อมูลบุตรหลาน</h2>
    <p>อัปเดตล่าสุด: {{ now()->locale('th')->isoFormat('D MMMM YYYY, HH:mm') }}</p>
</div>

{{-- Student Card --}}
<div class="card" style="margin-bottom:1rem;">
    <div style="padding:1.5rem; display:flex; align-items:center; gap:1.5rem;">
        @if($student->Photo)
            <div style="width:64px; height:64px; border-radius:50%; overflow:hidden; border:2px solid #e5e7eb; display:flex; align-items:center; justify-content:center; background:#f3f4f6; flex-shrink:0;">
                <img src="{{ asset('storage/' . $student->Photo) }}" alt="{{ $student->FullName }}" style="width:100%; height:100%; object-fit:cover;">
            </div>
        @else
            <div style="width:64px; height:64px; background:var(--navy); border-radius:50%; display:flex; align-items:center; justify-content:center; color:var(--gold); font-size:1.5rem; font-weight:700; flex-shrink:0;">
                {{ mb_substr($student->FullName, 0, 1) }}
            </div>
        @endif
        <div style="flex:1;">
            <div style="font-size:1.15rem; font-weight:700; color:var(--navy);">{{ $student->FullName }}</div>
            <div style="font-size:0.85rem; color:var(--text-muted); margin-top:0.2rem;">
                รหัส: {{ $student->StudentID }} &nbsp;|&nbsp;
                ชั้นเรียน: {{ $student->classroom_display }} &nbsp;|&nbsp;
                ครูประจำชั้น: {{ $student->advisory_teacher->user->FullName ?? 'ยังไม่มีข้อมูล' }}
            </div>
        </div>
        <div style="text-align:right;">
            <div style="font-size:2.5rem; font-weight:700; color:{{ $scoreColor }}; line-height:1;">
                {{ $score }}
            </div>
            <div style="font-size:0.75rem; color:var(--text-muted);">คะแนนพฤติกรรม</div>
            <span class="badge {{ $student->RiskStatus === 'ปกติ' ? 'badge-green' : ($student->RiskStatus === 'เฝ้าระวัง' ? 'badge-orange' : 'badge-red') }}" style="margin-top:0.35rem;">
                {{ $student->RiskStatus }}
            </span>
        </div>
    </div>
</div>

@php
    $currentMonth = now()->month;
    $currentYear = now()->year;
    $prayerStatus = $student->getPrayerMonthlyStatus($currentMonth, $currentYear);
@endphp
<div class="card" style="margin-bottom:1rem; border-left:4px solid {{ $prayerStatus['status'] === 'pass' ? '#10b981' : ($prayerStatus['status'] === 'corrected' ? '#3b82f6' : '#ef4444') }};">
    <div style="padding:1.25rem 1.5rem; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem;">
        <div>
            <h4 style="color:#0d5c3a; margin:0;"><i class="fas fa-star-and-crescent"></i> เกณฑ์การละหมาดของบุตรหลาน ประจำเดือน {{ now()->locale('th')->isoFormat('MMMM YYYY') }}</h4>
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
            <a href="{{ route('prayer.calendar') }}" class="btn btn-primary btn-sm" style="background:linear-gradient(135deg, #0d5c3a 0%, #10b981 100%); border:none; box-shadow:0 4px 10px rgba(13,92,58,0.15);">
                ดูปฏิทินละหมาด
            </a>
        </div>
    </div>
</div>

<div class="responsive-grid-2">
    {{-- Recent Behavior --}}
    <div class="card">
        <div class="card-header-bar">
            <h3><i class="fas fa-clipboard-list" style="color:var(--gold); margin-right:0.5rem"></i>พฤติกรรมล่าสุด</h3>
            <a href="{{ route('parent.behavior-records.index') }}" class="btn btn-outline btn-sm">ดูทั้งหมด</a>
        </div>
        <div>
            @forelse($recentRecords as $r)
            <div style="padding:0.75rem 1.25rem; border-bottom:1px solid #f0ece4; display:flex; align-items:center; justify-content:space-between; gap:0.75rem;">
                <div style="flex:1; min-width:0;">
                    <div style="font-size:0.85rem; font-weight:500;">{{ $r->rule->RuleName }}</div>
                    <div style="font-size:0.75rem; color:var(--text-muted);">
                        {{ \Carbon\Carbon::parse($r->RecordDate)->format('d/m/Y') }}
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap:0.5rem; flex-shrink:0;">
                    <span style="font-weight:700; font-size:0.9rem; color:{{ $r->rule->RuleType === 'ตัดคะแนน' ? 'var(--red)' : 'var(--green)' }}">
                        {{ $r->rule->RuleType === 'ตัดคะแนน' ? '-' : '+' }}{{ abs($r->rule->ScoreModifier) }}
                    </span>
                    <span class="badge {{ $r->Status === 'อนุมัติแล้ว' ? 'badge-green' : 'badge-gold' }}" style="font-size:0.65rem;">
                        {{ $r->Status }}
                    </span>
                </div>
            </div>
            @empty
            <div style="text-align:center; padding:2rem; color:var(--text-muted); font-size:0.875rem;">
                ไม่มีประวัติพฤติกรรม
            </div>
            @endforelse
        </div>
    </div>

    {{-- Attendance this week --}}
    <div class="card">
        <div class="card-header-bar">
            <h3><i class="fas fa-calendar-check" style="color:var(--gold); margin-right:0.5rem"></i>การเข้าแถว 7 วันล่าสุด</h3>
            <a href="{{ route('parent.attendance.index') }}" class="btn btn-outline btn-sm">ดูทั้งหมด</a>
        </div>
        <div style="padding:1rem 1.25rem;">
            @forelse($recentAttendance as $att)
            <div style="display:flex; align-items:center; justify-content:space-between; padding:0.45rem 0; border-bottom:1px solid #f0ece4;">
                <span style="font-size:0.85rem;">{{ \Carbon\Carbon::parse($att->Date)->locale('th')->isoFormat('ddd D MMM') }}</span>
                @php
                    $ac = match($att->Status) { 'มา' => 'badge-green', 'สาย' => 'badge-orange', 'ขาด' => 'badge-red' };
                @endphp
                <span class="badge {{ $ac }}">{{ $att->Status }}</span>
            </div>
            @empty
            <div style="text-align:center; padding:1.5rem; color:var(--text-muted); font-size:0.875rem;">
                ยังไม่มีข้อมูลการเข้าแถว
            </div>
            @endforelse
        </div>
    </div>
</div>
@endif
@endsection