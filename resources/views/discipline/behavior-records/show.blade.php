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
                $sc = match($behaviorRecord->Status) {
                    'รออนุมัติ' => 'badge-gold', 'อนุมัติแล้ว' => 'badge-green',
                    'ปฏิเสธ' => 'badge-red', default => 'badge-navy',
                };
            @endphp
            <span class="badge {{ $sc }}" style="font-size:0.85rem; padding:0.35rem 0.75rem;">{{ $behaviorRecord->Status }}</span>
        </div>
        <div class="card-body-pad">
            <div class="responsive-grid-2">
                <div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom:0.2rem;">นักเรียน</div>
                    <div style="font-weight:600;">{{ $behaviorRecord->student->FullName ?? '-' }}</div>
                    <div style="font-size:0.8rem; color:var(--text-muted);">{{ $behaviorRecord->student->Classroom ?? '' }}</div>
                </div>
                <div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom:0.2rem;">คะแนนปัจจุบัน</div>
                    <div style="font-size:1.2rem; font-weight:700; color:var(--navy);">
                        {{ $behaviorRecord->student->BehaviorScore ?? '-' }}
                    </div>
                </div>
                <div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom:0.2rem;">กฎเกณฑ์</div>
                    <span class="badge {{ $behaviorRecord->rule->RuleType === 'ตัดคะแนน' ? 'badge-red' : 'badge-green' }}">
                        {{ $behaviorRecord->rule->RuleType }}
                    </span>
                    <div style="font-size:0.875rem; margin-top:0.25rem;">{{ $behaviorRecord->rule->RuleName ?? '-' }}</div>
                </div>
                <div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom:0.2rem;">ผลกระทบคะแนน</div>
                    <strong style="font-size:1.3rem; color:{{ $behaviorRecord->rule->RuleType === 'ตัดคะแนน' ? 'var(--red)' : 'var(--green)' }}">
                        {{ $behaviorRecord->rule->RuleType === 'ตัดคะแนน' ? '-' : '+' }}{{ abs($behaviorRecord->rule->ScoreModifier ?? 0) }}
                    </strong>
                </div>
                <div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom:0.2rem;">วันที่บันทึก</div>
                    <div>{{ \Carbon\Carbon::parse($behaviorRecord->RecordDate)->format('d/m/Y H:i') }}</div>
                </div>
                <div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom:0.2rem;">ผู้บันทึก</div>
                    <div>{{ $behaviorRecord->recorder->FullName ?? '-' }}</div>
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
            </div>
        </div>
    </div>

    {{-- คำร้องโต้แย้ง (ถ้ามี) --}}
    @if($behaviorRecord->appeal)
    <div class="card" style="margin-bottom:1rem;">
        <div class="card-header-bar">
            <h3>คำร้องโต้แย้งที่เกี่ยวข้อง</h3>
            <a href="{{ route('discipline.appeals.show', $behaviorRecord->appeal->AppealID) }}" class="btn btn-outline btn-sm">
                ดูรายละเอียด
            </a>
        </div>
        <div class="card-body-pad">
            <span class="badge {{ $behaviorRecord->appeal->Status === 'รอตรวจสอบ' ? 'badge-gold' : 'badge-green' }}">
                {{ $behaviorRecord->appeal->Status }}
            </span>
            <span style="font-size:0.875rem; margin-left:0.5rem; color:var(--text-muted);">
                ยื่นเมื่อ {{ \Carbon\Carbon::parse($behaviorRecord->appeal->AppealDate)->format('d/m/Y') }}
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
                <form method="POST" action="{{ route('discipline.behavior-records.approve', $behaviorRecord->RecordID) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn btn-success"
                            onclick="return confirm('ยืนยันการอนุมัติและปรับคะแนนนักเรียน?')">
                        <i class="fas fa-check"></i> อนุมัติ
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
