<div class="nav-section-title">จัดการระบบ</div>
<div class="nav-item">
    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <i class="fas fa-th-large"></i> แดชบอร์ด
    </a>
</div>
<div class="nav-item">
    <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
        <i class="fas fa-users-cog"></i> จัดการผู้ใช้งาน
    </a>
</div>
<div class="nav-item">
    <a href="{{ route('admin.students.index') }}" class="{{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
        <i class="fas fa-user-graduate"></i> จัดการนักเรียน
    </a>
</div>
<div class="nav-item">
    <a href="{{ route('admin.teachers.index') }}" class="{{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}">
        <i class="fas fa-chalkboard-teacher"></i> จัดการครู
    </a>
</div>
<div class="nav-item">
    <a href="{{ route('admin.permissions.index') }}" class="{{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}">
        <i class="fas fa-shield-alt"></i> จัดการสิทธิ์
    </a>
</div>  