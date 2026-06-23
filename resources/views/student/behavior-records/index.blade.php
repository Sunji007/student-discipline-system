@extends('layouts.app')

@section('title', 'ประวัติพฤติกรรม')
@section('page-title', 'ประวัติพฤติกรรมของฉัน')

@section('content')
<div class="page-header" style="display:flex; align-items:center; justify-content:space-between;">
    <div>
        <h2>ประวัติพฤติกรรม</h2>
        <p>คะแนนปัจจุบัน:
            <strong style="color:{{ $student->BehaviorScore >= 80 ? 'var(--green)' : ($student->BehaviorScore >= 60 ? 'var(--orange)' : 'var(--red)') }}; font-size:1.1rem;">
                {{ $student->BehaviorScore }}
            </strong> คะแนน
        </p>
    </div>
    <div style="display:flex; gap:0.5rem;">
        <a href="{{ route('student.behavior-records.index') }}" class="btn btn-sm {{ !request('type') ? 'btn-primary' : 'btn-outline' }}">ทั้งหมด</a>
        <a href="{{ route('student.behavior-records.index', ['type' => 'ตัดคะแนน']) }}" class="btn btn-sm {{ request('type') === 'ตัดคะแนน' ? 'btn-danger' : 'btn-outline' }}">ตัดคะแนน</a>
        <a href="{{ route('student.behavior-records.index', ['type' => 'เพิ่มคะแนน']) }}" class="btn btn-sm {{ request('type') === 'เพิ่มคะแนน' ? 'btn-success' : 'btn-outline' }}">เพิ่มคะแนน</a>
    </div>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>พฤติกรรม</th>
                    <th>หมวดหมู่</th>
                    <th>คะแนน</th>
                    <th>วันที่</th>
                    <th>สถานะ</th>
                    <th style="text-align:right;">คำร้อง</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $r)
                <tr>
                    <td>
                        <div style="font-weight:500;">{{ $r->rule->RuleName }}</div>
                        @if($r->Description)
                            <div style="font-size:0.75rem; color:var(--text-muted); margin-top:0.15rem;">{{ \Str::limit($r->Description, 60) }}</div>
                        @endif
                    </td>
                    <td><span class="badge badge-gray">{{ $r->rule->Category }}</span></td>
                    <td>
                        <span style="font-weight:700; color:{{ $r->rule->RuleType === 'ตัดคะแนน' ? 'var(--red)' : 'var(--green)' }}; font-size:1rem;">
                            {{ $r->rule->RuleType === 'ตัดคะแนน' ? '-' : '+' }}{{ abs($r->rule->ScoreModifier) }}
                        </span>
                    </td>
                    <td style="font-size:0.82rem; color:var(--text-muted);">
                        {{ \Carbon\Carbon::parse($r->RecordDate)->format('d/m/Y') }}
                    </td>
                    <td>
                        @php
                            $sc = match($r->Status) {
                                'รออนุมัติ' => 'badge-gold',
                                'อนุมัติแล้ว' => 'badge-green',
                                'อยู่ในระหว่างโต้แย้ง' => 'badge-orange',
                                default => 'badge-gray',
                            };
                        @endphp
                        <span class="badge {{ $sc }}">{{ $r->Status }}</span>
                    </td>
                    <td style="text-align:right;">
                        @if($r->Status === 'อนุมัติแล้ว' && !$r->appeal)
                        <a href="{{ route('student.appeals.create', ['record' => $r->RecordID]) }}"
                           class="btn btn-outline btn-sm" style="font-size:0.72rem;">
                            <i class="fas fa-balance-scale"></i> โต้แย้ง
                        </a>
                        @elseif($r->appeal)
                        <span class="badge badge-orange" style="font-size:0.7rem;">ยื่นคำร้องแล้ว</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center; padding:2rem; color:var(--text-muted);">ยังไม่มีประวัติพฤติกรรม</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding:1rem 1.25rem; border-top:1px solid #ede8e0;">
        {{ $records->withQueryString()->links() }}
    </div>
</div>
@endsection