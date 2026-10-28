<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportVersion extends Model
{
    public const QUEUED = 'queued';

    public const READY = 'ready';

    public const FAILED = 'failed';

    protected $fillable = ['engagement_id', 'generated_by', 'version', 'status', 'path', 'sha256', 'size', 'findings_count'];

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
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function filename(): string
    {
        return sprintf('%s-informe-v%d.pdf', $this->engagement->code, $this->version);
    }
}
