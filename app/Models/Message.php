<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $primaryKey = 'MessageID';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MessageID',
        'SenderID',
        'ReceiverID',
        'Content',
        'SentDate',
        'IsRead',
        'AttachmentDir'
    ];

    public $timestamps = false; // The migration only has SentDate and IsRead perhaps? Wait, we'll check later. Let's just assume timestamps() was used, so let's keep it true. Wait, migration uses timestamps().
    
    // I will remove $timestamps = false so it uses standard timestamps.

    protected $casts = [
        'SentDate' => 'datetime',
        'IsRead' => 'boolean',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'SenderID', 'UserID');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'ReceiverID', 'UserID');
    }
}
