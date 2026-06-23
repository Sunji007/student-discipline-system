@extends('layouts.app')

@section('title', 'ประวัติการบันทึกพฤติกรรม')
@section('page-title', 'ประวัติการบันทึกของฉัน')

@section('content')
<div class="page-header" style="display:flex; justify-content:space-between; align-items:center;">
    <div>
        <h2>รายการที่ฉันบันทึก</h2>
        <p>คุณบันทึกไปทั้งหมด {{ $records->total() }} รายการ</p>
    </div>
    <a href="{{ route('teacher.behavior-records.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus"></i> เพิ่มบันทึกใหม่
    </a>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>วันที่</th>
                    <th>นักเรียน</th>
                    <th>พฤติกรรม</th>
                    <th>คะแนน</th>
                    <th>สถานะ</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $r)
                <tr>
                    <td style="font-size:0.82rem; color:var(--text-muted);">
                        {{ \Carbon\Carbon::parse($r->RecordDate)->format('d/m/Y') }}
                    </td>
                    <td>
                        <div style="font-weight:500;">{{ $r->student->FullName ?? 'N/A' }}</div>
                        <div style="font-size:0.75rem; color:var(--text-muted);">ห้อง {{ $r->student->Classroom ?? '-' }}</div>
                    </td>
                    <td>
                        <div style="font-size:0.875rem;">{{ $r->rule->RuleName ?? '-' }}</div>
                    </td>
                    <td>
                        <strong style="color:{{ optional($r->rule)->RuleType === 'ตัดคะแนน' ? 'var(--red)' : 'var(--green)' }}">
                            {{ optional($r->rule)->RuleType === 'ตัดคะแนน' ? '-' : '+' }}{{ abs(optional($r->rule)->ScoreModifier ?? 0) }}
                        </strong>
                    </td>
                    <td>
                        @php
                            $sc = match($r->Status) {
                                'รออนุมัติ' => 'badge-gold', 'อนุมัติแล้ว' => 'badge-green',
                                'ปฏิเสธ' => 'badge-red', 'อยู่ในระหว่างโต้แย้ง' => 'badge-orange',
                                default => 'badge-gray',
                            };
                        @endphp
                        <span class="badge {{ $sc }}" style="font-size:0.7rem;">{{ $r->Status }}</span>
                    </td>
                    <td style="text-align:right;">
                        <a href="{{ route('teacher.behavior-records.show', $r->RecordID) }}" class="btn btn-outline btn-sm">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center; padding:3rem; color:var(--text-muted);">ยังไม่มีประวัติการบันทึก</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding:1rem 1.25rem; border-top:1px solid #ede8e0;">
        {{ $records->links() }}
    </div>
</div>
@endsection
