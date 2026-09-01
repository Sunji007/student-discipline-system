<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\ParentGuardian;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MessageController extends Controller
{
    /**
     * ดึงเฉพาะนักเรียนและผู้ปกครองในห้องเรียนที่ครูคนนี้เป็นครูประจำชั้น
     */
    private function getAdvisoryRecipients()
    {
        $teacher = auth()->user()->teacher;
        $rooms = $teacher?->advisory_rooms ?? [];

        if (empty($rooms)) {
            return collect();
        }

        // 1. ดึงนักเรียนในห้องที่ดูแล
        $advisoryStudents = Student::with(['user', 'parent.user'])
            ->inAdvisoryRoom($rooms)
            ->get();

        $studentUserIds = $advisoryStudents->pluck('UserID')->filter()->toArray();
        $studentIds = $advisoryStudents->pluck('StudentID')->filter()->toArray();
        $parentIds = $advisoryStudents->pluck('ParentID')->filter()->toArray();

        // 2. ดึงผู้ปกครองของนักเรียนในห้องที่ดูแล
        $parentUserIds = [];

        // จาก ParentGuardian ตาม StudentID หรือ ParentID
        $pgs = ParentGuardian::with('user')
            ->where(function($q) use ($studentIds, $parentIds) {
                $q->whereIn('StudentID', $studentIds)
                  ->orWhereIn('ParentID', $parentIds);
            })
            ->get();

        foreach ($pgs as $pg) {
            if ($pg->UserID) {
                $parentUserIds[] = $pg->UserID;
            } elseif ($pg->user) {
                $parentUserIds[] = $pg->user->UserID;
            }
        }

        // จาก relation parent ใน Student
        foreach ($advisoryStudents as $st) {
            if ($st->parent && $st->parent->UserID) {
                $parentUserIds[] = $st->parent->UserID;
            } elseif ($st->parent && $st->parent->user) {
                $parentUserIds[] = $st->parent->user->UserID;
            }
        }

        $allAllowedUserIds = array_unique(array_filter(array_merge($studentUserIds, $parentUserIds)));

        if (empty($allAllowedUserIds)) {
            return collect();
        }

        return User::with(['student', 'parentGuardian.student'])
            ->whereIn('UserID', $allAllowedUserIds)
            ->where(function ($q) {
                $q->where('Status', 'ปกติ')
                  ->orWhereNull('Status')
                  ->orWhere('Status', 'active')
                  ->orWhere('Status', '');
            })
            ->orderBy('Role')
            ->orderBy('FirstName')
            ->orderBy('LastName')
            ->get();
    }

    public function index()
    {
        $userId = auth()->user()->UserID;

        $inbox = Message::with('sender')
            ->where('ReceiverID', $userId)
            ->orderBy('SentDate', 'desc')
            ->paginate(20, ['*'], 'inbox');

        $sent = Message::with('receiver')
            ->where('SenderID', $userId)
            ->orderBy('SentDate', 'desc')
            ->paginate(20, ['*'], 'sent');

        $unreadCount = Message::where('ReceiverID', $userId)
            ->where('IsRead', false)->count();

        $recipients = $this->getAdvisoryRecipients();

        return view('messages.index', compact('inbox', 'sent', 'unreadCount', 'recipients'));
    }

    public function show(Message $message)
    {
        $userId = auth()->user()->UserID;
        abort_if(
            $message->ReceiverID !== $userId && $message->SenderID !== $userId,
            403
        );

        $otherUserId = $message->SenderID === $userId ? $message->ReceiverID : $message->SenderID;
        $otherUser = User::find($otherUserId);

        // Mark all unread messages from this sender as read
        Message::where('SenderID', $otherUserId)
            ->where('ReceiverID', $userId)
            ->where('IsRead', false)
            ->update(['IsRead' => true]);

        // Get full conversation thread
        $thread = Message::with(['sender', 'receiver'])
            ->where(function ($q) use ($userId, $otherUserId) {
                $q->where('SenderID', $userId)->where('ReceiverID', $otherUserId);
            })
            ->orWhere(function ($q) use ($userId, $otherUserId) {
                $q->where('SenderID', $otherUserId)->where('ReceiverID', $userId);
            })
            ->orderBy('SentDate', 'asc')
            ->get();

        return view('messages.show', compact('message', 'thread', 'otherUser'));
    }

    public function create()
    {
        $recipients = $this->getAdvisoryRecipients();

        return view('messages.create', compact('recipients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ReceiverID'    => 'required|exists:users,UserID',
            'Content'       => 'required|string|min:1|max:5000',
            'attachment'    => 'nullable|file|max:10240',
            'attachments'   => 'nullable|array',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        $teacher = auth()->user()->teacher;
        $rooms = $teacher?->advisory_rooms ?? [];

        // ตรวจสอบสิทธิ์: ต้องเป็นนักเรียนหรือผู้ปกครองในห้องที่ดูแล หรือเป็นคู่สนทนาที่เคยส่งข้อความมาหา
        $advisoryStudents = Student::inAdvisoryRoom($rooms)->get();
        $studentUserIds = $advisoryStudents->pluck('UserID')->filter()->toArray();
        $studentIds = $advisoryStudents->pluck('StudentID')->filter()->toArray();
        $parentIds = $advisoryStudents->pluck('ParentID')->filter()->toArray();

        $pgUserIds = ParentGuardian::where(function($q) use ($studentIds, $parentIds) {
            $q->whereIn('StudentID', $studentIds)
              ->orWhereIn('ParentID', $parentIds);
        })->pluck('UserID')->filter()->toArray();

        // รวมผู้ที่เคยส่งข้อความมาหา (กรณีตอบกลับข้อความเดิมของฝ่ายปกครองหรือผู้ดูแลระบบ)
        $pastSenderIds = Message::where('ReceiverID', auth()->user()->UserID)
            ->pluck('SenderID')
            ->filter()
            ->toArray();

        $allAllowedIds = array_unique(array_merge($studentUserIds, $pgUserIds, $pastSenderIds));

        if (!in_array($validated['ReceiverID'], $allAllowedIds)) {
            return redirect()->back()
                ->withErrors(['ReceiverID' => 'ครูประจำชั้นสามารถส่งข้อความถึงนักเรียนและผู้ปกครองในห้องเรียนที่ตนเองดูแลเท่านั้น'])
                ->withInput();
        }

        $savedFiles = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                if ($file && $file->isValid()) {
                    $savedFiles[] = $file->store('messages/attachments', 'public');
                }
            }
        } elseif ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            if ($file && $file->isValid()) {
                $savedFiles[] = $file->store('messages/attachments', 'public');
            }
        }

        $attachmentDir = !empty($savedFiles) ? json_encode($savedFiles) : null;

        $receiverUser = User::find($validated['ReceiverID']);

        Message::create([
            'MessageID'     => Str::uuid(),
            'SenderID'      => auth()->user()->UserID,
            'ReceiverID'    => $validated['ReceiverID'],
            'Content'       => $validated['Content'],
            'SentDate'      => now(),
            'IsRead'        => false,
            'AttachmentDir' => $attachmentDir,
        ]);

        // Send copy to parent if requested
        if ($request->boolean('send_to_parent') && $receiverUser && $receiverUser->Role === 'นักเรียน' && $receiverUser->student) {
            $pRecord = ParentGuardian::where('StudentID', $receiverUser->student->StudentID)->first() ?? $receiverUser->student->parent;
            if ($pRecord && $pRecord->user && $pRecord->user->UserID !== $validated['ReceiverID']) {
                Message::create([
                    'MessageID'     => Str::uuid(),
                    'SenderID'      => auth()->user()->UserID,
                    'ReceiverID'    => $pRecord->user->UserID,
                    'Content'       => $validated['Content'],
                    'SentDate'      => now(),
                    'IsRead'        => false,
                    'AttachmentDir' => $attachmentDir,
                ]);
            }
        }

        return redirect()->back()->with('success', 'ส่งข้อความเรียบร้อยแล้ว');
    }
}
