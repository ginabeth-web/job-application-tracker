<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class FollowUpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo('2026-11-02');
    }

    private function make(User $user, ?string $followUpOn, ApplicationStatus $status = ApplicationStatus::Applied): Application
    {
        return Application::factory()->for($user)->create([
            'status' => $status,
            'follow_up_on' => $followUpOn,
        ]);
    }

    public function test_due_includes_today_and_past(): void
    {
        $user = User::factory()->create();
        $today = $this->make($user, '2026-11-02');
        $past = $this->make($user, '2026-11-01');
        $future = $this->make($user, '2026-11-03');
        $none = $this->make($user, null);

        $dueIds = Application::followUpDue()->pluck('id')->all();

        $this->assertEqualsCanonicalizing([$today->id, $past->id], $dueIds);
        $this->assertTrue($today->isFollowUpDue());
        $this->assertTrue($past->isFollowUpDue());
        $this->assertFalse($future->isFollowUpDue());
        $this->assertFalse($none->isFollowUpDue());
    }

    public function test_rejected_is_never_due(): void
    {
        $application = $this->make(User::factory()->create(), '2026-11-01', ApplicationStatus::Rejected);

        $this->assertFalse($application->isFollowUpDue());
        $this->assertSame(0, Application::followUpDue()->count());
    }

    public function test_board_props_include_due_count_and_flag(): void
    {
        $user = User::factory()->create();
        $this->make($user, '2026-11-02');
        $this->make($user, '2026-11-09');
        $this->make(User::factory()->create(), '2026-11-01');

        $this->actingAs($user)->get(route('board'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('dueCount', 1)
                ->where('columns.1.applications.0.follow_up_due', true)
                ->where('columns.1.applications.0.follow_up_on', '2026-11-02')
                ->where('columns.1.applications.1.follow_up_due', false));
    }

    public function test_show_page_includes_follow_up_fields(): void
    {
        $user = User::factory()->create();
        $application = $this->make($user, '2026-11-01');

        $this->actingAs($user)->get(route('applications.show', $application))
            ->assertInertia(fn (Assert $page) => $page
                ->where('application.follow_up_on', '2026-11-01')
                ->where('application.follow_up_due', true));
    }

    public function test_moving_to_rejected_stops_it_being_due(): void
    {
        $user = User::factory()->create();
        $application = $this->make($user, '2026-11-01');

        $this->actingAs($user)->get(route('board'))
            ->assertInertia(fn (Assert $page) => $page->where('dueCount', 1));

        $this->actingAs($user)->patch(route('applications.move', $application), [
            'status' => 'rejected',
            'position' => 0,
        ])->assertSessionHasNoErrors();

        $this->actingAs($user)->get(route('board'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('dueCount', 0)
                ->where('columns.4.applications.0.id', $application->id)
                ->where('columns.4.applications.0.follow_up_due', false));
    }

    public function test_follow_up_on_saved_from_form(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/applications', [
            'company' => 'Acme',
            'role' => 'Dev',
            'status' => 'applied',
            'follow_up_on' => '2026-11-10',
        ])->assertSessionHasNoErrors();

        $this->assertSame('2026-11-10', $user->applications()->first()->follow_up_on->format('Y-m-d'));
    }
}
