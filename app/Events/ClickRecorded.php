<?php

// app/Events/ClickRecorded.php

namespace App\Events;

use App\Models\Click;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast when a click is recorded.
 *
 * Uses ShouldBroadcastNow (not ShouldBroadcast) because we're
 * already inside the queue worker — no need to queue the broadcast too.
 */
class ClickRecorded implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly Click $click,
    ) {}

    /**
     * The channel the event broadcasts on.
     * Private channel per owner — only that owner can subscribe.
     */
    public function broadcastOn(): Channel
    {
        $userId = $this->click->url->user_id;

        // For guest URLs, broadcast on a per-code channel
        if ($userId === null) {
            return new Channel("url.{$this->click->url->public_code}");
        }

        return new PrivateChannel("user.{$userId}");
    }

    /**
     * The name the frontend listens for.
     */
    public function broadcastAs(): string
    {
        return 'click.recorded';
    }

    /**
     * The data payload.
     */
    public function broadcastWith(): array
    {
        return [
            'click_id' => $this->click->id,
            'url_id' => $this->click->url_id,
            'public_code' => $this->click->url->public_code,
            'browser' => $this->click->browser,
            'platform' => $this->click->platform,
            'device' => $this->click->device,
            'referer' => $this->click->referer,
            'recorded_at' => $this->click->created_at?->toIso8601String(),
        ];
    }
}
