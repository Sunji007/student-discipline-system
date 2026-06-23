@extends('layouts.app')

@section('title', 'ปฏิทินการละหมาด')
@section('page-title', 'ปฏิทินการละหมาดประจำเดือน')

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
                    <label class="form-label">เลือกนักเรียน</label>
                    <select name="student_id" id="studentSelect" class="form-control" onchange="document.getElementById('studentIdInput').value=''">
                        <option value="">เลือกนักเรียน...</option>
                        @foreach($filterStudents as $fs)
                            <option value="{{ $fs->StudentID }}" {{ $studentId == $fs->StudentID ? 'selected' : '' }}>
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
                               placeholder="หรือกรอกรหัสนักเรียน เช่น 10001"
                               maxlength="20"
                               value="{{ (!$studentId || collect($filterStudents)->pluck('StudentID')->contains($studentId)) ? '' : $studentId }}"
                               oninput="if(this.value){ document.getElementById('studentSelect').value=''; }"
                               onblur="syncStudentId(this.value)">

                    </div>
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
                    <label class="form-label">ปี ค.ศ.</label>
                    <select name="year" class="form-control">
                        @for($y = now()->year - 2; $y <= now()->year + 2; $y++)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y + 543 }} ({{ $y }})</option>
                        @endfor
                    </select>
                </div>

                <div style="display:flex; gap:0.5rem;">
                    <button type="submit" class="btn btn-primary" style="flex:1; justify-content:center;">
                        <i class="fas fa-search"></i> ดูข้อมูล
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
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-sync-alt"></i></button>
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

                        // Get records for Zuhur (เที่ยง) and Asr (บ่าย) for this day
                        $dayRecords = $calendarDays['records']->get($day) ?? collect();
                        $zuhurRecord = $dayRecords->where('Period', 'เที่ยง')->first();
                        $asrRecord = $dayRecords->where('Period', 'บ่าย')->first();
                    @endphp
                    <div class="calendar-cell @if($isToday) today @endif">
                        <span class="cell-date">{{ $day }}</span>

                        <div class="cell-prayers">
                            <!-- Noon Zuhur Check -->
                            @if($zuhurRecord)
                                @if($zuhurRecord->Status === 'ละหมาด')
                                    <div class="prayer-indicator prayed">🟢 เที่ยง</div>
                                @else
                                    <div class="prayer-indicator exempt">⚪ เที่ยง</div>
                                @endif
                            @elseif($isPast && !$isWeekend)
                                <div class="prayer-indicator absent">🔴 เที่ยง</div>
                            @endif

                            <!-- Afternoon Asr Check -->
                            @if($asrRecord)
                                @if($asrRecord->Status === 'ละหมาด')
                                    <div class="prayer-indicator prayed">🟢 บ่าย</div>
                                @else
                                    <div class="prayer-indicator exempt">⚪ บ่าย</div>
                                @endif
                            @elseif($isPast && !$isWeekend)
                                <div class="prayer-indicator absent">🔴 บ่าย</div>
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

    // Put it into the select (so it's submitted as student_id)
    if (finalId) {
        // Create a hidden option with the typed value if it's not in the list
        if (!sel.querySelector('option[value="' + finalId + '"]')) {
            var opt = document.createElement('option');
            opt.value = finalId;
            opt.selected = true;
            sel.appendChild(opt);
        } else {
            sel.value = finalId;
        }
    }
    // Clear typed field so it doesn't get submitted as extra param
    typed.name = ''; 
}
</script>
@endpush
