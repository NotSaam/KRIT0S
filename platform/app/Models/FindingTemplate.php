<?php

namespace App\Models;

use App\Support\Cvss\Cvss31;
use Database\Factories\FindingTemplateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FindingTemplate extends Model
{
    /** @use HasFactory<FindingTemplateFactory> */
    use HasFactory;

    protected $fillable = ['title', 'category', 'cvss_vector', 'description', 'impact', 'remediation', 'references'];

    protected $appends = ['cvss_score', 'severity'];

    /**
     * @return HasMany<Finding, $this>
     */
    public function findings(): HasMany
    {
        return $this->hasMany(Finding::class, 'template_id');
    }

    public function getCvssScoreAttribute(): float
    {
        return Cvss31::parse($this->cvss_vector)->baseScore();
    }

    public function getSeverityAttribute(): string
    {
        return Cvss31::parse($this->cvss_vector)->severity()->value;
    }
}
