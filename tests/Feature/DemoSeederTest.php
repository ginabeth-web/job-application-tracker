<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeds_demo_user_and_can_be_rerun(): void
    {
        $this->seed(DemoSeeder::class);
        $this->seed(DemoSeeder::class);

        $user = User::where('email', 'demo@example.com')->sole();

        $this->assertSame(9, $user->applications()->count());
        $this->assertSame(2, $user->applications()->followUpDue()->count());
        $this->assertDatabaseCount('notes', 4);
    }
}
