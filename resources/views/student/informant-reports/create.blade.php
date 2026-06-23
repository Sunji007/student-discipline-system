@extends('layouts.app')

@section('title', 'แจ้งเบาะแสพฤติกรรม')
@section('page-title', 'แจ้งเบาะแสพฤติกรรม')

@section('content')
<div class="page-header" style="display:flex; justify-content:space-between; align-items:center;">
    <div>
        <h2>แจ้งข้อมูลเบาะแสพฤติกรรม</h2>
        <p>รายงานพฤติกรรมที่ไม่เหมาะสมหรือการกระทำผิดวินัยเพื่อความปลอดภัยในโรงเรียน</p>
    </div>
    <a href="{{ route($layoutPrefix . '.informant-reports.index') }}" class="btn btn-outline">
        <i class="fas fa-arrow-left"></i> ย้อนกลับ
    </a>
</div>

<div class="card" style="max-width:800px; margin: 0 auto;">
    <div class="card-header-bar">
        <h3><i class="fas fa-bullhorn" style="color:var(--gold); margin-right:0.5rem;"></i>กรอกข้อมูลเบาะแส</h3>
    </div>
    <div class="card-body-pad">
        <form method="POST" action="{{ route($layoutPrefix . '.informant-reports.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <label class="form-label" for="Title">หัวข้อเบาะแส <span style="color:var(--red);">*</span></label>
                <input type="text" name="Title" id="Title" class="form-control @error('Title') is-invalid @enderror" value="{{ old('Title') }}" placeholder="เช่น แอบสูบบุหรี่หลังส้วม, ทะเลาะวิวาทกลุ่มใหญ่" required>
                @error('Title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="Category">ประเภทพฤติกรรม <span style="color:var(--red);">*</span></label>
                    <select name="Category" id="Category" class="form-control @error('Category') is-invalid @enderror" required>
                        <option value="">-- เลือกประเภท --</option>
                        <option value="สิ่งเสพติดและอบายมุข" {{ old('Category') == 'สิ่งเสพติดและอบายมุข' ? 'selected' : '' }}>สิ่งเสพติดและอบายมุข (บุหรี่, ยาเสพติด, สุรา)</option>
                        <option value="ความประพฤติทั่วไป" {{ old('Category') == 'ความประพฤติทั่วไป' ? 'selected' : '' }}>ความประพฤติทั่วไป (ชกต่อย, ทะเลาะวิวาท, ทรัพย์สินโรงเรียนเสียหาย)</option>
                        <option value="การเข้าเรียน" {{ old('Category') == 'การเข้าเรียน' ? 'selected' : '' }}>การเข้าเรียน (หนีเรียน, ออกนอกโรงเรียนโดยไม่ได้รับอนุญาต)</option>
                        <option value="การแต่งกาย" {{ old('Category') == 'การแต่งกาย' ? 'selected' : '' }}>การแต่งกายผิดระเบียบ</option>
                        <option value="อื่นๆ" {{ old('Category') == 'อื่นๆ' ? 'selected' : '' }}>อื่นๆ</option>
                    </select>
                    @error('Category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="StudentID">นักเรียนที่เกี่ยวข้อง (หากทราบชื่อหรือรหัส)</label>
                    <select name="StudentID" id="StudentID" class="form-control @error('StudentID') is-invalid @enderror">
                        <option value="">-- ค้นหา/เลือกนักเรียน --</option>
                        @foreach($students as $student)
                            <option value="{{ $student->StudentID }}" {{ old('StudentID') == $student->StudentID ? 'selected' : '' }}>
                                {{ $student->StudentID }} - {{ $student->FullName }} ({{ $student->classroom_display }})
                            </option>
                        @endforeach
                    </select>
                    @error('StudentID')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="Description">รายละเอียดเบาะแส <span style="color:var(--red);">*</span></label>
                <textarea name="Description" id="Description" rows="5" class="form-control @error('Description') is-invalid @enderror" placeholder="ระบุเหตุการณ์ วันเวลา สถานที่ และบุคคลที่พบเห็นอย่างละเอียด..." required style="resize:vertical;">{{ old('Description') }}</textarea>
                @error('Description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="evidence">แนบหลักฐาน (ถ้ามี - รองรับรูปภาพ หรือ PDF)</label>
                <input type="file" name="evidence" id="evidence" class="form-control @error('evidence') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf">
                <small style="color:var(--text-muted); display:block; margin-top:0.25rem;">ขนาดไฟล์สูงสุด 5MB</small>
                @error('evidence')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group" style="margin: 1.5rem 0;">
                <label class="btn btn-outline" style="display:inline-flex; align-items:center; gap:0.5rem; cursor:pointer; font-weight:normal; border-color:#d8d0c0; background:#faf8f4;">
                    <input type="checkbox" name="IsAnonymous" value="1" {{ old('IsAnonymous') ? 'checked' : '' }} style="width:16px; height:16px;">
                    ส่งข้อมูลแบบไม่เปิดเผยตัวตน (ไม่ระบุชื่อผู้แจ้งต่อฝ่ายปกครอง)
                </label>
            </div>

            <div style="text-align:right; border-top:1px solid #ede8e0; padding-top:1.25rem; margin-top:1rem;">
                <button type="submit" class="btn btn-gold">
                    <i class="fas fa-paper-plane"></i> ส่งรายงานเบาะแส
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
