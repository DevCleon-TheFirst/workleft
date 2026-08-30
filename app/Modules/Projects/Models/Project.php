<?php

namespace App\Modules\Projects\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Clients\Models\Client;
use App\Modules\Tasks\Models\Task;
use App\Modules\Meetings\Models\Meeting;
use App\Modules\Knowledge\Models\Document;

class Project extends Model
{
    protected $fillable = [
        'client_id', 'title', 'description', 'status', 'ai_generated_architecture'
    ];

    protected $casts = [
        'ai_generated_architecture' => 'array',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function meetings()
    {
        return $this->hasMany(Meeting::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function getProgressAttribute(): int
    {
        $total = $this->tasks()->count();
        if ($total === 0) return 0;
        $done = $this->tasks()->where('status', 'done')->count();
        return (int) round(($done / $total) * 100);
    }
}
