@extends('layouts.app')

@section('title', 'รายละเอียดคำร้อง')
@section('page-title', 'รายละเอียดคำร้องโต้แย้ง')

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
            <h3>รายการพฤติกรรมที่โต้แย้ง</h3>
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
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom:0.25rem;">วันที่เกิดเหตุ</div>
                    <div>{{ \Carbon\Carbon::parse($appeal->behaviorRecord->RecordDate)->format('d/m/Y H:i') }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- คำร้องโต้แย้ง --}}
    <div class="card" style="margin-bottom:1rem;">
        <div class="card-header-bar">
            <h3>คำร้องโต้แย้งของนักเรียน</h3>
            <span style="font-size:0.8rem; color:var(--text-muted);">
                ยื่นเมื่อ {{ \Carbon\Carbon::parse($appeal->AppealDate)->format('d/m/Y H:i') }}
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
            <h3>ผลการพิจารณา</h3>
        </div>
        <div class="card-body-pad">
            <p style="font-size:0.875rem; color:var(--text-muted); margin-bottom:1rem;">
                กรุณาพิจารณาคำร้องและเลือกผลการดำเนินการ
            </p>
            <div style="display:flex; gap:0.75rem;">
                <form method="POST" action="{{ route('discipline.appeals.resolve', $appeal->AppealID) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="action" value="คืนคะแนน">
                    <button type="submit" class="btn btn-success"
                            onclick="return confirm('ยืนยันการคืนคะแนนให้นักเรียน?')">
                        <i class="fas fa-undo"></i> คืนคะแนน
                    </button>
                </form>
                <form method="POST" action="{{ route('discipline.appeals.resolve', $appeal->AppealID) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="action" value="ยกเลิกคำร้อง">
                    <button type="submit" class="btn btn-danger"
                            onclick="return confirm('ยืนยันการยกเลิกคำร้องนี้?')">
                        <i class="fas fa-times"></i> ยกเลิกคำร้อง
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection