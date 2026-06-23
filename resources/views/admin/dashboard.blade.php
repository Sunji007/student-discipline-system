@extends('layouts.app')

@section('title', 'แดชบอร์ด')
@section('page-title', 'แดชบอร์ดผู้ดูแลระบบ')

@section('content')
<div class="page-header">
    <h2>ภาพรวมระบบ</h2>
    <p>ข้อมูล ณ วันที่ {{ now()->locale('th')->isoFormat('D MMMM YYYY') }}</p>
</div>

<div class="stat-grid">
    <a href="{{ route('admin.users.index') }}" class="stat-card navy" style="text-decoration:none; cursor:pointer;">
        <div class="stat-icon navy"><i class="fas fa-users"></i></div>
        <div class="stat-info">
            <div class="stat-value">{{ $stats['total_users'] }}</div>
            <div class="stat-label">ผู้ใช้งานทั้งหมด</div>
        </div>
    </a>
    <a href="{{ route('admin.students.index') }}" class="stat-card gold" style="text-decoration:none; cursor:pointer;">
        <div class="stat-icon gold"><i class="fas fa-user-graduate"></i></div>
        <div class="stat-info">
            <div class="stat-value">{{ $stats['total_students'] }}</div>
            <div class="stat-label">นักเรียนทั้งหมด</div>
        </div>
    </a>
    <a href="{{ route('admin.users.index', ['role' => 'ครู']) }}" class="stat-card green" style="text-decoration:none; cursor:pointer;">
        <div class="stat-icon green"><i class="fas fa-chalkboard-teacher"></i></div>
        <div class="stat-info">
            <div class="stat-value">{{ $stats['total_teachers'] }}</div>
            <div class="stat-label">ครูประจำชั้นทั้งหมด</div>
        </div>
    </a>
    <a href="{{ route('admin.users.index', ['role' => 'ฝ่ายปกครอง']) }}" class="stat-card red" style="text-decoration:none; cursor:pointer;">
        <div class="stat-icon red"><i class="fas fa-shield-alt"></i></div>
        <div class="stat-info">
            <div class="stat-value">{{ $stats['total_discipline'] }}</div>
            <div class="stat-label">ฝ่ายปกครองทั้งหมด</div>
        </div>
    </a>
    <a href="{{ route('admin.users.index', ['role' => 'ผู้ปกครอง']) }}" class="stat-card orange" style="text-decoration:none; cursor:pointer;">
        <div class="stat-icon orange"><i class="fas fa-user-friends"></i></div>
        <div class="stat-info">
            <div class="stat-value">{{ $stats['total_parents'] }}</div>
            <div class="stat-label">ผู้ปกครองทั้งหมด</div>
        </div>
    </a>
</div>

<div class="card">
    <div class="card-header-bar">
        <h3><i class="fas fa-users" style="color:var(--gold); margin-right:0.5rem"></i>ผู้ใช้งานล่าสุด</h3>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline btn-sm">ดูทั้งหมด</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ชื่อ-นามสกุล</th>
                    <th>ชื่อผู้ใช้</th>
                    <th>บทบาท</th>
                    <th>สถานะ</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentUsers as $user)
                <tr>
                    <td><strong>{{ $user->FullName }}</strong></td>
                    <td><code style="font-size:0.8rem; background:#f0ece4; padding:0.1rem 0.4rem; border-radius:2px;">{{ $user->Username }}</code></td>
                    <td>
                        @php
                            $roleColor = match($user->Role) {
                                'ผู้ดูแลระบบ' => 'navy',
                                'ฝ่ายปกครอง'  => 'gold',
                                'ครู'          => 'green',
                                'นักเรียน'     => 'orange',
                                'ผู้ปกครอง'    => 'gray',
                                default        => 'gray',
                            };
                        @endphp
                        <span class="badge badge-{{ $roleColor }}">{{ $user->Role }}</span>
                    </td>
                    <td>
                        <span class="badge {{ $user->Status === 'ปกติ' ? 'badge-green' : 'badge-red' }}">
                            {{ $user->Status }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('admin.users.edit', $user->UserID) }}" class="btn btn-outline btn-sm">
                            <i class="fas fa-pen"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center; color:var(--text-muted); padding:2rem;">
                        ยังไม่มีผู้ใช้งาน
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection