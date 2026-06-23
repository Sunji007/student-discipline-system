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

                <div class="form-group">
                    <label class="form-label" for="TeacherID">รหัสครู / รหัสประจำตัว <span style="color:var(--red)">*</span></label>
                    <input type="text" name="TeacherID" id="TeacherID" class="form-control @error('TeacherID') is-invalid @enderror" value="{{ old('TeacherID') }}" required maxlength="10" placeholder="รหัสประจำตัวครู (สูงสุด 10 ตัวอักษร, ใช้เป็น Username)">
                    @error('TeacherID')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="FullName">ชื่อ-นามสกุล <span style="color:var(--red)">*</span></label>
                    <input type="text" name="FullName" id="FullName" class="form-control @error('FullName') is-invalid @enderror" value="{{ old('FullName') }}" required placeholder="ระบุชื่อจริงและนามสกุล...">
                    @error('FullName')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="Department">กลุ่มสาระการเรียนรู้ / แผนก</label>
                        <input type="text" name="Department" id="Department" class="form-control" value="{{ old('Department') }}" placeholder="เช่น วิทยาศาสตร์, คณิตศาสตร์">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="AdvisoryRoom">ห้องเรียนที่ดูแลที่ปรึกษา (ระบุ ชั้น/ห้อง)</label>
                        <input type="text" name="AdvisoryRoom" id="AdvisoryRoom" class="form-control" value="{{ old('AdvisoryRoom') }}" placeholder="เช่น 4/1, 1/2 (ถ้ามี)">
                    </div>
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
