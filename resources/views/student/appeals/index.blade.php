@extends('layouts.app')

@section('title', 'อุทธรณ์คะแนน')
@section('page-title', 'อุทธรณ์คะแนนของฉัน')

@section('content')
<div class="page-header" style="display:flex; align-items:center; justify-content:space-between;">
    <div>
        <h2>อุทธรณ์คะแนน</h2>
        <p>ติดตามสถานะคำขออุทธรณ์ที่ยื่นไว้</p>
    </div>
    <a href="{{ route('student.appeals.create') }}" class="btn btn-gold">
        <i class="fas fa-plus"></i> เพิ่ม
    </a>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>รายการที่อุทธรณ์</th>
                    <th>คะแนน</th>
                    <th>เหตุผล</th>
                    <th>วันที่ยื่น</th>
                    <th>ผลการพิจารณา</th>
                    <th style="width:120px; text-align:center;">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($appeals as $appeal)
                <tr>
                    <td>
                        <div style="font-weight:500;">{{ $appeal->behaviorRecord->rule->RuleName }}</div>
                        <div style="font-size:0.75rem; color:var(--text-muted);">
                            @php $rd = \Carbon\Carbon::parse($appeal->behaviorRecord->created_at ?? $appeal->behaviorRecord->RecordDate); @endphp
                            {{ $rd->format('d/m/') . ($rd->year + 543) }}
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
                        @php $ad = \Carbon\Carbon::parse($appeal->created_at ?? $appeal->AppealDate); @endphp
                        {{ $ad->format('d/m/') . ($ad->year + 543) . $ad->format(' H:i') }}
                    </td>
                    <td>
                        @php
                            $displayStatus = in_array($appeal->Status, ['ยกเลิกคำร้อง', 'ยกเลิกคำร้องยื่นอุทธรณ์']) ? 'ยกเลิกคำร้อง' : $appeal->Status;
                            $sc = match($displayStatus) {
                                'รอตรวจสอบ'  => 'badge-gold',
                                'คืนคะแนน'   => 'badge-green',
                                'ยกเลิกคำร้อง' => 'badge-red',
                                default       => 'badge-gray',
                            };
                            $icon = match($displayStatus) {
                                'รอตรวจสอบ'  => 'fa-hourglass-half',
                                'คืนคะแนน'   => 'fa-check-circle',
                                'ยกเลิกคำร้อง' => 'fa-times-circle',
                                default       => 'fa-circle',
                            };
                        @endphp
                        <span class="badge {{ $sc }}" style="display:inline-flex; align-items:center; gap:0.25rem;">
                            <i class="fas {{ $icon }}"></i> {{ $displayStatus }}
                        </span>
                        @if($appeal->Status === 'คืนคะแนน')
                            @php $refPts = $appeal->RestoredPoints ?? abs($appeal->behaviorRecord?->rule?->ScoreModifier ?? 0); @endphp
                            <div style="font-size:0.75rem; color:#16a34a; font-weight:700; margin-top:0.25rem;">
                                (+{{ $refPts }} คะแนน)
                            </div>
                        @endif
                    </td>
                    <td style="text-align:center;">
                        <div style="display:flex; gap:0.35rem; justify-content:center;">
                            <a href="{{ route('student.appeals.show', $appeal->AppealID) }}" class="btn btn-outline btn-sm" style="padding:0.25rem 0.5rem; font-size:0.72rem;">
                                <i class="fas fa-eye"></i> ดู
                            </a>
                            @if($appeal->Status === 'รอตรวจสอบ')
                                <form method="POST" action="{{ route('student.appeals.cancel', $appeal->AppealID) }}" 
                                      data-confirm="คุณต้องการยกเลิกการอุทธรณ์นี้ใช่หรือไม่?"
                                      data-confirm-title="ยืนยันการยกเลิกการอุทธรณ์"
                                      data-confirm-theme="danger"
                                      data-confirm-submit="ยกเลิกการอุทธรณ์"
                                      data-confirm-icon="📋"
                                      style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-sm" style="padding:0.25rem 0.5rem; font-size:0.72rem;">
                                        <i class="fas fa-times"></i> ยกเลิก
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center; padding:2.5rem; color:var(--text-muted);">
                        <i class="fas fa-balance-scale" style="font-size:1.8rem; opacity:0.3; display:block; margin-bottom:0.75rem;"></i>
                        ยังไม่มีรายการอุทธรณ์คะแนน
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