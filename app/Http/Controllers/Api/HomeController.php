<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomOrder;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Throwable;

class HomeController extends Controller
{
    /**
     * Return home-page data for the authenticated user.
     *
     * Includes:
     * 1. Current-month active subscriptions
     * 2. Current-month customized flower orders
     */
    public function currentMonthSubscriptions(
        Request $request
    ): JsonResponse {
        try {
            /*
             * Get authenticated user from the Sanctum token.
             */
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            /*
             * Current date according to config/app.php timezone.
             */
            $now = Carbon::now();

            $monthStart = $now
                ->copy()
                ->startOfMonth()
                ->toDateString();

            $monthEnd = $now
                ->copy()
                ->endOfMonth()
                ->toDateString();

            /*
             * Get paid and active subscriptions that overlap
             * with the current month.
             */
            $subscriptions = Subscription::query()
                ->where('user_id', $user->id)
                ->where('payment_status', 'Paid')
                ->where('subscription_status', 'Active')
                ->whereDate('start_date', '<=', $monthEnd)
                ->whereDate('end_date', '>=', $monthStart)
                ->with([
                    'packet',
                    'address',
                ])
                ->orderByDesc('start_date')
                ->get();

            /*
             * Get customized flower orders belonging to the user.
             *
             * The orders are filtered by current-month delivery date.
             * Every order includes its address, items and flower details.
             */
            $customOrders = CustomOrder::query()
                ->where('user_id', $user->id)
                ->whereDate('delivery_date', '>=', $monthStart)
                ->whereDate('delivery_date', '<=', $monthEnd)
                ->with([
                    'address',
                    'items.flower',
                ])
                ->orderByDesc('delivery_date')
                ->orderByDesc('id')
                ->get();

            /*
             * Customized-order status summary.
             */
            $customOrderSummary = [
                'order_placed' => $customOrders
                    ->where('order_status', 'Order Placed')
                    ->count(),

                'confirmed' => $customOrders
                    ->where('order_status', 'Confirmed')
                    ->count(),

                'preparing' => $customOrders
                    ->where('order_status', 'Preparing')
                    ->count(),

                'ready_for_delivery' => $customOrders
                    ->where('order_status', 'Ready for Delivery')
                    ->count(),

                'out_for_delivery' => $customOrders
                    ->where('order_status', 'Out for Delivery')
                    ->count(),

                'delivered' => $customOrders
                    ->where('order_status', 'Delivered')
                    ->count(),

                'cancelled' => $customOrders
                    ->where('order_status', 'Cancelled')
                    ->count(),
            ];

            return response()->json([
                'status' => true,
                'message' => 'Home data fetched successfully.',

                'month' => $now->format('F Y'),

                'period' => [
                    'start_date' => $monthStart,
                    'end_date' => $monthEnd,
                ],

                /*
                 * Subscription data.
                 *
                 * The original "data" key is maintained so existing
                 * mobile-application code continues working.
                 */
                'has_subscription' => $subscriptions->isNotEmpty(),
                'total_subscriptions' => $subscriptions->count(),
                'data' => $subscriptions,

                /*
                 * Customized-order data.
                 */
                'has_custom_orders' => $customOrders->isNotEmpty(),
                'total_custom_orders' => $customOrders->count(),
                'custom_order_summary' => $customOrderSummary,
                'custom_orders' => $customOrders,
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'status' => false,
                'message' => 'Unable to fetch home data.',

                'error' => config('app.debug')
                    ? $e->getMessage()
                    : null,
            ], 500);
        }
    }
}