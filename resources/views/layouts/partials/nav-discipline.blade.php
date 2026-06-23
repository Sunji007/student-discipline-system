<div class="nav-section-title">ภาพรวม</div>
<div class="nav-item">
    <a href="{{ route('discipline.dashboard') }}" class="{{ request()->routeIs('discipline.dashboard') ? 'active' : '' }}">
        <i class="fas fa-th-large"></i> แดชบอร์ด
    </a>
</div>

<div class="nav-section-title">จัดการพฤติกรรม</div>
<div class="nav-item">
    <a href="{{ route('discipline.behavior-records.index') }}" class="{{ request()->routeIs('discipline.behavior-records.*') ? 'active' : '' }}">
        <i class="fas fa-clipboard-list"></i> บันทึกพฤติกรรม
    </a>
</div>
<div class="nav-item">
    <a href="{{ route('discipline.behavior-rules.index') }}" class="{{ request()->routeIs('discipline.behavior-rules.*') ? 'active' : '' }}">
        <i class="fas fa-book-open"></i> กฎเกณฑ์พฤติกรรม
    </a>
</div>
<div class="nav-item">
    <a href="{{ route('discipline.appeals.index') }}" class="{{ request()->routeIs('discipline.appeals.*') ? 'active' : '' }}">
        <i class="fas fa-balance-scale"></i> คำร้องโต้แย้ง
    </a>
</div>
<div class="nav-item">
    <a href="{{ route('discipline.risk-students') }}" class="{{ request()->routeIs('discipline.risk-students') ? 'active' : '' }}">
        <i class="fas fa-exclamation-triangle"></i> นักเรียนกลุ่มเสี่ยง
    </a>
</div>
<div class="nav-item">
    <a href="{{ route('discipline.behavior-report') }}" class="{{ request()->routeIs('discipline.behavior-report*') ? 'active' : '' }}">
        <i class="fas fa-chart-line"></i> รายงานสรุปพฤติกรรม
    </a>
</div>
<div class="nav-item">
    <a href="{{ route('discipline.informant-reports.index') }}" class="{{ request()->routeIs('discipline.informant-reports.*') ? 'active' : '' }}">
        <i class="fas fa-bell"></i> รับแจ้งเบาะแส
    </a>
</div>
<div class="nav-item">
    <a href="{{ route('discipline.messages.index') }}" class="{{ request()->routeIs('discipline.messages.*') ? 'active' : '' }}">
        <i class="fas fa-envelope"></i> ข้อความ
    </a>
</div>

<div class="nav-section-title">การละหมาด</div>
<div class="nav-item">
    <a href="{{ route('prayer.dashboard') }}" class="{{ request()->routeIs('prayer.dashboard') ? 'active' : '' }}">
        <i class="fas fa-star-and-crescent"></i> แดชบอร์ดละหมาด
    </a>
</div>
<div class="nav-item">
    <a href="{{ route('prayer.calendar') }}" class="{{ request()->routeIs('prayer.calendar') ? 'active' : '' }}">
        <i class="fas fa-calendar-alt"></i> ปฏิทินการละหมาด
    </a>
</div>
<div class="nav-item">
    <a href="{{ route('prayer.scan') }}" class="{{ request()->routeIs('prayer.scan') ? 'active' : '' }}">
        <i class="fas fa-qrcode"></i> เช็กชื่อละหมาด
    </a>
</div>
<div class="nav-item">
    <a href="{{ route('prayer.export-select') }}" class="{{ request()->routeIs('prayer.export*') ? 'active' : '' }}">
        <i class="fas fa-file-export"></i> ส่งออกรายงาน
    </a>
</div>