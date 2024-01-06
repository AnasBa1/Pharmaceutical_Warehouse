<?php

namespace App\Http\Controllers;

use App\Models\BuyOrder;
use App\Models\BuyOrderItem;
use App\Models\Medication;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    public function ordersReport(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => "Validate error.",
                'errors' => $validator->errors()
            ], 422);
        }

        $startDate = Carbon::createFromFormat('d/m/Y', $request->input('start_date'));
        $endDate = Carbon::createFromFormat('d/m/Y', $request->input('end_date'));

        //Count all orders between two dates
        $allOrders = BuyOrder::whereBetween('created_at', [$startDate, $endDate])->count();

        //Count under preparing orders between two dates
        $underPreparingOrders = BuyOrder::where('order_status_id', 1)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        //Count shipped orders between two dates
        $shippedOrders = BuyOrder::where('order_status_id', 2)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        //Count received orders between two dates
        $receivedOrders = BuyOrder::where('order_status_id', 3)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        //Count refused orders between two dates
        $refusedOrders = BuyOrder::where('order_status_id', 4)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $medicationOrders = BuyOrderItem::query()->get()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('medication_id')
            ->all();

        $medicationsQuantitiesList = [];
        foreach ($medicationOrders as $medication => $orders) {
            $totalOrderedQuantity = 0;
            foreach ($orders as $order) {
                $totalOrderedQuantity += $order["ordered_quantity"];
            }
            $medicationsQuantitiesList[$medication] = $totalOrderedQuantity;
        }
        arsort($medicationsQuantitiesList);

        $topThreeOrderedMedications = array_slice($medicationsQuantitiesList, 0, 3, true);

        $topThreeOrderedMedicationNames = [];
        foreach ($topThreeOrderedMedications as $medicationId => $quantity){
            $medicationName = Medication::query()->withTrashed()->find($medicationId)->trade_name;
            $topThreeOrderedMedicationNames[] = [
                'medication_name' => $medicationName,
                'medication_quantity' => $quantity
            ];
        }

        return response()->json([
            'status' => true,
            'message' => 'The orders report has been returned successfully.',
            'data' => [
                'all_orders' => $allOrders,
                'under_preparing_orders' => $underPreparingOrders,
                'shipped_orders' => $shippedOrders,
                'received_orders' => $receivedOrders,
                'refused_orders' => $refusedOrders,
                'most_ordered_medications' => $topThreeOrderedMedicationNames,
            ]
        ]);
    }

    public function salesReport(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => "Validate error.",
                'errors' => $validator->errors()
            ], 422);
        }

        $startDate = Carbon::createFromFormat('d/m/Y', $request->input('start_date'));
        $endDate = Carbon::createFromFormat('d/m/Y', $request->input('end_date'));

        //Count all orders between two dates
        $allOrders = BuyOrder::whereBetween('created_at', [$startDate, $endDate])->count();

        //Count paid orders between two dates
        $paidOrders = BuyOrder::where('pay_status', true)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        //Count unpaid orders between two dates without refused and under preparing orders
        $unpaidOrders = BuyOrder::where('pay_status', false)
            ->where('order_status_id', '!=', 1)
            ->where('order_status_id', '!=', 4)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get()->count();

        // Calculate the total price for paid orders
        $totalPaidPrice = $paidOrders->sum('total_price');

        $medicationOrders = BuyOrderItem::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('buy_order_id', $paidOrders->pluck('id'))->get()
            ->groupBy('medication_id')
            ->all();

        $medicationsPaidList = [];
        foreach ($medicationOrders as $medication => $orders) {
            $totalOrderedQuantity = 0;
            foreach ($orders as $order) {
                $totalOrderedQuantity += $order["ordered_quantity"];
            }
            $medicationsPaidList[$medication] = $totalOrderedQuantity;
        }
        arsort($medicationsPaidList);

        $topThreePaidMedications = array_slice($medicationsPaidList, 0, 3, true);

        $topThreePaidMedicationsNames = [];
        foreach ($topThreePaidMedications as $medicationId => $quantity){
            $medicationName = Medication::query()->withTrashed()->find($medicationId)->trade_name;
            $topThreePaidMedicationsNames[] = [
                'medication_name' => $medicationName,
                'medication_quantity' => $quantity
            ];
        }

        return response()->json([
            'status' => true,
            'message' => 'The sales report has been returned successfully.',
            'data' => [
                'all_orders' => $allOrders,
                'paid_orders' => $paidOrders->count(),
                'unpaid_orders' => $unpaidOrders,
                'total_sales' => $totalPaidPrice,
                'most_paid_medications' => $topThreePaidMedicationsNames,
            ]
        ]);
    }
}
