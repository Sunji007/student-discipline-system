@extends('layouts.app')

@section('title', 'รับแจ้งเบาะแส')
@section('page-title', 'รับแจ้งเบาะแสการทำผิด')

@section('content')
<div class="page-header">
    <div>
        <h2>เรื่องแจ้งเบาะแส</h2>
        <p>จัดการและติดตามเรื่องร้องเรียนที่ได้รับ</p>
    </div>
</div>

{{-- Status Summary Cards --}}
<div class="responsive-grid-3" style="margin-bottom:1.25rem;">
    @php
        $statusConfig = [
            'เรื่องใหม่'     => ['icon' => 'fa-bell',            'color' => 'gold',  'label' => 'เรื่องใหม่'],
            'กำลังตรวจสอบ'  => ['icon' => 'fa-search',          'color' => 'navy',  'label' => 'กำลังตรวจสอบ'],
            'ปิดเรื่องแล้ว' => ['icon' => 'fa-check-circle',    'color' => 'green', 'label' => 'ปิดเรื่องแล้ว'],
        ];
    @endphp
    @foreach($statusConfig as $status => $cfg)
    <a href="{{ route('discipline.informant-reports.index', ['status' => $status]) }}"
       style="text-decoration:none;">
        <div class="stat-card {{ $cfg['color'] }}" style="{{ request('status') === $status ? 'box-shadow:0 0 0 2px var(--gold);' : '' }}">
            <div class="stat-icon {{ $cfg['color'] }}"><i class="fas {{ $cfg['icon'] }}"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $counts[$status] }}</div>
                <div class="stat-label">{{ $cfg['label'] }}</div>
            </div>
        </div>
    </a>
    @endforeach
</div>

{{-- Filter --}}
<div class="card" style="margin-bottom:1rem;">
    <div class="card-body-pad">
        <form method="GET" style="display:flex; gap:0.75rem; align-items:flex-end; flex-wrap:wrap;">
            <div class="form-group" style="margin:0; flex:1; min-width:220px;">
                <label class="form-label">ค้นหาเนื้อหา</label>
                <input type="text" name="search" class="form-control"
                       value="{{ request('search') }}" placeholder="คำค้นหา...">
            </div>
            <div class="form-group" style="margin:0; min-width:160px;">
                <label class="form-label">สถานะ</label>
                <select name="status" class="form-control">
                    <option value="">ทั้งหมด</option>
                    @foreach(['เรื่องใหม่','กำลังตรวจสอบ','ปิดเรื่องแล้ว'] as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> ค้นหา
            </button>
            @if(request()->hasAny(['status','search']))
                <a href="{{ route('discipline.informant-reports.index') }}" class="btn btn-outline">ล้างตัวกรอง</a>
            @endif
        </form>
    </div>
</div>

{{-- Reports List --}}
<div style="display:flex; flex-direction:column; gap:0.75rem;">
    @forelse($reports as $report)
    @php
        $sc = match($report->Status) {
            'เรื่องใหม่'     => ['badge-gold',  'fa-bell',         'var(--gold)'],
            'กำลังตรวจสอบ'  => ['badge-navy',  'fa-search',       'var(--navy)'],
            'ปิดเรื่องแล้ว' => ['badge-green', 'fa-check-circle', 'var(--green)'],
            default          => ['badge-gray',  'fa-circle',       '#ccc'],
        };
    @endphp
    <div class="card" style="border-left:3px solid {{ $sc[2] }};">
        <div style="padding:1.1rem 1.25rem;">
            <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:1rem;">
                <div style="flex:1; min-width:0;">
                    <div style="display:flex; align-items:center; gap:0.6rem; margin-bottom:0.5rem; flex-wrap:wrap;">
                        <span class="badge {{ $sc[0] }}">
                            <i class="fas {{ $sc[1] }}" style="margin-right:0.25rem;"></i>
                            {{ $report->Status }}
                        </span>
                        <span style="font-size:0.78rem; color:var(--text-muted);">
                            <i class="fas fa-clock" style="margin-right:0.25rem;"></i>
                            {{ \Carbon\Carbon::parse($report->ReportDate)->locale('th')->diffForHumans() }}
                            — {{ \Carbon\Carbon::parse($report->ReportDate)->format('d/m/Y H:i') }}
                        </span>
                        @if($report->student)
                            <span class="badge badge-gold" style="font-size:0.68rem;">
                                <i class="fas fa-user-graduate"></i> เกี่ยวข้อง: {{ $report->student->FullName }} ({{ $report->student->classroom_display }})
                            </span>
                        @endif
                        @if($report->IsAnonymous)
                            <span class="badge badge-gray" style="font-size:0.68rem;">
                                <i class="fas fa-user-secret"></i> ปกปิดตัวตน
                            </span>
                        @else
                            <span class="badge badge-navy" style="font-size:0.68rem;">
                                <i class="fas fa-user"></i> ผู้แจ้ง: {{ $report->reporter?->FullName ?? $report->ReporterName ?? 'เปิดเผยตัวตน' }}
                            </span>
                        @endif
                        @if($report->EvidencePath)
                        <span class="badge badge-navy" style="font-size:0.65rem;">
                            <i class="fas fa-paperclip"></i> มีหลักฐาน
                        </span>
                        @endif
                    </div>
                    <p style="font-size:0.9rem; color:var(--text); line-height:1.6; margin:0;">
                        {{ \Str::limit($report->Description, 180) }}
                    </p>
                </div>

                {{-- Actions --}}
                <div style="display:flex; flex-direction:column; gap:0.4rem; flex-shrink:0;">
                    <a href="{{ route('discipline.informant-reports.show', $report->ReportID) }}"
                       class="btn btn-outline btn-sm">
                        <i class="fas fa-eye"></i> ดูรายละเอียด
                    </a>

                    @if($report->Status === 'เรื่องใหม่')
                    <form method="POST" action="{{ route('discipline.informant-reports.accept', $report->ReportID) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-gold btn-sm" style="width:100%;">
                            <i class="fas fa-check"></i> รับเรื่อง
                        </button>
                    </form>
                    @endif

                    @if($report->Status === 'กำลังตรวจสอบ')
                    <form method="POST" action="{{ route('discipline.informant-reports.close', $report->ReportID) }}"
                          onsubmit="return confirm('ยืนยันการปิดเรื่องนี้?')">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-success btn-sm" style="width:100%;">
                            <i class="fas fa-lock"></i> ปิดเรื่อง
                        </button>
                    </form>
                    @endif

                    @if($report->Status === 'ปิดเรื่องแล้ว')
                    <form method="POST" action="{{ route('discipline.informant-reports.destroy', $report->ReportID) }}"
                          onsubmit="return confirm('ยืนยันการลบเรื่องนี้?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" style="width:100%;">
                            <i class="fas fa-trash"></i> ลบ
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="card">
        <div style="text-align:center; padding:3rem; color:var(--text-muted);">
            <i class="fas fa-bell-slash" style="font-size:2.5rem; opacity:0.2; display:block; margin-bottom:1rem;"></i>
            ไม่มีเรื่องแจ้งเบาะแส
            @if(request()->hasAny(['status','search']))
                ที่ตรงกับการค้นหา
            @endif
        </div>
    </div>
    @endforelse
</div>

<div style="margin-top:1rem;">
    {{ $reports->withQueryString()->links() }}
</div>
@endsection