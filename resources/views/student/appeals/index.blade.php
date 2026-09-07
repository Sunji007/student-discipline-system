@extends('layouts.app')

@section('title', 'อุทธรณ์คะแนน')
@section('page-title', 'อุทธรณ์คะแนนของฉัน')

@section('content')
<div class="page-header" style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:1rem;">
    <div>
        <h2>อุทธรณ์คะแนน</h2>
        <p>ติดตามสถานะและผลการพิจารณาคำขออุทธรณ์ที่ยื่นไว้</p>
    </div>
    <div style="display:flex; align-items:center; gap:0.65rem; flex-wrap:wrap;">
        <button type="button" class="btn btn-outline" id="openAppealStatsModalBtn" style="font-size:0.85rem; font-weight:600; padding:0.5rem 1.15rem; border-radius:10px; display:inline-flex; align-items:center; gap:0.5rem; background:#fff; box-shadow:var(--shadow-sm); border:1.5px solid var(--border); color:var(--navy); cursor:pointer; transition:all 0.2s ease;">
            <i class="fas fa-chart-pie" style="color:var(--primary); font-size:1rem;"></i> สรุปสถิติ (ร้อยละ %)
        </button>
        <a href="{{ route('student.appeals.create') }}" class="btn btn-primary" style="white-space:nowrap; font-size:0.85rem; padding:0.5rem 1.15rem; border-radius:10px; display:inline-flex; align-items:center; gap:0.4rem;">
            <i class="fas fa-plus"></i> เพิ่ม
        </a>
    </div>
</div>

