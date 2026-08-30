<?php

namespace App\Modules\Tasks\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Projects\Models\Project;
use App\Models\User;

class Task extends Model
{
    protected $fillable = [
        'project_id', 'assignee_id', 'title', 'status', 'start_date', 'due_date'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'due_date'   => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }
}
