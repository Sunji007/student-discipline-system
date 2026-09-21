@extends('layouts.app')

@section('title', 'เพิ่มบันทึกพฤติกรรม')
@section('page-title', 'บันทึกพฤติกรรมนักเรียน')

@section('content')
<div style="max-width:680px;">
    <div class="card">
        <div class="card-header-bar">
            <h3>แบบฟอร์มบันทึกพฤติกรรม</h3>
            <a href="{{ route('teacher.behavior-records.index') }}" class="btn btn-outline btn-sm">
                <i class="fas fa-arrow-left"></i> ย้อนกลับ
            </a>
        </div>
        <div class="card-body-pad">
            <form method="POST" action="{{ route('teacher.behavior-records.store') }}" enctype="multipart/form-data" id="behaviorRecordForm">
                @csrf

                @php
                    $selectedRule = old('RuleID') ? $rules->where('RuleID', old('RuleID'))->first() : null;
                    $selectedType = $selectedRule ? $selectedRule->RuleType : 'ตัดคะแนน';
                @endphp

                <div class="form-row">
                    <div class="form-group">
                        <div style="display:flex; gap:0.5rem; margin-bottom:0.5rem; align-items:flex-end;">
                            <div style="flex:1;">
                                <label class="form-label">ห้องเรียนที่ดูแล</label>
                                @php
                                    $advisoryRooms = $rooms ?? auth()->user()->teacher?->advisory_rooms ?? [];
                                @endphp
                                @if(count($advisoryRooms) > 1)
                                    <select id="gradeFilterSelect" class="form-control" style="background:#f8fafc; border-color:#cbd5e1;">
                                        <option value="">ทุกห้องที่ดูแล</option>
                                        @foreach($advisoryRooms as $r)
                                            <option value="{{ $r }}">ห้อง {{ $r }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <div style="background:#f8fafc; border:1px solid #cbd5e1; border-radius:8px; padding:0.6rem 0.85rem; font-weight:600; color:#1e293b; font-size:0.875rem;">
                                        ห้อง {{ $advisoryRooms[0] ?? '-' }}
                                    </div>
                                    <input type="hidden" id="gradeFilterSelect" value="{{ $advisoryRooms[0] ?? '' }}">
                                @endif
                            </div>
                            <div style="flex:1.5;">
                                <label class="form-label">ค้นหานักเรียน</label>
                                <input type="text" id="studentSearchInput" class="form-control" placeholder="รหัส/ชื่อ..." style="background:#f8fafc; border-color:#cbd5e1; padding: 0.75rem 1rem; line-height: 1.5;">
                            </div>
                        </div>

                        <label class="form-label">นักเรียน <span style="color:var(--red)">*</span></label>
                        <select name="StudentID" id="studentSelect" class="form-control {{ $errors->has('StudentID') ? 'is-invalid' : '' }}" required>
                            <option value="">เลือกนักเรียน</option>
                            @foreach($students as $student)
                                @php
                                    $isPreselected = old('StudentID', $preselectedStudentId ?? request('student_id')) == $student->StudentID;
                                @endphp
                                <option value="{{ $student->StudentID }}" 
                                        data-id="{{ $student->StudentID }}"
                                        data-name="{{ $student->FullName }}"
                                        data-classroom="{{ $student->Classroom }}"
                                        data-grade="{{ $student->GradeLevel }}"
                                        {{ $isPreselected ? 'selected' : '' }}>
                                    {{ $student->StudentID }} - {{ $student->FullName }} (ห้อง {{ $student->Classroom }})
                                </option>
                            @endforeach
                        </select>
                        @error('StudentID')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label"><span id="recordDateText">{{ ($selectedType ?? 'ตัดคะแนน') === 'เพิ่มคะแนน' ? 'วันที่ทำความดี' : 'วันที่เกิดเหตุ' }}</span> <span style="color:var(--red)">*</span></label>
                        <div style="position: relative;">
                            <input type="text" id="RecordDate_display" class="form-control" style="background:#fff; cursor:pointer;" placeholder="เลือกวันที่" readonly>
                            <span style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: var(--text-muted); pointer-events: none;">
                                <i class="fas fa-calendar-alt"></i>
                            </span>
                        </div>
                        <input type="hidden" name="RecordDate" id="RecordDate_real" value="{{ old('RecordDate', date('Y-m-d')) }}">
                        @error('RecordDate')<div class="invalid-feedback" style="display:block;">{{ $message }}</div>@enderror
                    </div>
                </div>

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

                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <div style="display:flex; gap:0.75rem; margin-bottom:0.75rem; align-items:flex-end;">
                        <div style="flex:1;">
                            <label class="form-label">เลือกหมวดหมู่เกณฑ์</label>
                            <select id="categoryFilterSelect" class="form-control" style="background:#f8fafc; border-color:#cbd5e1;">
                                <option value="all">ทุกหมวดหมู่</option>
                            </select>
                        </div>
                        <div style="flex:2;">
                            <label class="form-label">ค้นหาเกณฑ์ประเมิน (ชื่อ หรือ คะแนน)</label>
                            <input type="text" id="ruleSearchInput" class="form-control" placeholder="พิมพ์เพื่อค้นหากฎเกณฑ์ เช่น มาสาย, ทรงผม, ลักขโมย, 10..." style="background:#f8fafc; border-color:#cbd5e1; padding: 0.75rem 1rem; line-height: 1.5;">
                        </div>
                    </div>

                    <label class="form-label">เกณฑ์ประเมินพฤติกรรม <span style="color:var(--red)">*</span></label>
                    <select name="RuleID" class="form-control {{ $errors->has('RuleID') ? 'is-invalid' : '' }}" id="ruleSelect" required>
                        <option value="">-- เลือกเกณฑ์ประเมินพฤติกรรม --</option>
                        @foreach($rules as $rule)
                            <option value="{{ $rule->RuleID }}" 
                                    data-type="{{ $rule->RuleType ?? ($rule->ScoreModifier > 0 ? 'เพิ่มคะแนน' : 'ตัดคะแนน') }}"
                                    data-score="{{ $rule->ScoreModifier }}"
                                    data-category="{{ $rule->Category ?? ($rule->ScoreModifier > 0 ? 'ความดีและจิตอาสา' : 'ทั่วไป') }}"
                                    {{ old('RuleID') == $rule->RuleID ? 'selected' : '' }}>
                                {{ $rule->RuleName }} ({{ ($rule->RuleType === 'ตัดคะแนน' || $rule->ScoreModifier < 0) ? '-' : '+' }}{{ abs($rule->ScoreModifier) }})
                            </option>
                        @endforeach
                    </select>
                    @error('RuleID')<div class="invalid-feedback">{{ $message }}</div>@enderror

                    {{-- Preview คะแนนที่จะเปลี่ยน --}}
                    <div id="scorePreview" style="display:none; margin-top:0.5rem; padding:0.5rem 0.75rem; border-radius:2px; font-size:0.85rem;"></div>
                </div>

                <div class="form-group" id="photoUploadGroup" style="display:block; margin-bottom:1rem;">
                    <label class="form-label"><span id="photoLabelText">{{ $selectedType === 'เพิ่มคะแนน' ? 'แนบรูปภาพกิจกรรม/การทำความดี' : 'แนบหลักฐานรูปภาพ' }}</span> <span style="color:var(--text-muted); font-weight:normal; font-size:0.75rem;">(ไม่บังคับ, แนบได้หลายรูปพร้อมกัน)</span></label>
                    
                    <label for="photoInput" id="photoDropzone" style="display:block; border:2px dashed #cbd5e1; border-radius:8px; padding:1.25rem; text-align:center; background:#f8fafc; cursor:pointer; transition:all 0.2s ease;">
                        <i class="fas fa-cloud-upload-alt" style="font-size:1.8rem; color:#64748b; margin-bottom:0.4rem;"></i>
                        <div id="photoDropzoneTitle" style="font-size:0.88rem; font-weight:600; color:#334155;">{{ $selectedType === 'เพิ่มคะแนน' ? 'คลิกเพื่อเลือกรูปภาพ หรือลากรูปภาพกิจกรรมมาวางที่นี่' : 'คลิกเพื่อเลือกรูปภาพ หรือลากรูปภาพมาวางที่นี่' }}</div>
                        <div id="photoDropzoneSubtitle" style="font-size:0.75rem; color:#94a3b8; margin-top:0.2rem;">{{ $selectedType === 'เพิ่มคะแนน' ? 'แนบรูปภาพกิจกรรม/การทำความดี (สามารถคลิกดูรูปหรือกดลบรูปที่ไม่ต้องการได้)' : 'แนบได้หลายรูปพร้อมกัน (สามารถคลิกดูรูปหรือกดลบรูปที่ไม่ต้องการได้)' }}</div>
                    </label>
                    <input type="file" name="Photo[]" id="photoInput" accept="image/*" multiple style="opacity:0; position:absolute; width:1px; height:1px; z-index:-1;">

                    <div id="photoPreviewList" style="margin-top:0.75rem; display:flex; flex-direction:column; gap:0.4rem;"></div>
                    @error('Photo')<div class="invalid-feedback" style="display:block;">{{ $message }}</div>@enderror
                    @error('Photo.*')<div class="invalid-feedback" style="display:block;">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" id="descriptionLabel">{{ $selectedType === 'เพิ่มคะแนน' ? 'รายละเอียดการทำความดี/กิจกรรม' : 'รายละเอียด/พฤติการณ์เพิ่มเติม' }}</label>
                    <textarea name="Description" id="descriptionTextarea" class="form-control" rows="3" placeholder="{{ $selectedType === 'เพิ่มคะแนน' ? 'อธิบายรายละเอียดการทำความดี หรือกิจกรรมจิตอาสา...' : 'ระบุเหตุการณ์เพิ่มเติม (ถ้ามี)' }}">{{ old('Description') }}</textarea>
                </div>

                <div class="form-group" id="penaltyGroup">
                    <label class="form-label">บทลงโทษที่ดำเนินการไปแล้ว (ถ้ามี)</label>
                    <input type="text" name="Penalty" class="form-control" value="{{ old('Penalty') }}" placeholder="เช่น ตักเตือนด้วยวาจา, ให้ทำความสะอาด">
                </div>

                <div style="display:flex; gap:0.75rem; margin-top:1.5rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> บันทึก
                    </button>
                    <a href="{{ route('teacher.behavior-records.index') }}" class="btn btn-outline">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>

@php
    $formattedRules = $rules->map(function($r) {
        $name = (string) $r->RuleName;
        $cat  = (string) ($r->Category ?? '');

        // Detect if rule is negative
        $isNegative = (
            $r->RuleType === 'ตัดคะแนน' ||
            $r->ScoreModifier < 0 ||
            str_contains($cat, 'ร้ายแรง') ||
            str_contains($cat, 'สารเสพติด') ||
            str_contains($cat, 'การแต่งกาย') ||
            str_contains($cat, 'การเข้าเรียน') ||
            str_contains($cat, 'เครื่องมือสื่อสาร') ||
            str_contains($cat, 'กริยามารยาท') ||
            str_contains($name, 'บุหรี่') ||
            str_contains($name, 'ยาเสพติด') ||
            str_contains($name, 'กัญชา') ||
            str_contains($name, 'ทะเลาะ') ||
            str_contains($name, 'ทำร้าย') ||
            str_contains($name, 'ขโมย') ||
            str_contains($name, 'มาสาย') ||
            str_contains($name, 'หนีเรียน') ||
            str_contains($name, 'ผิดระเบียบ') ||
            str_contains($name, 'ซอยผม')
        );

        if ($isNegative) {
            $type = 'ตัดคะแนน';
            $score = -abs((int) ($r->ScoreModifier ?: 10));
            $category = (str_contains($cat, 'ร้ายแรง') || empty($cat)) ? 'ความประพฤติและกริยามารยาท' : $cat;
        } else {
            $type = 'เพิ่มคะแนน';
            $score = abs((int) ($r->ScoreModifier ?: 5));
            $category = $cat ?: 'ความดีและจิตอาสา';
        }

        return [
            'value'    => (string) $r->RuleID,
            'name'     => $name,
            'text'     => $name,
            'type'     => $type,
            'score'    => $score,
            'category' => $category,
        ];
    })->values();
@endphp

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ruleSelect = document.getElementById('ruleSelect');
    const preview = document.getElementById('scorePreview');
    const radioFilters = document.getElementsByName('rule_type_filter');
    const initialRuleID = "{{ old('RuleID') }}";

    // Store all rules directly from backend database
    const allRules = {!! json_encode($formattedRules, JSON_UNESCAPED_UNICODE) !!};

    const categoryFilterSelect = document.getElementById('categoryFilterSelect');
    const ruleSearchInput = document.getElementById('ruleSearchInput');
    let selectedCategoryFilter = 'all';
    
    // Auto-detect category filter based on initial rule value if present
    if (initialRuleID) {
        const foundRule = allRules.find(r => r.value == initialRuleID);
        if (foundRule && foundRule.category) {
            selectedCategoryFilter = foundRule.category;
        }
    }

    function updateCategoryDropdown(selectedType) {
        if (!categoryFilterSelect) return;

        const isPositive = selectedType === 'เพิ่มคะแนน';
        const typeRules = allRules.filter(r => {
            return (r.type === selectedType) || (isPositive ? parseFloat(r.score) > 0 : parseFloat(r.score) < 0);
        });
        
        const uniqueCats = Array.from(new Set(typeRules.map(r => r.category).filter(Boolean)));

        categoryFilterSelect.innerHTML = '<option value="all">ทุกหมวดหมู่</option>';
        uniqueCats.forEach(cat => {
            const opt = document.createElement('option');
            opt.value = cat;
            opt.textContent = `หมวด${cat}`;
            if (cat === selectedCategoryFilter) {
                opt.selected = true;
            }
            categoryFilterSelect.appendChild(opt);
        });

        // If selectedCategoryFilter is not in current categories, reset to 'all'
        if (selectedCategoryFilter !== 'all' && !uniqueCats.includes(selectedCategoryFilter)) {
            selectedCategoryFilter = 'all';
            categoryFilterSelect.value = 'all';
        }
    }

    if (categoryFilterSelect) {
        categoryFilterSelect.addEventListener('change', function() {
            selectedCategoryFilter = this.value;
            filterRules(ruleSelect.value);
        });
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

        const isPositive = selectedType === 'เพิ่มคะแนน';

        // Toggle penalty field visibility
        const penaltyGroup = document.getElementById('penaltyGroup');
        if (penaltyGroup) {
            penaltyGroup.style.display = isPositive ? 'none' : 'block';
        }

        // Dynamically update labels and placeholders for positive vs negative behavior
        const recordDateText = document.getElementById('recordDateText');
        if (recordDateText) {
            recordDateText.textContent = isPositive ? 'วันที่ทำความดี' : 'วันที่เกิดเหตุ';
        }

        const descriptionLabel = document.getElementById('descriptionLabel');
        if (descriptionLabel) {
            descriptionLabel.textContent = isPositive ? 'รายละเอียดการทำความดี/กิจกรรม' : 'รายละเอียดเพิ่มเติม';
        }

        const descriptionTextarea = document.getElementById('descriptionTextarea');
        if (descriptionTextarea) {
            descriptionTextarea.placeholder = isPositive ? 'อธิบายรายละเอียดการทำความดี หรือกิจกรรมจิตอาสา...' : 'อธิบายรายละเอียดของพฤติกรรมที่พบ...';
        }

        const photoLabelText = document.getElementById('photoLabelText');
        if (photoLabelText) {
            photoLabelText.textContent = isPositive ? 'แนบรูปภาพกิจกรรม/การทำความดี' : 'แนบหลักฐานรูปภาพ';
        }

        const photoDropzoneTitle = document.getElementById('photoDropzoneTitle');
        if (photoDropzoneTitle) {
            photoDropzoneTitle.textContent = isPositive ? 'คลิกเพื่อเลือกรูปภาพ หรือลากรูปภาพกิจกรรมมาวางที่นี่' : 'คลิกเพื่อเลือกรูปภาพ หรือลากรูปภาพมาวางที่นี่';
        }

        const photoDropzoneSubtitle = document.getElementById('photoDropzoneSubtitle');
        if (photoDropzoneSubtitle) {
            photoDropzoneSubtitle.textContent = isPositive ? 'แนบรูปภาพกิจกรรม/การทำความดี (สามารถคลิกดูรูปหรือกดลบรูปที่ไม่ต้องการได้)' : 'แนบได้หลายรูปพร้อมกัน (สามารถคลิกดูรูปหรือกดลบรูปที่ไม่ต้องการได้)';
        }

        updateCategoryDropdown(selectedType);

        // Clear select options, keep the first one
        ruleSelect.innerHTML = '<option value="">-- เลือกพฤติกรรม/กฎเกณฑ์ --</option>';

        const query = ruleSearchInput ? ruleSearchInput.value.trim().toLowerCase() : '';

        // Filter and add matching options by type AND category AND text search query
        const filtered = allRules.filter(r => {
            const matchesType = (r.type === selectedType) || (isPositive ? (parseFloat(r.score) > 0) : (parseFloat(r.score) < 0));
            const matchesCategory = (selectedCategoryFilter === 'all') || (r.category === selectedCategoryFilter);
            const cleanText = r.text.replace(/^\[.*?\]\s*/, '');
            const matchesQuery = !query || cleanText.toLowerCase().includes(query) || (r.category && r.category.toLowerCase().includes(query)) || r.value.toLowerCase().includes(query);
            return matchesType && matchesCategory && matchesQuery;
        });

        // Group filtered rules by category
        const groups = {};
        filtered.forEach(r => {
            const cat = r.category || 'อื่นๆ';
            if (!groups[cat]) groups[cat] = [];
            groups[cat].push(r);
        });

        for (const [catName, catRules] of Object.entries(groups)) {
            const groupEl = document.createElement('optgroup');
            groupEl.label = `📂 หมวด${catName}`;

            catRules.forEach(r => {
                const opt = document.createElement('option');
                opt.value = r.value;
                const cleanName = r.text.replace(/^\[.*?\]\s*/, '').replace(/\s*\([+-]?\d+\)$/, '').trim();
                opt.text = `${cleanName} (${r.type === 'ตัดคะแนน' ? '-' : '+'}${Math.abs(r.score)})`;
                opt.dataset.type = r.type;
                opt.dataset.score = r.score;
                opt.dataset.category = r.category;
                if (selectedVal && r.value == selectedVal) {
                    opt.selected = true;
                }
                groupEl.appendChild(opt);
            });

            ruleSelect.appendChild(groupEl);
        }

        // Trigger change event to update score preview
        updatePreview();
    }

    if (ruleSearchInput) {
        ruleSearchInput.addEventListener('input', function() {
            filterRules(ruleSelect.value);
        });

        // Intercept enter key to prevent submission and select the first matched rule
        ruleSearchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault(); // Prevent form submission
                
                let selectedType = 'ตัดคะแนน';
                for (const radio of radioFilters) {
                    if (radio.checked) {
                        selectedType = radio.value;
                        break;
                    }
                }
                const query = ruleSearchInput.value.trim().toLowerCase();
                const filtered = allRules.filter(r => {
                    const matchesType = r.type === selectedType;
                    const matchesQuery = !query || r.text.toLowerCase().includes(query) || r.value.toLowerCase().includes(query);
                    return matchesType && matchesQuery;
                });
                
                if (filtered.length > 0) {
                    ruleSelect.value = filtered[0].value;
                    filterRules(filtered[0].value);
                    updatePreview();
                }
            }
        });
    }

    function updatePreview() {
        const val = ruleSelect.value;
        if (!val) { preview.style.display = 'none'; return; }

        const r = allRules.find(item => String(item.value) === String(val));
        if (!r) { preview.style.display = 'none'; return; }

        const isDeduct = r.type === 'ตัดคะแนน';
        preview.style.display = 'block';
        preview.style.background = isDeduct ? 'rgba(192,57,43,0.08)' : 'rgba(39,174,96,0.08)';
        preview.style.color = isDeduct ? 'var(--red)' : 'var(--green)';
        preview.style.borderLeft = `3px solid ${isDeduct ? 'var(--red)' : 'var(--green)'}`;
        preview.innerHTML = `<i class="fas fa-${isDeduct ? 'minus' : 'plus'}-circle"></i>
            <strong>${isDeduct ? 'ตัดคะแนน' : 'เพิ่มคะแนน'} ${Math.abs(r.score)} คะแนน</strong>
            ${isDeduct ? '(คะแนนจะถูกหักหลังอนุมัติ)' : '(คะแนนจะเพิ่มหลังอนุมัติ)'}`;
    }

    ruleSelect.addEventListener('change', updatePreview);

    // Add change listener to radio buttons
    for (const radio of radioFilters) {
        radio.addEventListener('change', function() {
            selectedCategoryFilter = 'all';
            if (ruleSearchInput) ruleSearchInput.value = '';
            filterRules();
        });
    }

    // Student search filtering
    const studentSelect = document.getElementById('studentSelect');
    const studentSearchInput = document.getElementById('studentSearchInput');
    const gradeFilterSelect = document.getElementById('gradeFilterSelect');
    const initialStudentID = "{{ old('StudentID') }}";

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
                classroom: opt.dataset.classroom,
                grade: opt.dataset.grade
            });
        }
    }

    function filterStudents() {
        const query = studentSearchInput ? studentSearchInput.value.trim().toLowerCase() : '';
        const selectedGrade = gradeFilterSelect ? gradeFilterSelect.value : '';
        const selectedVal = studentSelect.value;

        // Clear and add placeholder
        studentSelect.innerHTML = '<option value="">เลือกนักเรียน</option>';

        // Filter students matching ID, Name or Class, and Room/Grade
        const filtered = allStudents.filter(s => {
            const matchesQuery = !query || 
                                 s.id.toLowerCase().includes(query) || 
                                 s.name.toLowerCase().includes(query) ||
                                 s.classroom.toLowerCase().includes(query);
            const matchesGrade = !selectedGrade || 
                                 s.grade === selectedGrade || 
                                 s.classroom === selectedGrade ||
                                 s.classroom.includes(selectedGrade);
            return matchesQuery && matchesGrade;
        });

        filtered.forEach(s => {
            const opt = document.createElement('option');
            opt.value = s.value;
            opt.text = s.text;
            opt.dataset.id = s.id;
            opt.dataset.name = s.name;
            opt.dataset.classroom = s.classroom;
            opt.dataset.grade = s.grade;
            
            // Retain selection if it matches current selected value
            if (s.value === selectedVal || s.value === initialStudentID) {
                opt.selected = true;
            }
            studentSelect.add(opt);
        });
    }

    if (studentSearchInput) studentSearchInput.addEventListener('input', filterStudents);
    if (gradeFilterSelect && gradeFilterSelect.tagName === 'SELECT') gradeFilterSelect.addEventListener('change', filterStudents);

    // Prevent enter key from submitting the form, instead select the first matched student
    studentSearchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault(); // Prevent form submission
            
            const query = studentSearchInput.value.trim().toLowerCase();
            const isNumeric = /^\d+$/.test(query);
            
            let filtered = [];
            if (isNumeric) {
                // Exact match by student ID
                filtered = allStudents.filter(s => {
                    return s.id === query;
                });
            } else {
                // Partial match by name or classroom
                const selectedGrade = gradeFilterSelect.value;
                filtered = allStudents.filter(s => {
                    const matchesQuery = s.name.toLowerCase().includes(query) ||
                                         s.classroom.toLowerCase().includes(query);
                    const matchesGrade = !selectedGrade || s.grade === selectedGrade;
                    return matchesQuery && matchesGrade;
                });
            }
            
            if (filtered.length > 0) {
                // Re-render select options with filtered list and select the first one
                studentSelect.innerHTML = '<option value="">เลือกนักเรียน</option>';
                filtered.forEach((s, idx) => {
                    const opt = document.createElement('option');
                    opt.value = s.value;
                    opt.text = s.text;
                    opt.dataset.id = s.id;
                    opt.dataset.name = s.name;
                    opt.dataset.classroom = s.classroom;
                    opt.dataset.grade = s.grade;
                    
                    if (idx === 0) {
                        opt.selected = true;
                    }
                    studentSelect.add(opt);
                });
                
                // Focus on the next input field (ruleSelect)
                if (typeof ruleSelect !== 'undefined') {
                    ruleSelect.focus();
                }
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'ไม่พบข้อมูลนักเรียน',
                        text: 'ไม่พบรหัสประจำตัว หรือรายชื่อนักเรียนที่ตรงกับข้อมูลที่คุณพิมพ์ กรุณาตรวจสอบอีกครั้ง',
                        confirmButtonText: 'ตกลง',
                        buttonsStyling: false,
                        customClass: {
                            confirmButton: 'btn btn-primary btn-swal-confirm'
                        }
                    });
                } else {
                    alert('ไม่พบข้อมูลนักเรียน');
                }
            }
        }
    });

    // Initialize with old value if present
    filterRules(initialRuleID);

    // Initialize Buddhist Era Flatpickr Date & Timepicker
    if (typeof window.initBETimepicker === 'function') {
        window.initBETimepicker('#RecordDate', "{{ old('RecordDate', now()->format('Y-m-d H:i')) }}");
    } else if (typeof window.initBEDatepicker === 'function') {
        window.initBEDatepicker('#RecordDate', "{{ old('RecordDate', date('Y-m-d')) }}");
    }

    // Form submission confirmation popup
    const recordForm = document.getElementById('behaviorRecordForm');
    if (recordForm) {
        recordForm.addEventListener('submit', function(e) {
            if (this.dataset.confirmed === 'true') {
                return true;
            }
            e.preventDefault();

            const studentSelect = document.getElementById('studentSelect');
            const ruleSelect = document.getElementById('ruleSelect');

            if (!studentSelect || !studentSelect.value) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        iconHtml: '<i class="fas fa-user-graduate" style="color:var(--navy); font-size:2.8rem;"></i>',
                        title: 'กรุณาเลือกนักเรียน',
                        text: 'โปรดเลือกนักเรียนที่ต้องการบันทึกพฤติกรรม',
                        confirmButtonText: 'ตกลง',
                        customClass: { confirmButton: 'swal2-confirm btn-swal-success' },
                        buttonsStyling: false
                    });
                } else {
                    alert('กรุณาเลือกนักเรียน');
                }
                return false;
            }

            if (!ruleSelect || !ruleSelect.value) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        iconHtml: '<i class="fas fa-gavel" style="color:var(--navy); font-size:2.8rem;"></i>',
                        title: 'กรุณาเลือกกฎเกณฑ์พฤติกรรม',
                        text: 'โปรดเลือกกฎเกณฑ์พฤติกรรมก่อนทำการบันทึก',
                        confirmButtonText: 'ตกลง',
                        customClass: { confirmButton: 'swal2-confirm btn-swal-success' },
                        buttonsStyling: false
                    });
                } else {
                    alert('กรุณาเลือกกฎเกณฑ์พฤติกรรม');
                }
                return false;
            }

            const selectedStudentOption = studentSelect.options[studentSelect.selectedIndex];
            const studentName = selectedStudentOption ? (selectedStudentOption.getAttribute('data-name') || selectedStudentOption.text) : '';
            const form = this;

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'ยืนยันการบันทึกพฤติกรรม?',
                    html: `คุณต้องการบันทึกข้อมูลพฤติกรรมของ<br><strong style="color:var(--navy); font-size:1.05rem;">${studentName}</strong><br>ใช่หรือไม่?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'ตกลง',
                    cancelButtonText: 'ยกเลิก',
                    customClass: {
                        confirmButton: 'swal2-confirm btn-swal-success',
                        cancelButton: 'swal2-cancel btn-swal-cancel'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.dataset.confirmed = 'true';
                        form.submit();
                    }
                });
            } else {
                if (confirm(`ยืนยันการบันทึกข้อมูลพฤติกรรมของ ${studentName}?`)) {
                    form.dataset.confirmed = 'true';
                    form.submit();
                }
            }
        });
    }

    // ── Multi-Photo Drag & Drop and Individual Removal ───────────
    const photoInput = document.getElementById('photoInput');
    const photoPreviewList = document.getElementById('photoPreviewList');
    const photoDropzone = document.getElementById('photoDropzone');

    if (photoInput && photoPreviewList) {
        let dtPhoto = new DataTransfer();

        photoInput.addEventListener('change', function() {
            const files = Array.from(this.files);
            files.forEach(file => {
                let exists = false;
                for (let i = 0; i < dtPhoto.files.length; i++) {
                    if (dtPhoto.files[i].name === file.name && dtPhoto.files[i].size === file.size) {
                        exists = true;
                        break;
                    }
                }
                if (!exists) {
                    dtPhoto.items.add(file);
                }
            });

            try {
                photoInput.files = dtPhoto.files;
            } catch (err) {}

            renderPhotoPreview();
        });

        if (photoDropzone) {
            ['dragenter', 'dragover'].forEach(eventName => {
                photoDropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    photoDropzone.style.borderColor = 'var(--navy,#1e3a8a)';
                    photoDropzone.style.background = '#eff6ff';
                }, false);
            });
            ['dragleave', 'drop'].forEach(eventName => {
                photoDropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    photoDropzone.style.borderColor = '#cbd5e1';
                    photoDropzone.style.background = '#f8fafc';
                }, false);
            });
            photoDropzone.addEventListener('drop', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const droppedFiles = Array.from(e.dataTransfer.files);
                droppedFiles.forEach(file => {
                    let exists = false;
                    for (let i = 0; i < dtPhoto.files.length; i++) {
                        if (dtPhoto.files[i].name === file.name && dtPhoto.files[i].size === file.size) {
                            exists = true;
                            break;
                        }
                    }
                    if (!exists) {
                        dtPhoto.items.add(file);
                    }
                });
                try {
                    photoInput.files = dtPhoto.files;
                } catch (err) {}
                renderPhotoPreview();
            });
        }

        function renderPhotoPreview() {
            photoPreviewList.innerHTML = '';
            if (dtPhoto.files.length === 0) return;

            Array.from(dtPhoto.files).forEach((file, index) => {
                const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
                const fileCard = document.createElement('div');
                fileCard.style.cssText = 'display:flex; align-items:center; justify-content:space-between; background:#fff; border:1px solid #e2e8f0; border-radius:6px; padding:0.5rem 0.75rem; font-size:0.825rem; box-shadow:0 1px 3px rgba(0,0,0,0.05);';
                
                let iconClass = 'fa-file-image';
                if (!file.type.startsWith('image/')) {
                    if (file.type.includes('pdf')) iconClass = 'fa-file-pdf';
                    else if (file.type.includes('word') || file.type.includes('document')) iconClass = 'fa-file-word';
                    else iconClass = 'fa-file-alt';
                }

                const fileUrl = URL.createObjectURL(file);

                fileCard.innerHTML = `
                    <a href="${fileUrl}" target="_blank" title="คลิกเพื่อเปิดดูรูปตัวอย่าง" style="display:flex; align-items:center; gap:0.5rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:85%; text-decoration:none; color:inherit; cursor:pointer;" onmouseover="this.querySelector('.file-name-text').style.textDecoration='underline'; this.querySelector('.file-name-text').style.color='var(--navy,#1e3a8a)';" onmouseout="this.querySelector('.file-name-text').style.textDecoration='none'; this.querySelector('.file-name-text').style.color='#1e293b';">
                        <i class="fas ${iconClass}" style="color:var(--navy,#1e3a8a); font-size:1rem; flex-shrink:0;"></i>
                        <span class="file-name-text" style="font-weight:600; color:#1e293b; overflow:hidden; text-overflow:ellipsis; transition:color 0.15s ease;">${file.name}</span>
                        <span style="font-size:0.72rem; color:#94a3b8; flex-shrink:0;">(${sizeMB} MB)</span>
                    </a>
                    <button type="button" class="btn-remove-photo-file" data-index="${index}" style="background:#fef2f2; border:1px solid #fca5a5; color:#ef4444; font-size:0.78rem; cursor:pointer; padding:0.25rem 0.5rem; border-radius:4px; transition:all 0.2s; font-weight:600; display:flex; align-items:center; gap:0.25rem;" title="ลบรูปนี้">
                        <i class="fas fa-times"></i> ลบ
                    </button>
                `;

                photoPreviewList.appendChild(fileCard);
            });

            photoPreviewList.querySelectorAll('.btn-remove-photo-file').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const idx = parseInt(this.getAttribute('data-index'));
                    removePhotoAtIndex(idx);
                });
            });
        }

        function removePhotoAtIndex(index) {
            const newDt = new DataTransfer();
            Array.from(dtPhoto.files).forEach((file, i) => {
                if (i !== index) {
                    newDt.items.add(file);
                }
            });
            dtPhoto = newDt;
            try {
                photoInput.files = dtPhoto.files;
            } catch (err) {}
            renderPhotoPreview();
        }
    }
});
</script>
@endpush
@endsection
