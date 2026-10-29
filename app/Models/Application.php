<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Database\Factories\ApplicationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Application extends Model
{
    /** @use HasFactory<ApplicationFactory> */
    use HasFactory;

    protected $fillable = [
        'company',
        'role',
        'url',
        'location',
        'salary',
        'status',
        'applied_on',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ApplicationStatus::class,
            'applied_on' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Note, $this>
     */
    public function notes(): HasMany
    {
        return $this->hasMany(Note::class)->latest()->latest('id');
    }

    /**
     * @param  Builder<Application>  $query
     * @return Builder<Application>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('position')->orderBy('id');
    }

    /**
     * Move this application to $position within the given status column,
     * renumbering that column 0..n. The old column keeps its gaps.
     */
    public function moveTo(ApplicationStatus $status, int $position): void
    {
        DB::transaction(function () use ($status, $position) {
            $siblings = static::query()
                ->where('user_id', $this->user_id)
                ->where('status', $status)
                ->whereKeyNot($this->getKey())
                ->ordered()
                ->get();

            $siblings->splice(max(0, min($position, $siblings->count())), 0, [$this]);

            $this->status = $status;

            foreach ($siblings->values() as $index => $application) {
                if ($application->is($this)) {
                    $this->position = $index;
                    $this->save();
                } elseif ($application->position !== $index) {
                    $application->position = $index;
                    $application->save();
                }
            }
        });
    }
}
