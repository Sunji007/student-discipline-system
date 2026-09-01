@extends('layouts.app')

@section('title', 'ประวัติการละหมาด')
@section('page-title', 'ประวัติการละหมาดรายบุคคล')

@push('styles')
<style>
    :root {
        --islamic-primary: #0D5C3A;
        --islamic-emerald: #10B981;
        --islamic-gold: #C5A85C;
        --islamic-gold-pale: rgba(197, 168, 92, 0.08);
        --islamic-bg: #F4F9F6;
    }

    .calendar-card {
        background: var(--white);
        border-radius: 16px;
        border: 1px solid rgba(13, 92, 58, 0.12);
        box-shadow: 0 10px 30px rgba(13, 92, 58, 0.04);
        overflow: hidden;
    }

    .calendar-header {
        background: linear-gradient(135deg, var(--islamic-primary) 0%, #052b1b 100%);
        color: white;
        padding: 1.25rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 3px solid var(--islamic-gold);
    }

    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 6px;
        margin-top: 0.5rem;
    }

    .calendar-day-header {
        text-align: center;
        font-weight: 700;
        font-size: 0.78rem;
        color: var(--text-muted);
        padding: 0.5rem 0;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .calendar-cell {
        aspect-ratio: 1 / 1.15;
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 0.4rem;
        display: flex;
        flex-direction: column;
        background: white;
        position: relative;
        transition: all 0.2s;
    }

    .calendar-cell.today {
        border: 2px solid var(--islamic-gold);
        background-color: var(--islamic-gold-pale);
    }

    .calendar-cell.other-month {
        background: #f8fafc;
        opacity: 0.4;
        pointer-events: none;
    }

    .cell-date {
        font-family: 'Outfit', sans-serif;
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 0.35rem;
    }

    .cell-prayers {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
        margin-top: auto;
    }

    .prayer-indicator {
        font-size: 0.68rem;
        font-weight: 600;
        padding: 0.15rem 0.35rem;
        border-radius: 4px;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .prayer-indicator.prayed {
        background: rgba(16, 185, 129, 0.1);
        color: #047857;
    }

    .prayer-indicator.absent {
        background: rgba(189, 39, 67, 0.08);
        color: var(--red);
    }

    .prayer-indicator.exempt {
        background: #f1f5f9;
        color: #64748b;
        border: 1px dashed #cbd5e1;
    }

    /* Legend Box */
    .legend-box {
        display: flex;
        gap: 1.25rem;
        justify-content: center;
        flex-wrap: wrap;
        margin-top: 1.5rem;
        padding-top: 1rem;
        border-top: 1px solid var(--border);
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.8rem;
        color: var(--text-muted);
    }

    .legend-dot {
        width: 12px;
        height: 12px;
        border-radius: 4px;
    }

    .legend-dot.green { background: #10b981; }
    .legend-dot.red { background: #ef4444; }
    .legend-dot.gray { background: #cbd5e1; border: 1px dashed #94a3b8; }

    /* Grade Filter Buttons */
    .btn-grade-filter {
        padding: 0.2rem 0.6rem;
        font-size: 0.75rem;
        border-radius: 20px;
        border: 1px solid var(--border);
        background: #f8fafc;
        color: var(--text);
        cursor: pointer;
        transition: all 0.2s ease;
        font-family: 'Sarabun', sans-serif;
        font-weight: 500;
        margin-bottom: 0.25rem;
    }
    .btn-grade-filter:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }
    .btn-grade-filter.active {
        background: var(--primary, #0604ea);
        color: white;
        border-color: var(--primary, #0604ea);
        box-shadow: 0 2px 4px rgba(6, 4, 234, 0.15);
    }
    .classroom-card-item:hover {
        transform: translateY(-3px);
        border-color: var(--islamic-primary) !important;
        box-shadow: 0 8px 20px rgba(13, 92, 58, 0.15) !important;
    }
</style>
@endpush

@section('content')
<div class="prayer-container">
    <!-- Section 1: Filters (Only for Teachers/Admins/Discipline staff) -->
    @if(!$isLocked)
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header-bar">
            <h3><i class="fas fa-filter" style="color:var(--islamic-gold);"></i> เลือกข้อมูลที่ต้องการเรียกดู</h3>
        </div>
        <div class="card-body-pad">
            <form method="GET" id="calendarFilterForm" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; align-items: end;" onsubmit="mergeStudentId(event)">
                {{-- ===== Student selector column: dropdown + manual ID input ===== --}}
                <div class="form-group" style="margin:0;">
                    <label class="form-label" style="display:block; margin-bottom:0.25rem;">เลือกนักเรียน</label>
                    
                    {{-- Grade filter pills --}}
                    <div style="display:flex; gap:0.25rem; flex-wrap:wrap; margin-bottom:0.4rem;">
                        <input type="hidden" name="grade" id="gradeInput" value="{{ $selectedGrade ?? 'all' }}">
                        <button type="button" class="btn-grade-filter {{ empty($selectedGrade) || $selectedGrade === 'all' ? 'active' : '' }}" data-grade="all">ทั้งหมด</button>
                        @foreach(['ม.1','ม.2','ม.3','ม.4','ม.5','ม.6'] as $g)
                            <button type="button" class="btn-grade-filter {{ $selectedGrade === $g ? 'active' : '' }}" data-grade="{{ $g }}">{{ $g }}</button>
                        @endforeach
                    </div>

                    <select name="student_id" id="studentSelect" class="form-control" onchange="document.getElementById('studentIdInput').value=this.value">
                        <option value="">เลือกนักเรียน...</option>
                        @foreach($filterStudents as $fs)
                            @php
                                $fsGrade = '';
                                if ($fs->classroom_display) {
                                    $parts = explode('/', $fs->classroom_display);
                                    $fsGrade = trim($parts[0]);
                                }
                            @endphp
                            <option value="{{ $fs->StudentID }}" data-grade="{{ $fsGrade }}" {{ $studentId == $fs->StudentID ? 'selected' : '' }}>
                                [{{ $fs->classroom_display }}] {{ $fs->FullName }}
                            </option>
                        @endforeach
                    </select>

                    {{-- Text input for manual student ID, sits below the dropdown in the same column --}}
                    <div style="margin-top: 0.5rem;">
                        <input type="text"
                               id="studentIdInput"
                               name="student_id_typed"
                               class="form-control"
                               placeholder="หรือกรอกรหัสนักเรียน เช่น 6910101"
                               maxlength="20"
                               value="{{ $studentId }}"
                               oninput="if(this.value){ document.getElementById('studentSelect').value=''; }"
                               onblur="syncStudentId(this.value)">

                    </div>
                </div>

                <div class="form-group" style="margin:0;">
                    <label class="form-label">ห้องเรียน</label>
                    <select name="classroom" class="form-control" onchange="document.getElementById('calendarFilterForm').submit()">
                        <option value="all">ทุกห้องเรียน</option>
                        @foreach($classrooms as $c)
                            <option value="{{ $c }}" {{ ($selectedClassroom ?? '') == $c ? 'selected' : '' }}>ห้อง {{ $c }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="margin:0;">
                    <label class="form-label">เดือน</label>
                    <select name="month" class="form-control">
                        @foreach([1=>'มกราคม', 2=>'กุมภาพันธ์', 3=>'มีนาคม', 4=>'เมษายน', 5=>'พฤษภาคม', 6=>'มิถุนายน', 7=>'กรกฎาคม', 8=>'สิงหาคม', 9=>'กันยายน', 10=>'ตุลาคม', 11=>'พฤศจิกายน', 12=>'ธันวาคม'] as $num => $name)
                            <option value="{{ $num }}" {{ $month == $num ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="margin:0;">
                    <label class="form-label">ปีการศึกษา</label>
                    <select name="year" class="form-control">
                        @for($y = now()->year - 2; $y <= now()->year + 2; $y++)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y + 543 }}</option>
                        @endfor
                    </select>
                </div>

                <div style="display:flex; gap:0.5rem;">
                    <button type="submit" class="btn btn-primary" style="flex:1; justify-content:center;">
                        <i class="fas fa-search"></i> ค้นหา
                    </button>
                    <a href="{{ route('prayer.calendar') }}" class="btn btn-outline">ล้าง</a>
                </div>
            </form>
        </div>
    </div>
    @else
    <!-- Locked view (Student/Parent view) -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-body-pad" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem;">
            <div>
                <h3 style="color:var(--islamic-primary);"><i class="fas fa-user-graduate"></i> ประวัติการละหมาดของ: <strong>{{ $student->FullName ?? '-' }}</strong></h3>
                <p style="color:var(--text-muted); font-size:0.85rem; margin-top:0.2rem;">รหัสนักเรียน: {{ $student->StudentID ?? '-' }} | ชั้นเรียน: {{ $student->classroom_display ?? '-' }}</p>
            </div>
            
            <form method="GET" style="display:flex; gap: 0.5rem; align-items: center;">
                <select name="month" class="form-control" style="width: 130px;">
                    @foreach([1=>'มกราคม', 2=>'กุมภาพันธ์', 3=>'มีนาคม', 4=>'เมษายน', 5=>'พฤษภาคม', 6=>'มิถุนายน', 7=>'กรกฎาคม', 8=>'สิงหาคม', 9=>'กันยายน', 10=>'ตุลาคม', 11=>'พฤศจิกายน', 12=>'ธันวาคม'] as $num => $name)
                        <option value="{{ $num }}" {{ $month == $num ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>

                <select name="year" class="form-control" style="width: 100px;">
                    @for($y = now()->year - 2; $y <= now()->year + 2; $y++)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y + 543 }}</option>
                    @endfor
                </select>
                <button type="submit" class="btn btn-primary btn-sm" style="display:inline-flex; align-items:center; gap:0.35rem; padding:0.45rem 0.9rem; font-weight:600;">
                    <i class="fas fa-search"></i> ค้นหา
                </button>
            </form>
        </div>
    </div>
    @endif

    <!-- Section 2: Monthly Calendar Grid -->
    @if($student)
    
    <!-- Real-time Prayer Status Summary Card -->
    @if(isset($monthlyStatus))
    <div class="card" style="margin-bottom: 1.5rem; border-left: 4px solid {{ $monthlyStatus['status'] === 'pass' ? '#10b981' : ($monthlyStatus['status'] === 'corrected' ? '#3b82f6' : '#ef4444') }};">
        <div class="card-body-pad" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1.5rem;">
            <div style="flex:1; min-width:280px;">
                <h4 style="margin:0; font-size:1.05rem; color:var(--islamic-primary); display:flex; align-items:center; gap:0.5rem;">
                    <i class="fas fa-chart-line"></i> สถิติและเกณฑ์การละหมาดประจำเดือนนี้
                </h4>
                <div style="display:flex; gap:1rem; margin-top:0.75rem; flex-wrap:wrap;">
                    <div style="background:#f8fafc; padding:0.4rem 0.65rem; border-radius:8px; border:1px solid var(--border);">
                        <span style="font-size:0.75rem; color:var(--text-muted);">ละหมาดแล้ว:</span>
                        <strong style="color:var(--green); font-size:0.9rem;">{{ $monthlyStatus['prayed_count'] }} ครั้ง</strong>
                    </div>
                    <div style="background:#f8fafc; padding:0.4rem 0.65rem; border-radius:8px; border:1px solid var(--border);">
                        <span style="font-size:0.75rem; color:var(--text-muted);">ขาดละหมาด:</span>
                        <strong style="color:var(--red); font-size:0.9rem;">{{ $monthlyStatus['absent_count'] }} ครั้ง</strong>
                    </div>
                    <div style="background:#f8fafc; padding:0.4rem 0.65rem; border-radius:8px; border:1px solid var(--border);">
                        <span style="font-size:0.75rem; color:var(--text-muted);">ละหมาดไม่ได้:</span>
                        <strong style="color:#64748b; font-size:0.9rem;">{{ $monthlyStatus['exempt_count'] }} ครั้ง</strong>
                    </div>
                </div>
            </div>

            <div style="display:flex; align-items:center; gap:1.5rem; flex-wrap:wrap;">
                {{-- Percentage --}}
                <div style="text-align:center;">
                    <div style="font-size:2rem; font-weight:800; font-family:'Outfit', sans-serif; color: {{ $monthlyStatus['percentage'] >= 80 ? 'var(--green)' : ($monthlyStatus['percentage'] >= 60 ? 'var(--orange)' : 'var(--red)') }}; line-height:1;">
                        {{ $monthlyStatus['percentage'] }}%
                    </div>
                    <div style="font-size:0.7rem; color:var(--text-muted); margin-top:0.2rem;">ร้อยละการละหมาด</div>
                </div>

                {{-- Status Badge & Help text --}}
                <div style="text-align:right; min-width: 180px;">
                    <div>
                        @if($monthlyStatus['status'] === 'pass')
                            <span class="badge badge-green" style="font-size:0.85rem; padding:0.3rem 0.75rem;"><i class="fas fa-check-circle"></i> ผ่านเกณฑ์การละหมาด</span>
                        @elseif($monthlyStatus['status'] === 'corrected')
                            <span class="badge badge-primary" style="font-size:0.85rem; padding:0.3rem 0.75rem; background: rgba(59, 130, 246, 0.1); color: #1d4ed8; border: 1px solid rgba(59, 130, 246, 0.2);"><i class="fas fa-user-check"></i> แก้ละหมาดแล้ว (ผ่าน)</span>
                        @else
                            <span class="badge badge-red" style="font-size:0.85rem; padding:0.3rem 0.75rem;"><i class="fas fa-times-circle"></i> ไม่ผ่านเกณฑ์การละหมาด</span>
                        @endif
                    </div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-top:0.25rem;">
                        @if($monthlyStatus['status'] === 'pass')
                            เข้าละหมาดครบถ้วนตามเกณฑ์ (> 80%)
                        @elseif($monthlyStatus['status'] === 'corrected')
                            ได้รับการบันทึกแก้ละหมาดจากฝ่ายปกครองแล้ว
                        @else
                            ขาดละหมาดเกินเกณฑ์ (ติดต่อฝ่ายปกครองเพื่อแก้)
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="calendar-card">
        <div class="calendar-header">
            <h3 style="display:flex; align-items:center; gap:0.5rem;">
                <i class="fas fa-calendar-check" style="color:var(--islamic-gold);"></i>
                {{ $calendarDays['month_name'] }}
            </h3>
        </div>

        <div style="padding: 1.25rem;">
            <!-- Day Names headers -->
            <div class="calendar-grid">
                @foreach(['อาทิตย์','จันทร์','อังคาร','พุธ','พฤหัสบดี','ศุกร์','เสาร์'] as $d)
                    <div class="calendar-day-header">{{ $d }}</div>
                @endforeach
            </div>

            <!-- Calendar Days -->
            <div class="calendar-grid">
                <!-- Padding before the 1st of month -->
                @for($i = 0; $i < $calendarDays['first_day_of_week']; $i++)
                    <div class="calendar-cell other-month"></div>
                @endfor

                <!-- Month Days -->
                @for($day = 1; $day <= $calendarDays['days_in_month']; $day++)
                    @php
                        $dayDate = Carbon\Carbon::create($year, $month, $day);
                        $isToday = $dayDate->isToday();
                        $isWeekend = $dayDate->isWeekend();
                        $isPast = $dayDate->isPast();

                        // Get records for Zuhur (ซุฮรี/เที่ยง) and Asr (อัศรี/บ่าย) for this day
                        $dayRecords = $calendarDays['records']->get($day) ?? collect();
                        $zuhurRecord = $dayRecords->first(fn($r) => in_array($r->Period, ['เที่ยง', 'ซุฮรี']));
                        $asrRecord = $dayRecords->first(fn($r) => in_array($r->Period, ['บ่าย', 'อัศรี']));
                    @endphp
                    <div class="calendar-cell @if($isToday) today @endif">
                        <span class="cell-date">{{ $day }}</span>

                        <div class="cell-prayers">
                            <!-- Noon Zuhur Check -->
                            @if($zuhurRecord)
                                @if($zuhurRecord->Status === 'ละหมาด')
                                    <div class="prayer-indicator prayed">🟢 ซุฮรี</div>
                                @else
                                    <div class="prayer-indicator exempt">⚪ ซุฮรี</div>
                                @endif
                            @elseif($isPast && !$isWeekend)
                                <div class="prayer-indicator absent">🔴 ซุฮรี</div>
                            @endif

                            <!-- Afternoon Asr Check -->
                            @if($asrRecord)
                                @if($asrRecord->Status === 'ละหมาด')
                                    <div class="prayer-indicator prayed">🟢 อัศรี</div>
                                @else
                                    <div class="prayer-indicator exempt">⚪ อัศรี</div>
                                @endif
                            @elseif($isPast && !$isWeekend)
                                <div class="prayer-indicator absent">🔴 อัศรี</div>
                            @endif
                        </div>
                    </div>
                @endfor
            </div>

            <!-- Legend Info -->
            <div class="legend-box">
                <div class="legend-item">
                    <span class="legend-dot green"></span>
                    <span>🟢 ละหมาดแล้ว</span>
                </div>
                <div class="legend-item">
                    <span class="legend-dot red"></span>
                    <span>🔴 ไม่ละหมาด (ขาด) *เฉพาะวันเรียน</span>
                </div>
                <div class="legend-item">
                    <span class="legend-dot gray"></span>
                    <span>⚪ ละหมาดไม่ได้ (ประจำเดือน/ยกเว้น)</span>
                </div>
            </div>
        </div>
    </div>
    @elseif($overviewStats)
    <!-- Section: Overview by Grade and Classroom -->
    <div style="margin-bottom: 1.5rem;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; margin-bottom:1rem; gap:0.5rem;">
            <h3 style="color:var(--islamic-primary); margin:0; display:flex; align-items:center; gap:0.5rem;">
                <i class="fas fa-chart-pie" style="color:var(--islamic-gold);"></i> 
                ภาพรวมสรุปผลละหมาด{{ $selectedGrade && $selectedGrade !== 'all' ? ' ระดับชั้น ' . $selectedGrade : ' ทุกระดับชั้น' }} 
                ประจำเดือน {{ $overviewStats['month_name'] }}
            </h3>
        </div>

        <!-- 4 Summary Stats Cards -->
        <div class="stat-grid" style="margin-bottom: 1.5rem;">
            <div class="stat-card islamic-primary">
                <div class="stat-icon islamic-primary"><i class="fas fa-users"></i></div>
                <div class="stat-info">
                    <div class="stat-value">{{ $overviewStats['total_students'] }}</div>
                    <div class="stat-label">นักเรียนทั้งหมด</div>
                </div>
            </div>

            <div class="stat-card green">
                <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
                <div class="stat-info">
                    <div class="stat-value">{{ $overviewStats['pass_count'] }}</div>
                    <div class="stat-label">ผ่านเกณฑ์ (>= 80%)</div>
                </div>
            </div>

            <div class="stat-card red">
                <div class="stat-icon red"><i class="fas fa-times-circle"></i></div>
                <div class="stat-info">
                    <div class="stat-value">{{ $overviewStats['fail_count'] }}</div>
                    <div class="stat-label">ไม่ผ่านเกณฑ์ (< 80%)</div>
                </div>
            </div>

            <div class="stat-card islamic-gold">
                <div class="stat-icon islamic-gold"><i class="fas fa-percentage"></i></div>
                <div class="stat-info">
                    <div class="stat-value">{{ $overviewStats['avg_percent'] }}%</div>
                    <div class="stat-label">เปอร์เซ็นต์ละหมาดเฉลี่ย</div>
                </div>
            </div>
        </div>

        <!-- Classroom Breakdown Grid -->
        <div class="card" style="margin-bottom:1.5rem;">
            <div class="card-header-bar">
                <h3><i class="fas fa-door-open" style="color:var(--islamic-gold);"></i> สรุปผลแยกตามห้องเรียน (กดเลือกห้องเพื่อดูรายชื่อนักเรียน)</h3>
            </div>
            <div class="card-body-pad">
                <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap:1rem;">
                    @forelse($classroomSummaries as $cs)
                    @php
                        $isSelected = ($selectedClassroom === $cs['raw_classroom'] || $selectedClassroom === $cs['classroom']);
                    @endphp
                    <a href="{{ route('prayer.calendar', ['classroom' => $cs['raw_classroom'], 'grade' => $cs['grade'], 'month' => $month, 'year' => $year]) }}#student-table" 
                       class="classroom-card-item"
                       style="display:block; text-decoration:none; color:inherit; background:{{ $isSelected ? '#f0fdf4' : '#fff' }}; border:2px solid {{ $isSelected ? 'var(--islamic-primary)' : '#e2e8f0' }}; border-radius:12px; padding:1rem; box-shadow:{{ $isSelected ? '0 4px 12px rgba(13,92,58,0.12)' : '0 2px 4px rgba(0,0,0,0.02)' }}; transition:all 0.25s ease; cursor:pointer;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
                            <strong style="font-size:1.05rem; color:var(--islamic-primary);"><i class="fas fa-chalkboard-teacher" style="color:var(--islamic-gold); margin-right:0.3rem;"></i> ห้อง {{ $cs['classroom'] }}</strong>
                            <span class="badge" style="background:{{ $isSelected ? 'var(--islamic-primary)' : '#f1f5f9' }}; color:{{ $isSelected ? '#fff' : '#475569' }}; font-size:0.75rem;">
                                {{ $isSelected ? '✓ เลือกอยู่ (' . $cs['total'] . ' คน)' : $cs['total'] . ' คน' }}
                            </span>
                        </div>
                        <div style="display:flex; justify-content:space-between; align-items:baseline; margin-bottom:0.5rem;">
                            <span style="font-size:0.8rem; color:var(--text-muted);">เฉลี่ยละหมาด:</span>
                            <span style="font-weight:700; font-size:1.1rem; color:{{ $cs['avg_percent'] >= 80 ? 'var(--green)' : 'var(--red)' }};">{{ $cs['avg_percent'] }}%</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; align-items:center; font-size:0.78rem;">
                            <div style="display:flex; gap:0.5rem;">
                                <span style="color:var(--green);"><i class="fas fa-check-circle"></i> ผ่าน {{ $cs['pass'] }}</span>
                                <span style="color:var(--red);"><i class="fas fa-times-circle"></i> ไม่ผ่าน {{ $cs['fail'] }}</span>
                            </div>
                            <span style="font-size:0.75rem; color:var(--islamic-primary); font-weight:600;">
                                ดูรายชื่อ <i class="fas fa-arrow-right"></i>
                            </span>
                        </div>
                    </a>
                    @empty
                    <div style="color:var(--text-muted); text-align:center; grid-column:1/-1;">ไม่พบข้อมูลห้องเรียน</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Student List Table for this Grade/Classroom -->
        <div class="card" id="student-table">
            <div class="card-header-bar" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.5rem;">
                <h3><i class="fas fa-users-cog" style="color:var(--islamic-gold);"></i> รายชื่อนักเรียนและผลการละหมาดรายบุคคล {{ $selectedClassroom && $selectedClassroom !== 'all' ? '(ห้อง ' . $selectedClassroom . ')' : '' }}</h3>
                @if($selectedClassroom && $selectedClassroom !== 'all')
                    <a href="{{ route('prayer.calendar', ['grade' => $selectedGrade, 'month' => $month, 'year' => $year]) }}" class="btn btn-outline btn-sm" style="font-size:0.78rem;">
                        <i class="fas fa-undo"></i> แสดงทุกห้องเรียน
                    </a>
                @endif
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>รหัสนักเรียน</th>
                            <th>ชื่อ - สกุล</th>
                            <th>ชั้น/ห้อง</th>
                            <th>เพศ</th>
                            <th style="text-align:center;">เปอร์เซ็นต์ละหมาด</th>
                            <th style="text-align:center;">สถานะ</th>
                            <th style="text-align:center;">ปฏิทิน</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($studentOverviewList as $item)
                        @php
                            $st = $item['student'];
                            $sd = $item['status_data'];
                        @endphp
                        <tr>
                            <td><strong>{{ $st->StudentID }}</strong></td>
                            <td>{{ $st->FullName }}</td>
                            <td>{{ $st->classroom_display ?? '-' }}</td>
                            <td>{{ $st->Gender ?? '-' }}</td>
                            <td style="text-align:center;">
                                <span style="font-weight:700; color:{{ $sd['percentage'] >= 80 ? 'var(--green)' : 'var(--red)' }};">
                                    {{ $sd['percentage'] }}%
                                </span>
                            </td>
                            <td style="text-align:center;">
                                @if($item['is_pass'])
                                    <span class="badge badge-green" style="font-size:0.75rem;"><i class="fas fa-check-circle"></i> ผ่านเกณฑ์</span>
                                @else
                                    <span class="badge badge-red" style="font-size:0.75rem;"><i class="fas fa-times-circle"></i> ไม่ผ่านเกณฑ์</span>
                                @endif
                            </td>
                            <td style="text-align:center;">
                                <a href="{{ route('prayer.calendar', ['student_id' => $st->StudentID, 'month' => $month, 'year' => $year]) }}" class="btn btn-outline btn-sm" style="font-size:0.75rem; padding:0.25rem 0.5rem;">
                                    <i class="fas fa-calendar-alt"></i> ดูปฏิทิน
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" style="text-align:center; color:var(--text-muted); padding:2rem;">ไม่พบข้อมูลนักเรียน</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @else
    <div class="card" style="text-align:center; padding:3rem; color:var(--text-muted);">
        <i class="fas fa-calendar-times" style="font-size:3rem; color:var(--islamic-primary); opacity:0.3; margin-bottom:1rem; display:block;"></i>
        กรุณาเลือกนักเรียนเพื่อดูข้อมูลปฏิทินการละหมาดประจำเดือน
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var select = document.getElementById('studentSelect');
    var pills = document.querySelectorAll('.btn-grade-filter');
    
    if (select && pills.length > 0) {
        var defaultOpt = select.querySelector('option[value=""]');
        var originalOptions = Array.from(select.querySelectorAll('option')).filter(function(opt) {
            return opt.value !== '';
        });
        
        var activeGrade = 'all';
        
        function applyGradeFilter() {
            var currentValue = select.value;
            select.innerHTML = '';
            
            if (defaultOpt) {
                select.appendChild(defaultOpt.cloneNode(true));
            }
            
            var matchedOptions = originalOptions.filter(function(opt) {
                var optGrade = opt.getAttribute('data-grade');
                return activeGrade === 'all' || optGrade === activeGrade;
            });
            
            matchedOptions.forEach(function(opt) {
                var newOpt = opt.cloneNode(true);
                if (newOpt.value === currentValue) {
                    newOpt.selected = true;
                }
                select.appendChild(newOpt);
            });
            
            // If the previously selected value is no longer available in filtered list, reset select value
            var valueStillExists = matchedOptions.some(function(opt) {
                return opt.value === currentValue;
            });
            if (!valueStillExists && currentValue !== '') {
                select.value = '';
            }
        }
        
        pills.forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                pills.forEach(function(p) { p.classList.remove('active'); });
                this.classList.add('active');
                activeGrade = this.getAttribute('data-grade');
                applyGradeFilter();

                var gradeInput = document.getElementById('gradeInput');
                if (gradeInput) gradeInput.value = activeGrade;

                var sel = document.getElementById('studentSelect');
                var typed = document.getElementById('studentIdInput');
                if ((!sel || !sel.value) && (!typed || !typed.value)) {
                    var form = document.getElementById('calendarFilterForm');
                    if (form) form.submit();
                }
            });
        });
        
        // Pre-select active pill if student is pre-selected
        var currentVal = select.value;
        if (currentVal) {
            var selectedOpt = originalOptions.find(function(opt) {
                return opt.value === currentVal;
            });
            if (selectedOpt) {
                var initialGrade = selectedOpt.getAttribute('data-grade');
                if (initialGrade) {
                    pills.forEach(function(p) {
                        if (p.getAttribute('data-grade') === initialGrade) {
                            pills.forEach(function(x) { x.classList.remove('active'); });
                            p.classList.add('active');
                            activeGrade = initialGrade;
                        }
                    });
                    applyGradeFilter();
                }
            }
        }
        // Expose student options list globally for search validation
        window.originalStudentOptions = originalOptions;
    }
});

function syncStudentId(val) {
    // When user types a student ID, clear the dropdown
    if (val.trim()) {
        var sel = document.getElementById('studentSelect');
        if (sel) sel.value = '';
    }
}

function mergeStudentId(e) {
    var typed = document.getElementById('studentIdInput');
    var sel   = document.getElementById('studentSelect');
    if (!typed || !sel) return;

    var typedVal = typed.value.trim();
    var selVal   = sel.value.trim();

    // Decide which value to use
    var finalId = typedVal || selVal;

    if (!finalId) {
        e.preventDefault();
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                iconHtml: '<i class="fas fa-user-graduate" style="color:var(--navy); font-size:2.8rem;"></i>',
                title: 'กรุณาเลือกนักเรียน',
                text: 'กรุณาเลือกรายชื่อนักเรียน หรือกรอกรหัสนักเรียนที่ต้องการเรียกดูข้อมูล',
                confirmButtonText: 'ตกลง',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-primary btn-swal-confirm'
                }
            });
        } else {
            alert('กรุณาเลือกนักเรียน');
        }
        return;
    }

    // Validate if student ID exists
    var found = false;
    if (window.originalStudentOptions) {
        found = window.originalStudentOptions.some(function(opt) {
            return opt.value === finalId;
        });
    } else {
        var options = sel.querySelectorAll('option');
        for (var i = 0; i < options.length; i++) {
            if (options[i].value === finalId) {
                found = true;
                break;
            }
        }
    }

    if (!found) {
        e.preventDefault();
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'ไม่พบรหัสนักเรียน',
                text: 'ไม่พบรหัสนักเรียน "' + finalId + '" นี้ในระบบ กรุณาตรวจสอบและกรอกข้อมูลใหม่อีกครั้ง',
                confirmButtonText: 'ตกลง',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-primary btn-swal-confirm'
                }
            });
        } else {
            alert('ไม่พบรหัสนักเรียนนี้ในระบบ');
        }
        return;
    }

    // Set dropdown value and submit
    sel.value = finalId;
    typed.name = ''; 
}
</script>
@endpush
