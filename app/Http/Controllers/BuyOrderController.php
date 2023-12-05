<?php

namespace App\Http\Controllers;

use App\Models\BuyOrder;
use App\Models\BuyOrderItem;
use App\Models\Medication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BuyOrderController extends Controller
{
    public function listAllOrders(): JsonResponse
    {
        $orders = BuyOrder::query()->join('users', 'user_id', '=', 'users.id')
            ->join('order_statuses', 'order_status_id', '=', 'order_statuses.id')
            ->select('buy_orders.id', 'users.username', 'buy_orders.pay_status', 'order_statuses.status', 'buy_orders.order_status_id', 'buy_orders.created_at')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'The buy orders list has been successfully retrieved.',
            'data' => $orders
        ]);
    }

    public function showOrder($id): JsonResponse
    {

        $validator = Validator::make(['id' => $id], [
            'id' => ['exists:buy_orders,id'],
        ],
        [
            'id.exists' => 'The selected order does not exists.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => "Validate error.",
                'errors' => $validator->errors()
            ], 422);
        }

        $order = BuyOrder::query()
            ->join('users', 'user_id', '=', 'users.id')
            ->join('order_statuses', 'order_status_id', '=', 'order_statuses.id')
            ->where('buy_orders.id', '=', $id)
            ->select('buy_orders.id', 'users.username', 'buy_orders.pay_status', 'order_statuses.status', 'buy_orders.order_status_id', 'buy_orders.created_at')
            ->first();

        $medications = BuyOrderItem::query()
            ->join('medications', 'medication_id', '=', 'medications.id')
            ->join('medical_classifications', 'medical_classification_id', '=', 'medical_classifications.id')
            ->where('buy_order_items.buy_order_id', '=', $id)
            ->select('medications.id', 'medications.trade_name', 'buy_order_items.ordered_quantity', 'medications.price')
            ->get();

//        $order = BuyOrderItem::query()/*->select('medications.trade_name', 'buy_order_items.ordered_quantity')*/
//            ->join('medications', 'medication_id', '=', 'medications.id')
//            ->join('medical_classifications', 'medical_classification_id', '=', 'medical_classifications.id')
//            ->join('buy_orders', 'buy_order_id', '=', 'buy_orders.id')
//            ->join('order_statuses', 'order_status_id', '=', 'order_statuses.id')
//            ->where('buy_order_items.buy_order_id', '=', $id)
//            ->get();

//            $order = BuyOrder::query()->with(['order_status', 'user', 'buy_order_items'])
//                ->where('buy_orders.id', '=', $id)
//                ->get();

//        $order = BuyOrder::query()->join('buy_order_items', 'buy_orders.id', '=', 'buy_order_items.buy_order_id')
//            ->where('buy_orders.id', '=', $id)->get();


        return response()->json([
            'status' => true,
            'message' => 'The buy order has been found successfully',
            'data' => [
                'order_details' => $order,
                'medications' => $medications
                ]
        ]);
    }

    public function changeOrderStatus(Request $request, $id): JsonResponse
    {
        $request['id'] = $id;
        $validator = Validator::make($request->all(), [
            'pay_status' => ['required', 'boolean'],
            'order_status_id' => ['required', 'exists:order_statuses,id'],
            'id' => ['exists:buy_orders,id']
        ],
        [
            'id.exists' => 'The selected order does not exists.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => "Validate error",
                'errors' => $validator->errors()
            ], 422);
        }

//        //prevent the user from change the status to a previous status
//        if (BuyOrder::query()->find($id)->order_status_id >= $request['order_status_id']){
//            return response()->json([
//                'status' => false,
//                'message' => "You have change the order status in correct order.",
//                'data' => []
//            ], 422);
//        }

        BuyOrder::query()
            ->find($id)
            ->update([
                'pay_status' => $request['pay_status'],
                'order_status_id' => $request['order_status_id']
            ]);

//        $order = BuyOrder::query()
//            ->join('order_statuses', 'order_status_id', '=', 'order_statuses.id')
//            ->where('buy_orders.id', '=', $id)->first();

        $order = BuyOrder::query()
            ->join('users', 'user_id', '=', 'users.id')
            ->join('order_statuses', 'order_status_id', '=', 'order_statuses.id')
            ->where('buy_orders.id', '=', $id)
            ->select('buy_orders.id', 'users.username', 'buy_orders.pay_status', 'order_statuses.status', 'buy_orders.order_status_id', 'buy_orders.created_at')
            ->first();

        //decrement the quantity of medications after change the order status to Shipped
        if ($request['order_status_id'] == 2) {
            $orderItems = BuyOrderItem::query()->where('buy_order_id', '=', $id)->get();

            foreach ($orderItems as $orderItem) {
                $medicationOrderedQuantity = Medication::query()
                    ->where('medications.id', '=', $orderItem->medication_id)
                    ->first()->available_quantity;
                if ($medicationOrderedQuantity >= $orderItem->ordered_quantity) {
                    Medication::query()
                        ->where('id', '=', $orderItem->medication_id)
                        ->decrement('available_quantity', $orderItem->ordered_quantity);
                }
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'The buy order status has been successfully updated.',
            'data' => $order
        ]);
    }
}
