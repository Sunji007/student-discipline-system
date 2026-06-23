@extends('layouts.app')

@section('title', 'จัดการนักเรียน')
@section('page-title', 'จัดการข้อมูลนักเรียน')

@section('content')
<div class="page-header" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem;">
    <div>
        <h2>นักเรียนทั้งหมด</h2>
        <p>จัดการฐานข้อมูลนักเรียนและพิมพ์บัตรนักเรียน</p>
    </div>
    <a href="{{ route('admin.students.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> เพิ่มนักเรียนใหม่
    </a>
</div>

{{-- Filter --}}
<div class="card" style="margin-bottom:1rem;">
    <div class="card-body-pad">
        <form method="GET" style="display:flex; gap:0.75rem; flex-wrap:wrap; align-items:flex-end;">
            <div class="form-group" style="margin:0; flex:1; min-width:200px;">
                <label class="form-label">ค้นหา</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="ชื่อ หรือ รหัสนักเรียน...">
            </div>
            <div class="form-group" style="margin:0; min-width:150px;">
                <label class="form-label">ระดับชั้น</label>
                <select name="grade" class="form-control">
                    <option value="">ทั้งหมด</option>
                    @foreach(['ม.1','ม.2','ม.3','ม.4','ม.5','ม.6'] as $g)
                        <option value="{{ $g }}" {{ request('grade') === $g ? 'selected' : '' }}>{{ $g }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="margin-bottom:0.05rem;">
                <i class="fas fa-search"></i> ค้นหา
            </button>
            <a href="{{ route('admin.students.index') }}" class="btn btn-outline" style="margin-bottom:0.05rem;">ล้าง</a>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th style="width: 70px;">รูปถ่าย</th>
                    <th>รหัสนักเรียน</th>
                    <th>ชื่อ-นามสกุล</th>
                    <th>เพศ</th>
                    <th>ระดับชั้น / ห้อง</th>
                    <th>คะแนนความประพฤติ</th>
                    <th style="text-align:right;">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $s)
                <tr>
                    <td>
                        <div style="width: 48px; height: 48px; border-radius: 50%; overflow: hidden; background: #e5e7eb; display: flex; align-items: center; justify-content: center;">
                            @if($s->Photo)
                                <img src="{{ asset('storage/' . $s->Photo) }}" alt="{{ $s->FullName }}" style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                                <i class="fas fa-user-graduate" style="color: #9ca3af; font-size: 1.25rem;"></i>
                            @endif
                        </div>
                    </td>
                    <td><code style="font-size:0.85rem; background:#f0ece4; padding:0.1rem 0.4rem; border-radius:2px;">{{ $s->StudentID }}</code></td>
                    <td><strong>{{ $s->FullName }}</strong></td>
                    <td>{{ $s->Gender ?? '-' }}</td>
                    <td>{{ $s->classroom_display }}</td>
                    <td>
                        <span class="badge {{ $s->BehaviorScore >= 80 ? 'badge-green' : ($s->BehaviorScore >= 60 ? 'badge-orange' : 'badge-red') }}">
                            {{ $s->BehaviorScore }} คะแนน
                        </span>
                    </td>
                    <td style="text-align:right;">
                        <div style="display:flex; gap:0.35rem; justify-content:flex-end;">
                            <a href="{{ route('admin.students.card', $s->StudentID) }}" class="btn btn-gold btn-sm" title="พิมพ์บัตรนักเรียน" target="_blank">
                                <i class="fas fa-id-card"></i> บัตร
                            </a>
                            <a href="{{ route('admin.students.parents.index', $s->StudentID) }}"
                               class="btn btn-sm"
                               style="background:#ede9fe; color:#5b21b6; border:1px solid #c4b5fd;"
                               title="จัดการผู้ปกครอง">
                                <i class="fas fa-users"></i> ผู้ปกครอง
                            </a>
                            <a href="{{ route('admin.students.edit', $s->StudentID) }}" class="btn btn-outline btn-sm">
                                <i class="fas fa-pen"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.students.destroy', $s->StudentID) }}"
                                  onsubmit="return confirm('ยืนยันการลบนักเรียน {{ $s->FullName }}?')">
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
                    <td colspan="7" style="text-align:center; color:var(--text-muted); padding:2rem;">
                        ไม่พบข้อมูลนักเรียน
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding:1rem 1.25rem; border-top:1px solid #ede8e0;">
        {{ $students->withQueryString()->links() }}
    </div>
</div>
@endsection
