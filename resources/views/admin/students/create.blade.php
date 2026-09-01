@extends('layouts.app')

@section('title', 'เพิ่มนักเรียนใหม่')
@section('page-title', 'เพิ่มนักเรียนใหม่')

@section('content')
<div style="max-width: 600px; margin: 0 auto;">
    <div style="margin-bottom: 1rem;">
        <a href="{{ route('admin.students.index') }}" class="btn btn-outline btn-sm">
            <i class="fas fa-arrow-left"></i> ย้อนกลับ
        </a>
    </div>

    <div class="card">
        <div class="card-header-bar">
            <h3>กรอกข้อมูลนักเรียน</h3>
        </div>
        <div class="card-body-pad">
            <form method="POST" action="{{ route('admin.students.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="StudentID">รหัสนักเรียน <span style="color:var(--text-muted)">(สร้างอัตโนมัติ)</span></label>
                        <input type="text" name="StudentID" id="StudentID" class="form-control @error('StudentID') is-invalid @enderror" value="{{ old('StudentID', $nextStudentId) }}" readonly style="background-color: #f5f5f5; cursor: not-allowed;">
                        @error('StudentID')
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
                            <input type="text" name="FirstName" id="FirstName" class="form-control @error('FirstName') is-invalid @enderror" 
                                   value="{{ old('FirstName') }}" 
                                   oninput="this.value = this.value.replace(/[^ก-๙]/g, '')" required placeholder="เช่น เก่ง" style="flex: 1;">
                        </div>
                        @error('FirstName')
                            <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="LastName">นามสกุล <span style="color:var(--red)">*</span></label>
                        <input type="text" name="LastName" id="LastName" class="form-control @error('LastName') is-invalid @enderror" 
                               value="{{ old('LastName') }}" 
                               oninput="this.value = this.value.replace(/[^ก-๙]/g, '')" required placeholder="เช่น เรียนดี">
                        @error('LastName')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="GradeLevel">ระดับชั้น <span style="color:var(--red)">*</span></label>
                        <select name="GradeLevel" id="GradeLevel" class="form-control" required>
                            <option value="">เลือกชั้นปี</option>
                            @foreach(['ม.1','ม.2','ม.3','ม.4','ม.5','ม.6'] as $g)
                                <option value="{{ $g }}" {{ old('GradeLevel') === $g ? 'selected' : '' }}>{{ $g }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="Classroom">ห้องเรียน <span style="color:var(--red)">*</span></label>
                        <input type="text" name="Classroom" id="Classroom" class="form-control" value="{{ old('Classroom') }}" required placeholder="ระบุเลขห้อง (เฉพาะตัวเลข เช่น 1)" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">เพศ <span style="color:var(--red)">*</span></label>
                    <div style="display:flex; gap:1.5rem; margin-top:0.35rem;">
                        <label style="display:flex; align-items:center; gap:0.35rem; cursor:pointer;">
                            <input type="radio" name="Gender" value="ชาย" {{ old('Gender') === 'ชาย' ? 'checked' : '' }} required style="accent-color:var(--primary);">
                            ชาย
                        </label>
                        <label style="display:flex; align-items:center; gap:0.35rem; cursor:pointer;">
                            <input type="radio" name="Gender" value="หญิง" {{ old('Gender') === 'หญิง' ? 'checked' : '' }} style="accent-color:var(--primary);">
                            หญิง
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="Photo">รูปภาพนักเรียน</label>
                    <input type="file" name="Photo" id="Photo" class="form-control @error('Photo') is-invalid @enderror" accept="image/*">
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-top:0.25rem;">รองรับไฟล์ภาพสกุล jpeg, png, jpg ขนาดไม่เกิน 2MB</div>
                    @error('Photo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
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
    const gradeLevelSelect = document.getElementById('GradeLevel');
    const classroomInput = document.getElementById('Classroom');
    const studentIDInput = document.getElementById('StudentID');

    async function fetchNextStudentID() {
        const grade = gradeLevelSelect.value;
        const classroom = classroomInput.value;

        if (!grade || !classroom) {
            studentIDInput.value = '';
            return;
        }

        try {
            const url = `{{ route('admin.students.get-next-id') }}?grade=${encodeURIComponent(grade)}&classroom=${encodeURIComponent(classroom)}`;
            const response = await fetch(url);
            const data = await response.json();
            studentIDInput.value = data.StudentID;
        } catch (error) {
            console.error('Error fetching student ID:', error);
        }
    }

    gradeLevelSelect.addEventListener('change', fetchNextStudentID);
    classroomInput.addEventListener('input', fetchNextStudentID);
</script>
@endpush
