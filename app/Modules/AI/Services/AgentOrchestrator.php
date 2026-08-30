<?php

namespace App\Modules\AI\Services;

use App\Modules\AI\Providers\DeepSeekProvider;

class AgentOrchestrator
{
    protected DeepSeekProvider $deepseek;

    public function __construct(DeepSeekProvider $deepseek)
    {
        $this->deepseek = $deepseek;
    }

    /**
     * Runs the Master Architect pipeline in a single DeepSeek call.
     */
    public function orchestrate(string $prompt, ?callable $onProgress = null): array
    {
        if ($onProgress) {
            $onProgress([
                'agent' => 1,
                'name' => 'Master Architect',
                'seniority' => 'Principal Software & Security Architect',
                'emoji' => '🚀',
                'model' => 'deepseek',
                'model_label' => 'DeepSeek V3',
                'status' => 'analyzing',
                'excerpt' => 'Analyzing requirements and generating system architecture...',
            ]);
        }

        $systemPrompt = <<<PROMPT
You are the Master Architect, an elite Principal Software & Security Architect.
You must design a production-ready, highly scalable, and exceptionally secure software system based on the user's requirements.

You must apply strict "Fintech-grade" engineering standards:
1. TECH STACK: The backend MUST be either Laravel (PHP) or Java. The database MUST be MySQL. You may choose the frontend framework based on the best fit.
2. SECURITY: Enforce idempotency keys for mutations, strict RBAC, rate-limiting, and prevention of race conditions using atomic database locks or transactions.
3. PERFORMANCE: Identify and mitigate N+1 query risks. Suggest precise database indexing strategies.
4. ARCHITECTURE: Design clean, scalable service layers.
5. DIAGRAMS: You must output valid Mermaid.js code for an Architecture Flowchart, a UML Class Diagram, and an ER Diagram (database schema). 
   CRITICAL RULES FOR MERMAID:
   - YOU MUST NOT USE QUOTES OF ANY KIND (no double quotes, no single quotes).
   - YOU MUST NOT write cardinality labels (like "1", "*", "0..*"). Just draw the arrows (e.g., A --> B).
   - Any use of double quotes inside the Mermaid strings will break the JSON parser and cause a critical failure.

Your output MUST be a strict, valid JSON object with EXACTLY the following structure (do not wrap in markdown):

{
  "project_title": "String",
  "executive_summary": "String",
  "architecture_pattern": "String (e.g., Modular Monolith, Microservices)",
  "architecture_mermaid": "String (valid Mermaid.js graph TD code)",
  "tech_stack": {
    "rationale": "String explaining the choice of tech stack",
    "frontend": "String",
    "backend": "String (Must be Laravel or Java)",
    "database": "String (Must be MySQL)",
    "infrastructure": "String"
  },
  "erd_mermaid": "String (valid Mermaid.js erDiagram code)",
  "db_performance_report": {
    "n_plus_one_risks": [
      {
        "table": "string",
        "relationship": "string",
        "fix": "string"
      }
    ],
    "missing_indexes": [
      {
        "table": "string",
        "column": "string",
        "reason": "string"
      }
    ],
    "query_budgets": [
      {
        "endpoint": "string",
        "naive_query_count": 0,
        "budget": 0,
        "fix": "string"
      }
    ]
  },
  "uml_mermaid": "String (valid Mermaid.js classDiagram code)",
  "api_contracts": [
    {
      "endpoint": "/api/v1/resource",
      "method": "POST",
      "description": "string",
      "auth_required": true,
      "roles": ["admin"],
      "idempotency": "string",
      "rate_limit": "string",
      "request_body": "string",
      "response": "string"
    }
  ],
  "security_report": {
    "critical": [
      {
        "category": "string",
        "description": "string",
        "impact": "string",
        "mitigation": "string"
      }
    ],
    "high": [],
    "medium": []
  },
  "future_features": [
    {
      "title": "String",
      "description": "String",
      "business_value": "String"
    }
  ]
}
PROMPT;

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $prompt]
        ];

        // Ensure we request JSON mode from DeepSeek
        $rawOutput = $this->deepseek->chat($messages, true);

        // Extract JSON block (strip any markdown wrapper or conversational text)
        $cleanJson = trim($rawOutput);
        $firstBrace = strpos($cleanJson, '{');
        $lastBrace  = strrpos($cleanJson, '}');
        if ($firstBrace !== false && $lastBrace !== false) {
            $cleanJson = substr($cleanJson, $firstBrace, $lastBrace - $firstBrace + 1);
        }

        // Pre-sanitize: walk the JSON char-by-char and escape any unescaped double-quotes
        // that appear INSIDE a JSON string value. DeepSeek frequently injects cardinality
        // labels like "1", "*", "0..*" unescaped inside Mermaid string values.
        $cleanJson = $this->fixUnescapedQuotes($cleanJson);

        $deliverable = json_decode($cleanJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $deliverable = [
                'error'      => 'Master Architect failed to produce valid JSON',
                'json_error' => json_last_error_msg(),
                'raw'        => $rawOutput,
            ];
        }

        if ($onProgress) {
            $onProgress([
                'agent' => 1,
                'name' => 'Master Architect',
                'status' => 'complete',
                'excerpt' => 'Architecture blueprint generated successfully.',
            ]);
        }

        return [
            'agent_log' => [], // Empty because there is no multi-agent log anymore
            'deliverable' => $deliverable
        ];
    }

    /**
     * Walk a JSON string character-by-character and escape any bare double-quotes
     * that appear inside string values. This repairs the common LLM mistake of
     * writing Mermaid cardinality labels like "1", "*", "0..*" unescaped.
     */
    protected function fixUnescapedQuotes(string $json): string
    {
        $result   = '';
        $len      = strlen($json);
        $inString = false;  // are we currently inside a JSON string?
        $i        = 0;

        while ($i < $len) {
            $char = $json[$i];
            $prev = $i > 0 ? $json[$i - 1] : '';

            if ($char === '\\' && $inString) {
                // An escape sequence — copy both the backslash and the next char verbatim
                $result .= $char;
                $i++;
                if ($i < $len) {
                    $result .= $json[$i];
                    $i++;
                }
                continue;
            }

            if ($char === '"') {
                if (!$inString) {
                    // Opening quote — we are now inside a string
                    $inString = true;
                    $result  .= $char;
                } else {
                    // Could be a closing quote OR an unescaped quote inside the string.
                    // Peek ahead: if the next non-space character is one of the JSON structural
                    // characters (:  ,  }  ]) then this is a proper closing quote.
                    $j    = $i + 1;
                    while ($j < $len && $json[$j] === ' ') {
                        $j++;
                    }
                    $next = $j < $len ? $json[$j] : '';

                    if (in_array($next, [':', ',', '}', ']', "\n", "\r"], true) || $j >= $len) {
                        // Proper closing quote
                        $inString = false;
                        $result  .= $char;
                    } else {
                        // Bare/unescaped quote inside a string value — escape it
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
