<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Blueprint extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'raw_description',
        'agent_log',
        'deliverable',
        'status',
        'project_id',
        'uiux_design',
        'design_status',
    ];

    protected $casts = [
        'agent_log'    => 'array',
        'deliverable'  => 'array',
        'uiux_design'  => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
