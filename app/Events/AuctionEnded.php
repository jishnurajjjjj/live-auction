<?php

namespace App\Events;

use App\Models\Product;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AuctionEnded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    public function broadcastOn()
    {
        return new Channel('product.' . $this->product->id);
    }

    public function broadcastWith()
    {
        return [
            'product' => [
                'id' => $this->product->id,
                'is_active' => false,
                'winner' => $this->product->highestBid ? [
                    'id' => $this->product->highestBid->user->id,
                    'name' => $this->product->highestBid->user->name,
                    'amount' => $this->product->highestBid->amount,
                ] : null,
            ],
        ];
    }
}