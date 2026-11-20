<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeacherAccountCreated extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public string $plainPassword) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your '.config('app.name').' teacher account')
            ->greeting('Hello '.$notifiable->first_name.',')
            ->line('A teacher account has been created for you. Use the details below to sign in.')
            ->line('**Email:** '.$notifiable->email)
            ->line('**Password:** '.$this->plainPassword)
            ->action('Sign in', route('login'))
            ->line('For your security, please change your password after your first sign-in.');
    }
}
