<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class BoardTest extends TestCase
{
    use RefreshDatabase;

    private function make(User $user, string $company, ApplicationStatus $status, int $position = 0): Application
    {
        $application = Application::factory()->for($user)->create(['company' => $company, 'status' => $status]);
        $application->position = $position;
        $application->save();

        return $application;
    }

    public function test_board_has_all_five_columns_even_when_empty(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('board'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Board')
                ->has('columns', 5)
                ->where('columns.0.status', 'wishlist')
                ->where('columns.0.label', 'Wishlist')
                ->has('columns.4.applications', 0));
    }

    public function test_board_lists_cards_in_position_order(): void
    {
        $user = User::factory()->create();
        $this->make($user, 'Alpha Corp', ApplicationStatus::Applied, 1);
        $this->make($user, 'Beta Corp', ApplicationStatus::Applied, 0);

        $this->actingAs($user)->get(route('board'))
            ->assertInertia(fn (Assert $page) => $page
                ->has('columns.1.applications', 2)
                ->where('columns.1.applications.0.company', 'Beta Corp')
                ->where('columns.1.applications.1.company', 'Alpha Corp'));
    }

    public function test_board_excludes_other_users_cards(): void
    {
        $user = User::factory()->create();
        $this->make(User::factory()->create(), 'Secret Inc', ApplicationStatus::Applied);

        $this->actingAs($user)->get(route('board'))
            ->assertInertia(fn (Assert $page) => $page->has('columns.1.applications', 0));
    }

    public function test_move_updates_status_and_position(): void
    {
        $user = User::factory()->create();
        $this->make($user, 'Existing', ApplicationStatus::Interviewing, 0);
        $card = $this->make($user, 'Mover', ApplicationStatus::Applied, 0);

        $this->actingAs($user)
            ->patch(route('applications.move', $card), ['status' => 'interviewing', 'position' => 0])
            ->assertRedirect();

        $card->refresh();
        $this->assertSame(ApplicationStatus::Interviewing, $card->status);
        $this->assertSame(0, $card->position);
    }

    public function test_move_rejects_invalid_status(): void
    {
        $user = User::factory()->create();
        $card = $this->make($user, 'Mover', ApplicationStatus::Applied);

        $this->actingAs($user)
            ->patch(route('applications.move', $card), ['status' => 'hired', 'position' => 0])
            ->assertSessionHasErrors('status');
    }

    public function test_move_rejects_negative_position(): void
    {
        $user = User::factory()->create();
        $card = $this->make($user, 'Mover', ApplicationStatus::Applied);

        $this->actingAs($user)
            ->patch(route('applications.move', $card), ['status' => 'offer', 'position' => -1])
            ->assertSessionHasErrors('position');
    }

    public function test_move_other_users_application_is_forbidden(): void
    {
        $card = $this->make(User::factory()->create(), 'Theirs', ApplicationStatus::Applied);

        $this->actingAs(User::factory()->create())
            ->patch(route('applications.move', $card), ['status' => 'offer', 'position' => 0])
            ->assertForbidden();

        $this->assertSame(ApplicationStatus::Applied, $card->fresh()->status);
    }

    public function test_move_other_users_application_with_invalid_payload_is_forbidden_not_invalid(): void
    {
        $card = $this->make(User::factory()->create(), 'Theirs', ApplicationStatus::Applied);

        $this->actingAs(User::factory()->create())
            ->patch(route('applications.move', $card), ['status' => 'hired', 'position' => -1])
            ->assertForbidden();
    }
}
