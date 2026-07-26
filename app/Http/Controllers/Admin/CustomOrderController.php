<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Throwable;

class CustomOrderController extends Controller
{
    /**
     * Display custom orders.
     */
    public function index()
    {
        $orders = CustomOrder::query()
            ->with([
                'user',
                'address',
                'items.flower',
            ])
            ->latest()
            ->paginate(30);

        $orderStatuses = CustomOrder::ORDER_STATUSES;

        return view('admin.custom-orders.index', compact(
            'orders',
            'orderStatuses'
        ));
    }

    /**
     * Update custom-order status.
     */
    public function updateStatus(
        Request $request,
        CustomOrder $customOrder
    ): RedirectResponse {
        $validated = $request->validate([
            'order_status' => [
                'required',
                'string',
                Rule::in(CustomOrder::ORDER_STATUSES),
            ],
        ]);

        try {
            $oldStatus = $customOrder->order_status;

            $customOrder->update([
                'order_status' => $validated['order_status'],
            ]);

            Log::info('Custom order status updated.', [
                'order_id' => $customOrder->id,
                'old_status' => $oldStatus,
                'new_status' => $validated['order_status'],
            ]);

            return back()->with(
                'success',
                "Order #{$customOrder->id} status updated successfully."
            );
        } catch (Throwable $exception) {
            Log::error('Custom order status update failed.', [
                'order_id' => $customOrder->id,
                'message' => $exception->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to update the order status. Please try again.'
                );
        }
    }
}