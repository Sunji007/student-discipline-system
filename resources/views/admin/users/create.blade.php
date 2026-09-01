@extends('layouts.app')

@section('title', 'เพิ่มผู้ใช้งาน')
@section('page-title', 'เพิ่มผู้ใช้งานใหม่')

@section('content')
<div style="max-width:680px;">
    <div class="card">
        <div class="card-header-bar">
            <h3>ข้อมูลผู้ใช้งาน</h3>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline btn-sm">
                <i class="fas fa-arrow-left"></i> ย้อนกลับ
            </a>
        </div>
        <div class="card-body-pad">
            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf

                @if ($errors->any())
                    <div class="alert alert-danger" style="margin-bottom: 1.5rem; padding: 1rem; background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; border-radius: 8px;">
                        <ul style="margin: 0; padding-left: 1.25rem;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- ชื่อ-นามสกุล ภาษาไทย --}}
                <p style="font-size:0.78rem; font-weight:700; color:var(--gold); text-transform:uppercase; letter-spacing:0.08em; margin-bottom:0.5rem;">ข้อมูลส่วนตัว</p>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">ชื่อจริง (ภาษาไทย) <span style="color:var(--red)">*</span></label>
                        <div style="display: flex; gap: 0.5rem;">
                            <select name="prefix" class="form-control" style="width: 120px; flex-shrink: 0;" required>
                                <option value="">คำนำหน้า</option>
                                <option value="นาย" {{ old('prefix') === 'นาย' ? 'selected' : '' }}>นาย</option>
                                <option value="นาง" {{ old('prefix') === 'นาง' ? 'selected' : '' }}>นาง</option>
                                <option value="นางสาว" {{ old('prefix') === 'นางสาว' ? 'selected' : '' }}>นางสาว</option>
                                <option value="ด.ช." {{ old('prefix') === 'ด.ช.' ? 'selected' : '' }}>ด.ช.</option>
                                <option value="ด.ญ." {{ old('prefix') === 'ด.ญ.' ? 'selected' : '' }}>ด.ญ.</option>
                            </select>
                            <input type="text" name="FirstName" id="firstNameTH" class="form-control {{ $errors->has('FirstName') ? 'is-invalid' : '' }}"
                                   value="{{ old('FirstName') }}" placeholder="เช่น นูรีดา" 
                                   oninput="this.value = this.value.replace(/[^ก-๙]/g, '')" required style="flex: 1;">
                        </div>
                        @error('FirstName')<div class="invalid-feedback" style="display: block;">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">นามสกุล (ภาษาไทย) <span style="color:var(--red)">*</span></label>
                        <input type="text" name="LastName" id="lastNameTH" class="form-control {{ $errors->has('LastName') ? 'is-invalid' : '' }}"
                               value="{{ old('LastName') }}" placeholder="เช่น สาและ" 
                               oninput="this.value = this.value.replace(/[^ก-๙]/g, '')" required>
                         @error('LastName')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- ชื่อ-นามสกุล ภาษาอังกฤษ --}}
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">ชื่อจริง (ภาษาอังกฤษ) <span style="color:var(--red)">*</span></label>
                        <input type="text" name="FirstName_EN" id="firstNameEN" class="form-control {{ $errors->has('FirstName_EN') ? 'is-invalid' : '' }}"
                               value="{{ old('FirstName_EN') }}" placeholder="เช่น Nureeda"
                               oninput="this.value = this.value.replace(/[^a-zA-Z ]/g, '')" required>
                        @error('FirstName_EN')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">นามสกุล (ภาษาอังกฤษ) <span style="color:var(--red)">*</span></label>
                        <input type="text" name="LastName_EN" id="lastNameEN" class="form-control {{ $errors->has('LastName_EN') ? 'is-invalid' : '' }}"
                               value="{{ old('LastName_EN') }}" placeholder="เช่น Sarae"
                               oninput="this.value = this.value.replace(/[^a-zA-Z ]/g, '')" required>
                        @error('LastName_EN')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- เบอร์โทรศัพท์ --}}
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">เบอร์โทรศัพท์ <span style="color:var(--red)">*</span></label>
                        <input type="tel" name="Phone" id="phoneInput" class="form-control {{ $errors->has('Phone') ? 'is-invalid' : '' }}"
                               value="{{ old('Phone') }}" placeholder="เช่น 081-234-5678" maxlength="12"
                               oninput="let val = this.value.replace(/\D/g, ''); if(val.length > 3 && val.length <= 6) { this.value = val.slice(0,3) + '-' + val.slice(3); } else if(val.length > 6) { this.value = val.slice(0,3) + '-' + val.slice(3,6) + '-' + val.slice(6,10); } else { this.value = val; }" required>
                        <div id="phoneFeedback" style="display:none; color:var(--red,#ef4444); font-size:0.8rem; margin-top:0.25rem;"></div>
                        @error('Phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">บทบาท <span style="color:var(--red)">*</span></label>
                        <select name="Role" class="form-control {{ $errors->has('Role') ? 'is-invalid' : '' }}" id="roleSelect" required>
                            <option value="">เลือกบทบาท</option>
                            @foreach(['ผู้ดูแลระบบ','ฝ่ายปกครอง','ครู','นักเรียน','ผู้ปกครอง'] as $role)
                                <option value="{{ $role }}" {{ old('Role') === $role ? 'selected' : '' }}>{{ $role }}</option>
                            @endforeach
                        </select>
                        @error('Role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-row" style="margin-bottom:0.25rem;">
                    <div class="form-group">
                        <label class="form-label">รหัสบัตรประชาชน <span style="color:var(--red)">*</span></label>
                        <input type="text" name="CitizenID" id="citizenIdInput" class="form-control {{ $errors->has('CitizenID') ? 'is-invalid' : '' }}"
                               value="{{ old('CitizenID') }}" placeholder="เช่น 1234567890123" maxlength="13"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                        <div id="citizenIdFeedback" style="display:none; color:var(--red,#ef4444); font-size:0.8rem; margin-top:0.25rem;"></div>
                        @error('CitizenID')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">สถานะ</label>
                        <select name="Status" class="form-control">
                            <option value="ปกติ" {{ old('Status','ปกติ') === 'ปกติ' ? 'selected' : '' }}>ปกติ</option>
                            <option value="ระงับการใช้งาน" {{ old('Status') === 'ระงับการใช้งาน' ? 'selected' : '' }}>ระงับการใช้งาน</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="background: #F8FAFC; padding: 1rem 1.25rem; border-radius: 10px; border: 1px solid #E2E8F0; margin-bottom: 1.25rem;">
                    <label class="form-label" style="font-weight: 700; color: #1E293B; margin-bottom: 0.5rem;">
                        <i class="fas fa-user-shield" style="color: var(--primary);"></i> สิทธิ์บทบาทระบบที่เปิดให้ใช้งาน (เลือกได้มากกว่า 1 สิทธิ์):
                    </label>
                    <div style="display: flex; gap: 1.75rem; flex-wrap: wrap; margin-top: 0.35rem;">
                        <label style="display: flex; align-items: center; gap: 0.55rem; cursor: pointer; font-size: 0.95rem; font-weight: 500; color: #334155;">
                            <input type="checkbox" name="additional_roles[]" value="ครู" {{ (old('additional_roles') && in_array('ครู', old('additional_roles'))) ? 'checked' : '' }} style="accent-color: var(--primary); width: 17px; height: 17px; cursor: pointer;">
                            <span>👨‍🏫 ครูประจำชั้น</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.55rem; cursor: pointer; font-size: 0.95rem; font-weight: 500; color: #334155;">
                            <input type="checkbox" name="additional_roles[]" value="ฝ่ายปกครอง" {{ (old('additional_roles') && in_array('ฝ่ายปกครอง', old('additional_roles'))) ? 'checked' : '' }} style="accent-color: var(--primary); width: 17px; height: 17px; cursor: pointer;">
                            <span>🛡️ ฝ่ายปกครอง</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.55rem; cursor: pointer; font-size: 0.95rem; font-weight: 500; color: #334155;">
                            <input type="checkbox" name="additional_roles[]" value="ผู้ปกครอง" {{ (old('additional_roles') && in_array('ผู้ปกครอง', old('additional_roles'))) ? 'checked' : '' }} style="accent-color: var(--primary); width: 17px; height: 17px; cursor: pointer;">
                            <span>👨‍👩‍👧 ผู้ปกครอง</span>
                        </label>
                    </div>
                    <small style="color: #64748B; display: block; margin-top: 0.4rem; font-size: 0.8rem; line-height: 1.4;">
                        * หากเลือกมากกว่า 1 บทบาท เมื่อผู้ใช้คนนี้ล็อกอินเข้าระบบ หน้าต่างเลือกบทบาทจะเด้งขึ้นมาให้เลือกทันที
                    </small>
                </div>

                {{-- อีเมล --}}
                <div class="form-row" style="margin-bottom:0.25rem;">
                    <div class="form-group" style="grid-column: span 2;">
                        <label class="form-label">อีเมล <span style="color:var(--red)">*</span> <span style="color:var(--text-sub); font-size:0.75rem;">(ใช้สำหรับรีเซ็ตรหัสผ่าน)</span></label>
                        <input type="email" name="Email" id="emailInput" class="form-control {{ $errors->has('Email') ? 'is-invalid' : '' }}"
                               value="{{ old('Email') }}" placeholder="เช่น example@gmail.com" autocomplete="off" required>
                        <div id="emailFeedback" style="display:none; color:var(--red,#ef4444); font-size:0.8rem; margin-top:0.25rem;"></div>
                        @error('Email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <hr style="margin:1.25rem 0; border-color:#ede8e0;">

                {{-- Username / Password section with auto-gen badge --}}
                <div style="display:flex; align-items:center; gap:0.6rem; margin-bottom:0.75rem;">
                    <p style="font-size:0.78rem; font-weight:700; color:var(--gold); text-transform:uppercase; letter-spacing:0.08em; margin:0;">ข้อมูลเข้าสู่ระบบ</p>
                    <span id="autoGenBadge" style="display:none; font-size:0.7rem; background:var(--green,#22c55e); color:#fff; padding:0.15rem 0.55rem; border-radius:999px; font-weight:600;">
                        <i class="fas fa-magic"></i> สร้างรหัสประจำตัวและรหัสผ่านอัตโนมัติเรียบร้อยแล้ว
                    </span>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">รหัสประจำตัว (Username) <span style="color:var(--text-muted)">(สร้างอัตโนมัติ)</span></label>
                        <div style="position:relative;">
                            <input type="text" name="Username" id="usernameInput" class="form-control {{ $errors->has('Username') ? 'is-invalid' : '' }}"
                                   value="{{ old('Username') }}" readonly style="background-color: #f5f5f5; cursor: not-allowed;" placeholder="เลือกบทบาทเพื่อสร้างอัตโนมัติ">
                        </div>
                        @error('Username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">รหัสผ่าน (Password) <span style="color:var(--text-muted)">(สร้างอัตโนมัติ)</span></label>
                        <div style="position:relative;">
                            <input type="text" name="Password" id="passwordInput" class="form-control {{ $errors->has('Password') ? 'is-invalid' : '' }}"
                                   value="{{ old('Password') }}" readonly style="background-color: #f5f5f5; cursor: not-allowed;" placeholder="เลือกบทบาทเพื่อสร้างอัตโนมัติ">
                        </div>
                        @error('Password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="grid-column: span 2;">
                        <label class="form-label">ข้อมูลเพิ่มเติม</label>
                        <input type="text" name="AdditionalInfo" class="form-control"
                               value="{{ old('AdditionalInfo') }}" placeholder="หมายเหตุ (ถ้ามี)">
                    </div>
                </div>

                {{-- ฟิลด์เพิ่มเติมตาม Role --}}
                <div id="extra-teacher" style="display:none;">
                    <hr style="margin:1rem 0; border-color:#ede8e0;">
                    <p style="font-size:0.8rem; font-weight:600; color:var(--gold); margin-bottom:0.75rem; text-transform:uppercase; letter-spacing:0.08em;">ข้อมูลครู</p>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">รหัสครู / รหัสประจำตัว <span style="color:var(--text-muted)">(สร้างอัตโนมัติ)</span></label>
                            <input type="text" name="TeacherID" id="teacherIDInput" class="form-control @error('TeacherID') is-invalid @enderror" value="{{ old('TeacherID') }}" readonly style="background-color: #f5f5f5; cursor: not-allowed;" placeholder="สร้างอัตโนมัติ">
                            @error('TeacherID')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">แผนก/กลุ่มสาระ</label>
                            <select name="department_id" id="teacherDeptSelect" class="form-control">
                                <option value="">เลือกกลุ่มสาระการเรียนรู้</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->department_id }}" {{ old('department_id') == $dept->department_id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label class="form-label">ห้องเรียนที่ดูแลที่ปรึกษา <span style="color:var(--text-muted); font-size:0.75rem;">(ระบุ ชั้น/ห้อง เช่น 4/1, 1/2)</span></label>
                            <div id="advisory-rooms-container" style="display: flex; flex-direction: column; gap: 0.5rem;">
                                @php
                                    $oldRooms = old('advisory_rooms', []);
                                @endphp
                                @if(empty($oldRooms))
                                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                                        <input type="text" name="advisory_rooms[]" class="form-control" placeholder="เช่น 4/1" style="flex: 1;">
                                        <button type="button" class="btn btn-outline" onclick="addRoomInput()" style="padding: 0.5rem 0.75rem;"><i class="fas fa-plus"></i> เพิ่มห้อง</button>
                                    </div>
                                @else
                                    @foreach($oldRooms as $index => $room)
                                        <div style="display: flex; gap: 0.5rem; align-items: center; margin-top: {{ $index > 0 ? '0.5rem' : '0' }};">
                                            <input type="text" name="advisory_rooms[]" class="form-control" value="{{ $room }}" placeholder="เช่น 4/1" style="flex: 1;">
                                            @if($index === 0)
                                                <button type="button" class="btn btn-outline" onclick="addRoomInput()" style="padding: 0.5rem 0.75rem;"><i class="fas fa-plus"></i> เพิ่มห้อง</button>
                                            @else
                                                <button type="button" class="btn btn-outline-danger" onclick="this.parentElement.remove()" style="padding: 0.5rem 0.75rem; border-color: var(--red); color: var(--red); background: none;"><i class="fas fa-trash-alt"></i> ลบ</button>
                                            @endif
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            @error('advisory_rooms')
                                <div class="invalid-feedback" style="display:block; margin-top:0.5rem;">{{ $message }}</div>
                            @enderror
                            <div id="advisory-error-msg" style="color: var(--red, #ef4444); font-size: 0.8rem; margin-top: 0.5rem; display: none; font-weight: 500;">
                                <i class="fas fa-exclamation-circle"></i> ห้องเรียนที่ปรึกษาต้องไม่ซ้ำกัน
                            </div>
                        </div>
                    </div>
                </div>

                <div id="extra-discipline" style="display:none;">
                    <hr style="margin:1rem 0; border-color:#ede8e0;">
                    <p style="font-size:0.8rem; font-weight:600; color:var(--gold); margin-bottom:0.75rem; text-transform:uppercase; letter-spacing:0.08em;">ข้อมูลฝ่ายปกครอง</p>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">ตำแหน่ง</label>
                            <input type="text" name="Position" class="form-control" value="{{ old('Position') }}" placeholder="เช่น หัวหน้าฝ่ายปกครอง">
                        </div>
                        <div class="form-group">
                            <label class="form-label">ระดับสิทธิ์</label>
                            <select name="Level" class="form-control">
                                <option value="บันทึกได้">บันทึกได้</option>
                                <option value="อนุมัติผล/ตั้งค่า">อนุมัติผล/ตั้งค่า</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div id="extra-student" style="display:none;">
                    <hr style="margin:1rem 0; border-color:#ede8e0;">
                    <p style="font-size:0.8rem; font-weight:600; color:var(--gold); margin-bottom:0.75rem; text-transform:uppercase; letter-spacing:0.08em;">ข้อมูลนักเรียน</p>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">รหัสนักเรียน <span style="color:var(--text-muted)">(สร้างอัตโนมัติ)</span></label>
                            <input type="text" name="StudentID" id="studentIDInput" class="form-control @error('StudentID') is-invalid @enderror" value="{{ old('StudentID') }}" readonly style="background-color: #f5f5f5; cursor: not-allowed;" placeholder="สร้างอัตโนมัติ">
                            @error('StudentID')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">ระดับชั้น</label>
                            <select name="GradeLevel" id="studentGradeSelect" class="form-control">
                                <option value="">เลือกระดับชั้น</option>
                                @foreach(['ม.1','ม.2','ม.3','ม.4','ม.5','ม.6'] as $g)
                                    <option value="{{ $g }}" {{ old('GradeLevel') === $g ? 'selected' : '' }}>{{ $g }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">ห้อง</label>
                            <input type="text" name="Classroom" id="studentClassInput" class="form-control" value="{{ old('Classroom') }}" placeholder="ระบุเลขห้อง (เช่น 1)" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>
                    </div>
                </div>

                <div style="display:flex; gap:0.75rem; margin-top:1.5rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> บันทึก
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    window.addRoomInput = function() {
        const container = document.getElementById('advisory-rooms-container');
        const div = document.createElement('div');
        div.style.display = 'flex';
        div.style.gap = '0.5rem';
        div.style.alignItems = 'center';
        div.style.marginTop = '0.5rem';
        div.innerHTML = `
            <input type="text" name="advisory_rooms[]" class="form-control" placeholder="เช่น 4/2" style="flex: 1;">
            <button type="button" class="btn btn-outline-danger" onclick="this.parentElement.remove()" style="padding: 0.5rem 0.75rem; border-color: var(--red); color: var(--red); background: none;"><i class="fas fa-trash-alt"></i> ลบ</button>
        `;
        container.appendChild(div);
    };

    const roleSelect      = document.getElementById('roleSelect');
    const usernameInput   = document.getElementById('usernameInput');
    const passwordInput   = document.getElementById('passwordInput');
    const teacherIDInput  = document.getElementById('teacherIDInput');
    const studentIDInput  = document.getElementById('studentIDInput');
    const autoGenBadge    = document.getElementById('autoGenBadge');
    const firstNameEN     = document.getElementById('firstNameEN');
    const phoneInput      = document.getElementById('phoneInput');
    const citizenIdInput  = document.getElementById('citizenIdInput');

    const nextIds = @json($nextIds);

    const sections = {
        'ครู':         'extra-teacher',
        'ฝ่ายปกครอง': 'extra-discipline',
        'นักเรียน':    'extra-student',
    };

    function capitalize(str) {
        if (!str) return '';
        return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
    }

    function getGeneratedPassword() {
        const fnEN = (firstNameEN.value || '').trim().replace(/\s+/g, '');
        const phone = (phoneInput.value || '').trim().replace(/\D/g, '');
        
        if (fnEN && phone.length >= 4) {
            const phoneSuffix4 = phone.slice(-4);
            return capitalize(fnEN) + phoneSuffix4;
        }
        return '';
    }

    const studentGradeSelect = document.getElementById('studentGradeSelect');
    const studentClassInput = document.getElementById('studentClassInput');

    async function fetchNextStudentIDForUser() {
        const role = roleSelect.value;
        if (role !== 'นักเรียน') return;

        const grade = studentGradeSelect.value;
        const classroom = studentClassInput.value;

        if (!grade || !classroom) {
            studentIDInput.value = '';
            usernameInput.value = '';
            return;
        }

        try {
            const url = `{{ route('admin.students.get-next-id') }}?grade=${encodeURIComponent(grade)}&classroom=${encodeURIComponent(classroom)}`;
            const response = await fetch(url);
            const data = await response.json();
            
            studentIDInput.value = data.StudentID;
            usernameInput.value = data.StudentID; // For student, username is their student ID
        } catch (error) {
            console.error('Error fetching student ID:', error);
        }
    }

    studentGradeSelect.addEventListener('change', fetchNextStudentIDForUser);
    studentClassInput.addEventListener('input', fetchNextStudentIDForUser);

    async function updateTeacherIDForUser() {
        const role = roleSelect.value;
        if (role !== 'ครู') return;

        const deptSelect = document.getElementById('teacherDeptSelect');
        const advisoryInput = document.querySelector('input[name="advisory_rooms[]"]');
        if (!deptSelect || !advisoryInput) return;

        const dept = deptSelect.value;
        const room = advisoryInput.value.trim();

        if (!dept || !room) {
            teacherIDInput.value = '';
            usernameInput.value = '';
            return;
        }

        try {
            const url = `{{ route('admin.teachers.get-next-id') }}?department=${encodeURIComponent(dept)}&classroom=${encodeURIComponent(room)}`;
            const response = await fetch(url);
            const data = await response.json();
            
            if (data.TeacherID) {
                teacherIDInput.value = data.TeacherID;
            }
        } catch (error) {
            console.error('Error fetching teacher ID:', error);
        }
    }

    // Attach listeners for dynamic teacher ID updates
    document.addEventListener('change', function(e) {
        if (e.target.id === 'teacherDeptSelect') {
            updateTeacherIDForUser();
        }
    });

    document.addEventListener('input', function(e) {
        if (e.target.matches('input[name="advisory_rooms[]"]')) {
            const firstRoomInput = document.querySelector('input[name="advisory_rooms[]"]');
            if (e.target === firstRoomInput) {
                updateTeacherIDForUser();
            }
        }
    });

    function generateCredentials() {
        const role = roleSelect.value;
        const cfg = nextIds[role];
        const citizenId = citizenIdInput.value.trim();
        const fnEN = (firstNameEN.value || '').trim().toLowerCase().replace(/[^a-z0-9._-]/g, '');

        if (!role) {
            usernameInput.value = '';
            passwordInput.value = '';
            teacherIDInput.value = '';
            studentIDInput.value = '';
            autoGenBadge.style.display = 'none';
            return;
        }

        if (role === 'ครู' || role === 'ฝ่ายปกครอง') {
            const rawFn = (firstNameEN.value || '').trim().toLowerCase().replace(/[^a-z]/g, '');
            const phoneDigits = (phoneInput.value || '').trim().replace(/\D/g, '');
            const numSuffix = citizenId.length >= 4 ? citizenId.slice(-4) : (phoneDigits.length >= 4 ? phoneDigits.slice(-4) : '01');
            
            const username = rawFn ? (rawFn + numSuffix) : '';
            usernameInput.value = username;
            passwordInput.value = citizenId;

            if (role === 'ครู') {
                updateTeacherIDForUser();
            }

            if (username && citizenId.length === 13) {
                autoGenBadge.style.display = 'inline-flex';
                autoGenBadge.innerHTML = '<i class="fas fa-magic"></i> สร้าง Username (' + username + ') และรหัสผ่านจากเลขบัตรประชาชนเรียบร้อยแล้ว';
                autoGenBadge.style.backgroundColor = 'var(--green, #22c55e)';
            } else if (!rawFn && citizenId.length !== 13) {
                autoGenBadge.style.display = 'inline-flex';
                autoGenBadge.innerHTML = '<i class="fas fa-exclamation-circle"></i> กรุณากรอกชื่อภาษาอังกฤษและเลขบัตรประชาชน (13 หลัก) เพื่อสร้างข้อมูลเข้าสู่ระบบ';
                autoGenBadge.style.backgroundColor = 'var(--gold, #d97706)';
            } else if (!rawFn) {
                autoGenBadge.style.display = 'inline-flex';
                autoGenBadge.innerHTML = '<i class="fas fa-exclamation-circle"></i> กรุณากรอกชื่อภาษาอังกฤษเพื่อสร้าง Username';
                autoGenBadge.style.backgroundColor = 'var(--gold, #d97706)';
            } else {
                autoGenBadge.style.display = 'inline-flex';
                autoGenBadge.innerHTML = '<i class="fas fa-exclamation-circle"></i> กรุณากรอกเลขบัตรประชาชน (13 หลัก) เพื่อใช้เป็นรหัสผ่าน';
                autoGenBadge.style.backgroundColor = 'var(--gold, #d97706)';
            }
            return;
        }

        if (role === 'นักเรียน') {
            fetchNextStudentIDForUser();
            passwordInput.value = citizenId || getGeneratedPassword();
        } else if (role === 'ผู้ปกครอง') {
            usernameInput.value = citizenId;
            passwordInput.value = citizenId || getGeneratedPassword();
        } else {
            if (cfg) usernameInput.value = cfg.username;
            passwordInput.value = citizenId || getGeneratedPassword();
        }

        if (usernameInput.value && passwordInput.value) {
            autoGenBadge.style.display = 'inline-flex';
            autoGenBadge.innerHTML = '<i class="fas fa-magic"></i> สร้างรหัสประจำตัวและรหัสผ่านอัตโนมัติเรียบร้อยแล้ว';
            autoGenBadge.style.backgroundColor = 'var(--green, #22c55e)';
        } else {
            autoGenBadge.style.display = 'inline-flex';
            autoGenBadge.innerHTML = '<i class="fas fa-exclamation-circle"></i> กรุณากรอกข้อมูลที่จำเป็นเพื่อสร้างรหัสประจำตัวและรหัสผ่าน';
            autoGenBadge.style.backgroundColor = 'var(--gold, #d97706)';
        }
    }

    function updateSections() {
        Object.values(sections).forEach(id => {
            document.getElementById(id).style.display = 'none';
        });
        const selected = sections[roleSelect.value];
        if (selected) document.getElementById(selected).style.display = 'block';
        
        generateCredentials();
    }

    roleSelect.addEventListener('change', updateSections);
    firstNameEN.addEventListener('input', generateCredentials);
    phoneInput.addEventListener('input', generateCredentials);
    citizenIdInput.addEventListener('input', generateCredentials);
    updateSections();

    // Prevent duplicate rooms on submit
    const form = roleSelect.closest('form');
    const roomsContainer = document.getElementById('advisory-rooms-container');

    function validateAdvisoryRooms() {
        const inputs = Array.from(form.querySelectorAll('input[name="advisory_rooms[]"]'));
        const values = inputs.map(i => i.value.trim().replace(/^ม\./, ''));
        const errorMsg = document.getElementById('advisory-error-msg');
        
        // Clear previous error styles
        inputs.forEach(input => {
            input.style.borderColor = '';
            input.style.backgroundColor = '';
        });
        if (errorMsg) {
            errorMsg.style.display = 'none';
        }

        let hasDuplicate = false;
        
        for (let i = 0; i < inputs.length; i++) {
            const val1 = values[i];
            if (val1 === '') continue;
            
            for (let j = i + 1; j < inputs.length; j++) {
                const val2 = values[j];
                if (val1 === val2) {
                    inputs[i].style.borderColor = 'var(--red, #ef4444)';
                    inputs[i].style.backgroundColor = '#fef2f2';
                    inputs[j].style.borderColor = 'var(--red, #ef4444)';
                    inputs[j].style.backgroundColor = '#fef2f2';
                    hasDuplicate = true;
                }
            }
        }

        if (hasDuplicate && errorMsg) {
            errorMsg.style.display = 'block';
        }
        return hasDuplicate;
    }

    if (roomsContainer) {
        roomsContainer.addEventListener('input', function(e) {
            if (e.target.matches('input[name="advisory_rooms[]"]')) {
                validateAdvisoryRooms();
            }
        });
    }

    const citizenIdFeedback = document.getElementById('citizenIdFeedback');
    let isCitizenIDDuplicate = false;

    function checkCitizenID() {
        const val = citizenIdInput.value.trim();
        if (val.length === 0) {
            citizenIdInput.style.borderColor = 'var(--red, #ef4444)';
            citizenIdInput.style.backgroundColor = '#fef2f2';
            citizenIdFeedback.textContent = 'กรุณากรอกรหัสบัตรประชาชน (13 หลัก)';
            citizenIdFeedback.style.display = 'block';
            isCitizenIDDuplicate = true;
            return;
        }
        if (val.length < 13) {
            citizenIdInput.style.borderColor = 'var(--red, #ef4444)';
            citizenIdInput.style.backgroundColor = '#fef2f2';
            citizenIdFeedback.textContent = 'รหัสบัตรประชาชนต้องเป็นตัวเลข 13 หลัก (ปัจจุบัน ' + val.length + ' หลัก)';
            citizenIdFeedback.style.display = 'block';
            isCitizenIDDuplicate = true;
            return;
        }

        fetch('{{ route("admin.users.check-citizen-id") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                CitizenID: val,
                UserID: ''
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.exists) {
                citizenIdInput.style.borderColor = 'var(--red, #ef4444)';
                citizenIdInput.style.backgroundColor = '#fef2f2';
                citizenIdFeedback.textContent = data.message;
                citizenIdFeedback.style.display = 'block';
                isCitizenIDDuplicate = true;
            } else {
                citizenIdInput.style.borderColor = '';
                citizenIdInput.style.backgroundColor = '';
                citizenIdFeedback.style.display = 'none';
                isCitizenIDDuplicate = false;
            }
        })
        .catch(err => console.error('Error checking Citizen ID:', err));
    }

    citizenIdInput.addEventListener('input', checkCitizenID);
    citizenIdInput.addEventListener('blur', checkCitizenID);

    const phoneFeedback = document.getElementById('phoneFeedback');
    let isPhoneDuplicate = false;

    function checkPhone() {
        const val = phoneInput.value.trim();
        if (val.length !== 12) {
            phoneInput.style.borderColor = '';
            phoneInput.style.backgroundColor = '';
            phoneFeedback.style.display = 'none';
            isPhoneDuplicate = false;
            return;
        }

        fetch('{{ route("admin.users.check-phone") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                Phone: val,
                UserID: ''
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.exists) {
                phoneInput.style.borderColor = 'var(--red, #ef4444)';
                phoneInput.style.backgroundColor = '#fef2f2';
                phoneFeedback.textContent = data.message;
                phoneFeedback.style.display = 'block';
                isPhoneDuplicate = true;
            } else {
                phoneInput.style.borderColor = '';
                phoneInput.style.backgroundColor = '';
                phoneFeedback.style.display = 'none';
                isPhoneDuplicate = false;
            }
        })
        .catch(err => console.error('Error checking Phone:', err));
    }

    phoneInput.addEventListener('input', checkPhone);
    phoneInput.addEventListener('blur', checkPhone);

    const emailInput = document.getElementById('emailInput');
    const emailFeedback = document.getElementById('emailFeedback');
    let isEmailDuplicate = false;

    function checkEmail() {
        const val = emailInput.value.trim();
        if (val.length < 5 || !val.includes('@')) {
            emailInput.style.borderColor = '';
            emailInput.style.backgroundColor = '';
            emailFeedback.style.display = 'none';
            isEmailDuplicate = false;
            return;
        }

        fetch('{{ route("admin.users.check-email") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                Email: val,
                UserID: ''
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.exists) {
                emailInput.style.borderColor = 'var(--red, #ef4444)';
                emailInput.style.backgroundColor = '#fef2f2';
                emailFeedback.textContent = data.message;
                emailFeedback.style.display = 'block';
                isEmailDuplicate = true;
            } else {
                emailInput.style.borderColor = '';
                emailInput.style.backgroundColor = '';
                emailFeedback.style.display = 'none';
                isEmailDuplicate = false;
            }
        })
        .catch(err => console.error('Error checking Email:', err));
    }

    emailInput.addEventListener('input', checkEmail);
    emailInput.addEventListener('blur', checkEmail);

    if (form) {
        form.addEventListener('submit', function(e) {
            const cid = citizenIdInput.value.trim();
            if (!cid || cid.length !== 13) {
                e.preventDefault();
                citizenIdInput.style.borderColor = 'var(--red, #ef4444)';
                citizenIdInput.style.backgroundColor = '#fef2f2';
                citizenIdFeedback.textContent = 'กรุณากรอกรหัสบัตรประชาชนให้ถูกต้องครบ 13 หลัก';
                citizenIdFeedback.style.display = 'block';
                isCitizenIDDuplicate = true;
                citizenIdInput.focus();
                citizenIdInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            }
            if (isCitizenIDDuplicate) {
                e.preventDefault();
                citizenIdInput.focus();
                citizenIdInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            }
            if (isPhoneDuplicate) {
                e.preventDefault();
                phoneInput.focus();
                phoneInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            }
            if (isEmailDuplicate) {
                e.preventDefault();
                emailInput.focus();
                emailInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            }
            if (roleSelect.value === 'ครู') {
                if (validateAdvisoryRooms()) {
                    e.preventDefault();
                    if (roomsContainer) {
                        roomsContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                    return false;
                }
            }
        });
    }
</script>
@endpush
@endsection