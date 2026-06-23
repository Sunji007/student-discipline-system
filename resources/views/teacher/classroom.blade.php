@extends('layouts.app')

@section('title', 'รายชื่อนักเรียนในห้อง')
@section('page-title', 'รายชื่อนักเรียน')

@section('content')
<div class="page-header">
    <h2>ห้องที่ปรึกษา: {{ $classroom ?? '-' }}</h2>
    <p>นักเรียนทั้งหมด {{ $students->count() }} คน</p>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ลำดับ</th>
                    <th>รหัสนักเรียน</th>
                    <th>ชื่อ-นามสกุล</th>
                    <th style="text-align:center;">คะแนนพฤติกรรม</th>
                    <th>สถานะ</th>
                    <th>การเข้าแถววันนี้</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $i => $student)
                @php $attend = $todayAttendance[$student->StudentID] ?? null; @endphp
                <tr>
                    <td style="color:var(--text-muted);">{{ $i + 1 }}</td>
                    <td><code style="font-size:0.8rem; background:#f0ece4; padding:0.1rem 0.35rem; border-radius:2px;">{{ $student->StudentID }}</code></td>
                    <td><strong style="font-size:0.875rem;">{{ $student->FullName }}</strong></td>
                    <td style="text-align:center;">
                        <strong style="color:{{ $student->BehaviorScore >= 80 ? 'var(--green)' : ($student->BehaviorScore >= 60 ? 'var(--orange)' : 'var(--red)') }}">
                            {{ $student->BehaviorScore }}
                        </strong>
                    </td>
                    <td>
                        <span class="badge {{ match($student->RiskStatus) { 'ปกติ' => 'badge-green', 'เฝ้าระวัง' => 'badge-orange', 'วิกฤต' => 'badge-red', default => 'badge-gray' } }}">
                            {{ $student->RiskStatus }}
                        </span>
                    </td>
                    <td>
                        @if($attend)
                        <span class="badge {{ $attend === 'มา' ? 'badge-green' : ($attend === 'สาย' ? 'badge-orange' : 'badge-red') }}">{{ $attend }}</span>
                        @else
                        <span style="font-size:0.75rem; color:var(--text-muted);">ยังไม่ได้เช็ค</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
