<?php

namespace App\Modules\Classroom\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_template_id',
        'due_date',
        'status', // active, closed
        'visibility', // all, specific
    ];

    protected $casts = [
        'due_date' => 'datetime',
    ];

    public function template()
    {
        return $this->belongsTo(AssignmentTemplate::class, 'assignment_template_id');
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'assignment_user');
    }
}