{{-- Summary Cards (Clickable to open Stats Breakdown Modal) --}}
<div class="stat-grid" style="margin-bottom:1.25rem;">
    {{-- Card 1: คืนคะแนน --}}
    <div class="stat-card green stat-card-clickable" data-open-modal="true" role="button" tabindex="0" title="คลิกดูสรุปสถิติการคืนคะแนน (ร้อยละ %)">
        <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
        <div class="stat-info">
            <div style="display:flex; align-items:baseline; gap:0.45rem;">
                <div class="stat-value">{{ $stats['restored'] }}</div>
                <span style="font-size:0.92rem; font-weight:700; color:var(--green);">
                    ({{ $stats['restored_rate'] }}%)
                </span>
            </div>
            <div class="stat-label">คืนคะแนน (อนุมัติ)</div>
            <div style="margin-top:0.45rem; display:flex; gap:0.35rem; flex-wrap:wrap; align-items:center;">
                <span style="font-size:0.75rem; font-weight:700; color:#065f46; background:#d1fae5; padding:0.25rem 0.65rem; border-radius:6px; display:inline-flex; align-items:center; gap:0.35rem; border:1px solid #a7f3d0;">
                    <i class="fas fa-chart-pie"></i> คลิกดูสถิติ
                </span>
                @if($stats['total_restored_points'] > 0)
                <span style="font-size:0.72rem; font-weight:700; color:#15803d; background:#ecfdf5; padding:0.25rem 0.55rem; border-radius:6px; border:1px solid #bbf7d0;">
                    +{{ $stats['total_restored_points'] }} คะแนน
                </span>
                @endif
            </div>
        </div>
    </div>

    {{-- Card 2: รอพิจารณา --}}
    <div class="stat-card gold stat-card-clickable" data-open-modal="true" role="button" tabindex="0" title="คลิกดูสรุปสถิติคำร้องรอพิจารณา (ร้อยละ %)">
        <div class="stat-icon gold"><i class="fas fa-clock"></i></div>
        <div class="stat-info">
            <div style="display:flex; align-items:baseline; gap:0.45rem;">
                <div class="stat-value">{{ $stats['pending'] }}</div>
                <span style="font-size:0.92rem; font-weight:700; color:var(--orange);">
                    ({{ $stats['pending_rate'] }}%)
                </span>
            </div>
            <div class="stat-label">รอพิจารณา (รอตรวจสอบ)</div>
            <div style="margin-top:0.45rem;">
                <span style="font-size:0.75rem; font-weight:700; color:#78350f; background:#fef3c7; padding:0.25rem 0.65rem; border-radius:6px; display:inline-flex; align-items:center; gap:0.35rem; border:1px solid #fde68a;">
                    <i class="fas fa-chart-pie"></i> คลิกดูสถิติ
                </span>
            </div>
        </div>
    </div>

    {{-- Card 3: ยกเลิกคำร้อง --}}
    <div class="stat-card red stat-card-clickable" data-open-modal="true" role="button" tabindex="0" title="คลิกดูสรุปสถิติคำร้องที่ยกเลิก/ไม่อนุมัติ (ร้อยละ %)">
        <div class="stat-icon red"><i class="fas fa-times-circle"></i></div>
        <div class="stat-info">
            <div style="display:flex; align-items:baseline; gap:0.45rem;">
                <div class="stat-value">{{ $stats['cancelled'] }}</div>
                <span style="font-size:0.92rem; font-weight:700; color:var(--red);">
                    ({{ $stats['cancelled_rate'] }}%)
                </span>
            </div>
            <div class="stat-label">ยกเลิกคำร้อง</div>
            <div style="margin-top:0.45rem;">
                <span style="font-size:0.75rem; font-weight:700; color:#7f1d1d; background:#fee2e2; padding:0.25rem 0.65rem; border-radius:6px; display:inline-flex; align-items:center; gap:0.35rem; border:1px solid #fecaca;">
                    <i class="fas fa-chart-pie"></i> คลิกดูสถิติ
                </span>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header-bar" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.75rem; padding:1rem 1.25rem; border-bottom:1px solid var(--border);">
        <div style="font-size:0.95rem; font-weight:700; color:var(--navy); display:flex; align-items:center; gap:0.5rem;">
            <i class="fas fa-list" style="color:var(--primary);"></i> รายการคำขออุทธรณ์
            @if(request('status'))
                <span style="font-size:0.8rem; font-weight:600; color:var(--text-muted);">
                    (กรอง: {{ request('status') }})
                </span>
            @endif
        </div>

        {{-- Standardized Filter Dropdown --}}
        <div class="filter-dropdown-wrap">
            <label class="filter-dropdown-label" for="appealStatusFilter">
                <i class="fas fa-filter"></i> ตัวเลือกสถานะ:
            </label>
            <select id="appealStatusFilter" class="filter-dropdown-select" onchange="if(this.value){ window.location.href = this.value; }">
                <option value="{{ route('student.appeals.index') }}" {{ !request('status') ? 'selected' : '' }}>
                    📋 ทั้งหมด ({{ $stats['total'] }})
                </option>
                <option value="{{ route('student.appeals.index', ['status' => 'คืนคะแนน']) }}" {{ request('status') === 'คืนคะแนน' ? 'selected' : '' }}>
                    ✅ คืนคะแนน ({{ $stats['restored'] }})
                </option>
                <option value="{{ route('student.appeals.index', ['status' => 'รอพิจารณา']) }}" {{ in_array(request('status'), ['รอพิจารณา', 'รอตรวจสอบ']) ? 'selected' : '' }}>
                    ⏳ รอพิจารณา ({{ $stats['pending'] }})
                </option>
                <option value="{{ route('student.appeals.index', ['status' => 'ยกเลิกคำร้อง']) }}" {{ in_array(request('status'), ['ยกเลิกคำร้อง', 'ยกเลิกคำร้องยื่นอุทธรณ์']) ? 'selected' : '' }}>
                    ❌ ยกเลิกคำร้อง ({{ $stats['cancelled'] }})
                </option>
            </select>
        </div>
    </div>

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
                                'รอตรวจสอบ', 'รอพิจารณา' => 'badge-gold',
                                'คืนคะแนน'               => 'badge-green',
                                'ยกเลิกคำร้อง'            => 'badge-red',
                                default                  => 'badge-gray',
                            };
                            $icon = match($displayStatus) {
                                'รอตรวจสอบ', 'รอพิจารณา' => 'fa-hourglass-half',
                                'คืนคะแนน'               => 'fa-check-circle',
                                'ยกเลิกคำร้อง'            => 'fa-times-circle',
                                default                  => 'fa-circle',
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
                            @if(in_array($appeal->Status, ['รอตรวจสอบ', 'รอพิจารณา']))
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
                        ยังไม่มีรายการอุทธรณ์คะแนนในหมวดหมู่นี้
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

{{-- Appeal Stats Modal --}}
<div id="appealStatsModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); z-index:9999; align-items:center; justify-content:center; padding:1rem; backdrop-filter:blur(3px);">
    <div style="background:#fff; border-radius:16px; width:100%; max-width:680px; max-height:90vh; display:flex; flex-direction:column; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); overflow:hidden; animation:modalPop 0.2s cubic-bezier(0.16, 1, 0.3, 1);">
        <!-- Header -->
        <div style="padding:1.25rem 1.5rem; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; background:linear-gradient(to right, #f8fafc, #fff);">
            <div style="display:flex; align-items:center; gap:0.75rem;">
                <div style="width:40px; height:40px; border-radius:10px; background:rgba(201,168,76,0.15); display:flex; align-items:center; justify-content:center; color:var(--primary);">
                    <i class="fas fa-chart-pie" style="font-size:1.2rem;"></i>
                </div>
                <div>
                    <h3 style="margin:0; font-size:1.1rem; font-weight:700; color:var(--navy);">สรุปสถิติการอุทธรณ์คะแนน</h3>
                    <p style="margin:0; font-size:0.8rem; color:var(--text-muted);">ภาพรวมและสถิติรายเดือนแยกตามสถานะ (ร้อยละ %)</p>
                </div>
            </div>
            <button type="button" class="close-appeal-modal" style="border:none; background:transparent; font-size:1.35rem; color:var(--text-muted); cursor:padding:0.25rem; border-radius:6px; display:flex; align-items:center; justify-content:center;" title="ปิดหน้าต่าง">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Body -->
        <div style="padding:1.5rem; overflow-y:auto;">
            <!-- Overall Highlights Grid -->
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(160px, 1fr)); gap:1rem; margin-bottom:1.5rem;">
                <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:12px; padding:1rem; text-align:center;">
                    <div style="font-size:0.8rem; font-weight:700; color:#166534; margin-bottom:0.35rem;">
                        <i class="fas fa-check-circle"></i> คืนคะแนน (อนุมัติ)
                    </div>
                    <div style="font-size:1.6rem; font-weight:800; color:#15803d; line-height:1.2;">
                        {{ $stats['restored'] }} <span style="font-size:0.95rem; font-weight:600;">รายการ</span>
                    </div>
                    <div style="font-size:0.85rem; font-weight:700; color:#16a34a; margin-top:0.25rem;">
                        {{ $stats['restored_rate'] }}%
                    </div>
                    @if($stats['total_restored_points'] > 0)
                    <div style="font-size:0.75rem; color:#15803d; margin-top:0.35rem; font-weight:600;">
                        ได้คืนรวม +{{ $stats['total_restored_points'] }} คะแนน
                    </div>
                    @endif
                </div>

                <div style="background:#fefce8; border:1px solid #fef08a; border-radius:12px; padding:1rem; text-align:center;">
                    <div style="font-size:0.8rem; font-weight:700; color:#854d0e; margin-bottom:0.35rem;">
                        <i class="fas fa-clock"></i> รอพิจารณา
                    </div>
                    <div style="font-size:1.6rem; font-weight:800; color:#b45309; line-height:1.2;">
                        {{ $stats['pending'] }} <span style="font-size:0.95rem; font-weight:600;">รายการ</span>
                    </div>
                    <div style="font-size:0.85rem; font-weight:700; color:#d97706; margin-top:0.25rem;">
                        {{ $stats['pending_rate'] }}%
                    </div>
                    <div style="font-size:0.75rem; color:#854d0e; margin-top:0.35rem; font-weight:600;">
                        อยู่ระหว่างตรวจสอบ
                    </div>
                </div>

                <div style="background:#fef2f2; border:1px solid #fecaca; border-radius:12px; padding:1rem; text-align:center;">
                    <div style="font-size:0.8rem; font-weight:700; color:#991b1b; margin-bottom:0.35rem;">
                        <i class="fas fa-times-circle"></i> ยกเลิกคำร้อง
                    </div>
                    <div style="font-size:1.6rem; font-weight:800; color:#b91c1c; line-height:1.2;">
                        {{ $stats['cancelled'] }} <span style="font-size:0.95rem; font-weight:600;">รายการ</span>
                    </div>
                    <div style="font-size:0.85rem; font-weight:700; color:#dc2626; margin-top:0.25rem;">
                        {{ $stats['cancelled_rate'] }}%
                    </div>
                    <div style="font-size:0.75rem; color:#991b1b; margin-top:0.35rem; font-weight:600;">
                        ยกเลิกหรือไม่อนุมัติ
                    </div>
                </div>
            </div>

            <div style="font-size:0.88rem; font-weight:700; color:var(--navy); margin-bottom:0.75rem; display:flex; align-items:center; gap:0.4rem;">
                <i class="fas fa-calendar-alt" style="color:var(--primary);"></i> รายละเอียดแยกตามแต่ละเดือน:
            </div>

            <!-- Table of Monthly Breakdown -->
            <div style="border:1px solid var(--border); border-radius:12px; overflow:hidden; box-shadow:var(--shadow-sm);">
                <table style="width:100%; border-collapse:collapse; font-size:0.85rem; text-align:left;">
                    <thead>
                        <tr style="background:#f8fafc; border-bottom:1px solid var(--border); color:var(--text-muted); font-size:0.78rem; text-transform:uppercase; letter-spacing:0.04em;">
                            <th style="padding:0.75rem 1rem; font-weight:700;">เดือน</th>
                            <th style="padding:0.75rem 0.5rem; text-align:center; font-weight:700; color:var(--green);">คืนคะแนน</th>
                            <th style="padding:0.75rem 0.5rem; text-align:center; font-weight:700; color:var(--orange);">รอพิจารณา</th>
                            <th style="padding:0.75rem 0.5rem; text-align:center; font-weight:700; color:var(--red);">ยกเลิกคำร้อง</th>
                            <th style="padding:0.75rem 0.75rem; text-align:center; font-weight:700;">รวม</th>
                            <th style="padding:0.75rem 1rem; font-weight:700; min-width:130px;">ร้อยละคืนคะแนน (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($monthlyStats as $ms)
                        @php
                            $rateColor = $ms['restored_rate'] >= 80 ? 'var(--green)' : ($ms['restored_rate'] >= 50 ? 'var(--orange)' : 'var(--red)');
                            $barBg = $ms['restored_rate'] >= 80 ? 'linear-gradient(90deg, #10b981, #34d399)' : ($ms['restored_rate'] >= 50 ? 'linear-gradient(90deg, #f59e0b, #fbbf24)' : 'linear-gradient(90deg, #ef4444, #f87171)');
                        @endphp
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <td style="padding:0.85rem 1rem; font-weight:600; color:var(--navy);">
                                {{ $ms['month_name'] }}
                            </td>
                            <td style="padding:0.85rem 0.5rem; text-align:center; font-weight:700; color:var(--green);">
                                {{ $ms['restored'] }}
                                @if($ms['restored_points'] > 0)
                                    <div style="font-size:0.7rem; color:#16a34a; font-weight:600;">(+{{ $ms['restored_points'] }})</div>
                                @endif
                            </td>
                            <td style="padding:0.85rem 0.5rem; text-align:center; font-weight:{{ $ms['pending'] > 0 ? '700' : '400' }}; color:{{ $ms['pending'] > 0 ? 'var(--orange)' : 'var(--text-muted)' }};">
                                {{ $ms['pending'] }}
                            </td>
                            <td style="padding:0.85rem 0.5rem; text-align:center; font-weight:{{ $ms['cancelled'] > 0 ? '700' : '400' }}; color:{{ $ms['cancelled'] > 0 ? 'var(--red)' : 'var(--text-muted)' }};">
                                {{ $ms['cancelled'] }}
                            </td>
                            <td style="padding:0.85rem 0.75rem; text-align:center; font-weight:600; color:var(--text);">
                                {{ $ms['total'] }} รายการ
                            </td>
                            <td style="padding:0.85rem 1rem;">
                                <div style="display:flex; align-items:center; gap:0.6rem;">
                                    <span style="font-weight:700; color:{{ $rateColor }}; min-width:40px; font-size:0.85rem;">
                                        {{ $ms['restored_rate'] }}%
                                    </span>
                                    <div style="flex:1; height:6px; background:#e2e8f0; border-radius:3px; overflow:hidden;">
                                        <div style="height:100%; width:{{ $ms['restored_rate'] }}%; background:{{ $barBg }}; border-radius:3px; transition:width 0.6s ease;"></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="text-align:center; padding:2rem; color:var(--text-muted);">
                                ไม่พบข้อมูลสถิติการยื่นอุทธรณ์
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

        <!-- Footer -->
        <div style="padding:1rem 1.5rem; background:#f8fafc; border-top:1px solid var(--border); display:flex; justify-content:flex-end;">
            <button type="button" class="btn btn-outline close-appeal-modal" style="font-size:0.85rem; padding:0.45rem 1.25rem; border-radius:8px; cursor:pointer;">
                ปิดหน้าต่าง
            </button>
        </div>
    </div>
</div>

<style>
@keyframes modalPop {
    from { opacity: 0; transform: scale(0.94) translateY(10px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}
.stat-card-clickable {
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.stat-card-clickable:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-md, 0 4px 12px rgba(0,0,0,0.08));
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('appealStatsModal');
    const openBtn = document.getElementById('openAppealStatsModalBtn');
    const closeBtns = document.querySelectorAll('.close-appeal-modal');
    const statCards = document.querySelectorAll('.stat-card-clickable[data-open-modal="true"]');

    function openModal() {
        if (modal) {
            modal.style.display = 'flex';
        }
    }

    function closeModal() {
        if (modal) {
            modal.style.display = 'none';
        }
    }

    if (openBtn) {
        openBtn.addEventListener('click', openModal);
    }

    closeBtns.forEach(btn => {
        btn.addEventListener('click', closeModal);
    });

    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal();
            }
        });
    }

    statCards.forEach(card => {
        card.addEventListener('click', function() {
            openModal();
        });
        card.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                openModal();
            }
        });
    });
});
</script>
@endsection