@extends('layouts.app')

@section('title', 'การเข้าแถว')
@section('page-title', 'ประวัติการเข้าแถวของบุตรหลาน')

@section('content')
@php
    $prevMonth = \Carbon\Carbon::create($year, $mon, 1)->subMonth()->format('Y-m');
    $nextMonth = \Carbon\Carbon::create($year, $mon, 1)->addMonth()->format('Y-m');
    $thCarbon  = \Carbon\Carbon::create($year, $mon, 1)->locale('th');
    $thMonth   = $thCarbon->isoFormat('MMMM ') . ($year > 2400 ? $year : $year + 543);
    $firstDow  = (int)\Carbon\Carbon::create($year, $mon, 1)->dayOfWeek; // 0=Sun

    $mPresent = $monthSummary['มา'] ?? 0;
    $mLate    = $monthSummary['สาย'] ?? 0;
    $mAbsent  = $monthSummary['ขาด'] ?? 0;
    $mTotal   = $monthSummary['total'] ?? 0;
    $mRate    = $mTotal > 0 ? round(($mPresent / $mTotal) * 100) : 0;
    $mLateRate = $mTotal > 0 ? round(($mLate / $mTotal) * 100) : 0;
    $mAbsentRate = $mTotal > 0 ? round(($mAbsent / $mTotal) * 100) : 0;
@endphp

<div class="page-header" style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:1rem;">
    <div>
        <h2>ปฏิทินการเข้าแถว</h2>
        <p>{{ $student->FullName }} — ห้อง {{ $student->Classroom }}</p>
    </div>
    <button type="button" class="btn btn-outline" id="openMonthlyModalBtn" style="font-size:0.85rem; font-weight:600; padding:0.5rem 1.15rem; border-radius:10px; display:inline-flex; align-items:center; gap:0.5rem; background:#fff; box-shadow:var(--shadow-sm); border:1.5px solid var(--border); color:var(--navy); cursor:pointer; transition:all 0.2s ease;">
        <i class="fas fa-chart-pie" style="color:var(--primary); font-size:1rem;"></i> สรุปสถิติแต่ละเดือน (ร้อยละ %)
    </button>
</div>

{{-- Summary Cards (Clickable to open Monthly Breakdown Modal) --}}
<div class="stat-grid" style="margin-bottom:1.25rem;">
    <div class="stat-card green stat-card-clickable" data-open-modal="true" data-filter-type="มา" role="button" tabindex="0" title="คลิกดูสรุปการเข้าแถวแต่ละเดือน (ร้อยละ %)">
        <div class="stat-icon green"><i class="fas fa-check"></i></div>
        <div class="stat-info">
            <div style="display:flex; align-items:baseline; gap:0.45rem;">
                <div class="stat-value">{{ $mPresent }}</div>
                <span style="font-size:0.92rem; font-weight:700; color:var(--green);">
                    ({{ $mRate }}%)
                </span>
            </div>
            <div class="stat-label">เข้าแถว ({{ $thMonth }})</div>
            <div style="margin-top:0.45rem;">
                <span style="font-size:0.75rem; font-weight:700; color:#065f46; background:#d1fae5; padding:0.25rem 0.65rem; border-radius:6px; display:inline-flex; align-items:center; gap:0.35rem; border:1px solid #a7f3d0;">
                    <i class="fas fa-chart-pie"></i> คลิกดูแต่ละเดือน
                </span>
            </div>
        </div>
    </div>

    <div class="stat-card gold stat-card-clickable" data-open-modal="true" data-filter-type="สาย" role="button" tabindex="0" title="คลิกดูสรุปมาสายแต่ละเดือน (ร้อยละ %)">
        <div class="stat-icon gold"><i class="fas fa-clock"></i></div>
        <div class="stat-info">
            <div style="display:flex; align-items:baseline; gap:0.45rem;">
                <div class="stat-value">{{ $mLate }}</div>
                <span style="font-size:0.92rem; font-weight:700; color:var(--orange);">
                    ({{ $mLateRate }}%)
                </span>
            </div>
            <div class="stat-label">มาสาย ({{ $thMonth }})</div>
            <div style="margin-top:0.45rem;">
                <span style="font-size:0.75rem; font-weight:700; color:#92400e; background:#fef3c7; padding:0.25rem 0.65rem; border-radius:6px; display:inline-flex; align-items:center; gap:0.35rem; border:1px solid #fde68a;">
                    <i class="fas fa-chart-pie"></i> คลิกดูแต่ละเดือน
                </span>
            </div>
        </div>
    </div>

    <div class="stat-card red stat-card-clickable" data-open-modal="true" data-filter-type="ขาด" role="button" tabindex="0" title="คลิกดูสรุปขาดเข้าแถวแต่ละเดือน (ร้อยละ %)">
        <div class="stat-icon red"><i class="fas fa-times"></i></div>
        <div class="stat-info">
            <div style="display:flex; align-items:baseline; gap:0.45rem;">
                <div class="stat-value">{{ $mAbsent }}</div>
                <span style="font-size:0.92rem; font-weight:700; color:var(--red);">
                    ({{ $mAbsentRate }}%)
                </span>
            </div>
            <div class="stat-label">ขาดเข้าแถว ({{ $thMonth }})</div>
            <div style="margin-top:0.45rem;">
                <span style="font-size:0.75rem; font-weight:700; color:#7f1d1d; background:#fee2e2; padding:0.25rem 0.65rem; border-radius:6px; display:inline-flex; align-items:center; gap:0.35rem; border:1px solid #fecaca;">
                    <i class="fas fa-chart-pie"></i> คลิกดูแต่ละเดือน
                </span>
            </div>
        </div>
    </div>
</div>

<div class="card">
    {{-- Month Nav --}}
    <div class="card-header-bar" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.75rem;">
        <div style="display:flex; align-items:center;">
            <a href="{{ request()->url() }}?month={{ $prevMonth }}" class="btn btn-outline btn-sm" title="เดือนก่อนหน้า">
                <i class="fas fa-chevron-left"></i>
            </a>
            <h3 style="margin:0 1rem; font-size:1.05rem; font-weight:700; color:var(--navy);">{{ $thMonth }}</h3>
            <a href="{{ request()->url() }}?month={{ $nextMonth }}" class="btn btn-outline btn-sm" title="เดือนถัดไป">
                <i class="fas fa-chevron-right"></i>
            </a>
        </div>

        {{-- Standardized Filter Dropdown for Calendar --}}
        <div class="filter-dropdown-wrap">
            <label class="filter-dropdown-label" for="calStatusFilter">
                <i class="fas fa-filter"></i> ตัวเลือกสถานะ:
            </label>
            <select id="calStatusFilter" class="filter-dropdown-select">
                <option value="all">📋 ทั้งหมด ({{ $mTotal }})</option>
                <option value="มา">✅ เข้าแถว ({{ $mPresent }})</option>
                <option value="สาย">⏰ มาสาย ({{ $mLate }})</option>
                <option value="ขาด">❌ ขาด ({{ $mAbsent }})</option>
            </select>
        </div>
    </div>

    <div style="padding:1rem 1.25rem;">
        {{-- Day labels --}}
        <div style="display:grid; grid-template-columns:repeat(7, 1fr); gap:4px; margin-bottom:4px;">
            @foreach(['อา','จ','อ','พ','พฤ','ศ','ส'] as $d)
            <div style="text-align:center; font-size:0.72rem; font-weight:700; color:var(--text-muted); padding:0.25rem 0; letter-spacing:0.05em;">
                {{ $d }}
            </div>
            @endforeach
        </div>

        {{-- Calendar grid --}}
        <div style="display:grid; grid-template-columns:repeat(7, 1fr); gap:4px;" id="attendanceCalendarGrid">
            {{-- Empty cells before first day --}}
            @for($i = 0; $i < $firstDow; $i++)
            <div class="cal-empty-cell"></div>
            @endfor

            @for($day = 1; $day <= $daysInMonth; $day++)
            @php
                $dateStr = sprintf('%04d-%02d-%02d', $year, $mon, $day);
                $att = $records[$dateStr] ?? null;
                $isToday = $dateStr === now()->format('Y-m-d');
                $bg = $att ? match($att->Status) {
                    'มา'  => '#e8f8ef',
                    'สาย' => '#fff3e0',
                    'ขาด' => '#fdecea',
                } : ($isToday ? 'rgba(201,168,76,0.1)' : 'transparent');
                $textColor = $att ? match($att->Status) {
                    'มา'  => 'var(--green)',
                    'สาย' => 'var(--orange)',
                    'ขาด' => 'var(--red)',
                } : 'var(--text)';
                $border = $isToday ? '2px solid var(--gold)' : '1px solid #e8e3db';
            @endphp
            <div class="cal-day-cell" 
                 data-status="{{ $att ? $att->Status : '' }}"
                 data-day="{{ $day }}"
                 style="aspect-ratio:1; display:flex; flex-direction:column; align-items:center; justify-content:center;
                        background:{{ $bg }}; border:{{ $border }}; border-radius:4px; cursor:default;">
                <span style="font-size:0.85rem; font-weight:{{ $isToday ? '700' : '400' }}; color:{{ $textColor }};">
                    {{ $day }}
                </span>
                @if($att)
                <span style="font-size:0.6rem; font-weight:600; color:{{ $textColor }}; margin-top:1px;">
                    {{ $att->Status }}
                </span>
                @endif
            </div>
            @endfor
        </div>

        {{-- Filter Status Banner --}}
        <div id="attendanceFilterBanner" style="display:none; align-items:center; justify-content:space-between; margin-top:1.25rem; margin-bottom:0.5rem; padding:0.65rem 1rem; border-radius:10px; background:#f8fafc; border:1px solid #e2e8f0; transition:all 0.25s ease;">
            <div style="display:flex; align-items:center; gap:0.5rem; font-size:0.85rem; font-weight:600; color:var(--navy);">
                <span id="filterIndicatorDot" style="width:10px; height:10px; border-radius:50%; display:inline-block;"></span>
                <span id="attendanceFilterText">กำลังแสดงเฉพาะ: เข้าแถว</span>
            </div>
            <button type="button" id="clearAttendanceFilterBtn" class="btn btn-sm btn-outline" style="font-size:0.75rem; padding:0.25rem 0.75rem; border-radius:6px; cursor:pointer;">
                <i class="fas fa-times"></i> แสดงทั้งหมด / ล้างตัวกรอง
            </button>
        </div>

        {{-- Legend --}}
        <div style="display:flex; gap:1rem; margin-top:1rem; justify-content:center; flex-wrap:wrap;">
            @foreach(['มา' => ['#e8f8ef', 'var(--green)'], 'สาย' => ['#fff3e0', 'var(--orange)'], 'ขาด' => ['#fdecea', 'var(--red)']] as $label => [$bg, $color])
            <div style="display:flex; align-items:center; gap:0.4rem; font-size:0.78rem; color:var(--text-muted);">
                <span style="width:14px; height:14px; background:{{ $bg }}; border:1px solid {{ $color }}; border-radius:2px; display:inline-block;"></span>
                {{ $label }}
            </div>
            @endforeach
            <div style="display:flex; align-items:center; gap:0.4rem; font-size:0.78rem; color:var(--text-muted);">
                <span style="width:14px; height:14px; border:2px solid var(--gold); border-radius:2px; display:inline-block;"></span>
                วันนี้
            </div>
        </div>
    </div>
