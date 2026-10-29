<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Http\Requests\MoveApplicationRequest;
use App\Models\Application;
use Illuminate\Http\RedirectResponse;

class MoveApplicationController extends Controller
{
    public function __invoke(MoveApplicationRequest $request, Application $application): RedirectResponse
    {
        $application->moveTo(
            ApplicationStatus::from($request->validated('status')),
            (int) $request->validated('position'),
        );

        return back();
    }
}
