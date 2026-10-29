# Job Application Tracker Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** A personal job-application tracker: a Kanban board of applications by status, with notes per application and follow-up reminders.

**Architecture:** Laravel 12 backend with a Vue 3 frontend wired together by Inertia.js (no separate API: controllers return `Inertia::render('Page', props)`, Vue pages submit with Inertia's `useForm` / `router`). SQLite database. Each user owns their applications; a policy enforces ownership. API Resources shape every prop sent to Vue. The board uses `vue-draggable-plus`; a drop calls one `PATCH move` route. Reminders are a date column, a "due" query scope, and a daily scheduled command that emails a digest.

**Tech Stack:** PHP 8.2+, Laravel 12.70, Laravel Breeze (Vue + Inertia + TypeScript stack, includes Ziggy's `route()` in Vue), Vue 3, TypeScript, Tailwind, Vite 7, vue-draggable-plus, SQLite, PHPUnit.

**Spec:** No separate design doc. The brief this plan implements:
> Kanban board with statuses, follow-up reminders and notes for each company. A clean CRUD core plus extras added one at a time. Each feature branch gets a review before it's merged.
> Frontend: Vue.js (user requirement).

## Global Constraints

- Work only inside `C:\Users\gnbda\job-application-tracker`. Its parent (`C:\Users\gnbda`) is currently a git repo; the project must get its **own** repo (Task 0) and nothing may be committed to the home-directory repo.
- Default branch: `main`. One branch per phase, named as in each phase heading. Every phase ends with: `php artisan test` green, `npm run build` green (includes `vue-tsc` type-check), `vendor/bin/pint` clean, a code review (`/code-review` or superpowers:requesting-code-review), then `git merge --no-ff` into `main`.
- Frontend: Vue 3 `<script setup lang="ts">` single-file components only, Composition API, no Options API, no Vuex/Pinia (Inertia props are the state). Pages live in `resources/js/Pages/`, shared components in `resources/js/Components/`, shared TS types in `resources/js/types/index.d.ts`.
- Every model sent to Vue goes through an API Resource; dates are serialized as `Y-m-d` strings (or `null`).
- Database: SQLite (`database/database.sqlite`); tests use in-memory SQLite (Laravel default in `phpunit.xml`).
- Statuses, in board order, exactly: `wishlist`, `applied`, `interviewing`, `offer`, `rejected`. Labels: Wishlist, Applied, Interviewing, Offer, Rejected.
- `rejected` is the only closed status; reminders never fire for closed applications.
- Mail is `MAIL_MAILER=log` locally; no real mail provider in scope.
- The `PHP Warning: Unable to load dynamic library 'pdo_firebird'/'pdo_oci'` lines come from XAMPP's `php.ini` and are harmless; ignore them in command output.

## Review Focus

1. **Another user's application by URL** (show/edit/update/delete/move/notes) → must return 403, never data. Tested in Tasks 2, 4, 5.
2. **Move with an invalid status or negative/oversized position** → validation error for bad status/negative position; oversized position clamped to the column length. Tested in Tasks 3, 4.
3. **Follow-up date of today** → counts as due (inclusive), and an application moved to `rejected` stops being due. Tested in Task 6.
4. **Deleting an application with notes** → notes are deleted with it (FK cascade), no orphan rows. Tested in Task 5.
5. **Empty board / empty column** → board gets all five columns as props, each with an empty list; the Vue column shows an empty state. Tested in Task 4.

---

## Phase 0 — branch `main`

### Task 0: Repo, Vue/Inertia + auth scaffolding, baseline

**Files:**
- Create: `.git/` (new repo); by Breeze: auth controllers, `app/Http/Middleware/HandleInertiaRequests.php`, `resources/js/{app.ts,Pages,Components,Layouts,types}`, `tests/Feature/Auth/*`
- Modify: `.env` / `.env.example` (`APP_NAME="Job Tracker"`), `composer.json`, `package.json`

- [ ] **Step 1: Create the project's own repo**

Run: `git init -b main` in the project folder, then `git rev-parse --show-toplevel`
Expected: `C:/Users/gnbda/job-application-tracker`

- [ ] **Step 2: Confirm `.env`, `vendor/`, `node_modules/`, `database/database.sqlite` are ignored**

Run: `git status --short`
Expected: none of those paths listed (add `database/database.sqlite` to `.gitignore` if it shows).

- [ ] **Step 3: Install Breeze with Vue + Inertia + TypeScript**

Run: `composer require laravel/breeze --dev`, `php artisan breeze:install vue --typescript --no-interaction`, `npm install`, `npm run build`, `php artisan migrate`
Expected: `resources/js/app.ts` and `resources/js/Pages/Dashboard.vue` exist; build passes.

- [ ] **Step 4: Run the suite**

Run: `php artisan test`
Expected: all Breeze auth tests PASS.

- [ ] **Step 5: Commit baseline**

```bash
git add -A
git commit -m "chore: Laravel 12 + Inertia/Vue (Breeze) skeleton and implementation plan"
```

---

## Phase 1 — branch `feature/applications-crud`

### Task 1: Status enum, Application model, migration, factory

**Files:**
- Create: `app/Enums/ApplicationStatus.php`, `app/Models/Application.php`, `database/migrations/xxxx_create_applications_table.php`, `database/factories/ApplicationFactory.php`
- Modify: `app/Models/User.php` (add relation)
- Test: `tests/Unit/ApplicationStatusTest.php`, `tests/Feature/ApplicationModelTest.php`

**Interfaces:**
- Produces:
  - `enum ApplicationStatus: string { Wishlist='wishlist'; Applied='applied'; Interviewing='interviewing'; Offer='offer'; Rejected='rejected'; }` with `label(): string`, `isClosed(): bool` (true only for `Rejected`), and `static options(): array` → `[['value' => 'wishlist', 'label' => 'Wishlist'], ...]` in case order. Case order = board order.
  - Table `applications`: `id`, `user_id` (FK, cascade delete), `company` string(255), `role` string(255), `url` string nullable, `location` string nullable, `salary` string nullable, `status` string default `wishlist`, `position` unsigned int default 0, `applied_on` date nullable, `timestamps`. Index `(user_id, status, position)`.
  - `Application` model: fillable all of the above except `user_id`/`position`; casts `status => ApplicationStatus::class`, `applied_on => 'date'`; `user(): BelongsTo`.
  - `User::applications(): HasMany`.
  - `ApplicationFactory` default: fake company/jobTitle, random status.

- [ ] **Step 1: Write failing tests**

```php
// tests/Unit/ApplicationStatusTest.php
public function test_cases_are_in_board_order(): void
{
    $this->assertSame(['wishlist','applied','interviewing','offer','rejected'],
        array_column(ApplicationStatus::cases(), 'value'));
}
public function test_only_rejected_is_closed_and_labels(): void
{
    $this->assertTrue(ApplicationStatus::Rejected->isClosed());
    $this->assertFalse(ApplicationStatus::Offer->isClosed());
    $this->assertSame(['value' => 'interviewing', 'label' => 'Interviewing'], ApplicationStatus::options()[2]);
}

// tests/Feature/ApplicationModelTest.php (RefreshDatabase)
public function test_status_is_cast_and_defaults_to_wishlist(): void
{
    $app = User::factory()->create()->applications()->create(['company' => 'Acme', 'role' => 'Dev']);
    $this->assertSame(ApplicationStatus::Wishlist, $app->fresh()->status);
}
public function test_deleting_user_deletes_their_applications(): void { /* user + 2 apps, delete user, assertDatabaseCount('applications', 0) */ }
```

- [ ] **Step 2: Run, expect FAIL** — `php artisan test --filter=ApplicationStatus` and `--filter=ApplicationModel` (class not found).
- [ ] **Step 3: Implement** enum, migration (`php artisan make:model Application -mf`), relation.
- [ ] **Step 4: Run, expect PASS** — same commands.
- [ ] **Step 5: Commit** — `git commit -m "feat: add Application model and status enum"`

### Task 2: CRUD — policy, request, resource, controller, Vue pages

**Files:**
- Create: `app/Policies/ApplicationPolicy.php`, `app/Http/Requests/ApplicationRequest.php`, `app/Http/Resources/ApplicationResource.php`, `app/Http/Controllers/ApplicationController.php`, `resources/js/Pages/Applications/{Index,Create,Edit,Show}.vue`, `resources/js/Components/ApplicationForm.vue`
- Modify: `routes/web.php`, `resources/js/types/index.d.ts`, `resources/js/Layouts/AuthenticatedLayout.vue` (add "Applications" nav link)
- Test: `tests/Feature/ApplicationCrudTest.php`

**Interfaces:**
- Consumes: Task 1 model/enum.
- Produces:
  - Routes (inside `auth` middleware): `Route::resource('applications', ApplicationController::class)` → `applications.index|create|store|show|edit|update|destroy`.
  - `ApplicationPolicy::view|update|delete(User, Application): bool` → `$user->id === $application->user_id`. Controller uses `Gate::authorize(...)`.
  - `ApplicationRequest` rules: `company` required string max:255; `role` required string max:255; `url` nullable url max:255; `location`, `salary` nullable string max:255; `status` required `Rule::enum(ApplicationStatus::class)`; `applied_on` nullable date.
  - `ApplicationResource` → `{id, company, role, url, location, salary, status, status_label, position, applied_on}` (`applied_on` as `Y-m-d|null`). Later tasks add fields here.
  - TS types: `type ApplicationStatus = 'wishlist'|'applied'|'interviewing'|'offer'|'rejected'`; `interface StatusOption { value: ApplicationStatus; label: string }`; `interface Application { id: number; company: string; role: string; url: string|null; location: string|null; salary: string|null; status: ApplicationStatus; status_label: string; position: number; applied_on: string|null }`.
  - Inertia pages and props: `Applications/Index` `{applications: Application[]}` (own only, newest first); `Applications/Create` `{statuses: StatusOption[]}`; `Applications/Edit` `{application: Application, statuses: StatusOption[]}`; `Applications/Show` `{application: Application}`.
  - `ApplicationForm.vue` props `{ application?: Application; statuses: StatusOption[] }`; uses `useForm`, posts to `route('applications.store')` or puts to `route('applications.update', id)`; shows `form.errors` under each field (Breeze `InputError`).
  - `store` sets `position` to the user's current count in that status (append to bottom) and redirects to `applications.show`. `destroy` redirects to `applications.index`.

- [ ] **Step 1: Write failing tests** (`RefreshDatabase`, acting as a factory user; `use Inertia\Testing\AssertableInertia as Assert;`)

```php
test_guest_is_redirected_to_login()              // get /applications → assertRedirect('/login')
test_index_shows_only_own_applications()          // assertInertia(fn (Assert $p) => $p->component('Applications/Index')->has('applications', 1)->where('applications.0.company', 'Acme'))
test_create_page_gets_status_options()            // ->has('statuses', 5)->where('statuses.0.value', 'wishlist')
test_store_creates_application_for_user()         // post valid → assertRedirect; assertDatabaseHas user_id, status 'applied'
test_store_validates_required_and_status()        // post ['company'=>'', 'status'=>'hired'] → assertSessionHasErrors(['company','role','status'])
test_store_appends_position_to_end_of_column()    // 2 existing 'applied' → new one position 2
test_update_changes_fields()                      // put → assertDatabaseHas new role
test_destroy_deletes()                            // delete → assertModelMissing
test_other_users_application_is_forbidden()       // get show/edit, put, delete on other user's app → each assertForbidden()
```

- [ ] **Step 2: Run, expect FAIL** — `php artisan test --filter=ApplicationCrud`
- [ ] **Step 3: Implement** policy, request, resource, controller, routes, types, pages (wrap in `AuthenticatedLayout`, reuse Breeze `TextInput`, `InputLabel`, `InputError`, `PrimaryButton`, `DangerButton`).
- [ ] **Step 4: Verify** — `php artisan test` PASS and `npm run build` PASS.
- [ ] **Step 5: Commit** — `git commit -m "feat: application CRUD with Vue pages and ownership policy"`

### Phase 1 close

- [ ] Pint, `php artisan test`, `npm run build` all clean
- [ ] Code review of `feature/applications-crud` vs `main`; fix findings; re-run
- [ ] `git checkout main && git merge --no-ff feature/applications-crud`

---

## Phase 2 — branch `feature/kanban-board`

### Task 3: Ordering logic (`moveTo`)

**Files:**
- Modify: `app/Models/Application.php`
- Test: `tests/Feature/ApplicationMoveTest.php`

**Interfaces:**
- Produces: `Application::moveTo(ApplicationStatus $status, int $position): void` — in a DB transaction, takes the owner's other applications in `$status` ordered by `position`, inserts `$this` at `min($position, count)`, and rewrites `position` 0..n for that column. The old column is left as-is (gaps are fine; only relative order matters). Also `scopeOrdered(Builder $q)` → `orderBy('position')->orderBy('id')`.

- [ ] **Step 1: Write failing tests**

```php
test_move_into_middle_of_column()     // applied: A(0) B(1) C(2); move X (wishlist) to applied@1 → A, X, B, C; positions 0..3
test_move_within_same_column()        // A,B,C → move C to 0 → C,A,B
test_oversized_position_goes_last()   // position 99 → last
test_move_ignores_other_users_rows()  // other user's applied rows keep their positions
```

- [ ] **Step 2: Run, expect FAIL** — `php artisan test --filter=ApplicationMove`
- [ ] **Step 3: Implement** `moveTo` and `scopeOrdered`.
- [ ] **Step 4: Run, expect PASS**
- [ ] **Step 5: Commit** — `git commit -m "feat: ordered move of applications between statuses"`

### Task 4: Board page and move route

**Files:**
- Create: `app/Http/Controllers/BoardController.php`, `app/Http/Controllers/MoveApplicationController.php` (invokable), `resources/js/Pages/Board.vue`, `resources/js/Components/BoardColumn.vue`, `resources/js/Components/ApplicationCard.vue`
- Modify: `routes/web.php` (Breeze's `dashboard` route redirects to `board`), `AuthenticatedLayout.vue` (nav "Board"), `resources/js/types/index.d.ts`, `package.json` (`npm install vue-draggable-plus`)
- Test: `tests/Feature/BoardTest.php`

**Interfaces:**
- Consumes: `Application::moveTo`, `scopeOrdered`, `ApplicationPolicy::update`, `ApplicationResource`, `ApplicationStatus::options()`.
- Produces:
  - `GET /board` → name `board` → `Inertia::render('Board', ['columns' => ...])` where `columns` is a list of `{status: ApplicationStatus, label: string, applications: Application[]}`, all five in board order even if empty.
  - TS: `interface BoardColumn { status: ApplicationStatus; label: string; applications: Application[] }`.
  - `PATCH /applications/{application}/move` → name `applications.move`; body `{status: string, position: int}`; validates `status` `Rule::enum`, `position` `required|integer|min:0`; authorizes `update`; returns `back()`.
  - `Board.vue` props `{ columns: BoardColumn[] }`; copies them into a local `ref` so dragging updates instantly. `BoardColumn.vue` renders `<VueDraggable v-model="column.applications" group="board" @add @update>`; on drop it calls `router.patch(route('applications.move', card.id), { status: column.status, position: newIndex }, { preserveScroll: true })`. On error Inertia reloads props, which snaps the card back.
  - `ApplicationCard.vue` props `{ application: Application }`: company, role, `applied_on` if set, `Link` to `applications.show`. Empty column shows "No applications".

- [ ] **Step 1: Write failing tests**

```php
test_board_has_all_five_columns_even_when_empty()   // ->component('Board')->has('columns', 5)->where('columns.0.status', 'wishlist')->has('columns.4.applications', 0)
test_board_lists_cards_in_position_order()          // Beta position 0, Alpha position 1 → columns.1.applications.0.company == 'Beta Corp'
test_board_excludes_other_users_cards()
test_move_updates_status_and_position()             // patch → assertRedirect(); status 'interviewing', position 0
test_move_rejects_invalid_status()                  // ['status'=>'hired'] → assertSessionHasErrors('status')
test_move_rejects_negative_position()               // → assertSessionHasErrors('position')
test_move_other_users_application_is_forbidden()    // → assertForbidden()
```

- [ ] **Step 2: Run, expect FAIL** — `php artisan test --filter=Board`
- [ ] **Step 3: Implement** controllers, routes, types, Vue page and components.
- [ ] **Step 4: Verify** — `php artisan test` and `npm run build` PASS.
- [ ] **Step 5: Manual check** — `composer run dev`, log in, drag a card across columns and within a column, reload: card stays where dropped.
- [ ] **Step 6: Commit** — `git commit -m "feat: Kanban board with drag-and-drop"`

### Phase 2 close

- [ ] Pint, tests, build, code review, merge `feature/kanban-board` into `main` (`--no-ff`).

---

## Phase 3 — branch `feature/notes`

### Task 5: Notes per application

**Files:**
- Create: `app/Models/Note.php`, `database/migrations/xxxx_create_notes_table.php`, `database/factories/NoteFactory.php`, `app/Http/Controllers/NoteController.php`, `app/Http/Resources/NoteResource.php`, `resources/js/Components/NotesList.vue`
- Modify: `app/Models/Application.php`, `ApplicationResource.php` (add `notes_count`), `ApplicationController@show` (pass notes), `resources/js/Pages/Applications/Show.vue`, `ApplicationCard.vue` (show note count), `routes/web.php`, `resources/js/types/index.d.ts`
- Test: `tests/Feature/NoteTest.php`

**Interfaces:**
- Consumes: `ApplicationPolicy::update`.
- Produces:
  - Table `notes`: `id`, `application_id` FK `cascadeOnDelete`, `body` text, `timestamps`.
  - `Note::application(): BelongsTo`; `Application::notes(): HasMany` ordered `latest()`.
  - `NoteResource` → `{id, body, created_at (ISO 8601), created_human (diffForHumans)}`; TS `interface Note { id: number; body: string; created_at: string; created_human: string }`; `Application` gains `notes_count?: number`.
  - Routes: `POST /applications/{application}/notes` → `applications.notes.store`; `DELETE /notes/{note}` → `notes.destroy` (shallow). Both authorize `update` on the parent application. `body` `required|string|max:5000`. Both return `back()`.
  - `Applications/Show` props become `{application: Application, notes: Note[]}` (newest first). `NotesList.vue` props `{ applicationId: number; notes: Note[] }`: textarea with `useForm`, list rendered with `{{ }}` text interpolation (never `v-html`) and `whitespace-pre-line` to keep line breaks.

- [ ] **Step 1: Write failing tests**

```php
test_user_can_add_note()                          // post → assertDatabaseHas('notes', body)
test_note_body_required_and_max_5000()            // '' and str_repeat('a',5001) → assertSessionHasErrors('body')
test_show_page_has_notes_newest_first()           // ->where('notes.0.body', 'second')->where('notes.1.body', 'first')
test_user_can_delete_own_note()
test_cannot_add_or_delete_note_on_others_app()    // assertForbidden() for both
test_deleting_application_deletes_its_notes()     // assertDatabaseCount('notes', 0)
test_board_cards_include_notes_count()            // ->where('columns.0.applications.0.notes_count', 2)
```

- [ ] **Step 2: Run, expect FAIL** — `php artisan test --filter=Note`
- [ ] **Step 3: Implement** (board query adds `withCount('notes')`).
- [ ] **Step 4: Verify** — `php artisan test` and `npm run build` PASS.
- [ ] **Step 5: Commit** — `git commit -m "feat: notes on applications"`

### Phase 3 close

- [ ] Pint, tests, build, code review, merge `feature/notes` into `main` (`--no-ff`).

---

## Phase 4 — branch `feature/follow-up-reminders`

### Task 6: Follow-up date and "due" logic in the UI

**Files:**
- Create: `database/migrations/xxxx_add_follow_up_on_to_applications_table.php`
- Modify: `app/Models/Application.php`, `ApplicationRequest.php`, `ApplicationResource.php`, `BoardController.php`, `ApplicationForm.vue`, `ApplicationCard.vue`, `Board.vue`, `resources/js/types/index.d.ts`
- Test: `tests/Feature/FollowUpTest.php`

**Interfaces:**
- Produces:
  - Column `follow_up_on` date nullable; cast `'date'`; fillable; rule `nullable|date`.
  - `scopeFollowUpDue(Builder $q, ?CarbonInterface $today = null)` → `follow_up_on <= ($today ?? today())` and `status != rejected`.
  - `isFollowUpDue(): bool` mirroring the scope for one model.
  - `ApplicationResource` adds `follow_up_on` (`Y-m-d|null`) and `follow_up_due` (bool from `isFollowUpDue()`); TS `Application` gains both.
  - `Board` props add `dueCount: number`. `Board.vue` shows a banner "N follow-up(s) due" when `dueCount > 0`; `ApplicationCard.vue` shows a "Follow up" badge when `follow_up_due`. Due logic lives only in PHP; Vue just reads the flags.
  - `ApplicationForm.vue` gets a date input for `follow_up_on`.

- [ ] **Step 1: Write failing tests** (use `$this->travelTo('2026-11-02')`)

```php
test_due_includes_today_and_past()          // 2026-11-02 and 2026-11-01 due; 2026-11-03 not; null not
test_rejected_is_never_due()
test_board_props_include_due_count_and_flag()   // ->where('dueCount', 1)->where('columns.1.applications.0.follow_up_due', true)
test_follow_up_on_saved_from_form()
```

- [ ] **Step 2: Run, expect FAIL** — `php artisan test --filter=FollowUp`
- [ ] **Step 3: Implement**
- [ ] **Step 4: Verify** — `php artisan test` and `npm run build` PASS.
- [ ] **Step 5: Commit** — `git commit -m "feat: follow-up dates with due badges"`

### Task 7: Daily reminder email

**Files:**
- Create: `app/Console/Commands/SendFollowUpReminders.php`, `app/Notifications/FollowUpsDue.php`
- Modify: `routes/console.php`
- Test: `tests/Feature/SendFollowUpRemindersTest.php`

**Interfaces:**
- Consumes: `scopeFollowUpDue`.
- Produces:
  - Command signature `applications:send-follow-up-reminders`. For each user with ≥1 due application, sends **one** `FollowUpsDue` notification; users with none get nothing. Prints `Sent reminders to N user(s).`
  - `FollowUpsDue(Collection $applications)` — `via: ['mail']`; subject `"You have N follow-up(s) due"`; one line per application: `"{company} — {role}"`; action button to `route('board')`.
  - Schedule in `routes/console.php`: `Schedule::command('applications:send-follow-up-reminders')->dailyAt('08:00');`

- [ ] **Step 1: Write failing tests** (`Notification::fake()`, `travelTo`)

```php
test_sends_one_notification_per_user_with_due_items()   // user A 2 due → assertSentToTimes($a, FollowUpsDue::class, 1)
test_skips_users_with_nothing_due()                       // assertNotSentTo($b, ...)
test_notification_lists_only_due_applications()          // assertSentTo($a, fn($n) => $n->applications->count() === 2)
test_command_is_scheduled()                               // Schedule events contain the command string
```

- [ ] **Step 2: Run, expect FAIL** — `php artisan test --filter=SendFollowUpReminders`
- [ ] **Step 3: Implement**
- [ ] **Step 4: Run, expect PASS**; manual: `php artisan applications:send-follow-up-reminders`, then check `storage/logs/laravel.log` for the mail.
- [ ] **Step 5: Commit** — `git commit -m "feat: daily follow-up reminder email"`

### Phase 4 close

- [ ] Pint, tests, build, code review, merge `feature/follow-up-reminders` into `main` (`--no-ff`).

---

## Later (not in this plan)

Each is its own branch + plan when wanted: Vue component tests with Vitest, search/filter on the board, simple stats (applications per status, response rate), demo seeder + README screenshots for the portfolio, deployment.
