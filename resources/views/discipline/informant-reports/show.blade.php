@extends('layouts.app')

@section('title', 'รายละเอียดเบาะแส')
@section('page-title', 'รายละเอียดเรื่องแจ้งเบาะแส')

@section('content')
<div style="max-width:680px;">
    <a href="{{ route('discipline.informant-reports.index') }}" class="btn btn-outline btn-sm" style="margin-bottom:1rem;">
        <i class="fas fa-arrow-left"></i> ย้อนกลับ
    </a>

    @php
        $sc = match($informantReport->Status) {
            'เรื่องใหม่'     => ['badge-gold',  '#c9a84c'],
            'กำลังตรวจสอบ'  => ['badge-navy',  '#1a2744'],
            'ปิดเรื่องแล้ว' => ['badge-green', '#27ae60'],
            default          => ['badge-gray',  '#ccc'],
        };
    @endphp

    <div class="card">
        {{-- Header --}}
        <div style="padding:1.25rem 1.5rem; border-bottom:1px solid #ede8e0; background:#faf8f4; display:flex; align-items:center; justify-content:space-between;">
            <div>
                <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom:0.35rem;">
                    รหัสเรื่อง: <code style="font-size:0.75rem; background:#f0ece4; padding:0.1rem 0.4rem; border-radius:2px;">
                        {{ substr($informantReport->ReportID, 0, 8) }}...
                    </code>
                </div>
                <div style="font-size:0.85rem; color:var(--text-muted);">
                    <i class="fas fa-clock" style="margin-right:0.35rem;"></i>
                    แจ้งเมื่อ {{ \Carbon\Carbon::parse($informantReport->ReportDate)->locale('th')->isoFormat('D MMMM YYYY เวลา HH:mm น.') }}
                </div>
            </div>
            <span class="badge {{ $sc[0] }}" style="font-size:0.85rem; padding:0.4rem 0.875rem;">
                {{ $informantReport->Status }}
            </span>
        </div>

        {{-- Content --}}
        <div style="padding:1.5rem;">
            {{-- Metadata Box --}}
            <div class="responsive-grid-2" style="margin-bottom:1.25rem; background:#fffdf7; border:1px solid #ede8e0; padding:1rem; border-radius:4px;">
                <div>
                    <div style="font-size:0.75rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:var(--text-muted); margin-bottom:0.35rem;">
                        นักเรียนที่เกี่ยวข้อง
                    </div>
                    <div style="font-size:0.875rem; font-weight:600; color:var(--navy);">
                        @if($informantReport->student)
                            {{ $informantReport->student->FullName }}
                            <div style="font-size:0.78rem; color:var(--text-muted); font-weight:400; margin-top:0.15rem;">
                                รหัส: {{ $informantReport->student->StudentID }} | ชั้นเรียน: {{ $informantReport->student->classroom_display }}
                            </div>
                        @else
                            <span style="color:var(--text-muted); font-style:italic; font-weight:400;">ไม่ระบุเจาะจง</span>
                        @endif
                    </div>
                </div>
                <div>
                    <div style="font-size:0.75rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:var(--text-muted); margin-bottom:0.35rem;">
                        ผู้แจ้งเบาะแส / การเปิดเผยตัวตน
                    </div>
                    <div style="font-size:0.875rem; font-weight:600; color:var(--navy);">
                        @if($informantReport->IsAnonymous)
                            <span class="badge badge-gray" style="font-size:0.72rem;"><i class="fas fa-user-secret" style="margin-right:0.25rem;"></i> ปกปิดตัวตน</span>
                        @else
                            <span class="badge badge-navy" style="font-size:0.72rem; margin-bottom:0.25rem;"><i class="fas fa-user" style="margin-right:0.25rem;"></i> เปิดเผยตัวตน</span>
                            <div style="font-size:0.78rem; color:var(--text-muted); font-weight:400;">
                                {{ $informantReport->reporter?->FullName ?? $informantReport->ReporterName ?? 'บุคคลภายนอก' }}
                                @if($informantReport->reporter)
                                    <span class="badge badge-gray" style="font-size:0.65rem; margin-left:0.25rem;">{{ $informantReport->reporter->Role }}</span>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div style="font-size:0.75rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:var(--text-muted); margin-bottom:0.75rem;">
                รายละเอียดเรื่องแจ้ง
            </div>
            <div style="font-size:0.95rem; line-height:1.8; color:var(--text); background:#faf8f4;
                        padding:1.25rem; border-radius:2px; border-left:3px solid {{ $sc[1] }}; white-space:pre-wrap;">{{ $informantReport->Description }}</div>

            @if($informantReport->EvidencePath)
            <div style="margin-top:1.25rem; padding-top:1.25rem; border-top:1px solid #ede8e0;">
                <div style="font-size:0.75rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:var(--text-muted); margin-bottom:0.5rem;">
                    ไฟล์หลักฐาน
                </div>
                <a href="{{ asset('storage/' . $informantReport->EvidencePath) }}"
                   target="_blank" class="btn btn-outline btn-sm">
                    <i class="fas fa-paperclip"></i> ดาวน์โหลดหลักฐาน
                </a>
            </div>
            @endif
        </div>

        {{-- Timeline / Status Flow --}}
        <div style="padding:1.25rem 1.5rem; border-top:1px solid #ede8e0; background:#faf8f4;">
            <div style="font-size:0.75rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:var(--text-muted); margin-bottom:1rem;">
                ขั้นตอนการดำเนินการ
            </div>
            <div style="display:flex; align-items:center; gap:0;">
                @foreach(['เรื่องใหม่','กำลังตรวจสอบ','ปิดเรื่องแล้ว'] as $i => $step)
                @php
                    $statusOrder = ['เรื่องใหม่' => 0, 'กำลังตรวจสอบ' => 1, 'ปิดเรื่องแล้ว' => 2];
                    $currentOrder = $statusOrder[$informantReport->Status] ?? 0;
                    $stepOrder = $statusOrder[$step];
                    $isDone = $stepOrder <= $currentOrder;
                    $isCurrent = $stepOrder === $currentOrder;
                @endphp
                <div style="display:flex; align-items:center; flex:1;">
                    <div style="display:flex; flex-direction:column; align-items:center; flex:1;">
                        <div style="width:32px; height:32px; border-radius:50%; border:2px solid {{ $isDone ? 'var(--navy)' : '#d8d0c0' }};
                            background:{{ $isCurrent ? 'var(--navy)' : ($isDone ? 'var(--navy)' : 'white') }};
                            display:flex; align-items:center; justify-content:center; z-index:1; position:relative;">
                            @if($isDone && !$isCurrent)
                                <i class="fas fa-check" style="color:var(--gold); font-size:0.8rem;"></i>
                            @elseif($isCurrent)
                                <i class="fas fa-circle" style="color:var(--gold); font-size:0.5rem;"></i>
                            @endif
                        </div>
                        <div style="font-size:0.72rem; margin-top:0.4rem; color:{{ $isDone ? 'var(--navy)' : 'var(--text-muted)' }};
                            font-weight:{{ $isCurrent ? '600' : '400' }}; text-align:center; white-space:nowrap;">
                            {{ $step }}
                        </div>
                    </div>
                    @if(!$loop->last)
                    <div style="flex:1; height:2px; background:{{ $stepOrder < $currentOrder ? 'var(--navy)' : '#e8e3db' }}; margin-top:-18px;"></div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        {{-- Actions --}}
        <div style="padding:1.25rem 1.5rem; border-top:1px solid #ede8e0; display:flex; gap:0.75rem; flex-wrap:wrap;">
            @if($informantReport->Status === 'เรื่องใหม่')
            <form method="POST" action="{{ route('discipline.informant-reports.accept', $informantReport->ReportID) }}">
                @csrf @method('PATCH')
                <button type="submit" class="btn btn-gold">
                    <i class="fas fa-check"></i> รับเรื่อง — เริ่มตรวจสอบ
                </button>
            </form>
            @endif

            @if($informantReport->Status === 'กำลังตรวจสอบ')
            <form method="POST" action="{{ route('discipline.informant-reports.close', $informantReport->ReportID) }}"
                  onsubmit="return confirm('ยืนยันการปิดเรื่องนี้?')">
                @csrf @method('PATCH')
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-lock"></i> ปิดเรื่อง — ดำเนินการเสร็จสิ้น
                </button>
            </form>
            @endif

            @if($informantReport->Status === 'ปิดเรื่องแล้ว')
            <form method="POST" action="{{ route('discipline.informant-reports.destroy', $informantReport->ReportID) }}"
                  onsubmit="return confirm('ยืนยันการลบเรื่องนี้อย่างถาวร?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> ลบออกจากระบบ
                </button>
            </form>
            @endif

            <a href="{{ route('discipline.informant-reports.index') }}" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> ย้อนกลับ
            </a>
        </div>
    </div>
</div>
@endsection