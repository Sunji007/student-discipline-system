@extends('layouts.app')

@section('title', 'แชทข้อความ')
@section('page-title', 'กล่องแชทข้อความ')

@section('content')
<div class="page-header" style="display:flex; align-items:center; justify-content:space-between;">
    <div>
        <h2>กล่องแชทข้อความ</h2>
        @if($unreadCount > 0)
            <p style="color:var(--red);">มีข้อความที่ยังไม่ได้อ่าน {{ $unreadCount }} ข้อความ</p>
        @else
            <p>ไม่มีข้อความใหม่</p>
        @endif
    </div>
    <a href="{{ url()->current() }}?compose=1" class="btn btn-primary">
        <i class="fas fa-plus"></i> เพิ่ม
    </a>
</div>

@if(request('compose'))
{{-- Compose Form --}}
<div class="card" style="margin-bottom:1rem;">
    <div class="card-header-bar">
        <h3><i class="fas fa-pen" style="color:var(--gold); margin-right:0.5rem"></i>เขียนข้อความใหม่</h3>
    </div>
    <div class="card-body-pad">
        @php
            $routeMap = [
                'ผู้ดูแลระบบ' => 'admin.messages.store',
                'ฝ่ายปกครอง' => 'discipline.messages.store',
                'ครู'         => 'teacher.messages.store',
                'นักเรียน'    => 'student.messages.store',
                'ผู้ปกครอง'   => 'parent.messages.store',
            ];
            $storeRoute = $routeMap[auth()->user()->Role] ?? 'discipline.messages.store';
        @endphp
        <form method="POST" action="{{ route($storeRoute) }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                @if(auth()->user()->Role === 'นักเรียน' || auth()->user()->Role === 'ผู้ปกครอง')
                    <label class="form-label">ครูประจำชั้น <span style="color:var(--red)">*</span></label>
                @else
                    <label class="form-label">ถึง <span style="color:var(--red)">*</span></label>
                @endif
                @php
                    $colId = 'UserID';
                    $colStatus = 'Status';
                    $colRole = 'Role';
                    if (isset($recipients)) {
                        $allRecps = $recipients;
                    } elseif (auth()->user()->Role === 'ครู') {
                        $tRooms = auth()->user()->teacher?->advisory_rooms ?? [];
                        $tStudents = \App\Models\Student::inAdvisoryRoom($tRooms)->get();
                        $tStudUserIds = $tStudents->pluck('UserID')->filter()->toArray();
                        $tParUserIds = \App\Models\ParentGuardian::whereIn('StudentID', $tStudents->pluck('StudentID')->filter()->toArray())
                            ->orWhereIn('ParentID', $tStudents->pluck('ParentID')->filter()->toArray())
                            ->pluck('UserID')->filter()->toArray();
                        $allRecps = \App\Models\User::with(['student', 'parentGuardian.student'])
                            ->whereIn('UserID', array_unique(array_merge($tStudUserIds, $tParUserIds)))
                            ->where(function($q){ $q->where('Status', 'ปกติ')->orWhereNull('Status')->orWhere('Status', 'active')->orWhere('Status', ''); })
                            ->orderBy($colRole)->orderBy('FirstName')->orderBy('LastName')->get();
                    } else {
                        $allRecps = \App\Models\User::with(['student', 'parentGuardian.student'])->where($colId, '!=', auth()->user()->UserID)->where(function($q){ $q->where('Status', 'ปกติ')->orWhereNull('Status')->orWhere('Status', 'active')->orWhere('Status', ''); })->orderBy($colRole)->orderBy('FirstName')->orderBy('LastName')->get();
                    }
                    $receiverParam = request('receiver');
                    $preselected = $receiverParam ? ($allRecps->where($colId, $receiverParam)->first() ?? \App\Models\User::with(['student', 'parentGuardian.student'])->where('UserID', $receiverParam)->first()) : null;
                @endphp
                @if($preselected)
                    <input type="hidden" name="ReceiverID" value="{{ $preselected->UserID }}">
                    <div style="background:#f8fafc; border:1px solid #cbd5e1; border-radius:8px; padding:0.75rem 1rem; display:flex; align-items:center; gap:0.75rem;">
                        <div style="width:40px; height:40px; border-radius:50%; background:var(--navy, #1e3a8a); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:1.1rem; flex-shrink:0;">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <div style="font-weight:700; color:#1e293b; font-size:0.95rem;">
                                [{{ $preselected->Role === 'ครู' ? 'ครูประจำชั้น' : $preselected->Role }}] {{ $preselected->FullName }}
                            </div>
                            <div style="font-size:0.82rem; color:#64748b; margin-top:0.15rem;">
                                @if($preselected->Role === 'นักเรียน' && $preselected->student)
                                    รหัสนักเรียน: <strong>{{ $preselected->student->StudentID }}</strong> @if($preselected->student->Classroom) | ชั้น: <strong>{{ $preselected->student->Classroom }}</strong> @endif
                                @elseif($preselected->Role === 'ผู้ปกครอง' && $preselected->parentGuardian)
                                    @php
                                        $pStudent = $preselected->parentGuardian->student 
                                            ?? \App\Models\Student::where('StudentID', $preselected->parentGuardian->StudentID)->first()
                                            ?? \App\Models\Student::where('ParentID', $preselected->parentGuardian->ParentID)->first();
                                    @endphp
                                    ผู้ปกครองของ: <strong>{{ $pStudent ? $pStudent->FullName : '-' }}</strong> @if($pStudent && $pStudent->StudentID) (รหัสนักเรียน: <strong>{{ $pStudent->StudentID }}</strong> @if($pStudent->Classroom) | ชั้น: <strong>{{ $pStudent->Classroom }}</strong>@endif)@endif
                                @else
                                    รหัสประจำตัว: <strong>{{ $preselected->Username }}</strong>
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    @if(auth()->user()->Role !== 'นักเรียน' && auth()->user()->Role !== 'ผู้ปกครอง')
                        <div class="role-filter-container" style="margin-bottom: 0.6rem; display: flex; flex-wrap: wrap; gap: 0.35rem;">
                            <button type="button" class="btn-role-filter active" data-role="all">ทั้งหมด</button>
                            <button type="button" class="btn-role-filter" data-role="นักเรียน">นักเรียน</button>
                            <button type="button" class="btn-role-filter" data-role="ผู้ปกครอง">ผู้ปกครอง</button>
                            @if(auth()->user()->Role !== 'ครู')
                            <button type="button" class="btn-role-filter" data-role="ครู">ครู / ฝ่ายปกครอง</button>
                            @endif
                        </div>
                        <div style="margin-bottom: 0.5rem; display: flex; gap: 0.5rem;">
                            <input type="text" id="recipientSearchInput" class="form-control" placeholder="พิมพ์ชื่อ, รหัสนักเรียน หรือชื่อผู้ปกครองเพื่อค้นหา..." style="flex: 1;">
                            <button type="button" id="clearSearchBtn" class="btn btn-outline" style="display: none; padding: 0.375rem 0.75rem;"><i class="fas fa-times"></i></button>
                        </div>
                    @endif
                    <select name="ReceiverID" id="receiverSelect" class="form-control" required>
                        @if(auth()->user()->Role === 'นักเรียน' || auth()->user()->Role === 'ผู้ปกครอง')
                            <option value="">เลือกครูประจำชั้น</option>
                        @else
                            <option value="">เลือกผู้รับ</option>
                        @endif
                        @php
                            $grouped = $allRecps->groupBy(function($item) {
                                return $item->Role === 'ครู' ? 'ครูประจำชั้น' : $item->Role;
                            });
                        @endphp
                        @foreach($grouped as $roleGroupLabel => $userGroup)
                            <optgroup label="{{ $roleGroupLabel }}" data-role="{{ $roleGroupLabel }}">
                                @foreach($userGroup as $u)
                                    @php
                                        $extraSearch = '';
                                        $displayLabel = $u->FullName;
                                        if ($u->Role === 'นักเรียน' && $u->student) {
                                            $displayLabel .= ' (รหัสนักเรียน: ' . $u->student->StudentID . ')';
                                            $extraSearch = $u->student->StudentID;
                                        } elseif ($u->Role === 'ผู้ปกครอง' && $u->parentGuardian) {
                                            $pStudent = $u->parentGuardian->student 
                                                ?? \App\Models\Student::where('StudentID', $u->parentGuardian->StudentID)->first()
                                                ?? \App\Models\Student::where('ParentID', $u->parentGuardian->ParentID)->first();
                                            if ($pStudent) {
                                                $displayLabel .= ' (ผู้ปกครองของ: ' . $pStudent->FullName . ' - ' . $pStudent->StudentID . ')';
                                                $extraSearch = $pStudent->StudentID . ' ' . $pStudent->FullName;
                                            } else {
                                                $displayLabel .= ' (ผู้ปกครอง)';
                                            }
                                        } elseif (in_array($u->Role, ['ครู', 'ฝ่ายปกครอง'])) {
                                            if ($u->teacher && !empty($u->teacher->advisory_rooms)) {
                                                $rooms = implode(', ', $u->teacher->advisory_rooms);
                                                $displayLabel .= ' (ครูประจำชั้น ห้อง ม.' . $rooms . ')';
                                            } else {
                                                $displayLabel .= ' (' . ($u->Role === 'ครู' ? 'ครูประจำชั้น' : $u->Role) . ')';
                                            }
                                        }
                                    @endphp
                                    <option value="{{ $u->UserID }}" data-search="{{ $extraSearch }}">
                                        {{ $displayLabel }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                @endif
            </div>
            <div class="form-group">
                <label class="form-label">ข้อความ <span style="color:var(--red)">*</span></label>
                <textarea name="Content" class="form-control" rows="5" required
                          placeholder="พิมพ์ข้อความที่ต้องการส่ง..."></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">แนบไฟล์ (ขนาดไม่เกิน 10 MB/ไฟล์, แนบได้หลายไฟล์)</label>
                <input type="file" name="attachments[]" class="form-control" id="attachmentInput" multiple style="display:none;">
                
                <div id="fileUploadDropzone" onclick="document.getElementById('attachmentInput').click()" style="border:2px dashed #cbd5e1; border-radius:8px; padding:1.25rem; text-align:center; background:#f8fafc; cursor:pointer; transition:all 0.2s ease;">
                    <i class="fas fa-cloud-upload-alt" style="font-size:1.8rem; color:#64748b; margin-bottom:0.4rem;"></i>
                    <div style="font-size:0.88rem; font-weight:600; color:#334155;">คลิกเพื่อเลือกไฟล์ หรือลากไฟล์มาวางที่นี่</div>
                    <div style="font-size:0.75rem; color:#94a3b8; margin-top:0.2rem;">แนบได้หลายไฟล์พร้อมกัน (สามารถเลือกและกดลบรายไฟล์ที่ไม่ต้องการได้)</div>
                </div>

                <div id="filePreviewList" style="margin-top:0.75rem; display:flex; flex-direction:column; gap:0.4rem;"></div>
            </div>
            <div style="display:flex; gap:0.75rem;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> ส่ง
                </button>
                <a href="{{ url()->current() }}" class="btn btn-outline">ยกเลิก</a>
            </div>
        </form>
    </div>
</div>
@endif

<div class="responsive-grid-2">
    {{-- Inbox --}}
    <div class="card">
        <div class="card-header-bar">
            <h3><i class="fas fa-inbox" style="color:var(--gold); margin-right:0.5rem"></i>กล่องขาเข้า</h3>
            @if($unreadCount > 0)
                <span class="badge badge-red">{{ $unreadCount }} ใหม่</span>
            @endif
        </div>
        <div>
            @forelse($inbox as $msg)
            @php
                $readRoute = match(auth()->user()->Role) {
                    'ผู้ดูแลระบบ' => route('admin.messages.show', $msg->MessageID),
                    'ฝ่ายปกครอง' => route('discipline.messages.show', $msg->MessageID),
                    'ครู'         => route('teacher.messages.show', $msg->MessageID),
                    'นักเรียน'    => route('student.messages.show', $msg->MessageID),
                    'ผู้ปกครอง'   => route('parent.messages.show', $msg->MessageID),
                    default       => '#',
                };
            @endphp
            <a href="{{ $readRoute }}" style="display:block; padding:0.875rem 1.25rem; border-bottom:1px solid #f0ece4; text-decoration:none; transition:background 0.15s;
               {{ !$msg->IsRead ? 'background:#fffdf7;' : '' }}"
               class="msg-row">
                <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:0.75rem;">
                    <div style="flex:1; min-width:0;">
                        <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.25rem;">
                            @if(!$msg->IsRead)
                                <span style="width:8px; height:8px; background:var(--gold); border-radius:50%; flex-shrink:0;"></span>
                            @endif
                            <span style="font-size:0.85rem; font-weight:{{ !$msg->IsRead ? '600' : '400' }}; color:var(--navy);">
                                {{ $msg->sender->FullName }}
                            </span>
                            <span class="badge badge-gray" style="font-size:0.65rem;">{{ $msg->sender->Role === 'ครู' ? 'ครูประจำชั้น' : $msg->sender->Role }}</span>
                        </div>
                        <p style="font-size:0.8rem; color:var(--text-muted); margin:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                            {{ \Str::limit($msg->Content, 70) }}
                        </p>
                    </div>
                    <div style="font-size:0.72rem; color:var(--text-muted); white-space:nowrap; flex-shrink:0;">
                        {{ \Carbon\Carbon::parse($msg->SentDate)->diffForHumans() }}
                    </div>
                </div>
            </a>
            @empty
            <div style="text-align:center; padding:2rem; color:var(--text-muted); font-size:0.875rem;">
                <i class="fas fa-inbox" style="font-size:1.5rem; opacity:0.3; margin-bottom:0.5rem; display:block;"></i>
                ไม่มีข้อความ
            </div>
            @endforelse
        </div>
        <div style="padding:0.75rem 1.25rem; border-top:1px solid #ede8e0;">
            {{ $inbox->links() }}
        </div>
    </div>

    {{-- Sent --}}
    <div class="card">
        <div class="card-header-bar">
            <h3><i class="fas fa-paper-plane" style="color:var(--gold); margin-right:0.5rem"></i>ข้อความที่ส่ง</h3>
        </div>
        <div>
            @forelse($sent as $msg)
            @php
                $readRoute = match(auth()->user()->Role) {
                    'ผู้ดูแลระบบ' => route('admin.messages.show', $msg->MessageID),
                    'ฝ่ายปกครอง' => route('discipline.messages.show', $msg->MessageID),
                    'ครู'         => route('teacher.messages.show', $msg->MessageID),
                    'นักเรียน'    => route('student.messages.show', $msg->MessageID),
                    'ผู้ปกครอง'   => route('parent.messages.show', $msg->MessageID),
                    default       => '#',
                };
            @endphp
            <a href="{{ $readRoute }}" style="display:block; padding:0.875rem 1.25rem; border-bottom:1px solid #f0ece4; text-decoration:none; color:inherit; transition:background 0.15s;" class="msg-row">
                <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:0.75rem;">
                    <div style="flex:1; min-width:0;">
                        <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.25rem;">
                            <span style="font-size:0.78rem; color:var(--text-muted);">ถึง:</span>
                            <span style="font-size:0.85rem; font-weight:500; color:var(--navy);">
                                {{ $msg->receiver->FullName }}
                            </span>
                            <span class="badge badge-gray" style="font-size:0.65rem;">{{ $msg->receiver->Role === 'ครู' ? 'ครูประจำชั้น' : $msg->receiver->Role }}</span>
                        </div>
                        <p style="font-size:0.8rem; color:var(--text-muted); margin:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                            {{ \Str::limit($msg->Content, 70) }}
                        </p>
                    </div>
                    <div style="text-align:right; flex-shrink:0;">
                        <div style="font-size:0.72rem; color:var(--text-muted);">
                            {{ \Carbon\Carbon::parse($msg->SentDate)->diffForHumans() }}
                        </div>
                        <span style="font-size:0.7rem; color:{{ $msg->IsRead ? 'var(--green)' : 'var(--text-muted)' }}">
                            <i class="fas fa-check{{ $msg->IsRead ? '-double' : '' }}"></i>
                            {{ $msg->IsRead ? 'อ่านแล้ว' : 'ยังไม่อ่าน' }}
                        </span>
                    </div>
                </div>
            </a>
            @empty
            <div style="text-align:center; padding:2rem; color:var(--text-muted); font-size:0.875rem;">
                <i class="fas fa-paper-plane" style="font-size:1.5rem; opacity:0.3; margin-bottom:0.5rem; display:block;"></i>
                ยังไม่มีข้อความที่ส่ง
            </div>
            @endforelse
        </div>
        <div style="padding:0.75rem 1.25rem; border-top:1px solid #ede8e0;">
            {{ $sent->links() }}
        </div>
    </div>
</div>

@push('styles')
<style>
.msg-row:hover { background: #faf8f4 !important; }
.btn-role-filter {
    cursor: pointer;
    transition: all 0.2s ease;
    border: 1px solid #cbd5e1;
    background: #f8fafc;
    color: #64748b;
    font-weight: 500;
    padding: 0.25rem 0.75rem;
    font-size: 0.8rem;
    border-radius: 20px;
    outline: none;
}
.btn-role-filter:hover {
    background: #f1f5f9;
    color: #475569;
}
.btn-role-filter.active {
    background: var(--primary-gradient, linear-gradient(135deg, #4224B8 0%, #0604EA 100%)) !important;
    color: #ffffff !important;
    border-color: transparent !important;
    box-shadow: 0 2px 4px rgba(6, 4, 234, 0.15);
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('recipientSearchInput');
    const select = document.getElementById('receiverSelect');
    const clearBtn = document.getElementById('clearSearchBtn');
    const filterButtons = document.querySelectorAll('.btn-role-filter');
    
    if (select) {
        const defaultOpt = select.querySelector('option[value=""]');
        const originalGroupsData = Array.from(select.querySelectorAll('optgroup')).map(group => {
            return {
                label: group.label,
                role: group.getAttribute('data-role') || group.label,
                options: Array.from(group.querySelectorAll('option')).map(opt => ({
                    value: opt.value,
                    text: opt.text,
                    search: opt.getAttribute('data-search') || ''
                }))
            };
        });
        let activeRole = 'all';
        
        function applyFilters() {
            const query = searchInput ? searchInput.value.trim().toLowerCase() : '';
            if (clearBtn) {
                clearBtn.style.display = query === '' ? 'none' : 'block';
            }
            
            select.innerHTML = '';
            if (defaultOpt) {
                select.appendChild(defaultOpt.cloneNode(true));
            }
            
            originalGroupsData.forEach(group => {
                if (activeRole !== 'all') {
                    if (activeRole === 'ครู') {
                        if (!['ครู', 'ครูประจำชั้น', 'ฝ่ายปกครอง'].includes(group.role)) return;
                    } else if (group.role !== activeRole) {
                        return;
                    }
                }
                
                const matchedOptions = group.options.filter(opt => {
                    if (!query) return true;
                    const text = opt.text.toLowerCase();
                    const searchAttr = opt.search.toLowerCase();
                    return text.includes(query) || searchAttr.includes(query);
                });
                
                if (matchedOptions.length > 0) {
                    const groupEl = document.createElement('optgroup');
                    groupEl.label = group.label;
                    groupEl.setAttribute('data-role', group.role);
                    matchedOptions.forEach(opt => {
                        const optEl = document.createElement('option');
                        optEl.value = opt.value;
                        optEl.textContent = opt.text;
                        if (opt.search) optEl.setAttribute('data-search', opt.search);
                        groupEl.appendChild(optEl);
                    });
                    select.appendChild(groupEl);
                }
            });
        }
        
        if (searchInput) {
            searchInput.addEventListener('input', applyFilters);
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault(); // Prevent form submission
                    
                    const query = searchInput.value.trim().toLowerCase();
                    const isNumeric = /^\d+$/.test(query);
                    
                    const options = select.querySelectorAll('option');
                    let match = null;
                    
                    if (isNumeric) {
                        // Look for exact match in data-search (Student ID / Parent ID)
                        for (let i = 0; i < options.length; i++) {
                            const searchAttr = options[i].getAttribute('data-search') ? options[i].getAttribute('data-search').trim().toLowerCase() : '';
                            if (searchAttr === query) {
                                match = options[i];
                                break;
                            }
                        }
                    } else {
                        // Look for first partial match in text or data-search
                        for (let i = 0; i < options.length; i++) {
                            if (options[i].value !== '') {
                                const text = options[i].text.toLowerCase();
                                const searchAttr = options[i].getAttribute('data-search') ? options[i].getAttribute('data-search').toLowerCase() : '';
                                if (text.includes(query) || searchAttr.includes(query)) {
                                    match = options[i];
                                    break;
                                }
                            }
                        }
                    }
                    
                    if (match) {
                        select.value = match.value;
                        select.dispatchEvent(new Event('change'));
                        
                        // Focus on message textarea
                        const textarea = document.querySelector('textarea[name="Content"]');
                        if (textarea) {
                            textarea.focus();
                        }
                    } else {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'warning',
                                title: 'ไม่พบข้อมูลผู้รับ',
                                text: 'ไม่พบรหัสประจำตัว หรือรายชื่อผู้รับที่ตรงกับข้อมูลที่คุณพิมพ์ กรุณาตรวจสอบอีกครั้ง',
                                confirmButtonText: 'ตกลง',
                                buttonsStyling: false,
                                customClass: {
                                    confirmButton: 'btn btn-primary btn-swal-confirm'
                                }
                            });
                        } else {
                            alert('ไม่พบข้อมูลผู้รับ');
                        }
                    }
                }
            });
        }
        
        filterButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                filterButtons.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                activeRole = this.getAttribute('data-role');
                applyFilters();
            });
        });
        
        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                if (searchInput) searchInput.value = '';
                clearBtn.style.display = 'none';
                applyFilters();
            });
        }
    }

    // ── Multi-File Drag & Drop and Individual File Removal ───────────
    const fileInput = document.getElementById('attachmentInput');
    const previewList = document.getElementById('filePreviewList');
    const dropzone = document.getElementById('fileUploadDropzone');

    if (fileInput && previewList) {
        let dt = new DataTransfer();

        fileInput.addEventListener('change', function() {
            for (let i = 0; i < this.files.length; i++) {
                dt.items.add(this.files[i]);
            }
            fileInput.files = dt.files;
            renderFilePreview();
        });

        if (dropzone) {
            ['dragenter', 'dragover'].forEach(eventName => {
                dropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.style.borderColor = 'var(--navy,#1e3a8a)';
                    dropzone.style.background = '#eff6ff';
                }, false);
            });
            ['dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.style.borderColor = '#cbd5e1';
                    dropzone.style.background = '#f8fafc';
                }, false);
            });
            dropzone.addEventListener('drop', (e) => {
                const droppedFiles = e.dataTransfer.files;
                for (let i = 0; i < droppedFiles.length; i++) {
                    dt.items.add(droppedFiles[i]);
                }
                fileInput.files = dt.files;
                renderFilePreview();
            });
        }

        function renderFilePreview() {
            previewList.innerHTML = '';
            if (dt.files.length === 0) return;

            Array.from(dt.files).forEach((file, index) => {
                const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
                const fileCard = document.createElement('div');
                fileCard.style.cssText = 'display:flex; align-items:center; justify-content:space-between; background:#fff; border:1px solid #e2e8f0; border-radius:6px; padding:0.5rem 0.75rem; font-size:0.825rem; box-shadow:0 1px 3px rgba(0,0,0,0.05);';
                
                let iconClass = 'fa-file-alt';
                if (file.type.startsWith('image/')) iconClass = 'fa-file-image';
                else if (file.type.includes('pdf')) iconClass = 'fa-file-pdf';
                else if (file.type.includes('word') || file.type.includes('document')) iconClass = 'fa-file-word';
                else if (file.type.includes('zip') || file.type.includes('rar')) iconClass = 'fa-file-archive';

                const fileUrl = URL.createObjectURL(file);

                fileCard.innerHTML = `
                    <a href="${fileUrl}" target="_blank" title="คลิกเพื่อเปิดดูไฟล์ตัวอย่าง" style="display:flex; align-items:center; gap:0.5rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:85%; text-decoration:none; color:inherit; cursor:pointer;" onmouseover="this.querySelector('.file-name-text').style.textDecoration='underline'; this.querySelector('.file-name-text').style.color='var(--navy,#1e3a8a)';" onmouseout="this.querySelector('.file-name-text').style.textDecoration='none'; this.querySelector('.file-name-text').style.color='#1e293b';">
                        <i class="fas ${iconClass}" style="color:var(--navy,#1e3a8a); font-size:1rem; flex-shrink:0;"></i>
                        <span class="file-name-text" style="font-weight:600; color:#1e293b; overflow:hidden; text-overflow:ellipsis; transition:color 0.15s ease;">${file.name}</span>
                        <span style="font-size:0.72rem; color:#94a3b8; flex-shrink:0;">(${sizeMB} MB)</span>
                    </a>
                    <button type="button" class="btn-remove-file" data-index="${index}" style="background:#fef2f2; border:1px solid #fca5a5; color:#ef4444; font-size:0.78rem; cursor:pointer; padding:0.25rem 0.5rem; border-radius:4px; transition:all 0.2s; font-weight:600; display:flex; align-items:center; gap:0.25rem;" title="ลบไฟล์นี้">
                        <i class="fas fa-times"></i> ลบ
                    </button>
                `;

                previewList.appendChild(fileCard);
            });

            previewList.querySelectorAll('.btn-remove-file').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const idx = parseInt(this.getAttribute('data-index'));
                    removeFileAtIndex(idx);
                });
            });
        }

        function removeFileAtIndex(index) {
            const newDt = new DataTransfer();
            Array.from(dt.files).forEach((file, i) => {
                if (i !== index) {
                    newDt.items.add(file);
                }
            });
            dt = newDt;
            fileInput.files = dt.files;
            renderFilePreview();
        }
    }
});
</script>
@endpush
@endsection