<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Http\Requests\ApplicationRequest;
use App\Http\Resources\ApplicationResource;
use App\Models\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ApplicationController extends Controller
{
    public function index(Request $request): Response
    {
        $applications = $request->user()->applications()->latest('id')->get();

        return Inertia::render('Applications/Index', [
            'applications' => ApplicationResource::collection($applications)->resolve(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Applications/Create', [
            'statuses' => ApplicationStatus::options(),
        ]);
    }

    public function store(ApplicationRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $application = new Application($data);
        $application->position = $request->user()->applications()->where('status', $data['status'])->count();
        $application->user()->associate($request->user());
        $application->save();

        return redirect()->route('applications.show', $application);
    }

    public function show(Application $application): Response
    {
        Gate::authorize('view', $application);

        return Inertia::render('Applications/Show', [
            'application' => (new ApplicationResource($application))->resolve(),
        ]);
    }

    public function edit(Application $application): Response
    {
        Gate::authorize('update', $application);

        return Inertia::render('Applications/Edit', [
            'application' => (new ApplicationResource($application))->resolve(),
            'statuses' => ApplicationStatus::options(),
        ]);
    }

    public function update(ApplicationRequest $request, Application $application): RedirectResponse
    {
        Gate::authorize('update', $application);

        $application->update($request->validated());

        return redirect()->route('applications.show', $application);
    }

    public function destroy(Application $application): RedirectResponse
    {
        Gate::authorize('delete', $application);

        $application->delete();

        return redirect()->route('applications.index');
    }
}
