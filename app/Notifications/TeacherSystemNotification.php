<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
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
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $actionUrl = $this->data['data']['url'] ?? $this->data['url'] ?? null;
        $categoryLabel = match ($this->category) {
            'attendance' => 'Attendance',
            'grading' => 'Grading',
            'at_risk' => 'At-Risk Alert',
            'analytics' => 'Analytics & Performance',
            'import' => 'Data Import',
            default => ucfirst(str_replace('_', ' ', $this->category)),
        };

        return (new MailMessage)
            ->subject("[CIS Teacher Portal] {$this->title}")
            ->view('emails.teacher-system-notification', [
                'recipientName' => $notifiable->first_name ?? 'Teacher',
                'title' => $this->title,
                'category' => $this->category,
                'categoryLabel' => $categoryLabel,
                'notificationMessage' => $this->message,
                'actionUrl' => $actionUrl,
                'data' => $this->data,
            ]);
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