<?php

namespace App\Modules\Planner\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\AI\Contracts\AIProviderInterface;
use App\Modules\AI\Services\AgentOrchestrator;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AIPlannerController extends Controller
{
    protected AIProviderInterface $aiProvider;

    public function __construct(AIProviderInterface $aiProvider)
    {
        $this->aiProvider = $aiProvider;
    }

    /**
     * Endpoint for the AI Requirements Interview step (existing chat feature).
     */
    public function interview(Request $request)
    {
        $request->validate([
            'context' => 'nullable|array',
            'user_input' => 'required|string',
        ]);

        $context = $request->input('context', []);
        $userInput = $request->input('user_input');

        $response = $this->aiProvider->conductRequirementsInterview($context, $userInput);

        return response()->json($response);
    }

    /**
     * Endpoint to generate project architecture from gathered requirements (existing feature).
     */
    public function generateArchitecture(Request $request)
    {
        $request->validate([
            'requirements' => 'required|array',
        ]);

        $requirements = $request->input('requirements');
        $architecture = $this->aiProvider->generateArchitecture($requirements);

        return response()->json([
            'architecture' => $architecture
        ]);
    }

    /**
     * NEW: Dual-AI 7-agent pipeline endpoint — Streamed via Server-Sent Events.
     *
     * The client connects with an EventSource and receives one SSE event per
     * completed agent, plus a final [DONE] event.
     */
    public function analyze(Request $request): StreamedResponse
    {
        $request->validate([
            'description' => 'required|string|min:20|max:5000',
        ]);

        $description = $request->input('description');
        $orchestrator = app(AgentOrchestrator::class);

        return new StreamedResponse(function () use ($orchestrator, $description) {
            // Disable output buffering for streaming
            if (ob_get_level()) {
                ob_end_clean();
            }

            $result = $orchestrator->orchestrate(
                $description,
                function (array $progress) {
                    echo "data: " . json_encode($progress) . "\n\n";
                    flush();
                }
            );

            // Stream the final deliverable so Planner.vue captures it just like it did for agent 7
            $payload = json_encode([
                'agent' => 7, // Planner.vue expects output from agent 7 to transition
                'status' => 'complete',
                'output' => json_encode($result['deliverable'])
            ]);
            echo "data: {$payload}\n\n";
            flush();

            // Signal pipeline completion
            echo "data: [DONE]\n\n";
            flush();

        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache',
            'X-Accel-Buffering' => 'no', // Disable nginx buffering
            'Connection'        => 'keep-alive',
        ]);
    }
}
