@extends('layouts.app')

@section('title', 'เขียนข้อความ')
@section('page-title', 'เขียนข้อความใหม่')

@section('content')
<div style="max-width:680px;">
    @php
        $fromParam = request('from');
        $forStudentId = request('for_student');
        $prevUrl = url()->previous();
        $isFromRisk = $fromParam === 'risk-students' || ($prevUrl && str_contains($prevUrl, 'risk-students'));

        if ($forStudentId) {
            $forStudent = \App\Models\Student::where('StudentID', $forStudentId)->first();
            if ($forStudent && $forStudent->user) {
                $createRoute = auth()->user()->Role === 'ครู' ? 'teacher.messages.create' : (auth()->user()->Role === 'ฝ่ายปกครอง' ? 'discipline.messages.create' : 'admin.messages.create');
                $backRoute = route($createRoute, [
                    'receiver' => $forStudent->user->UserID,
                    'from' => $isFromRisk ? 'risk-students' : $fromParam
                ]);
            }
        }

        if (!isset($backRoute)) {
            if ($isFromRisk) {
                $backRoute = route('discipline.risk-students');
            } else {
                $backRoute = match(auth()->user()->Role) {
                    'ฝ่ายปกครอง' => route('discipline.messages.index'),
                    'ครู'         => route('teacher.messages.index'),
                    'นักเรียน'    => route('student.messages.index'),
                    'ผู้ปกครอง'   => route('parent.messages.index'),
                    default       => '#',
                };
            }
        }
        $backText = 'ย้อนกลับ';

        $routeMap = [
            'ฝ่ายปกครอง' => 'discipline.messages.store',
            'ครู'         => 'teacher.messages.store',
            'นักเรียน'    => 'student.messages.store',
            'ผู้ปกครอง'   => 'parent.messages.store',
        ];
        $storeRoute = $routeMap[auth()->user()->Role] ?? 'discipline.messages.store';
    @endphp
    
    <a href="{{ $backRoute }}" class="btn btn-outline btn-sm" style="margin-bottom:1rem;">
        <i class="fas fa-arrow-left"></i> {{ $backText }}
    </a>

    <div class="card">
        <div class="card-header-bar">
            <h3><i class="fas fa-pen" style="color:var(--gold); margin-right:0.5rem"></i>เขียนข้อความ</h3>
        </div>
        <div class="card-body-pad">
            <form method="POST" action="{{ route($storeRoute) }}" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    @if(auth()->user()->Role === 'นักเรียน' || auth()->user()->Role === 'ผู้ปกครอง')
                        <label class="form-label">ครูประจำชั้น <span style="color:var(--red)">*</span></label>
                    @else
                        <label class="form-label">ถึง <span style="color:var(--red)">*</span></label>
                    @endif
                    @php
                        $recipients->loadMissing(['student', 'parentGuardian.student']);
                        $receiverParam = request('receiver');
                        $preselected = $receiverParam ? ($recipients->where('UserID', $receiverParam)->first() ?? \App\Models\User::with(['student', 'parentGuardian.student'])->where('UserID', $receiverParam)->first()) : null;
                    @endphp
                    @if($preselected)
                        <input type="hidden" name="ReceiverID" value="{{ $preselected->UserID }}">
                        <div style="background:#f8fafc; border:1px solid #cbd5e1; border-radius:8px; padding:0.75rem 1rem; display:flex; align-items:center; gap:0.75rem;">
                            <div style="width:40px; height:40px; border-radius:50%; background:var(--navy, #1e3a8a); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:1.1rem; flex-shrink:0;">
                                <i class="fas fa-user"></i>
                            </div>
                            <div style="flex:1;">
                                <div style="font-weight:700; color:#1e293b; font-size:0.95rem;">
                                    [{{ $preselected->Role === 'ครู' ? 'ครูประจำชั้น' : $preselected->Role }}] {{ $preselected->FullName }}
                                </div>
                                <div style="font-size:0.82rem; color:#64748b; margin-top:0.15rem;">
                                    @if($preselected->Role === 'นักเรียน' && $preselected->student)
                                        รหัสนักเรียน: <strong>{{ $preselected->student->StudentID }}</strong> @if($preselected->student->Classroom) | ชั้น: <strong>{{ $preselected->student->Classroom }}</strong> @endif
                                    @elseif($preselected->Role === 'ผู้ปกครอง')
                                        @php
                                            $targetStudId = request('for_student');
                                            if ($targetStudId) {
                                                $pStudents = \App\Models\Student::where('StudentID', $targetStudId)->get();
                                            } else {
                                                $allParents = \App\Models\ParentGuardian::where('UserID', $preselected->UserID)->get();
                                                $studIds = $allParents->pluck('StudentID')->filter()->toArray();
                                                $parIds = $allParents->pluck('ParentID')->filter()->toArray();
                                                $pStudents = \App\Models\Student::whereIn('StudentID', $studIds)
                                                    ->orWhereIn('ParentID', $parIds)
                                                    ->get();
                                            }
                                        @endphp
                                        ผู้ปกครองของ:
                                        @forelse($pStudents as $idx => $pStud)
                                            <strong>{{ $pStud->FullName }}</strong> (รหัสนักเรียน: <strong>{{ $pStud->StudentID }}</strong> @if($pStud->Classroom) | ชั้น: <strong>{{ $pStud->Classroom }}</strong>@endif){{ $idx < count($pStudents) - 1 ? ' | ' : '' }}
                                        @empty
                                            -
                                        @endforelse
                                    @else
                                        รหัสประจำตัว: <strong>{{ $preselected->Username }}</strong>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if($preselected->Role === 'นักเรียน' && $preselected->student)
                            @php
                                $parentRec = \App\Models\ParentGuardian::where('StudentID', $preselected->student->StudentID)->first()
                                    ?? $preselected->student->parent 
                                    ?? \App\Models\ParentGuardian::where('ParentID', $preselected->student->ParentID)->first();
                                $parentUsr = $parentRec ? $parentRec->user : null;
                            @endphp
                            @if($parentRec && $parentUsr)
                                <div style="margin-top:0.75rem; background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:0.85rem 1rem;">
                                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.5rem;">
                                        <div style="font-size:0.875rem; color:#1e40af; font-weight:600; display:flex; align-items:center; gap:0.4rem;">
                                            <i class="fas fa-user-shield" style="font-size:1.1rem; color:#2563eb;"></i>
                                            <span>ผู้ปกครองของนักเรียนคนนี้: <strong>{{ $parentRec->FullName }}</strong> ({{ $parentRec->Relationship ?? 'ผู้ปกครอง' }})</span>
                                        </div>
                                        <a href="{{ route(auth()->user()->Role === 'ครู' ? 'teacher.messages.create' : (auth()->user()->Role === 'ฝ่ายปกครอง' ? 'discipline.messages.create' : 'admin.messages.create'), ['receiver' => $parentUsr->UserID, 'for_student' => $preselected->student->StudentID, 'from' => $isFromRisk ? 'risk-students' : request('from')]) }}" class="btn btn-sm" style="background:#fff; border:1px solid #93c5fd; color:#1d4ed8; font-size:0.8rem; font-weight:600; padding:0.3rem 0.65rem; border-radius:6px; text-decoration:none;">
                                            <i class="fas fa-paper-plane"></i> เปลี่ยนเป็นส่งถึงผู้ปกครองคนนี้
                                        </a>
                                    </div>
                                    <div style="margin-top:0.6rem; border-top:1px dashed #93c5fd; padding-top:0.5rem;">
                                        <label style="cursor:pointer; font-size:0.85rem; font-weight:600; color:#1e3a8a; display:flex; align-items:center; gap:0.4rem;">
                                            <input type="checkbox" name="send_to_parent" value="1" style="accent-color:#1e3a8a; width:16px; height:16px;">
                                            <span>ส่งข้อความฉบับนี้ถึงผู้ปกครองของนักเรียนคนนี้ด้วย (ส่งให้ทั้งนักเรียนและผู้ปกครองพร้อมกัน)</span>
                                        </label>
                                    </div>
                                </div>
                            @endif
                        @endif
                    @else
                        @if(auth()->user()->Role !== 'นักเรียน' && auth()->user()->Role !== 'ผู้ปกครอง')
                            <div class="role-filter-container" style="margin-bottom: 0.6rem; display: flex; flex-wrap: wrap; gap: 0.35rem;">
                                <button type="button" class="btn-role-filter active" data-role="all">ทั้งหมด</button>
                                <button type="button" class="btn-role-filter" data-role="นักเรียน">นักเรียน</button>
                                <button type="button" class="btn-role-filter" data-role="ผู้ปกครอง">ผู้ปกครอง</button>
                            </div>
                            <div style="margin-bottom: 0.5rem; display: flex; gap: 0.5rem;">
                                <input type="text" id="recipientSearchInput" class="form-control" placeholder="พิมพ์ชื่อ, รหัสนักเรียน หรือรหัสผู้ปกครองเพื่อค้นหา..." style="flex: 1;">
                                <button type="button" id="clearSearchBtn" class="btn btn-outline" style="display: none; padding: 0.375rem 0.75rem;"><i class="fas fa-times"></i></button>
                            </div>
                        @endif
                        <select name="ReceiverID" id="receiverSelect" class="form-control" required>
                            @if(auth()->user()->Role === 'นักเรียน' || auth()->user()->Role === 'ผู้ปกครอง')
                                @if($recipients->isEmpty())
                                    <option value="" disabled selected>ไม่พบข้อมูลครูประจำชั้นของท่านในระบบ</option>
                                @elseif($recipients->count() === 1)
                                    <option value="" disabled>เลือกครูประจำชั้น</option>
                                @else
                                    <option value="">เลือกครูประจำชั้น</option>
                                @endif
                            @else
                                <option value="">เลือกผู้รับ</option>
                            @endif
                            @php
                                $grouped = $recipients->groupBy(function($item) {
                                    return $item->Role === 'ครู' ? 'ครูประจำชั้น' : $item->Role;
                                });
                            @endphp
                            @foreach($grouped as $roleGroupLabel => $userGroup)
                                <optgroup label="{{ $roleGroupLabel }}">
                                    @foreach($userGroup as $u)
                                        @php
                                            $extraSearch = '';
                                            $displayLabel = $u->FullName;
                                            $pUserId = '';
                                            $pName = '';
                                            $pRel = '';
                                            if ($u->Role === 'นักเรียน' && $u->student) {
                                                $displayLabel .= ' (รหัสนักเรียน: ' . $u->student->StudentID . ')';
                                                $extraSearch = $u->student->StudentID;
                                                $pRec = \App\Models\ParentGuardian::where('StudentID', $u->student->StudentID)->first()
                                                    ?? $u->student->parent 
                                                    ?? \App\Models\ParentGuardian::where('ParentID', $u->student->ParentID)->first();
                                                if ($pRec && $pRec->user) {
                                                    $pUserId = $pRec->user->UserID;
                                                    $pName = $pRec->FullName;
                                                    $pRel = $pRec->Relationship ?? 'ผู้ปกครอง';
                                                }
                                            } elseif ($u->Role === 'ผู้ปกครอง') {
                                                $allParents = \App\Models\ParentGuardian::where('UserID', $u->UserID)->get();
                                                $studIds = $allParents->pluck('StudentID')->filter()->toArray();
                                                $parIds = $allParents->pluck('ParentID')->filter()->toArray();
                                                $pStudents = \App\Models\Student::whereIn('StudentID', $studIds)
                                                    ->orWhereIn('ParentID', $parIds)
                                                    ->get();
                                                $studStrList = [];
                                                foreach ($pStudents as $ps) {
                                                    $studStrList[] = $ps->FullName . ' (' . $ps->StudentID . ')';
                                                }
                                                if (count($studStrList) > 0) {
                                                    $displayLabel .= ' (ผู้ปกครองของ: ' . implode(', ', $studStrList) . ')';
                                                    $extraSearch = implode(' ', array_merge($studIds, array_map(fn($ps) => $ps->FullName, $pStudents->all())));
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
                                        <option value="{{ $u->UserID }}" {{ (old('ReceiverID') == $u->UserID || ((auth()->user()->Role === 'นักเรียน' || auth()->user()->Role === 'ผู้ปกครอง') && $recipients->count() === 1)) ? 'selected' : '' }} data-search="{{ $extraSearch }}" data-parent-user-id="{{ $pUserId }}" data-parent-name="{{ $pName }}" data-parent-rel="{{ $pRel }}">
                                            {{ $displayLabel }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>

                        <div id="dropdownParentBanner" style="display:none; margin-top:0.75rem; background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:0.85rem 1rem;">
                            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.5rem;">
                                <div style="font-size:0.875rem; color:#1e40af; font-weight:600; display:flex; align-items:center; gap:0.4rem;">
                                    <i class="fas fa-user-shield" style="font-size:1.1rem; color:#2563eb;"></i>
                                    <span>ผู้ปกครองของนักเรียนคนนี้: <strong id="bannerParentName"></strong> <span id="bannerParentRelation"></span></span>
                                </div>
                                <button type="button" id="btnSwitchToParent" class="btn btn-sm" style="background:#fff; border:1px solid #93c5fd; color:#1d4ed8; font-size:0.8rem; font-weight:600; padding:0.3rem 0.65rem; border-radius:6px;">
                                    <i class="fas fa-paper-plane"></i> เปลี่ยนเป็นส่งถึงผู้ปกครองคนนี้
                                </button>
                            </div>
                            <div style="margin-top:0.6rem; border-top:1px dashed #93c5fd; padding-top:0.5rem;">
                                <label style="cursor:pointer; font-size:0.85rem; font-weight:600; color:#1e3a8a; display:flex; align-items:center; gap:0.4rem;">
                                    <input type="checkbox" name="send_to_parent" id="dropdownSendToParent" value="1" style="accent-color:#1e3a8a; width:16px; height:16px;">
                                    <span>ส่งข้อความฉบับนี้ถึงผู้ปกครองของนักเรียนคนนี้ด้วย (ส่งให้ทั้งนักเรียนและผู้ปกครองพร้อมกัน)</span>
                                </label>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="form-group">
                    <label class="form-label">ข้อความ <span style="color:var(--red)">*</span></label>
                    <textarea name="Content" class="form-control" rows="8" required
                              placeholder="พิมพ์ข้อความที่ต้องการส่ง...">{{ old('Content') }}</textarea>
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
                <div style="display:flex; gap:0.75rem; margin-top:1.5rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> ส่ง
                    </button>
                    <a href="{{ $backRoute }}" class="btn btn-outline">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
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
        const originalGroups = Array.from(select.querySelectorAll('optgroup'));
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
            
            originalGroups.forEach(group => {
                const groupRole = group.getAttribute('data-role');
                if (activeRole !== 'all' && groupRole !== activeRole) {
                    return;
                }
                
                const options = Array.from(group.querySelectorAll('option'));
                const matchedOptions = options.filter(opt => {
                    const text = opt.text.toLowerCase();
                    const searchAttr = opt.getAttribute('data-search') ? opt.getAttribute('data-search').toLowerCase() : '';
                    return text.includes(query) || searchAttr.includes(query);
                });
                
                if (matchedOptions.length > 0) {
                    const groupClone = document.createElement('optgroup');
                    groupClone.label = group.label;
                    groupClone.setAttribute('data-role', groupRole);
                    matchedOptions.forEach(opt => {
                        groupClone.appendChild(opt.cloneNode(true));
                    });
                    select.appendChild(groupClone);
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

    // ── Receiver Dropdown Parent Detection ───────────────────────────
    const receiverSelect = document.getElementById('receiverSelect');
    const dropdownParentBanner = document.getElementById('dropdownParentBanner');
    const bannerParentName = document.getElementById('bannerParentName');
    const bannerParentRelation = document.getElementById('bannerParentRelation');
    const btnSwitchToParent = document.getElementById('btnSwitchToParent');
    let currentParentUserId = null;

    if (receiverSelect && dropdownParentBanner) {
        function checkParentInfo() {
            const selectedOpt = receiverSelect.options[receiverSelect.selectedIndex];
            const pUserId = selectedOpt ? selectedOpt.getAttribute('data-parent-user-id') : null;
            const pName = selectedOpt ? selectedOpt.getAttribute('data-parent-name') : null;
            const pRel = selectedOpt ? selectedOpt.getAttribute('data-parent-rel') : null;

            if (pUserId && pName) {
                currentParentUserId = pUserId;
                bannerParentName.textContent = pName;
                bannerParentRelation.textContent = pRel ? `(${pRel})` : '';
                dropdownParentBanner.style.display = 'block';
            } else {
                currentParentUserId = null;
                dropdownParentBanner.style.display = 'none';
            }
        }

        receiverSelect.addEventListener('change', checkParentInfo);
        checkParentInfo();

        if (btnSwitchToParent) {
            btnSwitchToParent.addEventListener('click', function() {
                if (currentParentUserId) {
                    receiverSelect.value = currentParentUserId;
                    receiverSelect.dispatchEvent(new Event('change'));
                }
            });
        }
    }
});
</script>
@endpush
@endsection
