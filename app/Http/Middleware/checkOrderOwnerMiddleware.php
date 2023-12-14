<?php

namespace App\Http\Middleware;

use App\Models\BuyOrder;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class checkOrderOwnerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if($request->user()->role == 'manager'){
            return $next($request);
        }

        $orderId = $request->route('id');

        $orderOwnerId = BuyOrder::query()
            ->where('buy_orders.id', '=', $orderId)
            ->select('buy_orders.user_id')
            ->first();

        if (!is_null($orderOwnerId) && $orderOwnerId->user_id == $request->user()->id){
            return $next($request);
        }

        return response()->json([
            'status' => false,
            'message' => "You are not authorized to show this order.",
            'data' => []
        ], 403);
    }
}
