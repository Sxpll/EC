<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MessageSent implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $message;
    public $user;



    public function __construct($user, $message)
    {
        $this->user = $user;
        $this->message = $message;

        Log::info('Event MessageSent triggered', ['user' => $user, 'message' => $message]);
    }


    public function broadcastOn()
    {
        return new Channel('chat');
    }
}
