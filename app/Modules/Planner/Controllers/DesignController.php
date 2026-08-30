<?php

namespace App\Modules\Planner\Controllers;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateUiUxDesign;
use App\Models\Blueprint;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class DesignController extends Controller
{
    /**
     * Display a list of approved blueprints for design generation.
     */
    public function index(Request $request)
    {
        $blueprints = Blueprint::where('user_id', Auth::id())
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Design/Index', [
            'blueprints' => $blueprints
        ]);
    }

    /**
     * Show the design spec for a specific blueprint.
     */
    public function show(Request $request, Blueprint $blueprint)
    {
        if ($blueprint->user_id !== Auth::id()) {
            abort(403);
        }

        return Inertia::render('Design/Show', [
            'blueprint' => $blueprint
        ]);
    }

    /**
     * Dispatch the UI/UX design generation job (returns immediately).
     */
    public function generate(Request $request, Blueprint $blueprint)
    {
        if ($blueprint->user_id !== Auth::id()) {
            abort(403);
        }

        if (!$blueprint->deliverable) {
            return response()->json(['error' => 'Blueprint architecture is missing.'], 400);
        }

        // If already processing, don't dispatch again
        if ($blueprint->design_status === 'processing') {
            return response()->json([
                'queued' => true,
                'design_status' => 'processing',
                'message' => 'Design generation is already in progress.',
            ]);
        }

        // Clear old design to allow fresh regeneration
        $blueprint->uiux_design  = null;
        $blueprint->design_status = 'processing';
        $blueprint->save();

        GenerateUiUxDesign::dispatch($blueprint);

        return response()->json([
            'queued' => true,
            'design_status' => 'processing',
            'message' => 'Design generation started. Poll /status for updates.',
        ]);
    }

    /**
     * Poll endpoint — returns current design_status and uiux_design when ready.
     */
    public function status(Request $request, Blueprint $blueprint)
    {
        if ($blueprint->user_id !== Auth::id()) {
            abort(403);
        }

        $blueprint->refresh();

        return response()->json([
            'design_status' => $blueprint->design_status,
            'uiux_design'   => $blueprint->uiux_design,
        ]);
    }
}
