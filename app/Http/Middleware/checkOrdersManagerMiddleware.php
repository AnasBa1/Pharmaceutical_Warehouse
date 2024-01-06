<?php

namespace App\Http\Middleware;

use App\Http\Controllers\BuyOrderController;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class checkOrdersManagerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if($request->user()->role != 'manager'){
            return app(BuyOrderController::class)->listUserOrders($request);
        }

        return $next($request);
    }
}
