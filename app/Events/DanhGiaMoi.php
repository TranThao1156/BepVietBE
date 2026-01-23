<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast; // Bắt buộc
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DanhGiaMoi implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $maCongThuc;
    public $trungBinhSao;

    // Truyền vào ID công thức và Số sao trung bình mới nhất
    public function __construct($maCongThuc, $trungBinhSao)
    {
        $this->maCongThuc = $maCongThuc;
        $this->trungBinhSao = $trungBinhSao;
    }

    public function broadcastOn()
    {
        // Kênh riêng cho từng công thức: cong-thuc.{id}
        return new Channel('cong-thuc.' . $this->maCongThuc);
    }

    public function broadcastAs()
    {
        return 'rating.updated'; // Tên sự kiện để React lắng nghe
    }
}