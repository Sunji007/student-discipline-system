@extends('layouts.app')

@section('title', 'รายชื่อนักเรียน')
@section('page-title', 'รายชื่อนักเรียนในห้องเรียนที่ปรึกษา')

@push('styles')
<style>
    /* Student Card Grid Styling */
    .student-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
        gap: 1.25rem;
        margin-bottom: 2rem;
    }

    .student-card {
        background: white;
        border-radius: 16px;
        border: 1px solid var(--border);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }

    .student-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-md);
        border-color: var(--primary-light);
    }

    .sc-top {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.25rem;
    }

    .sc-photo {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        border: 2px solid #e5e7eb;
        overflow: hidden;
        background: #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .sc-photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .sc-photo i {
        color: #d1d5db;
        font-size: 1.75rem;
    }

    .sc-info {
        flex: 1;
        min-width: 0;
    }

    .sc-name {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--text);
        margin-bottom: 0.25rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .sc-id {
        font-size: 0.78rem;
        color: var(--text-muted);
        font-family: 'Outfit', sans-serif;
    }

    .sc-meta {
        padding: 0.75rem 1.25rem 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-top: 1px solid #f8fafc;
        background: #fdfdfd;
    }

    .status-badge {
        font-size: 0.75rem;
        font-weight: 700;
        padding: 0.3rem 0.75rem;
        border-radius: 30px;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }

    .status-normal { background: rgba(16, 185, 129, 0.1); color: #047857; border: 1px solid rgba(16, 185, 129, 0.15); }
    .status-warning { background: rgba(245, 158, 11, 0.1); color: #b45309; border: 1px solid rgba(245, 158, 11, 0.15); }
    .status-critical { background: rgba(239, 68, 68, 0.1); color: #b91c1c; border: 1px solid rgba(239, 68, 68, 0.15); }
</style>
@endpush

@section('content')
{{-- ===== HEADER ===== --}}
<div class="page-header">
    <h2>
        <i class="fas fa-door-open" style="color:var(--primary); margin-right:0.5rem;"></i>
        @if($classroom === 'all')
            ทุกห้องที่คุณปรึกษา ({{ implode(', ', $rooms) }})
        @else
            ห้อง {{ $classroom ?? 'ยังไม่ได้รับมอบหมาย' }}
        @endif
    </h2>
    <p>
        @if($statusFilter === 'risk' || $statusFilter === 'เสี่ยง')
            นักเรียนกลุ่มเสี่ยง <strong>{{ $students->count() }}</strong> คน (จากทั้งหมด {{ $statusCounts['all'] ?? $students->count() }} คน)
        @elseif($statusFilter)
            นักเรียนสถานะ "{{ $statusFilter }}" <strong>{{ $students->count() }}</strong> คน (จากทั้งหมด {{ $statusCounts['all'] ?? $students->count() }} คน)
        @else
            นักเรียนในความดูแลทั้งหมด {{ $students->count() }} คน
        @endif
        &nbsp;|&nbsp; {{ now()->locale('th')->isoFormat('D MMMM ') . (now()->year + 543) }}
    </p>
</div>

<div style="display:flex; flex-wrap:wrap; align-items:center; gap:0.75rem; margin-bottom:1.25rem;">
    @if(isset($rooms) && count($rooms) > 1)
    <div class="filter-dropdown-wrap">
        <label class="filter-dropdown-label" for="teacherClassroomFilter">
            <i class="fas fa-door-open"></i> เลือกห้องเรียน:
        </label>
        <select id="teacherClassroomFilter" class="filter-dropdown-select" onchange="if(this.value){ window.location.href = this.value; }">
            <option value="{{ route('teacher.classroom.index', array_filter(['room' => 'all', 'status' => $statusFilter], fn($v) => !is_null($v) && $v !== '')) }}" {{ $classroom === 'all' ? 'selected' : '' }}>
                ทุกห้องที่คุณปรึกษา ({{ implode(', ', $rooms) }})
            </option>
            @foreach($rooms as $r)
                <option value="{{ route('teacher.classroom.index', array_filter(['room' => $r, 'status' => $statusFilter], fn($v) => !is_null($v) && $v !== '')) }}" {{ $classroom === $r ? 'selected' : '' }}>
                    ห้อง {{ $r }}
                </option>
            @endforeach
        </select>
    </div>
    @endif

    {{-- Status Filter Dropdown --}}
    <div class="filter-dropdown-wrap">
        <label class="filter-dropdown-label" for="teacherStatusFilter">
            <i class="fas fa-filter"></i> สถานะนักเรียน:
        </label>
        <select id="teacherStatusFilter" class="filter-dropdown-select" onchange="if(this.value){ window.location.href = this.value; }">
            <option value="{{ route('teacher.classroom.index', array_filter(['room' => $classroom], fn($v) => !is_null($v) && $v !== '')) }}" {{ !$statusFilter ? 'selected' : '' }}>
                ทั้งหมด ({{ $statusCounts['all'] ?? 0 }})
            </option>
            <option value="{{ route('teacher.classroom.index', array_filter(['room' => $classroom, 'status' => 'risk'], fn($v) => !is_null($v) && $v !== '')) }}" {{ ($statusFilter === 'risk' || $statusFilter === 'เสี่ยง') ? 'selected' : '' }}>
                นักเรียนเสี่ยง ({{ $statusCounts['risk'] ?? 0 }})
            </option>
            <option value="{{ route('teacher.classroom.index', array_filter(['room' => $classroom, 'status' => 'ปกติ'], fn($v) => !is_null($v) && $v !== '')) }}" {{ $statusFilter === 'ปกติ' ? 'selected' : '' }}>
                ปกติ ({{ $statusCounts['ปกติ'] ?? 0 }})
            </option>
            <option value="{{ route('teacher.classroom.index', array_filter(['room' => $classroom, 'status' => 'ตักเตือน'], fn($v) => !is_null($v) && $v !== '')) }}" {{ $statusFilter === 'ตักเตือน' ? 'selected' : '' }}>
                ตักเตือน ({{ $statusCounts['ตักเตือน'] ?? 0 }})
            </option>
            <option value="{{ route('teacher.classroom.index', array_filter(['room' => $classroom, 'status' => 'ทัณฑ์บน'], fn($v) => !is_null($v) && $v !== '')) }}" {{ $statusFilter === 'ทัณฑ์บน' ? 'selected' : '' }}>
                ทัณฑ์บน ({{ $statusCounts['ทัณฑ์บน'] ?? 0 }})
            </option>
        </select>
    </div>

    @if($statusFilter)
    <a href="{{ route('teacher.classroom.index', array_filter(['room' => $classroom], fn($v) => !is_null($v) && $v !== '')) }}" class="btn btn-sm" style="background: white; border: 1px solid var(--border); color: var(--text-muted); font-size: 0.8rem; font-weight: 600; padding: 0.35rem 0.75rem; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;" title="ล้างตัวกรอง">
        <i class="fas fa-times"></i> ล้างตัวกรอง
    </a>
    @endif
</div>



{{-- ===== STUDENT GRID ===== --}}
<div class="student-grid">
    @forelse($students as $s)
    <div class="student-card" id="card-{{ $s->StudentID }}">
        {{-- Top Info --}}
        <div class="sc-top" style="cursor: pointer;"
             data-student-name="{{ $s->FullName }}"
             data-student-id="{{ $s->StudentID }}"
             data-student-class="{{ $s->classroom_display }}"
             data-student-score="{{ $s->BehaviorScore }}"
             data-student-risk="{{ $s->RiskStatus }}"
             data-student-photo="{{ $s->Photo ? asset('storage/' . $s->Photo) : '' }}"
             data-parent-name="{{ $s->parent ? $s->parent->FullName : 'ยังไม่มีข้อมูลผู้ปกครอง' }}"
             data-parent-relationship="{{ $s->parent ? $s->parent->Relationship : '-' }}"
             data-parent-phone="{{ $s->parent ? $s->parent->Phone : '-' }}"
             data-parent-email="{{ $s->parent ? $s->parent->Email : '-' }}"
             data-parent-address="{{ $s->parent ? $s->parent->Address : '-' }}"
             data-parent-user-id="{{ $s->parent ? $s->parent->UserID : '' }}"
             onclick="showParentModal(this)"
             title="คลิกเพื่อดูข้อมูลผู้ปกครอง">
            <div class="sc-photo">
                @if($s->Photo)
                    <img src="{{ asset('storage/' . $s->Photo) }}" alt="{{ $s->FullName }}">
                @else
                    <i class="fas fa-user-graduate"></i>
                @endif
            </div>
            <div class="sc-info">
                <div class="sc-name">{{ $s->FullName }}</div>
                <div class="sc-id">รหัส: {{ $s->StudentID }} &bull; ชั้น {{ $s->classroom_display }}</div>
                @php $curScore = $s->BehaviorScore ?? 100; @endphp
                <div style="font-size: 0.8rem; font-weight: 700; margin-top: 0.35rem; color: {{ $curScore >= 80 ? 'var(--green)' : ($curScore >= 60 ? 'var(--orange)' : 'var(--red)') }}; display: flex; align-items: center; gap: 0.3rem;">
                    <i class="fas fa-star" style="color: var(--yellow);"></i> คะแนนพฤติกรรม: {{ $curScore }} คะแนน
                </div>
            </div>
        </div>

        {{-- Meta & Action --}}
        <div class="sc-meta">
            @php
                $rawRisk = $s->RiskStatus ?? 'ปกติ';
                $riskLabel = match($rawRisk) {
                    'เฝ้าระวัง', 'ตักเตือน' => 'ตักเตือน',
                    'วิกฤต', 'ทัณฑ์บน' => 'ทัณฑ์บน',
                    default => 'ปกติ'
                };
                $badgeClass = match($riskLabel) {
                    'ตักเตือน' => 'status-warning',
                    'ทัณฑ์บน' => 'status-critical',
                    default => 'status-normal'
                };
                $icon = match($riskLabel) {
                    'ตักเตือน' => 'exclamation-circle',
                    'ทัณฑ์บน' => 'exclamation-triangle',
                    default => 'check-circle'
                };
            @endphp
            <span class="status-badge {{ $badgeClass }}">
                <i class="fas fa-{{ $icon }}"></i> {{ $riskLabel }}
            </span>

            <a href="{{ route('teacher.behavior-records.create', ['student_id' => $s->StudentID]) }}" class="btn btn-primary btn-sm" style="font-size:0.75rem; font-weight:700; padding:0.4rem 0.8rem; border-radius:8px;">
                <i class="fas fa-plus"></i> บันทึก
            </a>
        </div>
    </div>
    @empty
    <div style="grid-column:1/-1; text-align:center; padding:3.5rem 2rem; background:white; border-radius:16px; border:1px solid var(--border); color:var(--text-muted);">
        <i class="fas {{ $statusFilter ? 'fa-user-check' : 'fa-users-slash' }}" style="font-size:2.5rem; margin-bottom:1rem; display:block; color:{{ $statusFilter ? 'var(--green)' : '#ccc' }};"></i>
        <h4 style="color:var(--text); margin-bottom:0.5rem; font-size:1.05rem;">
            @if($statusFilter)
                ไม่พบนักเรียนในสถานะ "{{ ($statusFilter === 'risk' || $statusFilter === 'เสี่ยง') ? 'กลุ่มเสี่ยง' : $statusFilter }}" {{ $classroom === 'all' ? 'ในทุกห้องที่คุณปรึกษา' : 'ในห้องนี้' }}
            @else
                ไม่มีนักเรียนในห้องที่ปรึกษาของคุณ
            @endif
        </h4>
        @if($statusFilter)
            <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:1.25rem;">นักเรียนทุกคนมีสถานะปกติ หรือไม่มีข้อมูลตรงตามเงื่อนไขที่เลือก</p>
            <a href="{{ route('teacher.classroom.index', array_filter(['room' => $classroom], fn($v) => !is_null($v) && $v !== '')) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-users"></i> ดูนักเรียนทั้งหมด ({{ $statusCounts['all'] ?? 0 }} คน)
            </a>
        @endif
    </div>
    @endforelse
</div>

<!-- Parent Info Modal -->
<div id="parentModal" class="modal-backdrop" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,14,52,0.4); backdrop-filter:blur(4px); z-index:9999; justify-content:center; align-items:center;">
    <div class="card" style="width:90%; max-width:440px; border-radius:18px; box-shadow:0 20px 50px rgba(15,14,52,0.15); animation: modalAppear 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); border:none;">
        <div class="card-header-bar" style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); color: white; border-bottom: none; padding: 1.25rem 1.5rem;">
            <h3 style="color: white; margin: 0; display:flex; align-items:center; gap:0.5rem;"><i class="fas fa-id-card"></i> ข้อมูลผู้ปกครอง</h3>
            <button onclick="closeParentModal()" style="background:transparent; border:none; color:white; font-size:1.2rem; cursor:pointer;"><i class="fas fa-times"></i></button>
        </div>
        <div class="card-body-pad" style="padding:1.5rem;">
            <!-- Student Header Profile -->
            <div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.5rem; background:#f8fafc; padding:1rem; border-radius:12px; border:1px solid var(--border);">
                <div id="modalStudentPhoto" style="width:55px; height:55px; border-radius:10px; overflow:hidden; border:2px solid #e5e7eb; display:flex; align-items:center; justify-content:center; background:#f3f4f6; flex-shrink:0;">
                </div>
                <div style="flex:1; min-width:0;">
                    <div id="modalStudentName" style="font-weight:700; color:var(--text); font-size:0.95rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"></div>
                    <div id="modalStudentClass" style="font-size:0.78rem; color:var(--text-muted); margin-top:0.15rem;"></div>
                </div>
            </div>

            <!-- Parent Info Section -->
            <div style="display:flex; flex-direction:column; gap:1rem;">
                <div style="display:flex; align-items:flex-start; gap:0.75rem;">
                    <div style="width:36px; height:36px; border-radius:50%; background:rgba(6,4,234,0.06); color:var(--primary); display:flex; align-items:center; justify-content:center; font-size:0.95rem; flex-shrink:0;">
                        <i class="fas fa-user-friends"></i>
                    </div>
                    <div>
                        <span style="font-size:0.75rem; color:var(--text-muted); display:block;">ชื่อผู้ปกครอง</span>
                        <strong id="modalParentName" style="color:var(--text); font-size:0.92rem;"></strong>
                        <span id="modalParentRelationship" class="badge badge-navy" style="font-size:0.68rem; margin-left:0.35rem; padding:0.1rem 0.4rem; border-radius:4px; vertical-align:middle;"></span>
                    </div>
                </div>


                <div style="display:flex; align-items:flex-start; gap:0.75rem;">
                    <div style="width:36px; height:36px; border-radius:50%; background:rgba(16,185,129,0.08); color:var(--green); display:flex; align-items:center; justify-content:center; font-size:0.95rem; flex-shrink:0;">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div>
                        <span style="font-size:0.75rem; color:var(--text-muted); display:block;">เบอร์โทรศัพท์</span>
                        <strong id="modalParentPhone" style="color:var(--text); font-size:0.92rem;"></strong>
                    </div>
                </div>

                <div style="display:flex; align-items:flex-start; gap:0.75rem;">
                    <div style="width:36px; height:36px; border-radius:50%; background:rgba(240,134,24,0.06); color:var(--orange); display:flex; align-items:center; justify-content:center; font-size:0.95rem; flex-shrink:0;">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div>
                        <span style="font-size:0.75rem; color:var(--text-muted); display:block;">อีเมล</span>
                        <strong id="modalParentEmail" style="color:var(--text); font-size:0.92rem; word-break:break-all;"></strong>
                    </div>
                </div>

                <div style="display:flex; align-items:flex-start; gap:0.75rem;">
                    <div style="width:36px; height:36px; border-radius:50%; background:rgba(99,102,241,0.08); color:var(--purple); display:flex; align-items:center; justify-content:center; font-size:0.95rem; flex-shrink:0;">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div style="flex:1; min-width:0;">
                        <span style="font-size:0.75rem; color:var(--text-muted); display:block;">ที่อยู่</span>
                        <strong id="modalParentAddress" style="color:var(--text); font-size:0.9rem; line-height:1.45; font-weight:600; display:block; word-break:break-word;"></strong>
                    </div>
                </div>
            </div>

            <!-- Action buttons -->
            <div style="margin-top:2rem; display:flex; gap:0.75rem;">
                <a id="modalMsgBtn" href="#" class="btn btn-primary" style="flex:1; justify-content:center; font-size:0.82rem; padding:0.5rem 1rem;">
                    <i class="fas fa-comment-dots"></i> ส่งข้อความ
                </a>
                <button onclick="closeParentModal()" class="btn btn-outline" style="flex:1; justify-content:center; font-size:0.82rem; padding:0.5rem 1rem;">
                    ปิดหน้าต่าง
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes modalAppear {
        from { opacity: 0; transform: scale(0.95) translateY(10px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }
</style>
@endsection

@push('scripts')
<script>
function showParentModal(element) {
    var sName = element.getAttribute('data-student-name');
    var sId = element.getAttribute('data-student-id');
    var sClass = element.getAttribute('data-student-class');
    var sPhoto = element.getAttribute('data-student-photo');
    var pName = element.getAttribute('data-parent-name');
    var pRel = element.getAttribute('data-parent-relationship');
    var pPhone = element.getAttribute('data-parent-phone');
    var pEmail = element.getAttribute('data-parent-email');
    var pAddress = element.getAttribute('data-parent-address');
    var pUserId = element.getAttribute('data-parent-user-id');

    document.getElementById('modalStudentName').textContent = sName;
    document.getElementById('modalStudentClass').textContent = 'รหัส: ' + sId + ' | ชั้น ' + sClass;
    
    var photoWrap = document.getElementById('modalStudentPhoto');
    if (sPhoto) {
        photoWrap.innerHTML = '<img src="' + sPhoto + '" alt="" style="width:100%; height:100%; object-fit:cover;">';
    } else {
        photoWrap.innerHTML = '<i class="fas fa-user-graduate" style="color:#d1d5db; font-size:1.5rem;"></i>';
    }

    document.getElementById('modalParentName').textContent = pName;
    
    var relBadge = document.getElementById('modalParentRelationship');
    if (pRel && pRel !== '-') {
        relBadge.style.display = 'inline-flex';
        relBadge.textContent = pRel;
    } else {
        relBadge.style.display = 'none';
    }

    document.getElementById('modalParentPhone').textContent = pPhone || '-';
    document.getElementById('modalParentEmail').textContent = pEmail || '-';
    document.getElementById('modalParentAddress').textContent = pAddress || '-';

    var msgBtn = document.getElementById('modalMsgBtn');
    if (pUserId) {
        msgBtn.style.display = 'inline-flex';
        msgBtn.href = '{{ route('teacher.messages.create') }}?receiver=' + pUserId;
    } else {
        msgBtn.style.display = 'none';
    }

    var modal = document.getElementById('parentModal');
    modal.style.display = 'flex';
}

function closeParentModal() {
    document.getElementById('parentModal').style.display = 'none';
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    var modal = document.getElementById('parentModal');
    if (event.target === modal) {
        closeParentModal();
    }
});
</script>
@endpush