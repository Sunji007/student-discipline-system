@extends('layouts.app')

@section('title', 'พิจารณาคำอุทธรณ์')
@section('page-title', 'พิจารณาคำอุทธรณ์')

@section('content')
<div class="page-header" style="display:flex; align-items:center; justify-content:space-between;">
    <div>
        <h2>พิจารณาคำอุทธรณ์</h2>
        <p>ตรวจสอบและพิจารณาคำขออุทธรณ์จากนักเรียน</p>
    </div>
    <div style="display:flex; gap:0.5rem;">
        @foreach(['','รอตรวจสอบ','คืนคะแนน','ยกเลิกคำร้อง'] as $s)
        <a href="{{ route('discipline.appeals.index', $s ? ['status' => $s] : []) }}"
           class="btn btn-sm {{ request('status') === $s ? 'btn-primary' : 'btn-outline' }}">
            {{ $s ?: 'ทั้งหมด' }}
        </a>
        @endforeach
    </div>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>นักเรียน</th>
                    <th>เรื่องที่โต้แย้ง</th>
                    <th>วันที่ยื่น</th>
                    <th>สถานะ</th>
                    <th style="text-align:right;">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($appeals as $appeal)
                <tr>
                    <td>
                        <strong>{{ $appeal->student->FullName }}</strong>
                        <div style="font-size:0.75rem; color:var(--text-muted);">{{ $appeal->student->Classroom }}</div>
                    </td>
                    <td>
                        <span style="font-size:0.82rem;">{{ $appeal->behaviorRecord->rule->RuleName }}</span>
                        <div style="font-size:0.75rem; color:var(--text-muted);">
                            {{ \Str::limit($appeal->Reason, 60) }}
                        </div>
                    </td>
                    <td style="font-size:0.82rem; color:var(--text-muted);">
                        @php $ad = \Carbon\Carbon::parse($appeal->created_at ?? $appeal->AppealDate); @endphp
                        {{ $ad->format('d/m/') . ($ad->year + 543) . $ad->format(' H:i') }}
                    </td>
                    <td>
                        @php
                            $displayStatus = in_array($appeal->Status, ['ยกเลิกคำร้อง', 'ยกเลิกคำร้องยื่นอุทธรณ์']) ? 'ยกเลิกคำร้อง' : $appeal->Status;
                            $sc = match($displayStatus) {
                                'รอตรวจสอบ' => 'badge-gold',
                                'คืนคะแนน'   => 'badge-green',
                                'ยกเลิกคำร้อง' => 'badge-red',
                                default       => 'badge-gray',
                            };
                        @endphp
                        <span class="badge {{ $sc }}">{{ $displayStatus }}</span>
                    </td>
                    <td style="text-align:right;">
                        <a href="{{ route('discipline.appeals.show', $appeal->AppealID) }}" class="btn btn-outline btn-sm">
                            <i class="fas fa-eye"></i> ดูรายละเอียด
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center; color:var(--text-muted); padding:2rem;">ไม่มีคำขออุทธรณ์</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding:1rem 1.25rem; border-top:1px solid #ede8e0;">
        {{ $appeals->withQueryString()->links() }}
    </div>
</div>
@endsection