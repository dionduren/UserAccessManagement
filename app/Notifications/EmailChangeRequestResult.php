<?php

namespace App\Notifications;

use App\Models\EmailChangeRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailChangeRequestResult extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public EmailChangeRequest $request) {}

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $msg = new MailMessage;
        if ($this->request->status === 'approved') {
            $msg->subject('Your Email Change Was Approved')
                ->line("Your email has been changed to {$this->request->new_email}.");
        } else {
            $msg->subject('Your Email Change Was Rejected')
                ->line('Your email change request was rejected.')
                ->lineIf($this->request->reason, "Reason: {$this->request->reason}");
        }
        return $msg;
    }
}
