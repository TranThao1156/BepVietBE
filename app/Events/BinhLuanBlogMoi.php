<?php

namespace App\Events;

use App\Models\BinhLuan;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast; //  Bắt buộc có
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BinhLuanBlogMoi implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $binhLuan;

    public function __construct($binhLuan)
    {
        $this->binhLuan = $binhLuan;
    }

    public function broadcastOn()
    {
        // Phát vào kênh riêng của bài Blog: blog.{id}
        return new Channel('blog.' . $this->binhLuan->Ma_Blog);
    }

    public function broadcastAs()
    {
        return 'comment.created'; // Tên sự kiện để React lắng nghe
    }
}