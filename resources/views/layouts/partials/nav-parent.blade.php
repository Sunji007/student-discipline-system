@if(auth()->user()->canAccess('dashboard'))
<div class="nav-section-title">บุตรหลานของฉัน</div>
<div class="nav-item">
    <a href="{{ route('parent.dashboard') }}" class="{{ request()->routeIs('parent.dashboard') ? 'active' : '' }}" data-title="แดชบอร์ด">
        <i class="fas fa-home"></i> <span class="nav-text">แดชบอร์ด</span>
    </a>
</div>
@endif

@if(auth()->user()->canAccess('behavior-records') || auth()->user()->canAccess('attendance') || auth()->user()->canAccess('messages'))
<div class="nav-section-title">ข้อมูลพฤติกรรมและการเรียน</div>
@endif

@if(auth()->user()->canAccess('behavior-records'))
<div class="nav-item">
    <a href="{{ route('parent.behavior-records.index') }}" class="{{ request()->routeIs('parent.behavior-records.*') ? 'active' : '' }}" data-title="ประวัติพฤติกรรม">
        <i class="fas fa-clipboard-list"></i> <span class="nav-text">ประวัติพฤติกรรม</span>
    </a>
</div>
@endif

@if(auth()->user()->canAccess('attendance'))
<div class="nav-item">
    <a href="{{ route('parent.attendance.index') }}" class="{{ request()->routeIs('parent.attendance.*') ? 'active' : '' }}" data-title="การเข้าแถว">
        <i class="fas fa-calendar-check"></i> <span class="nav-text">การเข้าแถว</span>
    </a>
</div>
@endif

@if(auth()->user()->canAccess('messages'))
<div class="nav-item">
    <a href="{{ route('parent.messages.index') }}" class="{{ request()->routeIs('parent.messages.*') ? 'active' : '' }}" data-title="แชทข้อความ">
        <i class="fas fa-comments"></i> <span class="nav-text">แชทข้อความ</span>
    </a>
</div>
@endif

@if(auth()->user()->canAccess('attendance'))
<div class="nav-section-title">การละหมาด</div>
<div class="nav-item">
    <a href="{{ route('prayer.calendar') }}" class="{{ request()->routeIs('prayer.calendar') ? 'active' : '' }}" data-title="การละหมาดของบุตรหลาน">
        <i class="fas fa-star-and-crescent"></i> <span class="nav-text">การละหมาดของบุตรหลาน</span>
    </a>
</div>
@endif