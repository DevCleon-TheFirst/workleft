<?php

namespace App\Modules\Clients\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Projects\Models\Project;

class Client extends Model
{
    protected $fillable = ['company_name', 'contact_email', 'notes'];

    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}
