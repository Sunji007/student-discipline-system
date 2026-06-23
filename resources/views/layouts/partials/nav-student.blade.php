<div class="nav-section-title">ของฉัน</div>
<div class="nav-item">
    <a href="{{ route('student.dashboard') }}" class="{{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
        <i class="fas fa-th-large"></i> แดชบอร์ด
    </a>
</div>
<div class="nav-item">
    <a href="{{ route('student.behavior-records.index') }}" class="{{ request()->routeIs('student.behavior-records.*') ? 'active' : '' }}">
        <i class="fas fa-clipboard-list"></i> ประวัติพฤติกรรม
    </a>
</div>
<div class="nav-item">
    <a href="{{ route('student.attendance.index') }}" class="{{ request()->routeIs('student.attendance.*') ? 'active' : '' }}">
        <i class="fas fa-calendar-check"></i> การเข้าแถว
    </a>
</div>
<div class="nav-item">
    <a href="{{ route('student.appeals.index') }}" class="{{ request()->routeIs('student.appeals.*') ? 'active' : '' }}">
        <i class="fas fa-balance-scale"></i> ยื่นคำร้องโต้แย้ง
    </a>
</div>
<div class="nav-item">
    <a href="{{ route('student.informant-reports.index') }}" class="{{ request()->routeIs('student.informant-reports.*') ? 'active' : '' }}">
        <i class="fas fa-bullhorn"></i> แจ้งเบาะแสพฤติกรรม
    </a>
</div>
<div class="nav-item">
    <a href="{{ route('student.messages.index') }}" class="{{ request()->routeIs('student.messages.*') ? 'active' : '' }}">
        <i class="fas fa-envelope"></i> ข้อความ
    </a>
</div>

<div class="nav-section-title">การละหมาด</div>
<div class="nav-item">
    <a href="{{ route('student.prayer-checkin') }}" class="{{ request()->routeIs('student.prayer-checkin') ? 'active' : '' }}">
        <i class="fas fa-qrcode"></i> เช็คละหมาด
    </a>
</div>
<div class="nav-item">
    <a href="{{ route('prayer.calendar') }}" class="{{ request()->routeIs('prayer.calendar') ? 'active' : '' }}">
        <i class="fas fa-star-and-crescent"></i> ประวัติการละหมาด
    </a>
</div>