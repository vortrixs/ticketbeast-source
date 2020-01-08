<?php

namespace App\Events;

use App\Concert;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConcertAdded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var FilesystemAdapter
     */
    private $storage;

    /**
     * @var Concert
     */
    private $concert;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Concert $concert, FilesystemAdapter $storage)
    {
        $this->concert = $concert;
        $this->storage = $storage;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }

    public function getConcert()
    {
        return $this->concert;
    }

    public function getStorage()
    {
        return $this->storage;
    }
}
