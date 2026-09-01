@if(auth()->user()->canAccess('dashboard'))
<div class="nav-section-title">ของฉัน</div>
<div class="nav-item">
    <a href="{{ route('student.dashboard') }}" class="{{ request()->routeIs('student.dashboard') ? 'active' : '' }}" data-title="หน้าหลัก">
        <i class="fas fa-home"></i> <span class="nav-text">หน้าหลัก</span>
    </a>
</div>
@endif

@if(auth()->user()->canAccess('behavior-records') || auth()->user()->canAccess('attendance') || auth()->user()->canAccess('appeals') || auth()->user()->canAccess('informant-reports') || auth()->user()->canAccess('messages'))
<div class="nav-section-title">พฤติกรรมและการเรียน</div>
@endif

@if(auth()->user()->canAccess('behavior-records'))
<div class="nav-item">
    <a href="{{ route('student.behavior-records.index') }}" class="{{ request()->routeIs('student.behavior-records.*') ? 'active' : '' }}" data-title="ประวัติพฤติกรรม">
        <i class="fas fa-clipboard-list"></i> <span class="nav-text">ประวัติพฤติกรรม</span>
    </a>
</div>
@endif

@if(auth()->user()->canAccess('attendance'))
<div class="nav-item">
    <a href="{{ route('student.attendance.index') }}" class="{{ request()->routeIs('student.attendance.*') ? 'active' : '' }}" data-title="การเข้าแถว">
        <i class="fas fa-calendar-check"></i> <span class="nav-text">การเข้าแถว</span>
    </a>
</div>
@endif

@if(auth()->user()->canAccess('appeals'))
<div class="nav-item">
    <a href="{{ route('student.appeals.index') }}" class="{{ request()->routeIs('student.appeals.*') ? 'active' : '' }}" data-title="อุทธรณ์คะแนน">
        <i class="fas fa-balance-scale"></i> <span class="nav-text">อุทธรณ์คะแนน</span>
    </a>
</div>
@endif

@if(auth()->user()->canAccess('informant-reports'))
<div class="nav-item">
    <a href="{{ route('student.informant-reports.index') }}" class="{{ request()->routeIs('student.informant-reports.*') ? 'active' : '' }}" data-title="แจ้งเบาะแสพฤติกรรม">
        <i class="fas fa-bullhorn"></i> <span class="nav-text">แจ้งเบาะแสพฤติกรรม</span>
    </a>
</div>
@endif

@if(auth()->user()->canAccess('messages'))
<div class="nav-item">
    <a href="{{ route('student.messages.index') }}" class="{{ request()->routeIs('student.messages.*') ? 'active' : '' }}" data-title="แชทข้อความ">
        <i class="fas fa-comments"></i> <span class="nav-text">แชทข้อความ</span>
    </a>
</div>
@endif

@if(auth()->user()->canAccess('attendance'))
<div class="nav-section-title">การละหมาด</div>
<div class="nav-item">
    <a href="{{ route('student.prayer-checkin') }}" class="{{ request()->routeIs('student.prayer-checkin') ? 'active' : '' }}" data-title="เช็คละหมาด">
        <i class="fas fa-qrcode"></i> <span class="nav-text">เช็คละหมาด</span>
    </a>
</div>
<div class="nav-item">
    <a href="{{ route('prayer.calendar') }}" class="{{ request()->routeIs('prayer.calendar') ? 'active' : '' }}" data-title="ประวัติการละหมาด">
        <i class="fas fa-star-and-crescent"></i> <span class="nav-text">ประวัติการละหมาด</span>
    </a>
</div>
@endif