@extends('layouts.app')

@section('title', 'บันทึกพฤติกรรม')
@section('page-title', 'บันทึกพฤติกรรมทั้งหมด')

@section('content')
<div class="page-header" style="display:flex; align-items:center; justify-content:space-between;">
    <div>
        <h2>บันทึกพฤติกรรมนักเรียน</h2>
        <p>จัดการและอนุมัติบันทึกพฤติกรรม</p>
    </div>
    <a href="{{ route('discipline.behavior-records.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> บันทึก
    </a>
</div>

{{-- Filter --}}
<div class="card" style="margin-bottom:1rem;">
    <div class="card-body-pad">
        <form method="GET" style="display:flex; gap:0.75rem; flex-wrap:wrap; align-items:flex-end;">
            <div class="form-group" style="margin:0; flex:1; min-width:200px;">
                <label class="form-label">ค้นหานักเรียน</label>
                <input type="text" name="search" class="form-control"
                       value="{{ request('search') }}" placeholder="ชื่อ หรือ รหัสนักเรียน...">
            </div>
            <div class="form-group" style="margin:0; min-width:180px;">
                <label class="form-label">หมวดหมู่พฤติกรรม</label>
                <select name="category" class="form-control" onchange="this.form.submit()">
                    <option value="">ทั้งหมด</option>
                    <option value="ความผิดไม่ร้ายแรง" {{ request('category') === 'ความผิดไม่ร้ายแรง' ? 'selected' : '' }}>⚠️ ความผิดไม่ร้ายแรง (ตัดน้อยกว่า 20 คะแนน)</option>
                    <option value="ความผิดร้ายแรง" {{ request('category') === 'ความผิดร้ายแรง' ? 'selected' : '' }}>🚨 ความผิดร้ายแรง (ตัด 20 คะแนนขึ้นไป / สารเสพติด)</option>
                    <option disabled>──────────</option>
                    @php
                        $catList = (isset($categories) && count($categories) > 0)
                            ? $categories
                            : ['การแต่งกายและทรงผม','การเข้าเรียนและระเบียบสถานศึกษา','การใช้เครื่องมือสื่อสาร','ความประพฤติและกริยามารยาท','สารเสพติดและของต้องห้าม','กิจกรรมและผลงาน','ความดีและจิตอาสา','ความประพฤติดีเด่น'];
                    @endphp
                    @foreach($catList as $cat)
                        <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>📁 {{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin:0; min-width:140px;">
                <label class="form-label">สถานะ</label>
                <select name="status" class="form-control" onchange="this.form.submit()">
                    <option value="">ทั้งหมด</option>
                    @foreach(['รออนุมัติ','อนุมัติ','อยู่ในระหว่างยื่นอุทธรณ์'] as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> ค้นหา</button>
            @if(request()->hasAny(['search','status','category']))
                <a href="{{ route('discipline.behavior-records.index') }}" class="btn btn-outline">ล้าง</a>
            @endif
        </form>
    </div>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>นักเรียน</th>
                    <th>พฤติกรรม</th>
                    <th>คะแนน</th>
                    <th>บันทึกโดย</th>
                    <th>วันที่</th>
                    <th>สถานะ</th>
                    <th style="text-align:right;">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $r)
                <tr>
                    <td>
                        <strong>{{ $r->student->FullName }}</strong>
                        <div style="font-size:0.75rem; color:var(--text-muted);">{{ $r->student->Classroom }}</div>
                    </td>
                    <td style="max-width:260px;">
                        <div style="font-size:0.875rem; font-weight:500;">{{ $r->rule->RuleName ?? '-' }}</div>
                        @if($r->Description)
                            <div style="font-size:0.72rem; color:var(--text-muted);">{{ \Str::limit($r->Description, 50) }}</div>
                        @endif
                    </td>
                    <td>
                        <span style="font-weight:700; color:{{ $r->rule->RuleType === 'ตัดคะแนน' ? 'var(--red)' : 'var(--green)' }}">
                            {{ $r->rule->RuleType === 'ตัดคะแนน' ? '-' : '+' }}{{ abs($r->rule->ScoreModifier) }}
                        </span>
                    </td>
                    <td style="font-size:0.82rem;">{{ $r->recorder->FullName }}</td>
                    <td style="font-size:0.82rem; color:var(--text-muted);">
                        {{ \Carbon\Carbon::parse($r->RecordDate)->format('d/m/') . (\Carbon\Carbon::parse($r->RecordDate)->year + 543) }}
                    </td>
                    <td>
                        @php
                            $displayStatus = match($r->Status) {
                                'อนุมัติแล้ว', 'อนุมัติ' => 'อนุมัติ',
                                'อยู่ในระหว่างโต้แย้ง', 'อยู่ในระหว่างยื่นอุทธรณ์' => 'อยู่ในระหว่างยื่นอุทธรณ์',
                                default => $r->Status,
                            };
                            $sc = match($displayStatus) {
                                'รออนุมัติ' => 'badge-gold',
                                'อนุมัติ' => 'badge-green',
                                'อยู่ในระหว่างยื่นอุทธรณ์' => 'badge-orange',
                                default => 'badge-gray',
                            };
                        @endphp
                        <span class="badge {{ $sc }}">{{ $displayStatus }}</span>
                    </td>
                    <td style="text-align:right;">
                        <div style="display:flex; gap:0.35rem; justify-content:flex-end; align-items:center;">
                            <a href="{{ route('discipline.behavior-records.show', $r->RecordID) }}"
                               class="btn btn-outline btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($r->Status === 'รออนุมัติ')
                            <form method="POST" action="{{ route('discipline.behavior-records.approve', $r->RecordID) }}" style="display:inline;">
                                @csrf @method('PATCH')
                                <button type="button" class="btn btn-success btn-sm" onclick="confirmApprove(this)">
                                    <i class="fas fa-check"></i> อนุมัติ
                                </button>
                            </form>
                            @endif
                            @if($r->Status === 'ยกเลิก')
                            <form method="POST" action="{{ route('discipline.behavior-records.destroy', $r->RecordID) }}" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete(this)">
                                    <i class="fas fa-trash-alt"></i> เอาออก
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center; padding:2rem; color:var(--text-muted);">
                        ไม่พบบันทึกพฤติกรรม
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding:1rem 1.25rem; border-top:1px solid #ede8e0;">
        {{ $records->withQueryString()->links() }}
    </div>
</div>

@push('scripts')
<script>
    function confirmApprove(button) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'ยืนยันการอนุมัติ?',
                text: 'ระบบจะทำการบันทึกและปรับคะแนนพฤติกรรมของนักเรียนทันที',
                icon: 'success',
                showCancelButton: true,
                confirmButtonText: 'ตกลง',
                cancelButtonText: 'ยกเลิก',
                customClass: {
                    confirmButton: 'swal2-confirm btn-swal-success',
                    cancelButton: 'swal2-cancel btn-swal-cancel'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    button.closest('form').submit();
                }
            });
        } else {
            if (confirm('ยืนยันการอนุมัติรายการนี้?')) {
                button.closest('form').submit();
            }
        }
    }

    function confirmDelete(button) {
        Swal.fire({
            title: 'ยืนยันการเอาออกรายการนี้?',
            text: 'รายการบันทึกนี้จะถูกลบออกจากระบบอย่างถาวรและไม่สามารถย้อนกลับได้',
            icon: 'error',
            showCancelButton: true,
            confirmButtonText: 'ตกลง',
            cancelButtonText: 'ยกเลิก',
            customClass: {
                confirmButton: 'swal2-confirm btn-swal-danger',
                cancelButton: 'swal2-cancel btn-swal-cancel'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                button.closest('form').submit();
            }
        });
    }
</script>
@endpush
@endsection