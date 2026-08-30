<?php

namespace App\Modules\AI\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\AI\Services\CryptoService;
use App\Modules\AI\Services\NewsService;
use App\Modules\AI\Services\WorkflowAnalysisService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IntelligenceController extends Controller
{
    public function index()
    {
        return Inertia::render('AI/Intelligence');
    }

    public function crypto(CryptoService $cryptoService)
    {
        return response()->json([
            'crypto' => $cryptoService->getAnalysis()
        ]);
    }

    public function news(NewsService $newsService)
    {
        return response()->json([
            'news' => $newsService->getTechNews()
        ]);
    }

    public function workflow(Request $request, WorkflowAnalysisService $workflowService)
    {
        return response()->json([
            'workflow' => $workflowService->getAnalysis($request)
        ]);
    }
}
