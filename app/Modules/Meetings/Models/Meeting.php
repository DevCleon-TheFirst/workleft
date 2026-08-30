<?php

namespace App\Modules\Meetings\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Projects\Models\Project;

class Meeting extends Model
{
    protected $fillable = [
        'project_id', 'title', 'status', 'scheduled_at', 'transcript', 'ai_summary'
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'ai_summary'   => 'array',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // Convenience scopes
    public function scopeUpcoming($query)
    {
        return $query->where('status', 'upcoming')
                     ->where('scheduled_at', '>', now());
    }

    public function scopePast($query)
    {
        return $query->where('status', 'past');
    }
}
