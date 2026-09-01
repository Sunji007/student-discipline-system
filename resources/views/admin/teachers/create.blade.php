@extends('layouts.app')

@section('title', 'เพิ่มครูใหม่')
@section('page-title', 'เพิ่มครูใหม่')

@section('content')
<div style="max-width: 600px; margin: 0 auto;">
    <div style="margin-bottom: 1rem;">
        <a href="{{ route('admin.teachers.index') }}" class="btn btn-outline btn-sm">
            <i class="fas fa-arrow-left"></i> ย้อนกลับ
        </a>
    </div>

    <div class="card">
        <div class="card-header-bar">
            <h3>กรอกข้อมูลครู</h3>
        </div>
        <div class="card-body-pad">
            <form method="POST" action="{{ route('admin.teachers.store') }}">
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

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="TeacherID">รหัสครู <span style="color:var(--text-muted)">(สร้างอัตโนมัติ)</span></label>
                        <input type="text" name="TeacherID" id="TeacherID" class="form-control @error('TeacherID') is-invalid @enderror" value="{{ old('TeacherID', $nextTeacherId) }}" readonly style="background-color: #f5f5f5; cursor: not-allowed;">
                        @error('TeacherID')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="CitizenID">รหัสบัตรประชาชน <span style="color:var(--red)">*</span></label>
                        <input type="text" name="CitizenID" id="CitizenID" class="form-control @error('CitizenID') is-invalid @enderror" value="{{ old('CitizenID') }}" required placeholder="เช่น 1234567890123" maxlength="13" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        @error('CitizenID')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="FirstName">ชื่อจริง <span style="color:var(--red)">*</span></label>
                        <div style="display: flex; gap: 0.5rem;">
                            <select name="prefix" class="form-control" style="width: 120px; flex-shrink: 0;" required>
                                <option value="">คำนำหน้า</option>
                                <option value="นาย" {{ old('prefix') === 'นาย' ? 'selected' : '' }}>นาย</option>
                                <option value="นาง" {{ old('prefix') === 'นาง' ? 'selected' : '' }}>นาง</option>
                                <option value="นางสาว" {{ old('prefix') === 'นางสาว' ? 'selected' : '' }}>นางสาว</option>
                                <option value="ด.ช." {{ old('prefix') === 'ด.ช.' ? 'selected' : '' }}>ด.ช.</option>
                                <option value="ด.ญ." {{ old('prefix') === 'ด.ญ.' ? 'selected' : '' }}>ด.ญ.</option>
                            </select>
                            <input type="text" name="FirstName" id="FirstName" class="form-control @error('FirstName') is-invalid @enderror" value="{{ old('FirstName') }}" required placeholder="เช่น นูรีดา" style="flex: 1;">
                        </div>
                        @error('FirstName')
                            <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="LastName">นามสกุล <span style="color:var(--red)">*</span></label>
                        <input type="text" name="LastName" id="LastName" class="form-control @error('LastName') is-invalid @enderror" value="{{ old('LastName') }}" required placeholder="เช่น สาและ">
                        @error('LastName')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label" for="Department">กลุ่มสาระการเรียนรู้ / แผนก</label>
                    <select name="department_id" id="Department" class="form-control">
                        <option value="">-- เลือกกลุ่มสาระการเรียนรู้ / แผนก --</option>
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
                                <input type="text" name="advisory_rooms[]" id="AdvisoryRoom" class="form-control" placeholder="เช่น 4/1" style="flex: 1;">
                                <button type="button" class="btn btn-outline" onclick="addRoomInput()" style="padding: 0.5rem 0.75rem;"><i class="fas fa-plus"></i> เพิ่มห้อง</button>
                            </div>
                        @else
                            @foreach($oldRooms as $index => $room)
                                <div style="display: flex; gap: 0.5rem; align-items: center; margin-top: {{ $index > 0 ? '0.5rem' : '0' }};">
                                    <input type="text" name="advisory_rooms[]" {!! $index === 0 ? 'id="AdvisoryRoom"' : '' !!} class="form-control" value="{{ $room }}" placeholder="เช่น 4/1" style="flex: 1;">
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

                <div style="margin-top:2rem;">
                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                        <i class="fas fa-save"></i> บันทึก
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const deptSelect = document.getElementById('Department');
        const advisoryInput = document.getElementById('AdvisoryRoom');
        const teacherIDInput = document.getElementById('TeacherID');

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

        async function updateTeacherID() {
            if (!deptSelect || !advisoryInput || !teacherIDInput) return;

            const dept = deptSelect.value;
            const room = advisoryInput.value.trim();

            if (!dept || !room) {
                teacherIDInput.value = '';
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

        // Only run on load if the value hasn't been set by old()
        @if(!old('TeacherID'))
        updateTeacherID();
        @endif

        deptSelect.addEventListener('change', updateTeacherID);
        advisoryInput.addEventListener('input', updateTeacherID);

        // Prevent duplicate rooms on submit
        const form = advisoryInput.closest('form');
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
                errorMsg.innerHTML = '<i class="fas fa-exclamation-circle"></i> ห้องเรียนที่ปรึกษาต้องไม่ซ้ำกัน';
            }

            let hasDuplicate = false;
            let hasFormatError = false;
            
            const regex = /^(ม\.)?\s*\d+\s*[\/\-]\s*\d+$/;
            
            // 1. Check format first
            inputs.forEach(input => {
                const val = input.value.trim();
                if (val !== '' && !regex.test(val)) {
                    input.style.borderColor = 'var(--red, #ef4444)';
                    input.style.backgroundColor = '#fef2f2';
                    hasFormatError = true;
                }
            });

            // 2. Check duplicates
            for (let i = 0; i < inputs.length; i++) {
                const val1 = values[i];
                if (val1 === '' || !regex.test(inputs[i].value.trim())) continue;
                
                for (let j = i + 1; j < inputs.length; j++) {
                    const val2 = values[j];
                    if (val2 === '' || !regex.test(inputs[j].value.trim())) continue;
                    
                    if (val1 === val2) {
                        inputs[i].style.borderColor = 'var(--red, #ef4444)';
                        inputs[i].style.backgroundColor = '#fef2f2';
                        inputs[j].style.borderColor = 'var(--red, #ef4444)';
                        inputs[j].style.backgroundColor = '#fef2f2';
                        hasDuplicate = true;
                    }
                }
            }

            if (hasFormatError || hasDuplicate) {
                if (errorMsg) {
                    errorMsg.style.display = 'block';
                    if (hasFormatError && hasDuplicate) {
                        errorMsg.innerHTML = '<i class="fas fa-exclamation-circle"></i> รูปแบบห้องเรียนไม่ถูกต้อง (เช่น 4/1) และห้ามกรอกห้องเรียนซ้ำกัน';
                    } else if (hasFormatError) {
                        errorMsg.innerHTML = '<i class="fas fa-exclamation-circle"></i> รูปแบบห้องเรียนไม่ถูกต้อง (ระบุ ชั้น/ห้อง เช่น 4/1, 1/2)';
                    } else {
                        errorMsg.innerHTML = '<i class="fas fa-exclamation-circle"></i> ห้องเรียนที่ปรึกษาต้องไม่ซ้ำกัน';
                    }
                }
                return true;
            }
            return false;
        }

        if (roomsContainer) {
            roomsContainer.addEventListener('input', function(e) {
                if (e.target.matches('input[name="advisory_rooms[]"]')) {
                    validateAdvisoryRooms();
                }
            });
        }

        if (form) {
            form.addEventListener('submit', function(e) {
                if (validateAdvisoryRooms()) {
                    e.preventDefault();
                    if (roomsContainer) {
                        roomsContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                    return false;
                }
            });
        }
    });
</script>
@endpush
