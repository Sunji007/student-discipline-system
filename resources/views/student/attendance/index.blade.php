@extends('layouts.app')

@section('title', 'การเข้าแถว')
@section('page-title', 'ประวัติการเข้าแถว')

@section('content')
@php
    $prevMonth = \Carbon\Carbon::create($year, $mon, 1)->subMonth()->format('Y-m');
    $nextMonth = \Carbon\Carbon::create($year, $mon, 1)->addMonth()->format('Y-m');
    $thMonth   = \Carbon\Carbon::create($year, $mon, 1)->locale('th')->isoFormat('MMMM YYYY');
    $firstDow  = (int)\Carbon\Carbon::create($year, $mon, 1)->dayOfWeek; // 0=Sun
@endphp

<div class="page-header">
    <h2>ปฏิทินการเข้าแถว</h2>
    <p>{{ $student->FullName }} — ห้อง {{ $student->Classroom }}</p>
</div>

{{-- Summary --}}
<div class="stat-grid" style="margin-bottom:1rem;">
    <div class="stat-card green">
        <div class="stat-icon green"><i class="fas fa-check"></i></div>
        <div class="stat-info">
            <div class="stat-value">{{ $summary['มา'] }}</div>
            <div class="stat-label">มาเรียนปกติ</div>
        </div>
    </div>
    <div class="stat-card gold">
        <div class="stat-icon gold"><i class="fas fa-clock"></i></div>
        <div class="stat-info">
            <div class="stat-value">{{ $summary['สาย'] }}</div>
            <div class="stat-label">มาสาย</div>
        </div>
    </div>
    <div class="stat-card red">
        <div class="stat-icon red"><i class="fas fa-times"></i></div>
        <div class="stat-info">
            <div class="stat-value">{{ $summary['ขาด'] }}</div>
            <div class="stat-label">ขาดเรียน</div>
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
        <div style="display:grid; grid-template-columns:repeat(7, 1fr); gap:4px;">
            {{-- Empty cells before first day --}}
            @for($i = 0; $i < $firstDow; $i++)
            <div></div>
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
            <div style="aspect-ratio:1; display:flex; flex-direction:column; align-items:center; justify-content:center;
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
@endsection