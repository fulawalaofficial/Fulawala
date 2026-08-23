<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\CustomOrder;
use App\Models\CustomOrderItem;
use App\Models\EventBooking;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\SubscriptionDelivery;
use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class AccountDeletionController extends Controller
{
    /**
     * Show the public account deletion page.
     */
    public function show(): View
    {
        return view('website.account-deletion');
    }

    /**
     * Permanently delete a customer account and related customer data.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'login' => ['required', 'string', 'max:190'],
            'password' => ['required', 'string', 'max:255'],
            'confirmation' => ['required', 'string', 'in:DELETE'],
            'understand' => ['accepted'],
        ], [
            'confirmation.in' => 'Please type DELETE exactly to confirm permanent deletion.',
            'understand.accepted' => 'You must confirm that you understand this action is permanent.',
        ]);

        $login = trim($validated['login']);

        $user = User::query()
            ->where(function ($query) use ($login): void {
                $query->where('email', $login)
                    ->orWhere('mobile', $login);
            })
            ->first();

        // Do not reveal whether the email/mobile exists.
        if (!$user || !$user->password || !Hash::check($validated['password'], $user->password)) {
            return back()
                ->withErrors([
                    'login' => 'The email/mobile or password is incorrect.',
                ])
                ->onlyInput('login');
        }

        $wasCurrentWebUser = Auth::check() && (int) Auth::id() === (int) $user->id;

        try {
            DB::transaction(function () use ($user): void {
                /*
                 * Revoke all Laravel Sanctum API tokens first so the deleted
                 * customer can no longer access the mobile/API application.
                 */
                $user->tokens()->delete();

                /*
                 * Delete child rows before their parent rows. This makes the
                 * deletion work even when some foreign keys use RESTRICT.
                 */
                $subscriptionIds = Subscription::query()
                    ->where('user_id', $user->id)
                    ->pluck('id');

                if ($subscriptionIds->isNotEmpty()) {
                    SubscriptionDelivery::query()
                        ->whereIn('subscription_id', $subscriptionIds)
                        ->delete();
                }

                $customOrderIds = CustomOrder::query()
                    ->where('user_id', $user->id)
                    ->pluck('id');

                if ($customOrderIds->isNotEmpty()) {
                    CustomOrderItem::query()
                        ->whereIn('custom_order_id', $customOrderIds)
                        ->delete();
                }

                Payment::query()
                    ->where('user_id', $user->id)
                    ->delete();

                EventBooking::query()
                    ->where('user_id', $user->id)
                    ->delete();

                Subscription::query()
                    ->where('user_id', $user->id)
                    ->delete();

                CustomOrder::query()
                    ->where('user_id', $user->id)
                    ->delete();

                Address::query()
                    ->where('user_id', $user->id)
                    ->delete();

                UserDevice::query()
                    ->where('user_id', $user->id)
                    ->delete();

                // User does not use SoftDeletes, so delete() is permanent.
                $user->delete();
            });
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput($request->only('login'))
                ->with('error', 'We could not delete the account. Please try again or contact support.');
        }

        if ($wasCurrentWebUser) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect()
            ->route('website.account-delete.form')
            ->with('success', 'Your Fulawala account and associated customer data have been permanently deleted.');
    }
}
