<?php

namespace App\Events;

use App\Models\Bid;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BidPlaced implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $bid;
    public $product;
    public $timeExtended;

    public function __construct(Bid $bid, bool $timeExtended = false)
    {
        $this->bid = $bid;
        $this->product = $bid->product;
        $this->timeExtended = $timeExtended;
    }

    public function broadcastOn()
    {
        return new Channel('product.' . $this->bid->product_id);
    }

    public function broadcastWith()
    {
        return [
            'bid' => [
                'id' => $this->bid->id,
                'amount' => $this->bid->amount,
                'created_at' => $this->bid->created_at->toDateTimeString(),
                'user' => [
                    'id' => $this->bid->user->id,
                    'name' => $this->bid->user->name,
                ],
            ],
            'product' => [
                'id' => $this->product->id,
                'current_price' => $this->product->current_price,
                'auction_end_time' => $this->product->auction_end_time->toDateTimeString(),
            ],
             'time_extended' => $this->timeExtended,
        ];
    }
}