<?php

namespace App\Modules\AI\Services;

use App\Modules\AI\Providers\DeepSeekProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CryptoService
{
    private DeepSeekProvider $ai;

    public function __construct(DeepSeekProvider $ai)
    {
        $this->ai = $ai;
    }

    public function getAnalysis(): array
    {
        return Cache::remember('ai_crypto_analysis', now()->addMinutes(15), function () {
            $coins    = 'solana,tether,bitcoin,ethereum,binancecoin';
            $response = Http::timeout(15)->get('https://api.coingecko.com/api/v3/coins/markets', [
                'vs_currency' => 'usd',
                'ids'         => $coins,
                'sparkline'   => 'true',
            ]);

            if ($response->failed()) {
                return [];
            }

            $data = $response->json();

            // Build a single batched prompt for ALL coins at once
            $coinSummaries = [];
            $rawCoins      = [];

            foreach ($data as $coin) {
                $sparkline    = array_slice($coin['sparkline_in_7d']['price'] ?? [], -10);
                $sparklineStr = implode(', ', array_map(fn($v) => round($v, 2), $sparkline));

                $coinSummaries[] = "- {$coin['name']} ({$coin['symbol']}): Current \${$coin['current_price']}, 24h: {$coin['price_change_percentage_24h']}%, Sparkline: [{$sparklineStr}]";

                $rawCoins[$coin['id']] = [
                    'id'        => $coin['id'],
                    'name'      => $coin['name'],
                    'symbol'    => strtoupper($coin['symbol']),
                    'price'     => $coin['current_price'],
                    'change_24h'=> round($coin['price_change_percentage_24h'] ?? 0, 2),
                ];
            }

            $coinBlock = implode("\n", $coinSummaries);

            $prompt = "You are a crypto analyst. Analyze the following coins and give a short signal for each.

{$coinBlock}

For each coin respond with exactly this format (one per line):
COINID|SIGNAL|One sentence reason

Where COINID is the exact coin id (e.g. bitcoin), SIGNAL is BUY, HOLD, or WAIT.
Example:
bitcoin|BUY|Strong upward momentum in recent prices.
ethereum|HOLD|Prices are stable with no clear trend.

Reply with ONLY those lines, nothing else.";

            $analysis = $this->ai->chat([['role' => 'user', 'content' => $prompt]]);

            $results = [];
            $lines   = array_filter(explode("\n", trim($analysis)));

            // Parse each line
            foreach ($lines as $line) {
                $parts = explode('|', trim($line));
                if (count($parts) < 3) continue;

                $coinId = strtolower(trim($parts[0]));
                $signal = strtoupper(trim($parts[1]));
                $reason = trim($parts[2]);

                // Normalize signal
                if (!in_array($signal, ['BUY', 'HOLD', 'WAIT'])) {
                    if (str_contains($signal, 'BUY'))  $signal = 'BUY';
                    elseif (str_contains($signal, 'HOLD')) $signal = 'HOLD';
                    else $signal = 'WAIT';
                }

                if (isset($rawCoins[$coinId])) {
                    $results[] = array_merge($rawCoins[$coinId], [
                        'signal' => $signal,
                        'reason' => $reason,
                    ]);
                }
            }

            // Fallback: if AI parsing failed, return coins with WAIT signal
            if (empty($results)) {
                foreach ($rawCoins as $coin) {
                    $results[] = array_merge($coin, ['signal' => 'WAIT', 'reason' => 'Analysis unavailable.']);
                }
            }

            return $results;
        });
    }
}
