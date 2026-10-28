<?php

namespace App\Models;

use App\Enums\EngagementStatus;
use App\Enums\EngagementType;
use App\Enums\Severity;
use Database\Factories\EngagementFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Engagement extends Model
{
    /** @use HasFactory<EngagementFactory> */
    use HasFactory;

    protected $fillable = [
        'client_id',
        'lead_id',
        'code',
        'title',
        'type',
        'status',
        'scope',
        'methodology',
        'starts_on',
        'ends_on',
        'executive_summary',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => EngagementType::class,
            'status' => EngagementStatus::class,
            'starts_on' => 'date',
            'ends_on' => 'date',
            'published_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lead_id');
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function team(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * @return HasMany<Finding, $this>
     */
    public function findings(): HasMany
    {
        return $this->hasMany(Finding::class);
    }

    /**
     * @return HasMany<ReportVersion, $this>
     */
    public function reports(): HasMany
    {
        return $this->hasMany(ReportVersion::class)->orderByDesc('version');
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null;
    }

    public function hasMember(User $user): bool
    {
        if ($this->lead_id === $user->id) {
            return true;
        }

        return $this->relationLoaded('team')
            ? $this->team->contains('id', $user->id)
            : $this->team()->whereKey($user->id)->exists();
    }

    /**
     * Restrict a query to what the given user is allowed to see.
     *
     * @param  Builder<self>  $query
     */
    public function scopeVisibleTo(Builder $query, User $user): void
    {
        if ($user->isAdmin()) {
            return;
        }

        if ($user->isClient()) {
            $query->where('client_id', $user->client_id)->whereNotNull('published_at');

            return;
        }

        $query->where(fn (Builder $q) => $q
            ->where('lead_id', $user->id)
            ->orWhereHas('team', fn (Builder $t) => $t->whereKey($user->id)));
    }

    /**
     * Count of findings per severity, always with every bucket present.
     *
     * @return array<string, int>
     */
    public function severityBreakdown(): array
    {
        $counts = $this->findings()
            ->selectRaw('severity, count(*) as total')
            ->groupBy('severity')
            ->pluck('total', 'severity');

        $breakdown = [];
        foreach (Severity::ordered() as $severity) {
            $breakdown[$severity->value] = (int) ($counts[$severity->value] ?? 0);
        }

        return $breakdown;
    }

    public static function nextCode(): string
    {
        $year = now()->year;
        $count = static::query()->where('code', 'like', "KRT-{$year}-%")->count();

        return sprintf('KRT-%d-%03d', $year, $count + 1);
    }
}
