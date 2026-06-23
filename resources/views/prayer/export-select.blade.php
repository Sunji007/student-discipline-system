@extends('layouts.app')

@section('title', 'ส่งออกรายงานการละหมาด')
@section('page-title', 'ส่งออกรายงานการละหมาด')

@push('styles')
<style>
    :root {
        --islamic-primary: #0D5C3A;
        --islamic-emerald: #10B981;
        --islamic-gold: #C5A85C;
    }

    .prayer-export-container {
        max-width: 760px;
        margin: 0 auto;
    }

    .export-card {
        background: white;
        border-radius: 16px;
        border: 1px solid rgba(13, 92, 58, 0.12);
        box-shadow: 0 10px 30px rgba(13, 92, 58, 0.04);
        margin-bottom: 1.5rem;
        overflow: hidden;
    }

    .export-card-header {
        background: linear-gradient(135deg, var(--islamic-primary) 0%, #052b1b 100%);
        color: white;
        padding: 1.25rem 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        border-bottom: 3px solid var(--islamic-gold);
    }

    .export-card-header h3 {
        font-size: 1.1rem;
        font-weight: 700;
        margin: 0;
    }

    .export-card-body {
        padding: 1.5rem;
    }

    .type-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0.75rem;
        margin-bottom: 1.25rem;
    }

    .type-btn {
        padding: 0.85rem;
        border-radius: 10px;
        border: 2px solid var(--border);
        background: white;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-muted);
        cursor: pointer;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.35rem;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .type-btn i {
        font-size: 1.35rem;
    }

    .type-btn:hover {
        border-color: var(--islamic-emerald);
        color: var(--islamic-primary);
        background: rgba(16, 185, 129, 0.04);
    }

    .type-btn.active {
        border-color: var(--islamic-primary);
        color: var(--islamic-primary);
        background: rgba(13, 92, 58, 0.06);
    }

    .action-btns {
        display: flex;
        gap: 0.75rem;
        margin-top: 1.25rem;
        flex-wrap: wrap;
    }

    .btn-islamic {
        background: linear-gradient(135deg, var(--islamic-primary) 0%, #052b1b 100%);
        color: white;
        border: none;
        padding: 0.7rem 1.5rem;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.9rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
        transition: all 0.2s;
        box-shadow: 0 4px 12px rgba(13, 92, 58, 0.2);
    }

    .btn-islamic:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(13, 92, 58, 0.3);
        color: white;
    }

    .btn-csv {
        background: linear-gradient(135deg, #047857 0%, #065f46 100%);
        color: white;
        border: none;
        padding: 0.7rem 1.5rem;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.9rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
        transition: all 0.2s;
    }

    .btn-csv:hover {
        transform: translateY(-1px);
        color: white;
    }

    @media (max-width: 768px) {
        .type-grid { grid-template-columns: repeat(2, 1fr); }
    }
</style>
@endpush

@section('content')
<div class="prayer-export-container">
    <div class="page-header">
        <h2><i class="fas fa-file-export" style="color: var(--islamic-emerald, #10b981); margin-right: 0.5rem;"></i>ส่งออกรายงานการละหมาด</h2>
        <p>เลือกประเภทรายงาน ช่วงเวลา และตัวกรองที่ต้องการ จากนั้นกดพิมพ์หรือดาวน์โหลด CSV</p>
    </div>

    <div class="export-card">
        <div class="export-card-header">
            <i class="fas fa-sliders-h"></i>
            <h3>ตั้งค่าการส่งออกรายงาน</h3>
        </div>
        <div class="export-card-body">
            <form id="exportForm" method="GET" action="{{ route('prayer.export') }}" target="_blank">
                {{-- Report Type --}}
                <label class="form-label" style="font-weight: 700; color: var(--islamic-primary, #0d5c3a); margin-bottom: 0.5rem; display: block;">ประเภทรายงาน</label>
                <div class="type-grid" id="type-grid">
                    <button type="button" class="type-btn active" data-type="daily" onclick="setType('daily')">
                        <i class="fas fa-calendar-day"></i>
                        รายวัน
                    </button>
                    <button type="button" class="type-btn" data-type="weekly" onclick="setType('weekly')">
                        <i class="fas fa-calendar-week"></i>
                        รายสัปดาห์
                    </button>
                    <button type="button" class="type-btn" data-type="monthly" onclick="setType('monthly')">
                        <i class="fas fa-calendar-alt"></i>
                        รายเดือน
                    </button>
                    <button type="button" class="type-btn" data-type="term" onclick="setType('term')">
                        <i class="fas fa-graduation-cap"></i>
                        รายภาคเรียน
                    </button>
                </div>
                <input type="hidden" name="type" id="typeInput" value="daily">

                {{-- Date Fields --}}
                <div id="daily-field" class="form-group">
                    <label class="form-label">วันที่</label>
                    <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" style="max-width: 240px;">
                </div>

                <div id="monthly-field" class="form-group" style="display: none;">
                    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                        <div>
                            <label class="form-label">เดือน</label>
                            <select name="month" class="form-control">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $m == date('n') ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create(null, $m)->locale('th')->isoFormat('MMMM') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="form-label">ปี (พ.ศ.)</label>
                            <select name="year" class="form-control">
                                @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                                    <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y + 543 }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>

                <div id="term-field" class="form-group" style="display: none;">
                    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                        <div>
                            <label class="form-label">ภาคเรียน</label>
                            <select name="term" class="form-control">
                                <option value="1">ภาคเรียนที่ 1 (พ.ค. – ก.ย.)</option>
                                <option value="2">ภาคเรียนที่ 2 (พ.ย. – มี.ค.)</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">ปีการศึกษา</label>
                            <select name="year" id="termYear" class="form-control">
                                @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                                    <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y + 543 }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>

                <hr style="margin: 1.25rem 0; border-color: #eee;">

                {{-- Filters --}}
                <label class="form-label" style="font-weight: 700; color: var(--islamic-primary, #0d5c3a); display: block; margin-bottom: 0.5rem;">ตัวกรอง (ไม่บังคับ)</label>
                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label">ระดับชั้น</label>
                        <select name="grade" class="form-control">
                            <option value="">ทุกระดับชั้น</option>
                            @foreach(['ม.1','ม.2','ม.3','ม.4','ม.5','ม.6'] as $g)
                                <option value="{{ $g }}">{{ $g }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label">ห้องเรียน</label>
                        <select name="classroom" class="form-control">
                            <option value="">ทุกห้องเรียน</option>
                            @foreach(['1','2','3','4','5','6','7','8'] as $c)
                                <option value="{{ $c }}">ห้อง {{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label">เกณฑ์การละหมาด</label>
                        <select name="passing_status" class="form-control">
                            <option value="">ทั้งหมด</option>
                            <option value="pass">ผ่านเกณฑ์ (80% ขึ้นไป)</option>
                            <option value="fail">ไม่ผ่านเกณฑ์ (ต่ำกว่า 80%)</option>
                        </select>
                    </div>
                </div>

                <div class="action-btns">
                    <button type="submit" class="btn-islamic">
                        <i class="fas fa-print"></i> พิมพ์ / บันทึก PDF
                    </button>
                    <button type="submit" name="excel" value="1" class="btn-csv">
                        <i class="fas fa-file-csv"></i> ดาวน์โหลด CSV (Excel)
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Quick links --}}
    <div class="card">
        <div class="card-header-bar">
            <h3><i class="fas fa-star-and-crescent" style="color: #10b981; margin-right: 0.5rem;"></i>ลิงก์ด่วน</h3>
        </div>
        <div class="card-body-pad">
            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                <a href="{{ route('prayer.scan') }}" class="btn btn-primary">
                    <i class="fas fa-qrcode"></i> เช็กชื่อละหมาด
                </a>
                <a href="{{ route('prayer.dashboard') }}" class="btn btn-outline">
                    <i class="fas fa-chart-bar"></i> แดชบอร์ดสรุป
                </a>
                <a href="{{ route('prayer.calendar') }}" class="btn btn-outline">
                    <i class="fas fa-calendar-alt"></i> ปฏิทินการละหมาด
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function setType(type) {
        // Update hidden input
        document.getElementById('typeInput').value = type;
        
        // Update button states
        document.querySelectorAll('.type-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.type === type);
        });

        // Show/hide date fields
        document.getElementById('daily-field').style.display  = (type === 'daily' || type === 'weekly') ? 'block' : 'none';
        document.getElementById('monthly-field').style.display = type === 'monthly' ? 'block' : 'none';
        document.getElementById('term-field').style.display   = type === 'term' ? 'block' : 'none';
    }
</script>
@endpush
