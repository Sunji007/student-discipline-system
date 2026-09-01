@extends('layouts.app')

@section('title', 'แชทข้อความ')
@section('page-title', 'สนทนาข้อความ')

@push('styles')
<style>
    .chat-container {
        max-width: 650px;
        margin: 0 auto;
    }
    .chat-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        border: 1px solid #e2e8f0;
        overflow: hidden;
    }
    .chat-header {
        padding: 0.85rem 1.25rem;
        background: #faf8f5;
        border-bottom: 1px solid #ede8e0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .chat-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: var(--islamic-primary, #0d5c3a);
        color: var(--islamic-gold, #d97706);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
        font-weight: 700;
        flex-shrink: 0;
        box-shadow: 0 2px 6px rgba(13, 92, 58, 0.15);
    }
    .chat-body {
        padding: 1.1rem 1.25rem;
        background: #f8fafc;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        min-height: 220px;
        max-height: 480px;
        overflow-y: auto;
    }
    .message-bubble-wrapper {
        display: flex;
        flex-direction: column;
        max-width: 75%;
        width: fit-content;
    }
    .message-bubble-wrapper.sent {
        align-self: flex-end;
        align-items: flex-end;
    }
    .message-bubble-wrapper.received {
        align-self: flex-start;
        align-items: flex-start;
    }
    .message-bubble {
        padding: 0.5rem 0.85rem;
        border-radius: 12px;
        font-size: 0.86rem;
        line-height: 1.45;
        word-break: break-word;
        white-space: pre-wrap;
        position: relative;
        width: fit-content;
    }
    .message-bubble.sent {
        background: linear-gradient(135deg, #0d5c3a 0%, #064e3b 100%);
        color: #ffffff;
        border-bottom-right-radius: 2px;
        box-shadow: 0 1px 4px rgba(13, 92, 58, 0.15);
    }
    .message-bubble.received {
        background: #ffffff;
        color: #1e293b;
        border: 1px solid #e2e8f0;
        border-bottom-left-radius: 2px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    }
    .message-meta {
        font-size: 0.68rem;
        margin-top: 0.2rem;
        display: flex;
        align-items: center;
        gap: 0.35rem;
    }
    .message-meta.sent {
        color: #64748b;
    }
    .message-meta.received {
        color: #94a3b8;
    }
    .chat-footer {
        padding: 0.9rem 1.25rem;
        background: #ffffff;
        border-top: 1px solid #ede8e0;
    }
    .attachment-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.25rem 0.55rem;
        border-radius: 6px;
        font-size: 0.75rem;
        text-decoration: none;
        transition: all 0.2s ease;
        margin-top: 0.35rem;
        width: fit-content;
    }
    .attachment-badge.sent {
        background: rgba(255, 255, 255, 0.18);
        color: #fff;
        border: 1px solid rgba(255, 255, 255, 0.35);
    }
    .attachment-badge.sent:hover {
        background: rgba(255, 255, 255, 0.28);
    }
    .attachment-badge.received {
        background: #f1f5f9;
        color: #334155;
        border: 1px solid #cbd5e1;
    }
    .attachment-badge.received:hover {
        background: #e2e8f0;
    }
</style>
@endpush

@section('content')
<div class="chat-container">
    @php
        $backRoute = match(auth()->user()->Role) {
            'ฝ่ายปกครอง' => route('discipline.messages.index'),
            'ครู'         => route('teacher.messages.index'),
            'นักเรียน'    => route('student.messages.index'),
            'ผู้ปกครอง'   => route('parent.messages.index'),
            default       => '#',
        };

        $other = $otherUser ?? ($message->SenderID === auth()->user()->UserID ? $message->receiver : $message->sender);

        $otherParentStudents = collect();
        if ($other && $other->Role === 'ผู้ปกครอง') {
            $parents = \App\Models\ParentGuardian::where('UserID', $other->UserID)->get();
            $otherParentStudents = \App\Models\Student::whereIn('StudentID', $parents->pluck('StudentID')->filter())
                ->orWhereIn('ParentID', $parents->pluck('ParentID')->filter())
                ->get();
        }
    @endphp

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.75rem;">
        <a href="{{ $backRoute }}" class="btn btn-outline btn-sm" style="font-size:0.8rem; padding:0.3rem 0.65rem;">
            <i class="fas fa-arrow-left"></i> ย้อนกลับ
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:0.75rem; border-radius:8px; background:#ecfdf5; border:1px solid #10b981; color:#065f46; padding:0.55rem 0.85rem; font-size:0.82rem;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger" style="margin-bottom:0.75rem; border-radius:8px; background:#fef2f2; border:1px solid #ef4444; color:#991b1b; padding:0.55rem 0.85rem; font-size:0.82rem;">
            <ul style="margin:0; padding-left:1.2rem;">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="chat-card">
        {{-- Chat Header --}}
        <div class="chat-header">
            <div class="chat-avatar">
                {{ mb_substr($other->FullName ?? 'U', 0, 1) }}
            </div>
            <div style="flex:1;">
                <div style="font-weight:700; font-size:0.95rem; color:#0f172a; display:flex; align-items:center; gap:0.4rem; flex-wrap:wrap;">
                    {{ $other->FullName ?? 'ผู้ใช้ระบบ' }}
                    @if($other)
                        @php
                            $badgeColor = match($other->Role) {
                                'ฝ่ายปกครอง' => 'background:#fef3c7; color:#92400e; border:1px solid #fde68a;',
                                'ครู' => 'background:#dbeafe; color:#1e40af; border:1px solid #bfdbfe;',
                                'นักเรียน' => 'background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0;',
                                'ผู้ปกครอง' => 'background:#f3e8ff; color:#6b21a8; border:1px solid #e9d5ff;',
                                default => 'background:#f1f5f9; color:#475569; border:1px solid #e2e8f0;'
                            };
                        @endphp
                        <span class="badge" style="{{ $badgeColor }} font-size:0.68rem; padding:0.15rem 0.45rem; border-radius:5px;">
                            {{ $other->Role === 'ครู' ? 'ครูประจำชั้น' : $other->Role }}
                        </span>
                    @endif
                </div>

                <div style="font-size:0.75rem; color:#64748b; margin-top:0.1rem;">
                    @if($other && $other->Role === 'ผู้ปกครอง' && $otherParentStudents->count() > 0)
                        <span>ผู้ปกครองของ: <strong>{{ $otherParentStudents->pluck('FullName')->implode(', ') }}</strong></span>
                    @elseif($other && $other->Role === 'นักเรียน' && $other->student)
                        <span>รหัส: <strong>{{ $other->student->StudentID }}</strong> | ชั้น <strong>{{ $other->student->GradeLevel }}/{{ $other->student->Classroom }}</strong></span>
                    @else
                        <span>ข้อความสนทนาในระบบ</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Chat Body (Conversation Thread) --}}
        <div class="chat-body" id="chat-messages-container">
            @php
                $conversationList = $thread ?? collect([$message]);
            @endphp

            @forelse($conversationList as $msgItem)
                @php
                    $isMine = ($msgItem->SenderID === auth()->user()->UserID);
                    $msgAttachments = $msgItem->attachments;
                    $msgDate = \Carbon\Carbon::parse($msgItem->SentDate)->locale('th');
                    $formattedDate = $msgDate->isoFormat('D MMM ') . ($msgDate->year + 543) . $msgDate->isoFormat(', HH:mm น.');
                @endphp

                <div class="message-bubble-wrapper {{ $isMine ? 'sent' : 'received' }}">
                    <div class="message-bubble {{ $isMine ? 'sent' : 'received' }}">{{ trim($msgItem->Content) }}@if(!empty($msgAttachments))<div style="margin-top:0.4rem; display:flex; flex-direction:column; gap:0.25rem;">@foreach($msgAttachments as $idx => $att)<a href="{{ asset('storage/' . $att) }}" target="_blank" class="attachment-badge {{ $isMine ? 'sent' : 'received' }}"><i class="fas fa-paperclip"></i><span>ไฟล์แนบ {{ count($msgAttachments) > 1 ? ($idx + 1) : '' }}</span><i class="fas fa-external-link-alt" style="font-size:0.65rem; margin-left:auto;"></i></a>@endforeach</div>@endif</div>
                    <div class="message-meta {{ $isMine ? 'sent' : 'received' }}">
                        @if($isMine)
                            <span>{{ $formattedDate }}</span>
                            <i class="fas fa-check" style="color:var(--islamic-primary); font-size:0.65rem;"></i>
                        @else
                            <span>{{ $msgItem->sender->FullName ?? 'ผู้ส่ง' }}</span>
                            <span>•</span>
                            <span>{{ $formattedDate }}</span>
                        @endif
                    </div>
                </div>
            @empty
                <div style="text-align:center; color:#94a3b8; padding:2rem 0; font-size:0.85rem;">
                    ไม่มีข้อความในประวัติ
                </div>
            @endforelse
        </div>

        {{-- Chat Footer (Reply Form) --}}
        <div class="chat-footer">
            <div style="font-size:0.8rem; font-weight:700; color:var(--navy); margin-bottom:0.5rem; display:flex; align-items:center; gap:0.35rem;">
                <i class="fas fa-reply" style="color:var(--gold);"></i> ตอบกลับข้อความ
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

            <form method="POST" action="{{ $replyRoute }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="ReceiverID" value="{{ $other->UserID ?? ($message->SenderID !== auth()->user()->UserID ? $message->SenderID : $message->ReceiverID) }}">
                
                <div class="form-group" style="margin-bottom:0.5rem;">
                    <textarea name="Content" class="form-control" rows="2"
                              style="border-radius:8px; resize:vertical; font-size:0.85rem; padding:0.5rem 0.75rem;"
                              placeholder="พิมพ์ข้อความตอบกลับ..." required></textarea>
                </div>

                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.5rem;">
                    <div style="flex:1; min-width:200px;">
                        <input type="file" name="attachments[]" class="form-control" multiple style="font-size:0.75rem; border-radius:6px; padding:0.25rem 0.5rem;">
                        <small style="color:#64748b; font-size:0.68rem;">แนบรูปหรือเอกสารเพิ่มเติม (ขนาดไม่เกิน 10 MB/ไฟล์, หลายไฟล์ได้)</small>
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm" style="padding:0.4rem 1rem; font-size:0.82rem; border-radius:8px; display:inline-flex; align-items:center; gap:0.35rem;">
                        <i class="fas fa-paper-plane"></i> ส่ง
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const container = document.getElementById("chat-messages-container");
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    });
</script>
@endpush