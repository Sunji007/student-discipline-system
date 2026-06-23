<div class="nav-section-title">ภาพรวม</div>
<div class="nav-item">
    <a href="{{ route('teacher.dashboard') }}" class="{{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}">
        <i class="fas fa-th-large"></i> แดชบอร์ด
    </a>
</div>

<div class="nav-section-title">ห้องเรียนของฉัน</div>
<div class="nav-item">
    <a href="{{ route('teacher.classroom.index') }}" class="{{ request()->routeIs('teacher.classroom.*') ? 'active' : '' }}">
        <i class="fas fa-door-open"></i> รายชื่อนักเรียน
    </a>
</div>
<div class="nav-item">
    <a href="{{ route('teacher.attendance.index') }}" class="{{ request()->routeIs('teacher.attendance.*') ? 'active' : '' }}">
        <i class="fas fa-calendar-check"></i> เช็คชื่อเข้าแถว
    </a>
</div>
<div class="nav-item">
    <a href="{{ route('teacher.behavior-records.index') }}" class="{{ request()->routeIs('teacher.behavior-records.*') ? 'active' : '' }}">
        <i class="fas fa-clipboard-list"></i> บันทึกพฤติกรรม
    </a>
</div>

<div class="nav-item">
    <a href="{{ route('teacher.messages.index') }}" class="{{ request()->routeIs('teacher.messages.*') ? 'active' : '' }}">
        <i class="fas fa-envelope"></i> ข้อความ
    </a>
</div>
