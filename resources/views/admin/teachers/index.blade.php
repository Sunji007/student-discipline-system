@extends('layouts.app')

@section('title', 'จัดการครู')
@section('page-title', 'จัดการข้อมูลครู')

@section('content')
<div class="page-header" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem;">
    <div>
        <h2>ครูทั้งหมด</h2>
        <p>จัดการฐานข้อมูลและสิทธิ์ผู้ใช้งานครูในระบบ</p>
    </div>
    <a href="{{ route('admin.teachers.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> เพิ่มครูใหม่
    </a>
</div>

{{-- Filter --}}
<div class="card" style="margin-bottom:1rem;">
    <div class="card-body-pad">
        <form method="GET" style="display:flex; gap:0.75rem; flex-wrap:wrap; align-items:flex-end;">
            <div class="form-group" style="margin:0; flex:1; min-width:200px;">
                <label class="form-label">ค้นหา</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="ชื่อ หรือ รหัสประจำตัวครู...">
            </div>
            <div class="form-group" style="margin:0; min-width:180px;">
                <label class="form-label">กลุ่มสาระฯ / แผนก</label>
                <input type="text" name="department" class="form-control" value="{{ request('department') }}" placeholder="เช่น ภาษาไทย, วิทยาศาสตร์...">
            </div>
            <button type="submit" class="btn btn-primary" style="margin-bottom:0.05rem;">
                <i class="fas fa-search"></i> ค้นหา
            </button>
            <a href="{{ route('admin.teachers.index') }}" class="btn btn-outline" style="margin-bottom:0.05rem;">ล้าง</a>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>รหัสประจำตัวครู</th>
                    <th>ชื่อ-นามสกุล</th>
                    <th>กลุ่มสาระฯ / แผนก</th>
                    <th>ชั้นห้องที่ปรึกษา</th>
                    <th>Username บัญชีผู้ใช้</th>
                    <th style="text-align:right;">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($teachers as $t)
                <tr>
                    <td><code style="font-size:0.85rem; background:#f0ece4; padding:0.1rem 0.4rem; border-radius:2px;">{{ $t->TeacherID }}</code></td>
                    <td><strong>{{ $t->user->FullName ?? '-' }}</strong></td>
                    <td>{{ $t->Department ?? '-' }}</td>
                    <td>{{ $t->AdvisoryRoom ? 'ม.' . $t->AdvisoryRoom : '-' }}</td>
                    <td><code style="font-size:0.85rem; color:var(--text-muted);">{{ $t->user->Username ?? '-' }}</code></td>
                    <td style="text-align:right;">
                        <div style="display:flex; gap:0.35rem; justify-content:flex-end;">
                            <a href="{{ route('admin.teachers.edit', $t->TeacherID) }}" class="btn btn-outline btn-sm">
                                <i class="fas fa-pen"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.teachers.destroy', $t->TeacherID) }}"
                                  onsubmit="return confirm('ยืนยันการลบข้อมูลครู {{ addslashes($t->user->FullName ?? "") }}?')">
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
                        ไม่พบข้อมูลครู
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding:1rem 1.25rem; border-top:1px solid #ede8e0;">
        {{ $teachers->withQueryString()->links() }}
    </div>
</div>
@endsection
