<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\PoojaPacket;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->get('search', ''));
        $status = $request->get('status', '');
        $payment = $request->get('payment', '');

        $query = Subscription::with(['user', 'packet', 'address']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $number = ltrim($search, '#');

                if (ctype_digit($number)) {
                    $q->where('id', (int) $number);
                }

                $q->orWhereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%");
                });

                $q->orWhereHas('packet', function ($packetQuery) use ($search) {
                    $packetQuery->where('packet_name', 'like', "%{$search}%");
                });
            });
        }

        if (in_array($status, ['Active', 'Paused', 'Cancelled', 'Expired'])) {
            $query->where('subscription_status', $status);
        }

        if (in_array($payment, ['Pending', 'Paid', 'Failed'])) {
            $query->where('payment_status', $payment);
        }

        $subscriptions = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total' => Subscription::count(),
            'active' => Subscription::where('subscription_status', 'Active')->count(),
            'paid' => Subscription::where('payment_status', 'Paid')->count(),
            'pending' => Subscription::where('payment_status', 'Pending')->count(),
            'revenue' => Subscription::where('payment_status', 'Paid')->sum('amount'),
        ];

        return view('admin.subscriptions.index', compact(
            'subscriptions',
            'stats',
            'search',
            'status',
            'payment'
        ));
    }

    public function create()
    {
        $customers = User::where('role', 'customer')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'mobile']);

        $packets = PoojaPacket::query()
            ->when(Schema::hasColumn('pooja_packets', 'status'), function ($q) {
                $q->where('status', 'Active');
            })
            ->orderBy('packet_name')
            ->get();

        return view('admin.subscriptions.create', compact('customers', 'packets'));
    }

    public function customerAddresses(User $user)
    {
        if ($user->role !== 'customer') {
            return response()->json([
                'addresses' => [],
                'message' => 'Invalid customer selected.',
            ], 422);
        }

        $ownerColumn = $this->addressOwnerColumn();

        if (!$ownerColumn) {
            return response()->json([
                'addresses' => [],
                'message' => 'addresses table must have user_id column.',
            ], 422);
        }

        $addresses = Address::where($ownerColumn, $user->id)
            ->orderByDesc('id')
            ->get()
            ->map(function ($address) {
                return [
                    'id' => $address->id,
                    'label' => $this->formatAddress($address),
                ];
            })
            ->values();

        return response()->json([
            'addresses' => $addresses,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => [
                'required',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('role', 'customer');
                }),
            ],
            'packet_id' => ['required', 'exists:pooja_packets,id'],
            'duration' => ['required', 'integer', 'in:1,2,3,6,12'],
            'start_date' => ['required', 'date'],
            'address_id' => ['nullable', 'integer'],
            'new_address' => ['nullable', 'string', 'max:1000'],
            'payment_status' => ['required', 'in:Pending,Paid,Failed'],
            'subscription_status' => ['required', 'in:Active,Paused,Cancelled,Expired'],
        ]);

        $userId = (int) $data['user_id'];
        $addressId = $this->resolveAddressId($userId, $request);

        $packet = PoojaPacket::findOrFail($data['packet_id']);

        $startDate = Carbon::parse($data['start_date']);
        $duration = (int) $data['duration'];

        $endDate = Subscription::calculateEndDate($startDate, $duration);

        $amount = (float) $packet->monthly_price * $duration;

        Subscription::create([
            'user_id' => $userId,
            'packet_id' => $data['packet_id'],
            'address_id' => $addressId,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'duration' => $duration,
            'amount' => $amount,
            'payment_status' => $data['payment_status'],
            'subscription_status' => $data['subscription_status'],
        ]);

        return redirect()
            ->route('admin.subscriptions.index')
            ->with('success', 'Subscription created successfully.');
    }

    private function resolveAddressId(int $userId, Request $request): int
    {
        $ownerColumn = $this->addressOwnerColumn();

        if (!$ownerColumn) {
            throw ValidationException::withMessages([
                'address_id' => 'addresses table must have user_id column.',
            ]);
        }

        if ($request->filled('address_id')) {
            $address = Address::where('id', $request->input('address_id'))
                ->where($ownerColumn, $userId)
                ->first();

            if (!$address) {
                throw ValidationException::withMessages([
                    'address_id' => 'Selected address does not belong to this customer.',
                ]);
            }

            return $address->id;
        }

        if ($request->filled('new_address')) {
            $address = $this->createAddressForUser($userId, $request->input('new_address'));

            return $address->id;
        }

        throw ValidationException::withMessages([
            'address_id' => 'Please select an address or add a new delivery address.',
        ]);
    }

    private function createAddressForUser(int $userId, string $addressText): Address
    {
        $ownerColumn = $this->addressOwnerColumn();
        $addressColumn = $this->mainAddressColumn();

        if (!$ownerColumn) {
            throw ValidationException::withMessages([
                'new_address' => 'No user_id column found in addresses table.',
            ]);
        }

        if (!$addressColumn) {
            throw ValidationException::withMessages([
                'new_address' => 'No address column found in addresses table.',
            ]);
        }

        $address = new Address();
        $address->{$ownerColumn} = $userId;
        $address->{$addressColumn} = $addressText;

        if (Schema::hasColumn('addresses', 'type')) {
            $address->type = 'Subscription';
        }

        if (Schema::hasColumn('addresses', 'status')) {
            $address->status = 'Active';
        }

        $address->save();

        return $address;
    }

    private function addressOwnerColumn(): ?string
    {
        foreach (['user_id', 'customer_id'] as $column) {
            if (Schema::hasColumn('addresses', $column)) {
                return $column;
            }
        }

        return null;
    }

    private function mainAddressColumn(): ?string
    {
        foreach (['address', 'address_line', 'address_line1', 'full_address', 'line1', 'street_address'] as $column) {
            if (Schema::hasColumn('addresses', $column)) {
                return $column;
            }
        }

        return null;
    }

    private function formatAddress(Address $address): string
    {
        $parts = [];

        foreach (['address', 'address_line', 'address_line1', 'full_address', 'line1', 'street_address'] as $column) {
            if (Schema::hasColumn('addresses', $column) && !empty($address->{$column})) {
                $parts[] = $address->{$column};
                break;
            }
        }

        foreach (['landmark', 'area', 'city', 'district', 'state', 'pincode', 'pin_code', 'postal_code'] as $column) {
            if (Schema::hasColumn('addresses', $column) && !empty($address->{$column})) {
                $parts[] = $address->{$column};
            }
        }

        $label = trim(implode(', ', array_filter($parts)));

        return $label !== '' ? $label : 'Address #' . $address->id;
    }
}