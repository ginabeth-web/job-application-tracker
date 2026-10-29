<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ApplicationCrudTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'company' => 'Acme',
            'role' => 'Engineer',
            'url' => 'https://acme.test/jobs/1',
            'location' => 'Remote',
            'salary' => '100k',
            'status' => 'applied',
            'applied_on' => '2026-10-01',
        ], $overrides);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/applications')->assertRedirect('/login');
    }

    public function test_index_shows_only_own_applications(): void
    {
        $user = User::factory()->create();
        Application::factory()->for($user)->create(['company' => 'Acme']);
        Application::factory()->create(['company' => 'Other']);

        $this->actingAs($user)->get('/applications')->assertInertia(fn (Assert $page) => $page
            ->component('Applications/Index')
            ->has('applications', 1)
            ->where('applications.0.company', 'Acme'));
    }

    public function test_create_page_gets_status_options(): void
    {
        $this->actingAs(User::factory()->create())->get('/applications/create')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Applications/Create')
                ->has('statuses', 5)
                ->where('statuses.0.value', 'wishlist'));
    }

    public function test_store_creates_application_for_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/applications', $this->validPayload());

        $application = Application::firstOrFail();
        $response->assertRedirect(route('applications.show', $application));
        $this->assertDatabaseHas('applications', [
            'user_id' => $user->id,
            'company' => 'Acme',
            'status' => 'applied',
        ]);
    }

    public function test_store_validates_required_and_status(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/applications', ['company' => '', 'status' => 'hired'])
            ->assertSessionHasErrors(['company', 'role', 'status']);
    }

    public function test_store_appends_position_to_end_of_column(): void
    {
        $user = User::factory()->create();
        Application::factory()->for($user)->count(2)->create(['status' => 'applied']);

        $this->actingAs($user)->post('/applications', $this->validPayload());

        $this->assertDatabaseHas('applications', ['company' => 'Acme', 'position' => 2]);
    }

    public function test_show_and_edit_render_for_owner(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->for($user)->create(['company' => 'Acme']);

        $this->actingAs($user)->get(route('applications.show', $application))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Applications/Show')
                ->where('application.company', 'Acme'));
        $this->actingAs($user)->get(route('applications.edit', $application))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Applications/Edit')
                ->where('application.id', $application->id)
                ->has('statuses', 5));
    }

    public function test_update_changes_fields(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->for($user)->create();

        $this->actingAs($user)
            ->put(route('applications.update', $application), $this->validPayload(['role' => 'Staff Engineer']))
            ->assertRedirect(route('applications.show', $application));

        $this->assertDatabaseHas('applications', ['id' => $application->id, 'role' => 'Staff Engineer']);
    }

    public function test_update_with_status_change_appends_to_new_column(): void
    {
        $user = User::factory()->create();
        foreach ([0, 1] as $position) {
            $existing = Application::factory()->for($user)->create(['status' => 'offer']);
            $existing->position = $position;
            $existing->save();
        }
        $application = Application::factory()->for($user)->create(['status' => 'applied']);

        $this->actingAs($user)
            ->put(route('applications.update', $application), $this->validPayload(['status' => 'offer']))
            ->assertRedirect(route('applications.show', $application));

        $application->refresh();
        $this->assertSame('offer', $application->status->value);
        $this->assertSame(2, $application->position);
    }

    public function test_update_without_status_change_keeps_position(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->for($user)->create(['status' => 'applied']);
        $application->position = 7;
        $application->save();

        $this->actingAs($user)
            ->put(route('applications.update', $application), $this->validPayload(['role' => 'Lead']))
            ->assertRedirect(route('applications.show', $application));

        $this->assertSame(7, $application->fresh()->position);
    }

    public function test_destroy_deletes(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->for($user)->create();

        $this->actingAs($user)->delete(route('applications.destroy', $application))
            ->assertRedirect(route('applications.index'));

        $this->assertModelMissing($application);
    }

    public function test_invalid_update_by_non_owner_is_forbidden_not_422(): void
    {
        $application = Application::factory()->create(['company' => 'Original']);

        $this->actingAs(User::factory()->create())
            ->put(route('applications.update', $application), ['company' => '', 'status' => 'hired'])
            ->assertForbidden();

        $this->assertDatabaseHas('applications', ['id' => $application->id, 'company' => 'Original']);
    }

    public function test_other_users_application_is_forbidden(): void
    {
        $application = Application::factory()->create();
        $intruder = User::factory()->create();

        $this->actingAs($intruder);
        $this->get(route('applications.show', $application))->assertForbidden();
        $this->get(route('applications.edit', $application))->assertForbidden();
        $this->put(route('applications.update', $application), $this->validPayload())->assertForbidden();
        $this->delete(route('applications.destroy', $application))->assertForbidden();
        $this->assertModelExists($application);
    }
}
