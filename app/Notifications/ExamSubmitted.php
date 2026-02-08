<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExamSubmitted extends Notification
{
    use Queueable;

    public $exam;
    public $student;

    /**
     * Create a new notification instance.
     */
    public function __construct($exam, $student)
    {
        $this->exam = $exam;
        $this->student = $student;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Ujian Selesai',
            'message' => "{$this->student->name} telah menyelesaikan {$this->exam->name}.",
            'exam_id' => $this->exam->id,
            'student_id' => $this->student->id,
            'type' => 'submission'
        ];
    }
}
