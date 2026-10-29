<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Http\Resources\ApplicationResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BoardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $byStatus = $request->user()->applications()->withCount('notes')->ordered()->get()->groupBy(fn ($a) => $a->status->value);

        $columns = array_map(fn (ApplicationStatus $status) => [
            'status' => $status->value,
            'label' => $status->label(),
            'applications' => ApplicationResource::collection($byStatus->get($status->value, collect()))->resolve(),
        ], ApplicationStatus::cases());

        return Inertia::render('Board', ['columns' => $columns]);
    }
}
