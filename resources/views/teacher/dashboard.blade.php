@extends('layouts.app')

@section('title', 'หน้าหลัก — ครู')
@section('page-title', 'หน้าหลักครูประจำชั้น')

@section('content')
<div class="page-header">
    <h2>{{ auth()->user()->FullName }}</h2>
    <p>ห้องที่ปรึกษา: <strong>{{ $room ?? 'ยังไม่ได้รับมอบหมาย' }}</strong>
        &nbsp;|&nbsp; {{ now()->locale('th')->isoFormat('D MMMM ') . (now()->year + 543) }}</p>
</div>

<div class="stat-grid">
    <a href="{{ route('teacher.classroom.index') }}" class="stat-card navy" style="text-decoration:none; cursor:pointer;" title="ดูรายชื่อนักเรียนทั้งหมด">
        <div class="stat-icon navy"><i class="fas fa-users"></i></div>
        <div class="stat-info">
            <div class="stat-value">{{ $stats['total'] }}</div>
            <div class="stat-label">นักเรียนทั้งหมด</div>
        </div>
    </a>
    <a href="{{ route('teacher.classroom.index') }}" class="stat-card red" style="text-decoration:none; cursor:pointer;" title="ดูนักเรียนกลุ่มเสี่ยง">
        <div class="stat-icon red"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="stat-info">
            <div class="stat-value">{{ $stats['risk'] }}</div>
            <div class="stat-label">นักเรียนเสี่ยง</div>
        </div>
    </a>
    <a href="{{ route('teacher.attendance.index') }}" class="stat-card gold" style="text-decoration:none; cursor:pointer;" title="เช็กชื่อเข้าแถว">
        <div class="stat-icon gold"><i class="fas fa-user-times"></i></div>
        <div class="stat-info">
            <div class="stat-value">{{ $stats['today_absent'] }}</div>
            <div class="stat-label">ขาดแถววันนี้</div>
        </div>
    </a>
    <a href="{{ route('teacher.behavior-records.index', ['status' => 'รออนุมัติ']) }}" class="stat-card green" style="text-decoration:none; cursor:pointer;" title="ดูบันทึกพฤติกรรมรอตรวจสอบ">
        <div class="stat-icon green"><i class="fas fa-clock"></i></div>
        <div class="stat-info">
            <div class="stat-value">{{ $stats['pending'] }}</div>
            <div class="stat-label">บันทึกรอตรวจสอบ</div>
        </div>
    </a>
</div>

<div class="responsive-grid-dashboard">
    {{-- Attendance Today --}}
    <div class="card">
        <div class="card-header-bar">
            <h3><i class="fas fa-calendar-check" style="color:var(--gold); margin-right:0.5rem"></i>การเข้าแถววันนี้</h3>
            <a href="{{ route('teacher.attendance.index') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> เช็คชื่อ
            </a>
        </div>
        @if($recentAttendance->isEmpty())
        <div style="text-align:center; padding:2rem; color:var(--text-muted); font-size:0.875rem;">
            ยังไม่ได้เช็คชื่อวันนี้
            <br><a href="{{ route('teacher.attendance.index') }}" class="btn btn-gold btn-sm" style="margin-top:0.75rem;">เช็คชื่อตอนนี้</a>
        </div>
        @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>นักเรียน</th>
                        <th style="text-align:center;">สถานะ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentAttendance as $att)
                    <tr>
                        <td>
                            <div style="font-weight:600; color:var(--text);">{{ $att->student->FullName }}</div>
                            <div style="font-size:0.75rem; color:var(--text-muted); display:flex; align-items:center; gap:0.25rem; margin-top:0.15rem;">
                                <i class="far fa-id-card" style="font-size:0.7rem;"></i> รหัสประจำตัว: <strong style="color:var(--text);">{{ $att->student->StudentID }}</strong>
                                @if($att->student->classroom_display)
                                    <span style="margin: 0 0.2rem;">&bull;</span>
                                    <span>ชั้น {{ $att->student->classroom_display }}</span>
                                @endif
                            </div>
                        </td>
                        <td style="text-align:center;">
                            @php
                                $ac = match($att->Status) { 'มา' => 'badge-green', 'สาย' => 'badge-orange', 'ขาด' => 'badge-red', default => 'badge-gray' };
                            @endphp
                            <span class="badge {{ $ac }}">{{ $att->Status }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- Student Risk Summary & Behavior Criteria --}}
    <div>
        <div class="card">
            <div class="card-header-bar">
                <h3><i class="fas fa-chart-pie" style="color:var(--gold); margin-right:0.5rem"></i>สรุปสถานะ</h3>
            </div>
            <div style="padding:1.25rem;">
                @foreach(['ปกติ' => ['green','check', ['ปกติ']], 'ตักเตือน' => ['orange','exclamation', ['ตักเตือน', 'เฝ้าระวัง']], 'ทัณฑ์บน' => ['red','times', ['ทัณฑ์บน', 'วิกฤต']]] as $status => [$color, $icon, $matchList])
                @php $count = $students->whereIn('RiskStatus', $matchList)->count(); @endphp
                <div style="display:flex; align-items:center; justify-content:space-between; padding:0.5rem 0; border-bottom:1px solid #f0ece4;">
                    <span style="display:flex; align-items:center; gap:0.5rem; font-size:0.875rem;">
                        <i class="fas fa-{{ $icon }}-circle" style="color:var(--{{ $color }}); width:16px;"></i>
                        {{ $status }}
                    </span>
                    <span style="font-weight:700; font-size:1rem; color:var(--{{ $color }})">{{ $count }}</span>
                </div>
                @endforeach

                <a href="{{ route('teacher.classroom.index') }}" class="btn btn-outline btn-sm" style="width:100%; margin-top:1rem; justify-content:center;">
                    <i class="fas fa-door-open"></i> ดูรายชื่อห้องเรียน
                </a>
            </div>
        </div>


    </div>
</div>
@endsection