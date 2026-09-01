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
        @if($errors->any())
        <div style="background:#fef2f2; border:1px solid #fca5a5; border-radius:10px; padding:1rem 1.25rem; margin-bottom:1.5rem; color:#991b1b;">
            <div style="font-weight:700; font-size:0.95rem; margin-bottom:0.4rem; display:flex; align-items:center; gap:0.5rem;">
                <i class="fas fa-exclamation-circle" style="color:#ef4444; font-size:1.1rem;"></i> ไม่สามารถส่งข้อมูลได้เนื่องจากพบข้อผิดพลาด:
            </div>
            <ul style="margin:0; padding-left:1.25rem; font-size:0.85rem; line-height:1.5;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('student.informant-reports.store') }}" enctype="multipart/form-data" id="informantReportForm">
            @csrf

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="Category">ประเภทพฤติกรรม <span style="color:var(--red);">*</span></label>
                    <select name="Category" id="Category" class="form-control @error('Category') is-invalid @enderror" required>
                        <option value="">-- เลือกประเภทพฤติกรรม --</option>
                        <option value="การแต่งกายและทรงผม" {{ old('Category') == 'การแต่งกายและทรงผม' ? 'selected' : '' }}>👔 การแต่งกายและทรงผม (แต่งกายผิดระเบียบ, ทรงผม, เครื่องประดับ)</option>
                        <option value="ความประพฤติและกริยามารยาท" {{ old('Category') == 'ความประพฤติและกริยามารยาท' ? 'selected' : '' }}>🤝 ความประพฤติและกริยามารยาท (ก้าวร้าว, ทะเลาะวิวาท, ทำร้ายร่างกาย, อนาจาร)</option>
                        <option value="สารเสพติดและของต้องห้าม" {{ old('Category') == 'สารเสพติดและของต้องห้าม' ? 'selected' : '' }}>🚫 สารเสพติดและของต้องห้าม (บุหรี่/บุหรี่ไฟฟ้า, ยาเสพติด, พกพาสิ่งของผิดกฎหมาย, การพนัน)</option>
                        <option value="การใช้เครื่องมือสื่อสาร" {{ old('Category') == 'การใช้เครื่องมือสื่อสาร' ? 'selected' : '' }}>📱 การใช้เครื่องมือสื่อสาร (ใช้โทรศัพท์ในเวลาเรียน, สื่อออนไลน์/โพสต์ข้อมูลเท็จ)</option>
                        <option value="การเข้าเรียนและระเบียบสถานศึกษา" {{ old('Category') == 'การเข้าเรียนและระเบียบสถานศึกษา' ? 'selected' : '' }}>🏫 การเข้าเรียนและระเบียบสถานศึกษา (มาสาย, หนีเรียน, ปีน/ลอดรั้ว, ขีดเขียนฝาผนัง, ลักขโมย)</option>
                        <option value="อื่นๆ" {{ old('Category') == 'อื่นๆ' ? 'selected' : '' }}>❓ อื่นๆ (เรื่องพฤติกรรมอื่นๆ)</option>
                    </select>
                    @error('Category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="StudentID">รหัสนักเรียนที่เกี่ยวข้อง <span style="font-weight:400; color:var(--text-muted); font-size:0.8rem;">(ถ้ามี)</span></label>
                    <input type="text" 
                           name="StudentID" 
                           id="StudentID" 
                           class="form-control @error('StudentID') is-invalid @enderror" 
                           placeholder="กรอกรหัสนักเรียน เช่น 6950201 (หากไม่ทราบสามารถเว้นว่างได้)" 
                           value="{{ old('StudentID') }}">
                    <small style="color:var(--text-muted); display:block; margin-top:0.35rem; font-size:0.78rem;">
                        <i class="fas fa-info-circle" style="color:var(--gold);"></i> หากมีหลายคน สามารถกรอกรหัสนักเรียนคั่นด้วยเครื่องหมายจุลภาค (,) เช่น <code>6950201, 6940201</code> (หรือเว้นว่างได้)
                    </small>
                    <div id="student-id-feedback" style="margin-top:0.4rem;"></div>
                    @error('StudentID')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="Title">หัวข้อเบาะแส <span style="color:var(--red);">*</span></label>
                <input type="text" name="Title" id="Title" class="form-control @error('Title') is-invalid @enderror" value="{{ old('Title') }}" placeholder="กรุณาเลือกประเภทพฤติกรรมเพื่อดูหัวข้อแนะนำ หรือพิมพ์หัวข้อเอง..." required>
                
                {{-- Dynamic Title Suggestion Chips --}}
                <div id="titleSuggestionsContainer" style="margin-top:0.45rem; display:none; flex-wrap:wrap; gap:0.35rem; align-items:center;">
                    <span style="font-size:0.78rem; font-weight:600; color:var(--text-muted); margin-right:0.25rem;">
                        <i class="fas fa-lightbulb" style="color:var(--gold);"></i> คลิกหัวข้อแนะนำ:
                    </span>
                    <div id="suggestionChips" style="display:inline-flex; flex-wrap:wrap; gap:0.35rem;"></div>
                </div>
                @error('Title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="Description">รายละเอียดเบาะแส <span style="color:var(--red);">*</span></label>
                <textarea name="Description" id="Description" rows="4" class="form-control @error('Description') is-invalid @enderror" placeholder="ระบุเหตุการณ์ วันเวลา สถานที่ หรือข้อความรายละเอียดเบาะแส..." required style="resize:vertical;">{{ old('Description') }}</textarea>
                @error('Description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="evidence">แนบหลักฐาน <span style="font-weight:400; color:var(--text-muted); font-size:0.8rem;">(ถ้ามี)</span> (รองรับรูปภาพ หรือ PDF - แนบได้หลายไฟล์พร้อมกัน)</label>
                <input type="file" name="evidence[]" id="evidence" class="form-control @error('evidence') is-invalid @enderror @error('evidence.*') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf,.webp,.heic,.heif,.gif,.bmp" multiple>
                <small style="color:var(--text-muted); display:block; margin-top:0.35rem; font-size:0.78rem;">
                    <i class="fas fa-info-circle" style="color:var(--gold);"></i> สามารถเลือกและแนบไฟล์หลักฐานได้มากกว่า 1 ไฟล์พร้อมกัน (ขนาดไฟล์ละไม่เกิน 20MB หรือสามารถเว้นว่างได้)
                </small>
                <div id="file-size-feedback" style="margin-top:0.4rem;"></div>
                @error('evidence')<div class="invalid-feedback">{{ $message }}</div>@enderror
                @error('evidence.*')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Anonymity Selection Cards --}}
            <div class="form-group" style="margin-top:1.5rem; margin-bottom:1.25rem;">
                <label class="form-label" style="font-weight:700; color:var(--navy); margin-bottom:0.6rem; display:flex; align-items:center; gap:0.4rem;">
                    <i class="fas fa-user-shield" style="color:var(--gold);"></i> ความประสงค์ในการเปิดเผยตัวตน <span style="color:var(--red)">*</span>
                </label>
                
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:0.85rem;">
                    <!-- Option 1: ปกปิดตัวตน (Default) -->
                    <label id="card-anonymous" class="anon-card active-anon" onclick="selectAnonymity(1)">
                        <input type="radio" name="IsAnonymous" id="radio-anonymous" value="1" {{ old('IsAnonymous', '1') == '1' ? 'checked' : '' }} style="position:absolute; opacity:0; pointer-events:none;">
                        <div style="display:flex; align-items:flex-start; gap:0.85rem;">
                            <div class="anon-icon-box" id="icon-box-anon" style="width:42px; height:42px; border-radius:10px; background:#dcfce7; color:#16a34a; display:flex; align-items:center; justify-content:center; font-size:1.25rem; flex-shrink:0; transition:all 0.25s ease;">
                                <i class="fas fa-user-secret"></i>
                            </div>
                            <div style="flex:1; min-width:0;">
                                <div style="display:flex; align-items:center; justify-content:space-between; gap:0.4rem;">
                                    <span style="font-weight:700; color:var(--navy); font-size:0.95rem;">ปกปิดตัวตน</span>
                                    <span class="badge badge-green" style="font-size:0.68rem; padding:0.15rem 0.5rem; border-radius:10px;">แนะนำ</span>
                                </div>
                                <div style="font-size:0.8rem; color:var(--text-muted); margin-top:0.25rem; line-height:1.4;">
                                    ระบบจะซ่อนชื่อและรหัสนักเรียนของคุณ เพื่อความปลอดภัยสูงสุด
                                </div>
                            </div>
                            <div class="anon-check" id="check-anon" style="color:#16a34a; font-size:1.2rem; flex-shrink:0;">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                    </label>

                    <!-- Option 2: เปิดเผยตัวตน -->
                    <label id="card-public" class="anon-card" onclick="selectAnonymity(0)">
                        <input type="radio" name="IsAnonymous" id="radio-public" value="0" {{ old('IsAnonymous') === '0' ? 'checked' : '' }} style="position:absolute; opacity:0; pointer-events:none;">
                        <div style="display:flex; align-items:flex-start; gap:0.85rem;">
                            <div class="anon-icon-box" id="icon-box-public" style="width:42px; height:42px; border-radius:10px; background:#f1f5f9; color:#64748b; display:flex; align-items:center; justify-content:center; font-size:1.25rem; flex-shrink:0; transition:all 0.25s ease;">
                                <i class="fas fa-user"></i>
                            </div>
                            <div style="flex:1; min-width:0;">
                                <div style="display:flex; align-items:center; justify-content:space-between; gap:0.4rem;">
                                    <span style="font-weight:700; color:var(--navy); font-size:0.95rem;">เปิดเผยตัวตน</span>
                                </div>
                                <div style="font-size:0.8rem; color:var(--text-muted); margin-top:0.25rem; line-height:1.4;">
                                    แสดงชื่อของคุณ (<strong style="color:var(--navy);">{{ auth()->user()->FullName }}</strong>) ต่อฝ่ายปกครอง
                                </div>
                            </div>
                            <div class="anon-check" id="check-public" style="color:#cbd5e1; font-size:1.2rem; flex-shrink:0;">
                                <i class="far fa-circle"></i>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:0.85rem 1rem; margin:1rem 0 0.75rem 0; font-size:0.85rem; color:#166534; display:flex; align-items:center; gap:0.65rem;">
                <i class="fas fa-shield-alt" style="font-size:1.3rem; color:#22c55e; flex-shrink:0;"></i>
                <div>
                    <strong>ระบบคุ้มครองผู้แจ้งเบาะแส 100%:</strong> ข้อมูลการแจ้งเบาะแสของคุณจะถูกเก็บเป็นความลับสูงสุดตามที่คุณเลือก เพื่อความปลอดภัยและความสบายใจของคุณ
                </div>
            </div>

            <div style="background:#fffbe0; border:1px solid #fef08a; border-radius:8px; padding:0.85rem 1rem; margin-bottom:1.5rem; font-size:0.82rem; color:#854d0e; display:flex; align-items:flex-start; gap:0.65rem;">
                <i class="fas fa-exclamation-triangle" style="font-size:1.2rem; color:#eab308; flex-shrink:0; margin-top:0.1rem;"></i>
                <div>
                    <strong style="color:#713f12;">คำเตือนเกี่ยวกับการแจ้งข้อมูล:</strong> แม้ระบบจะปกปิดตัวตนต่อสาธารณะ แต่ระบบมีการบันทึกประวัติการเข้าใช้งานไว้ หากพบการแจ้งข้อมูลเท็จหรือแจ้งกลั่นแกล้งผู้อื่น จะถือเป็นความผิดทางวินัยร้ายแรง และฝ่ายปกครองสามารถตรวจสอบตัวตนผู้แจ้งเพื่อดำเนินการลงโทษได้
                </div>
            </div>

            <div style="text-align:right; border-top:1px solid #ede8e0; padding-top:1.25rem; margin-top:1rem;">
                <button type="button" class="btn btn-gold" id="btnSubmitForm" onclick="confirmAndSubmitForm()">
                    <i class="fas fa-paper-plane"></i> ส่ง
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

<style>
.anon-card {
    position: relative;
    border: 2px solid #e2e8f0;
    background: #ffffff;
    border-radius: 12px;
    padding: 1rem 1.15rem;
    cursor: pointer;
    transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    user-select: none;
}

.anon-card:hover {
    border-color: #cbd5e1;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}

.anon-card.active-anon {
    border-color: #10b981 !important;
    background: #f0fdf4 !important;
    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.15) !important;
}

.anon-card.active-public {
    border-color: #3b82f6 !important;
    background: #eff6ff !important;
    box-shadow: 0 4px 14px rgba(59, 130, 246, 0.15) !important;
}

.suggestion-chip {
    padding: 0.35rem 0.75rem;
    font-size: 0.78rem;
    font-weight: 500;
    border-radius: 20px;
    border: 1px solid #cbd5e1;
    background-color: #f1f5f9;
    color: #334155;
    cursor: pointer;
    transition: all 0.2s ease-in-out;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    line-height: 1.3;
}

.suggestion-chip:hover {
    background-color: #0f172a !important;
    color: #ffffff !important;
    border-color: #0f172a !important;
    transform: translateY(-1px);
    box-shadow: 0 2px 6px rgba(15, 23, 42, 0.25);
}

.suggestion-chip:active {
    transform: translateY(0);
}
</style>

@push('scripts')
<script>
function selectAnonymity(val) {
    const radioAnon = document.getElementById('radio-anonymous');
    const radioPublic = document.getElementById('radio-public');
    const cardAnon = document.getElementById('card-anonymous');
    const cardPublic = document.getElementById('card-public');
    const iconBoxAnon = document.getElementById('icon-box-anon');
    const iconBoxPublic = document.getElementById('icon-box-public');
    const checkAnon = document.getElementById('check-anon');
    const checkPublic = document.getElementById('check-public');

    if (!cardAnon || !cardPublic) return;

    if (val == 1) {
        if (radioAnon) radioAnon.checked = true;
        if (radioPublic) radioPublic.checked = false;

        cardAnon.className = 'anon-card active-anon';
        cardPublic.className = 'anon-card';

        if (iconBoxAnon) {
            iconBoxAnon.style.background = '#dcfce7';
            iconBoxAnon.style.color = '#16a34a';
        }
        if (checkAnon) {
            checkAnon.innerHTML = '<i class="fas fa-check-circle"></i>';
            checkAnon.style.color = '#16a34a';
        }

        if (iconBoxPublic) {
            iconBoxPublic.style.background = '#f1f5f9';
            iconBoxPublic.style.color = '#64748b';
        }
        if (checkPublic) {
            checkPublic.innerHTML = '<i class="far fa-circle"></i>';
            checkPublic.style.color = '#cbd5e1';
        }
    } else {
        if (radioAnon) radioAnon.checked = false;
        if (radioPublic) radioPublic.checked = true;

        cardAnon.className = 'anon-card';
        cardPublic.className = 'anon-card active-public';

        if (iconBoxAnon) {
            iconBoxAnon.style.background = '#f1f5f9';
            iconBoxAnon.style.color = '#64748b';
        }
        if (checkAnon) {
            checkAnon.innerHTML = '<i class="far fa-circle"></i>';
            checkAnon.style.color = '#cbd5e1';
        }

        if (iconBoxPublic) {
            iconBoxPublic.style.background = '#dbeafe';
            iconBoxPublic.style.color = '#2563eb';
        }
        if (checkPublic) {
            checkPublic.innerHTML = '<i class="fas fa-check-circle"></i>';
            checkPublic.style.color = '#2563eb';
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const initialAnon = "{{ old('IsAnonymous', '1') }}" == '1' ? 1 : 0;
    selectAnonymity(initialAnon);

    const studentList = @json($students->map(function($s) {
        return [
            'id' => (string)$s->StudentID,
            'name' => $s->FullName,
            'class' => $s->classroom_display
        ];
    }));

    const studentMap = {};
    studentList.forEach(s => {
        studentMap[s.id] = s;
    });

    const input = document.getElementById('StudentID');
    const feedback = document.getElementById('student-id-feedback');

    // Title Suggestion Handling
    const categorySelect = document.getElementById('Category');
    const titleInput = document.getElementById('Title');
    const suggestionsContainer = document.getElementById('titleSuggestionsContainer');
    const suggestionChips = document.getElementById('suggestionChips');

    const topicSuggestions = {
        'การแต่งกายและทรงผม': [
            'แต่งกายผิดระเบียบโรงเรียน',
            'ทรงผมยาวเกินกำหนด / ซอยผม',
            'ไม่แขวนบัตรนักเรียน / ไม่ติดเข็ม',
            'เอาชายเสื้อออกนอกกางเกง/กระโปรง',
            'ใช้เครื่องประดับตกแต่งร่างกายไม่เหมาะสม'
        ],
        'ความประพฤติและกริยามารยาท': [
            'แอบก่อเหตุทะเลาะวิวาท',
            'แสดงกริยาก้าวร้าว / พูดจาหยาบคาย',
            'หยอกล้อรุนแรงจนเกิดบาดแผล',
            'ทำร้ายร่างกายเพื่อนนักเรียน',
            'พฤติกรรมลามกอนาจาร / ชู้สาวในโรงเรียน',
            'แอบอ้างชื่อผู้อื่น / ปลอมแปลงเอกสาร'
        ],
        'สารเสพติดและของต้องห้าม': [
            'แอบสูบบุหรี่ / บุหรี่ไฟฟ้าหลังห้องน้ำ',
            'พกพายาเสพติด / สิ่งเสพติดเข้ามาในโรงเรียน',
            'พกพาสิ่งของผิดกฎหมาย / ของมีคม',
            'แอบเล่นการพนันในโรงเรียน',
            'จุดประทัด / ดอกไม้ไฟภายในโรงเรียน'
        ],
        'การใช้เครื่องมือสื่อสาร': [
            'แอบใช้โทรศัพท์มือถือในเวลาเรียนโดยไม่ได้รับอนุญาต',
            'นำข้อมูลข่าวสารอันเป็นเท็จเข้าสู่สื่อออนไลน์',
            'แอบถ่ายคลิปวีดิโอ/สื่อสารไม่เหมาะสมในโรงเรียน'
        ],
        'การเข้าเรียนและระเบียบสถานศึกษา': [
            'แอบหนีเรียน / ไม่เข้าชั้นเรียน',
            'แอบปีนรั้ว / ลอดรั้วออกนอกโรงเรียน',
            'มาโรงเรียนสาย / หลีกเลี่ยงกิจกรรมหน้าเสาธง',
            'ลักขโมยทรัพย์สินของเพื่อน/โรงเรียน',
            'ขีดเขียนฝาผนัง / ทำลายทรัพย์สินโรงเรียน',
            'นำอาหาร/ขนมขึ้นไปทานบนอาคารเรียน'
        ],
        'อื่นๆ': [
            'พบเห็นพฤติกรรมไม่เหมาะสมอื่นๆ',
            'เหตุการณ์ผิดระเบียบในโรงเรียน'
        ]
    };

    function updateTitleSuggestions() {
        if (!categorySelect || !suggestionsContainer || !suggestionChips) return;
        const selectedCat = categorySelect.value;
        const list = topicSuggestions[selectedCat] || [];

        if (list.length === 0) {
            suggestionsContainer.style.display = 'none';
            return;
        }

        suggestionChips.innerHTML = '';
        list.forEach(item => {
            const chip = document.createElement('button');
            chip.type = 'button';
            chip.className = 'suggestion-chip';
            chip.innerHTML = `<i class="fas fa-plus-circle" style="font-size:0.7rem; opacity:0.6;"></i> ${item}`;

            chip.addEventListener('click', function() {
                if (titleInput) {
                    titleInput.value = item;
                    titleInput.focus();
                }
            });

            suggestionChips.appendChild(chip);
        });

        suggestionsContainer.style.display = 'flex';
    }

    if (categorySelect) {
        categorySelect.addEventListener('change', updateTitleSuggestions);
        updateTitleSuggestions();
    }

    if (input && feedback) {
        function checkStudentIds() {
            const val = input.value.trim();
            if (!val) {
                feedback.innerHTML = '';
                return;
            }
            const ids = val.split(/[\s,;]+/).map(i => i.trim()).filter(i => i.length > 0);
            if (ids.length === 0) {
                feedback.innerHTML = '';
                return;
            }

            const myStudentId = "{{ auth()->user()->student?->StudentID }}";
            let html = '';
            let hasError = false;
            ids.forEach(id => {
                if (myStudentId && id === myStudentId) {
                    hasError = true;
                    html += `<div style="display:inline-flex; align-items:center; gap:0.3rem; background:rgba(239,68,68,0.1); color:#dc2626; border:1px solid rgba(239,68,68,0.25); padding:0.25rem 0.65rem; border-radius:20px; font-size:0.8rem; margin-right:0.35rem; margin-bottom:0.35rem; font-weight:600;">
                        <i class="fas fa-exclamation-triangle" style="color:#ef4444;"></i> ไม่สามารถระบุรหัสนักเรียนของตนเองได้ (${id})
                    </div>`;
                } else if (studentMap[id]) {
                    const st = studentMap[id];
                    html += `<div style="display:inline-flex; align-items:center; gap:0.3rem; background:rgba(16,185,129,0.1); color:#047857; border:1px solid rgba(16,185,129,0.25); padding:0.25rem 0.65rem; border-radius:20px; font-size:0.8rem; margin-right:0.35rem; margin-bottom:0.35rem; font-weight:600;">
                        <i class="fas fa-check-circle" style="color:#10b981;"></i> พบข้อมูล: ${st.id} - ${st.name} (${st.class})
                    </div>`;
                } else {
                    hasError = true;
                    html += `<div style="display:inline-flex; align-items:center; gap:0.3rem; background:rgba(239,68,68,0.1); color:#dc2626; border:1px solid rgba(239,68,68,0.25); padding:0.25rem 0.65rem; border-radius:20px; font-size:0.8rem; margin-right:0.35rem; margin-bottom:0.35rem; font-weight:600;">
                        <i class="fas fa-exclamation-circle" style="color:#ef4444;"></i> ไม่พบรหัสนักเรียน: ${id} ในระบบ
                    </div>`;
                }
            });

            if (hasError) {
                input.classList.add('is-invalid');
            } else {
                input.classList.remove('is-invalid');
            }

            feedback.innerHTML = html;
        }

        input.addEventListener('input', checkStudentIds);
        checkStudentIds();
    }

    async function compressImageFile(file) {
        if (!file || !file.type.startsWith('image/') || file.size < 500 * 1024) {
            return file;
        }
        return new Promise((resolve) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = new Image();
                img.onload = function() {
                    const canvas = document.createElement('canvas');
                    let width = img.width;
                    let height = img.height;
                    const maxDim = 1600;

                    if (width > maxDim || height > maxDim) {
                        if (width > height) {
                            height = Math.round((height * maxDim) / width);
                            width = maxDim;
                        } else {
                            width = Math.round((width * maxDim) / height);
                            height = maxDim;
                        }
                    }

                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    canvas.toBlob(
                        (blob) => {
                            if (blob && blob.size < file.size) {
                                const compressedFile = new File([blob], file.name.replace(/\.[^/.]+$/, "") + ".jpg", {
                                    type: 'image/jpeg',
                                    lastModified: Date.now()
                                });
                                resolve(compressedFile);
                            } else {
                                resolve(file);
                            }
                        },
                        'image/jpeg',
                        0.8
                    );
                };
                img.onerror = () => resolve(file);
                img.src = e.target.result;
            };
            reader.onerror = () => resolve(file);
            reader.readAsDataURL(file);
        });
    }

    const evidenceInput = document.getElementById('evidence');
    const fileSizeFeedback = document.getElementById('file-size-feedback');
    if (evidenceInput && fileSizeFeedback) {
        evidenceInput.addEventListener('change', async function() {
            fileSizeFeedback.innerHTML = '';
            evidenceInput.classList.remove('is-invalid');
            if (!this.files || this.files.length === 0) return;

            fileSizeFeedback.innerHTML = `<div style="display:inline-flex; align-items:center; gap:0.3rem; background:rgba(59,130,246,0.1); color:#1d4ed8; border:1px solid rgba(59,130,246,0.25); padding:0.3rem 0.75rem; border-radius:20px; font-size:0.8rem; font-weight:600;">
                <i class="fas fa-spinner fa-spin" style="color:#3b82f6;"></i> กำลังประมวลผลรูปภาพสำหรับอัปโหลด...
            </div>`;

            try {
                const dt = new DataTransfer();
                let totalSize = 0;

                for (let i = 0; i < this.files.length; i++) {
                    const origFile = this.files[i];
                    const procFile = await compressImageFile(origFile);
                    dt.items.add(procFile);
                    totalSize += procFile.size;
                }

                this.files = dt.files;

                const fileCount = this.files.length;
                const totalMb = (totalSize / (1024 * 1024)).toFixed(2);
                fileSizeFeedback.innerHTML = `<div style="display:inline-flex; align-items:center; gap:0.3rem; background:rgba(16,185,129,0.1); color:#047857; border:1px solid rgba(16,185,129,0.25); padding:0.3rem 0.75rem; border-radius:20px; font-size:0.8rem; font-weight:600;">
                    <i class="fas fa-check-circle" style="color:#10b981;"></i> เลือกทั้งหมด ${fileCount} ไฟล์ (ขนาดรวม ${totalMb} MB) พร้อมสำหรับการอัปโหลด ✨
                </div>`;
            } catch (err) {
                console.error(err);
                fileSizeFeedback.innerHTML = `<div style="display:inline-flex; align-items:center; gap:0.3rem; background:rgba(16,185,129,0.1); color:#047857; border:1px solid rgba(16,185,129,0.25); padding:0.3rem 0.75rem; border-radius:20px; font-size:0.8rem; font-weight:600;">
                    <i class="fas fa-check-circle" style="color:#10b981;"></i> เลือกไฟล์เรียบร้อยแล้ว พร้อมสำหรับการอัปโหลด
                </div>`;
            }
        });
    }

});

