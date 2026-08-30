<?php

namespace App\Jobs;

use App\Models\Blueprint;
use App\Modules\AI\Services\UiUxOrchestrator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateUiUxDesign implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 360;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 2;

    public function __construct(
        protected Blueprint $blueprint
    ) {}

    public function handle(UiUxOrchestrator $orchestrator): void
    {
        Log::info('GenerateUiUxDesign: starting for blueprint #' . $this->blueprint->id);

        try {
            $uiuxDesign = $orchestrator->generate($this->blueprint->deliverable);

            if (isset($uiuxDesign['error'])) {
                Log::error('GenerateUiUxDesign: orchestrator returned error', $uiuxDesign);
                $this->blueprint->design_status = 'failed';
                $this->blueprint->save();
                return;
            }

            $this->blueprint->uiux_design = $uiuxDesign;
            $this->blueprint->design_status = 'completed';
            $this->blueprint->save();

            Log::info('GenerateUiUxDesign: completed for blueprint #' . $this->blueprint->id);

        } catch (\Exception $e) {
            Log::error('GenerateUiUxDesign: exception', ['message' => $e->getMessage()]);
            $this->blueprint->design_status = 'failed';
            $this->blueprint->save();
            throw $e;
        }
    }
}
