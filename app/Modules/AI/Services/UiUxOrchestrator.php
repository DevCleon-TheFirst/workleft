<?php

namespace App\Modules\AI\Services;

use App\Modules\AI\Providers\DeepSeekProvider;
use Illuminate\Support\Facades\Log;

class UiUxOrchestrator
{
    protected DeepSeekProvider $deepseek;

    public function __construct(DeepSeekProvider $deepseek)
    {
        $this->deepseek = $deepseek;
    }

    public function generate(array $architectureDeliverable): array
    {
        // ── PASS 1: Design system (brand, tokens, colors, typography) ─────────
        $designSystem = $this->generateDesignSystem($architectureDeliverable);
        if (isset($designSystem['error'])) {
            return $designSystem;
        }

        // ── PASS 2: Screen inventory ─────────────────────────────────────────
        $screens = $this->generateScreens($architectureDeliverable, $designSystem);
        if (isset($screens['error'])) {
            return $screens;
        }

        $deliverable = array_merge($designSystem, ['screens' => $screens]);

        // Attach a programmatically-generated SVG logo (no AI markup)
        $deliverable = $this->attachGeneratedLogo($deliverable);

        return $deliverable;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PASS 1 — Design System
    // ─────────────────────────────────────────────────────────────────────────
    protected function generateDesignSystem(array $arch): array
    {
        $systemPrompt = <<<PROMPT
You are a world-class, award-winning Senior Brand & UX Designer at a top design agency.
Your task: create a UNIQUE, premium design system inspired by award-winning work on Dribbble, Awwwards, and Behance.
RULES:
- NEVER use purple, indigo, violet, or any shade of #4F46E5, #6366F1, #7C3AED or similar.
- Pick a bold, distinct brand color that suits the PROJECT TYPE. For example: deep teal for fintech, charcoal+gold for enterprise, coral for consumer, navy+cyan for SaaS, slate+orange for POS systems.
- Use at minimum 3 distinct colors in the palette (primary, secondary, accent must all be different hues).
- Choose modern, premium fonts from Google Fonts — NOT Inter. Good choices: Plus Jakarta Sans, Outfit, DM Sans, Space Grotesk, Syne, Clash Display, Cabinet Grotesk.
- Reply with ONLY a raw JSON object — no markdown, no code fences.
- NEVER use double quotes inside string values.
- NEVER include SVG or HTML markup.
PROMPT;

        $appName = $arch['project_title'] ?? $arch['project_name'] ?? $arch['app_name'] ?? 'App';

        $context = "";
        if (!empty($arch['executive_summary'])) {
            $context .= "Executive Summary: {$arch['executive_summary']}\n";
        }
        if (!empty($arch['architecture_pattern'])) {
            $context .= "Architecture: {$arch['architecture_pattern']}\n";
        }

Generate the design system for "{$appName}".

CONTEXT:
{$context}

BRAND COLOR DIRECTIVE: Choose a color palette that feels AUTHENTIC to the nature of this specific project. Consider what emotions and professionalism the industry demands. Be bold and unique — do NOT default to purple or indigo.

Use this EXACT JSON structure (fill in all real, unique, hex color values — no placeholder text):

{
  "design_philosophy": "2 concise sentences describing the unique design direction.",
  "brand": {
    "app_name": "string",
    "tagline": "under 8 words",
    "personality_traits": ["trait1", "trait2", "trait3"],
    "logo": {
      "concept": "brief concept",
      "primary_color": "#hexcode",
      "shape": "circle|square|diamond|custom"
    }
  },
  "design_tokens": {
    "spacing": { "xs": 4, "sm": 8, "md": 16, "lg": 24, "xl": 40 },
    "border_radius": { "sm": 4, "md": 8, "lg": 12, "pill": 9999 },
    "motion": { "fast": "150ms", "normal": "250ms", "easing": "cubic-bezier(0.4, 0, 0.2, 1)" }
  },
  "color_system": {
    "palette": {
      "primary":     { "hex": "#hexcode", "usage": "Primary actions and brand identity" },
      "secondary":   { "hex": "#hexcode", "usage": "Supporting elements - MUST be a different hue from primary" },
      "accent":      { "hex": "#hexcode", "usage": "Highlights and CTAs - MUST be a contrasting, vibrant hue" },
      "neutral_900": { "hex": "#hexcode", "usage": "Primary text" },
      "neutral_600": { "hex": "#hexcode", "usage": "Secondary text" },
      "neutral_200": { "hex": "#hexcode", "usage": "Borders" },
      "neutral_50":  { "hex": "#hexcode", "usage": "Page background" },
      "success":     { "hex": "#hexcode", "usage": "Positive states" },
      "warning":     { "hex": "#hexcode", "usage": "Caution states" },
      "error":       { "hex": "#hexcode", "usage": "Error states" }
    },
    "dark_mode": {
      "background": "#hexcode",
      "surface":    "#hexcode",
      "border":     "#hexcode",
      "text":       "#hexcode"
    }
  },
  "typography": {
    "body_font": { "name": "Plus Jakarta Sans", "fallback": "sans-serif" },
    "mono_font": { "name": "JetBrains Mono", "fallback": "monospace" },
    "scale": [
      { "token": "h1", "size": 32, "weight": 800 },
      { "token": "h2", "size": 24, "weight": 700 },
      { "token": "h3", "size": 20, "weight": 600 },
      { "token": "body", "size": 16, "weight": 400 },
      { "token": "small", "size": 13, "weight": 400 }
    ]
  },
  "iconography": {
    "library": "Lucide",
    "style": "Line icons, 1.5px stroke, 24x24 viewBox"
  },
  "navigation_flow_mermaid": "graph TD\n  A[Login] --> B[Dashboard]\n  B --> C[Module1]\n  B --> D[Module2]"
}
PROMPT;

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user',   'content' => $userPrompt],
        ];

        $raw = $this->deepseek->chat($messages, true);
        file_put_contents(storage_path('logs/uiux_pass1_raw.txt'), $raw);

        $parsed = $this->parseJson($raw, 'design_system');
        return $parsed;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PASS 2 — Screens
    // ─────────────────────────────────────────────────────────────────────────
    protected function generateScreens(array $arch, array $designSystem): array
    {
        $systemPrompt = <<<PROMPT
You are a world-class, award-winning Principal UX Architect. Design stunning, Dribbble/Pinterest-inspired premium interfaces. Reply with ONLY a raw JSON array of screen objects — no markdown, no code fences.
NEVER use double quotes inside string values.
CRITICAL: Every screen object MUST contain all the following keys: id, name, route, category, platform, layout_type, description, visual_hierarchy, microcopy, interaction_states, motion, navigates_to, image_prompt. Do not omit ANY keys.
PROMPT;

        $appName = $designSystem['brand']['app_name'] ?? 'App';
        $primary = $designSystem['color_system']['palette']['primary']['hex'] ?? '#6366f1';

        // Pull screen list from architecture if available, otherwise infer
        $moduleList = '';
        if (!empty($arch['modules']) && is_array($arch['modules'])) {
            $moduleList = 'Key modules: ' . implode(', ', array_column($arch['modules'], 'name'));
        } elseif (!empty($arch['screens']) && is_array($arch['screens'])) {
            $moduleList = 'Screens: ' . implode(', ', array_column($arch['screens'], 'name'));
        }

        $context = "";
        if (!empty($arch['executive_summary'])) {
            $context .= "Executive Summary: {$arch['executive_summary']}\n";
        }
        if (!empty($arch['architecture_pattern'])) {
            $context .= "Architecture: {$arch['architecture_pattern']}\n";
        }

        $userPrompt = <<<PROMPT
Generate a JSON array of screens for "{$appName}". {$moduleList}

CONTEXT:
{$context}

Each screen object MUST use this EXACT structure (keep all values short and concise, except for image_prompt):

{
  "id": "kebab-case-id",
  "name": "Screen Name",
  "route": "/route",
  "category": "Category",
  "platform": "Both",
  "layout_type": "Brief layout description",
  "description": "One sentence what this screen does.",
  "visual_hierarchy": ["First element", "Second element", "Third element"],
  "microcopy": [
    { "element": "CTA", "copy": "Button label" }
  ],
  "interaction_states": {
    "loading": "Skeleton cards",
    "empty": "Empty state message",
    "error": "Error banner"
  },
  "motion": "Brief animation note",
  "navigates_to": ["other-id"],
  "image_prompt": "A highly detailed AI image generation prompt to create a UI mockup of this exact screen. Include app name, brand color {$primary}, dark mode, glassmorphism, and specific UI elements."
}

Generate 8 to 12 screens. Reply ONLY with the JSON array starting with [ and ending with ].
PROMPT;

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user',   'content' => $userPrompt],
        ];

        $raw = $this->deepseek->chat($messages, true);
        file_put_contents(storage_path('logs/uiux_pass2_raw.txt'), $raw);

        // Extract the array
        $clean = trim($raw);
        $firstBracket = strpos($clean, '[');
        $lastBracket  = strrpos($clean, ']');

        if ($firstBracket === false) {
            // Maybe it returned an object with a "screens" key
            $parsed = $this->parseJson($clean, 'screens');
            if (isset($parsed['error'])) return $parsed;
            return $parsed['screens'] ?? $parsed;
        }

        if ($lastBracket === false) {
            // Truncated — attempt recovery by closing the array
            Log::warning('UiUxOrchestrator: screens array truncated, attempting recovery');
            $clean = $this->recoverTruncatedArray($clean, $firstBracket);
        } else {
            $clean = substr($clean, $firstBracket, $lastBracket - $firstBracket + 1);
        }

        $clean = $this->sanitizeJson($clean);
        $screens = json_decode($clean, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($screens)) {
            Log::error('UiUxOrchestrator: screens parse failed', [
                'error' => json_last_error_msg(),
                'first_300' => substr($clean, 0, 300),
                'last_300'  => substr($clean, -300),
            ]);
            return ['error' => 'Screens failed to parse', 'json_error' => json_last_error_msg()];
        }

        return $screens;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    protected function parseJson(string $raw, string $context): array
    {
        $clean = trim($raw);

        // Strip markdown fences if any
        $clean = preg_replace('/^```(?:json)?\s*/i', '', $clean);
        $clean = preg_replace('/\s*```$/i', '', $clean);
        $clean = trim($clean);

        $firstBrace = strpos($clean, '{');
        $lastBrace  = strrpos($clean, '}');

        if ($firstBrace === false) {
            Log::error("UiUxOrchestrator [{$context}]: No JSON object found", ['raw' => substr($raw, 0, 300)]);
            return ['error' => "No JSON found in {$context} response"];
        }

        if ($lastBrace === false) {
            Log::warning("UiUxOrchestrator [{$context}]: Truncated JSON, attempting recovery");
            $clean = $this->recoverTruncatedObject(substr($clean, $firstBrace));
        } else {
            $clean = substr($clean, $firstBrace, $lastBrace - $firstBrace + 1);
        }

        $clean = $this->sanitizeJson($clean);
        $result = json_decode($clean, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error("UiUxOrchestrator [{$context}]: JSON parse failed", [
                'error'     => json_last_error_msg(),
                'first_300' => substr($clean, 0, 300),
                'last_300'  => substr($clean, -300),
            ]);
            return ['error' => "{$context} failed to produce valid JSON", 'json_error' => json_last_error_msg()];
        }

        return $result;
    }

    /**
     * Attempt to recover a truncated JSON object by closing open brackets/braces.
     */
    protected function recoverTruncatedObject(string $partial): string
    {
        // Remove any trailing partial token (e.g., broken string or key)
        $partial = rtrim($partial);

        // Remove trailing comma if present
        $partial = rtrim($partial, ', ');

        // Count unclosed braces and brackets
        $depth  = 0;
        $inStr  = false;
        $escape = false;

        $braceStack   = [];
        $bracketStack = [];

        for ($i = 0; $i < strlen($partial); $i++) {
            $c = $partial[$i];

            if ($escape)           { $escape = false; continue; }
            if ($c === '\\' && $inStr) { $escape = true; continue; }
            if ($c === '"')        { $inStr = !$inStr; continue; }
            if ($inStr)            { continue; }

            if ($c === '{') $braceStack[]   = $i;
            if ($c === '}') array_pop($braceStack);
            if ($c === '[') $bracketStack[] = $i;
            if ($c === ']') array_pop($bracketStack);
        }

        // Close open brackets first (innermost), then braces
        $closing = str_repeat(']', count($bracketStack)) . str_repeat('}', count($braceStack));

        return $partial . $closing;
    }

    /**
     * Attempt to recover a truncated JSON array by closing open brackets/braces.
     */
    protected function recoverTruncatedArray(string $raw, int $startPos): string
    {
        $partial = substr($raw, $startPos);
        $partial = rtrim($partial, ', ');

        return $this->recoverTruncatedObject($partial);
    }

    /**
     * Generate a clean geometric SVG logo from brand data.
     */
    protected function attachGeneratedLogo(array $deliverable): array
    {
        $appName = $deliverable['brand']['app_name'] ?? 'APP';
        $initial = strtoupper(substr($appName, 0, 1));
        $color   = $deliverable['brand']['logo']['primary_color']
                ?? $deliverable['color_system']['palette']['primary']['hex']
                ?? '#6366f1';
        $shape   = $deliverable['brand']['logo']['shape'] ?? 'square';

        $deliverable['brand']['logo']['svg_mark'] = $this->buildSvgMark($initial, $color, $shape);

        return $deliverable;
    }

    protected function buildSvgMark(string $initial, string $color, string $shape): string
    {
        $bg = htmlspecialchars($color, ENT_QUOTES);

        return match ($shape) {
            'circle' => sprintf(
                '<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\'><circle cx=\'12\' cy=\'12\' r=\'12\' fill=\'%s\'/><text x=\'12\' y=\'16\' text-anchor=\'middle\' font-size=\'11\' font-weight=\'700\' fill=\'white\' font-family=\'system-ui\'>%s</text></svg>',
                $bg, $initial
            ),
            'diamond' => sprintf(
                '<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\'><rect x=\'4\' y=\'4\' width=\'16\' height=\'16\' rx=\'2\' fill=\'%s\' transform=\'rotate(45 12 12)\'/><text x=\'12\' y=\'16\' text-anchor=\'middle\' font-size=\'10\' font-weight=\'700\' fill=\'white\' font-family=\'system-ui\'>%s</text></svg>',
                $bg, $initial
            ),
            default => sprintf( // square / custom / hexagon
                '<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\'><rect width=\'24\' height=\'24\' rx=\'5\' fill=\'%s\'/><text x=\'12\' y=\'16\' text-anchor=\'middle\' font-size=\'11\' font-weight=\'700\' fill=\'white\' font-family=\'system-ui\'>%s</text></svg>',
                $bg, $initial
            ),
        };
    }

    /**
     * Robust JSON sanitizer — handles the most common LLM output issues:
     * 1. Literal newlines/tabs inside string values
     * 2. Unescaped double-quotes inside string values
     */
    protected function sanitizeJson(string $json): string
    {
        $result   = '';
        $len      = strlen($json);
        $inString = false;
        $i        = 0;

        while ($i < $len) {
            $char = $json[$i];

            // Already in an escape sequence — copy verbatim
            if ($char === '\\' && $inString) {
                $result .= $char;
                $i++;
                if ($i < $len) {
                    $result .= $json[$i];
                    $i++;
                }
                continue;
            }

            // Control characters must be escaped inside strings
            if ($inString) {
                if ($char === "\n") { $result .= '\\n'; $i++; continue; }
                if ($char === "\r") { $result .= '\\r'; $i++; continue; }
                if ($char === "\t") { $result .= '\\t'; $i++; continue; }
            }

            if ($char === '"') {
                if (!$inString) {
                    $inString = true;
                    $result  .= $char;
                } else {
                    // Peek ahead to decide: closing quote or mid-value bare quote?
                    $j = $i + 1;
                    while ($j < $len && $json[$j] === ' ') $j++;
                    $next = $j < $len ? $json[$j] : '';

                    if (in_array($next, [':', ',', '}', ']', "\n", "\r", ''], true) || $j >= $len) {
                        $inString = false;
                        $result  .= $char;
                    } else {
                        $result .= '\\"';
                    }
                }
            } else {
                $result .= $char;
            }

            $i++;
        }

        return $result;
    }
}
