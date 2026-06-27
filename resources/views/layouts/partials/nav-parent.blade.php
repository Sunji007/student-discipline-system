@if(auth()->user()->canAccess('dashboard'))
<div class="nav-section-title">บุตรหลานของฉัน</div>
<div class="nav-item">
    <a href="{{ route('parent.dashboard') }}" class="{{ request()->routeIs('parent.dashboard') ? 'active' : '' }}">
        <i class="fas fa-th-large"></i> แดชบอร์ด
    </a>
</div>
@endif

@if(auth()->user()->canAccess('behavior-records') || auth()->user()->canAccess('attendance') || auth()->user()->canAccess('messages'))
<div class="nav-section-title">ข้อมูลพฤติกรรมและการเรียน</div>
@endif

@if(auth()->user()->canAccess('behavior-records'))
<div class="nav-item">
    <a href="{{ route('parent.behavior-records.index') }}" class="{{ request()->routeIs('parent.behavior-records.*') ? 'active' : '' }}">
        <i class="fas fa-clipboard-list"></i> ประวัติพฤติกรรม
    </a>
</div>
@endif

@if(auth()->user()->canAccess('attendance'))
<div class="nav-item">
    <a href="{{ route('parent.attendance.index') }}" class="{{ request()->routeIs('parent.attendance.*') ? 'active' : '' }}">
        <i class="fas fa-calendar-check"></i> การเข้าแถว
    </a>
</div>
@endif

@if(auth()->user()->canAccess('messages'))
<div class="nav-item">
    <a href="{{ route('parent.messages.index') }}" class="{{ request()->routeIs('parent.messages.*') ? 'active' : '' }}">
        <i class="fas fa-envelope"></i> ข้อความ
    </a>
</div>
@endif

@if(auth()->user()->canAccess('attendance'))
<div class="nav-section-title">การละหมาด</div>
<div class="nav-item">
    <a href="{{ route('prayer.calendar') }}" class="{{ request()->routeIs('prayer.calendar') ? 'active' : '' }}">
        <i class="fas fa-star-and-crescent"></i> การละหมาดของบุตรหลาน
    </a>
</div>
@endif