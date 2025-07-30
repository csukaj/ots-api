<?php
/**
 * User: lgabor
 * Date: 2018.06.07.
 * Time: 13:45
 */

namespace App\Events\Order;


use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BaseOrderStatusEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Site where we are
     */
    public $site;

    public $request;


    const CHANNEL_NAME = 'channel-name';

    /**
     * Create a new event instance.
     *
     * @param $request
     * @param string $site
     */
    public function __construct($request, $site = '')
    {
        $this->request = $request;
        $this->site = $site;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel(BaseOrderStatusEvent::CHANNEL_NAME);
    }
}