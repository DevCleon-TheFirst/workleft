<?php

namespace App\Modules\Classroom\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class Material extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'title',
        'module',       // e.g. "Week 1", "REST APIs", "Auth"
        'type',         // 'link', 'pdf', 'video'
        'content_url',  // YouTube link, PDF path, or external link
        'description',
        'visibility',   // 'all', 'specific'
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'material_user');
    }
}
