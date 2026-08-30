<?php

namespace App\Modules\Classroom\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Attendance extends Model
{
    protected $table = 'attendance';

    protected $fillable = [
        'student_id',
        'marked_by',
        'date',
        'status',       // present | absent | late
        'session_label',
        'note',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'marked_by');
    }
}
