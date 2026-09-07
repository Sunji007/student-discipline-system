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
                    รหัสเรื่อง: <code style="font-size:0.85rem; font-weight:700; background:#f0ece4; color:var(--navy); padding:0.15rem 0.5rem; border-radius:4px;">
                        {{ $informantReport->ReportID }}
                    </code>
                </div>
                <div style="font-size:0.85rem; color:var(--text-muted);">
                    <i class="fas fa-clock" style="margin-right:0.35rem;"></i>
                    @php
                        $dt = \Carbon\Carbon::parse($informantReport->ReportDate)->locale('th');
                    @endphp
                    แจ้งเมื่อ {{ $dt->isoFormat('D MMMM ') . ($dt->year + 543) . $dt->isoFormat(' เวลา HH:mm น.') }}
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
                        ผู้เกี่ยวข้อง / รูปพรรณสัณฐาน
                    </div>
                    <div style="font-size:0.875rem; font-weight:600; color:var(--navy);">
                        @php
                            $involvedStudents = $informantReport->involved_students;
                        @endphp
                        @if($involvedStudents->count() > 0)
                            @foreach($involvedStudents as $st)
                                <div style="margin-bottom:0.35rem;">
                                    <i class="fas fa-user-graduate" style="color:var(--gold); margin-right:0.25rem;"></i> {{ $st->FullName }}
                                    <div style="font-size:0.78rem; color:var(--text-muted); font-weight:400; margin-top:0.1rem; padding-left:1.1rem;">
                                        รหัส: {{ $st->StudentID }} | ชั้นเรียน: {{ $st->classroom_display }}
                                    </div>
                                </div>
                            @endforeach
                        @elseif(!empty($informantReport->StudentID))
                            <div style="font-size:0.875rem; color:var(--navy);">
                                <i class="fas fa-user-tag" style="color:var(--gold); margin-right:0.25rem;"></i> รูปพรรณสัณฐาน / ข้อมูลที่ระบุ: <strong>{{ $informantReport->StudentID }}</strong>
                            </div>
                        @else
                            <span style="color:var(--text-muted); font-style:italic; font-weight:400;">ไม่ระบุเจาะจง</span>
                        @endif
                    </div>
                </div>
                <div>
                    <div style="font-size:0.75rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:var(--text-muted); margin-bottom:0.35rem;">
                        ผู้แจ้งเบาะแส
                    </div>
                    <div style="font-size:0.875rem; font-weight:600; color:var(--navy);">
                        @if($informantReport->IsAnonymous)
                            <span class="badge badge-gray" style="font-size:0.8rem; padding:0.3rem 0.6rem; margin-bottom:0.25rem; display:inline-block;">
                                <i class="fas fa-user-secret" style="margin-right:0.3rem; color:var(--text-muted);"></i> ปกปิดตัวตน
                            </span>
                            @php
                                $realName = $informantReport->ReporterName ?? $informantReport->reporter?->FullName;
                                $realId   = $informantReport->ReporterID ?? $informantReport->reporter?->UserID;
                                if (!$realId && $realName) {
                                    $nameParts = explode(' ', trim($realName), 2);
                                    $fn = $nameParts[0] ?? $realName;
                                    $foundUser = \App\Models\User::where('FirstName', 'LIKE', "%{$fn}%")->first();
                                    if (!$foundUser) {
                                        $foundUser = \App\Models\User::where('Role', 'นักเรียน')->first();
                                    }
                                    if ($foundUser) {
                                        $realId = $foundUser->UserID;
                                    }
                                }
                            @endphp
                            @if($realName || $realId)
                                <details style="margin-top:0.35rem; font-size:0.8rem; color:var(--text-muted);">
                                    <summary style="cursor:pointer; color:var(--navy); font-weight:600; font-size:0.78rem;">
                                        <i class="fas fa-search-plus" style="color:var(--gold);"></i> ตรวจสอบตัวตนจริง (เฉพาะฝ่ายปกครอง)
                                    </summary>
                                    <div style="margin-top:0.35rem; padding:0.5rem 0.65rem; background:#f8fafc; border-radius:6px; border:1px solid #e2e8f0; border-left:3px solid var(--navy);">
                                        <div style="font-size:0.8rem;"><strong style="color:var(--navy);">ชื่อผู้แจ้ง:</strong> {{ $realName ?? 'ไม่ระบุ' }}</div>
                                        <div style="font-size:0.8rem;"><strong style="color:var(--navy);">รหัสบัญชี:</strong> <code style="background:#e2e8f0; padding:0.1rem 0.3rem; border-radius:3px;">{{ $realId ?? '-' }}</code></div>
                                        <div style="font-size:0.7rem; color:var(--red); margin-top:0.25rem; line-height:1.3;">
                                            * แสดงเฉพาะฝ่ายปกครองเพื่อใช้ในการตรวจสอบกรณีแจ้งเท็จหรือกลั่นแกล้งเท่านั้น
                                        </div>
                                    </div>
                                </details>
                            @endif
                        @else
                            <span class="badge badge-navy" style="font-size:0.8rem; padding:0.3rem 0.6rem; margin-bottom:0.25rem; display:inline-block;">
                                <i class="fas fa-user" style="margin-right:0.3rem;"></i> เปิดเผยตัวตน
                            </span>
                            @php
                                $reporterName = $informantReport->ReporterName ?? $informantReport->reporter?->FullName ?? 'ผู้แจ้งเบาะแส';
                                $reporterStudent = $informantReport->reporter?->student;
                            @endphp
                            <div style="font-size:0.88rem; color:var(--navy); margin-top:0.2rem;">
                                <strong>{{ $reporterName }}</strong>
                                @if($reporterStudent)
                                    <div style="font-size:0.78rem; color:var(--text-muted); margin-top:0.1rem;">
                                        รหัส: {{ $reporterStudent->StudentID }} | ชั้นเรียน: {{ $reporterStudent->classroom_display }}
                                    </div>
                                @elseif($informantReport->ReporterID)
                                    <div style="font-size:0.78rem; color:var(--text-muted); margin-top:0.1rem;">
                                        รหัสผู้ใช้: {{ $informantReport->ReporterID }}
                                    </div>
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

            @php
                $evidenceList = $informantReport->evidence_paths;
            @endphp
            @if(!empty($evidenceList))
            <div style="margin-top:1.25rem; padding-top:1.25rem; border-top:1px solid #ede8e0;">
                <div style="font-size:0.75rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:var(--text-muted); margin-bottom:0.5rem;">
                    ไฟล์หลักฐาน ({{ count($evidenceList) }} ไฟล์)
                </div>
                <div style="display:flex; flex-wrap:wrap; gap:0.5rem;">
                    @foreach($evidenceList as $idx => $path)
                    @php
                        $fileUrl = (str_starts_with($path, 'uploads/') || str_starts_with($path, 'http')) ? asset($path) : asset('storage/' . $path);
                    @endphp
                    <a href="{{ $fileUrl }}" target="_blank" class="btn btn-outline btn-sm">
                        <i class="fas fa-paperclip"></i> ดาวน์โหลดหลักฐาน {{ count($evidenceList) > 1 ? ($idx + 1) : '' }}
                    </a>
                    @endforeach
                </div>
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
                    <i class="fas fa-check"></i> รับเรื่อง
                </button>
            </form>
            @endif

            @if($informantReport->Status === 'กำลังตรวจสอบ')
            <form method="POST" action="{{ route('discipline.informant-reports.close', $informantReport->ReportID) }}">
                @csrf @method('PATCH')
                <button type="button" class="btn btn-success btn-close-report">
                    <i class="fas fa-lock"></i> ปิดเรื่อง
                </button>
            </form>
            @endif

            @if($informantReport->Status === 'ปิดเรื่องแล้ว')
            <form method="POST" action="{{ route('discipline.informant-reports.destroy', $informantReport->ReportID) }}">
                @csrf @method('DELETE')
                <button type="button" class="btn btn-danger btn-delete-report">
                    <i class="fas fa-trash"></i> ลบ
                </button>
            </form>
            @endif

            <a href="{{ route('discipline.informant-reports.index') }}" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> ย้อนกลับ
            </a>
        </div>
    </div>
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