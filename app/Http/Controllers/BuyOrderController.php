<?php

namespace App\Http\Controllers;

use App\Models\BuyOrder;
use App\Models\BuyOrderItem;
use App\Models\Medication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BuyOrderController extends Controller
{
    public function listAllOrders(): JsonResponse
    {
        $orders = BuyOrder::query()->join('users', 'user_id', '=', 'users.id')
            ->join('order_statuses', 'order_status_id', '=', 'order_statuses.id')
            ->orderBy('buy_orders.id', 'desc')
            ->select('buy_orders.id', 'users.username', 'users.phone_number', 'buy_orders.pay_status', 'order_statuses.status', 'buy_orders.order_status_id', 'buy_orders.total_price', 'buy_orders.created_at')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'The buy orders list has been successfully retrieved.',
            'data' => $orders
        ]);
    }

    public function listUserOrders(): JsonResponse
    {
        $userId = Auth::user()->getAuthIdentifier();

        $orders = BuyOrder::query()->join('users', 'user_id', '=', 'users.id')
            ->join('order_statuses', 'order_status_id', '=', 'order_statuses.id')
            ->where('buy_orders.user_id', '=', $userId)
            ->orderBy('buy_orders.id', 'desc')
            ->select('buy_orders.id', 'users.username', 'users.phone_number', 'buy_orders.pay_status', 'order_statuses.status', 'buy_orders.order_status_id', 'buy_orders.total_price', 'buy_orders.created_at')
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
            ->select('buy_orders.id', 'users.username', 'users.phone_number', 'buy_orders.pay_status', 'order_statuses.status', 'buy_orders.order_status_id', 'buy_orders.total_price', 'buy_orders.created_at')
            ->first();

        $medications = BuyOrderItem::query()
            ->join('medications', 'medication_id', '=', 'medications.id')
            ->join('medical_classifications', 'medical_classification_id', '=', 'medical_classifications.id')
            ->where('buy_order_items.buy_order_id', '=', $id)
            ->select('medications.id', 'medications.trade_name', 'buy_order_items.ordered_quantity', 'medications.price')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'The buy order has been found successfully.',
            'data' => [
                'order_details' => $order,
                'medications' => $medications
                ]
        ]);
    }

    public function createOrder(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'medications' => ['array', 'present'],
            'medications.*.medication_id' => ['required', 'exists:medications,id'],
            'medications.*.ordered_quantity' => ['required', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => "Validate error.",
                'errors' => $validator->errors()
            ], 422);
        }

        $totalPrice = 0;

        //Check if I have enough available quantity for the ordered medications
        foreach ($request['medications'] as $medication) {
            $orderedMedication = Medication::query()->withTrashed()
                ->where('medications.id', '=', $medication['medication_id'])
                ->first();
            if ($orderedMedication->available_quantity < $medication['ordered_quantity']) {
                $unavailableMedications[] = [
                    'id' => $orderedMedication->id,
                    'trade_name' => $orderedMedication->trade_name,
                    'available_quantity' => $orderedMedication->available_quantity,
                    'ordered_quantity' => (int)$medication['ordered_quantity'],
                ];
            }
            $totalPrice += $medication['ordered_quantity'] * $orderedMedication->price;
        }

        //Return a failed message when the order have unavailable medications
        if (isset($unavailableMedications)){
            return response()->json([
                'status' => false,
                'message' => 'Sorry, the ordered medications quantities is more than the available.',
                'data' => $unavailableMedications
            ], 400);
        }

        $userId = Auth::user()->getAuthIdentifier();

        //Create the order where do all operations together on DB successfully or do nothing
        $orderData = null;
        try {
            DB::beginTransaction();
            $buyOrder = BuyOrder::query()->create([
                'user_id' => $userId,
                'total_price' => $totalPrice
            ]);

            foreach ($request['medications'] as $medication){
                BuyOrderItem::query()->create([
                    'buy_order_id' => $buyOrder['id'],
                    'medication_id' => $medication['medication_id'],
                    'ordered_quantity' => $medication['ordered_quantity'],
                ]);
            }

            $orderData = $this->showOrder($buyOrder['id'])->original['data'];

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to create your order, please try again later.',
                'data' => [],
            ], 500);
        }

        return response()->json([
            'status' => true,
            'message' => 'The buy order has been created successfully.',
            'data' => $orderData,
        ], 201);
    }

    public function changeOrderStatus($id): JsonResponse
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

        $orderData = $this->showOrder($id)->original['data'];
        $statusId = $orderData['order_details']['order_status_id'];

        //Don't change the order status if the order refused or received
        if ($statusId == 3 || $statusId == 4){
            return response()->json([
                'status' => false,
                'message' => 'You can\'t change the order status because it\'s ' . $orderData['order_details']['status'] . '.',
                'data' => $orderData
            ], 400);
        }

        //change the order status if the shipped
        else if ($statusId == 2){
            BuyOrder::query()->find($id)->update([
                'order_status_id' => 3,
            ]);
        }

        //change the status for a new order or refuse it if I don't have enough quantity to fulfil the order
        else {
            $orderItems = BuyOrderItem::query()->where('buy_order_id', '=', $id)->get();

            foreach ($orderItems as $orderItem) {
                $orderedMedication = Medication::query()->withTrashed()
                    ->where('medications.id', '=', $orderItem['medication_id'])
                    ->first();
                if ($orderedMedication->available_quantity < $orderItem->ordered_quantity) {
                    $unavailableMedication[] = [
                        'id' => $orderedMedication->id,
                        'trade_name' => $orderedMedication->trade_name,
                        'available_quantity' => $orderedMedication->available_quantity,
                        'ordered_quantity' => $orderItem->ordered_quantity,
                    ];
                }
            }

            //refuse the order if I don't have enough quantity to fulfil the order
            if (isset($unavailableMedication)){
                BuyOrder::query()->find($id)->update([
                    'order_status_id' => 4,
                ]);

                return response()->json([
                    'status' => false,
                    'message' => 'Sorry, the order has been refused, because the ordered medications quantities is more than the available.',
                    'data' => $unavailableMedication
                ], 400);
            }

            //ship the order and reduce the shipped quantities medications where do all operations together on DB successfully or do nothing
            try {
                DB::beginTransaction();

                //Reduce the available quantity in the warehouse
                foreach ($orderItems as $orderItem) {
                        Medication::query()->withTrashed()
                            ->where('id', '=', $orderItem->medication_id)
                            ->decrement('available_quantity', $orderItem->ordered_quantity);
                }

                BuyOrder::query()->find($id)->update([
                    'order_status_id' => 2,
                ]);

                DB::commit();
            } catch (\Exception $exception) {
                DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to change order status, please try again later.',
                'data' => [],
            ], 500);
            }
        }

        $orderData = $this->showOrder($id)->original['data'];

        return response()->json([
            'status' => true,
            'message' => 'The buy order status has been updated successfully.',
            'data' => $orderData
        ]);
    }

    public function changeOrderPayStatus($id): JsonResponse
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

        $orderData = $this->showOrder($id)->original['data'];

        if ($orderData['order_details']['pay_status']){
            return response()->json([
                'status' => false,
                'message' => 'Sorry, the ordered is already paid.',
                'data' => $orderData
            ], 400);
        }

        if ($orderData['order_details']['order_status_id'] == 1){
            return response()->json([
                'status' => false,
                'message' => 'You can\'t change the pay status before the order is shipped.',
                'data' => $orderData
            ], 400);
        } else if ($orderData['order_details']['order_status_id'] == 4){
            return response()->json([
                'status' => false,
                'message' => 'Sorry, the order is refused you can\'t change pay status to paid.',
                'data' => $orderData
            ], 400);
        }

        BuyOrder::query()->find($id)->update([
            'pay_status' => true,
        ]);

        $orderData = $this->showOrder($id)->original['data'];

        return response()->json([
            'status' => true,
            'message' => 'The pay status of the order has been updated successfully.',
            'data' => $orderData
        ]);
    }
}
