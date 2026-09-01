@extends('layouts.app')

@section('title', 'รายละเอียดบันทึกพฤติกรรม')
@section('page-title', 'รายละเอียดบันทึกพฤติกรรม')

@section('content')
<div style="max-width:680px;">
    <a href="{{ route('discipline.behavior-records.index') }}" class="btn btn-outline btn-sm" style="margin-bottom:1rem;">
        <i class="fas fa-arrow-left"></i> ย้อนกลับ
    </a>

    <div class="card" style="margin-bottom:1rem;">
        <div class="card-header-bar">
            <h3>ข้อมูลบันทึก</h3>
            @php
                $displayStatus = match($behaviorRecord->Status) {
                    'อนุมัติแล้ว', 'อนุมัติ' => 'อนุมัติ',
                    default => $behaviorRecord->Status,
                };
                $sc = match($displayStatus) {
                    'รออนุมัติ' => 'badge-gold', 'อนุมัติ' => 'badge-green',
                    'ปฏิเสธ' => 'badge-red', default => 'badge-navy',
                };
            @endphp
            <span class="badge {{ $sc }}" style="font-size:0.85rem; padding:0.35rem 0.75rem;">{{ $displayStatus }}</span>
        </div>
        <div class="card-body-pad">
            <div class="responsive-grid-2">
                <div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom:0.2rem;">นักเรียน</div>
                    <div style="font-weight:600;">{{ $behaviorRecord->student->FullName ?? '-' }}</div>
                    <div style="font-size:0.8rem; color:var(--text-muted);">
                        @if($behaviorRecord->student)
                            รหัสประจำตัว: {{ $behaviorRecord->student->StudentID }} | ชั้น {{ $behaviorRecord->student->Classroom ?? '-' }}
                        @else
                            -
                        @endif
                    </div>
                </div>
                <div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom:0.2rem;">คะแนนปัจจุบัน</div>
                    <div style="font-size:1.2rem; font-weight:700; color:var(--navy);">
                        {{ $behaviorRecord->student->BehaviorScore ?? '-' }}
                    </div>
                </div>
                <div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom:0.2rem;">หมวดหมู่พฤติกรรม</div>
                    <div>
                        <span class="badge" style="background:#e0f2fe; color:#0369a1; border:1px solid #bae6fd; font-size:0.8rem; font-weight:600;">
                            <i class="fas fa-folder" style="margin-right:0.25rem;"></i> {{ $behaviorRecord->rule->Category ?? 'ทั่วไป' }}
                        </span>
                    </div>
                </div>
                <div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom:0.2rem;">กฎเกณฑ์</div>
                    <span class="badge {{ $behaviorRecord->rule->RuleType === 'ตัดคะแนน' ? 'badge-red' : 'badge-green' }}">
                        {{ $behaviorRecord->rule->RuleType }}
                    </span>
                    <div style="font-size:0.875rem; margin-top:0.25rem; font-weight:600;">{{ $behaviorRecord->rule->RuleName ?? '-' }}</div>
                </div>
                <div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom:0.2rem;">ผลกระทบคะแนน</div>
                    <strong style="font-size:1.3rem; color:{{ $behaviorRecord->rule->RuleType === 'ตัดคะแนน' ? 'var(--red)' : 'var(--green)' }}">
                        {{ $behaviorRecord->rule->RuleType === 'ตัดคะแนน' ? '-' : '+' }}{{ abs($behaviorRecord->rule->ScoreModifier ?? 0) }}
                    </strong>
                </div>
                <div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom:0.2rem;">วันที่บันทึก</div>
                    @php $rd = \Carbon\Carbon::parse($behaviorRecord->created_at ?? $behaviorRecord->RecordDate); @endphp
                    <div>{{ $rd->format('d/m/') . ($rd->year + 543) . $rd->format(' H:i') }}</div>
                </div>
                <div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom:0.2rem;">ผู้บันทึก</div>
                    <div style="font-weight:600;">{{ $behaviorRecord->recorder->FullName ?? '-' }}</div>
                    @if($behaviorRecord->recorder)
                        <div style="font-size:0.8rem; color:var(--text-muted);">
                            รหัสประจำตัว: {{ $behaviorRecord->recorder->Username ?? $behaviorRecord->recorder->teacher?->TeacherID ?? '-' }}
                        </div>
                    @endif
                </div>
                @if($behaviorRecord->Penalty)
                <div style="grid-column:1/-1;">
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom:0.2rem;">มาตรการ/การลงโทษ</div>
                    <div>{{ $behaviorRecord->Penalty }}</div>
                </div>
                @endif
                @if($behaviorRecord->Description)
                <div style="grid-column:1/-1;">
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom:0.2rem;">รายละเอียด</div>
                    <div style="background:#faf8f4; padding:0.75rem; border-radius:4px; font-size:0.875rem;">
                        {{ $behaviorRecord->Description }}
                    </div>
                </div>
                @endif
                @if($behaviorRecord->Photo)
                <div style="grid-column:1/-1;">
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom:0.4rem;">หลักฐานรูปภาพ</div>
                    <div style="display:flex; gap:0.75rem; flex-wrap:wrap; margin-top:0.25rem;">
                        @php
                            $photos = [];
                            if (str_starts_with($behaviorRecord->Photo, '[') && str_ends_with($behaviorRecord->Photo, ']')) {
                                $photos = json_decode($behaviorRecord->Photo, true) ?: [];
                            } else {
                                $photos = [$behaviorRecord->Photo];
                            }
                        @endphp
                        @foreach($photos as $p)
                            <img src="{{ asset('storage/' . $p) }}" 
                                 alt="หลักฐานพฤติกรรม" 
                                 style="max-width:160px; max-height:160px; object-fit:cover; border-radius:8px; border:1px solid #ede8e0; cursor:pointer; transition: transform 0.2s;"
                                 onclick="window.open(this.src, '_blank')"
                                 onmouseover="this.style.transform='scale(1.02)'"
                                 onmouseout="this.style.transform='scale(1)'">
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- อุทธรณ์คะแนน (ถ้ามี) --}}
    @if($behaviorRecord->appeal)
    <div class="card" style="margin-bottom:1rem;">
        <div class="card-header-bar">
            <h3>อุทธรณ์คะแนนที่เกี่ยวข้อง</h3>
            <a href="{{ route('discipline.appeals.show', $behaviorRecord->appeal->AppealID) }}" class="btn btn-outline btn-sm">
                ดูรายละเอียด
            </a>
        </div>
        <div class="card-body-pad">
            <span class="badge {{ $behaviorRecord->appeal->Status === 'รอตรวจสอบ' ? 'badge-gold' : 'badge-green' }}">
                {{ $behaviorRecord->appeal->Status }}
            </span>
            <span style="font-size:0.875rem; margin-left:0.5rem; color:var(--text-muted);">
                @php $ad = \Carbon\Carbon::parse($behaviorRecord->appeal->created_at ?? $behaviorRecord->appeal->AppealDate); @endphp
                ยื่นเมื่อ {{ $ad->format('d/m/') . ($ad->year + 543) . $ad->format(' H:i') }}
            </span>
        </div>
    </div>
    @endif

    {{-- ปุ่มดำเนินการ --}}
    @if($behaviorRecord->Status === 'รออนุมัติ')
    <div class="card">
        <div class="card-header-bar"><h3>ดำเนินการ</h3></div>
        <div class="card-body-pad">
            <div style="display:flex; gap:0.75rem;">
                <form method="POST" action="{{ route('discipline.behavior-records.approve', $behaviorRecord->RecordID) }}" id="approveRecordForm">
                    @csrf @method('PATCH')
                    <button type="button" class="btn btn-success" onclick="confirmApprove()">
                        <i class="fas fa-check"></i> อนุมัติ
                    </button>
                </form>

                <form method="POST" action="{{ route('discipline.behavior-records.reject', $behaviorRecord->RecordID) }}" id="rejectRecordForm">
                    @csrf @method('PATCH')
                    <button type="button" class="btn btn-danger" onclick="confirmReject()">
                        <i class="fas fa-times"></i> ยกเลิก
                    </button>
                </form>
            </div>
        </div>
    </div>
    @elseif($behaviorRecord->Status === 'ยกเลิก')
    <div class="card">
        <div class="card-header-bar"><h3>ดำเนินการ</h3></div>
        <div class="card-body-pad">
            <div style="display:flex; gap:0.75rem;">
                <form method="POST" action="{{ route('discipline.behavior-records.destroy', $behaviorRecord->RecordID) }}" id="deleteRecordForm">
                    @csrf @method('DELETE')
                    <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                        <i class="fas fa-trash-alt"></i> เอาออก
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
    function confirmApprove() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'ยืนยันการอนุมัติ?',
                text: 'ระบบจะทำการบันทึกและปรับคะแนนพฤติกรรมของนักเรียนทันที',
                icon: 'success',
                showCancelButton: true,
                confirmButtonText: 'ตกลง',
                cancelButtonText: 'ยกเลิก',
                customClass: {
                    confirmButton: 'swal2-confirm btn-swal-success',
                    cancelButton: 'swal2-cancel btn-swal-cancel'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('approveRecordForm').submit();
                }
            });
        } else {
            if (confirm('ยืนยันการอนุมัติรายการนี้?')) {
                document.getElementById('approveRecordForm').submit();
            }
        }
    }

    function confirmReject() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'ยืนยันการยกเลิกรายการ?',
                text: 'รายการบันทึกพฤติกรรมนี้จะถูกยกเลิกและไม่มีการตัดหรือเพิ่มคะแนนนักเรียน',
                icon: 'error',
                showCancelButton: true,
                confirmButtonText: 'ตกลง',
                cancelButtonText: 'ย้อนกลับ',
                customClass: {
                    confirmButton: 'swal2-confirm btn-swal-danger',
                    cancelButton: 'swal2-cancel btn-swal-cancel'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('rejectRecordForm').submit();
                }
            });
        } else {
            if (confirm('ยืนยันการยกเลิกรายการนี้?')) {
                document.getElementById('rejectRecordForm').submit();
            }
        }
    }

    function confirmDelete() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'ยืนยันการเอาออกรายการนี้?',
                text: 'รายการบันทึกนี้จะถูกลบออกจากระบบอย่างถาวรและไม่สามารถย้อนกลับได้',
                icon: 'error',
                showCancelButton: true,
                confirmButtonText: 'ตกลง',
                cancelButtonText: 'ยกเลิก',
                customClass: {
                    confirmButton: 'swal2-confirm btn-swal-danger',
                    cancelButton: 'swal2-cancel btn-swal-cancel'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('deleteRecordForm').submit();
                }
            });
        } else {
            if (confirm('ยืนยันการเอาออกรายการนี้?')) {
                document.getElementById('deleteRecordForm').submit();
            }
        }
    }
</script>
@endpush
@endsection
