# shadcn-vue Styling Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Restyle the whole job tracker with shadcn-vue: a sidebar app shell, a polished Kanban board, and consistent forms, dialogs and auth pages, in light and dark mode.

**Architecture:** Upgrade Tailwind 3 → 4 first, then add shadcn-vue (Reka UI primitives, components copied into the repo) with its theme tokens in `resources/css/app.css`. Replace Breeze's hand-rolled components page by page, then delete them. No backend behaviour changes: the same Inertia pages, props, and routes.

**Tech Stack:** Laravel 12, Inertia 2, Vue 3 + TypeScript, Tailwind CSS 4 (`@tailwindcss/vite`), shadcn-vue (latest at execution time; record the version in the Task 2 commit), reka-ui, lucide-vue-next, vue-sonner, @vueuse/core.

**Spec:** No design doc (user skipped questions). The request: "I want to improve styling of my layout using shadcn-ui." Builds on `docs/superpowers/plans/2026-10-29-job-application-tracker.md`.

## Global Constraints

- Work in `C:\Users\gnbda\job-application-tracker` on branch `feature/shadcn-ui` (cut from `feature/follow-up-reminders`). Never touch git in `C:\Users\gnbda`.
- **Windows case-insensitive filesystem:** the existing folder is `resources/js/Components` (capital C). shadcn-vue must be configured with aliases `components: "@/Components"`, `ui: "@/Components/ui"`, `lib: "@/lib"`, `utils: "@/lib/utils"`, `composables: "@/composables"`. Never create a lowercase `components` folder.
- No PHP/backend changes (only `resources/views/app.blade.php` in Task 2); if another seems needed, stop and report. Inertia page names and props stay identical, so the existing 74 PHP tests must keep passing untouched.
- Every task ends green on: `php artisan test`, `npm run build` (vue-tsc + vite), `vendor/bin/pint --test`.
- Vue 3 `<script setup lang="ts">`, Composition API. Icons only from `lucide-vue-next`.
- Theme (user asked for "more colorful"; approved mockup): shadcn `neutral` base colour with a **violet accent**, CSS variables, `--radius: 0.625rem`, font Figtree (already loaded by Breeze). Light and dark mode via the `.dark` class on `<html>`.
  - `--primary` = violet-600 (`oklch(0.541 0.281 293.009)`) in light, violet-500 (`oklch(0.606 0.25 292.717)`) in dark; `--primary-foreground` white; `--ring` = primary. Page background slightly violet-tinted (light `oklch(0.985 0.005 293)`, dark `oklch(0.16 0.02 285)`).
  - Sidebar is filled: `--sidebar` = violet-800 (`oklch(0.432 0.232 292.759)`) light and violet-950 (`oklch(0.283 0.141 291.089)`) dark; `--sidebar-foreground` white; `--sidebar-accent` = white at 20% (`oklch(1 0 0 / 0.2)`), `--sidebar-accent-foreground` white; `--sidebar-border` white at 20%.
  - User avatar fallback: amber-400 background, amber-950 text.
- Status colours live in one place, `resources/js/lib/status.ts`: wishlist = slate, applied = blue, interviewing = amber, offer = emerald, rejected = rose. It exports class strings (with `dark:` variants) for: dot, title text, column background tint (light `-50`, dark `-950/60`), solid count badge (`-500`/`-600` bg, white text), card top stripe (`border-t-[3px]` in the `-500` shade), and dashed empty-state border/text.
- Follow-up "due" uses amber everywhere (card badge, detail badge, board banner).
- Visual checks use a dedicated server on port 8001 (`php artisan serve --port=8001`), so the user's own dev server on 8000 is left alone.
- Each task includes a visual check in the built-in browser (`php artisan serve` + log in with the seeded test user), light and dark. If screenshots time out, use `get_page_text`/`read_page` to confirm structure and say so in the report.

## Review Focus

