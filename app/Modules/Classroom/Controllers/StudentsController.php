<?php

namespace App\Modules\Classroom\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use App\Models\User;

class StudentsController extends Controller
{
    public function index()
    {
        $students = User::where('role', 'student')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'created_at']);

        return Inertia::render('Classroom/Students/Index', [
            'students' => $students,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
        ]);

        // Generate a secure one-time password to show teacher
        $plainPassword = Str::random(10);

        $student = User::create([
            'name'              => $request->name,
            'email'             => $request->email,
            'password'          => Hash::make($plainPassword),
            'role'              => 'student',
            'email_verified_at' => now(),
        ]);

        return redirect()->back()->with([
            'success'        => 'Student account created.',
            'plain_password' => $plainPassword,
            'student_name'   => $student->name,
        ]);
    }

    public function destroy(User $user)
    {
        if ($user->role !== 'student') {
            abort(403, 'You can only remove student accounts.');
        }
        $user->delete();
        return redirect()->back()->with('success', 'Student removed.');
    }
}
