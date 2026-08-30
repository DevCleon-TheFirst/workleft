<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureIsTeacher
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || $request->user()->role !== 'teacher') {
            abort(403, 'Access restricted to teachers only.');
        }
        return $next($request);
    }
}
