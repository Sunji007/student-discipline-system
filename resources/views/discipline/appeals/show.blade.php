@extends('layouts.app')

@section('title', 'รายละเอียดคำขออุทธรณ์')
@section('page-title', 'รายละเอียดการอุทธรณ์คะแนน')

@section('content')
<div style="max-width:720px;">
    <a href="{{ route('discipline.appeals.index') }}" class="btn btn-outline btn-sm" style="margin-bottom:1rem;">
        <i class="fas fa-arrow-left"></i> ย้อนกลับ
    </a>

    {{-- ข้อมูลนักเรียน --}}
    <div class="card" style="margin-bottom:1rem;">
        <div class="card-header-bar">
            <h3>ข้อมูลนักเรียน</h3>
            <span class="badge {{ $appeal->Status === 'รอตรวจสอบ' ? 'badge-gold' : ($appeal->Status === 'คืนคะแนน' ? 'badge-green' : 'badge-red') }}">
                {{ $appeal->Status }}
            </span>
        </div>
        <div class="card-body-pad">
            <div class="form-row">
                <div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom:0.25rem;">ชื่อ-นามสกุล</div>
                    <div style="font-weight:600;">{{ $appeal->student->FullName }}</div>
                </div>
                <div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom:0.25rem;">ห้องเรียน</div>
                    <div>{{ $appeal->student->Classroom }}</div>
                </div>
                <div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom:0.25rem;">คะแนนปัจจุบัน</div>
                    <div style="font-size:1.2rem; font-weight:700; color:{{ $appeal->student->BehaviorScore < 60 ? 'var(--red)' : ($appeal->student->BehaviorScore < 80 ? 'var(--orange)' : 'var(--green)') }}">
                        {{ $appeal->student->BehaviorScore }} คะแนน
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- รายการพฤติกรรมที่โต้แย้ง --}}
    <div class="card" style="margin-bottom:1rem;">
        <div class="card-header-bar">
            <h3>รายการพฤติกรรมที่อุทธรณ์</h3>
        </div>
        <div class="card-body-pad">
            <div style="display:grid; gap:0.75rem;">
                <div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom:0.25rem;">กฎเกณฑ์</div>
                    <span class="badge {{ $appeal->behaviorRecord->rule->RuleType === 'ตัดคะแนน' ? 'badge-red' : 'badge-green' }}">
                        {{ $appeal->behaviorRecord->rule->RuleType }}
                        {{ $appeal->behaviorRecord->rule->RuleType === 'ตัดคะแนน' ? '-' : '+' }}{{ abs($appeal->behaviorRecord->rule->ScoreModifier) }}
                    </span>
                    <span style="margin-left:0.5rem;">{{ $appeal->behaviorRecord->rule->RuleName }}</span>
                </div>
                <div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom:0.25rem;">รายละเอียดจากฝ่ายปกครอง</div>
                    <div style="font-size:0.875rem;">{{ $appeal->behaviorRecord->Description ?? '-' }}</div>
                </div>
                <div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom:0.25rem;">บันทึกโดย</div>
                    <div>{{ $appeal->behaviorRecord->recorder->FullName }}</div>
                </div>
                <div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom:0.25rem;">{{ ($appeal->behaviorRecord?->rule?->RuleType === 'เพิ่มคะแนน') ? 'วันที่ทำความดี' : 'วันที่เกิดเหตุ' }}</div>
                    @php $rd = \Carbon\Carbon::parse($appeal->behaviorRecord->created_at ?? $appeal->behaviorRecord->RecordDate); @endphp
                    <div>{{ $rd->format('d/m/') . ($rd->year + 543) . $rd->format(' H:i') }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- คำร้องโต้แย้ง --}}
    <div class="card" style="margin-bottom:1rem;">
        <div class="card-header-bar">
            <h3>คำขออุทธรณ์ของนักเรียน</h3>
            @php $ad = \Carbon\Carbon::parse($appeal->created_at ?? $appeal->AppealDate); @endphp
            <span style="font-size:0.8rem; color:var(--text-muted);">
                ยื่นเมื่อ {{ $ad->format('d/m/') . ($ad->year + 543) . $ad->format(' H:i') }}
            </span>
        </div>
        <div class="card-body-pad">
            <div style="font-size:0.875rem; line-height:1.7; background:#faf8f4; padding:1rem; border-radius:2px; border-left:3px solid var(--gold);">
                {{ $appeal->Reason }}
            </div>

            @if($appeal->EvidencePath)
            <div style="margin-top:1rem;">
                <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom:0.5rem;">ไฟล์หลักฐาน</div>
                <a href="{{ asset('storage/' . $appeal->EvidencePath) }}" target="_blank" class="btn btn-outline btn-sm">
                    <i class="fas fa-paperclip"></i> ดูไฟล์หลักฐาน
                </a>
            </div>
            @endif
        </div>
    </div>

    {{-- ปุ่มดำเนินการ --}}
    @if($appeal->Status === 'รอตรวจสอบ')
    <div class="card">
        <div class="card-header-bar">
            <h3><i class="fas fa-gavel" style="color:var(--navy); margin-right:0.5rem;"></i>ผลการพิจารณาอุทธรณ์</h3>
        </div>
        <div class="card-body-pad">
            @php
                $origDeducted = abs($appeal->behaviorRecord->rule->ScoreModifier);
                $halfDeducted = ceil($origDeducted / 2);
            @endphp
            
            <form method="POST" action="{{ route('discipline.appeals.resolve', $appeal->AppealID) }}" id="resolveForm">
                @csrf @method('PATCH')
                <input type="hidden" name="action" id="actionInput" value="คืนคะแนน">

                <div id="pointsInputSection" style="background:#f8fafc; border:1px solid #cbd5e1; border-radius:8px; padding:1.1rem; margin-bottom:1.25rem;">
                    <label class="form-label" style="font-weight:700; color:var(--navy); font-size:0.9rem;">
                        <i class="fas fa-coins" style="color:var(--gold); margin-right:0.35rem;"></i>
                        ระบุจำนวนคะแนนที่เหมาะสมที่ต้องการคืนให้นักเรียน <span style="color:var(--red)">*</span>
                    </label>
                    
                    <div style="display:flex; align-items:center; gap:0.75rem; margin-top:0.4rem; flex-wrap:wrap;">
                        <input type="number" 
                               name="restored_points" 
                               id="restoredPointsInput" 
                               class="form-control" 
                               style="width:160px; font-size:1.15rem; font-weight:700; text-align:center; color:#047857; border-color:#10b981; background:#fff;" 
                               value="{{ old('restored_points', $origDeducted) }}" 
                               min="1" 
                               max="100" 
                               required>
                        <span style="font-weight:600; font-size:0.9rem; color:var(--text-muted);">คะแนน</span>
                        
                        {{-- Quick Presets --}}
                        <div style="display:flex; gap:0.4rem; align-items:center;">
                            <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('restoredPointsInput').value = {{ $origDeducted }}" style="font-size:0.78rem;">
                                <i class="fas fa-check-double" style="color:var(--green)"></i> คืนเต็ม ({{ $origDeducted }} คะแนน)
                            </button>
                            <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('restoredPointsInput').value = {{ $halfDeducted }}" style="font-size:0.78rem;">
                                <i class="fas fa-adjust" style="color:var(--gold)"></i> คืนครึ่งหนึ่ง ({{ $halfDeducted }} คะแนน)
                            </button>
                        </div>
                    </div>

                    <small style="color:var(--text-muted); display:block; margin-top:0.6rem; font-size:0.78rem;">
                        <i class="fas fa-info-circle" style="color:var(--gold);"></i> ฝ่ายปกครองสามารถให้คะแนนความเหมาะสมตามดุลพินิจได้ (คืนคะแนนเต็ม {{ $origDeducted }} คะแนน หรือพิมพ์ระบุจำนวนคะแนนที่พิจารณาเห็นชอบได้)
                    </small>
                </div>

                <div style="margin-bottom:1.25rem;">
                    <label class="form-label" style="font-weight:700; color:var(--navy); font-size:0.9rem;">
                        <i class="fas fa-comment-alt" style="color:var(--navy); margin-right:0.35rem;"></i>
                        ความเห็น / เหตุผลประกอบการพิจารณาของฝ่ายปกครอง (ถ้ามี)
                    </label>
                    <textarea name="review_notes" class="form-control" rows="2" placeholder="ระบุเหตุผลในการปรับคืนคะแนนความเหมาะสม หรือเหตุผลที่ปฏิเสธ..."></textarea>
                </div>

                <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
                    <button type="button" class="btn btn-success" onclick="submitResolve('คืนคะแนน')">
                        <i class="fas fa-check"></i> ยืนยัน
                    </button>
                    <button type="button" class="btn btn-danger" onclick="submitResolve('ยกเลิกคำร้อง')">
                        <i class="fas fa-times"></i> ปฏิเสธ
                    </button>
                </div>
            </form>
        </div>
    </div>
    @else
    {{-- แสดงผลการพิจารณาที่เสร็จสิ้นแล้ว --}}
    <div class="card">
        <div class="card-header-bar">
            <h3><i class="fas fa-history" style="color:var(--navy); margin-right:0.5rem;"></i>ผลการพิจารณาโดยฝ่ายปกครอง</h3>
        </div>
        <div class="card-body-pad">
            <div style="display:grid; gap:0.75rem;">
                <div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom:0.25rem;">สถานะการพิจารณา</div>
                    <span class="badge {{ $appeal->Status === 'คืนคะแนน' ? 'badge-green' : 'badge-red' }}">
                        {{ $appeal->Status }}
                    </span>
                </div>
                @if($appeal->Status === 'คืนคะแนน')
                <div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom:0.25rem;">จำนวนคะแนนที่คืน</div>
                    @php $resPts = $appeal->RestoredPoints ?? abs(optional($appeal->behaviorRecord?->rule)->ScoreModifier ?? 0); @endphp
                    <span class="badge badge-green" style="font-size:0.9rem; font-weight:700;">
                        <i class="fas fa-plus-circle"></i> +{{ $resPts }} คะแนน
                    </span>
                </div>
                @endif
                @if($appeal->ReviewNotes)
                <div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom:0.25rem;">ความเห็นจากฝ่ายปกครอง</div>
                    <div style="font-size:0.875rem; background:#f8fafc; padding:0.75rem; border-radius:6px; border-left:3px solid var(--navy);">
                        {{ $appeal->ReviewNotes }}
                    </div>
                </div>
                @endif
                @if($appeal->ReviewDate)
                <div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom:0.25rem;">วันที่พิจารณา</div>
                    @php $rdDate = \Carbon\Carbon::parse($appeal->ReviewDate); @endphp
                    <div>{{ $rdDate->format('d/m/') . ($rdDate->year + 543) . $rdDate->format(' H:i') }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
    function submitResolve(action) {
        const actionInput = document.getElementById('actionInput');
        const pointsInput = document.getElementById('restoredPointsInput');
        const form = document.getElementById('resolveForm');

        actionInput.value = action;

        if (action === 'คืนคะแนน') {
            const points = pointsInput.value ? parseInt(pointsInput.value) : 0;
            if (!points || points <= 0) {
                Swal.fire('ข้อผิดพลาด', 'กรุณาระบุจำนวนคะแนนที่ต้องการคืนให้ถูกต้อง', 'warning');
                return;
            }

            Swal.fire({
                title: `ยืนยันการคืนคะแนน ${points} คะแนน?`,
                text: `ระบบจะทำการเพิ่มคะแนนพฤติกรรมให้กับนักเรียนจำนวน ${points} คะแนน`,
                icon: 'success',
                showCancelButton: true,
                confirmButtonText: 'ยืนยัน',
                cancelButtonText: 'ยกเลิก',
                customClass: {
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
            Swal.fire({
                title: 'ยืนยันการปฏิเสธคำขออุทธรณ์?',
                text: 'ระบบจะปฏิเสธคำขออุทธรณ์นี้และคงคะแนนการหักเดิมไว้',
                icon: 'error',
                showCancelButton: true,
                confirmButtonText: 'ปฏิเสธ',
                cancelButtonText: 'ยกเลิก',
                customClass: {
                    confirmButton: 'swal2-confirm btn-swal-danger',
                    cancelButton: 'swal2-cancel btn-swal-cancel'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    }
</script>
@endpush
@endsection