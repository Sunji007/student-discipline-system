@extends('layouts.app')

@section('title', 'การแสดงสิทธิ์')
@section('page-title', 'การแสดงสิทธิ์')

@section('content')
<div class="page-header">
    <h2>การแสดงสิทธิ์การเข้าถึงโมดูล</h2>
    <p>ตารางแสดงสิทธิ์การเข้าถึงแต่ละโมดูลสำหรับแต่ละบทบาทในระบบ</p>
</div>

@php
$moduleLabels = [
    'dashboard'          => ['label' => 'หน้าหลัก', 'icon' => 'fa-home'],
    'behavior-records'   => ['label' => 'บันทึกพฤติกรรม', 'icon' => 'fa-clipboard-list'],
    'behavior-rules'     => ['label' => 'เกณฑ์ประเมินพฤติกรรม', 'icon' => 'fa-book-open'],
    'appeals'            => ['label' => 'พิจารณาคำอุทธรณ์', 'icon' => 'fa-balance-scale'],
    'attendance'         => ['label' => 'การเข้าแถว', 'icon' => 'fa-calendar-check'],
    'messages'           => ['label' => 'ข้อความ', 'icon' => 'fa-envelope'],
    'users'              => ['label' => 'จัดการผู้ใช้', 'icon' => 'fa-users'],
    'permissions'        => ['label' => 'การแสดงสิทธิ์', 'icon' => 'fa-shield-alt'],
    'risk-students'      => ['label' => 'นักเรียนเสี่ยง', 'icon' => 'fa-exclamation-triangle'],
    'informant-reports'  => ['label' => 'รับแจ้งเบาะแส', 'icon' => 'fa-bell'],
];

// กำหนดสิทธิ์ในการเขียนข้อมูล (ปากกา) หรือดูอย่างเดียว (ตา)
$writeMap = [
    'dashboard'          => [],
    'behavior-records'   => ['ผู้ดูแลระบบ', 'ฝ่ายปกครอง', 'ครู'],
    'behavior-rules'     => ['ผู้ดูแลระบบ', 'ฝ่ายปกครอง'],
    'appeals'            => ['ผู้ดูแลระบบ', 'ฝ่ายปกครอง', 'นักเรียน'], // ฝ่ายปกครองอนุมัติ/จัดการ นักเรียนยื่นคำร้อง
    'attendance'         => ['ผู้ดูแลระบบ', 'ฝ่ายปกครอง', 'ครู'],
    'messages'           => ['ผู้ดูแลระบบ', 'ฝ่ายปกครอง', 'ครู'],
    'users'              => ['ผู้ดูแลระบบ'],
    'permissions'        => [], // ดูได้อย่างเดียว
    'risk-students'      => ['ผู้ดูแลระบบ', 'ฝ่ายปกครอง'],
    'informant-reports'  => ['ผู้ดูแลระบบ', 'ฝ่ายปกครอง', 'นักเรียน'], // นักเรียนแจ้งเบาะแส ฝ่ายปกครองจัดการ
];
@endphp

    <div class="card">
        <div class="table-wrap">
            <table class="matrix-table">
                <thead>
                    <tr>
                        <th style="min-width:180px;">โมดูล</th>
                        @foreach($roles as $role)
                        <th style="text-align:center; min-width:110px;">
                            @php
                                $rc = match($role) {
                                    'ผู้ดูแลระบบ' => 'navy', 'ฝ่ายปกครอง' => 'gold',
                                    'ครู' => 'green', 'นักเรียน' => 'orange', default => 'gray',
                                };
                            @endphp
                            <span class="badge badge-{{ $rc }}">{{ $role }}</span>
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($modules as $module)
                    @php $info = $moduleLabels[$module] ?? ['label' => $module, 'icon' => 'fa-circle']; @endphp
                    <tr>
                        <td>
                            <span style="display:flex; align-items:center; gap:0.5rem; font-weight: 500;">
                                <i class="fas {{ $info['icon'] }}" style="color:var(--gold); width:16px; text-align:center;"></i>
                                {{ $info['label'] }}
                            </span>
                        </td>
                        @foreach($roles as $role)
                        @php
                            $canAccess = $permissions[$role][$module]->CanAccess ?? false;
                            $canWrite = $canAccess && in_array($role, $writeMap[$module] ?? []);
                        @endphp
                        <td style="text-align:center;">
                            @if($canAccess)
                                @if($canWrite)
                                    <div class="status-indicator status-allowed status-write" title="บทบาท {{ $role }} สามารถบันทึก/จัดการโมดูล {{ $info['label'] }} ได้">
                                        <i class="fas fa-pencil-alt"></i>
                                    </div>
                                @else
                                    <div class="status-indicator status-allowed status-read" title="บทบาท {{ $role }} สามารถอ่าน/ดูข้อมูลโมดูล {{ $info['label'] }} ได้เท่านั้น">
                                        <i class="fas fa-eye"></i>
                                    </div>
                                @endif
                            @else
                                <div class="status-indicator status-denied" title="ไม่อนุญาตให้บทบาท {{ $role }} เข้าถึงโมดูล {{ $info['label'] }}">
                                    <i class="fas fa-times"></i>
                                </div>
                            @endif
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="padding:1.25rem; border-top:1px solid #ede8e0; background-color: #faf9f6; display: flex; flex-direction: column; gap: 0.75rem;">
            <div style="display: flex; gap: 2rem; flex-wrap: wrap; font-size: 0.85rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div class="status-indicator status-allowed status-write" style="width: 24px; height: 24px; cursor: default;">
                        <i class="fas fa-pencil-alt" style="font-size: 0.75rem;"></i>
                    </div>
                    <span style="font-weight: 500; color: var(--text-dark);">บันทึก / จัดการได้ (Write & Manage)</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div class="status-indicator status-allowed status-read" style="width: 24px; height: 24px; cursor: default;">
                        <i class="fas fa-eye" style="font-size: 0.75rem;"></i>
                    </div>
                    <span style="font-weight: 500; color: var(--text-dark);">ดูได้อย่างเดียว (Read-Only)</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div class="status-indicator status-denied" style="width: 24px; height: 24px; cursor: default;">
                        <i class="fas fa-times" style="font-size: 0.75rem;"></i>
                    </div>
                    <span style="font-weight: 500; color: var(--text-muted);">ไม่มีสิทธิ์เข้าถึง (No Access)</span>
                </div>
            </div>
            <p style="font-size:0.8rem; color:var(--text-muted); margin: 0; display: flex; align-items: center; gap: 0.5rem; border-top: 1px solid #f1ece4; padding-top: 0.75rem;">
                <i class="fas fa-info-circle" style="color:var(--primary); font-size: 1rem;"></i>
                ตารางแสดงรายละเอียดสิทธิ์การเข้าถึงและการจัดการข้อมูลของแต่ละบทบาทในระบบเพื่อตรวจสอบความถูกต้องของโครงสร้างระบบสิทธิ์
            </p>
        </div>
    </div>

@push('styles')
<style>
/* Matrix Table & Row Hover */
.matrix-table tbody tr {
    transition: background-color 0.15s ease;
}
.matrix-table tbody tr:hover {
    background-color: rgba(6, 4, 234, 0.02) !important;
}

/* Status Indicators */
.status-indicator {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    font-size: 0.8rem;
    transition: all 0.2s ease;
}

.status-indicator.status-write {
    background-color: #ecfdf5;
    color: #10b981;
    border: 1px solid #a7f3d0;
}

.status-indicator.status-read {
    background-color: #eff6ff;
    color: #3b82f6;
    border: 1px solid #bfdbfe;
}

.status-indicator.status-denied {
    background-color: #f8fafc;
    color: #cbd5e1;
    border: 1px solid #e2e8f0;
}

.status-indicator:hover {
    transform: scale(1.1);
}
</style>
@endpush
@endsection