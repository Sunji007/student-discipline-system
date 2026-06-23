@extends('layouts.app')

@section('title', 'รายละเอียดบันทึก')
@section('page-title', 'รายละเอียดการบันทึกพฤติกรรม')

@section('content')
<div style="max-width:680px;">
    <div class="card">
        <div class="card-header-bar">
            <h3>ข้อมูลบันทึกพฤติกรรม</h3>
            <a href="{{ route('teacher.behavior-records.index') }}" class="btn btn-outline btn-sm">
                <i class="fas fa-arrow-left"></i> ย้อนกลับ
            </a>
        </div>
        <div class="card-body-pad">
            <div style="display:grid; grid-template-columns:120px 1fr; gap:1rem; margin-bottom:1rem;">
                <div style="font-weight:600; color:var(--navy);">สถานะ:</div>
                <div>
                    @php
                        $sc = match($behaviorRecord->Status) {
                            'รออนุมัติ' => 'badge-gold', 'อนุมัติแล้ว' => 'badge-green',
                            'ปฏิเสธ' => 'badge-red', 'อยู่ในระหว่างโต้แย้ง' => 'badge-orange',
                            default => 'badge-gray',
                        };
                    @endphp
                    <span class="badge {{ $sc }}">{{ $behaviorRecord->Status }}</span>
                </div>
                
                <div style="font-weight:600; color:var(--navy);">นักเรียน:</div>
                <div>{{ $behaviorRecord->student->FullName ?? '-' }} (รหัส: {{ $behaviorRecord->student->StudentID ?? '-' }})</div>

                <div style="font-weight:600; color:var(--navy);">พฤติกรรม:</div>
                <div>{{ $behaviorRecord->rule->RuleName ?? '-' }}</div>

                <div style="font-weight:600; color:var(--navy);">คะแนน:</div>
                <div>
                    <strong style="color:{{ optional($behaviorRecord->rule)->RuleType === 'ตัดคะแนน' ? 'var(--red)' : 'var(--green)' }}">
                        {{ optional($behaviorRecord->rule)->RuleType === 'ตัดคะแนน' ? '-' : '+' }}{{ abs(optional($behaviorRecord->rule)->ScoreModifier ?? 0) }}
                    </strong>
                </div>

                <div style="font-weight:600; color:var(--navy);">รายละเอียด:</div>
                <div>{{ $behaviorRecord->Description ?: '-' }}</div>

                <div style="font-weight:600; color:var(--navy);">บทลงโทษเบื้องต้น:</div>
                <div>{{ $behaviorRecord->Penalty ?: '-' }}</div>

                <div style="font-weight:600; color:var(--navy);">วันที่เกิดเหตุ:</div>
                <div>{{ \Carbon\Carbon::parse($behaviorRecord->RecordDate)->locale('th')->isoFormat('D MMMM YYYY') }}</div>
            </div>
            
            @if($behaviorRecord->appeal)
            <hr style="margin:1.5rem 0; border-color:#ede8e0;">
            <div style="background:#fff3e0; padding:1rem; border-radius:4px; border-left:4px solid var(--orange);">
                <h4 style="color:var(--orange); margin-bottom:0.5rem;"><i class="fas fa-exclamation-circle"></i> มีการยื่นคำร้องโต้แย้ง</h4>
                <p style="font-size:0.875rem; margin-bottom:0.25rem;"><strong>เหตุผล:</strong> {{ $behaviorRecord->appeal->Reason }}</p>
                <p style="font-size:0.875rem; color:var(--text-muted);">สถานะคำร้อง: {{ $behaviorRecord->appeal->Status }}</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
