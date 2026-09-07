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
@endphp

<div class="page-header">
    <h2>ปฏิทินการเข้าแถว</h2>
    <p>{{ $student->FullName }} — ห้อง {{ $student->Classroom }}</p>
</div>

{{-- Summary --}}
<div class="stat-grid" style="margin-bottom:1rem;">
    <div class="stat-card green stat-card-clickable" data-filter="มา" role="button" tabindex="0" title="คลิกเพื่อกรองดูเฉพาะวันที่เข้าแถว">
        <div class="stat-icon green"><i class="fas fa-check"></i></div>
        <div class="stat-info">
            <div class="stat-value">{{ $summary['มา'] ?? 0 }}</div>
            <div class="stat-label">เข้าแถว</div>
            <div class="stat-filter-badge" style="font-size:0.7rem; color:var(--text-muted); margin-top:0.35rem; display:flex; align-items:center; gap:0.25rem;">
                <i class="fas fa-filter" style="font-size:0.65rem;"></i> <span class="filter-text">คลิกเพื่อดูเฉพาะรายการนี้</span>
            </div>
        </div>
    </div>
    <div class="stat-card gold stat-card-clickable" data-filter="สาย" role="button" tabindex="0" title="คลิกเพื่อกรองดูเฉพาะวันที่มาสาย">
        <div class="stat-icon gold"><i class="fas fa-clock"></i></div>
        <div class="stat-info">
            <div class="stat-value">{{ $summary['สาย'] ?? 0 }}</div>
            <div class="stat-label">มาสาย</div>
            <div class="stat-filter-badge" style="font-size:0.7rem; color:var(--text-muted); margin-top:0.35rem; display:flex; align-items:center; gap:0.25rem;">
                <i class="fas fa-filter" style="font-size:0.65rem;"></i> <span class="filter-text">คลิกเพื่อดูเฉพาะรายการนี้</span>
            </div>
        </div>
    </div>
    <div class="stat-card red stat-card-clickable" data-filter="ขาด" role="button" tabindex="0" title="คลิกเพื่อกรองดูเฉพาะวันที่ขาดเข้าแถว">
        <div class="stat-icon red"><i class="fas fa-times"></i></div>
        <div class="stat-info">
            <div class="stat-value">{{ $summary['ขาด'] ?? 0 }}</div>
            <div class="stat-label">ขาดเข้าแถว</div>
            <div class="stat-filter-badge" style="font-size:0.7rem; color:var(--text-muted); margin-top:0.35rem; display:flex; align-items:center; gap:0.25rem;">
                <i class="fas fa-filter" style="font-size:0.65rem;"></i> <span class="filter-text">คลิกเพื่อดูเฉพาะรายการนี้</span>
            </div>
        </div>
    </div>
</div>

<div class="card">
    {{-- Month Nav --}}
    <div class="card-header-bar">
        <a href="{{ request()->url() }}?month={{ $prevMonth }}" class="btn btn-outline btn-sm">
            <i class="fas fa-chevron-left"></i>
        </a>
        <h3 style="margin:0 1rem;">{{ $thMonth }}</h3>
        <a href="{{ request()->url() }}?month={{ $nextMonth }}" class="btn btn-outline btn-sm">
            <i class="fas fa-chevron-right"></i>
        </a>
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
                <span style="font-size:0.6rem; color:{{ $textColor }}; margin-top:1px;">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.stat-card-clickable');
    const cells = document.querySelectorAll('.cal-day-cell');
    const emptyCells = document.querySelectorAll('.cal-empty-cell');
    const banner = document.getElementById('attendanceFilterBanner');
    const bannerText = document.getElementById('attendanceFilterText');
    const bannerDot = document.getElementById('filterIndicatorDot');
    const clearBtn = document.getElementById('clearAttendanceFilterBtn');

    let currentFilter = null;

    const filterConfig = {
        'มา': {
            label: 'เข้าแถว',
            color: '#10b981',
            bg: '#e8f8ef'
        },
        'สาย': {
            label: 'มาสาย',
            color: '#f59e0b',
            bg: '#fff3e0'
        },
        'ขาด': {
            label: 'ขาดเข้าแถว',
            color: '#ef4444',
            bg: '#fdecea'
        }
    };

    function applyFilter(filterType) {
        if (currentFilter === filterType) {
            clearFilter();
            return;
        }

        currentFilter = filterType;
        const cfg = filterConfig[filterType];

        // Update cards
        cards.forEach(c => {
            const f = c.dataset.filter;
            const textSpan = c.querySelector('.filter-text');
            if (f === filterType) {
                c.classList.add('active');
                c.classList.remove('dimmed');
                if (textSpan) textSpan.innerHTML = '<strong style="color:' + cfg.color + ';">✓ กำลังแสดงรายการนี้</strong>';
            } else {
                c.classList.remove('active');
                c.classList.add('dimmed');
                if (textSpan) textSpan.innerHTML = 'คลิกเพื่อดูเฉพาะรายการนี้';
            }
        });

        // Filter calendar cells
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

        emptyCells.forEach(ec => {
            ec.style.opacity = '0.2';
        });

        // Update banner
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

    function clearFilter() {
        currentFilter = null;
        cards.forEach(c => {
            c.classList.remove('active', 'dimmed');
            const textSpan = c.querySelector('.filter-text');
            if (textSpan) textSpan.innerHTML = 'คลิกเพื่อดูเฉพาะรายการนี้';
        });

        cells.forEach(cell => {
            cell.classList.remove('dimmed', 'highlighted');
        });

        emptyCells.forEach(ec => {
            ec.style.opacity = '1';
        });

        if (banner) {
            banner.style.display = 'none';
        }
    }

    cards.forEach(c => {
        c.addEventListener('click', function() {
            applyFilter(this.dataset.filter);
        });
        c.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                applyFilter(this.dataset.filter);
            }
        });
    });

    if (clearBtn) {
        clearBtn.addEventListener('click', clearFilter);
    }
});
</script>
@endsection
