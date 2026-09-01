@extends('layouts.app')

@section('title', 'รายละเอียดการอุทธรณ์คะแนน')
@section('page-title', 'รายละเอียดการอุทธรณ์คะแนน')

@section('content')
<div style="max-width:680px;">
    <div class="card">
        <div class="card-header-bar">
            <h3>อุทธรณ์คะแนน</h3>
            <div style="display:flex; gap:0.5rem; align-items:center;">
                @if($appeal->Status === 'รอตรวจสอบ')
                    <form method="POST" action="{{ route('student.appeals.cancel', $appeal->AppealID) }}" 
                          data-confirm="คุณต้องการยกเลิกการอุทธรณ์นี้ใช่หรือไม่?"
                          data-confirm-title="ยืนยันการยกเลิกการอุทธรณ์"
                          data-confirm-theme="danger"
                          data-confirm-submit="ยกเลิกการอุทธรณ์"
                          data-confirm-icon="📋"
                          style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="fas fa-times"></i> ยกเลิก
                        </button>
                    </form>
                @endif
                <a href="{{ route('student.appeals.index') }}" class="btn btn-outline btn-sm">
                    <i class="fas fa-arrow-left"></i> ย้อนกลับ
                </a>
            </div>
        </div>
        <div class="card-body-pad">
            @php
                $displayStatus = in_array($appeal->Status, ['ยกเลิกคำร้อง', 'ยกเลิกคำร้องยื่นอุทธรณ์']) ? 'ยกเลิกคำร้อง' : $appeal->Status;
                $sc = match($displayStatus) {
                    'รอตรวจสอบ'  => 'badge-gold',
                    'คืนคะแนน'   => 'badge-green',
                    'ยกเลิกคำร้อง' => 'badge-red',
                    default       => 'badge-gray',
                };
                $pointsRestored = $appeal->RestoredPoints ?? abs(optional($appeal->behaviorRecord?->rule)->ScoreModifier ?? 0);
            @endphp

            @if($appeal->Status === 'คืนคะแนน')
            <div style="background:#f0fdf4; border:1.5px solid #86efac; border-radius:10px; padding:1.1rem 1.25rem; margin-bottom:1.5rem; display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap;">
                <div style="display:flex; align-items:center; gap:0.85rem;">
                    <div style="width:44px; height:44px; border-radius:50%; background:#dcfce7; color:#16a34a; display:flex; align-items:center; justify-content:center; font-size:1.35rem; flex-shrink:0;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <div style="font-weight:700; color:#166534; font-size:1.05rem;">
                            ฝ่ายปกครองพิจารณา "คืนคะแนน" เรียบร้อยแล้ว
                        </div>
                        <div style="font-size:0.82rem; color:#15803d; margin-top:0.15rem;">
                            คะแนนความประพฤติได้รับการปรับปรุงคืนเข้าสู่ระบบแล้ว
                        </div>
                    </div>
                </div>
                <div style="text-align:right; background:#fff; padding:0.45rem 1.1rem; border-radius:8px; border:1px solid #bbf7d0; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
                    <div style="font-size:0.75rem; color:#166534; font-weight:600;">ฝ่ายปกครองคืนคะแนน</div>
                    <div style="font-size:1.4rem; font-weight:800; color:#16a34a; line-height:1.2;">
                        +{{ $pointsRestored }} คะแนน
                    </div>
                </div>
            </div>
            @endif

            <div style="display:grid; grid-template-columns:130px 1fr; gap:1rem; margin-bottom:1rem;">
                <div style="font-weight:600; color:var(--navy);">สถานะการอุทธรณ์:</div>
                <div>
                    <span class="badge {{ $sc }}">{{ $displayStatus }}</span>
                </div>
                
                @if($appeal->Status === 'คืนคะแนน')
                <div style="font-weight:600; color:var(--navy);">จำนวนคะแนนที่คืน:</div>
                <div>
                    <span class="badge badge-green" style="font-size:0.9rem; font-weight:700;">
                        <i class="fas fa-plus-circle"></i> +{{ $pointsRestored }} คะแนน
                    </span>
                </div>
                @endif

                <div style="font-weight:600; color:var(--navy);">วันที่ยื่นเรื่อง:</div>
                @php $ad = \Carbon\Carbon::parse($appeal->created_at ?? $appeal->AppealDate)->locale('th'); @endphp
                <div>{{ $ad->isoFormat('D MMMM ') . ($ad->year + 543) . $ad->format(' H:i น.') }}</div>

                <div style="font-weight:600; color:var(--navy);">รายการที่อุทธรณ์:</div>
                <div>
                    <strong>{{ $appeal->behaviorRecord->rule->RuleName ?? '-' }}</strong> 
                    (<span style="color:var(--red);">-{{ abs(optional($appeal->behaviorRecord->rule)->ScoreModifier ?? 0) }} คะแนน</span>)
                </div>

                <div style="font-weight:600; color:var(--navy);">{{ ($appeal->behaviorRecord?->rule?->RuleType === 'เพิ่มคะแนน') ? 'วันที่ทำความดี:' : 'วันที่เกิดเหตุ:' }}</div>
                @php $rd = \Carbon\Carbon::parse($appeal->behaviorRecord->created_at ?? $appeal->behaviorRecord->RecordDate)->locale('th'); @endphp
                <div>{{ $rd->isoFormat('D MMMM ') . ($rd->year + 543) . $rd->format(' H:i น.') }}</div>

                <div style="font-weight:600; color:var(--navy);">ผู้บันทึก:</div>
                <div>{{ $appeal->behaviorRecord->recorder->FullName ?? 'ไม่ระบุ' }}</div>

                @if($appeal->ReviewDate)
                <div style="font-weight:600; color:var(--navy);">วันที่พิจารณา:</div>
                @php $revDate = \Carbon\Carbon::parse($appeal->ReviewDate)->locale('th'); @endphp
                <div>{{ $revDate->isoFormat('D MMMM ') . ($revDate->year + 543) }}</div>
                @endif

                @if($appeal->reviewer)
                <div style="font-weight:600; color:var(--navy);">ผู้พิจารณา:</div>
                <div>{{ $appeal->reviewer->FullName }}</div>
                @endif
            </div>

            <hr style="margin:1.5rem 0; border-color:#ede8e0;">

            <div style="margin-bottom:1.5rem;">
                <h4 style="margin-bottom:0.75rem; color:var(--navy);">เหตุผลการขออุทธรณ์</h4>
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

            @if($appeal->ReviewNotes)
            <div style="margin-top:1.5rem;">
                <h4 style="margin-bottom:0.75rem; color:var(--navy);"><i class="fas fa-comment-dots" style="color:var(--navy); margin-right:0.35rem;"></i>ความเห็นผลการพิจารณาจากฝ่ายปกครอง</h4>
                <div style="padding:1rem; background:#f8fafc; border-radius:6px; border-left:4px solid var(--navy); font-size:0.95rem;">
                    {!! nl2br(e($appeal->ReviewNotes)) !!}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
