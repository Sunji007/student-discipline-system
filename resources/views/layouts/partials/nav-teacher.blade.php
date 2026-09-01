@if(auth()->user()->canAccess('dashboard'))
<div class="nav-section-title">ภาพรวม</div>
<div class="nav-item">
    <a href="{{ route('teacher.dashboard') }}" class="{{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}" data-title="หน้าหลัก">
        <i class="fas fa-home"></i> <span class="nav-text">หน้าหลัก</span>
    </a>
</div>
@endif

@if(auth()->user()->canAccess('attendance') || auth()->user()->canAccess('behavior-records'))
<div class="nav-section-title">ห้องเรียนของฉัน</div>
@endif

@if(auth()->user()->canAccess('attendance'))
<div class="nav-item">
    <a href="{{ route('teacher.classroom.index') }}" class="{{ request()->routeIs('teacher.classroom.*') ? 'active' : '' }}" data-title="รายชื่อนักเรียน">
        <i class="fas fa-door-open"></i> <span class="nav-text">รายชื่อนักเรียน</span>
    </a>
</div>
<div class="nav-item">
    <a href="{{ route('teacher.attendance.index') }}" class="{{ request()->routeIs('teacher.attendance.*') ? 'active' : '' }}" data-title="เช็คชื่อเข้าแถว">
        <i class="fas fa-calendar-check"></i> <span class="nav-text">เช็คชื่อเข้าแถว</span>
    </a>
</div>
@endif

@if(auth()->user()->canAccess('behavior-records'))
<div class="nav-item">
    <a href="{{ route('teacher.behavior-records.index') }}" class="{{ request()->routeIs('teacher.behavior-records.*') ? 'active' : '' }}" data-title="บันทึกพฤติกรรม">
        <i class="fas fa-clipboard-list"></i> <span class="nav-text">บันทึกพฤติกรรม</span>
    </a>
</div>
@endif

@if(auth()->user()->canAccess('messages'))
<div class="nav-item">
    <a href="{{ route('teacher.messages.index') }}" class="{{ request()->routeIs('teacher.messages.*') ? 'active' : '' }}" data-title="แชทข้อความ">
        <i class="fas fa-comments"></i> <span class="nav-text">แชทข้อความ</span>
    </a>
</div>
@endif
