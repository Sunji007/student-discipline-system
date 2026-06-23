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
        $user = auth()->user();
        $parent = $user->parent;
        
        $recipients = collect(); // Default empty collection

        if ($parent) {
            // Find student child
            $student = \App\Models\Student::where('StudentID', $parent->StudentID)->first();
            if ($student) {
                // Find teacher who advises this classroom
                $teacher = $student->advisory_teacher;
                if ($teacher) {
                    $teacherUser = \App\Models\User::where('UserID', $teacher->UserID)
                        ->where('Status', 'ปกติ')
                        ->first();
                    if ($teacherUser) {
                        $recipients = collect([$teacherUser]);
                    }
                }
            }
        }

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

        if ($message->ReceiverID === $userId && !$message->IsRead) {
            $message->update(['IsRead' => true]);
        }

        return view('messages.show', compact('message'));
    }

    public function create()
    {
        $user = auth()->user();
        $parent = $user->parent;
        
        $recipients = collect(); // Default empty collection

        if ($parent) {
            // Find student child
            $student = \App\Models\Student::where('StudentID', $parent->StudentID)->first();
            if ($student) {
                // Find teacher who advises this classroom
                $teacher = $student->advisory_teacher;
                if ($teacher) {
                    $teacherUser = \App\Models\User::where('UserID', $teacher->UserID)
                        ->where('Status', 'ปกติ')
                        ->first();
                    if ($teacherUser) {
                        $recipients = collect([$teacherUser]);
                    }
                }
            }
        }

        return view('messages.create', compact('recipients'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $parent = $user->parent;
        
        // Find allowed receiver UserID
        $allowedTeacherUserId = null;
        if ($parent) {
            $student = \App\Models\Student::where('StudentID', $parent->StudentID)->first();
            if ($student) {
                $teacher = $student->advisory_teacher;
                if ($teacher) {
                    $allowedTeacherUserId = $teacher->UserID;
                }
            }
        }

        $validated = $request->validate([
            'ReceiverID'  => [
                'required',
                'exists:users,UserID',
                function ($attribute, $value, $fail) use ($allowedTeacherUserId) {
                    if ($value !== $allowedTeacherUserId) {
                        $fail('คุณสามารถส่งข้อความได้เฉพาะครูประจำชั้นของบุตรหลานเท่านั้น');
                    }
                }
            ],
            'Content'     => 'required|string|min:1|max:5000',
            'attachment'  => 'nullable|file|max:10240',
        ]);

        $attachmentDir = null;
        if ($request->hasFile('attachment')) {
            $attachmentDir = $request->file('attachment')
                ->store('messages/attachments', 'public');
        }

        Message::create([
            'MessageID'     => Str::uuid(),
            'SenderID'      => auth()->user()->UserID,
            'ReceiverID'    => $validated['ReceiverID'],
            'Content'       => $validated['Content'],
            'SentDate'      => now(),
            'IsRead'        => false,
            'AttachmentDir' => $attachmentDir,
        ]);

        return redirect()->back()->with('success', 'ส่งข้อความเรียบร้อยแล้ว');
    }
}