</div>

{{-- Modal สรุปสถิติการเข้าแถวแต่ละเดือน (Monthly Attendance Breakdown Modal) --}}
<div id="monthlyAttendanceModal" style="display:none; position:fixed; inset:0; background:rgba(15,14,52,0.6); backdrop-filter:blur(5px); z-index:9999; align-items:center; justify-content:center; padding:1rem;">
    <div style="background:#fff; border-radius:18px; max-width:680px; width:100%; max-height:90vh; display:flex; flex-direction:column; box-shadow:0 25px 60px rgba(0,0,0,0.3); overflow:hidden; animation:modalPop 0.25s ease-out;">
        
        <!-- Header -->
        <div style="background:linear-gradient(135deg, #0f0e34, #27256e); padding:1.25rem 1.5rem; display:flex; justify-content:space-between; align-items:center; color:#fff;">
            <div style="display:flex; align-items:center; gap:0.75rem;">
                <div style="width:42px; height:42px; border-radius:10px; background:rgba(255,255,255,0.12); display:flex; align-items:center; justify-content:center; font-size:1.25rem; color:var(--yellow);">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <div>
                    <h3 style="margin:0; font-size:1.15rem; font-weight:700; color:#fff;">สรุปสถิติการเข้าแถวแต่ละเดือน</h3>
                    <p style="margin:0.2rem 0 0; font-size:0.8rem; color:rgba(255,255,255,0.75);">
                        {{ $student->FullName }} (ห้อง {{ $student->Classroom }})
                    </p>
                </div>
            </div>
            <button type="button" class="close-monthly-modal" style="background:none; border:none; color:rgba(255,255,255,0.8); font-size:1.35rem; cursor:pointer; padding:0.25rem; line-height:1; transition:color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,0.8)'">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Body -->
        <div style="padding:1.5rem; overflow-y:auto; flex:1;">
            
            <!-- Overall Semester Card -->
            <div style="background:linear-gradient(135deg, #f8fafc, #f1f5f9); border:1.5px solid #e2e8f0; border-radius:12px; padding:1.1rem 1.25rem; margin-bottom:1.5rem; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem;">
                <div>
                    <div style="font-size:0.75rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.05em;">ภาพรวมทั้งภาคเรียน</div>
                    <div style="display:flex; align-items:baseline; gap:0.5rem; margin-top:0.25rem;">
                        <span style="font-size:2rem; font-weight:800; color:var(--navy); line-height:1;">
                            {{ $summary['rate'] }}%
                        </span>
                        <span style="font-size:0.85rem; color:var(--text-muted);">อัตราการเข้าแถวเฉลี่ย</span>
                    </div>
                </div>
                <div style="display:flex; gap:1.25rem; font-size:0.85rem;">
                    <div style="text-align:center;">
                        <div style="font-size:1.15rem; font-weight:700; color:var(--green);">{{ $summary['มา'] }}</div>
                        <div style="font-size:0.72rem; color:var(--text-muted);">เข้าแถว</div>
                    </div>
                    <div style="text-align:center;">
                        <div style="font-size:1.15rem; font-weight:700; color:var(--orange);">{{ $summary['สาย'] }}</div>
                        <div style="font-size:0.72rem; color:var(--text-muted);">มาสาย</div>
                    </div>
                    <div style="text-align:center;">
                        <div style="font-size:1.15rem; font-weight:700; color:var(--red);">{{ $summary['ขาด'] }}</div>
                        <div style="font-size:0.72rem; color:var(--text-muted);">ขาด</div>
                    </div>
                    <div style="text-align:center; border-left:1px solid #cbd5e1; padding-left:1.25rem;">
                        <div style="font-size:1.15rem; font-weight:700; color:var(--navy);">{{ $summary['total'] }}</div>
                        <div style="font-size:0.72rem; color:var(--text-muted);">รวม (วัน)</div>
                    </div>
                </div>
            </div>

            <div style="font-size:0.88rem; font-weight:700; color:var(--navy); margin-bottom:0.75rem; display:flex; align-items:center; gap:0.4rem;">
                <i class="fas fa-calendar-alt" style="color:var(--primary);"></i> รายละเอียดแยกตามแต่ละเดือน:
            </div>

            <!-- Table of Monthly Breakdown -->
            <div style="border:1px solid var(--border); border-radius:12px; overflow:hidden; box-shadow:var(--shadow-sm);">
                <table style="width:100%; border-collapse:collapse; font-size:0.85rem; text-align:left;">
                    <thead>
                        <tr style="background:#f8fafc; border-bottom:1px solid var(--border); color:var(--text-muted); font-size:0.78rem; text-transform:uppercase; letter-spacing:0.04em;">
                            <th style="padding:0.75rem 1rem; font-weight:700;">เดือน</th>
                            <th style="padding:0.75rem 0.5rem; text-align:center; font-weight:700; color:var(--green);">เข้าแถว</th>
                            <th style="padding:0.75rem 0.5rem; text-align:center; font-weight:700; color:var(--orange);">มาสาย</th>
                            <th style="padding:0.75rem 0.5rem; text-align:center; font-weight:700; color:var(--red);">ขาด</th>
                            <th style="padding:0.75rem 0.75rem; text-align:center; font-weight:700;">รวม</th>
                            <th style="padding:0.75rem 1rem; font-weight:700; min-width:130px;">ร้อยละ (%)</th>
                            <th style="padding:0.75rem 1rem; text-align:center; font-weight:700;">ปฏิทิน</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($monthlyBreakdown as $mb)
                        @php
                            $rateColor = $mb['rate'] >= 80 ? 'var(--green)' : ($mb['rate'] >= 60 ? 'var(--orange)' : 'var(--red)');
                            $barBg = $mb['rate'] >= 80 ? 'linear-gradient(90deg, #10b981, #34d399)' : ($mb['rate'] >= 60 ? 'linear-gradient(90deg, #f59e0b, #fbbf24)' : 'linear-gradient(90deg, #ef4444, #f87171)');
                        @endphp
                        <tr style="border-bottom:1px solid #f1f5f9; {{ $mb['is_current'] ? 'background:#f0fdf4;' : '' }}">
                            <td style="padding:0.85rem 1rem; font-weight:600; color:var(--navy);">
                                {{ $mb['month_name'] }}
                                @if($mb['is_current'])
                                    <span class="badge badge-green" style="font-size:0.65rem; margin-left:0.35rem;">เดือนนี้</span>
                                @endif
                            </td>
                            <td style="padding:0.85rem 0.5rem; text-align:center; font-weight:700; color:var(--green);">
                                {{ $mb['present'] }}
                            </td>
                            <td style="padding:0.85rem 0.5rem; text-align:center; font-weight:{{ $mb['late'] > 0 ? '700' : '400' }}; color:{{ $mb['late'] > 0 ? 'var(--orange)' : 'var(--text-muted)' }};">
                                {{ $mb['late'] }}
                            </td>
                            <td style="padding:0.85rem 0.5rem; text-align:center; font-weight:{{ $mb['absent'] > 0 ? '700' : '400' }}; color:{{ $mb['absent'] > 0 ? 'var(--red)' : 'var(--text-muted)' }};">
                                {{ $mb['absent'] }}
                            </td>
                            <td style="padding:0.85rem 0.75rem; text-align:center; font-weight:600; color:var(--text);">
                                {{ $mb['total'] }} วัน
                            </td>
                            <td style="padding:0.85rem 1rem;">
                                <div style="display:flex; align-items:center; gap:0.6rem;">
                                    <span style="font-weight:700; color:{{ $rateColor }}; min-width:40px; font-size:0.85rem;">
                                        {{ $mb['rate'] }}%
                                    </span>
                                    <div style="flex:1; height:6px; background:#e2e8f0; border-radius:3px; overflow:hidden;">
                                        <div style="height:100%; width:{{ $mb['rate'] }}%; background:{{ $barBg }}; border-radius:3px; transition:width 0.6s ease;"></div>
                                    </div>
                                </div>
                            </td>
                            <td style="padding:0.85rem 1rem; text-align:center;">
                                @if($mb['is_current'])
                                    <span style="font-size:0.75rem; color:var(--text-muted); font-weight:600;">กำลังดูอยู่</span>
                                @else
                                    <a href="{{ request()->url() }}?month={{ $mb['year_month'] }}" class="btn btn-sm btn-outline" style="font-size:0.75rem; padding:0.2rem 0.55rem; border-radius:6px; text-decoration:none;">
                                        ดูปฏิทิน <i class="fas fa-arrow-right" style="font-size:0.65rem;"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" style="text-align:center; padding:2rem; color:var(--text-muted);">
                                ไม่พบข้อมูลสถิติในภาคเรียนนี้
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

        <!-- Footer -->
        <div style="padding:1rem 1.5rem; background:#f8fafc; border-top:1px solid var(--border); display:flex; justify-content:flex-end;">
            <button type="button" class="btn btn-outline close-monthly-modal" style="font-size:0.85rem; padding:0.45rem 1.25rem; border-radius:8px;">
                ปิดหน้าต่าง
            </button>
        </div>
    </div>
