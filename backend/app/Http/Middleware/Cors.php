<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Cors
{
    public function handle(Request $request, Closure $next)
    {
        $config = config('cors');

        $allowedOrigins = $config['allowed_origins'] ?? ['*'];
        $allowedMethods = $config['allowed_methods'] ?? ['*'];
        $allowedHeaders = $config['allowed_headers'] ?? ['*'];
        $supportsCredentials = $config['supports_credentials'] ?? false;

        $headers = [
            'Access-Control-Allow-Origin' => is_array($allowedOrigins) ? implode(', ', $allowedOrigins) : $allowedOrigins,
            'Access-Control-Allow-Methods' => is_array($allowedMethods) ? implode(', ', $allowedMethods) : $allowedMethods,
            'Access-Control-Allow-Headers' => is_array($allowedHeaders) ? implode(', ', $allowedHeaders) : $allowedHeaders,
        ];

        if ($supportsCredentials) {
            $headers['Access-Control-Allow-Credentials'] = 'true';
        }

        if ($request->getMethod() === 'OPTIONS') {
            return response('', 200, $headers);
        }

        $response = $next($request);

        foreach ($headers as $key => $value) {
            $response->headers->set($key, $value);
        }

        return $response;
    }
}
