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
<div style="margin-bottom:1.25rem; display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:1rem;">
    @php
        $statusConfig = [
            'เรื่องใหม่'     => ['icon' => 'fa-bell',         'color' => 'gold',   'label' => 'เรื่องใหม่'],
            'กำลังตรวจสอบ'  => ['icon' => 'fa-search',       'color' => 'navy',   'label' => 'กำลังตรวจสอบ'],
            'ปิดเรื่องแล้ว' => ['icon' => 'fa-check-circle', 'color' => 'green',  'label' => 'ปิดเรื่องแล้ว'],
        ];
    @endphp
    @foreach($statusConfig as $status => $cfg)
    <a href="{{ route('discipline.informant-reports.index', ['status' => $status]) }}"
       style="text-decoration:none;">
        <div class="stat-card {{ $cfg['color'] }}" style="{{ request('status') === $status ? 'box-shadow:0 0 0 2px var(--gold);' : '' }}">
            <div class="stat-icon {{ $cfg['color'] }}"><i class="fas {{ $cfg['icon'] }}"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $counts[$status] ?? 0 }}</div>
                <div class="stat-label">{{ $cfg['label'] }}</div>
            </div>
        </div>
    </a>
    @endforeach
</div>

{{-- Filter --}}
<div class="card" style="margin-bottom:1rem;">
    <div class="card-body-pad" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem;">
        <form method="GET" style="display:flex; gap:0.75rem; align-items:flex-end; flex-wrap:wrap; flex:1;">
            <div class="form-group" style="margin:0; flex:1; min-width:220px;">
                <label class="form-label">ค้นหาเนื้อหา / รหัส</label>
                <input type="text" name="search" class="form-control"
                       value="{{ request('search') }}" placeholder="ค้นหาเนื้อหา, รหัสเรื่อง, รหัสนักเรียน...">
            </div>
            <div class="form-group" style="margin:0; min-width:160px;">
                <label class="form-label">สถานะ</label>
                <select name="status" class="form-control" onchange="this.form.submit()">
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
            'เรื่องใหม่'   => ['badge-gold',  'fa-bell',         'var(--gold)'],
            'กำลังตรวจสอบ' => ['badge-navy',  'fa-search',       'var(--navy)'],
            'ปิดเรื่องแล้ว'=> ['badge-green', 'fa-check-circle', 'var(--green)'],
            default        => ['badge-gray',  'fa-circle',       '#ccc'],
        };
    @endphp
    <div class="card" style="border-left:3px solid {{ $sc[2] }};">
        <div style="padding:1.1rem 1.25rem;">
            <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:1rem;">
                <div style="flex:1; min-width:0;">
                    <div style="display:flex; align-items:center; gap:0.6rem; margin-bottom:0.5rem; flex-wrap:wrap;">
                        <code style="font-weight:700; color:var(--navy); background:#e2e8f0; padding:0.15rem 0.5rem; border-radius:4px; font-size:0.8rem;">{{ $report->ReportID }}</code>
                        <span class="badge {{ $sc[0] }}">
                            <i class="fas {{ $sc[1] }}" style="margin-right:0.25rem;"></i>
                            {{ $report->Status }}
                        </span>
                        <span style="font-size:0.78rem; color:var(--text-muted);">
                            <i class="fas fa-clock" style="margin-right:0.25rem;"></i>
                            @php
                                $rDate = \Carbon\Carbon::parse($report->ReportDate);
                            @endphp
                            {{ $rDate->locale('th')->diffForHumans() }}
                            — {{ $rDate->format('d/m/') . ($rDate->year + 543) . $rDate->format(' H:i') }}
                        </span>
                        @php
                            $involvedList = $report->involved_students;
                        @endphp
                        @if($involvedList->count() > 0)
                            <span class="badge badge-gold" style="font-size:0.68rem;">
                                <i class="fas fa-user-graduate"></i> เกี่ยวข้อง ({{ $involvedList->count() }} คน): {{ $involvedList->pluck('FullName')->join(', ') }}
                            </span>
                        @elseif(!empty($report->StudentID))
                            <span class="badge badge-gold" style="font-size:0.68rem;">
                                <i class="fas fa-id-card"></i> เกี่ยวข้อง: {{ $report->StudentID }}
                            </span>
                        @endif
                        @if($report->IsAnonymous)
                            <span class="badge badge-gray" style="font-size:0.68rem;">
                                <i class="fas fa-user-secret"></i> ปกปิดตัวตน
                            </span>
                        @else
                            <span class="badge badge-navy" style="font-size:0.68rem;">
                                <i class="fas fa-user"></i> เปิดเผยตัวตน: {{ $report->ReporterName ?? $report->reporter?->FullName ?? 'ผู้แจ้ง' }}
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
                    <form method="POST" action="{{ route('discipline.informant-reports.accept', $report->ReportID) }}" style="margin:0;">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-gold btn-sm" style="width:100%;">
                            <i class="fas fa-check"></i> รับเรื่อง
                        </button>
                    </form>
                    @endif

                    @if($report->Status === 'กำลังตรวจสอบ')
                    <form method="POST" action="{{ route('discipline.informant-reports.close', $report->ReportID) }}" style="margin:0;">
                        @csrf @method('PATCH')
                        <button type="button" class="btn btn-success btn-sm btn-close-report" style="width:100%;">
                            <i class="fas fa-lock"></i> ปิดเรื่อง
                        </button>
                    </form>
                    @endif

                    <form method="POST" action="{{ route('discipline.informant-reports.destroy', $report->ReportID) }}" style="margin:0;">
                        @csrf @method('DELETE')
                        <button type="button" class="btn btn-danger btn-sm btn-delete-report" style="width:100%;">
                            <i class="fas fa-trash"></i> ลบ
                        </button>
                    </form>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Close report Swal confirmation
    document.querySelectorAll('.btn-close-report').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'ยืนยันการปิดเรื่องนี้?',
                    text: 'เมื่อปิดเรื่องแล้ว สถานะเรื่องแจ้งเบาะแสจะเปลี่ยนเป็นปิดเรื่องเรียบร้อย',
                    iconHtml: '<i class="fas fa-lock" style="color:#16a34a; font-size:2.6rem;"></i>',
                    showCancelButton: true,
                    confirmButtonText: 'ตกลง',
                    cancelButtonText: 'ยกเลิก',
                    customClass: {
                        icon: 'swal2-icon-custom-green',
                        confirmButton: 'swal2-confirm btn-swal-success',
                        cancelButton: 'swal2-cancel btn-swal-cancel'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            } else {
                if (confirm('ยืนยันการปิดเรื่องนี้?')) {
                    form.submit();
                }
            }
        });
    });

    // Delete report 2-step Swal confirmation
    document.querySelectorAll('.btn-delete-report').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'ยืนยันการลบเรื่องแจ้งเบาะแส?',
                    text: 'คุณกำลังจะลบรายการแจ้งเบาะแสนี้ออกจากระบบ',
                    iconHtml: '<i class="fas fa-trash-alt" style="color:#ef4444; font-size:2.6rem;"></i>',
                    showCancelButton: true,
                    confirmButtonText: 'ตกลง',
                    cancelButtonText: 'ยกเลิก',
                    customClass: {
                        icon: 'swal2-icon-custom-red',
                        confirmButton: 'swal2-confirm btn-swal-danger',
                        cancelButton: 'swal2-cancel btn-swal-cancel'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'ยืนยันลบข้อมูลจริงในฐานข้อมูล?',
                            text: 'การลบนี้จะทำการลบข้อมูลออกจากฐานข้อมูลโดยตรงและไม่สามารถกู้คืนได้!',
                            iconHtml: '<i class="fas fa-exclamation-triangle" style="color:#dc2626; font-size:2.6rem;"></i>',
                            showCancelButton: true,
                            confirmButtonText: 'ตกลง',
                            cancelButtonText: 'ยกเลิก',
                            customClass: {
                                icon: 'swal2-icon-custom-red',
                                confirmButton: 'swal2-confirm btn-swal-danger',
                                cancelButton: 'swal2-cancel btn-swal-cancel'
                            },
                            buttonsStyling: false
                        }).then((res2) => {
                            if (res2.isConfirmed) {
                                form.submit();
                            }
                        });
                    }
                });
            } else {
                if (confirm('ยืนยันการลบเรื่องนี้?')) {
                    form.submit();
                }
            }
        });
    });
});
</script>
@endpush
@endsection