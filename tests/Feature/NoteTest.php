<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class NoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_add_note(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->for($user)->create();

        $this->actingAs($user)
            ->post(route('applications.notes.store', $application), ['body' => 'Recruiter called'])
            ->assertRedirect();

        $this->assertDatabaseHas('notes', ['application_id' => $application->id, 'body' => 'Recruiter called']);
    }

    public function test_note_body_required_and_max_5000(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->for($user)->create();

        $this->actingAs($user)
            ->post(route('applications.notes.store', $application), ['body' => ''])
            ->assertSessionHasErrors('body');

        $this->actingAs($user)
            ->post(route('applications.notes.store', $application), ['body' => str_repeat('a', 5001)])
            ->assertSessionHasErrors('body');

        $this->assertDatabaseCount('notes', 0);
    }

    public function test_show_page_has_notes_newest_first(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->for($user)->create();
        Note::factory()->for($application)->create(['body' => 'first']);
        Note::factory()->for($application)->create(['body' => 'second']);

        $this->actingAs($user)->get(route('applications.show', $application))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Applications/Show')
                ->has('notes', 2)
                ->where('notes.0.body', 'second')
                ->where('notes.1.body', 'first')
                ->has('notes.0.created_at')
                ->has('notes.0.created_human'));
    }

    public function test_user_can_delete_own_note(): void
    {
        $user = User::factory()->create();
        $note = Note::factory()->for(Application::factory()->for($user))->create();

        $this->actingAs($user)->delete(route('notes.destroy', $note))->assertRedirect();

        $this->assertDatabaseMissing('notes', ['id' => $note->id]);
    }

    public function test_cannot_add_or_delete_note_on_others_app(): void
    {
        $application = Application::factory()->for(User::factory()->create())->create();
        $note = Note::factory()->for($application)->create();
        $intruder = User::factory()->create();

        $this->actingAs($intruder)
            ->post(route('applications.notes.store', $application), ['body' => 'hi'])
            ->assertForbidden();

        $this->actingAs($intruder)
            ->post(route('applications.notes.store', $application), ['body' => ''])
            ->assertForbidden();

        $this->actingAs($intruder)->delete(route('notes.destroy', $note))->assertForbidden();

        $this->assertDatabaseCount('notes', 1);
    }

    public function test_deleting_application_deletes_its_notes(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->for($user)->create();
        Note::factory()->for($application)->count(2)->create();

        $this->actingAs($user)->delete(route('applications.destroy', $application));

        $this->assertDatabaseCount('notes', 0);
    }

    public function test_board_cards_include_notes_count(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->for($user)->create(['status' => 'wishlist']);
        Note::factory()->for($application)->count(2)->create();

        $this->actingAs($user)->get(route('board'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('columns.0.applications.0.notes_count', 2));
    }
}
