<?php

namespace App\Http\Controllers;

use App\Http\Requests\NoteRequest;
use App\Models\Application;
use App\Models\Note;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class NoteController extends Controller
{
    public function store(NoteRequest $request, Application $application): RedirectResponse
    {
        $application->notes()->create($request->validated());

        return back();
    }

    public function destroy(Note $note): RedirectResponse
    {
        Gate::authorize('update', $note->application);

        $note->delete();

        return back();
    }
}
