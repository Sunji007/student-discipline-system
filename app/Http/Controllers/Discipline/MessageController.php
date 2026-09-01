<?php

namespace App\Http\Controllers\Discipline;

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

        $recipients = null;
        if (request('compose')) {
            $recipients = User::with(['student', 'parentGuardian.student'])
                ->where('UserID', '!=', $userId)
                ->where(function($q) {
                    $q->where('Status', 'ปกติ')->orWhereNull('Status')->orWhere('Status', 'active')->orWhere('Status', '');
                })
                ->orderBy('Role')
                ->orderBy('FirstName')
                ->orderBy('LastName')
                ->get();
        }

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
        // ดึง users ที่สามารถส่งหาได้ (ยกเว้นตัวเอง)
        $recipients = User::where('UserID', '!=', auth()->user()->UserID)
            ->where('Status', 'ปกติ')
            ->orderBy('Role')
            ->orderBy('FirstName')
            ->orderBy('LastName')
            ->get();

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
            $pRecord = \App\Models\ParentGuardian::where('StudentID', $receiverUser->student->StudentID)->first() ?? $receiverUser->student->parent;
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