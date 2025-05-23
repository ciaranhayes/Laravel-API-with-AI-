<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChatHistory extends Model
{
    use HasFactory;


    protected $table = 'chat_histories';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'session_id',
        'user_message',
        'bot_response',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
