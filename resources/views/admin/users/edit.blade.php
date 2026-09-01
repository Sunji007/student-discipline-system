@extends('layouts.app')

@section('title', 'แก้ไขผู้ใช้งาน')
@section('page-title', 'แก้ไขข้อมูลผู้ใช้งาน')

@section('content')
<div style="max-width:680px;">
    <div class="card">
        <div class="card-header-bar">
            <h3>แก้ไขข้อมูล: {{ $user->FullName }}</h3>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline btn-sm">
                <i class="fas fa-arrow-left"></i> ย้อนกลับ
            </a>
        </div>
        <div class="card-body-pad">
            <form method="POST" action="{{ route('admin.users.update', $user->UserID) }}">
                @csrf @method('PUT')
                <input type="hidden" name="UserID" id="UserID" value="{{ old('UserID', $user->UserID) }}">

                @if ($errors->any())
                    <div class="alert alert-danger" style="margin-bottom: 1.5rem; padding: 1rem; background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; border-radius: 8px;">
                        <ul style="margin: 0; padding-left: 1.25rem;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div style="background:#faf8f4; padding:0.75rem 1rem; border-radius:8px; border: 1px solid var(--border); margin-bottom:1.5rem; font-size:0.85rem; color:var(--text-muted); display:flex; align-items:center; gap:0.5rem;">
                    <i class="fas fa-info-circle" style="color:var(--gold);"></i>
                    <span>ชื่อผู้ใช้งาน (Username): <strong id="usernameDisplay">{{ $user->Username }}</strong></span>
                </div>

                <div class="form-row">
                    @php
                        $prefixes = ['นาย', 'นางสาว', 'นาง', 'ด.ช.', 'ด.ญ.'];
                        $selectedPrefix = '';
                        $firstNameOnly = $user->FirstName;
                        foreach ($prefixes as $p) {
                            if (str_starts_with($user->FirstName, $p)) {
                                $selectedPrefix = $p;
                                $firstNameOnly = substr($user->FirstName, strlen($p));
                                break;
                            }
                        }
                    @endphp
                    <div class="form-group">
                        <label class="form-label">ชื่อจริง (ภาษาไทย) <span style="color:var(--red)">*</span></label>
                        <div style="display: flex; gap: 0.5rem;">
                            <select name="prefix" class="form-control" style="width: 120px; flex-shrink: 0;" required>
                                <option value="">คำนำหน้า</option>
                                <option value="นาย" {{ old('prefix', $selectedPrefix) === 'นาย' ? 'selected' : '' }}>นาย</option>
                                <option value="นาง" {{ old('prefix', $selectedPrefix) === 'นาง' ? 'selected' : '' }}>นาง</option>
                                <option value="นางสาว" {{ old('prefix', $selectedPrefix) === 'นางสาว' ? 'selected' : '' }}>นางสาว</option>
                                <option value="ด.ช." {{ old('prefix', $selectedPrefix) === 'ด.ช.' ? 'selected' : '' }}>ด.ช.</option>
                                <option value="ด.ญ." {{ old('prefix', $selectedPrefix) === 'ด.ญ.' ? 'selected' : '' }}>ด.ญ.</option>
                            </select>
                            <input type="text" name="FirstName" class="form-control {{ $errors->has('FirstName') ? 'is-invalid' : '' }}"
                                   value="{{ old('FirstName', $firstNameOnly) }}"
                                   oninput="this.value = this.value.replace(/[^ก-๙]/g, '')" required style="flex: 1;">
                        </div>
                        @error('FirstName')<div class="invalid-feedback" style="display: block;">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">นามสกุล (ภาษาไทย) <span style="color:var(--red)">*</span></label>
                        <input type="text" name="LastName" class="form-control {{ $errors->has('LastName') ? 'is-invalid' : '' }}"
                               value="{{ old('LastName', $user->LastName) }}"
                               oninput="this.value = this.value.replace(/[^ก-๙]/g, '')" required>
                         @error('LastName')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">ชื่อจริง (ภาษาอังกฤษ) <span style="color:var(--red)">*</span></label>
                        <input type="text" name="FirstName_EN" class="form-control {{ $errors->has('FirstName_EN') ? 'is-invalid' : '' }}"
                               value="{{ old('FirstName_EN', $user->FirstName_EN) }}"
                               placeholder="เช่น Nureeda" oninput="this.value = this.value.replace(/[^a-zA-Z ]/g, '')" required>
                        @error('FirstName_EN')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">นามสกุล (ภาษาอังกฤษ) <span style="color:var(--red)">*</span></label>
                        <input type="text" name="LastName_EN" class="form-control {{ $errors->has('LastName_EN') ? 'is-invalid' : '' }}"
                               value="{{ old('LastName_EN', $user->LastName_EN) }}"
                               placeholder="เช่น Sarae" oninput="this.value = this.value.replace(/[^a-zA-Z ]/g, '')" required>
                        @error('LastName_EN')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">เบอร์โทรศัพท์</label>
                        <input type="tel" name="Phone" id="phoneInput" class="form-control {{ $errors->has('Phone') ? 'is-invalid' : '' }}"
                               value="{{ old('Phone', $user->Phone) }}"
                               placeholder="เช่น 081-234-5678" maxlength="12"
                               oninput="let val = this.value.replace(/\D/g, ''); if(val.length > 3 && val.length <= 6) { this.value = val.slice(0,3) + '-' + val.slice(3); } else if(val.length > 6) { this.value = val.slice(0,3) + '-' + val.slice(3,6) + '-' + val.slice(6,10); } else { this.value = val; }">
                        <div id="phoneFeedback" style="display:none; color:var(--red,#ef4444); font-size:0.8rem; margin-top:0.25rem;"></div>
                        @error('Phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">รหัสบัตรประชาชน <span style="color:var(--text-muted); font-size:0.8rem;">(ไม่สามารถแก้ไขได้)</span></label>
                        <input type="text" name="CitizenID" id="citizenIdInput" class="form-control {{ $errors->has('CitizenID') ? 'is-invalid' : '' }}"
                               value="{{ old('CitizenID', $user->CitizenID) }}"
                               placeholder="เช่น 1234567890123" maxlength="13"
                               readonly style="background-color: #f1f5f9; cursor: not-allowed;" required>
                        <div id="citizenIdFeedback" style="display:none; color:var(--red,#ef4444); font-size:0.8rem; margin-top:0.25rem;"></div>
                        @error('CitizenID')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- อีเมล --}}
                <div class="form-row">
                    <div class="form-group" style="grid-column: span 2;">
                        <label class="form-label">อีเมล <span style="color:var(--red)">*</span> <span style="color:var(--text-sub); font-size:0.75rem;">(ใช้สำหรับรีเซ็ตรหัสผ่าน)</span></label>
                        <input type="email" name="Email" id="emailInput" class="form-control {{ $errors->has('Email') ? 'is-invalid' : '' }}"
                               value="{{ old('Email', $user->Email) }}" placeholder="เช่น example@gmail.com" autocomplete="off" required>
                        <div id="emailFeedback" style="display:none; color:var(--red,#ef4444); font-size:0.8rem; margin-top:0.25rem;"></div>
                        @error('Email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">บทบาทหลัก <span style="color:var(--red)">*</span></label>
                        <select name="Role" class="form-control {{ $errors->has('Role') ? 'is-invalid' : '' }}" required>
                            @foreach(['ผู้ดูแลระบบ','ฝ่ายปกครอง','ครู','นักเรียน','ผู้ปกครอง'] as $r)
                                <option value="{{ $r }}" {{ old('Role', $user->Role) === $r ? 'selected' : '' }}>{{ $r === 'ครู' ? 'ครูประจำชั้น' : $r }}</option>
                            @endforeach
                        </select>
                        @error('Role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">สถานะ</label>
                        <select name="Status" class="form-control">
                            <option value="ปกติ" {{ old('Status', $user->Status) === 'ปกติ' ? 'selected' : '' }}>ปกติ</option>
                            <option value="ระงับการใช้งาน" {{ old('Status', $user->Status) === 'ระงับการใช้งาน' ? 'selected' : '' }}>ระงับการใช้งาน</option>
                        </select>
                    </div>
                </div>

                @php
                    $hasTeacherProfile = $user->teacher()->exists();
                    $hasDisciplineProfile = $user->disciplineOfficer()->exists();
                    $hasParentProfile = $user->parentGuardian()->exists() || $user->parentStudents()->count() > 0;
                @endphp
                <div class="form-group" style="background: #F8FAFC; padding: 1rem 1.25rem; border-radius: 10px; border: 1px solid #E2E8F0; margin-bottom: 1.25rem;">
                    <label class="form-label" style="font-weight: 700; color: #1E293B; margin-bottom: 0.5rem;">
                        <i class="fas fa-user-shield" style="color: var(--primary);"></i> สิทธิ์บทบาทระบบที่เปิดให้ใช้งาน (เลือกได้มากกว่า 1 สิทธิ์):
                    </label>
                    <div style="display: flex; gap: 1.75rem; flex-wrap: wrap; margin-top: 0.35rem;">
                        <label style="display: flex; align-items: center; gap: 0.55rem; cursor: pointer; font-size: 0.95rem; font-weight: 500; color: #334155;">
                            <input type="checkbox" name="additional_roles[]" value="ครู" {{ (old('additional_roles') ? in_array('ครู', old('additional_roles')) : ($user->Role === 'ครู' || $hasTeacherProfile)) ? 'checked' : '' }} style="accent-color: var(--primary); width: 17px; height: 17px; cursor: pointer;">
                            <span>👨‍🏫 ครูประจำชั้น</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.55rem; cursor: pointer; font-size: 0.95rem; font-weight: 500; color: #334155;">
                            <input type="checkbox" name="additional_roles[]" value="ฝ่ายปกครอง" {{ (old('additional_roles') ? in_array('ฝ่ายปกครอง', old('additional_roles')) : ($user->Role === 'ฝ่ายปกครอง' || $hasDisciplineProfile)) ? 'checked' : '' }} style="accent-color: var(--primary); width: 17px; height: 17px; cursor: pointer;">
                            <span>🛡️ ฝ่ายปกครอง</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.55rem; cursor: pointer; font-size: 0.95rem; font-weight: 500; color: #334155;">
                            <input type="checkbox" name="additional_roles[]" value="ผู้ปกครอง" {{ (old('additional_roles') ? in_array('ผู้ปกครอง', old('additional_roles')) : ($user->Role === 'ผู้ปกครอง' || $hasParentProfile)) ? 'checked' : '' }} style="accent-color: var(--primary); width: 17px; height: 17px; cursor: pointer;">
                            <span>👨‍👩‍👧 ผู้ปกครอง</span>
                        </label>
                    </div>
                    <small style="color: #64748B; display: block; margin-top: 0.4rem; font-size: 0.8rem; line-height: 1.4;">
                        * หากเลือกมากกว่า 1 บทบาท เมื่อผู้ใช้คนนี้ล็อกอินเข้าระบบ หน้าต่างเลือกบทบาทจะเด้งขึ้นมาให้เลือกทันที
                    </small>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">รีเซ็ตรหัสผ่าน (เว้นว่างถ้าไม่เปลี่ยน)</label>
                        <div style="position: relative;">
                            <input type="password" name="Password" id="edit_user_password" class="form-control"
                                   placeholder="อย่างน้อย 8 ตัวอักษร (พิมพ์ใหญ่ พิมพ์เล็ก ตัวเลข อักขระพิเศษ)"
                                   style="padding-right: 2.75rem;"
                                   oninput="this.value = this.value.replace(/[\u0e00-\u0e7f]/g, '')">
                            <button type="button" id="togglePasswordBtn" onclick="togglePasswordInput('edit_user_password', this)"
                                    title="แสดง/ซ่อนรหัสผ่าน"
                                    style="position: absolute; right: 0.6rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: #64748b; cursor: pointer; padding: 0.35rem 0.5rem; font-size: 1.1rem; border-radius: 4px; display: flex; align-items: center; justify-content: center; transition: color 0.15s ease;"
                                    onmouseover="this.style.color='var(--navy)'" onmouseout="this.style.color='#64748b'">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        @error('Password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">ข้อมูลเพิ่มเติม</label>
                    <input type="text" name="AdditionalInfo" class="form-control"
                           value="{{ old('AdditionalInfo', $user->AdditionalInfo) }}" placeholder="หมายเหตุ">
                </div>

                <div style="display:flex; gap:0.75rem; margin-top:0.5rem;">
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
    window.togglePasswordInput = function(inputId, btn) {
        const input = document.getElementById(inputId);
        if (!input) return;
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            if (icon) {
                icon.className = 'fas fa-eye-slash';
            }
        } else {
            input.type = 'password';
            if (icon) {
                icon.className = 'fas fa-eye';
            }
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        const citizenIdInput = document.getElementById('citizenIdInput');
        const citizenIdFeedback = document.getElementById('citizenIdFeedback');
        const form = citizenIdInput.closest('form');
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
                    UserID: '{{ $user->UserID }}'
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

        const phoneInput = document.getElementById('phoneInput');
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
                    UserID: '{{ $user->UserID }}'
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
                    UserID: '{{ $user->UserID }}'
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

        const roleSelect = document.querySelector('select[name="Role"]');
        const usernameDisplay = document.getElementById('usernameDisplay');
        const originalUsername = '{{ $user->Username }}';

        function updateUsernameDisplay() {
            if (roleSelect && roleSelect.value === 'ผู้ปกครอง') {
                usernameDisplay.textContent = citizenIdInput.value.trim() || 'รหัสบัตรประชาชน';
            } else {
                usernameDisplay.textContent = originalUsername;
            }
        }

        if (roleSelect) {
            roleSelect.addEventListener('change', updateUsernameDisplay);
            citizenIdInput.addEventListener('input', updateUsernameDisplay);
            updateUsernameDisplay();
        }

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
            });
        }
    });
</script>
@endpush
@endsection