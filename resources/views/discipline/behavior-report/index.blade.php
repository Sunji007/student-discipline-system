@extends('layouts.app')

@section('title', 'รายงานสรุปพฤติกรรมนักเรียน')
@section('page-title', 'รายงานสรุปพฤติกรรมนักเรียน')

@push('styles')
<style>
    .report-filters {
        background: var(--white);
        padding: 1.5rem;
        border-radius: 12px;
        border: 1px solid var(--border);
        box-shadow: var(--shadow-sm);
        margin-bottom: 2rem;
    }
    .filter-actions {
        display: flex;
        align-items: flex-end;
        gap: 0.75rem;
        margin-bottom: 1.25rem;
    }
    .chart-grid {
        display: grid;
        grid-template-columns: 320px 1fr;
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    .chart-card {
        background: var(--white);
        border-radius: 12px;
        border: 1px solid var(--border);
        padding: 1.5rem;
        box-shadow: var(--shadow-md);
    }
    .chart-card h3 {
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 1.25rem;
        color: #0f0e34;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .chart-container {
        position: relative;
        height: 260px;
        width: 100%;
    }
    .section-title-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.25rem;
        margin-top: 2rem;
    }
    .section-title-bar h3 {
        font-size: 1.2rem;
        font-weight: 700;
        color: #0f0e34;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .data-card-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    .avatar-cell {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        object-fit: cover;
        background: #f0f0f8;
        border: 1px solid var(--border);
    }
    .student-link {
        color: var(--primary);
        text-decoration: none;
        font-weight: 600;
        transition: var(--transition);
    }
    .student-link:hover {
        color: var(--primary-light);
        text-decoration: underline;
    }
</style>
@endpush

@section('content')
<div class="page-header" style="display:flex; justify-content:space-between; align-items:flex-start;">
    <div>
        <h2>รายงานสรุปพฤติกรรมและสถิติวินัยนักเรียน</h2>
        <p>ฝ่ายปกครองโรงเรียนศิริราษฎร์สามัคคี</p>
    </div>
    <div style="display:flex; gap:0.75rem;">
        <a href="{{ route('discipline.behavior-report.export', array_merge(request()->all(), ['start_date' => $startDate, 'end_date' => $endDate])) }}" target="_blank" class="btn btn-outline">
            <i class="fas fa-print"></i> พิมพ์รายงาน / PDF
        </a>
        <a href="{{ route('discipline.behavior-report.export', array_merge(request()->all(), ['excel' => 1, 'start_date' => $startDate, 'end_date' => $endDate])) }}" class="btn btn-primary">
            <i class="fas fa-file-excel"></i> ส่งออก Excel (CSV)
        </a>
    </div>
</div>

