@extends('layouts.app')

@section('title', 'เพิ่มเรื่องแจ้งเบาะแส')
@section('page-title', 'บันทึกเรื่องแจ้งเบาะแส')

@section('content')
<div style="max-width:640px;">
    <div class="card">
        <div class="card-header-bar">
            <h3>บันทึกเรื่องแจ้งเบาะแสใหม่</h3>
            <a href="{{ route('discipline.informant-reports.index') }}" class="btn btn-outline btn-sm">
                <i class="fas fa-arrow-left"></i> ย้อนกลับ
            </a>
        </div>
        <div class="card-body-pad">
            <div class="alert alert-warning" style="margin-bottom:1.25rem;">
                <i class="fas fa-shield-alt"></i>
                ข้อมูลการแจ้งเบาะแสจะถูกเก็บเป็นความลับ ผู้แจ้งจะไม่ถูกเปิดเผยตัวตน
            </div>

            <form method="POST"
                  action="{{ route('discipline.informant-reports.store') }}"
                  enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label class="form-label">รายละเอียดเรื่องที่แจ้ง <span style="color:var(--red)">*</span></label>
                    <textarea name="Description"
                              class="form-control {{ $errors->has('Description') ? 'is-invalid' : '' }}"
                              rows="7"
                              required
                              placeholder="อธิบายรายละเอียดให้ชัดเจน เช่น วัน เวลา สถานที่ พฤติกรรมที่พบ...">{{ old('Description') }}</textarea>
                    @error('Description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-top:0.35rem;">
                        ระบุรายละเอียดให้มากที่สุดเพื่อประโยชน์ในการตรวจสอบ
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">ไฟล์หลักฐาน (ถ้ามี)</label>
                    <input type="file" name="evidence" class="form-control"
                           accept=".pdf,.jpg,.jpeg,.png">
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-top:0.3rem;">
                        รองรับ PDF, JPG, PNG ขนาดไม่เกิน 5MB
                    </div>
                    @error('evidence')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div style="display:flex; gap:0.75rem; margin-top:0.5rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> ส่งเรื่องแจ้ง
                    </button>
                    <a href="{{ route('discipline.informant-reports.index') }}" class="btn btn-outline">
                        ยกเลิก
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection