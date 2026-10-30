<?php

namespace App\Console\Commands;

use App\Models\Application;
use App\Notifications\FollowUpsDue;
use Illuminate\Console\Command;

class SendFollowUpReminders extends Command
{
    protected $signature = 'applications:send-follow-up-reminders';

    protected $description = 'Email each user a summary of their applications whose follow-up is due';

    public function handle(): int
    {
        $sent = Application::query()
            ->followUpDue()
            ->with('user')
            ->get()
            ->groupBy('user_id')
            ->each(fn ($applications) => $applications->first()->user->notify(new FollowUpsDue($applications->values())))
            ->count();

        $this->info('Sent reminders to '.$sent.' '.str('user')->plural($sent).'.');

        return self::SUCCESS;
    }
}