</div>

<style>
@keyframes modalPop {
    from { opacity: 0; transform: scale(0.94) translateY(10px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ── Monthly Breakdown Modal ──────────────────────────────
    const modal = document.getElementById('monthlyAttendanceModal');
    const openBtn = document.getElementById('openMonthlyModalBtn');
    const closeBtns = document.querySelectorAll('.close-monthly-modal');
    const statCards = document.querySelectorAll('.stat-card-clickable[data-open-modal="true"]');

    function openModal() {
        if (modal) {
            modal.style.display = 'flex';
        }
    }

    function closeModal() {
        if (modal) {
            modal.style.display = 'none';
        }
    }

    if (openBtn) {
        openBtn.addEventListener('click', openModal);
    }

    closeBtns.forEach(btn => {
        btn.addEventListener('click', closeModal);
    });

    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal();
            }
        });
    }

    // Clicking any summary card directly opens the monthly breakdown modal!
    statCards.forEach(card => {
        card.addEventListener('click', function() {
            openModal();
        });
        card.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                openModal();
            }
        });
    });

    // ── Calendar Tab Filtering ──────────────────────────────
    const calTabs = document.querySelectorAll('.filter-tab-bar .filter-tab-item[data-cal-filter]');
    const cells = document.querySelectorAll('.cal-day-cell');
    const emptyCells = document.querySelectorAll('.cal-empty-cell');
    const banner = document.getElementById('attendanceFilterBanner');
    const bannerText = document.getElementById('attendanceFilterText');
    const bannerDot = document.getElementById('filterIndicatorDot');
    const clearBtn = document.getElementById('clearAttendanceFilterBtn');

    const filterConfig = {
        'มา': { label: 'เข้าแถว', color: '#10b981' },
        'สาย': { label: 'มาสาย', color: '#f59e0b' },
        'ขาด': { label: 'ขาดเข้าแถว', color: '#ef4444' }
    };

    function applyCalendarFilter(filterType) {
        // Update active tab
        calTabs.forEach(t => {
            if (t.dataset.calFilter === filterType) {
                t.classList.add('active');
            } else {
                t.classList.remove('active');
            }
        });

        if (filterType === 'all') {
            // Show all
            cells.forEach(c => c.classList.remove('dimmed', 'highlighted'));
            emptyCells.forEach(ec => ec.style.opacity = '1');
            if (banner) banner.style.display = 'none';
            return;
        }

        const cfg = filterConfig[filterType];
        let matchCount = 0;
        let matchedDays = [];

        cells.forEach(cell => {
            const st = cell.dataset.status;
            if (st === filterType) {
                cell.classList.remove('dimmed');
                cell.classList.add('highlighted');
                matchCount++;
                if (cell.dataset.day) matchedDays.push(cell.dataset.day);
            } else {
                cell.classList.add('dimmed');
                cell.classList.remove('highlighted');
            }
        });

        emptyCells.forEach(ec => ec.style.opacity = '0.2');

        if (banner && bannerText && bannerDot) {
            banner.style.display = 'flex';
            bannerDot.style.background = cfg.color;
            if (matchCount > 0) {
                const daysList = matchedDays.length <= 6 ? ' (วันที่ ' + matchedDays.join(', ') + ')' : ' (ทั้งหมด ' + matchCount + ' วัน)';
                bannerText.innerHTML = 'กำลังแสดงเฉพาะ: <strong style="color:' + cfg.color + ';">' + cfg.label + '</strong>' + daysList;
            } else {
                bannerText.innerHTML = 'กำลังแสดงเฉพาะ: <strong style="color:' + cfg.color + ';">' + cfg.label + '</strong> (ไม่พบรายการในเดือนนี้)';
            }
        }
    }

    calTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            applyCalendarFilter(this.dataset.calFilter);
        });
    });

    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            applyCalendarFilter('all');
        });
    }
});
</script>
@endsection
