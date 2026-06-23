@extends('layouts.app')

@section('title', 'เช็คละหมาด')
@section('page-title', 'เช็คชื่อการละหมาด')

@push('styles')
<style>
    .checkin-wrapper {
        max-width: 520px;
        margin: 0 auto;
    }

    /* ===== ID Card ===== */
    .student-id-card {
        background: linear-gradient(135deg, #0D5C3A 0%, #052b1b 100%);
        border-radius: 20px;
        padding: 2rem 1.75rem;
        color: white;
        text-align: center;
        position: relative;
        overflow: hidden;
        box-shadow: 0 16px 40px rgba(13,92,58,0.3);
        margin-bottom: 1.5rem;
        border: 1px solid rgba(197,168,92,0.35);
    }

    /* Decorative Islamic pattern */
    .student-id-card::before {
        content: '';
        position: absolute;
        top: -40px; right: -40px;
        width: 160px; height: 160px;
        border-radius: 50%;
        background: rgba(255,255,255,0.03);
        border: 30px solid rgba(255,255,255,0.04);
    }

    .student-id-card::after {
        content: '';
        position: absolute;
        bottom: -30px; left: -30px;
        width: 120px; height: 120px;
        border-radius: 50%;
        background: rgba(255,255,255,0.03);
        border: 20px solid rgba(255,255,255,0.04);
    }

    .card-school-name {
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: rgba(197,168,92,0.9);
        margin-bottom: 1.25rem;
    }

    .student-avatar {
        width: 80px; height: 80px;
        border-radius: 50%;
        margin: 0 auto 1rem;
        border: 3px solid rgba(197,168,92,0.6);
        overflow: hidden;
        background: rgba(255,255,255,0.1);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .student-avatar img {
        width: 100%; height: 100%;
        object-fit: cover;
    }

    .student-avatar i {
        font-size: 2rem;
        color: rgba(255,255,255,0.5);
    }

    .student-name {
        font-size: 1.2rem;
        font-weight: 800;
        letter-spacing: 0.01em;
        margin-bottom: 0.35rem;
    }

    .student-meta {
        display: flex;
        justify-content: center;
        gap: 1rem;
        margin-bottom: 0.5rem;
        font-size: 0.82rem;
        color: rgba(255,255,255,0.65);
    }

    .student-meta span {
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }

    .student-id-badge {
        display: inline-block;
        font-family: 'Outfit', sans-serif;
        font-size: 0.95rem;
        font-weight: 800;
        background: rgba(197,168,92,0.15);
        border: 1px solid rgba(197,168,92,0.4);
        color: #C5A85C;
        padding: 0.3rem 1.2rem;
        border-radius: 20px;
        letter-spacing: 0.1em;
        margin-bottom: 1.5rem;
    }

    /* QR + Barcode area */
    .code-tabs {
        display: flex;
        background: rgba(0,0,0,0.2);
        border-radius: 10px;
        padding: 4px;
        margin-bottom: 1.1rem;
    }

    .code-tab {
        flex: 1;
        padding: 0.5rem;
        border-radius: 7px;
        border: none;
        background: transparent;
        color: rgba(255,255,255,0.55);
        font-size: 0.8rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
    }

    .code-tab.active {
        background: rgba(255,255,255,0.12);
        color: white;
    }

    .code-display {
        background: white;
        border-radius: 12px;
        padding: 1.25rem;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 130px;
    }

    #qr-canvas, #barcode-svg {
        max-width: 100%;
        height: auto;
    }

    .scan-hint {
        font-size: 0.75rem;
        color: rgba(255,255,255,0.45);
        margin-top: 0.75rem;
    }

    /* ===== Today's Status ===== */
    .today-status-card {
        background: white;
        border-radius: 16px;
        border: 1px solid var(--border);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        margin-bottom: 1.5rem;
    }

    .today-status-header {
        background: #f8fafc;
        border-bottom: 1px solid var(--border);
        padding: 0.9rem 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }

    .today-status-header h3 {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--text);
        flex: 1;
    }

    .today-date-badge {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-muted);
        background: white;
        border: 1px solid var(--border);
        padding: 0.25rem 0.65rem;
        border-radius: 6px;
    }

    .prayer-row {
        display: flex;
        align-items: center;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #f8fafc;
        gap: 0.75rem;
    }

    .prayer-row:last-child { border-bottom: none; }

    .prayer-icon-wrap {
        width: 40px; height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .prayer-row-info { flex: 1; }

    .prayer-row-name {
        font-weight: 700;
        font-size: 0.9rem;
        color: var(--text);
    }

    .prayer-row-time {
        font-size: 0.75rem;
        color: var(--text-muted);
        margin-top: 0.1rem;
    }

    .prayer-status-chip {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.35rem 0.85rem;
        border-radius: 20px;
        font-size: 0.78rem;
        font-weight: 700;
    }

    .chip-prayed  { background: rgba(16,185,129,0.1); color: #047857; border: 1px solid rgba(16,185,129,0.25); }
    .chip-absent  { background: rgba(189,39,67,0.08); color: #b91c1c; border: 1px solid rgba(189,39,67,0.2); }
    .chip-pending { background: #f8fafc; color: var(--text-muted); border: 1px solid var(--border); }

    /* ===== Instruction card ===== */
    .instruction-card {
        background: linear-gradient(135deg, rgba(6,4,234,0.04) 0%, rgba(197,168,92,0.04) 100%);
        border: 1px solid rgba(6,4,234,0.1);
        border-radius: 14px;
        padding: 1.25rem;
        margin-bottom: 1.5rem;
    }

    .instruction-card h4 {
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 0.85rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .step-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .step-list li {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        font-size: 0.85rem;
        color: var(--text);
        padding: 0.4rem 0;
    }

    .step-num {
        width: 22px; height: 22px;
        border-radius: 50%;
        background: var(--primary);
        color: white;
        font-size: 0.7rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-top: 0.05rem;
    }

    @media (max-width: 480px) {
        .student-name { font-size: 1rem; }
        .code-display { min-height: 100px; }
    }

    /* ===== Toggle Buttons & Toast ===== */
    :root {
        --islamic-primary: #0D5C3A;
        --islamic-emerald: #10B981;
        --islamic-gold: #C5A85C;
    }

    .toggle-group {
        display: flex;
        gap: 0.75rem;
        margin-bottom: 1.25rem;
    }

    .toggle-btn {
        flex: 1;
        padding: 0.85rem;
        border-radius: 12px;
        border: 2px solid rgba(99, 102, 241, 0.15) !important;
        background: white !important;
        font-weight: 700 !important;
        color: #4b5563 !important;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .toggle-btn i {
        color: #6366f1 !important;
        font-size: 1.1rem;
        display: none !important; /* Hide icon when inactive */
    }

    .toggle-btn:hover {
        background: #f8fafc !important;
        border-color: #cbd5e1 !important;
    }

    /* Period Active States */
    .toggle-btn.period-active {
        background: rgba(16, 185, 129, 0.08) !important;
        border-color: #10b981 !important;
        color: #047857 !important;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);
    }
    .toggle-btn.period-active i {
        color: #10b981 !important;
        display: inline-block !important; /* Show icon when active */
    }

    /* Status Active States */
    .toggle-btn.status-active[data-status="ละหมาด"] {
        background: rgba(16, 185, 129, 0.08) !important;
        border-color: #10b981 !important;
        color: #047857 !important;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);
    }
    .toggle-btn.status-active[data-status="ละหมาด"] i {
        color: #10b981 !important;
        display: inline-block !important;
    }

    .toggle-btn.status-active[data-status="ละหมาดไม่ได้"] {
        background: rgba(189, 39, 67, 0.08) !important;
        border-color: #bd2743 !important;
        color: #bd2743 !important;
        box-shadow: 0 4px 12px rgba(189, 39, 67, 0.15);
    }
    .toggle-btn.status-active[data-status="ละหมาดไม่ได้"] i {
        color: #bd2743 !important;
        display: inline-block !important;
    }

    /* Toast */
    .prayer-toast {
        position: fixed;
        bottom: 1.5rem; right: 1.5rem;
        z-index: 9999;
        padding: 0.85rem 1.5rem;
        border-radius: 12px;
        font-size: 0.9rem;
        font-weight: 600;
        color: white;
        display: flex;
        align-items: center;
        gap: 0.6rem;
        transform: translateY(100px);
        opacity: 0;
        transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
        box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        pointer-events: none;
    }
    .prayer-toast.show { transform: translateY(0); opacity: 1; }
    .prayer-toast.success { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
    .prayer-toast.error   { background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%); }
</style>
@endpush

@section('content')
<div class="checkin-wrapper">

    {{-- ===== STUDENT ID CARD ===== --}}
    <div class="student-id-card">
        <div class="card-school-name">
            <i class="fas fa-star-and-crescent"></i> &nbsp;โรงเรียนศิริราษฎร์สามัคคี &nbsp;<i class="fas fa-star-and-crescent"></i>
        </div>

        {{-- Photo --}}
        <div class="student-avatar">
            @if($student->Photo)
                <img src="{{ asset('storage/' . $student->Photo) }}" alt="{{ $student->FullName }}">
            @else
                <i class="fas fa-user-graduate"></i>
            @endif
        </div>

        <div class="student-name">{{ $student->FullName }}</div>
        <div class="student-meta">
            <span><i class="fas fa-layer-group"></i> {{ $student->GradeLevel }}</span>
            <span><i class="fas fa-door-open"></i> ห้อง {{ $student->Classroom }}</span>
            @if($student->Gender)
            <span><i class="fas fa-{{ $student->Gender === 'ชาย' ? 'mars' : 'venus' }}"></i> {{ $student->Gender }}</span>
            @endif
        </div>
        <div class="student-id-badge">{{ $student->StudentID }}</div>

        {{-- Code tabs --}}
        <div class="code-tabs">
            <button class="code-tab active" id="tab-barcode" onclick="switchTab('barcode')">
                <i class="fas fa-barcode"></i> Barcode
            </button>
            <button class="code-tab" id="tab-qr" onclick="switchTab('qr')">
                <i class="fas fa-qrcode"></i> QR Code
            </button>
        </div>

        {{-- Barcode (default) --}}
        <div class="code-display" id="panel-barcode">
            <svg id="barcode-svg"></svg>
        </div>

        {{-- QR Code (hidden by default) --}}
        <div class="code-display" id="panel-qr" style="display:none;">
            <canvas id="qr-canvas"></canvas>
        </div>

        <div class="scan-hint">
            <i class="fas fa-info-circle"></i>
            แสดงให้ครูสแกน เพื่อบันทึกการละหมาด
        </div>
    </div>

    @php
        $currentHour = now()->hour;
        $defaultPeriod = $currentHour < 14 ? 'เที่ยง' : 'บ่าย';
        $prayedNoon  = $prayerToday->where('Period','เที่ยง')->where('Status','ละหมาด')->first();
        $absentNoon  = $prayerToday->where('Period','เที่ยง')->where('Status','ละหมาดไม่ได้')->first();
        $prayedAsr   = $prayerToday->where('Period','บ่าย')->where('Status','ละหมาด')->first();
        $absentAsr   = $prayerToday->where('Period','บ่าย')->where('Status','ละหมาดไม่ได้')->first();
    @endphp

    {{-- ===== SCAN OPTIONS CARD ===== --}}
    <div class="today-status-card">
        <div class="today-status-header" style="background: linear-gradient(135deg, var(--islamic-primary) 0%, #052b1b 100%); color: white; border-bottom: 3px solid var(--islamic-gold);">
            <i class="fas fa-cog" style="color: var(--islamic-gold);"></i>
            <h3 style="color: white; margin: 0;">ตั้งค่าตัวเลือกสำหรับการสแกน</h3>
        </div>
        <div style="padding: 1.25rem;">
            <!-- Period Select -->
            <label class="form-label" style="font-weight: 700; color: var(--islamic-primary); margin-bottom: 0.5rem; display: block; font-size: 0.82rem;">1. เลือกช่วงเวลาการละหมาด</label>
            <div class="toggle-group" id="period-toggles" style="margin-bottom: 1.25rem;">
                <button type="button" class="toggle-btn {{ $defaultPeriod === 'เที่ยง' ? 'period-active' : '' }}" id="btnNoon" onclick="setPeriod('เที่ยง')" data-period="เที่ยง">
                    <i class="fas fa-sun"></i> เที่ยง (ซุฮรี)
                </button>
                <button type="button" class="toggle-btn {{ $defaultPeriod === 'บ่าย' ? 'period-active' : '' }}" id="btnAsr" onclick="setPeriod('บ่าย')" data-period="บ่าย">
                    <i class="fas fa-cloud-sun"></i> บ่าย (อัศรี)
                </button>
            </div>

            <!-- Status Select -->
            <label class="form-label" style="font-weight: 700; color: var(--islamic-primary); margin-bottom: 0.5rem; display: block; font-size: 0.82rem;">2. เลือกสถานะการละหมาดของคุณ</label>
            <div class="toggle-group" id="status-toggles" style="margin-bottom: 0.5rem;">
                <button type="button" class="toggle-btn status-active" id="btnStatusPray" onclick="setStatus('ละหมาด')" data-status="ละหมาด">
                    <i class="fas fa-check-circle"></i> ✅ ละหมาดได้
                </button>
                <button type="button" class="toggle-btn" id="btnStatusExempt" onclick="setStatus('ละหมาดไม่ได้')" data-status="ละหมาดไม่ได้">
                    <i class="fas fa-times-circle"></i> ❌ ละหมาดไม่ได้
                </button>
            </div>
            <p style="font-size:0.75rem; color:var(--text-muted); text-align:center; margin-top:0.75rem; background:#f8fafc; padding:0.5rem; border-radius:8px;">
                <i class="fas fa-info-circle" style="color:var(--islamic-primary);"></i> เมื่อเลือกแล้ว คิวอาร์โค้ดและบาร์โค้ดด้านบนจะปรับเปลี่ยนตามตัวเลือกโดยอัตโนมัติ เพื่อให้ครูสแกนเช็คชื่อได้ทันที
            </p>
        </div>
    </div>

    {{-- ===== TODAY'S PRAYER STATUS ===== --}}
    <div class="today-status-card">
        <div class="today-status-header">
            <i class="fas fa-calendar-day" style="color:#10b981;"></i>
            <h3>สถานะวันนี้</h3>
            <span class="today-date-badge">{{ now()->locale('th')->isoFormat('D MMM YYYY') }}</span>
        </div>

        {{-- ละหมาดเที่ยง --}}
        <div class="prayer-row">
            <div class="prayer-icon-wrap" style="background:rgba(251,191,36,0.12);">
                <i class="fas fa-sun" style="color:#d97706;"></i>
            </div>
            <div class="prayer-row-info">
                <div class="prayer-row-name">ละหมาดเที่ยง</div>
                <div class="prayer-row-time">Zuhur (12:00 – 13:30)</div>
            </div>
            <div id="status-chip-noon">
                @if($prayedNoon)
                    <div class="prayer-status-chip chip-prayed">
                        <i class="fas fa-check-circle"></i> ละหมาดแล้ว
                    </div>
                @elseif($absentNoon)
                    <div class="prayer-status-chip chip-absent">
                        <i class="fas fa-times-circle"></i> ไม่ได้ละหมาด
                    </div>
                @else
                    <div class="prayer-status-chip chip-pending">
                        <i class="fas fa-clock"></i> ยังไม่บันทึก
                    </div>
                @endif
            </div>
        </div>

        {{-- ละหมาดบ่าย --}}
        <div class="prayer-row">
            <div class="prayer-icon-wrap" style="background:rgba(16,185,129,0.1);">
                <i class="fas fa-cloud-sun" style="color:#059669;"></i>
            </div>
            <div class="prayer-row-info">
                <div class="prayer-row-name">ละหมาดบ่าย</div>
                <div class="prayer-row-time">Asr (15:00 – 16:30)</div>
            </div>
            <div id="status-chip-asr">
                @if($prayedAsr)
                    <div class="prayer-status-chip chip-prayed">
                        <i class="fas fa-check-circle"></i> ละหมาดแล้ว
                    </div>
                @elseif($absentAsr)
                    <div class="prayer-status-chip chip-absent">
                        <i class="fas fa-times-circle"></i> ไม่ได้ละหมาด
                    </div>
                @else
                    <div class="prayer-status-chip chip-pending">
                        <i class="fas fa-clock"></i> ยังไม่บันทึก
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ===== HOW TO USE ===== --}}
    <div class="instruction-card">
        <h4><i class="fas fa-info-circle"></i> วิธีเช็คละหมาด</h4>
        <ul class="step-list">
            <li>
                <span class="step-num">1</span>
                <span>แสดงหน้าจอนี้ให้ครูหรือเจ้าหน้าที่เห็น</span>
            </li>
            <li>
                <span class="step-num">2</span>
                <span>ครูนำเครื่องสแกนบาร์โค้ดหรือกล้อง สแกน Barcode / QR Code ของคุณ</span>
            </li>
            <li>
                <span class="step-num">3</span>
                <span>ระบบบันทึกการละหมาดโดยอัตโนมัติ สถานะจะอัปเดตในหน้านี้</span>
            </li>
        </ul>
        <div style="margin-top:1rem; padding-top:1rem; border-top:1px solid rgba(6,4,234,0.08);">
            <a href="{{ route('prayer.calendar') }}" class="btn btn-outline btn-sm" style="width:100%; justify-content:center;">
                <i class="fas fa-star-and-crescent"></i> ดูประวัติการละหมาดทั้งหมด
            </a>
        </div>
    </div>

</div>
@endsection

@push('scripts')
{{-- JsBarcode --}}
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
{{-- qrious.js --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>
<script>
// ===== Constants =====
var studentId    = '{{ $student->StudentID }}';
var selectedPeriod = sessionStorage.getItem('selectedPeriod') || '{{ $defaultPeriod }}';
var selectedStatus = sessionStorage.getItem('selectedStatus') || 'ละหมาด'; // Default to can pray

// ===== Period toggle =====
function setPeriod(period) {
    selectedPeriod = period;
    sessionStorage.setItem('selectedPeriod', period);
    var btnNoon = document.getElementById('btnNoon');
    var btnAsr  = document.getElementById('btnAsr');
    if (btnNoon) btnNoon.classList.toggle('period-active', period === 'เที่ยง');
    if (btnAsr)  btnAsr.classList.toggle('period-active',  period === 'บ่าย');
    
    generateCodes();
}

// ===== Status toggle =====
function setStatus(status) {
    selectedStatus = status;
    sessionStorage.setItem('selectedStatus', status);
    var btnStatusPray   = document.getElementById('btnStatusPray');
    var btnStatusExempt = document.getElementById('btnStatusExempt');
    if (btnStatusPray)   btnStatusPray.classList.toggle('status-active', status === 'ละหมาด');
    if (btnStatusExempt) btnStatusExempt.classList.toggle('status-active', status === 'ละหมาดไม่ได้');
    
    generateCodes();
}

// ===== Generate Codes Dynamically =====
function generateCodes() {
    var periodVal = selectedPeriod; // 'เที่ยง' or 'บ่าย'
    var statusVal = selectedStatus; // 'ละหมาด' or 'ละหมาดไม่ได้'
    
    // Format barcode payload: e.g. 10001-noon-pray
    var barcodePeriod = (periodVal === 'เที่ยง') ? 'noon' : 'asr';
    var barcodeStatus = (statusVal === 'ละหมาด') ? 'pray' : 'exempt';
    var barcodePayload = studentId + '-' + barcodePeriod + '-' + barcodeStatus;
    
    // Format QR payload: JSON string
    var qrPayload = JSON.stringify({
        id: studentId,
        period: periodVal,
        status: statusVal
    });
    
    // 1. Generate Barcode
    try {
        JsBarcode('#barcode-svg', barcodePayload, {
            format: 'CODE128', width: 2, height: 70,
            displayValue: true, lineColor: '#0d5c3a',
            margin: 6, fontSize: 13, fontOptions: 'bold'
        });
    } catch(e) { console.warn('Barcode error:', e); }

    // 2. Generate QR Code
    try {
        new QRious({
            element: document.getElementById('qr-canvas'),
            value: qrPayload,
            size: 180,
            background: 'white',
            foreground: '#0D5C3A',
            level: 'H'
        });
    } catch(e) { console.warn('QR error:', e); }
}

// ===== Tab switch (Barcode / QR) =====
function switchTab(tab) {
    var isBarcode = (tab === 'barcode');
    document.getElementById('panel-barcode').style.display = isBarcode ? 'flex' : 'none';
    document.getElementById('panel-qr').style.display      = isBarcode ? 'none' : 'flex';
    document.getElementById('tab-barcode').className = 'code-tab' + (isBarcode ? ' active' : '');
    document.getElementById('tab-qr').className      = 'code-tab' + (isBarcode ? '' : ' active');
}

// ===== Initialization =====
document.addEventListener("DOMContentLoaded", function() {
    // Apply initial UI states based on variables
    setPeriod(selectedPeriod);
    setStatus(selectedStatus);
    
    // Auto reload every 30 seconds to fetch updated statuses from server scans
    setTimeout(function() {
        window.location.reload();
    }, 30000);
});
</script>
@endpush

