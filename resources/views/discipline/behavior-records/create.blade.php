@extends('layouts.app')

@section('title', 'บันทึกพฤติกรรม')
@section('page-title', 'บันทึกพฤติกรรมนักเรียน')

@section('content')
<div style="max-width:640px;">
    <div class="card">
        <div class="card-header-bar">
            <h3>บันทึกพฤติกรรมใหม่</h3>
            <a href="{{ route('discipline.behavior-records.index') }}" class="btn btn-outline btn-sm">
                <i class="fas fa-arrow-left"></i> ย้อนกลับ
            </a>
        </div>
        <div class="card-body-pad">
            <form method="POST" action="{{ route('discipline.behavior-records.store') }}">
                @csrf

                <div class="form-group">
                    <label class="form-label">ค้นหานักเรียน (รหัส หรือ ชื่อ-นามสกุล)</label>
                    <input type="text" id="studentSearchInput" class="form-control" placeholder="พิมพ์เพื่อค้นหา เช่น 10004 หรือ เด็กดื้อ..." style="margin-bottom:0.5rem; background:#f8fafc; border-color:#cbd5e1;">

                    <label class="form-label" style="margin-top:0.5rem;">นักเรียน <span style="color:var(--red)">*</span></label>
                    <select name="StudentID" id="studentSelect" class="form-control {{ $errors->has('StudentID') ? 'is-invalid' : '' }}">
                        <option value="">เลือกนักเรียน</option>
                        @foreach($students as $s)
                            <option value="{{ $s->StudentID }}" 
                                    data-id="{{ $s->StudentID }}"
                                    data-name="{{ $s->FullName }}"
                                    data-classroom="{{ $s->Classroom }}"
                                    {{ (old('StudentID', request('student_id')) === $s->StudentID) ? 'selected' : '' }}>
                                {{ $s->FullName }} ({{ $s->Classroom }}) — รหัส {{ $s->StudentID }}
                            </option>
                        @endforeach
                    </select>
                    @error('StudentID')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                @php
                    $selectedRule = old('RuleID') ? $rules->where('RuleID', old('RuleID'))->first() : null;
                    $selectedType = $selectedRule ? $selectedRule->RuleType : 'ตัดคะแนน';
                @endphp
                <div class="form-group">
                    <label class="form-label">ประเภทคะแนน <span style="color:var(--red)">*</span></label>
                    <div style="display:flex; gap:1.5rem; margin-top:0.35rem; margin-bottom:0.75rem;">
                        <label style="cursor:pointer; font-size:0.875rem; font-weight:600; display:flex; align-items:center; gap:0.35rem; color:var(--red);">
                            <input type="radio" name="rule_type_filter" value="ตัดคะแนน" {{ $selectedType === 'ตัดคะแนน' ? 'checked' : '' }} style="accent-color:var(--red);">
                            <i class="fas fa-minus-circle"></i> ลดคะแนน (พฤติกรรมไม่พึงประสงค์)
                        </label>
                        <label style="cursor:pointer; font-size:0.875rem; font-weight:600; display:flex; align-items:center; gap:0.35rem; color:var(--green);">
                            <input type="radio" name="rule_type_filter" value="เพิ่มคะแนน" {{ $selectedType === 'เพิ่มคะแนน' ? 'checked' : '' }} style="accent-color:var(--green);">
                            <i class="fas fa-plus-circle"></i> เพิ่มคะแนน (ความดี/สร้างชื่อเสียง)
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">กฎเกณฑ์พฤติกรรม <span style="color:var(--red)">*</span></label>
                    <select name="RuleID" class="form-control {{ $errors->has('RuleID') ? 'is-invalid' : '' }}" id="ruleSelect">
                        <option value="">เลือกกฎเกณฑ์</option>
                        @foreach($rules as $rule)
                            <option value="{{ $rule->RuleID }}"
                                    data-type="{{ $rule->RuleType }}"
                                    data-score="{{ $rule->ScoreModifier }}"
                                    {{ old('RuleID') === $rule->RuleID ? 'selected' : '' }}>
                                [{{ $rule->Category }}] {{ $rule->RuleName }}
                                ({{ $rule->RuleType === 'ตัดคะแนน' ? '-' : '+' }}{{ abs($rule->ScoreModifier) }})
                            </option>
                        @endforeach
                    </select>
                    @error('RuleID')<div class="invalid-feedback">{{ $message }}</div>@enderror

                    {{-- Preview คะแนนที่จะเปลี่ยน --}}
                    <div id="scorePreview" style="display:none; margin-top:0.5rem; padding:0.5rem 0.75rem; border-radius:2px; font-size:0.85rem;"></div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">วันที่เกิดเหตุ <span style="color:var(--red)">*</span></label>
                        <input type="datetime-local" name="RecordDate" class="form-control"
                               value="{{ old('RecordDate', now()->format('Y-m-d\TH:i')) }}">
                        @error('RecordDate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">มาตรการ/การลงโทษ</label>
                        <input type="text" name="Penalty" class="form-control"
                               value="{{ old('Penalty') }}" placeholder="เช่น ทำความสะอาด, แจ้งผู้ปกครอง">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">รายละเอียดเพิ่มเติม</label>
                    <textarea name="Description" class="form-control" rows="4"
                              placeholder="อธิบายรายละเอียดของพฤติกรรมที่พบ...">{{ old('Description', request('description')) }}</textarea>
                </div>

                <div style="display:flex; gap:0.75rem; margin-top:0.5rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> บันทึก (รออนุมัติ)
                    </button>
                    <a href="{{ route('discipline.behavior-records.index') }}" class="btn btn-outline">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const ruleSelect = document.getElementById('ruleSelect');
    const preview = document.getElementById('scorePreview');
    const radioFilters = document.getElementsByName('rule_type_filter');
    const initialRuleID = "{{ old('RuleID', request('rule_id')) }}";

    // Store all rules in an array
    const allRules = [];
    for (let i = 0; i < ruleSelect.options.length; i++) {
        const opt = ruleSelect.options[i];
        if (opt.value) {
            allRules.push({
                value: opt.value,
                text: opt.text,
                type: opt.dataset.type,
                score: opt.dataset.score
            });
        }
    }

    function filterRules(selectedVal = null) {
        // Find which radio is checked
        let selectedType = 'ตัดคะแนน';
        for (const radio of radioFilters) {
            if (radio.checked) {
                selectedType = radio.value;
                break;
            }
        }

        // Clear select options, keep the first one
        ruleSelect.innerHTML = '<option value="">เลือกกฎเกณฑ์</option>';

        // Filter and add matching options
        const filtered = allRules.filter(r => r.type === selectedType);
        filtered.forEach(r => {
            const opt = document.createElement('option');
            opt.value = r.value;
            opt.text = r.text;
            opt.dataset.type = r.type;
            opt.dataset.score = r.score;
            if (selectedVal && r.value == selectedVal) {
                opt.selected = true;
            }
            ruleSelect.add(opt);
        });

        // Trigger change event to update score preview
        updatePreview();
    }

    function updatePreview() {
        const opt = ruleSelect.options[ruleSelect.selectedIndex];
        if (!opt || !opt.value) { preview.style.display = 'none'; return; }

        const type = opt.dataset.type;
        const score = opt.dataset.score;

        if (!type) { preview.style.display = 'none'; return; }

        const isDeduct = type === 'ตัดคะแนน';
        preview.style.display = 'block';
        preview.style.background = isDeduct ? 'rgba(192,57,43,0.08)' : 'rgba(39,174,96,0.08)';
        preview.style.color = isDeduct ? 'var(--red)' : 'var(--green)';
        preview.style.borderLeft = `3px solid ${isDeduct ? 'var(--red)' : 'var(--green)'}`;
        preview.innerHTML = `<i class="fas fa-${isDeduct ? 'minus' : 'plus'}-circle"></i>
            <strong>${isDeduct ? 'ตัดคะแนน' : 'เพิ่มคะแนน'} ${Math.abs(score)} คะแนน</strong>
            ${isDeduct ? '(คะแนนจะถูกหักหลังอนุมัติ)' : '(คะแนนจะเพิ่มหลังอนุมัติ)'}`;
    }

    ruleSelect.addEventListener('change', updatePreview);

    // Add change listener to radio buttons
    for (const radio of radioFilters) {
        radio.addEventListener('change', function() {
            filterRules();
        });
    }

    // Student search filtering
    const studentSelect = document.getElementById('studentSelect');
    const studentSearchInput = document.getElementById('studentSearchInput');
    const initialStudentID = "{{ old('StudentID', request('student_id')) }}";

    // Store all students in an array
    const allStudents = [];
    for (let i = 0; i < studentSelect.options.length; i++) {
        const opt = studentSelect.options[i];
        if (opt.value) {
            allStudents.push({
                value: opt.value,
                text: opt.text,
                id: opt.dataset.id,
                name: opt.dataset.name,
                classroom: opt.dataset.classroom
            });
        }
    }

    function filterStudents() {
        const query = studentSearchInput.value.trim().toLowerCase();
        const selectedVal = studentSelect.value;

        // Clear and add placeholder
        studentSelect.innerHTML = '<option value="">เลือกนักเรียน</option>';

        // Filter students matching ID or Name
        const filtered = allStudents.filter(s => {
            return s.id.toLowerCase().includes(query) || 
                   s.name.toLowerCase().includes(query) ||
                   s.classroom.toLowerCase().includes(query);
        });

        filtered.forEach(s => {
            const opt = document.createElement('option');
            opt.value = s.value;
            opt.text = s.text;
            opt.dataset.id = s.id;
            opt.dataset.name = s.name;
            opt.dataset.classroom = s.classroom;
            
            // Retain selection if it matches current selected value
            if (s.value === selectedVal || s.value === initialStudentID) {
                opt.selected = true;
            }
            studentSelect.add(opt);
        });
    }

    studentSearchInput.addEventListener('input', filterStudents);

    // Initialize with old value if present
    filterRules(initialRuleID);
</script>
@endpush
@endsection