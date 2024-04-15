<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    // Specify the table if it's not the plural form of the model name
    protected $table = 'messages';

    // Fillable properties ensure that mass assignment can happen safely
    protected $fillable = [
        'sender_id', 'recipient_id', 'content', 'is_delivered', 'is_read'
    ];

    // Indicates if the model should be timestamped. (created_at and updated_at columns)
    public $timestamps = true;

    // Sender relation (User who sent the message)
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // Recipient relation (User who received the message)
    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }
}
