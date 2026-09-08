@if(auth()->user()->canAccess('dashboard'))
<div class="nav-section-title">ภาพรวม</div>
<div class="nav-item">
    <a href="{{ route('discipline.dashboard') }}" class="{{ request()->routeIs('discipline.dashboard') ? 'active' : '' }}" data-title="แดชบอร์ด">
        <i class="fas fa-home"></i> <span class="nav-text">แดชบอร์ด</span>
    </a>
</div>
@endif

@if(auth()->user()->canAccess('behavior-records') || auth()->user()->canAccess('behavior-rules') || auth()->user()->canAccess('appeals') || auth()->user()->canAccess('risk-students') || auth()->user()->canAccess('informant-reports') || auth()->user()->canAccess('messages'))
<div class="nav-section-title">จัดการพฤติกรรม</div>
@endif

@if(auth()->user()->canAccess('behavior-records'))
<div class="nav-item">
    <a href="{{ route('discipline.behavior-records.index') }}" class="{{ request()->routeIs('discipline.behavior-records.*') ? 'active' : '' }}" data-title="บันทึกพฤติกรรม">
        <i class="fas fa-clipboard-list"></i> <span class="nav-text">บันทึกพฤติกรรม</span>
    </a>
</div>
@endif

@if(auth()->user()->canAccess('behavior-rules'))
<div class="nav-item">
    <a href="{{ route('discipline.behavior-rules.index') }}" class="{{ request()->routeIs('discipline.behavior-rules.*') ? 'active' : '' }}" data-title="เกณฑ์ประเมินพฤติกรรม">
        <i class="fas fa-book-open"></i> <span class="nav-text">เกณฑ์ประเมินพฤติกรรม</span>
    </a>
</div>
@endif

@if(auth()->user()->canAccess('appeals'))
<div class="nav-item">
    <a href="{{ route('discipline.appeals.index') }}" class="{{ request()->routeIs('discipline.appeals.*') ? 'active' : '' }}" data-title="พิจารณาคำอุทธรณ์">
        <i class="fas fa-balance-scale"></i> <span class="nav-text">พิจารณาคำอุทธรณ์</span>
    </a>
</div>
@endif

@if(auth()->user()->canAccess('risk-students'))
<div class="nav-item">
    <a href="{{ route('discipline.risk-students') }}" class="{{ request()->routeIs('discipline.risk-students') ? 'active' : '' }}" data-title="นักเรียนกลุ่มเสี่ยง">
        <i class="fas fa-exclamation-triangle"></i> <span class="nav-text">นักเรียนกลุ่มเสี่ยง</span>
    </a>
</div>
@endif

@if(auth()->user()->canAccess('behavior-records'))
<div class="nav-item">
    <a href="{{ route('discipline.behavior-report') }}" class="{{ request()->routeIs('discipline.behavior-report*') ? 'active' : '' }}" data-title="รายงานสรุปพฤติกรรม">
        <i class="fas fa-chart-line"></i> <span class="nav-text">รายงานสรุปพฤติกรรม</span>
    </a>
</div>
@endif

@if(auth()->user()->canAccess('informant-reports'))
<div class="nav-item">
    <a href="{{ route('discipline.informant-reports.index') }}" class="{{ request()->routeIs('discipline.informant-reports.*') ? 'active' : '' }}" data-title="รับแจ้งเบาะแส">
        <i class="fas fa-bell"></i> <span class="nav-text">รับแจ้งเบาะแส</span>
    </a>
</div>
@endif

@if(auth()->user()->canAccess('messages'))
<div class="nav-item">
    <a href="{{ route('discipline.messages.index') }}" class="{{ request()->routeIs('discipline.messages.*') ? 'active' : '' }}" data-title="แชทข้อความ">
        <i class="fas fa-comments"></i> <span class="nav-text">แชทข้อความ</span>
    </a>
</div>
@endif

@if(auth()->user()->canAccess('attendance'))
<div class="nav-section-title">การละหมาด</div>
<div class="nav-item">
    <a href="{{ route('prayer.scan') }}" class="{{ request()->routeIs('prayer.scan') ? 'active' : '' }}" data-title="เช็คชื่อละหมาด">
        <i class="fas fa-qrcode"></i> <span class="nav-text">เช็คชื่อละหมาด</span>
    </a>
</div>
<div class="nav-item">
    <a href="{{ route('prayer.dashboard') }}" class="{{ request()->routeIs('prayer.dashboard') ? 'active' : '' }}" data-title="แดชบอร์ดละหมาด">
        <i class="fas fa-star-and-crescent"></i> <span class="nav-text">แดชบอร์ดละหมาด</span>
    </a>
</div>
<div class="nav-item">
    <a href="{{ route('prayer.export-select') }}" class="{{ request()->routeIs('prayer.export*') ? 'active' : '' }}" data-title="ส่งออกรายงาน">
        <i class="fas fa-file-export"></i> <span class="nav-text">ส่งออกรายงาน</span>
    </a>
</div>
@endif