@extends('layouts.app')

@section('title', 'ระบบเลื่อนชั้นปีนักเรียน — ผู้ดูแลระบบ')
@section('page-title', 'ระบบเลื่อนชั้นปีนักเรียน')

@push('styles')
<style>
    .promotion-header-card {
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

    .promotion-stepper {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #fff;
        border-radius: 10px;
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        border: 1px solid #e2e8f0;
    }

    .step-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #64748b;
        font-size: 0.88rem;
        font-weight: 500;
    }

    .step-item.active {
        color: #1e1b4b;
        font-weight: 700;
    }

    .step-number {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #f1f5f9;
        color: #64748b;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        font-weight: 700;
    }

    .step-item.active .step-number {
        background: #1e1b4b;
        color: #fff;
    }

    .step-item.completed .step-number {
        background: #10b981;
        color: #fff;
    }

    .step-divider {
        flex: 1;
        height: 2px;
        background: #e2e8f0;
        margin: 0 1rem;
    }

    .room-selector-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.25rem;
        margin-bottom: 1.5rem;
    }

    @media (max-width: 768px) {
        .room-selector-grid {
            grid-template-columns: 1fr;
        }
    }

    .selector-box {
        background: #fff;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        padding: 1.25rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }

    .selector-box-title {
        font-weight: 700;
        font-size: 0.95rem;
        color: #1e293b;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .student-table {
        width: 100%;
        border-collapse: collapse;
    }

    .student-table th {
        background: #f8fafc;
        color: #475569;
        font-weight: 600;
        font-size: 0.82rem;
        padding: 0.75rem 1rem;
        text-align: left;
        border-bottom: 1px solid #e2e8f0;
    }

    .student-table td {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.88rem;
        vertical-align: middle;
    }

    .student-table tr:hover {
        background: #f8fafc;
    }

    .student-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        object-fit: cover;
        background: #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: #475569;
        font-size: 0.85rem;
    }

    .score-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.2rem 0.6rem;
        border-radius: 9999px;
        font-size: 0.78rem;
        font-weight: 600;
    }

    .score-green { background: #dcfce7; color: #166534; }
    .score-yellow { background: #fef3c7; color: #92400e; }
    .score-red { background: #fee2e2; color: #991b1b; }

    .action-summary-bar {
        position: sticky;
        bottom: 1rem;
        background: #fff;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        padding: 1rem 1.5rem;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        z-index: 10;
        margin-top: 1.5rem;
    }

    .pill-counter {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.3rem 0.75rem;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .pill-promote { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }
    .pill-repeat  { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
    .pill-graduate{ background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
    .pill-hold    { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
</style>
@endpush

@section('content')
<div class="container-fluid" style="padding: 0;">

    <!-- 1. Header Card -->
    <div class="promotion-header-card">
        <div>
            <h2 style="margin: 0 0 0.4rem 0; font-size: 1.4rem; display: flex; align-items: center; gap: 0.6rem;">
                <i class="fas fa-layer-group" style="color: #fbbf24; margin-right: 0.25rem;"></i>
                <span>ระบบจัดการเลื่อนชั้นปีนักเรียน (Student Grade Promotion)</span>
            </h2>
            <p style="margin: 0; opacity: 0.9; font-size: 0.88rem;">
                เครื่องมือปรับระดับชั้นและห้องเรียนแบบกลุ่ม สำหรับเตรียมความพร้อมขึ้นปีการศึกษาใหม่
            </p>
        </div>
        <div style="display: flex; gap: 0.65rem; align-items: center; flex-wrap: wrap;">
            <span class="badge" style="background: rgba(255,255,255,0.15); color: #fff; padding: 0.55rem 0.95rem; font-size: 0.88rem; border: 1px solid rgba(255,255,255,0.25); border-radius: 8px; display: inline-flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-calendar-alt" style="color: #fbbf24; font-size: 0.95rem;"></i>
                <span>{{ $currentSemester ? "ปีการศึกษา {$currentSemester->academic_year} (ภาคเรียนที่ {$currentSemester->term})" : "ปีการศึกษา " . (now()->year + 543) }}</span>
            </span>
            <button type="button" class="btn" onclick="openSemesterModal()" style="background: #fbbf24; color: #78350f; font-weight: 700; font-size: 0.85rem; padding: 0.55rem 1rem; border-radius: 8px; border: none; display: inline-flex; align-items: center; gap: 0.4rem; box-shadow: 0 2px 6px rgba(0,0,0,0.15); cursor: pointer; transition: all 0.2s ease;">
                <i class="fas fa-cog"></i> จัดการปีการศึกษา / เพิ่มปีใหม่
            </button>
        </div>
    </div>

    <!-- 2. Stepper Progress -->
    <div class="promotion-stepper">
        <div class="step-item completed">
            <div class="step-number"><i class="fas fa-check" style="font-size: 0.7rem;"></i></div>
            <span>1. เลือกห้องต้นทาง</span>
        </div>
        <div class="step-divider"></div>
        <div class="step-item active">
            <div class="step-number">2</div>
            <span>2. กำหนดห้องปลายทาง</span>
        </div>
        <div class="step-divider"></div>
        <div class="step-item">
            <div class="step-number">3</div>
            <span>3. ตรวจสอบรายชื่อ</span>
        </div>
        <div class="step-divider"></div>
        <div class="step-item">
            <div class="step-number">4</div>
            <span>4. ยืนยันเลื่อนชั้น</span>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="background: #dcfce7; border-left: 4px solid #10b981; color: #166534; padding: 1rem 1.25rem; border-radius: 8px; margin-bottom: 1.5rem;">
            <i class="fas fa-check-circle" style="margin-right: 0.4rem;"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger" style="background: #fee2e2; border-left: 4px solid #ef4444; color: #991b1b; padding: 1rem 1.25rem; border-radius: 8px; margin-bottom: 1.5rem;">
            <i class="fas fa-exclamation-triangle" style="margin-right: 0.4rem;"></i> {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('admin.promotions.execute') }}" method="POST" id="promotionForm">
        @csrf

        <!-- 3. Source & Target Selection Cards -->
        <div class="room-selector-grid">
            <!-- Source Room Box -->
            <div class="selector-box">
                <div class="selector-box-title">
                    <i class="fas fa-sign-out-alt" style="color: #3b82f6; margin-right: 0.35rem;"></i>
                    <span>ห้องเรียนต้นทาง (ที่จะทำการเลื่อนชั้น)</span>
                </div>
                <div class="form-group" style="margin: 0;">
                    <select name="source_room" id="sourceRoomSelect" class="form-control" style="font-size: 0.95rem; font-weight: 600; padding: 0.6rem 0.85rem;" onchange="window.location.href='{{ route('admin.promotions.index') }}?source_room=' + encodeURIComponent(this.value)">
                        @foreach($allRooms as $r)
                            <option value="{{ $r['name'] }}" {{ $selectedRoom === $r['name'] ? 'selected' : '' }}>
                                ห้อง {{ $r['name'] }} &nbsp; (นักเรียน {{ $r['count'] }} คน)
                            </option>
                        @endforeach
                    </select>
                </div>
                <div style="font-size: 0.78rem; color: #64748b; margin-top: 0.5rem;">
                    <i class="fas fa-info-circle" style="margin-right: 0.25rem;"></i> เลือกระดับชั้นและห้องเรียนปัจจุบันที่ต้องการนำนักเรียนไปเลื่อนชั้น
                </div>
            </div>

            <!-- Target Room Box -->
            <div class="selector-box">
                <div class="selector-box-title">
                    <i class="fas fa-sign-in-alt" style="color: #10b981; margin-right: 0.35rem;"></i>
                    <span>ห้องเรียนปลายทาง (ระดับชั้นใหม่)</span>
                </div>
                <div class="form-group" style="margin: 0;">
                    <select name="target_room" id="targetRoomSelect" class="form-control" style="font-size: 0.95rem; font-weight: 600; padding: 0.6rem 0.85rem;" onchange="updateTargetPreview(this.value)">
                        @if(str_starts_with($selectedRoom, 'ม.6'))
                            <option value="graduate" selected>🎓 สำเร็จการศึกษา (จบการศึกษาชั้น ม.6)</option>
                        @endif

                        <optgroup label="ระดับชั้น มัธยมศึกษาตอนต้น">
                            @foreach($allRooms as $r)
                                @if(in_array($r['grade'], ['ม.1', 'ม.2', 'ม.3']))
                                    <option value="{{ $r['name'] }}" {{ $suggestedTargetRoom === $r['name'] ? 'selected' : '' }}>
                                        เลื่อนไปห้อง {{ $r['name'] }}
                                    </option>
                                @endif
                            @endforeach
                        </optgroup>

                        <optgroup label="ระดับชั้น มัธยมศึกษาตอนปลาย">
                            @foreach($allRooms as $r)
                                @if(in_array($r['grade'], ['ม.4', 'ม.5', 'ม.6']))
                                    <option value="{{ $r['name'] }}" {{ $suggestedTargetRoom === $r['name'] ? 'selected' : '' }}>
                                        เลื่อนไปห้อง {{ $r['name'] }}
                                    </option>
                                @endif
                            @endforeach
                        </optgroup>

                        @if(!str_starts_with($selectedRoom, 'ม.6'))
                            <option value="graduate" {{ $suggestedTargetRoom === 'graduate' ? 'selected' : '' }}>
                                🎓 สำเร็จการศึกษา (สำหรับนักเรียนจบการศึกษา)
                            </option>
                        @endif
                    </select>
                </div>
                <div style="font-size: 0.78rem; color: #10b981; margin-top: 0.5rem; font-weight: 500;" id="targetHelpText">
                    <i class="fas fa-magic" style="margin-right: 0.25rem;"></i> ระบบแนะนำห้องถัดไปให้อัตโนมัติ (สามารถปรับเปลี่ยนห้องได้ตามต้องการ)
                </div>
            </div>
        </div>

        <!-- 4. Student Review Table Card -->
        <div class="card" style="border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; margin-bottom: 1.5rem;">
            <div class="card-header-bar" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 1rem 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                <div style="display: flex; align-items: center; gap: 0.6rem;">
                    <h3 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: #1e293b;">
                        <i class="fas fa-users" style="color: #3b82f6; margin-right: 0.35rem;"></i>
                        รายชื่อนักเรียนห้อง {{ $selectedRoom }}
                        <span class="badge badge-primary" style="font-size: 0.8rem; margin-left: 0.35rem; font-weight: normal;">
                            {{ $students->count() }} คน
                        </span>
                    </h3>
                </div>
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <button type="button" class="btn btn-outline btn-sm" onclick="setAllActions('promote')">
                        <i class="fas fa-check-double" style="margin-right: 0.25rem;"></i> เลื่อนชั้นทั้งหมด
                    </button>
                    <button type="button" class="btn btn-outline btn-sm" onclick="setAllActions('repeat')">
                        <i class="fas fa-undo" style="margin-right: 0.25rem;"></i> ซ้ำชั้นทั้งหมด
                    </button>
                </div>
            </div>

            <div class="table-wrap" style="overflow-x: auto;">
                <table class="student-table">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">
                                <input type="checkbox" id="selectAllCheckbox" checked onchange="toggleSelectAll(this)">
                            </th>
                            <th style="width: 60px;"></th>
                            <th>รหัสนักเรียน</th>
                            <th>ชื่อ - นามสกุล</th>
                            <th style="text-align: center;">คะแนนความประพฤติ</th>
                            <th style="width: 260px;">การดำเนินการ (รายบุคคล)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $s)
                            @php
                                $score = $s->behavior_score ?? 100;
                                $scoreClass = $score >= 80 ? 'score-green' : ($score >= 50 ? 'score-yellow' : 'score-red');
                                $isGraduate = str_starts_with($selectedRoom, 'ม.6');
                            @endphp
                            <tr id="row-{{ $s->StudentID }}">
                                <td style="text-align: center;">
                                    <input type="checkbox" class="student-checkbox" 
                                           data-id="{{ $s->StudentID }}" 
                                           checked 
                                           onchange="handleCheckboxChange(this, '{{ $s->StudentID }}')">
                                </td>
                                <td>
                                    <div class="student-avatar">
                                        {{ mb_substr($s->FullName, 0, 1) }}
                                    </div>
                                </td>
                                <td>
                                    <code style="font-size: 0.85rem; font-weight: 700; color: #1e293b; background: #f1f5f9; padding: 0.15rem 0.4rem; border-radius: 4px;">
                                        {{ $s->StudentID }}
                                    </code>
                                </td>
                                <td>
                                    <strong>{{ $s->FullName }}</strong>
                                    <div style="font-size: 0.75rem; color: #64748b;">เพศ: {{ $s->Gender ?? '-' }}</div>
                                </td>
                                <td style="text-align: center;">
                                    <span class="score-badge {{ $scoreClass }}">
                                        <i class="fas {{ $score >= 80 ? 'fa-shield-alt' : 'fa-exclamation-triangle' }}" style="margin-right: 0.2rem;"></i>
                                        {{ $score }} คะแนน
                                    </span>
                                </td>
                                <td>
                                    <select name="students[{{ $s->StudentID }}][action]" 
                                            id="action-{{ $s->StudentID }}" 
                                            class="form-control form-control-sm action-selector" 
                                            style="font-size: 0.84rem; font-weight: 500;"
                                            onchange="recalculateSummary()">
                                        @if($isGraduate)
                                            <option value="graduate" selected>🎓 สำเร็จการศึกษา (จบ ม.6)</option>
                                            <option value="repeat">🔴 ซ้ำชั้น (ไม่จบการศึกษา)</option>
                                            <option value="hold">🟡 พักการเรียน / รอปรับปรุง</option>
                                        @else
                                            <option value="promote" selected>🟢 เลื่อนชั้นปกติ (ไปยังห้องปลายทาง)</option>
                                            <option value="repeat">🔴 ซ้ำชั้น (คงอยู่ห้องเดิม)</option>
                                            <option value="hold">🟡 พักการเรียน</option>
                                            <option value="graduate">🎓 สำเร็จการศึกษา</option>
                                        @endif
                                    </select>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 3rem 1rem; color: #64748b;">
                                    <i class="fas fa-user-slash" style="font-size: 2rem; margin-bottom: 0.75rem; opacity: 0.4; display: block;"></i>
                                    ไม่พบรายชื่อนักเรียนในห้อง {{ $selectedRoom }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 5. Action & Summary Footer Bar -->
        @if($students->count() > 0)
            <div class="action-summary-bar">
                <div style="display: flex; gap: 0.6rem; align-items: center; flex-wrap: wrap;">
                    <span style="font-size: 0.9rem; font-weight: 700; color: #1e293b; margin-right: 0.25rem;">
                        <i class="fas fa-calculator" style="color: #3b82f6; margin-right: 0.3rem;"></i> สรุปการดำเนินการ:
                    </span>
                    <span id="pillPromote" class="pill-counter pill-promote">
                        <i class="fas fa-arrow-up" style="margin-right: 0.25rem;"></i> เลื่อนชั้น: <strong id="cntPromote">0</strong> คน
                    </span>
                    <span id="pillGraduate" class="pill-counter pill-graduate" style="display: none;">
                        <i class="fas fa-graduation-cap" style="margin-right: 0.25rem;"></i> จบการศึกษา: <strong id="cntGraduate">0</strong> คน
                    </span>
                    <span id="pillRepeat" class="pill-counter pill-repeat">
                        <i class="fas fa-undo" style="margin-right: 0.25rem;"></i> ซ้ำชั้น: <strong id="cntRepeat">0</strong> คน
                    </span>
                    <span id="pillHold" class="pill-counter pill-hold">
                        <i class="fas fa-pause-circle" style="margin-right: 0.25rem;"></i> พักการเรียน: <strong id="cntHold">0</strong> คน
                    </span>
                </div>

                <div style="display: flex; gap: 0.75rem; align-items: center;">
                    <a href="{{ route('admin.students.index') }}" class="btn btn-outline" style="padding: 0.6rem 1.25rem;">
                        ยกเลิก
                    </a>
                    <button type="button" class="btn btn-primary" style="padding: 0.6rem 1.5rem; font-size: 0.95rem; font-weight: 700; background: #1e1b4b; border-color: #1e1b4b;" onclick="openConfirmModal()">
                        <i class="fas fa-layer-group" style="margin-right: 0.35rem; color: #fbbf24;"></i> ดำเนินการเลื่อนชั้นปี
                    </button>
                </div>
            </div>
        @endif
    </form>
</div>

<!-- Confirmation Modal -->
<div id="confirmModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #fff; border-radius: 12px; width: 100%; max-width: 480px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); overflow: hidden; animation: modalFadeIn 0.2s ease-out;">
        <div style="background: #1e1b4b; color: #fff; padding: 1.25rem 1.5rem; display: flex; align-items: center; justify-content: space-between;">
            <h4 style="margin: 0; font-size: 1.1rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-exclamation-triangle" style="color: #fbbf24;"></i> ยืนยันการเลื่อนชั้นปี
            </h4>
            <button type="button" onclick="closeConfirmModal()" style="background: none; border: none; color: #fff; font-size: 1.2rem; cursor: pointer; opacity: 0.8;">&times;</button>
        </div>
        <div style="padding: 1.5rem;">
            <p style="margin: 0 0 1rem 0; color: #334155; font-size: 0.95rem;">
                ท่านกำลังจะทำการปรับระดับชั้นนักเรียนห้อง <strong>{{ $selectedRoom }}</strong> ไปยัง 
                <strong id="modalTargetRoom" style="color: #1e1b4b;">-</strong>
            </p>
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem; margin-bottom: 1.25rem;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.4rem; font-size: 0.88rem;">
                    <span style="color: #64748b;">จำนวนนักเรียนที่จะเลื่อนชั้น:</span>
                    <strong id="modalPromoteCount" style="color: #1e40af;">0 คน</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.4rem; font-size: 0.88rem;">
                    <span style="color: #64748b;">จำนวนนักเรียนซ้ำชั้น (คงเดิม):</span>
                    <strong id="modalRepeatCount" style="color: #991b1b;">0 คน</strong>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 0.88rem;">
                    <span style="color: #64748b;">จำนวนนักเรียนพักการเรียน:</span>
                    <strong id="modalHoldCount" style="color: #92400e;">0 คน</strong>
                </div>
            </div>
            <div style="font-size: 0.8rem; color: #e11d48; margin-bottom: 1.5rem; display: flex; gap: 0.4rem; align-items: flex-start;">
                <i class="fas fa-shield-alt" style="margin-top: 2px;"></i>
                <span>ระบบจะทำการอัปเดตระดับชั้นและห้องเรียนของนักเรียนทันที โดยประวัติความประพฤติและผลการละหมาดเดิมในอดีตจะยังคงอยู่ครบถ้วน</span>
            </div>
            <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                <button type="button" class="btn btn-outline" onclick="closeConfirmModal()">
                    ยกเลิก
                </button>
                <button type="button" class="btn btn-primary" style="background: #1e1b4b; border-color: #1e1b4b;" onclick="submitPromotionForm()">
                    <i class="fas fa-check"></i> ยืนยันและดำเนินการ
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Semester Management Modal -->
<div id="semesterModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.55); backdrop-filter: blur(3px); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #fff; border-radius: 16px; width: 100%; max-width: 680px; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); border: 1px solid var(--border);">
        {{-- Modal Header --}}
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; background: linear-gradient(135deg, var(--navy) 0%, #1e1b4b 100%); color: #fff; border-top-left-radius: 15px; border-top-right-radius: 15px;">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(251,191,36,0.2); color: #fbbf24; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 1.1rem; font-weight: 700; color: #fff;">จัดการปีการศึกษาและภาคเรียน</h3>
                    <p style="margin: 0; font-size: 0.8rem; color: rgba(255,255,255,0.75);">เพิ่มปีการศึกษาใหม่ หรือสลับภาคเรียนปัจจุบันของสถานศึกษา</p>
                </div>
            </div>
            <button type="button" onclick="closeSemesterModal()" style="background: rgba(255,255,255,0.15); border: none; color: #fff; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 1rem;">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- Modal Body --}}
        <div style="padding: 1.5rem;">
            {{-- Form: Add new semester --}}
            <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 1.25rem; margin-bottom: 1.5rem;">
                <h4 style="margin: 0 0 1rem 0; font-size: 0.95rem; font-weight: 700; color: var(--navy); display: flex; align-items: center; gap: 0.4rem;">
                    <i class="fas fa-plus-circle" style="color: var(--primary);"></i> เพิ่มปีการศึกษา / ภาคเรียนใหม่
                </h4>
                <form action="{{ route('admin.semesters.store') }}" method="POST">
                    @csrf
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <label style="display: block; font-size: 0.82rem; font-weight: 600; margin-bottom: 0.35rem; color: #334155;">
                                ปีการศึกษา (พ.ศ.) <span style="color: #ef4444;">*</span>
                            </label>
                            @php
                                $maxYear = isset($semestersList) ? $semestersList->max('academic_year') : (now()->year + 543);
                            @endphp
                            <input type="number" name="academic_year" required min="2500" max="2700" 
                                   value="{{ $maxYear }}"
                                   placeholder="เช่น 2570"
                                   style="width: 100%; height: 38px; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0 0.75rem; font-size: 0.9rem; font-family: 'Sarabun', sans-serif;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.82rem; font-weight: 600; margin-bottom: 0.35rem; color: #334155;">
                                ภาคเรียนที่ <span style="color: #ef4444;">*</span>
                            </label>
                            <select name="term" required style="width: 100%; height: 38px; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0 0.75rem; font-size: 0.9rem; font-family: 'Sarabun', sans-serif; background: #fff;">
                                <option value="1">ภาคเรียนที่ 1</option>
                                <option value="2">ภาคเรียนที่ 2</option>
                                <option value="3">ภาคเรียนฤดูร้อน (3)</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: #334155; cursor: pointer; user-select: none;">
                            <input type="checkbox" name="is_active" value="1" style="width: 16px; height: 16px; accent-color: var(--primary); cursor: pointer;">
                            <span>ตั้งเป็น <strong>ภาคเรียนปัจจุบัน (Active)</strong> ทันที</span>
                        </label>
                        <button type="submit" class="btn btn-primary" style="font-size: 0.85rem; padding: 0.45rem 1.25rem; border-radius: 8px; display: inline-flex; align-items: center; gap: 0.4rem;">
                            <i class="fas fa-save"></i> บันทึก
                        </button>
                    </div>
                </form>
            </div>

            {{-- Table: All Semesters --}}
            <div>
                <h4 style="margin: 0 0 0.75rem 0; font-size: 0.95rem; font-weight: 700; color: var(--navy); display: flex; align-items: center; gap: 0.4rem;">
                    <i class="fas fa-list-ul" style="color: var(--primary);"></i> รายการปีการศึกษาทั้งหมดในระบบ
                </h4>
                <div style="border: 1px solid var(--border); border-radius: 10px; overflow: hidden;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                        <thead style="background: #f1f5f9; border-bottom: 1px solid var(--border); text-align: left;">
                            <tr>
                                <th style="padding: 0.65rem 1rem; color: #475569; font-weight: 600;">ปีการศึกษา / ภาคเรียน</th>
                                <th style="padding: 0.65rem 1rem; color: #475569; font-weight: 600; text-align: center;">สถานะ</th>
                                <th style="padding: 0.65rem 1rem; color: #475569; font-weight: 600; text-align: center;">ข้อมูลในเทอม</th>
                                <th style="padding: 0.65rem 1rem; color: #475569; font-weight: 600; text-align: right;">การจัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($semestersList))
                                @forelse($semestersList as $sem)
                                <tr style="border-bottom: 1px solid #f1f5f9; background: {{ $sem->is_active ? '#f0fdf4' : '#fff' }};">
                                    <td style="padding: 0.75rem 1rem; font-weight: 600; color: #1e293b;">
                                        ปีการศึกษา {{ $sem->academic_year }} ภาคเรียนที่ {{ $sem->term }}
                                    </td>
                                    <td style="padding: 0.75rem 1rem; text-align: center;">
                                        @if($sem->is_active)
                                            <span style="background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; padding: 0.2rem 0.6rem; border-radius: 20px; font-size: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.3rem;">
                                                <i class="fas fa-check-circle"></i> ปัจจุบัน
                                            </span>
                                        @else
                                            <span style="color: #94a3b8; font-size: 0.78rem;">ปกติ</span>
                                        @endif
                                    </td>
                                    <td style="padding: 0.75rem 1rem; text-align: center; font-size: 0.78rem; color: #64748b;">
                                        @php
                                            $recordsTotal = ($sem->attendances_count ?? 0) + ($sem->behavior_records_count ?? 0) + ($sem->prayer_records_count ?? 0);
                                        @endphp
                                        @if($recordsTotal > 0)
                                            <span title="เช็คชื่อ: {{ $sem->attendances_count ?? 0 }} | พฤติกรรม: {{ $sem->behavior_records_count ?? 0 }} | ละหมาด: {{ $sem->prayer_records_count ?? 0 }}">
                                                {{ $recordsTotal }} รายการ
                                            </span>
                                        @else
                                            <span style="color: #cbd5e1;">-</span>
                                        @endif
                                    </td>
                                    <td style="padding: 0.75rem 1rem; text-align: right;">
                                        <div style="display: inline-flex; align-items: center; gap: 0.4rem;">
                                            @if(!$sem->is_active)
                                            <form action="{{ route('admin.semesters.set-active', $sem->semester_id) }}" method="POST" style="margin: 0;">
                                                @csrf
                                                <button type="submit" class="btn" style="background: #f1f5f9; color: #0f172a; border: 1px solid #cbd5e1; font-size: 0.75rem; font-weight: 600; padding: 0.25rem 0.65rem; border-radius: 6px; cursor: pointer;" title="สลับให้เป็นภาคเรียนปัจจุบัน">
                                                    <i class="fas fa-toggle-on" style="color: #10b981;"></i> ตั้งเป็นปัจจุบัน
                                                </button>
                                            </form>

                                            @if($recordsTotal === 0)
                                            <form action="{{ route('admin.semesters.destroy', $sem->semester_id) }}" method="POST" style="margin: 0;" onsubmit="return confirm('ยืนยันการลบปีการศึกษา {{ $sem->academic_year }} ภาคเรียนที่ {{ $sem->term }} หรือไม่?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn" style="background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; font-size: 0.75rem; font-weight: 600; padding: 0.25rem 0.55rem; border-radius: 6px; cursor: pointer;" title="ลบปีการศึกษานี้">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                            @endif
                                            @else
                                            <span style="font-size: 0.75rem; font-weight: 600; color: #15803d; padding: 0.25rem 0.5rem;">
                                                <i class="fas fa-star" style="color: #f59e0b;"></i> ใช้งานอยู่
                                            </span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" style="text-align: center; padding: 1.5rem; color: #94a3b8;">
                                        ไม่พบข้อมูลปีการศึกษา
                                    </td>
                                </tr>
                                @endforelse
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        {{-- Modal Footer --}}
        <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; background: #f8fafc; border-bottom-left-radius: 15px; border-bottom-right-radius: 15px;">
            <button type="button" class="btn" onclick="closeSemesterModal()" style="background: #e2e8f0; color: #334155; font-size: 0.85rem; font-weight: 600; padding: 0.45rem 1.25rem; border-radius: 8px; border: none; cursor: pointer;">
                ปิดหน้าต่าง
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        recalculateSummary();
    });

    function updateTargetPreview(val) {
        const text = document.getElementById('targetHelpText');
        if (val === 'graduate') {
            text.innerHTML = '<i class="fas fa-graduation-cap"></i> นักเรียนจะได้รับสถานะ "สำเร็จการศึกษา" และเก็บเข้าคลังประวัติ';
        } else {
            text.innerHTML = '<i class="fas fa-magic"></i> นักเรียนจะถูกย้ายระดับชั้นและห้องเรียนไปยัง ' + val;
        }
        recalculateSummary();
    }

    function toggleSelectAll(master) {
        const checkboxes = document.querySelectorAll('.student-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = master.checked;
            handleCheckboxChange(cb, cb.dataset.id);
        });
        recalculateSummary();
    }

    function handleCheckboxChange(cb, studentId) {
        const select = document.getElementById('action-' + studentId);
        if (!select) return;
        if (cb.checked) {
            const isGrad = '{{ str_starts_with($selectedRoom, "ม.6") ? "true" : "false" }}' === 'true';
            select.value = isGrad ? 'graduate' : 'promote';
        } else {
            select.value = 'repeat';
        }
        recalculateSummary();
    }

    function setAllActions(actionType) {
        const selects = document.querySelectorAll('.action-selector');
        const checkboxes = document.querySelectorAll('.student-checkbox');
        selects.forEach(s => s.value = actionType);
        checkboxes.forEach(cb => {
            cb.checked = (actionType === 'promote' || actionType === 'graduate');
        });
        document.getElementById('selectAllCheckbox').checked = (actionType === 'promote' || actionType === 'graduate');
        recalculateSummary();
    }

    function recalculateSummary() {
        let p = 0, g = 0, r = 0, h = 0;
        const selects = document.querySelectorAll('.action-selector');
        selects.forEach(s => {
            if (s.value === 'promote') p++;
            else if (s.value === 'graduate') g++;
            else if (s.value === 'repeat') r++;
            else if (s.value === 'hold') h++;
        });

        document.getElementById('cntPromote').innerText = p;
        document.getElementById('cntGraduate').innerText = g;
        document.getElementById('cntRepeat').innerText = r;
        document.getElementById('cntHold').innerText = h;

        // Show/hide graduate pill
        const pillGrad = document.getElementById('pillGraduate');
        const pillProm = document.getElementById('pillPromote');
        if (g > 0) {
            pillGrad.style.display = 'inline-flex';
        } else {
            pillGrad.style.display = 'none';
        }

        if (p > 0 || g === 0) {
            pillProm.style.display = 'inline-flex';
        } else {
            pillProm.style.display = 'none';
        }
    }

    function openConfirmModal() {
        const targetSelect = document.getElementById('targetRoomSelect');
        const targetText = targetSelect.options[targetSelect.selectedIndex].text;
        
        document.getElementById('modalTargetRoom').innerText = targetText;
        
        const p = parseInt(document.getElementById('cntPromote').innerText) || 0;
        const g = parseInt(document.getElementById('cntGraduate').innerText) || 0;
        document.getElementById('modalPromoteCount').innerText = (p + g) + ' คน';
        document.getElementById('modalRepeatCount').innerText = document.getElementById('cntRepeat').innerText + ' คน';
        document.getElementById('modalHoldCount').innerText = document.getElementById('cntHold').innerText + ' คน';

        const modal = document.getElementById('confirmModal');
        modal.style.display = 'flex';
    }

    function closeConfirmModal() {
        document.getElementById('confirmModal').style.display = 'none';
    }

    function submitPromotionForm() {
        document.getElementById('promotionForm').submit();
    }

    function openSemesterModal() {
        document.getElementById('semesterModal').style.display = 'flex';
    }

    function closeSemesterModal() {
        document.getElementById('semesterModal').style.display = 'none';
    }

    window.addEventListener('click', function(e) {
        const sModal = document.getElementById('semesterModal');
        if (e.target === sModal) {
            closeSemesterModal();
        }
        const cModal = document.getElementById('confirmModal');
        if (e.target === cModal) {
            closeConfirmModal();
        }
    });
</script>
@endpush
