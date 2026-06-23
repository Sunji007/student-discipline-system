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

                <div class="form-group">
                    <label class="form-label" for="StudentID">รหัสนักเรียน <span style="color:var(--red)">*</span></label>
                    <input type="text" name="StudentID" id="StudentID" class="form-control @error('StudentID') is-invalid @enderror" value="{{ old('StudentID') }}" required placeholder="ระบุรหัสนักเรียน (เช่น 12345)">
                    @error('StudentID')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="FullName">ชื่อ-นามสกุล <span style="color:var(--red)">*</span></label>
                    <input type="text" name="FullName" id="FullName" class="form-control @error('FullName') is-invalid @enderror" value="{{ old('FullName') }}" required placeholder="เด็กชาย/เด็กหญิง/นาย/นางสาว...">
                    @error('FullName')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
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
                        <i class="fas fa-save"></i> บันทึกข้อมูลและสร้างรหัสสมาชิก
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
