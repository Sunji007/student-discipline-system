@extends('layouts.app')

@section('title', 'รายละเอียดคำร้องโต้แย้ง')
@section('page-title', 'รายละเอียดคำร้องโต้แย้ง')

@section('content')
<div style="max-width:680px;">
    <div class="card">
        <div class="card-header-bar">
            <h3>คำร้องโต้แย้ง</h3>
            <a href="{{ route('student.appeals.index') }}" class="btn btn-outline btn-sm">
                <i class="fas fa-arrow-left"></i> ย้อนกลับ
            </a>
        </div>
        <div class="card-body-pad">
            <div style="display:grid; grid-template-columns:120px 1fr; gap:1rem; margin-bottom:1rem;">
                <div style="font-weight:600; color:var(--navy);">สถานะคำร้อง:</div>
                <div>
                    @php
                        $sc = match($appeal->Status) {
                            'รอตรวจสอบ' => 'badge-gold',
                            'คืนคะแนน'  => 'badge-green',
                            'ยกเลิกคำร้อง' => 'badge-red',
                            default      => 'badge-gray',
                        };
                    @endphp
                    <span class="badge {{ $sc }}">{{ $appeal->Status }}</span>
                </div>
                
                <div style="font-weight:600; color:var(--navy);">วันที่ยื่นคำร้อง:</div>
                <div>{{ \Carbon\Carbon::parse($appeal->AppealDate)->locale('th')->isoFormat('D MMMM YYYY') }}</div>

                <div style="font-weight:600; color:var(--navy);">รายการที่โต้แย้ง:</div>
                <div>
                    <strong>{{ $appeal->behaviorRecord->rule->RuleName ?? '-' }}</strong> 
                    (<span style="color:var(--red);">-{{ abs(optional($appeal->behaviorRecord->rule)->ScoreModifier ?? 0) }} คะแนน</span>)
                </div>

                <div style="font-weight:600; color:var(--navy);">วันที่เกิดเหตุ:</div>
                <div>{{ \Carbon\Carbon::parse($appeal->behaviorRecord->RecordDate)->locale('th')->isoFormat('D MMMM YYYY') }}</div>

                <div style="font-weight:600; color:var(--navy);">ผู้บันทึก:</div>
                <div>{{ $appeal->behaviorRecord->recorder->FullName ?? 'ไม่ระบุ' }}</div>
            </div>

            <hr style="margin:1.5rem 0; border-color:#ede8e0;">

            <div style="margin-bottom:1.5rem;">
                <h4 style="margin-bottom:0.75rem; color:var(--navy);">เหตุผลในการโต้แย้ง</h4>
                <div style="padding:1rem; background:#f9f9f9; border-radius:4px; border:1px solid #ede8e0; font-size:0.95rem;">
                    {!! nl2br(e($appeal->Reason)) !!}
                </div>
            </div>

            @if($appeal->EvidencePath)
            <div>
                <h4 style="margin-bottom:0.75rem; color:var(--navy);">หลักฐานแนบ</h4>
                <a href="{{ asset('storage/' . $appeal->EvidencePath) }}" target="_blank" class="btn btn-outline btn-sm">
                    <i class="fas fa-file-download"></i> ดูหลักฐาน / ดาวน์โหลด
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
