<?php

namespace App\Http\Controllers\ParentGuardian;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MessageController extends Controller
{
    public function index()
    {
        $userId = auth()->user()->UserID;
        $students = auth()->user()->parentStudents;
        $recipients = collect();

        foreach ($students as $student) {
            $teachers = $student->advisory_teachers;
            foreach ($teachers as $teacher) {
                $teacherUser = \App\Models\User::where('UserID', $teacher->UserID)
                    ->where('Status', 'ปกติ')
                    ->first();
                if ($teacherUser && !$recipients->contains('UserID', $teacherUser->UserID)) {
                    $recipients->push($teacherUser);
                }
            }
            if ($student->advisory_teacher) {
                $teacherUser = \App\Models\User::where('UserID', $student->advisory_teacher->UserID)
                    ->where('Status', 'ปกติ')
                    ->first();
                if ($teacherUser && !$recipients->contains('UserID', $teacherUser->UserID)) {
                    $recipients->push($teacherUser);
                }
            }
        }

        // Removed global staffUsers loop to limit recipients to homeroom teachers only

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
        $students = auth()->user()->parentStudents;
        $recipients = collect();

        foreach ($students as $student) {
            $teachers = $student->advisory_teachers;
            foreach ($teachers as $teacher) {
                $teacherUser = \App\Models\User::where('UserID', $teacher->UserID)
                    ->where('Status', 'ปกติ')
                    ->first();
                if ($teacherUser && !$recipients->contains('UserID', $teacherUser->UserID)) {
                    $recipients->push($teacherUser);
                }
            }
            if ($student->advisory_teacher) {
                $teacherUser = \App\Models\User::where('UserID', $student->advisory_teacher->UserID)
                    ->where('Status', 'ปกติ')
                    ->first();
                if ($teacherUser && !$recipients->contains('UserID', $teacherUser->UserID)) {
                    $recipients->push($teacherUser);
                }
            }
        }

        return view('messages.create', compact('recipients'));
    }

    public function store(Request $request)
    {
        $students = auth()->user()->parentStudents;
        $allowedTeacherUserIds = [];
        foreach ($students as $student) {
            $teachers = $student->advisory_teachers;
            foreach ($teachers as $teacher) {
                $allowedTeacherUserIds[] = $teacher->UserID;
            }
            if ($student->advisory_teacher) {
                $allowedTeacherUserIds[] = $student->advisory_teacher->UserID;
            }
        }

        $pastSenderIds = Message::where('ReceiverID', auth()->user()->UserID)
            ->pluck('SenderID')
            ->toArray();

        $allAllowedIds = array_unique(array_merge($allowedTeacherUserIds, $pastSenderIds));

        $validated = $request->validate([
            'ReceiverID'  => [
                'required',
                'exists:users,UserID',
                function ($attribute, $value, $fail) use ($allAllowedIds) {
                    if (!in_array($value, $allAllowedIds)) {
                        $fail('ไม่สามารถส่งข้อความถึงผู้ใช้นี้ได้');
                    }
                }
            ],
            'Content'       => 'required|string|min:1|max:5000',
            'attachment'    => 'nullable|file|max:10240',
            'attachments'   => 'nullable|array',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

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

        Message::create([
            'MessageID'     => (string) Str::uuid(),
            'SenderID'      => auth()->user()->UserID,
            'ReceiverID'    => $validated['ReceiverID'],
            'Content'       => $validated['Content'],
            'SentDate'      => now(),
            'IsRead'        => false,
            'AttachmentDir' => $attachmentDir,
        ]);

        return redirect()->back()->with('success', 'ส่งข้อความตอบกลับเรียบร้อยแล้ว');
    }
}
