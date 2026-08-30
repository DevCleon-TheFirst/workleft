<?php

namespace App\Modules\Planner\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Blueprint;
use Illuminate\Http\Request;

class BlueprintController extends Controller
{
    /**
     * List all blueprints for the authenticated user.
     */
    public function index(Request $request)
    {
        $blueprints = Blueprint::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get(['id', 'title', 'status', 'created_at']);

        return response()->json($blueprints);
    }

    /**
     * Save an approved blueprint to the database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'           => 'nullable|string|max:255',
            'raw_description' => 'required|string',
            'agent_log'       => 'required|array',
            'deliverable'     => 'required|array',
        ]);

        $deliverable = $request->input('deliverable');
        $title = $request->input('title')
            ?? ($deliverable['project_title'] ?? 'Untitled Blueprint');

        $blueprint = Blueprint::create([
            'user_id'         => $request->user()->id,
            'title'           => $title,
            'raw_description' => $request->input('raw_description'),
            'agent_log'       => $request->input('agent_log'),
            'deliverable'     => $deliverable,
            'status'          => 'approved',
        ]);

        return response()->json($blueprint, 201);
    }

    /**
     * Retrieve a single blueprint.
     */
    public function show(Request $request, Blueprint $blueprint)
    {
        // Ensure the blueprint belongs to the authenticated user
        if ($blueprint->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized.');
        }

        return response()->json($blueprint);
    }

    /**
     * Update an existing blueprint.
     */
    public function update(Request $request, Blueprint $blueprint)
    {
        // Ensure the blueprint belongs to the authenticated user
        if ($blueprint->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'title'           => 'nullable|string|max:255',
            'raw_description' => 'required|string',
            'agent_log'       => 'required|array',
            'deliverable'     => 'required|array',
        ]);

        $deliverable = $request->input('deliverable');
        $title = $request->input('title')
            ?? ($deliverable['project_title'] ?? 'Untitled Blueprint');

        $blueprint->update([
            'title'           => $title,
            'raw_description' => $request->input('raw_description'),
            'agent_log'       => $request->input('agent_log'),
            'deliverable'     => $deliverable,
        ]);

        return response()->json($blueprint);
    }
}
