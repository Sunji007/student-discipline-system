@extends('layouts.app')

@section('title', 'เพิ่มเกณฑ์ประเมินพฤติกรรม')
@section('page-title', 'เพิ่มเกณฑ์ประเมินพฤติกรรม')

@section('content')
<div style="max-width:580px;">
    <div class="card">
        <div class="card-header-bar">
            <h3>เพิ่มเกณฑ์ประเมินพฤติกรรมใหม่</h3>
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
                        <select name="Category" id="categorySelect" class="form-control {{ $errors->has('Category') ? 'is-invalid' : '' }}" required>
                            <option value="">เลือกหมวดหมู่</option>
                        </select>
                        @error('Category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">ชื่อเกณฑ์ประเมินพฤติกรรม <span style="color:var(--red)">*</span></label>
                    <input type="text" name="RuleName"
                           class="form-control {{ $errors->has('RuleName') ? 'is-invalid' : '' }}"
                           value="{{ old('RuleName') }}" placeholder="อธิบายพฤติกรรมที่ชัดเจน">
                    @error('RuleName')<div class="invalid-feedback" style="display:block; color:var(--red); font-size:0.8rem; margin-top:0.3rem;">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">คะแนนที่เปลี่ยนแปลง <span style="color:var(--red)">*</span></label>
                    <div style="display:flex; align-items:center; gap:0.75rem;">
                        <input type="text" name="ScoreModifier" id="scoreInput"
                               class="form-control {{ $errors->has('ScoreModifier') ? 'is-invalid' : '' }}"
                               value="{{ old('ScoreModifier') }}"
                               inputmode="numeric" pattern="[0-9]*"
                               placeholder="ระบุจำนวน (สูงสุด 100)">
                        <div id="scorePreview" style="font-size:0.9rem; font-weight:600; min-width:80px;"></div>
                    </div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-top:0.3rem;">
                        ระบุเป็นตัวเลขบวกเสมอ (สูงสุด 100 คะแนน) ระบบจะจัดการเครื่องหมายให้อัตโนมัติตามประเภท
                    </div>
                    @error('ScoreModifier')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div style="display:flex; gap:0.75rem; margin-top:0.5rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> บันทึก
                    </button>
                    <a href="{{ route('discipline.behavior-rules.index') }}" class="btn btn-outline">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const typeSelect     = document.getElementById('ruleTypeSelect');
    const categorySelect = document.getElementById('categorySelect');
    const scoreInput     = document.getElementById('scoreInput');
    const scorePreview   = document.getElementById('scorePreview');
    const oldCategory    = "{{ old('Category') }}";

    const deductCategories = [
        'การแต่งกายและทรงผม',
        'ความประพฤติและกริยามารยาท',
        'สารเสพติดและของต้องห้าม',
        'การใช้เครื่องมือสื่อสาร',
        'การเข้าเรียนและระเบียบสถานศึกษา'
    ];

    const addCategories = [
        'ความดีและจิตอาสา',
        'กิจกรรมและสร้างชื่อเสียง',
        'ความประพฤติดีเด่นและวินัย',
        'คุณธรรมและศาสนกิจ',
        'ความเป็นผู้นำและการมีส่วนร่วม',
        'วิชาการและความขยันหมั่นเพียร'
    ];

    function updateCategoryOptions() {
        const type = typeSelect.value;
        const currentVal = categorySelect.value || oldCategory;

        categorySelect.innerHTML = '<option value="">เลือกหมวดหมู่</option>';

        let targetList = [];
        if (type === 'ตัดคะแนน') {
            targetList = deductCategories;
        } else if (type === 'เพิ่มคะแนน') {
            targetList = addCategories;
        } else {
            targetList = [...deductCategories, ...addCategories];
        }

        targetList.forEach(cat => {
            const opt = document.createElement('option');
            opt.value = cat;
            opt.textContent = cat;
            if (currentVal && currentVal === cat) {
                opt.selected = true;
            }
            categorySelect.appendChild(opt);
        });
    }

    function updatePreview() {
        const type  = typeSelect.value;
        // Strip any non-digit character first
        scoreInput.value = scoreInput.value.replace(/[^0-9]/g, '');
        let score = parseInt(scoreInput.value) || 0;
        
        // Enforce maximum 100 limit in realtime
        if (score > 100) {
            score = 100;
            scoreInput.value = 100;
        } else if (score < 1 && scoreInput.value !== '') {
            score = 1;
            scoreInput.value = 1;
        }

        if (!type || !score) { scorePreview.textContent = ''; return; }
        const isDeduct = type === 'ตัดคะแนน';
        scorePreview.textContent = (isDeduct ? '-' : '+') + score + ' คะแนน';
        scorePreview.style.color = isDeduct ? 'var(--red)' : 'var(--green)';
    }

    typeSelect.addEventListener('change', function() {
        updateCategoryOptions();
        updatePreview();
    });
    scoreInput.addEventListener('input', updatePreview);

    // Initialize on load
    updateCategoryOptions();
    updatePreview();
</script>
@endpush
@endsection