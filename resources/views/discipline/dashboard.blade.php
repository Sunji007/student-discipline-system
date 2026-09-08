@extends('layouts.app')

@section('title', 'แดชบอร์ด — ฝ่ายปกครอง')
@section('page-title', 'แดชบอร์ดฝ่ายปกครอง')

@section('content')
<div class="page-header">
    <h2>ภาพรวมวินัยนักเรียน</h2>
    <p>เจ้าหน้าที่: {{ auth()->user()->FullName }} &nbsp;&bull;&nbsp; ข้อมูล ณ วันที่ {{ now()->locale('th')->isoFormat('D MMMM ') . (now()->year + 543) }}</p>
</div>

{{-- Stat Cards Grid --}}
<div class="stat-grid">
    <a href="{{ route('discipline.behavior-records.index', ['status' => 'รออนุมัติ']) }}" style="text-decoration:none; color:inherit;">
        <div class="stat-card gold" style="cursor:pointer; transition:transform 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
            <div class="stat-icon gold"><i class="fas fa-clock"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $stats['pending'] }}</div>
                <div class="stat-label">รอการอนุมัติ</div>
            </div>
        </div>
    </a>
    <a href="{{ route('discipline.appeals.index') }}" style="text-decoration:none; color:inherit;">
        <div class="stat-card navy" style="cursor:pointer; transition:transform 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
            <div class="stat-icon navy"><i class="fas fa-balance-scale"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $stats['appeals'] }}</div>
                <div class="stat-label">พิจารณาคำอุทธรณ์</div>
            </div>
        </div>
    </a>
    <a href="{{ route('discipline.risk-students') }}" style="text-decoration:none; color:inherit;">
        <div class="stat-card red" style="cursor:pointer; transition:transform 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
            <div class="stat-icon red"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $stats['risk'] }}</div>
                <div class="stat-label">นักเรียนกลุ่มเสี่ยง</div>
            </div>
        </div>
    </a>
    <a href="{{ route('discipline.informant-reports.index') }}" style="text-decoration:none; color:inherit;">
        <div class="stat-card green" style="cursor:pointer; transition:transform 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
            <div class="stat-icon green"><i class="fas fa-bullhorn"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $stats['today_reports'] }}</div>
                <div class="stat-label">เบาะแสวันนี้</div>
            </div>
        </div>
    </a>
</div>

{{-- Charts Section --}}
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap:1.25rem; margin-bottom:1.25rem;">
    {{-- Chart 1: Category Distribution --}}
    <div class="card">
        <div class="card-header-bar" style="border-bottom:1px solid #f0ece4; padding:0.85rem 1rem;">
            <h3 style="font-size:0.95rem; font-weight:700; color:var(--navy); margin:0;">
                <i class="fas fa-chart-pie" style="color:var(--gold); margin-right:0.4rem;"></i>สัดส่วนพฤติกรรมตามหมวดหมู่
            </h3>
        </div>
        <div style="padding:1rem; height:240px; display:flex; justify-content:center; align-items:center;">
            <canvas id="categoryDoughnutChart"></canvas>
        </div>
    </div>

    {{-- Chart 2: Rule Type Comparison --}}
    <div class="card">
        <div class="card-header-bar" style="border-bottom:1px solid #f0ece4; padding:0.85rem 1rem;">
            <h3 style="font-size:0.95rem; font-weight:700; color:var(--navy); margin:0;">
                <i class="fas fa-chart-bar" style="color:var(--navy); margin-right:0.4rem;"></i>เปรียบเทียบประเภทพฤติกรรม (ตัด vs เพิ่ม)
            </h3>
        </div>
        <div style="padding:1rem; height:240px; display:flex; justify-content:center; align-items:center;">
            <canvas id="ruleTypeBarChart"></canvas>
        </div>
    </div>
</div>

