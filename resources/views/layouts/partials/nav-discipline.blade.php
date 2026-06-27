@if(auth()->user()->canAccess('dashboard'))
<div class="nav-section-title">ภาพรวม</div>
<div class="nav-item">
    <a href="{{ route('discipline.dashboard') }}" class="{{ request()->routeIs('discipline.dashboard') ? 'active' : '' }}">
        <i class="fas fa-th-large"></i> แดชบอร์ด
    </a>
</div>
@endif

@if(auth()->user()->canAccess('behavior-records') || auth()->user()->canAccess('behavior-rules') || auth()->user()->canAccess('appeals') || auth()->user()->canAccess('risk-students') || auth()->user()->canAccess('informant-reports') || auth()->user()->canAccess('messages'))
<div class="nav-section-title">จัดการพฤติกรรม</div>
@endif

@if(auth()->user()->canAccess('behavior-records'))
<div class="nav-item">
    <a href="{{ route('discipline.behavior-records.index') }}" class="{{ request()->routeIs('discipline.behavior-records.*') ? 'active' : '' }}">
        <i class="fas fa-clipboard-list"></i> บันทึกพฤติกรรม
    </a>
</div>
@endif

@if(auth()->user()->canAccess('behavior-rules'))
<div class="nav-item">
    <a href="{{ route('discipline.behavior-rules.index') }}" class="{{ request()->routeIs('discipline.behavior-rules.*') ? 'active' : '' }}">
        <i class="fas fa-book-open"></i> กฎเกณฑ์พฤติกรรม
    </a>
</div>
@endif

@if(auth()->user()->canAccess('appeals'))
<div class="nav-item">
    <a href="{{ route('discipline.appeals.index') }}" class="{{ request()->routeIs('discipline.appeals.*') ? 'active' : '' }}">
        <i class="fas fa-balance-scale"></i> คำร้องโต้แย้ง
    </a>
</div>
@endif

@if(auth()->user()->canAccess('risk-students'))
<div class="nav-item">
    <a href="{{ route('discipline.risk-students') }}" class="{{ request()->routeIs('discipline.risk-students') ? 'active' : '' }}">
        <i class="fas fa-exclamation-triangle"></i> นักเรียนกลุ่มเสี่ยง
    </a>
</div>
@endif

@if(auth()->user()->canAccess('behavior-records'))
<div class="nav-item">
    <a href="{{ route('discipline.behavior-report') }}" class="{{ request()->routeIs('discipline.behavior-report*') ? 'active' : '' }}">
        <i class="fas fa-chart-line"></i> รายงานสรุปพฤติกรรม
    </a>
</div>
@endif

@if(auth()->user()->canAccess('informant-reports'))
<div class="nav-item">
    <a href="{{ route('discipline.informant-reports.index') }}" class="{{ request()->routeIs('discipline.informant-reports.*') ? 'active' : '' }}">
        <i class="fas fa-bell"></i> รับแจ้งเบาะแส
    </a>
</div>
@endif

@if(auth()->user()->canAccess('messages'))
<div class="nav-item">
    <a href="{{ route('discipline.messages.index') }}" class="{{ request()->routeIs('discipline.messages.*') ? 'active' : '' }}">
        <i class="fas fa-envelope"></i> ข้อความ
    </a>
</div>
@endif

@if(auth()->user()->canAccess('attendance'))
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
@endif