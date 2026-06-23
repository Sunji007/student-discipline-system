@extends('layouts.app')

@section('title', 'รายงานสรุปผลการละหมาด')
@section('page-title', 'รายงานสรุปผลการละหมาดประจำเดือน')

@push('styles')
<style>
    :root {
        --islamic-primary: #0D5C3A;
        --islamic-emerald: #10B981;
        --islamic-gold: #C5A85C;
        --islamic-gold-pale: rgba(197, 168, 92, 0.08);
        --islamic-bg: #F4F9F6;
    }

    .stat-card.islamic-primary::before { background: linear-gradient(135deg, var(--islamic-primary) 0%, var(--islamic-emerald) 100%); }
    .stat-card.islamic-gold::before { background: linear-gradient(135deg, var(--islamic-gold) 0%, #fef3c7 100%); }
    
    .stat-icon.islamic-primary { background: rgba(13, 92, 58, 0.06); color: var(--islamic-primary); }
    .stat-icon.islamic-gold { background: var(--islamic-gold-pale); color: var(--islamic-gold-dark, #a37d22); }
</style>
@endpush

@section('content')
<div class="prayer-container">
    <!-- Filters Card -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header-bar">
            <h3><i class="fas fa-filter" style="color:var(--islamic-gold);"></i> ตัวกรองรายงาน</h3>
        </div>
        <div class="card-body-pad">
            <form method="GET" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 1rem; align-items: end;">
                <div class="form-group" style="margin:0;">
                    <label class="form-label">ระดับชั้น</label>
                    <select name="grade" class="form-control">
                        <option value="">ทั้งหมด</option>
                        @foreach($grades as $g)
                            <option value="{{ $g }}" {{ $grade == $g ? 'selected' : '' }}>{{ $g }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="margin:0;">
                    <label class="form-label">ห้องเรียน</label>
                    <select name="classroom" class="form-control">
                        <option value="">ทั้งหมด</option>
                        @foreach($classrooms as $c)
                            <option value="{{ $c }}" {{ $classroom == $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="margin:0;">
                    <label class="form-label">เดือน</label>
                    <select name="month" class="form-control">
                        @foreach([1=>'มกราคม', 2=>'กุมภาพันธ์', 3=>'มีนาคม', 4=>'เมษายน', 5=>'พฤษภาคม', 6=>'มิถุนายน', 7=>'กรกฎาคม', 8=>'สิงหาคม', 9=>'กันยายน', 10=>'ตุลาคม', 11=>'พฤศจิกายน', 12=>'ธันวาคม'] as $num => $name)
                            <option value="{{ $num }}" {{ $month == $num ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="margin:0;">
                    <label class="form-label">ปี ค.ศ.</label>
                    <select name="year" class="form-control">
                        @for($y = now()->year - 2; $y <= now()->year + 2; $y++)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y + 543 }} ({{ $y }})</option>
                        @endfor
                    </select>
                </div>

                <div class="form-group" style="margin:0;">
                    <label class="form-label">เกณฑ์การละหมาด</label>
                    <select name="passing_status" class="form-control">
                        <option value="">ทั้งหมด</option>
                        <option value="pass" {{ $passingStatus == 'pass' ? 'selected' : '' }}>ผ่านเกณฑ์ (80% ขึ้นไป)</option>
                        <option value="fail" {{ $passingStatus == 'fail' ? 'selected' : '' }}>ไม่ผ่านเกณฑ์ (ต่ำกว่า 80%)</option>
                    </select>
                </div>

                <div style="display:flex; gap:0.5rem;">
                    <button type="submit" class="btn btn-primary" style="flex:1; justify-content:center;">
                        <i class="fas fa-sync"></i> อัปเดต
                    </button>
                    <a href="{{ route('prayer.dashboard') }}" class="btn btn-outline">ล้าง</a>
                </div>
            </form>
        </div>
    </div>

    <!-- School-wide Summary Stats -->
    <div class="stat-grid">
        <div class="stat-card islamic-primary">
            <div class="stat-icon islamic-primary"><i class="fas fa-users"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $schoolTotalStudents }}</div>
                <div class="stat-label">นักเรียนทั้งหมด</div>
            </div>
        </div>

        <div class="stat-card green">
            <div class="stat-icon green"><i class="fas fa-check-double"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $schoolTotalPrayed }}</div>
                <div class="stat-label">จำนวนครั้งที่ละหมาด</div>
            </div>
        </div>

        <div class="stat-card red">
            <div class="stat-icon red"><i class="fas fa-user-times"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $schoolTotalAbsent }}</div>
                <div class="stat-label">จำนวนครั้งที่ขาด</div>
            </div>
        </div>

        <div class="stat-card islamic-gold">
            <div class="stat-icon islamic-gold"><i class="fas fa-percentage"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ round($schoolPercentage, 1) }}%</div>
                <div class="stat-label">เปอร์เซ็นต์ละหมาดรวม</div>
            </div>
        </div>
    </div>

    <!-- Export & Individual List -->
    <div class="card">
        <div class="card-header-bar" style="flex-wrap:wrap; gap:1rem;">
            <h3><i class="fas fa-clipboard-list" style="color:var(--islamic-gold);"></i> ตารางสรุปผลละหมาดรายบุคคล</h3>
            
            <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
                <!-- PDF Export Link (Print View) -->
                <a href="{{ route('prayer.export', ['type' => 'monthly', 'month' => $month, 'year' => $year, 'grade' => $grade, 'classroom' => $classroom, 'passing_status' => $passingStatus]) }}" 
                   target="_blank" class="btn btn-outline btn-sm">
                    <i class="fas fa-file-pdf" style="color:var(--red);"></i> ส่งออก PDF
                </a>
                
                <!-- Excel Export Link -->
                <a href="{{ route('prayer.export', ['type' => 'monthly', 'month' => $month, 'year' => $year, 'grade' => $grade, 'classroom' => $classroom, 'passing_status' => $passingStatus, 'excel' => 1]) }}" 
                   class="btn btn-outline btn-sm">
                    <i class="fas fa-file-excel" style="color:var(--green);"></i> ส่งออก Excel
                </a>
            </div>
        </div>
        
        <div style="padding:1rem 1.5rem; background:#f8fafc; font-size:0.82rem; color:var(--text-muted); border-bottom:1px solid var(--border);">
            <i class="fas fa-info-circle"></i> เซสชันการเช็กชื่อในเดือนนี้ทั้งหมด: <strong>{{ $totalActiveSessions }}</strong> คาบ 
            (เปอร์เซ็นต์การละหมาดคำนวณจาก: <code>(ละหมาด ÷ (คาบเช็กทั้งหมด - ละหมาดไม่ได้)) × 100</code>)
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>รหัสนักเรียน</th>
                        <th>ชื่อ-สกุล</th>
                        <th>ระดับชั้น/ห้อง</th>
                        <th>เพศ</th>
                        <th style="text-align:center;">จำนวนครั้งที่ละหมาด</th>
                        <th style="text-align:center;">จำนวนครั้งที่ขาด</th>
                        <th style="text-align:center;">เปอร์เซ็นต์ละหมาด</th>
                        <th style="text-align:center;">เกณฑ์การละหมาด</th>
                        <th style="text-align:right;">ปฏิทิน</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($studentStats as $stat)
                    <tr>
                        <td><code style="font-size:0.85rem; background:#f0ece4; padding:0.1rem 0.4rem; border-radius:2px;">{{ $stat['student']->StudentID }}</code></td>
                        <td><strong>{{ $stat['student']->FullName }}</strong></td>
                        <td>{{ $stat['student']->classroom_display }}</td>
                        <td>{{ $stat['student']->Gender ?? '-' }}</td>
                        <td style="text-align:center; font-weight:700; color:var(--green);">
                            {{ $stat['prayed'] }}
                        </td>
                        <td style="text-align:center; font-weight:700; color:var(--red);">
                            {{ $stat['absent'] }}
                        </td>
                        <td style="text-align:center;">
                            <div style="font-weight:700; color: {{ $stat['percent'] >= 80 ? 'var(--green)' : ($stat['percent'] >= 60 ? 'var(--orange)' : 'var(--red)') }}">
                                {{ $stat['percent'] }}%
                            </div>
                        </td>
                        <td style="text-align:center;">
                            @if($stat['percent'] >= 80)
                                <span class="badge badge-green" style="font-size:0.75rem;"><i class="fas fa-check-circle"></i> ผ่านเกณฑ์</span>
                            @else
                                @if($stat['is_corrected'])
                                    <div style="display:flex; flex-direction:column; gap:0.25rem; align-items:center;">
                                        <span class="badge badge-primary" style="font-size:0.75rem; background: rgba(59, 130, 246, 0.1); color: #1d4ed8; border: 1px solid rgba(59, 130, 246, 0.2);"><i class="fas fa-user-check"></i> แก้ละหมาดแล้ว</span>
                                        <form action="{{ route('prayer.corrections.toggle') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="student_id" value="{{ $stat['student']->StudentID }}">
                                            <input type="hidden" name="month" value="{{ $month }}">
                                            <input type="hidden" name="year" value="{{ $year }}">
                                            <button type="submit" class="btn btn-outline btn-sm" style="padding:0.15rem 0.4rem; font-size:0.7rem; border-color:#94a3b8; color:#475569;">
                                                <i class="fas fa-undo"></i> ยกเลิก
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <div style="display:flex; flex-direction:column; gap:0.25rem; align-items:center;">
                                        <span class="badge badge-red" style="font-size:0.75rem;"><i class="fas fa-times-circle"></i> ไม่ผ่านเกณฑ์</span>
                                        <form action="{{ route('prayer.corrections.toggle') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="student_id" value="{{ $stat['student']->StudentID }}">
                                            <input type="hidden" name="month" value="{{ $month }}">
                                            <input type="hidden" name="year" value="{{ $year }}">
                                            <button type="submit" class="btn btn-sm" style="background:#fef3c7; color:#d97706; border:1px solid #fde68a; padding:0.15rem 0.4rem; font-size:0.7rem;">
                                                <i class="fas fa-edit"></i> บันทึกแก้ละหมาด
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            @endif
                        </td>
                        <td style="text-align:right;">
                            <a href="{{ route('prayer.calendar', ['student_id' => $stat['student']->StudentID, 'month' => $month, 'year' => $year]) }}" 
                               class="btn btn-outline btn-sm" title="ดูปฏิทินของนักเรียนคนนี้">
                                <i class="fas fa-calendar-alt"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align:center; color:var(--text-muted); padding:2rem;">
                            ไม่พบข้อมูลสถิตินักเรียน
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
