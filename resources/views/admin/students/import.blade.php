@extends('layouts.app')

@section('title', 'นำเข้าข้อมูลนักเรียนผ่านไฟล์ Excel / CSV')
@section('page-title', 'นำเข้าข้อมูลนักเรียน')

@push('styles')
<style>
    .import-header-card {
        background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);
        color: #fff;
        border-radius: 12px;
        padding: 1.5rem 1.75rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 4px 20px rgba(30, 27, 75, 0.15);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .dropzone-box {
        border: 2px dashed #94a3b8;
        background: #f8fafc;
        border-radius: 12px;
        padding: 2.5rem 1.5rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
    }

    .dropzone-box:hover, .dropzone-box.dragover {
        border-color: #3b82f6;
        background: #eff6ff;
    }

    .dropzone-icon {
        font-size: 3rem;
        color: #3b82f6;
        margin-bottom: 0.75rem;
        display: inline-block;
    }

    .mode-card {
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        padding: 1rem;
        cursor: pointer;
        transition: all 0.2s;
        background: #fff;
    }

    .mode-card.active {
        border-color: #1e1b4b;
        background: #f5f3ff;
        box-shadow: 0 0 0 1px #1e1b4b;
    }

    .stat-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.75rem;
        border-radius: 6px;
        font-size: 0.88rem;
        font-weight: 600;
    }

    .badge-insert { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .badge-update { background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }
    .badge-error  { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

    .preview-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.88rem;
    }

    .preview-table th {
        background: #f1f5f9;
        color: #475569;
        font-weight: 700;
        padding: 0.75rem 0.9rem;
        text-align: left;
        border-bottom: 1px solid #cbd5e1;
        white-space: nowrap;
    }

    .preview-table td {
        padding: 0.65rem 0.9rem;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .row-insert { background: rgba(240, 253, 244, 0.6); }
    .row-update { background: rgba(239, 246, 255, 0.6); }
    .row-error  { background: rgba(254, 242, 242, 0.9); }
</style>
@endpush

@section('content')
<div class="container-fluid" style="padding: 0;">

    <!-- 1. Header Card -->
    <div class="import-header-card">
        <div>
            <h2 style="margin: 0 0 0.4rem 0; font-size: 1.4rem; display: flex; align-items: center; gap: 0.6rem;">
                <i class="fas fa-file-excel" style="color: #10b981; margin-right: 0.25rem;"></i>
                <span>นำเข้าข้อมูลนักเรียนผ่านไฟล์ Excel / CSV</span>
            </h2>
            <p style="margin: 0; opacity: 0.9; font-size: 0.88rem;">
                เพิ่มนักเรียนใหม่ หรือ อัปเดตข้อมูลนักเรียนเดิมแบบกลุ่มได้อย่างรวดเร็วและปลอดภัย
            </p>
        </div>
        <div style="display: flex; gap: 0.6rem; align-items: center;">
            <a href="{{ route('admin.students.import.template') }}" class="btn btn-sm" style="background: #10b981; color: #fff; font-weight: 600; padding: 0.5rem 1rem; border-radius: 8px; border: none; display: inline-flex; align-items: center; gap: 0.4rem; text-decoration: none;">
                <i class="fas fa-download"></i> ดาวน์โหลดไฟล์เทมเพลต Excel (.CSV)
            </a>
            <a href="{{ route('admin.students.index') }}" class="btn btn-sm" style="background: rgba(255,255,255,0.15); color: #fff; font-weight: 500; padding: 0.5rem 0.9rem; border-radius: 8px; border: 1px solid rgba(255,255,255,0.3); text-decoration: none;">
                <i class="fas fa-arrow-left"></i> กลับหน้ารายชื่อ
            </a>
        </div>
    </div>

    <!-- 2. Configuration & Upload Grid -->
    <div style="display: grid; grid-template-columns: 1fr 1.2fr; gap: 1.5rem; margin-bottom: 1.5rem;">
        
        <!-- Left: Upload Box -->
        <div class="card" style="padding: 1.25rem; border-radius: 10px; border: 1px solid #e2e8f0;">
            <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 1rem; color: #1e293b; display: flex; align-items: center; gap: 0.4rem;">
                <i class="fas fa-cloud-upload-alt" style="color: #3b82f6;"></i>
                <span>1. เลือกหรือลากไฟล์ Excel / CSV ที่นี่</span>
            </h3>

            <div class="dropzone-box" id="dropZone" onclick="document.getElementById('fileInput').click()">
                <input type="file" id="fileInput" accept=".xlsx, .xls, .csv, .txt" style="display: none;" onchange="handleFileSelect(event)">
                <i class="fas fa-file-excel dropzone-icon"></i>
                <h4 style="margin: 0 0 0.35rem 0; font-size: 1.05rem; font-weight: 700; color: #1e293b;" id="fileNameDisplay">
                    คลิกเพื่อเลือกไฟล์ หรือลากไฟล์มาวางที่นี่
                </h4>
                <p style="margin: 0; font-size: 0.82rem; color: #64748b;">
                    รองรับไฟล์นามสกุล <strong>.xlsx, .xls, .csv</strong> (ขนาดไม่เกิน 10 MB)
                </p>
            </div>

            <div style="margin-top: 1rem; background: #f8fafc; border-radius: 8px; padding: 0.85rem; font-size: 0.8rem; color: #475569; border: 1px solid #e2e8f0;">
                <strong style="color: #1e293b;"><i class="fas fa-lightbulb" style="color: #eab308;"></i> คำแนะนำ:</strong>
                <ul style="margin: 0.3rem 0 0 1.2rem; padding: 0;">
                    <li>ใช้ไฟล์เทมเพลตที่ดาวน์โหลดจากระบบ เพื่อความถูกต้องของชื่อคอลัมน์</li>
                    <li>คอลัมน์ที่จำเป็น: <code>รหัสนักเรียน</code>, <code>ชื่อจริง</code>, <code>นามสกุล</code>, <code>ระดับชั้น</code>, <code>ห้องเรียน</code></li>
                </ul>
            </div>
        </div>

        <!-- Right: Mode Selection -->
        <div class="card" style="padding: 1.25rem; border-radius: 10px; border: 1px solid #e2e8f0; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 1rem; color: #1e293b; display: flex; align-items: center; gap: 0.4rem;">
                    <i class="fas fa-sliders-h" style="color: #8b5cf6;"></i>
                    <span>2. เลือกเงื่อนไขการประมวลผลข้อมูล (Import Mode)</span>
                </h3>

                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <label class="mode-card active" id="modeCard-update_and_insert" onclick="setMode('update_and_insert')">
                        <div style="display: flex; gap: 0.6rem; align-items: flex-start;">
                            <input type="radio" name="import_mode" value="update_and_insert" checked style="margin-top: 0.2rem;">
                            <div>
                                <strong style="color: #1e1b4b; font-size: 0.92rem;">🔄 อัปเดตข้อมูลเดิม + เพิ่มนักเรียนใหม่ (แนะนำ)</strong>
                                <div style="font-size: 0.8rem; color: #64748b; margin-top: 0.15rem;">
                                    ถ้ารหัสตรงกับนักเรียนเดิมจะอัปเดตข้อมูลให้ทันที และถ้าเป็นรหัสใหม่จะสร้างบัญชีให้อัตโนมัติ
                                </div>
                            </div>
                        </div>
                    </label>

                    <label class="mode-card" id="modeCard-insert_only" onclick="setMode('insert_only')">
                        <div style="display: flex; gap: 0.6rem; align-items: flex-start;">
                            <input type="radio" name="import_mode" value="insert_only" style="margin-top: 0.2rem;">
                            <div>
                                <strong style="color: #1e1b4b; font-size: 0.92rem;">➕ เพิ่มเฉพาะนักเรียนใหม่ (ข้ามคนเดิม)</strong>
                                <div style="font-size: 0.8rem; color: #64748b; margin-top: 0.15rem;">
                                    สำหรับรับสมัครนักเรียนใหม่เข้าสู่ระบบ โดยจะไม่แตะต้องข้อมูลของนักเรียนเดิม
                                </div>
                            </div>
                        </div>
                    </label>

                    <label class="mode-card" id="modeCard-update_only" onclick="setMode('update_only')">
                        <div style="display: flex; gap: 0.6rem; align-items: flex-start;">
                            <input type="radio" name="import_mode" value="update_only" style="margin-top: 0.2rem;">
                            <div>
                                <strong style="color: #1e1b4b; font-size: 0.92rem;">📝 อัปเดตเฉพาะนักเรียนเดิม (ไม่เพิ่มคนใหม่)</strong>
                                <div style="font-size: 0.8rem; color: #64748b; margin-top: 0.15rem;">
                                    สำหรับปรับปรุงข้อมูล เช่น เบอร์โทร, ห้องเรียน โดยไม่เพิ่มรายชื่อใหม่เข้าไป
                                </div>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <div style="margin-top: 1rem; display: flex; justify-content: flex-end;">
                <button type="button" id="btnSubmitImport" class="btn btn-primary" style="padding: 0.65rem 1.5rem; font-weight: 700; background: #1e1b4b; border-color: #1e1b4b; font-size: 0.95rem;" disabled onclick="submitImport()">
                    <i class="fas fa-check-circle" style="margin-right: 0.35rem; color: #10b981;"></i> ยืนยันและนำเข้าข้อมูล (<span id="btnValidCount">0</span> รายการ)
                </button>
            </div>
        </div>

    </div>

    <!-- 3. Real-Time Validation & Preview Card -->
    <div class="card" id="previewCard" style="border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; display: none;">
        <div style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 1rem 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.6rem;">
                <h3 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: #1e293b;">
                    <i class="fas fa-table" style="color: #3b82f6; margin-right: 0.35rem;"></i>
                    ตารางตรวจสอบความถูกต้องข้อมูล (Validation Preview)
                </h3>
            </div>

            <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                <span class="stat-badge badge-insert">
                    <i class="fas fa-user-plus"></i> เพิ่มใหม่: <strong id="statInsert">0</strong>
                </span>
                <span class="stat-badge badge-update">
                    <i class="fas fa-user-edit"></i> อัปเดตเดิม: <strong id="statUpdate">0</strong>
                </span>
                <span class="stat-badge badge-error">
                    <i class="fas fa-exclamation-circle"></i> ข้อผิดพลาด: <strong id="statError">0</strong>
                </span>
            </div>
        </div>

        <div style="max-height: 480px; overflow-y: auto; overflow-x: auto;">
            <table class="preview-table">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">ลำดับ</th>
                        <th style="width: 140px;">สถานะการตรวจสอบ</th>
                        <th>รหัสนักเรียน</th>
                        <th>เลขบัตรประชาชน</th>
                        <th>ชื่อ - นามสกุล</th>
                        <th>เพศ</th>
                        <th>ระดับชั้น</th>
                        <th>ห้องเรียน</th>
                        <th>เบอร์โทร</th>
                        <th>ข้อความแจ้งเตือน</th>
                    </tr>
                </thead>
                <tbody id="previewTableBody">
                    <!-- Populated dynamically by JS -->
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<!-- SheetJS CDN for high-speed Excel/CSV Parsing -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<script>
    const existingStudentsMap = new Map();
    const existingCitizenMap = new Map();

    // Load existing students into lookup map
    @foreach($existingStudents as $es)
        existingStudentsMap.set('{{ $es['student_id'] }}', @json($es));
        @if(!empty($es['citizen_id']))
            existingCitizenMap.set('{{ $es['citizen_id'] }}', @json($es));
        @endif
    @endforeach

    let parsedRows = [];
    let currentMode = 'update_and_insert';

    function setMode(mode) {
        currentMode = mode;
        document.querySelectorAll('.mode-card').forEach(el => el.classList.remove('active'));
        document.getElementById('modeCard-' + mode).classList.add('active');
        document.querySelector(`input[name="import_mode"][value="${mode}"]`).checked = true;
        if (parsedRows.length > 0) {
            validateAndRender();
        }
    }

    // Drag & Drop Handlers
    const dropZone = document.getElementById('dropZone');
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            dropZone.classList.add('dragover');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            dropZone.classList.remove('dragover');
        }, false);
    });

    dropZone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files.length) {
            processFile(files[0]);
        }
    });

    function handleFileSelect(event) {
        const file = event.target.files[0];
        if (file) {
            processFile(file);
        }
    }

    function processFile(file) {
        document.getElementById('fileNameDisplay').innerText = '📄 ' + file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
        
        const reader = new FileReader();
        reader.onload = function(e) {
            try {
                const data = new Uint8Array(e.target.result);
                const workbook = XLSX.read(data, { type: 'array' });
                const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
                const rawJson = XLSX.utils.sheet_to_json(firstSheet, { defval: '' });

                if (rawJson.length === 0) {
                    alert('ไม่พบข้อมูลในไฟล์ หรือไฟล์ว่างเปล่า');
                    return;
                }

                parsedRows = normalizeHeaders(rawJson);
                validateAndRender();
            } catch (err) {
                console.error(err);
                alert('เกิดข้อผิดพลาดในการอ่านไฟล์: ' + err.message);
            }
        };
        reader.readAsArrayBuffer(file);
    }

    function normalizeHeaders(rawRows) {
        return rawRows.map(r => {
            let studentId = (r['รหัสนักเรียน'] || r['StudentID'] || r['student_id'] || r['รหัส'] || '').toString().trim();
            let citizenId = (r['รหัสบัตรประชาชน'] || r['CitizenID'] || r['citizen_id'] || r['เลขบัตรประชาชน'] || '').toString().trim();
            let prefix    = (r['คำนำหน้า'] || r['Prefix'] || r['คำนำหน้านาม'] || '').toString().trim();
            let firstName = (r['ชื่อจริง'] || r['FirstName'] || r['first_name'] || r['ชื่อ'] || '').toString().trim();
            let lastName  = (r['นามสกุล'] || r['LastName'] || r['last_name'] || '').toString().trim();
            let gender    = (r['เพศ'] || r['Gender'] || r['gender'] || '').toString().trim();
            let grade     = (r['ระดับชั้น'] || r['GradeLevel'] || r['grade'] || r['ชั้น'] || '').toString().trim();
            let room      = (r['ห้องเรียน'] || r['Classroom'] || r['room'] || r['ห้อง'] || '').toString().trim();
            let phone     = (r['เบอร์โทรศัพท์'] || r['Phone'] || r['phone'] || r['เบอร์โทร'] || '').toString().trim();

            return {
                student_id: studentId,
                citizen_id: citizenId,
                prefix: prefix,
                first_name: firstName,
                last_name: lastName,
                gender: gender,
                grade: grade,
                room: room,
                phone: phone
            };
        });
    }

    function validateAndRender() {
        const tbody = document.getElementById('previewTableBody');
        tbody.innerHTML = '';

        let cntInsert = 0;
        let cntUpdate = 0;
        let cntError = 0;

        parsedRows.forEach((row, idx) => {
            let errors = [];
            let status = 'insert';
            let statusText = '🟢 เพิ่มใหม่';
            let rowClass = 'row-insert';

            // Validations
            if (!row.student_id) errors.push('ไม่มีรหัสนักเรียน');
            if (!row.first_name) errors.push('ไม่มีชื่อ');
            if (!row.last_name) errors.push('ไม่มีนามสกุล');
            if (!row.grade) errors.push('ไม่มีระดับชั้น');
            if (!row.room) errors.push('ไม่มีห้องเรียน');

            const isExisting = existingStudentsMap.has(row.student_id) || (row.citizen_id && existingCitizenMap.has(row.citizen_id));

            if (errors.length > 0) {
                status = 'error';
                statusText = '🔴 ไม่ถูกต้อง';
                rowClass = 'row-error';
                cntError++;
            } else if (isExisting) {
                status = 'update';
                statusText = '🔵 อัปเดตเดิม';
                rowClass = 'row-update';
                cntUpdate++;
            } else {
                status = 'insert';
                statusText = '🟢 เพิ่มใหม่';
                rowClass = 'row-insert';
                cntInsert++;
            }

            row._status = status;
            row._errors = errors;

            const tr = document.createElement('tr');
            tr.className = rowClass;
            tr.innerHTML = `
                <td style="text-align: center; color: #64748b;">${idx + 1}</td>
                <td><strong>${statusText}</strong></td>
                <td><code>${row.student_id || '-'}</code></td>
                <td><small>${row.citizen_id || '-'}</small></td>
                <td><strong>${(row.prefix ? row.prefix + ' ' : '') + row.first_name + ' ' + row.last_name}</strong></td>
                <td>${row.gender || '-'}</td>
                <td><span class="badge" style="background:#f1f5f9; color:#1e293b;">${row.grade || '-'}</span></td>
                <td>${row.room || '-'}</td>
                <td><small>${row.phone || '-'}</small></td>
                <td><span style="color: ${errors.length ? '#dc2626' : '#16a34a'}; font-size: 0.8rem;">${errors.length ? errors.join(', ') : 'พร้อมนำเข้า'}</span></td>
            `;
            tbody.appendChild(tr);
        });

        document.getElementById('statInsert').innerText = cntInsert;
        document.getElementById('statUpdate').innerText = cntUpdate;
        document.getElementById('statError').innerText = cntError;

        const validCount = (cntInsert + cntUpdate);
        document.getElementById('btnValidCount').innerText = validCount;
        document.getElementById('btnSubmitImport').disabled = (validCount === 0);

        document.getElementById('previewCard').style.display = 'block';
    }

    function submitImport() {
        const validRows = parsedRows.filter(r => r._status !== 'error');
        if (validRows.length === 0) {
            alert('ไม่พบรายการที่ถูกต้องสำหรับนำเข้า');
            return;
        }

        if (!confirm(`ยืนยันการนำเข้าข้อมูลนักเรียนจำนวน ${validRows.length} รายการ เข้าสู่ระบบ?`)) {
            return;
        }

        const btn = document.getElementById('btnSubmitImport');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> กำลังบันทึกข้อมูล...';

        fetch('{{ route('admin.students.import.store') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                mode: currentMode,
                students: validRows
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('✅ ' + data.message);
                window.location.href = data.redirect || '{{ route('admin.students.index') }}';
            } else {
                alert('❌ เกิดข้อผิดพลาด: ' + data.message);
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check-circle"></i> ยืนยันและนำเข้าข้อมูล';
            }
        })
        .catch(err => {
            console.error(err);
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-circle"></i> ยืนยันและนำเข้าข้อมูล';
        });
    }
</script>
@endpush
