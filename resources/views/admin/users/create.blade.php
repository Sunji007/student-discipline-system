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

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">ชื่อ-นามสกุล <span style="color:var(--red)">*</span></label>
                        <input type="text" name="FullName" class="form-control {{ $errors->has('FullName') ? 'is-invalid' : '' }}"
                               value="{{ old('FullName') }}" placeholder="ชื่อ-นามสกุลเต็ม">
                        @error('FullName')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">บทบาท <span style="color:var(--red)">*</span></label>
                        <select name="Role" class="form-control {{ $errors->has('Role') ? 'is-invalid' : '' }}"
                                id="roleSelect">
                            <option value="">เลือกบทบาท</option>
                            @foreach(['ผู้ดูแลระบบ','ฝ่ายปกครอง','ครู','นักเรียน','ผู้ปกครอง'] as $role)
                                <option value="{{ $role }}" {{ old('Role') === $role ? 'selected' : '' }}>{{ $role }}</option>
                            @endforeach
                        </select>
                        @error('Role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Username <span style="color:var(--red)">*</span></label>
                        <input type="text" name="Username" class="form-control {{ $errors->has('Username') ? 'is-invalid' : '' }}"
                               value="{{ old('Username') }}" placeholder="ภาษาอังกฤษและตัวเลขเท่านั้น"
                               oninput="this.value = this.value.replace(/[^a-zA-Z0-9]/g, '')">
                        <div style="font-size:0.75rem; color:var(--text-muted); margin-top:0.25rem;">ใช้ได้เฉพาะตัวอักษรภาษาอังกฤษ (a-z, A-Z) และตัวเลข (0-9) เท่านั้น</div>
                        @error('Username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password <span style="color:var(--red)">*</span></label>
                        <input type="password" name="Password" class="form-control {{ $errors->has('Password') ? 'is-invalid' : '' }}"
                               placeholder="ภาษาอังกฤษและตัวเลขเท่านั้น (อย่างน้อย 6 ตัว)"
                               oninput="this.value = this.value.replace(/[^a-zA-Z0-9]/g, '')">
                        <div style="font-size:0.75rem; color:var(--text-muted); margin-top:0.25rem;">ใช้ได้เฉพาะตัวอักษรภาษาอังกฤษ (a-z, A-Z) และตัวเลข (0-9) เท่านั้น</div>
                        @error('Password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">สถานะ</label>
                        <select name="Status" class="form-control">
                            <option value="ปกติ" {{ old('Status','ปกติ') === 'ปกติ' ? 'selected' : '' }}>ปกติ</option>
                            <option value="ระงับการใช้งาน" {{ old('Status') === 'ระงับการใช้งาน' ? 'selected' : '' }}>ระงับการใช้งาน</option>
                        </select>
                    </div>
                    <div class="form-group">
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
                            <label class="form-label">รหัสครู / รหัสประจำตัว <span style="color:var(--red)">*</span></label>
                            <input type="text" name="TeacherID" class="form-control @error('TeacherID') is-invalid @enderror" value="{{ old('TeacherID') }}" maxlength="10" placeholder="สูงสุด 10 ตัวอักษร">
                            @error('TeacherID')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">แผนก/กลุ่มสาระ</label>
                            <input type="text" name="Department" class="form-control" value="{{ old('Department') }}" placeholder="เช่น คณิตศาสตร์">
                        </div>
                        <div class="form-group">
                            <label class="form-label">ห้องที่ปรึกษา</label>
                            <input type="text" name="AdvisoryRoom" class="form-control" value="{{ old('AdvisoryRoom') }}" placeholder="เช่น 6/1">
                            <div style="font-size:0.75rem; color:var(--text-muted); margin-top:0.25rem;">ระบุเลขชั้น/เลขห้อง เช่น 6/1 (ไม่ต้องใส่ ม.)</div>
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
                            <label class="form-label">รหัสนักเรียน <span style="color:var(--red)">*</span></label>
                            <input type="text" name="StudentID" class="form-control" value="{{ old('StudentID') }}" placeholder="เช่น 6501001">
                        </div>
                        <div class="form-group">
                            <label class="form-label">ระดับชั้น</label>
                            <select name="GradeLevel" class="form-control">
                                <option value="">เลือกระดับชั้น</option>
                                @foreach(['ม.1','ม.2','ม.3','ม.4','ม.5','ม.6'] as $g)
                                    <option value="{{ $g }}" {{ old('GradeLevel') === $g ? 'selected' : '' }}>{{ $g }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">ห้อง</label>
                            <input type="text" name="Classroom" class="form-control" value="{{ old('Classroom') }}" placeholder="ระบุเลขห้อง (เฉพาะตัวเลข เช่น 1)" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>
                    </div>
                </div>

                <div style="display:flex; gap:0.75rem; margin-top:1.5rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> บันทึกข้อมูล
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const roleSelect = document.getElementById('roleSelect');
    const sections = {
        'ครู':          'extra-teacher',
        'ฝ่ายปกครอง':  'extra-discipline',
        'นักเรียน':     'extra-student',
    };

    function updateSections() {
        Object.values(sections).forEach(id => {
            document.getElementById(id).style.display = 'none';
        });
        const selected = sections[roleSelect.value];
        if (selected) document.getElementById(selected).style.display = 'block';
    }

    roleSelect.addEventListener('change', updateSections);
    updateSections(); // initial
</script>
@endpush
@endsection