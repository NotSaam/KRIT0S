<?php

namespace App\Models;

use App\Enums\FindingStatus;
use App\Enums\Severity;
use App\Support\Cvss\Cvss31;
use Database\Factories\FindingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Finding extends Model
{
    /** @use HasFactory<FindingFactory> */
    use HasFactory;

    protected $fillable = [
        'engagement_id',
        'template_id',
        'author_id',
        'number',
        'title',
        'cvss_vector',
        'status',
        'affected_assets',
        'description',
        'proof_of_concept',
        'impact',
        'remediation',
        'references',
    ];

    protected $appends = ['reference'];

    protected function casts(): array
    {
        return [
            'cvss_score' => 'float',
            'severity' => Severity::class,
            'status' => FindingStatus::class,
        ];
    }

    protected static function booted(): void
    {
        // Score and severity are always derived from the vector, never trusted from input.
        static::saving(function (Finding $finding) {
            $cvss = Cvss31::parse($finding->cvss_vector);
            $finding->cvss_vector = $cvss->toString();
            $finding->cvss_score = $cvss->baseScore();
            $finding->severity = $cvss->severity();
        });

        static::creating(function (Finding $finding) {
            $finding->number ??= (int) static::query()
                ->where('engagement_id', $finding->engagement_id)
                ->max('number') + 1;
        });
    }

    /**
     * @return BelongsTo<Engagement, $this>
     */
    public function engagement(): BelongsTo
    {
        return $this->belongsTo(Engagement::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * @return BelongsTo<FindingTemplate, $this>
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(FindingTemplate::class);
    }

    public function getReferenceAttribute(): string
    {
        return sprintf('F-%02d', $this->number);
    }
}
