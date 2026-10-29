<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_status_is_cast_and_defaults_to_wishlist(): void
    {
        $app = User::factory()->create()->applications()->create(['company' => 'Acme', 'role' => 'Dev']);

        $this->assertSame(ApplicationStatus::Wishlist, $app->fresh()->status);
    }

    public function test_deleting_user_deletes_their_applications(): void
    {
        $user = User::factory()->create();
        Application::factory()->count(2)->for($user)->create();
        $this->assertDatabaseCount('applications', 2);

        $user->delete();

        $this->assertDatabaseCount('applications', 0);
    }
}
