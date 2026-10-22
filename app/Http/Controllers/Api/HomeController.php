<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomOrder;
use App\Models\HomeBanner;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Throwable;

class HomeController extends Controller
{
    /**
     * Public API for the mobile home slider: GET /api/home/banners.
     * Admin manages images, destinations, active flags and schedule.
     */
    public function banners(): JsonResponse
    {
        try {
            $now = Carbon::now();

            $banners = HomeBanner::query()
                ->where('is_active', true)
                ->where(function ($query) use ($now): void {
                    $query->whereNull('starts_at')
                        ->orWhere('starts_at', '<=', $now);
                })
                ->where(function ($query) use ($now): void {
                    $query->whereNull('ends_at')
                        ->orWhere('ends_at', '>=', $now);
                })
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->map(function (HomeBanner $banner): array {
                    return [
                        'id' => $banner->id,
                        'title' => $banner->title,
                        'image_url' => $banner->image_url,
                        'target_screen' => $banner->target_screen,
                        'sort_order' => $banner->sort_order,
                        'updated_at' => $banner->updated_at?->toIso8601String(),
                    ];
                })->values();

            return response()->json([
                'status' => true,
                'message' => 'Home banners fetched successfully.',
                'data' => $banners,
            ])->header('Cache-Control', 'no-store, max-age=0');
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'status' => false,
                'message' => 'Unable to load home banners.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Return home-page data for the authenticated user.
     * Includes current-month active subscriptions and customized flower orders.
     */
    public function currentMonthSubscriptions(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            $now = Carbon::now();
            $monthStart = $now->copy()->startOfMonth()->toDateString();
            $monthEnd = $now->copy()->endOfMonth()->toDateString();

            $subscriptions = Subscription::query()
                ->where('user_id', $user->id)
                ->where('payment_status', 'Paid')
                ->where('subscription_status', 'Active')
                ->whereDate('start_date', '<=', $monthEnd)
                ->whereDate('end_date', '>=', $monthStart)
                ->with(['packet', 'address'])
                ->orderByDesc('start_date')
                ->get();

            $customOrders = CustomOrder::query()
                ->where('user_id', $user->id)
                ->whereDate('delivery_date', '>=', $monthStart)
                ->whereDate('delivery_date', '<=', $monthEnd)
                ->with(['address', 'items.flower'])
                ->orderByDesc('delivery_date')
                ->orderByDesc('id')
                ->get();

            $customOrderSummary = [
                'order_placed' => $customOrders->where('order_status', 'Order Placed')->count(),
                'confirmed' => $customOrders->where('order_status', 'Confirmed')->count(),
                'preparing' => $customOrders->where('order_status', 'Preparing')->count(),
                'ready_for_delivery' => $customOrders->where('order_status', 'Ready for Delivery')->count(),
                'out_for_delivery' => $customOrders->where('order_status', 'Out for Delivery')->count(),
                'delivered' => $customOrders->where('order_status', 'Delivered')->count(),
                'cancelled' => $customOrders->where('order_status', 'Cancelled')->count(),
            ];

            return response()->json([
                'status' => true,
                'message' => 'Home data fetched successfully.',
                'month' => $now->format('F Y'),
                'period' => [
                    'start_date' => $monthStart,
                    'end_date' => $monthEnd,
                ],
                'has_subscription' => $subscriptions->isNotEmpty(),
                'total_subscriptions' => $subscriptions->count(),
                // Existing "data" key is kept for mobile backward compatibility.
                'data' => $subscriptions,
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
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
