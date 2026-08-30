<?php

namespace App\Modules\Classroom\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClassroomController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'teacher') {
            return Inertia::render('Classroom/TeacherDashboard');
        }

        return Inertia::render('Classroom/StudentDashboard');
    }
}
