@extends('layouts.app')

@section('title', 'คำร้องโต้แย้ง')
@section('page-title', 'คำร้องโต้แย้งของฉัน')

@section('content')
<div class="page-header" style="display:flex; align-items:center; justify-content:space-between;">
    <div>
        <h2>คำร้องโต้แย้งคะแนน</h2>
        <p>ติดตามสถานะคำร้องที่ยื่นไว้</p>
    </div>
    <a href="{{ route('student.appeals.create') }}" class="btn btn-gold">
        <i class="fas fa-plus"></i> ยื่นคำร้องใหม่
    </a>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>รายการที่โต้แย้ง</th>
                    <th>คะแนน</th>
                    <th>เหตุผล</th>
                    <th>วันที่ยื่น</th>
                    <th>ผลการพิจารณา</th>
                </tr>
            </thead>
            <tbody>
                @forelse($appeals as $appeal)
                <tr>
                    <td>
                        <div style="font-weight:500;">{{ $appeal->behaviorRecord->rule->RuleName }}</div>
                        <div style="font-size:0.75rem; color:var(--text-muted);">
                            {{ \Carbon\Carbon::parse($appeal->behaviorRecord->RecordDate)->format('d/m/Y') }}
                        </div>
                    </td>
                    <td>
                        <span style="color:var(--red); font-weight:700;">
                            -{{ abs($appeal->behaviorRecord->rule->ScoreModifier) }}
                        </span>
                    </td>
                    <td style="font-size:0.82rem; max-width:220px;">
                        {{ \Str::limit($appeal->Reason, 70) }}
                        @if($appeal->EvidencePath)
                            <span class="badge badge-navy" style="font-size:0.65rem; margin-left:0.35rem;">
                                <i class="fas fa-paperclip"></i> มีหลักฐาน
                            </span>
                        @endif
                    </td>
                    <td style="font-size:0.82rem; color:var(--text-muted);">
                        {{ \Carbon\Carbon::parse($appeal->AppealDate)->format('d/m/Y') }}
                    </td>
                    <td>
                        @php
                            $sc = match($appeal->Status) {
                                'รอตรวจสอบ'    => 'badge-gold',
                                'คืนคะแนน'     => 'badge-green',
                                'ยกเลิกคำร้อง' => 'badge-red',
                                default         => 'badge-gray',
                            };
                            $icon = match($appeal->Status) {
                                'รอตรวจสอบ'    => 'fa-hourglass-half',
                                'คืนคะแนน'     => 'fa-check-circle',
                                'ยกเลิกคำร้อง' => 'fa-times-circle',
                                default         => 'fa-circle',
                            };
                        @endphp
                        <span class="badge {{ $sc }}">
                            <i class="fas {{ $icon }}" style="margin-right:0.25rem;"></i>
                            {{ $appeal->Status }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center; padding:2.5rem; color:var(--text-muted);">
                        <i class="fas fa-balance-scale" style="font-size:1.8rem; opacity:0.3; display:block; margin-bottom:0.75rem;"></i>
                        ยังไม่มีคำร้องโต้แย้ง
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding:1rem 1.25rem; border-top:1px solid #ede8e0;">
        {{ $appeals->links() }}
    </div>
</div>
@endsection