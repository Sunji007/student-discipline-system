@extends('layouts.app')

@section('title', 'จัดการผู้ใช้งาน')
@section('page-title', 'จัดการผู้ใช้งาน')

@section('content')
<div class="page-header" style="display:flex; align-items:center; justify-content:space-between;">
    <div>
        <h2>ผู้ใช้งานทั้งหมด</h2>
        <p>จัดการบัญชีผู้ใช้งานในระบบ</p>
    </div>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> เพิ่มผู้ใช้งาน
    </a>
</div>

{{-- Filter --}}
<div class="card" style="margin-bottom:1rem;">
    <div class="card-body-pad">
        <form method="GET" style="display:flex; gap:0.75rem; flex-wrap:wrap; align-items:flex-end;">
            <div class="form-group" style="margin:0; flex:1; min-width:200px;">
                <label class="form-label">ค้นหา</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="ชื่อ หรือ Username...">
            </div>
            <div class="form-group" style="margin:0; min-width:150px;">
                <label class="form-label">บทบาท</label>
                <select name="role" class="form-control">
                    <option value="">ทั้งหมด</option>
                    @foreach(['ผู้ดูแลระบบ','ฝ่ายปกครอง','ครู','นักเรียน','ผู้ปกครอง'] as $role)
                        <option value="{{ $role }}" {{ request('role') === $role ? 'selected' : '' }}>{{ $role }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="margin-bottom:0.05rem;">
                <i class="fas fa-search"></i> ค้นหา
            </button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline" style="margin-bottom:0.05rem;">ล้าง</a>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ชื่อ-นามสกุล</th>
                    <th>Username</th>
                    <th>บทบาท</th>
                    <th>สถานะ</th>
                    <th>ข้อมูลเพิ่มเติม</th>
                    <th style="text-align:right;">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td><strong>{{ $user->FullName }}</strong></td>
                    <td><code style="font-size:0.8rem; background:#f0ece4; padding:0.1rem 0.4rem; border-radius:2px;">{{ $user->Username }}</code></td>
                    <td>
                        @php
                            $roleColor = match($user->Role) {
                                'ผู้ดูแลระบบ' => 'navy', 'ฝ่ายปกครอง' => 'gold',
                                'ครู' => 'green', 'นักเรียน' => 'orange', default => 'gray',
                            };
                        @endphp
                        <span class="badge badge-{{ $roleColor }}">{{ $user->Role }}</span>
                    </td>
                    <td>
                        <span class="badge {{ $user->Status === 'ปกติ' ? 'badge-green' : 'badge-red' }}">
                            {{ $user->Status }}
                        </span>
                    </td>
                    <td style="color:var(--text-muted); font-size:0.82rem;">{{ $user->AdditionalInfo ?? '-' }}</td>
                    <td style="text-align:right;">
                        <div style="display:flex; gap:0.35rem; justify-content:flex-end;">
                            <a href="{{ route('admin.users.edit', $user->UserID) }}" class="btn btn-outline btn-sm">
                                <i class="fas fa-pen"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.users.destroy', $user->UserID) }}"
                                  onsubmit="return confirm('ยืนยันการลบผู้ใช้งาน {{ $user->FullName }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center; color:var(--text-muted); padding:2rem;">
                        ไม่พบผู้ใช้งาน
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding:1rem 1.25rem; border-top:1px solid #ede8e0;">
        {{ $users->withQueryString()->links() }}
    </div>
</div>
@endsection