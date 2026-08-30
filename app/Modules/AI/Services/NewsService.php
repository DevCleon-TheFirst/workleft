<?php

namespace App\Modules\AI\Services;

use App\Modules\AI\Providers\DeepSeekProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class NewsService
{
    private DeepSeekProvider $ai;

    public function __construct(DeepSeekProvider $ai)
    {
        $this->ai = $ai;
    }

    public function getTechNews(): array
    {
        return Cache::remember('ai_tech_news', now()->addHours(2), function () {
            // Fetch top 500 story IDs from HackerNews (no rate limit, no API key)
            $idsResponse = Http::timeout(10)->get('https://hacker-news.firebaseio.com/v0/topstories.json');

            if ($idsResponse->failed()) {
                return [];
            }

            $ids = array_slice($idsResponse->json(), 0, 20); // take top 20 to filter from

            $articles = [];
            $count    = 0;

            foreach ($ids as $id) {
                if ($count >= 5) break;

                $itemResponse = Http::timeout(8)->get("https://hacker-news.firebaseio.com/v0/item/{$id}.json");

                if ($itemResponse->failed()) continue;

                $item = $itemResponse->json();

                // Skip non-story items (jobs, polls) and items without URLs
                if (($item['type'] ?? '') !== 'story' || empty($item['url'])) continue;

                $title = $item['title'] ?? 'Untitled';
                $url   = $item['url'];
                $score = $item['score'] ?? 0;

                // Only surface higher-signal stories
                if ($score < 50) continue;

                $prompt = "Headline: \"{$title}\"\nWrite a 1-sentence insight on why this matters for a software developer or entrepreneur. Be concise and direct.";

                $insight = $this->ai->chat([['role' => 'user', 'content' => $prompt]]);

                $articles[] = [
                    'title'   => $title,
                    'url'     => $url,
                    'score'   => $score,
                    'insight' => trim($insight),
                ];

                $count++;
            }

            return $articles;
        });
    }
}
