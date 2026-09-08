@if(auth()->user()->canAccess('dashboard') || auth()->user()->canAccess('users') || auth()->user()->canAccess('permissions'))
<div class="nav-section-title">จัดการระบบ</div>
@endif

@if(auth()->user()->canAccess('dashboard'))
<div class="nav-item">
    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" data-title="หน้าหลัก">
        <i class="fas fa-home"></i> <span class="nav-text">หน้าหลัก</span>
    </a>
</div>
@endif

@if(auth()->user()->canAccess('users'))
<div class="nav-item">
    <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}" data-title="จัดการข้อมูลผู้ใช้งาน">
        <i class="fas fa-users-cog"></i> <span class="nav-text">จัดการข้อมูลผู้ใช้งาน</span>
    </a>
</div>
<div class="nav-item">
    <a href="{{ route('admin.students.index') }}" class="{{ request()->routeIs('admin.students.*') ? 'active' : '' }}" data-title="จัดการข้อมูลนักเรียน">
        <i class="fas fa-user-graduate"></i> <span class="nav-text">จัดการข้อมูลนักเรียน</span>
    </a>
</div>
<div class="nav-item">
    <a href="{{ route('admin.teachers.index') }}" class="{{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}" data-title="จัดการข้อมูลครู">
        <i class="fas fa-chalkboard-teacher"></i> <span class="nav-text">จัดการข้อมูลครู</span>
    </a>
</div>
<div class="nav-item">
    <a href="{{ route('admin.promotions.index') }}" class="{{ request()->routeIs('admin.promotions.*') ? 'active' : '' }}" data-title="เลื่อนชั้นปีการศึกษา">
        <i class="fas fa-layer-group"></i> <span class="nav-text">เลื่อนชั้นปีการศึกษา</span>
    </a>
</div>
@endif

@if(auth()->user()->canAccess('permissions'))
<div class="nav-item">
    <a href="{{ route('admin.permissions.index') }}" class="{{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}" data-title="การแสดงสิทธิ์">
        <i class="fas fa-shield-alt"></i> <span class="nav-text">การแสดงสิทธิ์</span>
    </a>
</div>
@endif  