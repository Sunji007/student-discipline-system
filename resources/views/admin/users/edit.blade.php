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

                <div style="background:#faf8f4; padding:0.75rem 1rem; border-radius:2px; margin-bottom:1rem; font-size:0.82rem; color:var(--text-muted);">
                    <i class="fas fa-info-circle" style="color:var(--gold); margin-right:0.35rem;"></i>
                    Username: <strong>{{ $user->Username }}</strong>
                    &nbsp;|&nbsp; บทบาท: <span class="badge badge-navy" style="font-size:0.7rem;">{{ $user->Role }}</span>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">ชื่อ-นามสกุล <span style="color:var(--red)">*</span></label>
                        <input type="text" name="FullName" class="form-control {{ $errors->has('FullName') ? 'is-invalid' : '' }}"
                               value="{{ old('FullName', $user->FullName) }}" required>
                        @error('FullName')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">สถานะ</label>
                        <select name="Status" class="form-control">
                            <option value="ปกติ" {{ old('Status', $user->Status) === 'ปกติ' ? 'selected' : '' }}>ปกติ</option>
                            <option value="ระงับการใช้งาน" {{ old('Status', $user->Status) === 'ระงับการใช้งาน' ? 'selected' : '' }}>ระงับการใช้งาน</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">รีเซ็ตรหัสผ่าน (เว้นว่างถ้าไม่ต้องการเปลี่ยน)</label>
                    <input type="password" name="Password" class="form-control"
                           placeholder="รหัสผ่านใหม่ (อย่างน้อย 6 ตัวอักษร)">
                    @error('Password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">ข้อมูลเพิ่มเติม</label>
                    <input type="text" name="AdditionalInfo" class="form-control"
                           value="{{ old('AdditionalInfo', $user->AdditionalInfo) }}" placeholder="หมายเหตุ">
                </div>

                <div style="display:flex; gap:0.75rem; margin-top:0.5rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> บันทึกการแก้ไข
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection