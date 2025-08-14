<?php

namespace App\Notifications;

use App\Models\EmailChangeRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailChangeRequestSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public EmailChangeRequest $request) {}

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Email Change Approval Needed')
            ->line("User {$this->request->current_email} requested to change email to {$this->request->new_email}.")
            ->action('Review Request', route('admin.email-change-requests.show', $this->request->id));
    }
}
