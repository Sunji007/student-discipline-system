@extends('layouts.app')

@section('title', 'แดชบอร์ดฝ่ายปกครอง')
@section('page-title', 'แดชบอร์ดฝ่ายปกครอง')

@section('content')
<div class="page-header">
    <h2>ภาพรวมวินัยนักเรียน</h2>
    <p>ข้อมูล ณ วันที่ {{ now()->locale('th')->isoFormat('D MMMM YYYY') }}</p>
</div>

<div class="stat-grid">
    <div class="stat-card gold">
        <div class="stat-icon gold"><i class="fas fa-clock"></i></div>
        <div class="stat-info">
            <div class="stat-value">{{ $stats['pending'] }}</div>
            <div class="stat-label">รอการอนุมัติ</div>
        </div>
    </div>
    <div class="stat-card navy">
        <div class="stat-icon navy"><i class="fas fa-balance-scale"></i></div>
        <div class="stat-info">
            <div class="stat-value">{{ $stats['appeals'] }}</div>
            <div class="stat-label">คำร้องโต้แย้งใหม่</div>
        </div>
    </div>
    <div class="stat-card red">
        <div class="stat-icon red"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="stat-info">
            <div class="stat-value">{{ $stats['risk'] }}</div>
            <div class="stat-label">นักเรียนกลุ่มเสี่ยง</div>
        </div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon green"><i class="fas fa-user-times"></i></div>
        <div class="stat-info">
            <div class="stat-value">{{ $stats['today_absent'] }}</div>
            <div class="stat-label">ขาดเรียนวันนี้</div>
        </div>
    </div>
</div>

<div class="responsive-grid-dashboard">
    {{-- Recent Records --}}
    <div class="card">
        <div class="card-header-bar">
            <h3><i class="fas fa-clipboard-list" style="color:var(--gold); margin-right:0.5rem"></i>บันทึกพฤติกรรมล่าสุด</h3>
            <a href="{{ route('discipline.behavior-records.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> บันทึกใหม่
            </a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>นักเรียน</th>
                        <th>พฤติกรรม</th>
                        <th>วันที่</th>
                        <th>สถานะ</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentRecords as $r)
                    <tr>
                        <td>
                            <strong>{{ $r->student->FullName }}</strong>
                            <div style="font-size:0.75rem; color:var(--text-muted);">{{ $r->student->Classroom }}</div>
                        </td>
                        <td>
                            <span class="badge {{ $r->rule->RuleType === 'ตัดคะแนน' ? 'badge-red' : 'badge-green' }}">
                                {{ $r->rule->RuleType === 'ตัดคะแนน' ? '-' : '+' }}{{ abs($r->rule->ScoreModifier) }}
                            </span>
                            <span style="font-size:0.82rem; margin-left:0.35rem;">{{ $r->rule->RuleName }}</span>
                        </td>
                        <td style="font-size:0.82rem; color:var(--text-muted);">
                            {{ \Carbon\Carbon::parse($r->RecordDate)->format('d/m/Y') }}
                        </td>
                        <td>
                            @php
                                $sc = match($r->Status) {
                                    'รออนุมัติ' => 'badge-gold',
                                    'อนุมัติแล้ว' => 'badge-green',
                                    default => 'badge-orange',
                                };
                            @endphp
                            <span class="badge {{ $sc }}">{{ $r->Status }}</span>
                        </td>
                        <td>
                            @if($r->Status === 'รออนุมัติ')
                            <form method="POST" action="{{ route('discipline.behavior-records.approve', $r->RecordID) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-success btn-sm">อนุมัติ</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align:center; color:var(--text-muted); padding:2rem;">ยังไม่มีบันทึก</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Risk Students --}}
    <div class="card">
        <div class="card-header-bar">
            <h3><i class="fas fa-exclamation-triangle" style="color:var(--red); margin-right:0.5rem"></i>นักเรียนเสี่ยงสูงสุด</h3>
            <a href="{{ route('discipline.risk-students') }}" class="btn btn-outline btn-sm">ดูทั้งหมด</a>
        </div>
        <div class="card-body-pad" style="padding:0.75rem;">
            @forelse($riskStudents as $s)
            <div style="display:flex; align-items:center; justify-content:space-between; padding:0.6rem 0.5rem; border-bottom:1px solid #f0ece4;">
                <div>
                    <div style="font-size:0.875rem; font-weight:500;">{{ $s->FullName }}</div>
                    <div style="font-size:0.75rem; color:var(--text-muted);">{{ $s->Classroom }}</div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:1.1rem; font-weight:700; color:{{ $s->BehaviorScore < 60 ? 'var(--red)' : 'var(--orange)' }}">
                        {{ $s->BehaviorScore }}
                    </div>
                    <span class="badge {{ $s->RiskStatus === 'วิกฤต' ? 'badge-red' : 'badge-orange' }}" style="font-size:0.65rem;">
                        {{ $s->RiskStatus }}
                    </span>
                </div>
            </div>
            @empty
            <p style="text-align:center; color:var(--text-muted); padding:1.5rem 0; font-size:0.875rem;">ไม่มีนักเรียนกลุ่มเสี่ยง 🎉</p>
            @endforelse
        </div>
    </div>
</div>
@endsection