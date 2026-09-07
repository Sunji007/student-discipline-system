@extends('layouts.app')

@section('title', 'ยื่นเรื่องอุทธรณ์คะแนน')
@section('page-title', 'ยื่นเรื่องอุทธรณ์คะแนน')

@section('content')
<div style="max-width:640px;">
    <div class="card">
        <div class="card-header-bar">
            <h3>ยื่นเรื่องอุทธรณ์คะแนน</h3>
            <a href="{{ route('student.appeals.index') }}" class="btn btn-outline btn-sm">
                <i class="fas fa-arrow-left"></i> ย้อนกลับ
            </a>
        </div>
        <div class="card-body-pad">
            @if($records->isEmpty() && !$selectedRecord)
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle"></i>
                    ไม่มีรายการพฤติกรรมที่สามารถยื่นอุทธรณ์ได้ (ต้องเป็นรายการที่อนุมัติและยังไม่มีการอุทธรณ์)
                </div>
            @else
            <form method="POST" action="{{ route('student.appeals.store') }}" enctype="multipart/form-data">
                @csrf

                @if($selectedRecord)
                    <input type="hidden" name="RecordID" value="{{ $selectedRecord->RecordID }}">
                    
                    <div class="form-group">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.4rem;">
                            <label class="form-label" style="margin-bottom:0; font-weight:700; color:var(--text);">
                                รายการพฤติกรรมที่ต้องการอุทธรณ์ <span style="color:var(--red)">*</span>
                            </label>
                            <a href="{{ route('student.appeals.create') }}" style="font-size:0.75rem; color:var(--primary); text-decoration:underline;">
                                <i class="fas fa-list"></i> เลือกรายการอื่น
                            </a>
                        </div>

                        @php $srd = \Carbon\Carbon::parse($selectedRecord->RecordDate); @endphp
                        <div style="background:#f8fafc; border:1.5px solid #cbd5e1; border-radius:10px; padding:1rem 1.25rem; display:flex; justify-content:space-between; align-items:center; gap:1rem;">
                            <div>
                                <div style="font-size:1rem; font-weight:700; color:var(--navy);">
                                    {{ $selectedRecord->rule->RuleName }}
                                </div>
                                <div style="font-size:0.8rem; color:var(--text-muted); margin-top:0.25rem; display:flex; gap:0.75rem; align-items:center; flex-wrap:wrap;">
                                    <span><i class="fas fa-tag" style="color:var(--gold);"></i> {{ $selectedRecord->rule->Category }}</span>
                                    <span><i class="far fa-calendar-alt"></i> วันที่: {{ $srd->format('d/m/') . ($srd->year + 543) }}</span>
                                </div>
                                @if($selectedRecord->Description)
                                <div style="font-size:0.78rem; color:var(--text-sub); margin-top:0.35rem; background:#fff; padding:0.35rem 0.6rem; border-radius:6px; border:1px solid #e2e8f0;">
                                    {{ $selectedRecord->Description }}
                                </div>
                                @endif
                            </div>
                            <div style="text-align:right; flex-shrink:0;">
                                <div style="font-weight:800; font-size:1.35rem; color:{{ $selectedRecord->rule->RuleType === 'ตัดคะแนน' ? 'var(--red)' : 'var(--green)' }}; line-height:1;">
                                    {{ $selectedRecord->rule->RuleType === 'ตัดคะแนน' ? '-' : '+' }}{{ abs($selectedRecord->rule->ScoreModifier) }}
                                </div>
                                <div style="font-size:0.72rem; color:var(--text-muted); margin-top:0.2rem;">คะแนน</div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="form-group">
                        <label class="form-label">เลือกรายการที่ต้องการอุทธรณ์ <span style="color:var(--red)">*</span></label>
                        <select name="RecordID" class="form-control {{ $errors->has('RecordID') ? 'is-invalid' : '' }}" required>
                            <option value="">-- เลือกรายการพฤติกรรม --</option>
                            @foreach($records as $r)
                            @php $rd = \Carbon\Carbon::parse($r->RecordDate); @endphp
                            <option value="{{ $r->RecordID }}" {{ (old('RecordID', $selectedRecordId ?? '') === $r->RecordID) ? 'selected' : '' }}>
                                {{ $r->rule->RuleName }}
                                ({{ $r->rule->RuleType === 'ตัดคะแนน' ? '-' : '+' }}{{ abs($r->rule->ScoreModifier) }})
                                — {{ $rd->format('d/m/') . ($rd->year + 543) }}
                            </option>
                            @endforeach
                        </select>
                        @error('RecordID')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                @endif

                <div class="form-group">
                    <label class="form-label">เหตุผลการขออุทธรณ์ <span style="color:var(--red)">*</span></label>
                    <textarea name="Reason" class="form-control {{ $errors->has('Reason') ? 'is-invalid' : '' }}"
                               rows="6" required placeholder="อธิบายเหตุผลอย่างละเอียด ว่าทำไมคิดว่ารายการนี้ไม่ถูกต้อง...">{{ old('Reason') }}</textarea>
                    @error('Reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                    หลังยื่นเรื่องอุทธรณ์ ฝ่ายปกครองจะพิจารณาและแจ้งผลให้ทราบ รายการที่ยื่นแล้วไม่สามารถแก้ไขได้
                </div>

                <div style="display:flex; gap:0.75rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> ส่ง
                    </button>
                    <a href="{{ route('student.appeals.index') }}" class="btn btn-outline">ยกเลิก</a>
                </div>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection