<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StudentViolationEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $student_id;
    public $student_name;
    public $violation_type;
    public $timestamp;
    public $exam_id;
    public $message;
    public $classroom;

    /**
     * Create a new event instance.
     */
    public function __construct($student_id, $student_name, $violation_type, $exam_id, $message = null, $classroom = null)
    {
        $this->student_id = $student_id;
        $this->student_name = $student_name;
        $this->violation_type = $violation_type;
        $this->exam_id = $exam_id;
        $this->message = $message;
        $this->classroom = $classroom;
        $this->timestamp = now()->toIso8601String();

        \Illuminate\Support\Facades\Log::info("📢 [LOUD EVENT] StudentViolationEvent Created: {$student_name} ({$student_id}) - {$violation_type}");
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('security-monitoring'),
        ];
    }

    public function broadcastWith()
    {
        return [
            'student_id' => $this->student_id,
            'student_name' => $this->student_name,
            'violation_type' => $this->violation_type,
            'exam_id' => $this->exam_id,
            'message' => $this->message,
            'classroom' => $this->classroom,
            'timestamp' => $this->timestamp,
            // For dashboard counts or filtering
            'dashboard_key' => 'security_alert'
        ];
    }

    public function broadcastAs()
    {
        return 'student-violation';
    }
}
