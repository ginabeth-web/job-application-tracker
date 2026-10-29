<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationMoveTest extends TestCase
{
    use RefreshDatabase;

    private function make(User $user, string $company, ApplicationStatus $status, int $position): Application
    {
        $application = Application::factory()->for($user)->create(['company' => $company, 'status' => $status]);
        $application->position = $position;
        $application->save();

        return $application;
    }

    /**
     * @return array<string, int>
     */
    private function column(User $user, ApplicationStatus $status): array
    {
        return $user->applications()->where('status', $status)->ordered()->get()
            ->mapWithKeys(fn ($a) => [$a->company => $a->position])->all();
    }

    public function test_move_into_middle_of_column(): void
    {
        $user = User::factory()->create();
        $this->make($user, 'A', ApplicationStatus::Applied, 0);
        $this->make($user, 'B', ApplicationStatus::Applied, 1);
        $this->make($user, 'C', ApplicationStatus::Applied, 2);
        $x = $this->make($user, 'X', ApplicationStatus::Wishlist, 0);

        $x->moveTo(ApplicationStatus::Applied, 1);

        $this->assertSame(['A' => 0, 'X' => 1, 'B' => 2, 'C' => 3], $this->column($user, ApplicationStatus::Applied));
        $this->assertSame(ApplicationStatus::Applied, $x->fresh()->status);
    }

    public function test_move_within_same_column(): void
    {
        $user = User::factory()->create();
        $this->make($user, 'A', ApplicationStatus::Applied, 0);
        $this->make($user, 'B', ApplicationStatus::Applied, 1);
        $c = $this->make($user, 'C', ApplicationStatus::Applied, 2);

        $c->moveTo(ApplicationStatus::Applied, 0);

        $this->assertSame(['C' => 0, 'A' => 1, 'B' => 2], $this->column($user, ApplicationStatus::Applied));
    }

    public function test_oversized_position_goes_last(): void
    {
        $user = User::factory()->create();
        $this->make($user, 'A', ApplicationStatus::Applied, 0);
        $this->make($user, 'B', ApplicationStatus::Applied, 1);
        $x = $this->make($user, 'X', ApplicationStatus::Wishlist, 0);

        $x->moveTo(ApplicationStatus::Applied, 99);

        $this->assertSame(['A' => 0, 'B' => 1, 'X' => 2], $this->column($user, ApplicationStatus::Applied));
    }

    public function test_move_ignores_other_users_rows(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $this->make($other, 'O1', ApplicationStatus::Applied, 5);
        $this->make($other, 'O2', ApplicationStatus::Applied, 9);
        $x = $this->make($user, 'X', ApplicationStatus::Wishlist, 0);

        $x->moveTo(ApplicationStatus::Applied, 0);

        $this->assertSame(['O1' => 5, 'O2' => 9], $this->column($other, ApplicationStatus::Applied));
        $this->assertSame(['X' => 0], $this->column($user, ApplicationStatus::Applied));
    }
}
