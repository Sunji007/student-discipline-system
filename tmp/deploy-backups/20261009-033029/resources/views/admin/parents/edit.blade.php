@extends('layouts.app')

@section('title', 'แก้ไขข้อมูลผู้ปกครอง')
@section('page-title', 'แก้ไขข้อมูลผู้ปกครอง')

@section('content')
<div style="max-width:600px; margin:0 auto;">
    <div style="margin-bottom:1rem;">
        <a href="{{ route('admin.students.parents.index', $student->StudentID) }}" class="btn btn-outline btn-sm">
            <i class="fas fa-arrow-left"></i> ย้อนกลับ
        </a>
    </div>

    <div class="card">
        <div class="card-header-bar">
            <h3><i class="fas fa-user-edit" style="color:var(--primary);"></i> แก้ไขผู้ปกครอง</h3>
            <span style="font-size:0.8rem; color:var(--text-muted);">
                นักเรียน: {{ $student->FullName }} ({{ $student->StudentID }})
            </span>
        </div>
        <div class="card-body-pad">
            <form method="POST" action="{{ route('admin.students.parents.update', [$student->StudentID, $parent->ParentID]) }}">
                @csrf @method('PUT')
                <input type="hidden" name="UserID" id="UserID" value="{{ old('UserID', $parent->UserID) }}">

                @if($parentUsers->count() > 0)
                <div class="form-group" style="margin-bottom: 1.5rem; background-color: #f8fafc; padding: 1.25rem; border-radius: 8px; border: 1px dashed var(--border);">
                    <label class="form-label" for="user_select" style="font-weight: 600; color: var(--primary); display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-search"></i> เชื่อมโยงกับบัญชีผู้ใช้ผู้ปกครองในระบบ
                    </label>
                    <select id="user_select" class="form-control" style="background-color: #fff;">
                        <option value="">-- ไม่เชื่อมโยงบัญชีผู้ใช้ (กรอกข้อมูลแยกเป็นอิสระ) --</option>
                        @foreach($parentUsers as $u)
                            <option value="{{ $u->UserID }}" 
                                    data-firstname="{{ $u->FirstName }}" 
                                    data-lastname="{{ $u->LastName }}" 
                                    data-citizenid="{{ $u->CitizenID }}" 
                                    data-phone="{{ $u->Phone }}" 
                                    data-email="{{ $u->Email }}"
                                    {{ old('UserID', $parent->UserID) == $u->UserID ? 'selected' : '' }}>
                                {{ $u->FullName }} (เลขบัตร: {{ $u->CitizenID ?? 'ไม่มีข้อมูล' }})
                            </option>
                        @endforeach
                    </select>
                    <p style="font-size: 0.78rem; color: var(--text-muted); margin-top: 0.5rem; margin-bottom: 0;">
                        * เมื่อเลือกเชื่อมโยงบัญชีผู้ใช้ ระบบจะดึงข้อมูล ชื่อ นามสกุล เลขบัตรประชาชน และเบอร์โทรศัพท์มาใส่ในฟอร์มและล็อกเป็นอ่านอย่างเดียว (Readonly) โดยอัตโนมัติ
                    </p>
                </div>
                @endif

                <div class="form-row">
                    @php
                        $prefixes = ['นาย', 'นางสาว', 'นาง', 'ด.ช.', 'ด.ญ.'];
                        $selectedPrefix = '';
                        $firstNameOnly = $parent->FirstName;
                        foreach ($prefixes as $p) {
                            if (str_starts_with($parent->FirstName, $p)) {
                                $selectedPrefix = $p;
                                $firstNameOnly = substr($parent->FirstName, strlen($p));
                                break;
                            }
                        }
                    @endphp
                    <div class="form-group">
                        <label class="form-label" for="FirstName">ชื่อจริง <span style="color:var(--red)">*</span></label>
                        <div style="display: flex; gap: 0.5rem;">
                            <select name="prefix" id="prefixSelect" class="form-control" style="width: 120px; flex-shrink: 0;" required>
                                <option value="">คำนำหน้า</option>
                                <option value="นาย" {{ old('prefix', $selectedPrefix) === 'นาย' ? 'selected' : '' }}>นาย</option>
                                <option value="นาง" {{ old('prefix', $selectedPrefix) === 'นาง' ? 'selected' : '' }}>นาง</option>
                                <option value="นางสาว" {{ old('prefix', $selectedPrefix) === 'นางสาว' ? 'selected' : '' }}>นางสาว</option>
                                <option value="ด.ช." {{ old('prefix', $selectedPrefix) === 'ด.ช.' ? 'selected' : '' }}>ด.ช.</option>
                                <option value="ด.ญ." {{ old('prefix', $selectedPrefix) === 'ด.ญ.' ? 'selected' : '' }}>ด.ญ.</option>
                            </select>
                            <input type="text" name="FirstName" id="FirstName"
                                   class="form-control @error('FirstName') is-invalid @enderror"
                                   value="{{ old('FirstName', $firstNameOnly) }}"
                                   oninput="this.value = this.value.replace(/[^ก-๙]/g, '')"
                                   required style="flex: 1;">
                        </div>
                        @error('FirstName')
                            <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="LastName">นามสกุล <span style="color:var(--red)">*</span></label>
                        <input type="text" name="LastName" id="LastName"
                               class="form-control @error('LastName') is-invalid @enderror"
                               value="{{ old('LastName', $parent->LastName) }}"
                               oninput="this.value = this.value.replace(/[^ก-๙]/g, '')"
                               required>
                        @error('LastName')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="Relationship">ความสัมพันธ์ <span style="color:var(--red)">*</span></label>
                        <select name="Relationship" id="Relationship"
                                class="form-control @error('Relationship') is-invalid @enderror" required>
                            <option value="">-- เลือกความสัมพันธ์ --</option>
                            @foreach(['บิดา','มารดา','ปู่','ย่า','ตา','ยาย','ลุง','ป้า','น้า','อา','พี่','ผู้ปกครอง','อื่นๆ'] as $rel)
                                <option value="{{ $rel }}" {{ old('Relationship', $parent->Relationship) === $rel ? 'selected' : '' }}>{{ $rel }}</option>
                            @endforeach
                        </select>
                        @error('Relationship')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="CitizenID">รหัสบัตรประชาชน <span style="color:var(--text-muted); font-size:0.8rem;">(ไม่สามารถแก้ไขได้)</span></label>
                        <input type="text" name="CitizenID" id="CitizenID"
                               class="form-control @error('CitizenID') is-invalid @enderror"
                               value="{{ old('CitizenID', $parent->CitizenID) }}"
                               required placeholder="เช่น 1234567890123" maxlength="13"
                               readonly style="background-color: #f1f5f9; cursor: not-allowed;">
                        @error('CitizenID')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="Phone">เบอร์โทรศัพท์</label>
                        <input type="text" name="Phone" id="Phone"
                               class="form-control @error('Phone') is-invalid @enderror"
                               value="{{ old('Phone', $parent->Phone) }}" placeholder="เช่น 081-234-5678" maxlength="12"
                               oninput="let val = this.value.replace(/\D/g, ''); if(val.length > 3 && val.length <= 6) { this.value = val.slice(0,3) + '-' + val.slice(3); } else if(val.length > 6) { this.value = val.slice(0,3) + '-' + val.slice(3,6) + '-' + val.slice(6,10); } else { this.value = val; }">
                        @error('Phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="Email">อีเมล <span style="color:var(--red)">*</span></label>
                        <input type="email" name="Email" id="Email"
                               class="form-control @error('Email') is-invalid @enderror"
                               value="{{ old('Email', $parent->Email) }}" placeholder="example@email.com" required>
                        @error('Email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="Address">ที่อยู่</label>
                    <textarea name="Address" id="Address"
                              class="form-control @error('Address') is-invalid @enderror"
                              rows="3">{{ old('Address', $parent->Address) }}</textarea>
                    @error('Address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div style="margin-top:2rem; display:flex; gap:0.75rem;">
                    <button type="submit" class="btn btn-primary" style="flex:1; justify-content:center;">
                        <i class="fas fa-save"></i> บันทึก
                    </button>
                    <a href="{{ route('admin.students.parents.index', $student->StudentID) }}"
                       class="btn btn-outline" style="justify-content:center;">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const userSelect = document.getElementById('user_select');
    if (!userSelect) return;

    userSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const userId = selectedOption.value;
        const firstName = selectedOption.getAttribute('data-firstname') || '';
        const lastName = selectedOption.getAttribute('data-lastname') || '';
        const citizenId = selectedOption.getAttribute('data-citizenid') || '';
        const phone = selectedOption.getAttribute('data-phone') || '';
        const email = selectedOption.getAttribute('data-email') || '';

        // Set hidden UserID
        document.getElementById('UserID').value = userId;

        // Prefill fields
        let displayFirstName = firstName;
        let selectedPrefix = '';
        const prefixes = ['นาย', 'นางสาว', 'นาง', 'ด.ช.', 'ด.ญ.'];
        for (const p of prefixes) {
            if (firstName.startsWith(p)) {
                selectedPrefix = p;
                displayFirstName = firstName.substring(p.length);
                break;
            }
        }

        const prefixSelect = document.getElementById('prefixSelect');
        if (prefixSelect) {
            prefixSelect.value = selectedPrefix;
            if (userId) {
                prefixSelect.setAttribute('disabled', 'disabled');
                // Add hidden input to preserve value when form is submitted if disabled
                let hiddenPrefix = document.getElementById('hidden_prefix');
                if (!hiddenPrefix) {
                    hiddenPrefix = document.createElement('input');
                    hiddenPrefix.type = 'hidden';
                    hiddenPrefix.name = 'prefix';
                    hiddenPrefix.id = 'hidden_prefix';
                    prefixSelect.parentNode.appendChild(hiddenPrefix);
                }
                hiddenPrefix.value = selectedPrefix;
            } else {
                prefixSelect.removeAttribute('disabled');
                const hiddenPrefix = document.getElementById('hidden_prefix');
                if (hiddenPrefix) hiddenPrefix.remove();
            }
        }

        const fields = {
            'FirstName': displayFirstName,
            'LastName': lastName,
            'CitizenID': citizenId,
            'Phone': phone,
            'Email': email
        };

        for (const [id, val] of Object.entries(fields)) {
            const el = document.getElementById(id);
            if (el) {
                el.value = val;
                if (userId) {
                    el.setAttribute('readonly', 'readonly');
                    el.style.backgroundColor = '#f1f5f9'; // read-only styling
                } else {
                    el.removeAttribute('readonly');
                    el.style.backgroundColor = '';
                }
            }
        }
    });

    // Run once on page load in case of validation back-redirect or pre-linked user
    if (userSelect.value) {
        userSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endsection
