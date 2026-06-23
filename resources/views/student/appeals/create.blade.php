@extends('layouts.app')

@section('title', 'ยื่นคำร้องโต้แย้ง')
@section('page-title', 'ยื่นคำร้องโต้แย้งคะแนน')

@section('content')
<div style="max-width:640px;">
    <div class="card">
        <div class="card-header-bar">
            <h3>ยื่นคำร้องโต้แย้ง</h3>
            <a href="{{ route('student.appeals.index') }}" class="btn btn-outline btn-sm">
                <i class="fas fa-arrow-left"></i> ย้อนกลับ
            </a>
        </div>
        <div class="card-body-pad">
            @if($records->isEmpty())
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle"></i>
                    ไม่มีรายการพฤติกรรมที่สามารถยื่นคำร้องได้ (ต้องเป็นรายการที่อนุมัติแล้วและยังไม่มีคำร้อง)
                </div>
            @else
            <form method="POST" action="{{ route('student.appeals.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label class="form-label">เลือกรายการที่ต้องการโต้แย้ง <span style="color:var(--red)">*</span></label>
                    <select name="RecordID" class="form-control {{ $errors->has('RecordID') ? 'is-invalid' : '' }}" required>
                        <option value="">เลือกรายการ</option>
                        @foreach($records as $r)
                        <option value="{{ $r->RecordID }}" {{ old('RecordID') === $r->RecordID ? 'selected' : '' }}>
                            {{ $r->rule->RuleName }}
                            ({{ $r->rule->RuleType === 'ตัดคะแนน' ? '-' : '+' }}{{ abs($r->rule->ScoreModifier) }})
                            — {{ \Carbon\Carbon::parse($r->RecordDate)->format('d/m/Y') }}
                        </option>
                        @endforeach
                    </select>
                    @error('RecordID')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">เหตุผลการโต้แย้ง <span style="color:var(--red)">*</span></label>
                    <textarea name="Reason" class="form-control {{ $errors->has('Reason') ? 'is-invalid' : '' }}"
                              rows="6" required placeholder="อธิบายเหตุผลอย่างละเอียด ว่าทำไมคิดว่ารายการนี้ไม่ถูกต้อง...">{{ old('Reason') }}</textarea>
                    @error('Reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-top:0.35rem;">
                        กรุณาระบุรายละเอียดอย่างน้อย 20 ตัวอักษร
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">ไฟล์หลักฐาน (ถ้ามี)</label>
                    <input type="file" name="evidence" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-top:0.35rem;">
                        รองรับไฟล์ PDF, JPG, PNG ขนาดไม่เกิน 5MB
                    </div>
                    @error('evidence')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div style="background:#faf8f4; border:1px solid #e8e3db; border-radius:2px; padding:0.875rem; margin-bottom:1rem; font-size:0.82rem; color:var(--text-muted);">
                    <i class="fas fa-info-circle" style="color:var(--gold); margin-right:0.35rem;"></i>
                    หลังยื่นคำร้อง ฝ่ายปกครองจะพิจารณาและแจ้งผลให้ทราบ คำร้องที่ยื่นแล้วไม่สามารถแก้ไขได้
                </div>

                <div style="display:flex; gap:0.75rem;">
                    <button type="submit" class="btn btn-gold">
                        <i class="fas fa-paper-plane"></i> ยื่นคำร้อง
                    </button>
                    <a href="{{ route('student.appeals.index') }}" class="btn btn-outline">ยกเลิก</a>
                </div>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection