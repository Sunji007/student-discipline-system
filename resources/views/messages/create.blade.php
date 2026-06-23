@extends('layouts.app')

@section('title', 'เขียนข้อความ')
@section('page-title', 'เขียนข้อความใหม่')

@section('content')
<div style="max-width:680px;">
    @php
        $backRoute = match(auth()->user()->Role) {
            'ฝ่ายปกครอง' => route('discipline.messages.index'),
            'ครู'         => route('teacher.messages.index'),
            'นักเรียน'    => route('student.messages.index'),
            'ผู้ปกครอง'   => route('parent.messages.index'),
            default       => '#',
        };
        $routeMap = [
            'ฝ่ายปกครอง' => 'discipline.messages.store',
            'ครู'         => 'teacher.messages.store',
            'นักเรียน'    => 'student.messages.store',
            'ผู้ปกครอง'   => 'parent.messages.store',
        ];
        $storeRoute = $routeMap[auth()->user()->Role] ?? 'discipline.messages.store';
    @endphp
    
    <a href="{{ $backRoute }}" class="btn btn-outline btn-sm" style="margin-bottom:1rem;">
        <i class="fas fa-arrow-left"></i> กลับกล่องข้อความ
    </a>

    <div class="card">
        <div class="card-header-bar">
            <h3><i class="fas fa-pen" style="color:var(--gold); margin-right:0.5rem"></i>เขียนข้อความใหม่</h3>
        </div>
        <div class="card-body-pad">
            <form method="POST" action="{{ route($storeRoute) }}" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label class="form-label">ถึง <span style="color:var(--red)">*</span></label>
                    @php
                        $preselected = request('receiver') ? $recipients->where('UserID', request('receiver'))->first() : null;
                    @endphp
                    @if($preselected)
                        <input type="hidden" name="ReceiverID" value="{{ $preselected->UserID }}">
                        <input type="text" class="form-control" value="[{{ $preselected->Role }}] {{ $preselected->FullName }}" disabled style="background:#f1f5f9; color:#475569; font-weight:600; border-color:#cbd5e1;">
                    @else
                        <select name="ReceiverID" class="form-control" required>
                            <option value="">เลือกผู้รับ</option>
                            @foreach($recipients as $u)
                                <option value="{{ $u->UserID }}" {{ (old('ReceiverID') == $u->UserID) ? 'selected' : '' }}>
                                    [{{ $u->Role }}] {{ $u->FullName }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>
                <div class="form-group">
                    <label class="form-label">ข้อความ <span style="color:var(--red)">*</span></label>
                    <textarea name="Content" class="form-control" rows="8" required
                              placeholder="พิมพ์ข้อความที่ต้องการส่ง...">{{ old('Content') }}</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">แนบไฟล์ (ถ้ามี)</label>
                    <input type="file" name="attachment" class="form-control">
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-top:0.3rem;">ขนาดไม่เกิน 10MB</div>
                </div>
                <div style="display:flex; gap:0.75rem; margin-top:1.5rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> ส่งข้อความ
                    </button>
                    <a href="{{ $backRoute }}" class="btn btn-outline">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
