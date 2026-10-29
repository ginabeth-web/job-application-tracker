<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class FollowUpsDue extends Notification
{
    /**
     * @param  Collection<int, Application>  $applications
     */
    public function __construct(public Collection $applications) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $count = $this->applications->count();

        $message = (new MailMessage)
            ->subject('You have '.$count.' '.str('follow-up')->plural($count).' due')
            ->line('These applications are ready for a follow-up:');

        foreach ($this->applications as $application) {
            $message->line("{$application->company} — {$application->role}");
        }

        return $message->action('Open your board', route('board'));
    }
}
