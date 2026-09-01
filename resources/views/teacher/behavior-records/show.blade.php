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
                        $displayStatus = match($behaviorRecord->Status) {
                            'อนุมัติแล้ว', 'อนุมัติ' => 'อนุมัติ',
                            default => $behaviorRecord->Status,
                        };
                        $sc = match($displayStatus) {
                            'รออนุมัติ' => 'badge-gold', 'อนุมัติ' => 'badge-green',
                            'ปฏิเสธ' => 'badge-red', 'อยู่ในระหว่างยื่นอุทธรณ์' => 'badge-orange',
                            default => 'badge-gray',
                        };
                    @endphp
                    <span class="badge {{ $sc }}">{{ $displayStatus }}</span>
                </div>
                
                <div style="font-weight:600; color:var(--navy);">นักเรียน:</div>
                <div>{{ $behaviorRecord->student->FullName ?? '-' }} (รหัส: {{ $behaviorRecord->student->StudentID ?? '-' }})</div>

                <div style="font-weight:600; color:var(--navy);">หมวดหมู่พฤติกรรม:</div>
                <div>
                    <span class="badge" style="background:#e0f2fe; color:#0369a1; border:1px solid #bae6fd; font-size:0.8rem; font-weight:600;">
                        <i class="fas fa-folder" style="margin-right:0.25rem;"></i> {{ optional($behaviorRecord->rule)->Category ?? 'ทั่วไป' }}
                    </span>
                </div>

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

                @if($behaviorRecord->Photo)
                <div style="font-weight:600; color:var(--navy); margin-bottom:0.5rem;">หลักฐานรูปภาพ:</div>
                <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
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
                @endif

                <div style="font-weight:600; color:var(--navy);">{{ ($behaviorRecord->rule && $behaviorRecord->rule->RuleType === 'เพิ่มคะแนน') ? 'วันที่ทำความดี:' : 'วันที่เกิดเหตุ:' }}</div>
                @php $rd = \Carbon\Carbon::parse($behaviorRecord->RecordDate)->locale('th'); @endphp
                <div>{{ $rd->isoFormat('D MMMM ') . ($rd->year + 543) }}</div>

                <div style="font-weight:600; color:var(--navy);">ผู้บันทึก:</div>
                <div>
                    {{ $behaviorRecord->recorder->FullName ?? '-' }}
                    @if($behaviorRecord->recorder)
                        <span style="color:var(--text-muted); font-size:0.85rem;">(รหัสประจำตัว: {{ $behaviorRecord->recorder->Username ?? $behaviorRecord->recorder->teacher?->TeacherID ?? '-' }})</span>
                    @endif
                </div>
            </div>
            
            @if($behaviorRecord->appeal)
            <hr style="margin:1.5rem 0; border-color:#ede8e0;">
            <div style="background:#fff3e0; padding:1rem; border-radius:4px; border-left:4px solid var(--orange);">
                <h4 style="color:var(--orange); margin-bottom:0.5rem;"><i class="fas fa-exclamation-circle"></i> มีการยื่นเรื่องอุทธรณ์คะแนน</h4>
                <p style="font-size:0.875rem; margin-bottom:0.25rem;"><strong>เหตุผล:</strong> {{ $behaviorRecord->appeal->Reason }}</p>
                <p style="font-size:0.875rem; color:var(--text-muted);">สถานะการอุทธรณ์: {{ $behaviorRecord->appeal->Status }}</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
