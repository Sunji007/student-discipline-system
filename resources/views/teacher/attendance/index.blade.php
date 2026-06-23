@extends('layouts.app')

@section('title', 'เช็คชื่อเข้าแถว')
@section('page-title', 'เช็คชื่อเข้าแถว')

@section('content')
<div class="page-header">
    <h2>เช็คชื่อนักเรียนเข้าแถว</h2>
    <p>ห้องที่ปรึกษา: <strong>{{ auth()->user()->teacher->AdvisoryRoom ?? 'ไม่ระบุ' }}</strong></p>
</div>

<div class="card">
    <div class="card-header-bar">
        <h3>เลือกวันที่</h3>
    </div>
    <div class="card-body-pad">
        <form method="GET" style="display:flex; gap:0.75rem; align-items:flex-end;">
            <div class="form-group" style="margin:0;">
                <label class="form-label">วันที่</label>
                <input type="date" name="date" class="form-control" value="{{ $date }}">
            </div>
            <button type="submit" class="btn btn-primary">ดูข้อมูล</button>
        </form>
    </div>
</div>

<div class="card" style="margin-top:1rem;">
    <div class="card-header-bar">
        <h3>รายชื่อนักเรียน — {{ \Carbon\Carbon::parse($date)->locale('th')->isoFormat('D MMMM YYYY') }}</h3>
        <div style="font-size:0.82rem; color:var(--text-muted);">{{ $students->count() }} คน</div>
    </div>

    @if($students->isEmpty())
        <div class="card-body-pad" style="text-align:center; color:var(--text-muted); padding:2rem;">
            ไม่มีนักเรียนในห้องที่ปรึกษา
        </div>
    @else
    <form method="POST" action="{{ route('teacher.attendance.store') }}">
        @csrf
        <input type="hidden" name="date" value="{{ $date }}">

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ลำดับ</th>
                        <th>รหัสนักเรียน</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th style="text-align:center;">มา</th>
                        <th style="text-align:center;">สาย</th>
                        <th style="text-align:center;">ขาด</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $i => $student)
                    @php $currentStatus = $attendanceMap[$student->StudentID] ?? 'มา'; @endphp
                    <tr>
                        <td style="color:var(--text-muted);">{{ $i + 1 }}</td>
                        <td><code style="font-size:0.8rem; background:#f0ece4; padding:0.1rem 0.4rem; border-radius:2px;">{{ $student->StudentID }}</code></td>
                        <td><strong>{{ $student->FullName }}</strong></td>
                        @foreach(['มา','สาย','ขาด'] as $status)
                        @php
                            $color = match($status) { 'มา' => 'var(--green)', 'สาย' => 'var(--orange)', 'ขาด' => 'var(--red)' };
                        @endphp
                        <td style="text-align:center;">
                            <label style="cursor:pointer; display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:50%; transition:background 0.15s;"
                                   title="{{ $status }}">
                                <input type="radio"
                                       name="attendance[{{ $student->StudentID }}]"
                                       value="{{ $status }}"
                                       style="display:none;"
                                       class="attendance-radio"
                                       data-color="{{ $color }}"
                                       {{ $currentStatus === $status ? 'checked' : '' }}>
                                <span class="radio-dot" style="width:18px; height:18px; border-radius:50%; border:2px solid {{ $color }};
                                    background: {{ $currentStatus === $status ? $color : 'transparent' }};
                                    display:block; transition:background 0.15s;"></span>
                            </label>
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="padding:1rem 1.25rem; border-top:1px solid #ede8e0; display:flex; gap:0.75rem; align-items:center;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> บันทึกการเข้าแถว
            </button>
            <span style="font-size:0.8rem; color:var(--text-muted);">
                ระบบจะบันทึกทับข้อมูลเดิมของวันนั้นอัตโนมัติ
            </span>
        </div>
    </form>
    @endif
</div>

@push('scripts')
<script>
    // Interactive radio buttons
    document.querySelectorAll('.attendance-radio').forEach(radio => {
        radio.addEventListener('change', function () {
            const row = this.closest('tr');
            row.querySelectorAll('.radio-dot').forEach(dot => {
                dot.style.background = 'transparent';
            });
            const dot = this.closest('label').querySelector('.radio-dot');
            dot.style.background = this.dataset.color;
        });
    });
</script>
@endpush
@endsection