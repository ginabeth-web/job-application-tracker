<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\User;
use App\Notifications\FollowUpsDue;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SendFollowUpRemindersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        $this->travelTo('2026-10-29 08:00:00');
    }

    public function test_sends_one_notification_per_user_with_due_items(): void
    {
        $a = User::factory()->create();
        Application::factory()->for($a)->count(2)->create(['follow_up_on' => '2026-10-28']);

        $this->artisan('applications:send-follow-up-reminders')
            ->expectsOutput('Sent reminders to 1 user.')
            ->assertSuccessful();

        Notification::assertSentToTimes($a, FollowUpsDue::class, 1);
    }

    public function test_skips_users_with_nothing_due(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $c = User::factory()->create();
        Application::factory()->for($a)->create(['follow_up_on' => '2026-10-29']);
        Application::factory()->for($b)->create(['follow_up_on' => '2026-10-30']);
        Application::factory()->for($c)->create([
            'follow_up_on' => '2026-10-01',
            'status' => ApplicationStatus::Rejected,
        ]);

        $this->artisan('applications:send-follow-up-reminders')->assertSuccessful();

        Notification::assertSentTo($a, FollowUpsDue::class);
        Notification::assertNotSentTo($b, FollowUpsDue::class);
        Notification::assertNotSentTo($c, FollowUpsDue::class);
    }

    public function test_notification_lists_only_due_applications(): void
    {
        $a = User::factory()->create();
        Application::factory()->for($a)->count(2)->create(['follow_up_on' => '2026-10-28']);
        Application::factory()->for($a)->create(['follow_up_on' => '2026-11-15']);
        Application::factory()->for($a)->create(['follow_up_on' => null]);

        $this->artisan('applications:send-follow-up-reminders')->assertSuccessful();

        Notification::assertSentTo($a, FollowUpsDue::class, fn ($n) => $n->applications->count() === 2);
    }

    public function test_mail_subject_pluralizes_and_links_to_board(): void
    {
        $app = Application::factory()->create(['company' => 'Acme', 'role' => 'Dev']);
        $one = (new FollowUpsDue(collect([$app])))->toMail($app->user);
        $two = (new FollowUpsDue(collect([$app, $app])))->toMail($app->user);

        $this->assertSame('You have 1 follow-up due', $one->subject);
        $this->assertSame('You have 2 follow-ups due', $two->subject);
        $this->assertSame(route('board'), $one->actionUrl);
        $this->assertContains('Acme — Dev', $one->introLines);
    }

    public function test_reports_zero_users_when_nothing_due(): void
    {
        User::factory()->create();

        $this->artisan('applications:send-follow-up-reminders')
            ->expectsOutput('Sent reminders to 0 users.')
            ->assertSuccessful();
    }

    public function test_command_is_scheduled(): void
    {
        $event = collect(app(Schedule::class)->events())
            ->first(fn ($e) => str_contains($e->command, 'applications:send-follow-up-reminders'));

        $this->assertNotNull($event);
        $this->assertSame('0 8 * * *', $event->expression);
    }
}
