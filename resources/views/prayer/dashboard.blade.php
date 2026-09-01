@extends('layouts.app')

@section('title', 'รายงานสรุปผลการละหมาด')
@section('page-title', 'แดชบอร์ดสรุปผลการละหมาด')

@push('styles')
<style>
    :root {
        --islamic-primary: #0D5C3A;
        --islamic-emerald: #10B981;
        --islamic-gold: #C5A85C;
        --islamic-gold-pale: rgba(197, 168, 92, 0.08);
        --islamic-bg: #F4F9F6;
    }

    /* Live Today Banner */
    .today-live-banner {
        background: linear-gradient(135deg, #064e3b 0%, #0d5c3a 50%, #059669 100%);
        border-radius: 12px;
        padding: 1.25rem 1.5rem;
        color: #fff;
        margin-bottom: 1.5rem;
        box-shadow: 0 4px 15px rgba(6, 78, 59, 0.15);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1.5rem;
        flex-wrap: wrap;
    }

    .today-live-title {
        display: flex;
        align-items: center;
        gap: 0.85rem;
    }

    .today-live-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.15);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        color: #fde047;
        backdrop-filter: blur(4px);
    }

    .today-stats-group {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        align-items: center;
    }

    .today-stat-pill {
        background: rgba(255, 255, 255, 0.12);
        padding: 0.5rem 0.9rem;
        border-radius: 8px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(4px);
        text-align: center;
    }

    .today-stat-pill .num {
        font-size: 1.25rem;
        font-weight: 800;
        color: #fff;
        line-height: 1.1;
    }

    .today-stat-pill .lbl {
        font-size: 0.72rem;
        color: #a7f3d0;
        margin-top: 0.15rem;
    }

    /* Enhanced 6 Stat Grid */
    .stat-grid-6 {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .stat-card.purple::before { background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%); }
    .stat-icon.purple { background: rgba(124, 58, 237, 0.08); color: #7c3aed; }

    .stat-card.orange::before { background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%); }
    .stat-icon.orange { background: rgba(217, 119, 6, 0.08); color: #d97706; }

    .stat-card.islamic-primary::before { background: linear-gradient(135deg, var(--islamic-primary) 0%, var(--islamic-emerald) 100%); }
    .stat-card.islamic-gold::before { background: linear-gradient(135deg, var(--islamic-gold) 0%, #fef3c7 100%); }
    
    .stat-icon.islamic-primary { background: rgba(13, 92, 58, 0.06); color: var(--islamic-primary); }
    .stat-icon.islamic-gold { background: var(--islamic-gold-pale); color: var(--islamic-gold-dark, #a37d22); }

    /* Analytics Chart Grid */
    .analytics-grid {
        display: grid;
        grid-template-columns: 1.3fr 1fr;
        gap: 1.25rem;
        margin-bottom: 1.5rem;
    }

    @media (max-width: 992px) {
        .analytics-grid {
            grid-template-columns: 1fr;
        }
    }

    .chart-box {
        background: #fff;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        padding: 1.25rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }

    .chart-box-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid #f1f5f9;
    }

    .chart-box-header h4 {
        margin: 0;
        font-size: 0.98rem;
        color: var(--navy, #1e293b);
        display: flex;
        align-items: center;
        gap: 0.45rem;
        font-weight: 700;
    }

</style>
@endpush

@section('content')
<div class="prayer-container">

    <!-- 1. Today's Quick Live Status Banner -->
    <div class="today-live-banner">
        <div class="today-live-title">
            <div class="today-live-icon">
                <i class="fas fa-star-and-crescent"></i>
            </div>
            <div>
                <div style="font-size:0.78rem; text-transform:uppercase; letter-spacing:0.05em; opacity:0.9; color:#a7f3d0;">
                    <i class="fas fa-satellite-dish" style="animation: pulse 2s infinite;"></i> สถานะการละหมาดวันนี้ (Real-Time)
                </div>
                <div style="font-size:1.15rem; font-weight:800; line-height:1.2; margin-top:0.15rem;">
                    {{ $todayStats['date_thai'] }}
                </div>
            </div>
        </div>

        <div class="today-stats-group">
            <div class="today-stat-pill">
                <div class="num">{{ $todayStats['zuhur_count'] }}</div>
                <div class="lbl">☀️ ซุฮรี (เที่ยง)</div>
            </div>
            <div class="today-stat-pill">
                <div class="num">{{ $todayStats['asr_count'] }}</div>
                <div class="lbl">🌤️ อัศรี (บ่าย)</div>
            </div>
            <div class="today-stat-pill">
                <div class="num" style="color:#e9d5ff;">{{ $todayStats['exempt_count'] }}</div>
                <div class="lbl">🌸 มีรอบเดือน</div>
            </div>
            <div class="today-stat-pill" style="background:rgba(0,0,0,0.2);">
                <div class="num" style="color:#fde047;">{{ $todayStats['checked_students'] }}/{{ $todayStats['total_students'] }}</div>
                <div class="lbl">👥 เช็คแล้ววันนี้</div>
            </div>
            <a href="{{ route('prayer.scan') }}" class="btn btn-sm" style="background:#fff; color:#064e3b; font-weight:700; border:none; padding:0.45rem 0.9rem; font-size:0.82rem; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.1);">
                <i class="fas fa-qrcode"></i> เช็คชื่อทันที
            </a>
        </div>
    </div>

    <!-- 2. Filters Card -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header-bar">
            <h3><i class="fas fa-filter" style="color:var(--islamic-gold);"></i> ตัวกรองรายงาน</h3>
        </div>
        <div class="card-body-pad">
            <form method="GET" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 0.85rem; align-items: end;">
                <div class="form-group" style="margin:0;">
                    <label class="form-label">ระดับชั้น</label>
                    <select name="grade" id="grade" class="form-control">
                        <option value="">ทั้งหมด</option>
                        @foreach($grades as $g)
                            <option value="{{ $g }}" {{ $grade == $g ? 'selected' : '' }}>{{ $g }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="margin:0;">
                    <label class="form-label">ห้องเรียน</label>
                    <select name="classroom" id="classroom" class="form-control">
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
                    <label class="form-label">ปีการศึกษา</label>
                    <select name="year" class="form-control">
                        @for($y = now()->year - 2; $y <= now()->year + 2; $y++)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y + 543 }}</option>
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

                <div class="form-group" style="margin:0;">
                    <label class="form-label">เพศ</label>
                    <select name="gender" class="form-control">
                        <option value="">ทั้งหมด (ชาย/หญิง)</option>
                        <option value="ชาย" {{ ($gender ?? '') == 'ชาย' ? 'selected' : '' }}>👨 ชาย</option>
                        <option value="หญิง" {{ ($gender ?? '') == 'หญิง' ? 'selected' : '' }}>👩 หญิง</option>
                    </select>
                </div>

                <div style="display:flex; gap:0.5rem;">
                    <button type="submit" class="btn btn-primary" style="flex:1; justify-content:center;">
                        <i class="fas fa-search"></i> ค้นหา
                    </button>
                    <a href="{{ route('prayer.dashboard') }}" class="btn btn-outline">ล้าง</a>
                </div>
            </form>
        </div>
    </div>

    <!-- 3. Enhanced 6 Summary Stat Cards -->
    <div class="stat-grid-6">
        <div class="stat-card islamic-primary">
            <div class="stat-icon islamic-primary"><i class="fas fa-users"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $schoolTotalStudents }}</div>
                <div class="stat-label">นักเรียนทั้งหมด</div>
            </div>
        </div>

        <div class="stat-card green">
            <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $schoolPassCount }}</div>
                <div class="stat-label">ผ่านเกณฑ์ (>= 80%)</div>
            </div>
        </div>

        <div class="stat-card red">
            <div class="stat-icon red"><i class="fas fa-times-circle"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $schoolFailCount }}</div>
                <div class="stat-label">ไม่ผ่านเกณฑ์ (< 80%)</div>
            </div>
        </div>

        <div class="stat-card purple">
            <div class="stat-icon purple"><i class="fas fa-female"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $schoolExemptStudentsCount }} <span style="font-size:0.75rem; font-weight:normal; color:#6b7280;">({{ $schoolTotalExempt }} คาบ)</span></div>
                <div class="stat-label">ละหมาดไม่ได้ (มีรอบเดือน)</div>
            </div>
        </div>

        <div class="stat-card orange">
            <div class="stat-icon orange"><i class="fas fa-user-check"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $schoolCorrectedCount }}</div>
                <div class="stat-label">แก้ละหมาดแล้ว</div>
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

    <!-- 4. Interactive Analytics Charts Section -->
    <div class="analytics-grid">
        <!-- Classroom Comparison Chart -->
        <div class="chart-box">
            <div class="chart-box-header">
                <h4><i class="fas fa-chart-bar" style="color:var(--islamic-emerald);"></i> เปรียบเทียบ % การละหมาดรายห้องเรียน</h4>
                <span style="font-size:0.75rem; color:#64748b;"><i class="fas fa-info-circle"></i> เกณฑ์ผ่าน 80%</span>
            </div>
            <div style="height: 250px; position: relative;">
                <canvas id="classroomChart"></canvas>
            </div>
        </div>

        <!-- Prayer Period Analytics (Zuhur vs Asr) -->
        <div class="chart-box">
            <div class="chart-box-header">
                <h4><i class="fas fa-chart-pie" style="color:var(--islamic-gold);"></i> สถิติช่วงเวลา ซุฮรี vs อัศรี</h4>
                <span style="font-size:0.75rem; color:#64748b;">(ประจำเดือนนี้)</span>
            </div>
            <div style="display:grid; grid-template-columns: 140px 1fr; gap:1rem; align-items:center; height:250px;">
                <div style="height: 180px; position:relative;">
                    <canvas id="periodDoughnutChart"></canvas>
                </div>
                <div style="display:flex; flex-direction:column; gap:0.75rem; font-size:0.82rem;">
                    <!-- Zuhur Bar -->
                    <div style="background:#f8fafc; padding:0.6rem 0.75rem; border-radius:8px; border-left:3px solid #0d5c3a;">
                        <div style="display:flex; justify-content:space-between; font-weight:700; margin-bottom:0.25rem;">
                            <span>☀️ ซุฮรี (เที่ยง)</span>
                            <span style="color:#0d5c3a;">{{ $periodAnalytics['zuhur']['percent'] }}%</span>
                        </div>
                        <div style="font-size:0.75rem; color:#64748b;">
                            มา: <strong>{{ $periodAnalytics['zuhur']['prayed'] }}</strong> | ขาด: <strong>{{ $periodAnalytics['zuhur']['absent'] }}</strong> | ยกเว้น: <strong>{{ $periodAnalytics['zuhur']['exempt'] }}</strong>
                        </div>
                    </div>
                    <!-- Asr Bar -->
                    <div style="background:#f8fafc; padding:0.6rem 0.75rem; border-radius:8px; border-left:3px solid #c5a85c;">
                        <div style="display:flex; justify-content:space-between; font-weight:700; margin-bottom:0.25rem;">
                            <span>🌤️ อัศรี (บ่าย)</span>
                            <span style="color:#a37d22;">{{ $periodAnalytics['asr']['percent'] }}%</span>
                        </div>
                        <div style="font-size:0.75rem; color:#64748b;">
                            มา: <strong>{{ $periodAnalytics['asr']['prayed'] }}</strong> | ขาด: <strong>{{ $periodAnalytics['asr']['absent'] }}</strong> | ยกเว้น: <strong>{{ $periodAnalytics['asr']['exempt'] }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. Export & Individual List -->
    <div class="card">
        <div class="card-header-bar" style="flex-wrap:wrap; gap:0.75rem; align-items:center; justify-content:space-between;">
            <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
                <h3 style="margin:0; white-space:nowrap;">
                    <i class="fas fa-clipboard-list" style="color:var(--islamic-gold);"></i> ตารางสรุปผลละหมาดรายบุคคล
                    <span id="studentCountBadge" class="badge badge-primary" style="font-size:0.8rem; margin-left:0.35rem; font-weight:normal;">
                        {{ count($studentStats) }} คน
                    </span>
                </h3>
            </div>
            
            <div style="display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap;">
                <!-- Search Box inside Table Header Toolbar -->
                <div style="position:relative; width:250px; max-width:100%;">
                    <input type="text" id="searchInput" class="form-control" 
                           value="{{ $search ?? request('search') }}" 
                           placeholder="พิมพ์รหัส หรือ ชื่อนักเรียน..." 
                           autocomplete="off"
                           style="padding-left:2.2rem; padding-right:2rem; height:36px; font-size:0.85rem; border-radius:8px; border:1px solid #cbd5e1; background:#fff; box-shadow: 0 1px 2px rgba(0,0,0,0.04);">
                    <i class="fas fa-search" style="position:absolute; left:0.75rem; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:0.85rem; pointer-events:none;"></i>
                    <button type="button" id="clearSearchBtn" style="position:absolute; right:0.6rem; top:50%; transform:translateY(-50%); border:none; background:transparent; color:#94a3b8; cursor:pointer; font-size:0.85rem; display:{{ !empty($search ?? request('search')) ? 'block' : 'none' }};" title="ล้างคำค้นหา">
                        <i class="fas fa-times-circle"></i>
                    </button>
                </div>

                <!-- Gender Quick Filter Pills -->
                <div style="display:inline-flex; background:#f1f5f9; border-radius:10px; padding:3px; gap:4px; border:1px solid #cbd5e1;">
                    <a href="{{ request()->fullUrlWithQuery(['gender' => '']) }}" 
                       class="btn btn-sm {{ empty($gender) ? 'btn-primary' : 'btn-outline' }}" style="border-radius:7px; padding:0.25rem 0.75rem; font-size:0.8rem;">
                       <i class="fas fa-users"></i> ทั้งหมด
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['gender' => 'ชาย']) }}" 
                       class="btn btn-sm {{ ($gender ?? '') == 'ชาย' ? 'btn-primary' : 'btn-outline' }}" style="border-radius:7px; padding:0.25rem 0.75rem; font-size:0.8rem; {{ ($gender ?? '') == 'ชาย' ? 'background:#2563eb; border-color:#2563eb; color:#fff;' : '' }}">
                       <i class="fas fa-mars" style="color:{{ ($gender ?? '') == 'ชาย' ? '#fff' : '#2563eb' }};"></i> ชาย
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['gender' => 'หญิง']) }}" 
                       class="btn btn-sm {{ ($gender ?? '') == 'หญิง' ? 'btn-primary' : 'btn-outline' }}" style="border-radius:7px; padding:0.25rem 0.75rem; font-size:0.8rem; {{ ($gender ?? '') == 'หญิง' ? 'background:#ec4899; border-color:#ec4899; color:#fff;' : '' }}">
                       <i class="fas fa-venus" style="color:{{ ($gender ?? '') == 'หญิง' ? '#fff' : '#ec4899' }};"></i> หญิง
                    </a>
                </div>

                <!-- PDF Export Link (Print View) -->
                <a href="{{ route('prayer.export', ['type' => 'monthly', 'month' => $month, 'year' => $year, 'grade' => $grade, 'classroom' => $classroom, 'gender' => $gender, 'passing_status' => $passingStatus, 'search' => $search ?? request('search')]) }}" 
                   target="_blank" class="btn btn-outline btn-sm">
                    <i class="fas fa-file-pdf" style="color:var(--red);"></i> ส่งออก PDF
                </a>
                
                <!-- Excel Export Link -->
                <a href="{{ route('prayer.export', ['type' => 'monthly', 'month' => $month, 'year' => $year, 'grade' => $grade, 'classroom' => $classroom, 'gender' => $gender, 'passing_status' => $passingStatus, 'search' => $search ?? request('search'), 'excel' => 1]) }}" 
                   class="btn btn-outline btn-sm">
                    <i class="fas fa-file-excel" style="color:var(--green);"></i> ส่งออก Excel
                </a>
            </div>
        </div>
        
        <div style="padding:1rem 1.5rem; background:#f8fafc; font-size:0.82rem; color:var(--text-muted); border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.5rem;">
            <div>
                <i class="fas fa-info-circle"></i> เซสชันการเช็กชื่อในเดือนนี้ทั้งหมด: <strong>{{ $totalActiveSessions }}</strong> คาบ 
                (เปอร์เซ็นต์การละหมาดคำนวณจาก: <code>(ละหมาด ÷ (คาบเช็กทั้งหมด - ละหมาดไม่ได้)) × 100</code>)
            </div>
        </div>

        <div class="table-wrap">
            <table id="prayerTable">
                <thead>
                    <tr>
                        <th>รหัสนักเรียน</th>
                        <th>ชื่อ-สกุล</th>
                        <th>ระดับชั้น/ห้อง</th>
                        <th>เพศ</th>
                        <th style="text-align:center;">จำนวนครั้งที่ละหมาด</th>
                        <th style="text-align:center;">มีรอบเดือน</th>
                        <th style="text-align:center;">จำนวนครั้งที่ขาด</th>
                        <th style="text-align:center;">เปอร์เซ็นต์ละหมาด</th>
                        <th style="text-align:center;">เกณฑ์การละหมาด</th>
                        <th style="text-align:right;">ปฏิทิน</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($studentStats as $stat)
                    <tr class="student-row" data-studentid="{{ $stat['student']->StudentID }}" data-name="{{ $stat['student']->FullName }}">
                        <td><code style="font-size:0.85rem; background:#f0ece4; padding:0.1rem 0.4rem; border-radius:2px; font-weight:600;">{{ $stat['student']->StudentID }}</code></td>
                        <td><strong>{{ $stat['student']->FullName }}</strong></td>
                        <td>{{ $stat['student']->classroom_display }}</td>
                        <td>{{ $stat['student']->Gender ?? '-' }}</td>
                        <td style="text-align:center; font-weight:700; color:var(--green);">
                            {{ $stat['prayed'] }}
                        </td>
                        <td style="text-align:center; color:#7c3aed; font-weight:600;">
                            {{ $stat['exempt'] > 0 ? $stat['exempt'] : '-' }}
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
                                                <i class="fas fa-edit"></i> แก้ละหมาด
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
                    <tr id="emptyRow">
                        <td colspan="10" style="text-align:center; color:var(--text-muted); padding:2rem;">
                            <i class="fas fa-user-slash" style="font-size:1.5rem; margin-bottom:0.5rem; display:block; opacity:0.5;"></i>
                            ไม่พบข้อมูลสถิตินักเรียนที่ตรงกับเงื่อนไข
                        </td>
                    </tr>
                    @endforelse
                    <tr id="noMatchRow" style="display:none;">
                        <td colspan="10" style="text-align:center; color:var(--text-muted); padding:2rem;">
                            <i class="fas fa-search" style="font-size:1.5rem; margin-bottom:0.5rem; display:block; opacity:0.5;"></i>
                            ไม่พบข้อมูลนักเรียนที่ตรงกับคำค้นหา
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // === 1. Live instant search filter by student ID or Name ===
    const searchInput = document.getElementById('searchInput');
    const clearSearchBtn = document.getElementById('clearSearchBtn');
    const tableBody = document.querySelector('#prayerTable tbody');
    const studentRows = document.querySelectorAll('.student-row');
    const noMatchRow = document.getElementById('noMatchRow');
    const countBadge = document.getElementById('studentCountBadge');

    if (searchInput && studentRows.length > 0) {
        function filterTable() {
            const query = searchInput.value.trim().toLowerCase();
            let visibleCount = 0;

            if (clearSearchBtn) {
                clearSearchBtn.style.display = query !== '' ? 'block' : 'none';
            }

            studentRows.forEach(row => {
                const text = row.innerText.toLowerCase();
                if (query === '' || text.includes(query)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            if (noMatchRow) {
                noMatchRow.style.display = (visibleCount === 0 && query !== '') ? '' : 'none';
            }

            if (countBadge) {
                countBadge.textContent = visibleCount + ' คน';
            }
        }

        searchInput.addEventListener('input', filterTable);

        if (clearSearchBtn) {
            clearSearchBtn.addEventListener('click', function() {
                searchInput.value = '';
                filterTable();
                searchInput.focus();
            });
        }

        if (searchInput.value.trim() !== '') {
            filterTable();
        }
    }

    // === 2. Dependent classroom dropdown filter ===
    const gradeSelect = document.getElementById('grade');
    const classroomSelect = document.getElementById('classroom');
    
    if (gradeSelect && classroomSelect) {
        const originalClassrooms = Array.from(classroomSelect.options).map(opt => ({
            value: opt.value,
            text: opt.text,
            selected: opt.selected
        }));
        
        function updateClassrooms() {
            const selectedGrade = gradeSelect.value;
            const currentSelectedValue = classroomSelect.value;
            
            classroomSelect.innerHTML = '';
            
            originalClassrooms.forEach(optData => {
                if (optData.value === '') {
                    const opt = document.createElement('option');
                    opt.value = optData.value;
                    opt.text = optData.text;
                    opt.selected = optData.selected;
                    classroomSelect.appendChild(opt);
                    return;
                }
                
                const belongsToGrade = !selectedGrade || optData.value === selectedGrade || optData.value.startsWith(selectedGrade + '/');
                
                if (belongsToGrade) {
                    const opt = document.createElement('option');
                    opt.value = optData.value;
                    opt.text = optData.text;
                    if (optData.value === currentSelectedValue) {
                        opt.selected = true;
                    }
                    classroomSelect.appendChild(opt);
                }
            });
        }
        
        gradeSelect.addEventListener('change', updateClassrooms);
        updateClassrooms();
    }

    // === 3. Classroom Ranking Bar Chart ===
    const classroomData = @json($classroomRanking);
    if (classroomData && classroomData.length > 0 && document.getElementById('classroomChart')) {
        const labels = classroomData.map(c => c.name);
        const dataValues = classroomData.map(c => c.avg_percentage);
        const bgColors = dataValues.map(v => v >= 80 ? 'rgba(16, 185, 129, 0.85)' : (v >= 60 ? 'rgba(245, 158, 11, 0.85)' : 'rgba(239, 68, 68, 0.85)'));
        const borderColors = dataValues.map(v => v >= 80 ? '#059669' : (v >= 60 ? '#d97706' : '#dc2626'));

        new Chart(document.getElementById('classroomChart'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '% การละหมาดเฉลี่ย',
                    data: dataValues,
                    backgroundColor: bgColors,
                    borderColor: borderColors,
                    borderWidth: 1.5,
                    borderRadius: 6,
                    maxBarThickness: 45
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                return ` ละหมาดเฉลี่ย: ${ctx.parsed.y}%`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) { return value + '%'; },
                            font: { size: 11 }
                        },
                        grid: { color: '#f1f5f9' }
                    },
                    x: {
                        ticks: { font: { size: 11 } },
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // === 4. Period Analytics Doughnut Chart ===
    const periodStats = @json($periodAnalytics);
    const doughnutCanvas = document.getElementById('periodDoughnutChart');
    if (periodStats && doughnutCanvas) {
        const totalZuhur = Number(periodStats.zuhur?.prayed || 0);
        const totalAsr = Number(periodStats.asr?.prayed || 0);
        const totalExempt = Number(periodStats.zuhur?.exempt || 0) + Number(periodStats.asr?.exempt || 0);
        const totalAbsent = Number(periodStats.zuhur?.absent || 0) + Number(periodStats.asr?.absent || 0);
        const sumAll = totalZuhur + totalAsr + totalExempt + totalAbsent;

        if (sumAll === 0) {
            new Chart(doughnutCanvas, {
                type: 'doughnut',
                data: {
                    labels: ['ยังไม่มีการเช็กชื่อในเดือนนี้'],
                    datasets: [{
                        data: [1],
                        backgroundColor: ['#e2e8f0'],
                        borderWidth: 2,
                        borderColor: '#f8fafc'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function() {
                                    return ' ยังไม่มีประวัติการละหมาดในเดือนนี้';
                                }
                            }
                        }
                    },
                    cutout: '72%'
                }
            });
        } else {
            new Chart(doughnutCanvas, {
                type: 'doughnut',
                data: {
                    labels: ['ซุฮรี (มา)', 'อัศรี (มา)', 'มีรอบเดือน', 'ขาดละหมาด'],
                    datasets: [{
                        data: [totalZuhur, totalAsr, totalExempt, totalAbsent],
                        backgroundColor: [
                            '#0d5c3a',
                            '#c5a85c',
                            '#a855f7',
                            '#ef4444'
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff',
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    return ` ${ctx.label}: ${ctx.parsed} ครั้ง`;
                                }
                            }
                        }
                    },
                    cutout: '68%'
                }
            });
        }
    }
});
</script>
@endpush
