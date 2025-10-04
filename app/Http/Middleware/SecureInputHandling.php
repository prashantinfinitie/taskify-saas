<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

class SecureInputHandling
{
    /**
     * Potential SQL Injection Patterns
     * These regex patterns detect common SQL injection techniques
     */
    protected $sqlPatterns = [
        '/\b(SELECT|INSERT|UPDATE|DELETE|DROP|UNION|ALTER|CREATE)\b/i',
        '/\b(FROM|WHERE|OR)\b/i',
        '/\b(1=1|--|\/\*|\*\/|;)\b/i',
        '/\b(CONCAT|CHAR|ASCII|HEX)\b/i',
        '/\s*[\'"]?\s*\b(AND|OR)\b\s*[\'"]?\s*\d+\s*[=<>]\s*\d+/i',
        '/\/\*.*\*\//i',
        '/--.*$/m',
        '/\b(EXEC|PROCEDURE)\b/i',
    ];

    /**
     * XSS Patterns to Detect and Remove
     * Identifies potential cross-site scripting attack vectors
     */
    protected $xssPatterns = [
        '/<script\b[^>]*>(.*?)<\/script>/is',
        '/on\w+=/i',
        '/javascript:/i',
        '/vbscript:/i',
    ];

    /**
     * Rich Text Fields That Require Special Handling
     * These fields will bypass standard sanitization
     */
    protected $richTextFields = [
        'description',
        'content',
        'message',
        'body',
        'details',
        'footer_text',
        'visible_columns',
        'company_address',
        'contract_description',
        'modules',
        'tenurePrices',
        'discountedPrices',
        'custom_fields',
        'project_description',
        'task_description',
        'comment_body',
        'features',
        'refund_policy',
        'terms_and_conditions',
        'privacy_policy',
        'title',
        'support_email',
        "signatureImage",
        "field_type",
        "openrouter_api_key",
        "fields[]"
    ];

    /**
     * Allowed HTML Tags for Rich Text Fields
     * Provides a safe set of HTML tags for rich text inputs
     */
    protected $allowedRichTextTags = [
        'p',
        'br',
        'strong',
        'b',
        'i',
        'em',
        'u',
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6',
        'ul',
        'ol',
        'li',
        'table',
        'thead',
        'tbody',
        'tr',
        'th',
        'td',
        'a',
        'span',
        'div',
        'img'  // Be cautious with img tags
    ];

    /**
     * Handle incoming request
     * Sanitizes input for modifying HTTP methods
     */
    public function handle(Request $request, Closure $next): Response
    {
        // dd($request);


        $excludedRoutes = [
            'superadmin/settings/languages/save_labels',
            'master-panel/lead-forms/update/*',
            'master-panel/lead-forms/store'
        ];

        foreach ($excludedRoutes as $route) {
            if (Str::is($route, $request->path())) {
                return $next($request); // Skip middleware for excluded route
            }
        }

        // Only process methods that modify data
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
            try {
                $input = $request->all();
                $sanitizedInput = $this->comprehensiveSanitize($input);
                $request->replace($sanitizedInput);
            } catch (\Exception $e) {
                // Log sanitization errors without breaking request flow
                Log::warning('Security middleware sanitization error: ' . $e->getMessage());
            }
        }

        // Process the request
        $response = $next($request);

        // Add robust security headers
        return $this->addSecurityHeaders($response);
    }

    /**
     * Comprehensive Input Sanitization
     * Recursively handles different input types with context-aware sanitization
     */
    protected function comprehensiveSanitize(array $input): array
    {
        $sanitized = [];

        foreach ($input as $key => $value) {
            // Recursively handle nested arrays
            if (is_array($value)) {
                $sanitized[$key] = $this->comprehensiveSanitize($value);
            } elseif (is_string($value)) {
                // Special handling for rich text fields
                if (in_array($key, $this->richTextFields)) {
                    $sanitized[$key] = $this->sanitizeRichText($value);
                } else {
                    // Standard input sanitization
                    $sanitized[$key] = $this->preventXssAndSqlInjection($value);
                }
            } else {
                // Preserve non-string values
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Sanitize Rich Text Content
     * Allows specific HTML tags while removing potential security risks
     */
    protected function sanitizeRichText(string $value): string
    {
        // Remove script tags and event handlers
        $value = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $value);
        $value = preg_replace('/on\w+\s*=\s*[\'"].*?[\'"]/i', '', $value);

        // Remove potentially malicious HTML attributes
        $value = preg_replace('/\s+(href|src)=([\'"])\s*javascript:/i', '', $value);

        // Strip out any tags not in the allowed list
        $value = strip_tags($value, '<' . implode('><', $this->allowedRichTextTags) . '>');

        return $value;
    }

    /**
     * Prevent XSS and SQL Injection
     * Detects and neutralizes potential security threats in standard inputs
     */
    protected function preventXssAndSqlInjection(string $value): string
    {
        // Replace XSS patterns with readable markers
        $xssReplacements = [
            '/<script\b[^>]*>(.*?)<\/script>/is' => '[BLOCKED SCRIPT CONTENT]',
            '/on\w+=/i' => '[BLOCKED EVENT HANDLER]',
            '/javascript:/i' => '[BLOCKED JAVASCRIPT PROTOCOL]',
            '/vbscript:/i' => '[BLOCKED VBSCRIPT PROTOCOL]',
        ];

        foreach ($xssReplacements as $pattern => $replacement) {
            $value = preg_replace($pattern, $replacement, $value);
        }

        // Detect SQL Injection attempts
        foreach ($this->sqlPatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                Log::warning("Potential SQL Injection Detected: {$value}");
                $value = '';
                break;
            }
        }

        // Final HTML encoding for additional protection
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

        return $value;
    }

    /**
     * Add Security Headers
     * Enhances browser-level security
     */
    protected function addSecurityHeaders($response)
    {
        // Skip adding headers for file responses
        if ($response instanceof BinaryFileResponse) {
            return $response;
        }

        // Add security headers for normal responses
        $headers = [
            'X-XSS-Protection' => '1; mode=block',
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy' => 'strict-origin-when-cross-origin'
        ];

        foreach ($headers as $key => $value) {
            $response->headers->set($key, $value);
        }

        return $response;
    }
}
