@extends('layouts.app')

@section('title', 'ข้อความ')
@section('page-title', 'รายละเอียดข้อความ')

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
    @endphp
    <a href="{{ $backRoute }}" class="btn btn-outline btn-sm" style="margin-bottom:1rem;">
        <i class="fas fa-arrow-left"></i> กลับกล่องข้อความ
    </a>

    <div class="card">
        {{-- Header --}}
        <div style="padding:1.25rem; border-bottom:1px solid #ede8e0; background:#faf8f4;">
            <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:0.5rem;">
                <div style="width:38px; height:38px; background:var(--navy); border-radius:50%; display:flex; align-items:center; justify-content:center; color:var(--gold); font-size:0.85rem; font-weight:700; flex-shrink:0;">
                    {{ mb_substr($message->sender->FullName, 0, 1) }}
                </div>
                <div>
                    <div style="font-weight:600; color:var(--navy);">{{ $message->sender->FullName }}</div>
                    <div style="font-size:0.75rem; color:var(--text-muted);">
                        <span class="badge badge-gray" style="font-size:0.65rem; margin-right:0.35rem;">{{ $message->sender->Role }}</span>
                        {{ \Carbon\Carbon::parse($message->SentDate)->locale('th')->isoFormat('D MMM YYYY, HH:mm') }}
                    </div>
                </div>
            </div>
            <div style="font-size:0.8rem; color:var(--text-muted);">
                ถึง: <strong>{{ $message->receiver->FullName }}</strong>
                <span class="badge badge-gray" style="font-size:0.65rem; margin-left:0.35rem;">{{ $message->receiver->Role }}</span>
            </div>
        </div>

        {{-- Body --}}
        <div style="padding:1.5rem; line-height:1.8; font-size:0.9rem; white-space:pre-wrap; min-height:120px;">{{ $message->Content }}</div>

        {{-- Attachment --}}
        @if($message->AttachmentDir)
        <div style="padding:0.875rem 1.25rem; border-top:1px solid #ede8e0; background:#faf8f4;">
            <a href="{{ asset('storage/' . $message->AttachmentDir) }}" target="_blank" class="btn btn-outline btn-sm">
                <i class="fas fa-paperclip"></i> ดูไฟล์แนบ
            </a>
        </div>
        @endif

        {{-- Reply --}}
        <div style="padding:1.25rem; border-top:1px solid #ede8e0;">
            <div style="font-size:0.8rem; font-weight:600; color:var(--navy); margin-bottom:0.75rem; text-transform:uppercase; letter-spacing:0.08em;">
                <i class="fas fa-reply" style="color:var(--gold); margin-right:0.35rem;"></i> ตอบกลับ
            </div>
            @php
                $replyRoute = match(auth()->user()->Role) {
                    'ฝ่ายปกครอง' => route('discipline.messages.store'),
                    'ครู'         => route('teacher.messages.store'),
                    'นักเรียน'    => route('student.messages.store'),
                    'ผู้ปกครอง'   => route('parent.messages.store'),
                    default       => '#',
                };
            @endphp
            <form method="POST" action="{{ $replyRoute }}">
                @csrf
                <input type="hidden" name="ReceiverID" value="{{ $message->SenderID !== auth()->user()->UserID ? $message->SenderID : $message->ReceiverID }}">
                <div class="form-group">
                    <textarea name="Content" class="form-control" rows="4"
                              placeholder="พิมพ์ข้อความตอบกลับ..." required></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-paper-plane"></i> ส่งข้อความตอบกลับ
                </button>
            </form>
        </div>
    </div>
</div>
@endsection