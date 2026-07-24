<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Throwable;

class HomeController extends Controller
{
    /**
     * Return the logged-in user's subscriptions
     * that are active during the current month.
     */
    public function currentMonthSubscriptions(
        Request $request
    ): JsonResponse {
        try {
            /*
             * Carbon uses the timezone configured in config/app.php.
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
             * A subscription belongs to the current month when:
             *
             * start_date <= last day of this month
             * AND
             * end_date >= first day of this month
             *
             * This also includes subscriptions that started in an
             * earlier month but remain active this month.
             */
            $subscriptions = Subscription::query()
                ->where(
                    'user_id',
                    $request->user()->id
                )
                ->where(
                    'payment_status',
                    'Paid'
                )
                ->where(
                    'subscription_status',
                    'Active'
                )
                ->whereDate(
                    'start_date',
                    '<=',
                    $monthEnd
                )
                ->whereDate(
                    'end_date',
                    '>=',
                    $monthStart
                )
                ->with([
                    'packet',
                    'address',
                ])
                ->orderByDesc('start_date')
                ->get();

            return response()->json([
                'status' => true,
                'message' =>
                    'Current month subscriptions fetched successfully.',

                'month' => $now->format('F Y'),

                'period' => [
                    'start_date' => $monthStart,
                    'end_date' => $monthEnd,
                ],

                'has_subscription' =>
                    $subscriptions->isNotEmpty(),

                'total_subscriptions' =>
                    $subscriptions->count(),

                'data' => $subscriptions,
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'status' => false,
                'message' =>
                    'Unable to fetch current month subscriptions.',

                'error' => config('app.debug')
                    ? $e->getMessage()
                    : null,
            ], 500);
        }
    }
}