<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TeacherSystemNotification extends Notification
{
    use Queueable;

    public string $category;
    public string $title;
    public string $message;
    public array $data;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $category, string $title, string $message, array $data = [])
    {
        $this->category = $category;
        $this->title = $title;
        $this->message = $message;
        $this->data = $data;
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
            'category' => $this->category,
            'title' => $this->title,
            'message' => $this->message,
            'data' => $this->data,
        ];
    }
}