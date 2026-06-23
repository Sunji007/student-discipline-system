@extends('layouts.app')

@section('title', 'เพิ่มกฎเกณฑ์')
@section('page-title', 'เพิ่มกฎเกณฑ์พฤติกรรม')

@section('content')
<div style="max-width:580px;">
    <div class="card">
        <div class="card-header-bar">
            <h3>เพิ่มกฎเกณฑ์ใหม่</h3>
            <a href="{{ route('discipline.behavior-rules.index') }}" class="btn btn-outline btn-sm">
                <i class="fas fa-arrow-left"></i> ย้อนกลับ
            </a>
        </div>
        <div class="card-body-pad">
            <form method="POST" action="{{ route('discipline.behavior-rules.store') }}">
                @csrf

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">ประเภท <span style="color:var(--red)">*</span></label>
                        <select name="RuleType" class="form-control {{ $errors->has('RuleType') ? 'is-invalid' : '' }}" id="ruleTypeSelect">
                            <option value="">เลือกประเภท</option>
                            <option value="ตัดคะแนน" {{ old('RuleType') === 'ตัดคะแนน' ? 'selected' : '' }}>▼ ตัดคะแนน</option>
                            <option value="เพิ่มคะแนน" {{ old('RuleType') === 'เพิ่มคะแนน' ? 'selected' : '' }}>▲ เพิ่มคะแนน</option>
                        </select>
                        @error('RuleType')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">หมวดหมู่ <span style="color:var(--red)">*</span></label>
                        <input type="text" name="Category"
                               class="form-control {{ $errors->has('Category') ? 'is-invalid' : '' }}"
                               value="{{ old('Category') }}" list="category-list" placeholder="เช่น การแต่งกาย, ความประพฤติ">
                        <datalist id="category-list">
                            @foreach(['การแต่งกาย','การเรียน','ความประพฤติ','การเข้าแถว','กิจกรรม','ความดี'] as $cat)
                                <option value="{{ $cat }}">
                            @endforeach
                        </datalist>
                        @error('Category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">ชื่อกฎเกณฑ์ <span style="color:var(--red)">*</span></label>
                    <input type="text" name="RuleName"
                           class="form-control {{ $errors->has('RuleName') ? 'is-invalid' : '' }}"
                           value="{{ old('RuleName') }}" placeholder="อธิบายพฤติกรรมที่ชัดเจน">
                    @error('RuleName')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">คะแนนที่เปลี่ยนแปลง <span style="color:var(--red)">*</span></label>
                    <div style="display:flex; align-items:center; gap:0.75rem;">
                        <input type="number" name="ScoreModifier" id="scoreInput"
                               class="form-control {{ $errors->has('ScoreModifier') ? 'is-invalid' : '' }}"
                               value="{{ old('ScoreModifier') }}" min="1" max="100"
                               placeholder="ระบุจำนวน (ไม่ต้องใส่เครื่องหมาย)">
                        <div id="scorePreview" style="font-size:0.9rem; font-weight:600; min-width:80px;"></div>
                    </div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-top:0.3rem;">
                        ระบุเป็นตัวเลขบวกเสมอ ระบบจะจัดการเครื่องหมายให้อัตโนมัติตามประเภท
                    </div>
                    @error('ScoreModifier')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div style="display:flex; gap:0.75rem; margin-top:0.5rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> บันทึกกฎเกณฑ์
                    </button>
                    <a href="{{ route('discipline.behavior-rules.index') }}" class="btn btn-outline">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const typeSelect  = document.getElementById('ruleTypeSelect');
    const scoreInput  = document.getElementById('scoreInput');
    const scorePreview = document.getElementById('scorePreview');

    function updatePreview() {
        const type  = typeSelect.value;
        const score = parseInt(scoreInput.value) || 0;
        if (!type || !score) { scorePreview.textContent = ''; return; }
        const isDeduct = type === 'ตัดคะแนน';
        scorePreview.textContent = (isDeduct ? '-' : '+') + score + ' คะแนน';
        scorePreview.style.color = isDeduct ? 'var(--red)' : 'var(--green)';
    }

    typeSelect.addEventListener('change', updatePreview);
    scoreInput.addEventListener('input', updatePreview);
</script>
@endpush
@endsection