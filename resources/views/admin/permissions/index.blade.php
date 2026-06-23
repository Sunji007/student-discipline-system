@extends('layouts.app')

@section('title', 'จัดการสิทธิ์')
@section('page-title', 'จัดการสิทธิ์การใช้งาน')

@section('content')
<div class="page-header">
    <h2>ตั้งค่าสิทธิ์การเข้าถึงโมดูล</h2>
    <p>กำหนดการเข้าถึงแต่ละโมดูลสำหรับแต่ละบทบาท</p>
</div>

@php
$moduleLabels = [
    'dashboard'          => ['label' => 'แดชบอร์ด', 'icon' => 'fa-th-large'],
    'behavior-records'   => ['label' => 'บันทึกพฤติกรรม', 'icon' => 'fa-clipboard-list'],
    'behavior-rules'     => ['label' => 'กฎเกณฑ์พฤติกรรม', 'icon' => 'fa-book-open'],
    'appeals'            => ['label' => 'คำร้องโต้แย้ง', 'icon' => 'fa-balance-scale'],
    'attendance'         => ['label' => 'การเข้าแถว', 'icon' => 'fa-calendar-check'],
    'messages'           => ['label' => 'ข้อความ', 'icon' => 'fa-envelope'],
    'users'              => ['label' => 'จัดการผู้ใช้', 'icon' => 'fa-users'],
    'permissions'        => ['label' => 'จัดการสิทธิ์', 'icon' => 'fa-shield-alt'],
    'risk-students'      => ['label' => 'นักเรียนเสี่ยง', 'icon' => 'fa-exclamation-triangle'],
    'informant-reports'  => ['label' => 'รับแจ้งเบาะแส', 'icon' => 'fa-bell'],
];
@endphp

<form method="POST" action="{{ route('admin.permissions.store') }}">
    @csrf

    <div class="card">
        <div class="table-wrap">
            <table>
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
                            <span style="display:flex; align-items:center; gap:0.5rem;">
                                <i class="fas {{ $info['icon'] }}" style="color:var(--gold); width:16px; text-align:center;"></i>
                                {{ $info['label'] }}
                            </span>
                        </td>
                        @foreach($roles as $role)
                        @php
                            $canAccess = $permissions[$role][$module]->CanAccess ?? false;
                        @endphp
                        <td style="text-align:center;">
                            <label class="toggle-switch" title="{{ $canAccess ? 'เปิดใช้งาน' : 'ปิดใช้งาน' }}">
                                <input type="checkbox"
                                       name="permissions[{{ $role }}][{{ $module }}]"
                                       value="1"
                                       class="perm-checkbox"
                                       {{ $canAccess ? 'checked' : '' }}>
                                <span class="toggle-track"></span>
                            </label>
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="padding:1.25rem; border-top:1px solid #ede8e0; display:flex; align-items:center; justify-content:space-between;">
            <p style="font-size:0.8rem; color:var(--text-muted);">
                <i class="fas fa-info-circle" style="color:var(--gold);"></i>
                การเปลี่ยนแปลงสิทธิ์จะมีผลทันทีหลังบันทึก
            </p>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> บันทึกการตั้งค่าสิทธิ์
            </button>
        </div>
    </div>
</form>

@push('styles')
<style>
/* Toggle Switch */
.toggle-switch {
    position: relative;
    display: inline-flex;
    align-items: center;
    cursor: pointer;
}

.toggle-switch input { display: none; }

.toggle-track {
    width: 40px;
    height: 22px;
    background: #d8d0c0;
    border-radius: 11px;
    transition: background 0.2s;
    position: relative;
}

.toggle-track::after {
    content: '';
    position: absolute;
    top: 3px;
    left: 3px;
    width: 16px;
    height: 16px;
    background: white;
    border-radius: 50%;
    transition: transform 0.2s;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}

.toggle-switch input:checked + .toggle-track {
    background: var(--navy);
}

.toggle-switch input:checked + .toggle-track::after {
    transform: translateX(18px);
}
</style>
@endpush
@endsection