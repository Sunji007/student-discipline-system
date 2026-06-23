@extends('layouts.app')

@section('title', 'ประวัติการแจ้งเบาะแส')
@section('page-title', 'ประวัติการแจ้งเบาะแสของฉัน')

@section('content')
<div class="page-header" style="display:flex; justify-content:space-between; align-items:center;">
    <div>
        <h2>รายการเบาะแสที่ฉันแจ้ง</h2>
        <p>ติดตามสถานะการตรวจสอบข้อมูลที่ท่านรายงาน</p>
    </div>
    <a href="{{ route($layoutPrefix . '.informant-reports.create') }}" class="btn btn-gold">
        <i class="fas fa-plus"></i> แจ้งเบาะแสใหม่
    </a>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>หัวข้อเบาะแส</th>
                    <th>ประเภท</th>
                    <th>นักเรียนที่เกี่ยวข้อง</th>
                    <th>การเปิดเผยตัวตน</th>
                    <th>วันที่แจ้ง</th>
                    <th>สถานะการดำเนินงาน</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $report)
                <tr>
                    <td>
                        <div style="font-weight:600; color:var(--navy);">{{ $report->Title }}</div>
                        <div style="font-size:0.78rem; color:var(--text-muted); margin-top:0.2rem; max-width:300px;">
                            {{ \Str::limit($report->Description, 100) }}
                        </div>
                        @if($report->Remarks)
                            <div style="font-size:0.75rem; background:#f7f3eb; border-left:2px solid var(--gold); padding:0.25rem 0.5rem; margin-top:0.4rem; color:var(--text-muted);">
                                <strong>บันทึกจากฝ่ายปกครอง:</strong> {{ $report->Remarks }}
                            </div>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-navy" style="font-size:0.7rem;">{{ $report->Category }}</span>
                    </td>
                    <td style="font-size:0.85rem;">
                        @if($report->student)
                            {{ $report->student->FullName }}
                            <div style="font-size:0.75rem; color:var(--text-muted);">
                                รหัส: {{ $report->student->StudentID }} ({{ $report->student->classroom_display }})
                            </div>
                        @else
                            <span style="color:var(--text-muted); font-style:italic;">ไม่ระบุเจาะจง</span>
                        @endif
                    </td>
                    <td>
                        @if($report->IsAnonymous)
                            <span class="badge badge-gray"><i class="fas fa-user-secret"></i> ปกปิดตัวตน</span>
                        @else
                            <span class="badge badge-navy"><i class="fas fa-user"></i> เปิดเผยตัวตน</span>
                        @endif
                    </td>
                    <td style="font-size:0.82rem; color:var(--text-muted);">
                        {{ $report->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td>
                        @php
                            $badgeClass = match($report->Status) {
                                'เรื่องใหม่' => 'badge-gold',
                                'กำลังตรวจสอบ' => 'badge-orange',
                                'ปิดเรื่องแล้ว' => 'badge-green',
                                default => 'badge-gray',
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ $report->Status }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center; padding:3.5rem; color:var(--text-muted);">
                        <i class="fas fa-bullhorn" style="font-size:2.5rem; opacity:0.25; display:block; margin-bottom:0.75rem;"></i>
                        ยังไม่พบประวัติการแจ้งข้อมูลเบาะแสพฤติกรรม
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($reports->hasPages())
        <div style="padding:1rem 1.25rem; border-top:1px solid #ede8e0;">
            {{ $reports->links() }}
        </div>
    @endif
</div>
@endsection
