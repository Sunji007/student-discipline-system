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

                <div class="form-group">
                    <label class="form-label" for="FullName">ชื่อ-นามสกุลผู้ปกครอง <span style="color:var(--red)">*</span></label>
                    <input type="text" name="FullName" id="FullName"
                           class="form-control @error('FullName') is-invalid @enderror"
                           value="{{ old('FullName', $parent->FullName) }}"
                           required>
                    @error('FullName')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

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

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="Phone">เบอร์โทรศัพท์</label>
                        <input type="text" name="Phone" id="Phone"
                               class="form-control @error('Phone') is-invalid @enderror"
                               value="{{ old('Phone', $parent->Phone) }}" placeholder="0xx-xxx-xxxx">
                        @error('Phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="Email">อีเมล</label>
                        <input type="email" name="Email" id="Email"
                               class="form-control @error('Email') is-invalid @enderror"
                               value="{{ old('Email', $parent->Email) }}" placeholder="example@email.com">
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
                        <i class="fas fa-save"></i> บันทึกการเปลี่ยนแปลง
                    </button>
                    <a href="{{ route('admin.students.parents.index', $student->StudentID) }}"
                       class="btn btn-outline" style="justify-content:center;">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
