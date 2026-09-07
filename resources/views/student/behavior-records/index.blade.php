@extends('layouts.app')

@section('title', 'ประวัติพฤติกรรม')
@section('page-title', 'ประวัติพฤติกรรมของฉัน')

@section('content')
<div class="page-header" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem;">
    <div>
        <h2>ประวัติพฤติกรรม</h2>
        <p>คะแนนปัจจุบัน:
            <strong style="color:{{ $student->BehaviorScore >= 80 ? 'var(--green)' : ($student->BehaviorScore >= 60 ? 'var(--orange)' : 'var(--red)') }}; font-size:1.1rem;">
                {{ $student->BehaviorScore }}
            </strong> คะแนน
        </p>
    </div>
    <div class="filter-tab-bar">
        <a href="{{ route('student.behavior-records.index') }}" 
           class="filter-tab-item {{ !request('type') ? 'active' : '' }}">
            <i class="fas fa-list"></i> ทั้งหมด
        </a>
        <a href="{{ route('student.behavior-records.index', ['type' => 'ตัดคะแนน']) }}" 
           class="filter-tab-item active-danger {{ request('type') === 'ตัดคะแนน' ? 'active' : '' }}">
            <i class="fas fa-minus-circle"></i> ตัดคะแนน
        </a>
        <a href="{{ route('student.behavior-records.index', ['type' => 'เพิ่มคะแนน']) }}" 
           class="filter-tab-item active-success {{ request('type') === 'เพิ่มคะแนน' ? 'active' : '' }}">
            <i class="fas fa-plus-circle"></i> เพิ่มคะแนน
        </a>
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
                        <div style="font-weight:500;">
                            {{ $r->rule->RuleName }}
                            @if($r->Photo)
                                @php
                                    $firstPhoto = $r->Photo;
                                    if (str_starts_with($r->Photo, '[') && str_ends_with($r->Photo, ']')) {
                                        $decoded = json_decode($r->Photo, true);
                                        $firstPhoto = !empty($decoded) ? $decoded[0] : '';
                                    }
                                @endphp
                                @if($firstPhoto)
                                    <a href="{{ asset('storage/' . $firstPhoto) }}" target="_blank" 
                                       style="display:inline-flex; align-items:center; gap:0.25rem; font-size:0.72rem; color:var(--orange, #f97316); margin-left:0.5rem; text-decoration:none; font-weight:600; background:rgba(249,115,22,0.08); padding:0.15rem 0.45rem; border-radius:4px; transition: all 0.2s;"
                                       onmouseover="this.style.background='rgba(249,115,22,0.15)'"
                                       onmouseout="this.style.background='rgba(249,115,22,0.08)'">
                                        <i class="fas fa-image"></i> ดูรูปภาพหลักฐาน
                                    </a>
                                @endif
                            @endif
                        </div>
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
                        {{ \Carbon\Carbon::parse($r->RecordDate)->format('d/m/') . (\Carbon\Carbon::parse($r->RecordDate)->year + 543) }}
                    </td>
                    <td>
                        @php
                            $displayStatus = match($r->Status) {
                                'อนุมัติแล้ว', 'อนุมัติ' => 'อนุมัติ',
                                default => $r->Status,
                            };
                            $sc = match($displayStatus) {
                                'รออนุมัติ' => 'badge-gold',
                                'อนุมัติ' => 'badge-green',
                                'อยู่ในระหว่างยื่นอุทธรณ์' => 'badge-orange',
                                default => 'badge-gray',
                            };
                        @endphp
                        <span class="badge {{ $sc }}">{{ $displayStatus }}</span>
                    </td>
                    <td style="text-align:right;">
                        @if(in_array($r->Status, ['อนุมัติ', 'อนุมัติแล้ว']) && !$r->appeal)
                        <a href="{{ route('student.appeals.create', ['record' => $r->RecordID]) }}"
                           class="btn btn-outline btn-sm" style="font-size:0.72rem;">
                            <i class="fas fa-balance-scale"></i> อุทธรณ์
                        </a>
                        @elseif($r->appeal)
                        <span class="badge badge-orange" style="font-size:0.7rem;">ยื่นเรื่องอุทธรณ์แล้ว</span>
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