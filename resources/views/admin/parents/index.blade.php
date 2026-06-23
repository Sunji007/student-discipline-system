@extends('layouts.app')

@section('title', 'ผู้ปกครองของ ' . $student->FullName)
@section('page-title', 'จัดการข้อมูลผู้ปกครอง')

@section('content')
<div class="page-header" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem; margin-bottom:1.25rem;">
    <div style="display:flex; align-items:center; gap:1rem;">
        <a href="{{ route('admin.students.index') }}" class="btn btn-outline btn-sm">
            <i class="fas fa-arrow-left"></i> ย้อนกลับ
        </a>
        <div>
            <h2 style="margin:0;">
                <i class="fas fa-users" style="color:var(--primary);"></i>
                ผู้ปกครองของ <strong>{{ $student->FullName }}</strong>
            </h2>
            <p style="margin:0.15rem 0 0; color:var(--text-muted); font-size:0.85rem;">
                รหัสนักเรียน: {{ $student->StudentID }} &nbsp;|&nbsp; ชั้น {{ $student->classroom_display }}
            </p>
        </div>
    </div>
    <a href="{{ route('admin.students.parents.create', $student->StudentID) }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> เพิ่มผู้ปกครอง
    </a>
</div>

@if(session('success'))
<div style="background:#d1fae5; border:1px solid #6ee7b7; border-radius:8px; padding:0.75rem 1rem; margin-bottom:1rem; color:#065f46; display:flex; align-items:center; gap:0.5rem;">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

@if($parents->isEmpty())
<div class="card" style="text-align:center; padding:3rem; color:var(--text-muted);">
    <i class="fas fa-user-friends" style="font-size:3rem; opacity:0.25; display:block; margin-bottom:1rem;"></i>
    <p>ยังไม่มีข้อมูลผู้ปกครองของนักเรียนคนนี้</p>
    <a href="{{ route('admin.students.parents.create', $student->StudentID) }}" class="btn btn-primary" style="margin-top:0.75rem;">
        <i class="fas fa-plus"></i> เพิ่มผู้ปกครองคนแรก
    </a>
</div>
@else
<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>ชื่อ-นามสกุล</th>
                    <th>ความสัมพันธ์</th>
                    <th>เบอร์โทรศัพท์</th>
                    <th>อีเมล</th>
                    <th>ที่อยู่</th>
                    <th style="text-align:right;">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @foreach($parents as $i => $p)
                <tr>
                    <td style="color:var(--text-muted); font-size:0.85rem;">{{ $i + 1 }}</td>
                    <td>
                        <div style="display:flex; align-items:center; gap:0.6rem;">
                            <div style="width:36px; height:36px; border-radius:50%; background:linear-gradient(135deg,#4c4bf7,#0604EA); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                <i class="fas fa-user" style="color:white; font-size:0.85rem;"></i>
                            </div>
                            <strong>{{ $p->FullName }}</strong>
                        </div>
                    </td>
                    <td>
                        <span style="background:#ede9fe; color:#5b21b6; padding:0.2rem 0.6rem; border-radius:999px; font-size:0.8rem; font-weight:600;">
                            {{ $p->Relationship }}
                        </span>
                    </td>
                    <td>
                        @if($p->Phone)
                            <a href="tel:{{ $p->Phone }}" style="color:var(--primary); text-decoration:none;">
                                <i class="fas fa-phone" style="font-size:0.8rem;"></i> {{ $p->Phone }}
                            </a>
                        @else
                            <span style="color:var(--text-muted);">-</span>
                        @endif
                    </td>
                    <td>{{ $p->Email ?? '-' }}</td>
                    <td style="max-width:200px; font-size:0.85rem; color:var(--text-muted);">{{ $p->Address ?? '-' }}</td>
                    <td style="text-align:right;">
                        <div style="display:flex; gap:0.35rem; justify-content:flex-end;">
                            <a href="{{ route('admin.students.parents.edit', [$student->StudentID, $p->ParentID]) }}"
                               class="btn btn-outline btn-sm" title="แก้ไข">
                                <i class="fas fa-pen"></i>
                            </a>
                            <form method="POST"
                                  action="{{ route('admin.students.parents.destroy', [$student->StudentID, $p->ParentID]) }}"
                                  onsubmit="return confirm('ยืนยันการลบข้อมูลผู้ปกครอง {{ $p->FullName }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" title="ลบ">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection
