<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogRequestsAndResponses
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $contents = json_decode($response->getContent(), true);

        $headers = $request->header();

        $data = [
            'Path' => $request->getPathInfo(),
            'Method' => $request->getMethod(),
            'IP' => $request->ip(),
            'HTTP Version' => $_SERVER['SERVER_PROTOCOL'],
            'Headers' => $headers,
            'request' => $request->all(),
        ];

        // If request is authenticated
        if ($request->user()) {
            $data['User ID'] = $request->user()->id;
        }

        // Log request information
        Log::info("Request Info - {$request->getPathInfo()}", $data);

        // Log response information
        Log::info("Response Info - {$request->getPathInfo()}", ['Status Code' => $response->status(), 'Response' => $contents]);

        // Return the response
        return $response;
    }
}