1. **Drag-and-drop still works after restyling** (cards wrapped in new markup can break vue-draggable-plus's item detection): a cross-column drop and a same-column reorder must persist. Checked in Task 4 in the browser and by DB query.
2. **Dark mode on first paint:** a user with dark mode saved must not see a white flash or unreadable text on any page. Checked in Tasks 2 and 6.
3. **Mobile width (375px):** sidebar collapses to a sheet; board columns scroll horizontally without the page scrolling sideways. Checked in Tasks 3 and 4.
4. **Destructive actions still confirm:** deleting an application, a note, or the account goes through an AlertDialog; cancelling does nothing. Checked in Tasks 5 and 6.
5. **Form validation errors still show** under the right field after the switch to shadcn inputs. Checked in Tasks 5 and 6.

---

### Task 1: Upgrade Tailwind 3 → 4

**Files:**
- Modify: `package.json`, `vite.config.js`, `resources/css/app.css`
- Delete: `tailwind.config.js`, `postcss.config.js`

- [ ] **Step 1:** Run `npx @tailwindcss/upgrade` (commit or stash nothing else first; the tool needs a clean tree). Review its diff.
- [ ] **Step 2:** Make sure the result is: `tailwindcss` ^4 and `@tailwindcss/vite` ^4 in devDependencies; `tailwindcss()` from `@tailwindcss/vite` added to `vite.config.js` plugins; no `postcss.config.js`, no `autoprefixer`, no `tailwind.config.js`; `app.css` starts with `@import "tailwindcss";`, keeps `@plugin "@tailwindcss/forms";` for now (Breeze inputs still use it), sets `--font-sans: Figtree, ui-sans-serif, system-ui, sans-serif` in `@theme`, and has `@source` lines covering `resources/js/**/*.vue` and the Laravel pagination/blade views the old config listed.
- [ ] **Step 3: Verify** — `npm run build` passes; `php artisan test` 74 passed; browser: login page, board and an application form look the same as before (no unstyled elements, no lost borders on inputs/buttons).
- [ ] **Step 4: Commit** — `chore: upgrade to Tailwind CSS 4`

### Task 2: shadcn-vue setup, theme, dark mode

**Files:**
- Create: `components.json`, `resources/js/lib/utils.ts`, `resources/js/lib/status.ts`, `resources/js/composables/useAppearance.ts`, `resources/js/Components/ui/**` (generated)
- Modify: `resources/css/app.css`, `resources/views/app.blade.php`, `resources/js/app.ts`, `package.json`

**Interfaces:**
- Produces:
  - `components.json` with `"typescript": true`, `"tailwind": { "css": "resources/css/app.css", "baseColor": "neutral", "cssVariables": true }`, `"iconLibrary": "lucide"`, and the aliases from Global Constraints. Write it by hand before running the CLI so the CLI never asks.
  - `cn(...inputs: ClassValue[]): string` in `@/lib/utils` (clsx + tailwind-merge).
  - Generated via `npx shadcn-vue@latest add`: `button input label textarea select card badge table dropdown-menu alert-dialog sidebar separator avatar sonner tooltip sheet skeleton`.
  - `@/lib/status.ts`: `statusStyles(status: ApplicationStatus): { dot: string; title: string; column: string; count: string; stripe: string; empty: string; badge: string }` returning the Tailwind class strings from Global Constraints (light + `dark:`; `badge` = soft tinted pill for tables and the detail page). Plain data map, no component. Class strings must be written out in full (no string-built class names) so Tailwind 4 detects them.
  - `useAppearance()` in `@/composables/useAppearance.ts` → `{ appearance: Ref<'light'|'dark'|'system'>, setAppearance(v) }`, persisted in `localStorage` key `appearance`, toggling `.dark` on `document.documentElement`, following `prefers-color-scheme` when `system`. Default `system`.
  - `app.blade.php` gets a tiny inline script in `<head>` (before Vite assets) that applies `.dark` from `localStorage`/`prefers-color-scheme` so there is no flash on load.
  - `app.ts` mounts `<Toaster />` (vue-sonner) once, globally (e.g. in a small root wrapper or in the layouts — pick one place and say which).
- `app.css`: shadcn theme tokens (`:root` and `.dark` blocks, `@theme inline` mapping, `@custom-variant dark (&:is(.dark *));`, `tw-animate-css` import) added by the CLI; keep the Figtree font setting.

- [ ] **Step 1:** Write `components.json`; run `npx shadcn-vue@latest add <components above>`; confirm files land in `resources/js/Components/ui/` (capital C) and nothing is created under a lowercase `components/`.
- [ ] **Step 2:** Add `status.ts`, `useAppearance.ts`, the no-flash head script, and the Toaster.
- [ ] **Step 3: Verify** — `npm run build` and `php artisan test` pass; browser: existing pages unchanged in light mode; set `localStorage.appearance = 'dark'` and reload any page: no white flash, `<html class="dark">` present.
- [ ] **Step 4: Commit** — `feat: add shadcn-vue with theme tokens and dark mode` (body: shadcn-vue version used)

### Task 3: App shell — sidebar layout

**Files:**
- Create: `resources/js/Components/AppSidebar.vue`, `resources/js/Components/AppHeader.vue`, `resources/js/Components/AppearanceToggle.vue`, `resources/js/Components/UserMenu.vue`
- Modify: `resources/js/Layouts/AuthenticatedLayout.vue`, every page using it (only if its slot usage changes)

**Interfaces:**
- `AuthenticatedLayout.vue` = `SidebarProvider` + `AppSidebar` + `SidebarInset` containing `AppHeader` and a `<main class="flex-1 p-4 md:p-6">` default slot. Keeps its existing `header` slot (rendered as the page title in `AppHeader`) and adds an optional `actions` slot (right side of the header).
- `AppSidebar.vue`: header = app logo + "Job Tracker"; nav group with `Board` (`LayoutDashboard` icon, `route('board')`, active on `board`) and `Applications` (`Briefcase` icon, `route('applications.index')`, active on `applications.*`); footer = `UserMenu`. `collapsible="icon"` on desktop; on mobile it opens as a sheet via `SidebarTrigger`.
- `UserMenu.vue`: DropdownMenu with avatar initials + name/email; items `Profile` (`route('profile.edit')`) and `Log out` (Inertia `Link` with `method="post"` `as="button"` to `route('logout')`).
- `AppHeader.vue`: `SidebarTrigger`, `Separator`, page title (header slot), spacer, `actions` slot, `AppearanceToggle` (DropdownMenu: Light / Dark / System via `useAppearance`).
- Board page passes a "New application" `Button` (`Plus` icon → `applications.create`) in the `actions` slot.
- Breeze `NavLink`, `ResponsiveNavLink`, `Dropdown`, `DropdownLink` become unused by the layout (deleted in Task 6 once nothing imports them).

- [ ] **Step 1:** Build the four components and the new layout.
- [ ] **Step 2: Verify** — build + tests pass; browser (desktop): sidebar shows both links with the active one highlighted, collapse/expand works, user menu logs out; theme toggle switches light/dark and survives reload; browser at 375px width: sidebar hidden, trigger opens it as a sheet, no horizontal page scroll.
- [ ] **Step 3: Commit** — `feat: sidebar app shell with user menu and theme toggle`

### Task 4: Board styling

**Files:**
- Modify: `resources/js/Pages/Board.vue`, `resources/js/Components/BoardColumn.vue`, `resources/js/Components/ApplicationCard.vue`

**Interfaces:**
- Consumes: `statusStyles`, shadcn `Card`, `Badge`, lucide icons.
- Board: columns in a horizontal scroll row (`overflow-x-auto`, each column fixed width ~18rem, `snap-x` on mobile). Follow-up banner = amber alert-style box with `BellRing` icon and the existing "N follow-up(s) due" text and anchor link.
- Column: rounded panel tinted with `statusStyles().column`; header shows the status dot, the label in the status colour (`title`), and a solid count pill (`count`). The drop zone (`VueDraggable`) keeps `min-h-24` and the empty state ("No applications") goes **inside** the drop zone as a dashed placeholder in the status colour (`empty`) (fixes the earlier deferred minor).
- Card: shadcn `Card` (compact padding) with the status-coloured top stripe (`stripe`), company (semibold) + role (muted), footer row with `applied_on` (`CalendarDays` icon), notes count (`MessageSquare` icon, only when > 0) and the amber "Follow up" `Badge` when due. Whole card is the `Link` to `applications.show`; `cursor-grab`, hover shadow. Keep `id="application-{id}"` and the existing `data-*`/key attributes the drag handler relies on. The draggable item must remain the direct child element of `VueDraggable`.

- [ ] **Step 1:** Restyle the three files.
- [ ] **Step 2: Verify** — build + tests pass; browser: board renders in light and dark; drag a card to the end of another column and reorder within a column, then confirm with `php artisan tinker` that statuses/positions persisted (Review Focus 1); 375px width: columns scroll sideways inside the board only.
- [ ] **Step 3: Commit** — `feat: restyle Kanban board with shadcn cards and badges`

### Task 5: Application pages, form, notes

**Files:**
- Modify: `resources/js/Pages/Applications/{Index,Create,Edit,Show}.vue`, `resources/js/Components/ApplicationForm.vue`, `resources/js/Components/NotesList.vue`
- Create: `resources/js/Components/ConfirmDialog.vue`

**Interfaces:**
- `ConfirmDialog.vue` (wraps AlertDialog): props `{ title: string; description: string; confirmLabel?: string }` (default "Delete"), default slot = trigger, emits `confirm`. Confirm button uses `variant="destructive"`. Used for deleting applications and notes (replaces `window.confirm`).
- Index: shadcn `Table` (Company, Role, Status badge, Applied, Follow up), row click/link to show; empty state card with a "New application" button. "New application" button in the layout `actions` slot.
- ApplicationForm: inside a `Card`; shadcn `Label` + `Input` / `Select` (status, options from the existing `statuses` prop) / date `Input`s; two-column grid on `md+`; errors under each field via existing `InputError` (restyled to `text-sm text-destructive`); footer with `Button` Save (disabled while processing) and `Button variant="outline"` Cancel (existing destination logic unchanged). On success, `toast.success('Application saved')`.
- Show: header title = company, actions slot = Edit (`Pencil`) + Delete (`ConfirmDialog`). Details `Card` with a description list (status badge, role, URL as external link with `ExternalLink` icon, location, salary, applied on, follow up on + amber badge when due). Notes `Card`: `Textarea` + "Add note" button; list of notes with `created_human` muted and a ghost icon `Trash2` button wrapped in `ConfirmDialog`. `toast.success('Note added')` / `('Note deleted')`. Note bodies still rendered with `{{ }}` and `whitespace-pre-line` (never `v-html`).

- [ ] **Step 1:** Build `ConfirmDialog`, restyle the six files.
- [ ] **Step 2: Verify** — build + tests pass; browser: create with empty required fields shows errors under Company and Role (Review Focus 5); create, edit, add/delete a note, delete an application — each delete asks first and Cancel keeps the record (Review Focus 4); toasts appear; light and dark.
- [ ] **Step 3: Commit** — `feat: restyle application pages, form and notes`

### Task 6: Auth, profile, welcome pages; remove Breeze components

**Files:**
- Modify: `resources/js/Layouts/GuestLayout.vue`, `resources/js/Pages/Auth/*.vue`, `resources/js/Pages/Profile/Edit.vue`, `resources/js/Pages/Profile/Partials/*.vue`, `resources/js/Pages/Welcome.vue`, `resources/js/Components/InputError.vue`, `resources/css/app.css`, `package.json`
- Delete: `resources/js/Components/{PrimaryButton,SecondaryButton,DangerButton,TextInput,InputLabel,Checkbox,Modal,Dropdown,DropdownLink,NavLink,ResponsiveNavLink}.vue`

**Interfaces:**
- GuestLayout: centered `Card` (max-w-sm) with logo + "Job Tracker" above, muted background, works in dark mode.
- Auth pages: shadcn `Label`/`Input`/`Button`; "Remember me" uses shadcn `Checkbox` (add it via the CLI if Task 2 didn't); links styled as `text-sm underline-offset-4 hover:underline`.
- Profile: three `Card`s (profile info, password, delete account); delete account uses AlertDialog with the password field inside (replaces Breeze `Modal`).
- Welcome: simple hero (title, one-line pitch, Log in / Register buttons, or "Go to board" when authenticated), light and dark.
- Remove `@plugin "@tailwindcss/forms";` and the `@tailwindcss/forms` package once no Breeze inputs remain.
- After deleting the Breeze components, `grep -r` for their names under `resources/js` must return nothing.

- [ ] **Step 1:** Restyle the pages, delete the components, drop the forms plugin.
- [ ] **Step 2: Verify** — build + tests pass (Breeze auth tests still green); grep check clean; browser: register, log out, log in (wrong password shows the error), forgot-password page, profile update, password update, delete-account dialog Cancel; every page in light and dark with no white flash on reload (Review Focus 2).
- [ ] **Step 3: Commit** — `feat: restyle auth and profile pages; remove Breeze components`

### Close

- [ ] Full suite, build, pint; whole-branch review on the most capable model; fix wave; then ask the user how to integrate (merge `feature/follow-up-reminders` and `feature/shadcn-ui` into `main`, or keep).

## Later (not in this plan)

Toast messages driven by server flash data, keyboard-accessible "Move to…" menu on cards, skeleton loading states, a stats page.
