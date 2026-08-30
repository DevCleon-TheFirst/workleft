<?php

namespace App\Modules\Knowledge\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Projects\Models\Project;

class Document extends Model
{
    protected $fillable = ['project_id', 'type', 'content_markdown'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