{{-- Filters Form --}}
<div class="report-filters">
    <form method="GET" action="{{ route('discipline.behavior-report') }}">
        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="start_date">ตั้งแต่วันที่</label>
                <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate }}">
            </div>
            <div class="form-group">
                <label class="form-label" for="end_date">ถึงวันที่</label>
                <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate }}">
            </div>
            <div class="form-group">
                <label class="form-label" for="grade">ระดับชั้น</label>
                <select name="grade" id="grade" class="form-control">
                    <option value="">ทั้งหมด</option>
                    @foreach($grades as $g)
                        <option value="{{ $g }}" {{ $grade == $g ? 'selected' : '' }}>{{ $g }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="classroom">ห้องเรียน</label>
                <select name="classroom" id="classroom" class="form-control">
                    <option value="">ทั้งหมด</option>
                    @foreach($classrooms as $c)
                        <option value="{{ $c }}" {{ $classroom == $c ? 'selected' : '' }}>{{ $c }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div style="display:flex; justify-content:flex-end; gap:0.5rem; margin-top:0.5rem;">
            <a href="{{ route('discipline.behavior-report') }}" class="btn btn-outline btn-sm">
                <i class="fas fa-undo"></i> ล้างตัวกรอง
            </a>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-filter"></i> ค้นหา
            </button>
        </div>
    </form>
</div>

{{-- Statistics summary --}}
<div class="stat-grid">
    <div class="stat-card navy">
        <div class="stat-icon navy"><i class="fas fa-clipboard-list"></i></div>
        <div class="stat-info">
            <div class="stat-value">{{ $totalRecords }}</div>
            <div class="stat-label">บันทึกที่อนุมัติแล้ว</div>
        </div>
    </div>
    <div class="stat-card red">
        <div class="stat-icon red"><i class="fas fa-minus-circle"></i></div>
        <div class="stat-info">
            <div class="stat-value" style="color:var(--red);">{{ $demeritCount }} / -{{ $demeritPoints }}</div>
            <div class="stat-label">ครั้งที่ตัดคะแนน (คะแนน)</div>
        </div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon green"><i class="fas fa-plus-circle"></i></div>
        <div class="stat-info">
            <div class="stat-value" style="color:var(--green);">{{ $meritCount }} / +{{ $meritPoints }}</div>
            <div class="stat-label">ครั้งที่เพิ่มคะแนน (คะแนน)</div>
        </div>
    </div>
    <div class="stat-card primary">
        <div class="stat-icon primary"><i class="fas fa-graduation-cap"></i></div>
        <div class="stat-info">
            <div class="stat-value">{{ $avgScore }}</div>
            <div class="stat-label">คะแนนความพฤติกรรมเฉลี่ย</div>
        </div>
    </div>
</div>

{{-- Visual Charts --}}
<div class="chart-grid">
    <div class="chart-card">
        <h3><i class="fas fa-pie-chart" style="color:var(--red)"></i> สัดส่วนความเสี่ยงนักเรียน</h3>
        <div class="chart-container">
            <canvas id="riskStatusChart"></canvas>
        </div>
        <div style="margin-top: 1rem; font-size: 0.75rem; color: var(--text-muted); text-align: center; line-height: 1.4;">
            วิกฤต: {{ $riskCritical }} คน | เฝ้าระวัง: {{ $riskWatch }} คน | ปกติ: {{ $riskNormal }} คน
        </div>
    </div>
    
    <div class="chart-card">
        <h3><i class="fas fa-bar-chart" style="color:var(--primary)"></i> สถิติพฤติกรรมตามหมวดหมู่</h3>
        <div class="chart-container">
            <canvas id="categoryChart"></canvas>
        </div>
    </div>
</div>

{{-- Classroom Summaries Table --}}
<div class="section-title-bar">
    <h3><i class="fas fa-chalkboard" style="color:var(--primary);"></i> สรุปผลสัมฤทธิ์รายห้องเรียน</h3>
</div>
<div class="card" style="margin-bottom: 2rem;">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ห้องเรียน</th>
                    <th>ระดับชั้น</th>
                    <th class="text-center">จำนวนนักเรียน</th>
                    <th class="text-center">คะแนนพฤติกรรมเฉลี่ย</th>
                    <th class="text-center" style="color:var(--green);">จำนวนบันทึกความดี</th>
                    <th class="text-center" style="color:var(--red);">จำนวนบันทึกความผิด</th>
                    <th>แถบเปรียบเทียบคะแนนเฉลี่ย</th>
                </tr>
            </thead>
            <tbody>
                @forelse($classroomStats as $class)
                <tr>
                    <td><strong>ห้อง {{ $class->Classroom }}</strong></td>
                    <td>{{ $class->GradeLevel }}</td>
                    <td class="text-center">{{ $class->student_count }} คน</td>
                    <td class="text-center" style="font-weight:700; color:{{ $class->avg_score >= 80 ? 'var(--green)' : ($class->avg_score >= 60 ? 'var(--orange)' : 'var(--red)') }}">
                        {{ $class->avg_score }}
                    </td>
                    <td class="text-center" style="color:var(--green); font-weight: 600;">{{ $class->merit_count }} ครั้ง</td>
                    <td class="text-center" style="color:var(--red); font-weight: 600;">{{ $class->demerit_count }} ครั้ง</td>
                    <td>
                        <div class="score-bar" style="width: 120px;">
                            <div class="score-bar-fill {{ $class->avg_score < 60 ? 'low' : ($class->avg_score < 80 ? 'medium' : '') }}" style="width: {{ $class->avg_score }}%;"></div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center; padding:2rem; color:var(--text-muted);">ไม่มีข้อมูลสรุปรายห้อง</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Rule Violations Frequency --}}
<div class="section-title-bar">
    <h3><i class="fas fa-clipboard-list" style="color:var(--red);"></i> กฎพฤติกรรมที่พบมากที่สุด (10 ลำดับแรก)</h3>
</div>
<div class="card" style="margin-bottom: 2rem;">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>กฎพฤติกรรม</th>
                    <th>ประเภท</th>
                    <th>หมวดหมู่</th>
                    <th class="text-center">ความถี่การเกิดพฤติกรรม</th>
                    <th class="text-center">ผลกระทบคะแนนรวม</th>
                </tr>
            </thead>
            <tbody>
                @forelse($frequentRules as $rule)
                <tr>
                    <td><strong>{{ $rule->RuleName }}</strong></td>
                    <td>
                        <span class="badge {{ $rule->RuleType === 'ตัดคะแนน' ? 'badge-red' : 'badge-green' }}">
                            {{ $rule->RuleType }}
                        </span>
                    </td>
                    <td><span class="badge badge-gray">{{ $rule->Category }}</span></td>
                    <td class="text-center" style="font-weight: 700;">{{ $rule->record_count }} ครั้ง</td>
                    <td class="text-center" style="font-weight: 700; color: {{ $rule->RuleType === 'ตัดคะแนน' ? 'var(--red)' : 'var(--green)' }}">
                        {{ $rule->RuleType === 'ตัดคะแนน' ? '-' : '+' }}{{ $rule->total_points }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center; padding:2rem; color:var(--text-muted);">ไม่มีบันทึกข้อมูลพฤติกรรม</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Low & High Student Rankings --}}
<div class="data-card-grid">
    {{-- Lowest Scores --}}
    <div class="card">
        <div class="card-header-bar">
            <h3 style="color:var(--red);"><i class="fas fa-arrow-down-long"></i> นักเรียนที่มีคะแนนพฤติกรรมต่ำสุด (10 ลำดับ)</h3>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>รูป</th>
                        <th>ชื่อ-สกุล</th>
                        <th>ระดับชั้น/ห้อง</th>
                        <th class="text-center">คะแนน</th>
                        <th class="text-center">สถานะความเสี่ยง</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lowestScoreStudents as $s)
                    <tr>
                        <td class="text-center">
                            @if($s->Photo)
                                <img src="{{ asset('storage/' . $s->Photo) }}" class="avatar-cell" alt="{{ $s->FullName }}">
                            @else
                                <div class="avatar-cell" style="display:inline-flex; align-items:center; justify-content:center; color:var(--text-muted); font-size:0.8rem;">
                                    <i class="fas fa-user"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $s->FullName }}</strong>
                            <div style="font-size:0.75rem; color:var(--text-muted);">รหัส: {{ $s->StudentID }}</div>
                        </td>
                        <td>
                            {{ $s->classroom_display }}
                        </td>
                        <td class="text-center" style="font-weight: 700; color:var(--red);">{{ $s->BehaviorScore }}</td>
                        <td class="text-center">
                            <span class="badge {{ $s->RiskStatus === 'วิกฤต' ? 'badge-red' : 'badge-orange' }}">
                                {{ $s->RiskStatus }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align:center; padding:1.5rem; color:var(--text-muted);">ไม่มีข้อมูลนักเรียน</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Highest Scores --}}
    <div class="card">
        <div class="card-header-bar">
            <h3 style="color:var(--green);"><i class="fas fa-arrow-up-long"></i> นักเรียนที่มีคะแนนพฤติกรรมสูงสุด (10 ลำดับ)</h3>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>รูป</th>
                        <th>ชื่อ-สกุล</th>
                        <th>ระดับชั้น/ห้อง</th>
                        <th class="text-center">คะแนน</th>
                        <th class="text-center">สถานะความเสี่ยง</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($highestScoreStudents as $s)
                    <tr>
                        <td class="text-center">
                            @if($s->Photo)
                                <img src="{{ asset('storage/' . $s->Photo) }}" class="avatar-cell" alt="{{ $s->FullName }}">
                            @else
                                <div class="avatar-cell" style="display:inline-flex; align-items:center; justify-content:center; color:var(--text-muted); font-size:0.8rem;">
                                    <i class="fas fa-user"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $s->FullName }}</strong>
                            <div style="font-size:0.75rem; color:var(--text-muted);">รหัส: {{ $s->StudentID }}</div>
                        </td>
                        <td>
                            {{ $s->classroom_display }}
                        </td>
                        <td class="text-center" style="font-weight: 700; color:var(--green);">{{ $s->BehaviorScore }}</td>
                        <td class="text-center">
                            <span class="badge badge-green">
                                {{ $s->RiskStatus }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align:center; padding:1.5rem; color:var(--text-muted);">ไม่มีข้อมูลนักเรียน</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // === Chart 1: Risk Status Distribution ===
    const riskCtx = document.getElementById('riskStatusChart').getContext('2d');
    
    // Normal, Watchlist, Critical
    const normalCount = {{ $riskNormal }};
    const watchCount = {{ $riskWatch }};
    const criticalCount = {{ $riskCritical }};
    
    new Chart(riskCtx, {
        type: 'doughnut',
        data: {
            labels: ['ปกติ', 'เฝ้าระวัง', 'วิกฤต'],
            datasets: [{
                data: [normalCount, watchCount, criticalCount],
                backgroundColor: [
                    '#16a34a', // Green
                    '#F08618', // Orange
                    '#BD2743'  // Red
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        padding: 15,
                        font: {
                            family: 'Sarabun',
                            size: 11
                        }
                    }
                }
            },
            cutout: '65%'
        }
    });

    // === Chart 2: Category Breakdown ===
    const catCtx = document.getElementById('categoryChart').getContext('2d');
    
    const rawCategories = @json($categoryCounts);
    const labels = Object.keys(rawCategories);
    const data = Object.values(rawCategories);

    new Chart(catCtx, {
        type: 'bar',
        data: {
            labels: labels.length > 0 ? labels : ['ไม่มีข้อมูล'],
            datasets: [{
                label: 'จำนวนครั้งที่เกิดขึ้น',
                data: data.length > 0 ? data : [0],
                backgroundColor: 'rgba(6, 4, 234, 0.75)',
                borderColor: 'var(--primary)',
                borderWidth: 1,
                borderRadius: 6,
                barPercentage: 0.5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        font: {
                            family: 'Sarabun',
                            size: 11
                        }
                    },
                    grid: {
                        color: '#f0f0f8'
                    }
                },
                x: {
                    ticks: {
                        font: {
                            family: 'Sarabun',
                            size: 11
                        }
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
});
</script>
@endpush
