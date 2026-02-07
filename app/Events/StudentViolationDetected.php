<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StudentViolationDetected implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $studentId;
    public $studentName;
    public $violationType;
    public $timestamp;
    public $examId;
    public $message;
    public $classroom;

    /**
     * Create a new event instance.
     */
    public function __construct($studentId, $studentName, $violationType, $examId, $message = null, $classroom = null)
    {
        $this->studentId = $studentId;
        $this->studentName = $studentName;
        $this->violationType = $violationType;
        $this->examId = $examId;
        $this->message = $message;
        $this->classroom = $classroom;
        $this->timestamp = now()->toIso8601String();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('exam-monitor'),
        ];
    }

    public function broadcastWith()
    {
        return [
            'studentId' => $this->studentId,
            'studentName' => $this->studentName,
            'violationType' => $this->violationType,
            'examId' => $this->examId,
            'message' => $this->message,
            'classroom' => $this->classroom,
            'timestamp' => $this->timestamp,
            // For dashboard counts
            'dashboard_key' => 'security_alert'
        ];
    }
}
