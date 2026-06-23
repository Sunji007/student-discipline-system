@extends('layouts.app')

@section('title', 'ข้อความ')
@section('page-title', 'กล่องข้อความ')

@section('content')
<div class="page-header" style="display:flex; align-items:center; justify-content:space-between;">
    <div>
        <h2>กล่องข้อความ</h2>
        @if($unreadCount > 0)
            <p style="color:var(--red);">มีข้อความที่ยังไม่ได้อ่าน {{ $unreadCount }} ข้อความ</p>
        @else
            <p>ไม่มีข้อความใหม่</p>
        @endif
    </div>
    <a href="{{ url()->current() }}?compose=1" class="btn btn-primary">
        <i class="fas fa-pen"></i> เขียนข้อความใหม่
    </a>
</div>

@if(request('compose'))
{{-- Compose Form --}}
<div class="card" style="margin-bottom:1rem;">
    <div class="card-header-bar">
        <h3><i class="fas fa-pen" style="color:var(--gold); margin-right:0.5rem"></i>เขียนข้อความใหม่</h3>
    </div>
    <div class="card-body-pad">
        @php
            $routeMap = [
                'ผู้ดูแลระบบ' => 'admin.messages.store',
                'ฝ่ายปกครอง' => 'discipline.messages.store',
                'ครู'         => 'teacher.messages.store',
                'นักเรียน'    => 'student.messages.store',
                'ผู้ปกครอง'   => 'parent.messages.store',
            ];
            $storeRoute = $routeMap[auth()->user()->Role] ?? 'discipline.messages.store';
        @endphp
        <form method="POST" action="{{ route($storeRoute) }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label class="form-label">ถึง <span style="color:var(--red)">*</span></label>
                @php
                    $colId = 'UserID';
                    $colStatus = 'Status';
                    $colRole = 'Role';
                    $colName = 'FullName';
                    $allRecps = $recipients ?? \App\Models\User::where($colId, '!=', auth()->user()->UserID)->where($colStatus, 'ปกติ')->orderBy($colRole)->orderBy($colName)->get();
                    $preselected = request('receiver') ? $allRecps->where($colId, request('receiver'))->first() : null;
                @endphp
                @if($preselected)
                    <input type="hidden" name="ReceiverID" value="{{ $preselected->UserID }}">
                    <input type="text" class="form-control" value="[{{ $preselected->Role }}] {{ $preselected->FullName }}" disabled style="background:#f1f5f9; color:#475569; font-weight:600; border-color:#cbd5e1;">
                @else
                    <select name="ReceiverID" class="form-control" required>
                        <option value="">เลือกผู้รับ</option>
                        @foreach($allRecps as $u)
                            <option value="{{ $u->UserID }}">
                                [{{ $u->Role }}] {{ $u->FullName }}
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>
            <div class="form-group">
                <label class="form-label">ข้อความ <span style="color:var(--red)">*</span></label>
                <textarea name="Content" class="form-control" rows="5" required
                          placeholder="พิมพ์ข้อความที่ต้องการส่ง..."></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">แนบไฟล์ (ถ้ามี)</label>
                <input type="file" name="attachment" class="form-control">
                <div style="font-size:0.75rem; color:var(--text-muted); margin-top:0.3rem;">ขนาดไม่เกิน 10MB</div>
            </div>
            <div style="display:flex; gap:0.75rem;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> ส่งข้อความ
                </button>
                <a href="{{ url()->current() }}" class="btn btn-outline">ยกเลิก</a>
            </div>
        </form>
    </div>
</div>
@endif

<div class="responsive-grid-2">
    {{-- Inbox --}}
    <div class="card">
        <div class="card-header-bar">
            <h3><i class="fas fa-inbox" style="color:var(--gold); margin-right:0.5rem"></i>กล่องขาเข้า</h3>
            @if($unreadCount > 0)
                <span class="badge badge-red">{{ $unreadCount }} ใหม่</span>
            @endif
        </div>
        <div>
            @forelse($inbox as $msg)
            @php
                $readRoute = match(auth()->user()->Role) {
                    'ผู้ดูแลระบบ' => route('admin.messages.show', $msg->MessageID),
                    'ฝ่ายปกครอง' => route('discipline.messages.show', $msg->MessageID),
                    'ครู'         => route('teacher.messages.show', $msg->MessageID),
                    'นักเรียน'    => route('student.messages.show', $msg->MessageID),
                    'ผู้ปกครอง'   => route('parent.messages.show', $msg->MessageID),
                    default       => '#',
                };
            @endphp
            <a href="{{ $readRoute }}" style="display:block; padding:0.875rem 1.25rem; border-bottom:1px solid #f0ece4; text-decoration:none; transition:background 0.15s;
               {{ !$msg->IsRead ? 'background:#fffdf7;' : '' }}"
               class="msg-row">
                <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:0.75rem;">
                    <div style="flex:1; min-width:0;">
                        <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.25rem;">
                            @if(!$msg->IsRead)
                                <span style="width:8px; height:8px; background:var(--gold); border-radius:50%; flex-shrink:0;"></span>
                            @endif
                            <span style="font-size:0.85rem; font-weight:{{ !$msg->IsRead ? '600' : '400' }}; color:var(--navy);">
                                {{ $msg->sender->FullName }}
                            </span>
                            <span class="badge badge-gray" style="font-size:0.65rem;">{{ $msg->sender->Role }}</span>
                        </div>
                        <p style="font-size:0.8rem; color:var(--text-muted); margin:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                            {{ \Str::limit($msg->Content, 70) }}
                        </p>
                    </div>
                    <div style="font-size:0.72rem; color:var(--text-muted); white-space:nowrap; flex-shrink:0;">
                        {{ \Carbon\Carbon::parse($msg->SentDate)->diffForHumans() }}
                    </div>
                </div>
            </a>
            @empty
            <div style="text-align:center; padding:2rem; color:var(--text-muted); font-size:0.875rem;">
                <i class="fas fa-inbox" style="font-size:1.5rem; opacity:0.3; margin-bottom:0.5rem; display:block;"></i>
                ไม่มีข้อความ
            </div>
            @endforelse
        </div>
        <div style="padding:0.75rem 1.25rem; border-top:1px solid #ede8e0;">
            {{ $inbox->links() }}
        </div>
    </div>

    {{-- Sent --}}
    <div class="card">
        <div class="card-header-bar">
            <h3><i class="fas fa-paper-plane" style="color:var(--gold); margin-right:0.5rem"></i>ข้อความที่ส่ง</h3>
        </div>
        <div>
            @forelse($sent as $msg)
            <div style="padding:0.875rem 1.25rem; border-bottom:1px solid #f0ece4;">
                <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:0.75rem;">
                    <div style="flex:1; min-width:0;">
                        <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.25rem;">
                            <span style="font-size:0.78rem; color:var(--text-muted);">ถึง:</span>
                            <span style="font-size:0.85rem; font-weight:500; color:var(--navy);">
                                {{ $msg->receiver->FullName }}
                            </span>
                            <span class="badge badge-gray" style="font-size:0.65rem;">{{ $msg->receiver->Role }}</span>
                        </div>
                        <p style="font-size:0.8rem; color:var(--text-muted); margin:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                            {{ \Str::limit($msg->Content, 70) }}
                        </p>
                    </div>
                    <div style="text-align:right; flex-shrink:0;">
                        <div style="font-size:0.72rem; color:var(--text-muted);">
                            {{ \Carbon\Carbon::parse($msg->SentDate)->diffForHumans() }}
                        </div>
                        <span style="font-size:0.7rem; color:{{ $msg->IsRead ? 'var(--green)' : 'var(--text-muted)' }}">
                            <i class="fas fa-check{{ $msg->IsRead ? '-double' : '' }}"></i>
                            {{ $msg->IsRead ? 'อ่านแล้ว' : 'ยังไม่อ่าน' }}
                        </span>
                    </div>
                </div>
            </div>
            @empty
            <div style="text-align:center; padding:2rem; color:var(--text-muted); font-size:0.875rem;">
                <i class="fas fa-paper-plane" style="font-size:1.5rem; opacity:0.3; margin-bottom:0.5rem; display:block;"></i>
                ยังไม่มีข้อความที่ส่ง
            </div>
            @endforelse
        </div>
        <div style="padding:0.75rem 1.25rem; border-top:1px solid #ede8e0;">
            {{ $sent->links() }}
        </div>
    </div>
</div>

@push('styles')
<style>
.msg-row:hover { background: #faf8f4 !important; }
</style>
@endpush
@endsection