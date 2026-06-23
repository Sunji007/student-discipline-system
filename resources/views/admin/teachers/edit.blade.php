@extends('layouts.app')

@section('title', 'แก้ไขข้อมูลครู')
@section('page-title', 'แก้ไขข้อมูลครู')

@section('content')
<div style="max-width: 600px; margin: 0 auto;">
    <div style="margin-bottom: 1rem;">
        <a href="{{ route('admin.teachers.index') }}" class="btn btn-outline btn-sm">
            <i class="fas fa-arrow-left"></i> ย้อนกลับ
        </a>
    </div>

    <div class="card">
        <div class="card-header-bar">
            <h3>ข้อมูลของ {{ $teacher->user->FullName ?? '' }}</h3>
            <span style="font-size:0.8rem; color:var(--text-muted);">รหัส: {{ $teacher->TeacherID }}</span>
        </div>
        <div class="card-body-pad">
            <form method="POST" action="{{ route('admin.teachers.update', $teacher->TeacherID) }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label class="form-label" for="FullName">ชื่อ-นามสกุล <span style="color:var(--red)">*</span></label>
                    <input type="text" name="FullName" id="FullName" class="form-control @error('FullName') is-invalid @enderror" value="{{ old('FullName', $teacher->user->FullName ?? '') }}" required placeholder="ระบุชื่อจริงและนามสกุล...">
                    @error('FullName')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="Department">กลุ่มสาระการเรียนรู้ / แผนก</label>
                        <input type="text" name="Department" id="Department" class="form-control" value="{{ old('Department', $teacher->Department) }}" placeholder="เช่น วิทยาศาสตร์, คณิตศาสตร์">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="AdvisoryRoom">ห้องเรียนที่ดูแลที่ปรึกษา (ระบุ ชั้น/ห้อง)</label>
                        <input type="text" name="AdvisoryRoom" id="AdvisoryRoom" class="form-control" value="{{ old('AdvisoryRoom', $teacher->AdvisoryRoom) }}" placeholder="เช่น 4/1, 1/2 (ถ้ามี)">
                    </div>
                </div>

                <div style="margin-top:2rem;">
                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                        <i class="fas fa-save"></i> บันทึกการเปลี่ยนแปลง
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