<div class="responsive-grid-dashboard">
    {{-- Recent Records --}}
    <div class="card">
        <div class="card-header-bar">
            <h3><i class="fas fa-clipboard-list" style="color:var(--gold); margin-right:0.5rem"></i>บันทึกพฤติกรรมล่าสุด</h3>
            <a href="{{ route('discipline.behavior-records.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> บันทึก
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
                                    default => 'badge-orange',
                                };
                            @endphp
                            <span class="badge {{ $sc }}">{{ $displayStatus }}</span>
                        </td>
                        <td>
                            @if($r->Status === 'รออนุมัติ')
                            <form method="POST" action="{{ route('discipline.behavior-records.approve', $r->RecordID) }}" style="display:inline;">
                                @csrf @method('PATCH')
                                <button type="button" class="btn btn-success btn-sm" onclick="confirmApprove(this)">
                                    <i class="fas fa-check"></i> อนุมัติ
                                </button>
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

    {{-- Risk Students & Behavior Criteria --}}
    <div>
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
                        @php
                            $isCrit = in_array($s->RiskStatus, ['วิกฤต', 'ทัณฑ์บน']);
                        @endphp
                        <span class="badge {{ $isCrit ? 'badge-red' : 'badge-orange' }}" style="font-size:0.65rem;">
                            {{ $isCrit ? 'ทัณฑ์บน' : 'ตักเตือน' }}
                        </span>
                    </div>
                </div>
                @empty
                <p style="text-align:center; color:var(--text-muted); padding:1.5rem 0; font-size:0.875rem;">ไม่มีนักเรียนกลุ่มเสี่ยง 🎉</p>
                @endforelse
            </div>
        </div>

        {{-- Behavior Criteria Legend --}}
        <div class="card" style="margin-top:1rem;">
            <div class="card-header-bar" style="border-bottom:1px solid #f0ece4; padding:0.85rem 1rem;">
                <h3 style="font-size:0.95rem; font-weight:700; color:var(--navy); margin:0;">
                    <i class="fas fa-info-circle" style="color:var(--gold); margin-right:0.4rem;"></i>รายละเอียดเกณฑ์คะแนนพฤติกรรม
                </h3>
            </div>
            <div style="padding:0.85rem 1rem; display:flex; flex-direction:column; gap:0.6rem;">
                <div style="display:flex; align-items:flex-start; gap:0.6rem; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:0.65rem 0.75rem;">
                    <div style="font-size:1.1rem; line-height:1.2;">🟢</div>
                    <div style="font-size:0.82rem; line-height:1.4;">
                        <strong style="color:#166534;">ระดับปกติ (Normal):</strong> 
                        <span style="color:#15803d;">คะแนนคงเหลือตั้งแต่ 80 ถึง 100 คะแนน</span>
                    </div>
                </div>
                <div style="display:flex; align-items:flex-start; gap:0.6rem; background:#fefce8; border:1px solid #fef08a; border-radius:8px; padding:0.65rem 0.75rem;">
                    <div style="font-size:1.1rem; line-height:1.2;">🟡</div>
                    <div style="font-size:0.82rem; line-height:1.4;">
                        <strong style="color:#854d0e;">ระดับตักเตือน (Warning):</strong> 
                        <span style="color:#a16207;">คะแนนคงเหลือตั้งแต่ 60 ถึง 79 คะแนน (ควรเริ่มว่ากล่าวตักเตือนและส่งข้อความแจ้งเตือนผู้ปกครอง)</span>
                    </div>
                </div>
                <div style="display:flex; align-items:flex-start; gap:0.6rem; background:#fef2f2; border:1px solid #fecaca; border-radius:8px; padding:0.65rem 0.75rem;">
                    <div style="font-size:1.1rem; line-height:1.2;">🔴</div>
                    <div style="font-size:0.82rem; line-height:1.4;">
                        <strong style="color:#991b1b;">ระดับทัณฑ์บน (Probation):</strong> 
                        <span style="color:#b91c1c;">คะแนนคงเหลือ ต่ำกว่า 60 คะแนน (ระบบจะจัดกลุ่มนี้เป็นกลุ่มความเสี่ยงสูงเพื่อเตรียมมาตรการแนะแนวหรือทำทัณฑ์บน)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Category Doughnut Chart
        const catCtx = document.getElementById('categoryDoughnutChart');
        if (catCtx) {
            const catData = @json($categoryStats);
            const labels = Object.keys(catData).length > 0 ? Object.keys(catData) : ['ไม่มีข้อมูล'];
            const values = Object.keys(catData).length > 0 ? Object.values(catData) : [1];

            new Chart(catCtx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: [
                            '#1e293b', '#C5A85C', '#ef4444', '#10b981',
                            '#3b82f6', '#f59e0b', '#8b5cf6', '#06b6d4'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: { font: { family: 'Prompt', size: 11 }, boxWidth: 12 }
                        }
                    }
                }
            });
        }

        // Rule Type Bar Chart
        const typeCtx = document.getElementById('ruleTypeBarChart');
        if (typeCtx) {
            const typeData = @json($ruleTypeStats);
            const cutCount = typeData['ตัดคะแนน'] || 0;
            const addCount = typeData['เพิ่มคะแนน'] || 0;

            new Chart(typeCtx, {
                type: 'bar',
                data: {
                    labels: ['ตัดคะแนน', 'เพิ่มคะแนน'],
                    datasets: [{
                        label: 'จำนวนรายการ',
                        data: [cutCount, addCount],
                        backgroundColor: ['#ef4444', '#10b981'],
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0, font: { family: 'Prompt' } }
                        },
                        x: {
                            ticks: { font: { family: 'Prompt', weight: 'bold' } }
                        }
                    }
                }
            });
        }
    });

    function confirmApprove(button) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'ยืนยันการอนุมัติ?',
                text: 'ระบบจะทำการบันทึกและปรับคะแนนพฤติกรรมของนักเรียนทันที',
                icon: 'success',
                showCancelButton: true,
                confirmButtonText: 'ตกลง',
                cancelButtonText: 'ยกเลิก',
                customClass: {
                    confirmButton: 'swal2-confirm btn-swal-success',
                    cancelButton: 'swal2-cancel btn-swal-cancel'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    button.closest('form').submit();
                }
            });
        } else {
            if (confirm('ยืนยันการอนุมัติรายการนี้?')) {
                button.closest('form').submit();
            }
        }
    }
</script>
@endpush
@endsection