window.confirmAndSubmitForm = function() {
    const reportForm = document.getElementById('informantReportForm');
    if (!reportForm) return;

    const titleInput = document.getElementById('Title');
    const categorySelect = document.getElementById('Category');
    const descInput = document.getElementById('Description');
    const evidenceInputEl = document.getElementById('evidence');
    const studentIdInput = document.getElementById('StudentID');

    const title = titleInput ? titleInput.value.trim() : '';
    const category = categorySelect ? categorySelect.value.trim() : '';
    const desc = descInput ? descInput.value.trim() : '';
    const fileCount = (evidenceInputEl && evidenceInputEl.files) ? evidenceInputEl.files.length : 0;

    if (studentIdInput && studentIdInput.classList.contains('is-invalid')) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'รหัสนักเรียนไม่ถูกต้อง',
                text: 'โปรดตรวจสอบรหัสนักเรียนที่เกี่ยวข้อง และห้ามระบุรหัสของตนเอง',
                confirmButtonText: 'ตกลง',
                confirmButtonColor: '#ef4444'
            });
        } else {
            alert('โปรดตรวจสอบรหัสนักเรียนที่เกี่ยวข้อง');
        }
        return false;
    }

    if (evidenceInputEl && evidenceInputEl.classList.contains('is-invalid')) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'ไฟล์หลักฐานเกินกำหนด',
                text: 'ขนาดไฟล์หลักฐานเกินขีดจำกัด โปรดเลือกไฟล์ขนาดเล็กลง',
                confirmButtonText: 'ตกลง',
                confirmButtonColor: '#ef4444'
            });
        } else {
            alert('ขนาดไฟล์หลักฐานเกินกำหนด');
        }
        return false;
    }

    if (!category || !title || !desc) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'กรุณากรอกข้อมูลให้ครบถ้วน',
                text: 'โปรดระบุประเภทพฤติกรรม หัวข้อเบาะแส และรายละเอียดเบาะแสก่อนส่ง',
                confirmButtonText: 'ตกลง',
                confirmButtonColor: '#3b82f6'
            });
        } else {
            alert('โปรดระบุข้อมูลที่จำเป็น (*) ให้ครบถ้วนก่อนส่งข้อมูล');
        }
        return false;
    }

    if (typeof Swal !== 'undefined') {
        const fileText = fileCount > 0 ? `${fileCount} ไฟล์` : 'ไม่มีแนบไฟล์';
        const isAnonChecked = document.querySelector('input[name="IsAnonymous"]:checked')?.value === '1';
        const anonText = isAnonChecked 
            ? '<span style="color:#059669; font-weight:600;"><i class="fas fa-user-secret"></i> ปกปิดตัวตน</span>' 
            : '<span style="color:#2563eb; font-weight:600;"><i class="fas fa-user"></i> เปิดเผยตัวตน ({{ auth()->user()->FullName }})</span>';

        Swal.fire({
            title: 'ยืนยันการส่งข้อมูลแจ้งเบาะแส',
            html: `<div style="font-size:0.9rem; color:#4b5563; line-height:1.6; text-align:left; background:#f9fafb; padding:0.85rem 1rem; border-radius:8px; border:1px solid #e5e7eb; margin-top:0.5rem;">
                     <div style="margin-bottom:0.3rem;"><strong>📌 ประเภท:</strong> ${category}</div>
                     <div style="margin-bottom:0.3rem;"><strong>📝 หัวข้อ:</strong> ${title}</div>
                     <div style="margin-bottom:0.3rem;"><strong>👤 สถานะตัวตน:</strong> ${anonText}</div>
                     <div style="margin-bottom:0.3rem;"><strong>📁 ไฟล์หลักฐาน:</strong> ${fileText}</div>
                     <div style="margin-top:0.5rem; font-size:0.82rem; color:#059669; font-weight:600;">
                       <i class="fas fa-shield-alt"></i> ข้อมูลของท่านจะถูกส่งตรงถึงฝ่ายปกครองเพื่อเข้าตรวจสอบ
                     </div>
                   </div>`,
            iconHtml: '<i class="fas fa-bullhorn" style="color:#f59e0b; font-size:2.8rem;"></i>',
            showCancelButton: true,
            confirmButtonText: 'ยืนยัน',
            cancelButtonText: 'ยกเลิก',
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                const btn = document.getElementById('btnSubmitForm');
                if (btn) {
                    btn.style.pointerEvents = 'none';
                    btn.style.opacity = '0.75';
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> กำลังส่งข้อมูล...';
                }
                reportForm.submit();
            }
        });
    } else {
        if (confirm(`ยืนยันการส่งข้อมูลแจ้งเบาะแสเรื่อง "${title}" ใช่หรือไม่?`)) {
            reportForm.submit();
        }
    }
};
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush
