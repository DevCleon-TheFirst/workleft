<?php

namespace App\Modules\Classroom\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class AssignmentTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'title',
        'description_markdown',
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
