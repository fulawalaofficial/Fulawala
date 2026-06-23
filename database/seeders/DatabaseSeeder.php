<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\AppSetting;
use App\Models\EventBooking;
use App\Models\FlowerProduct;
use App\Models\PoojaPacket;
use App\Models\Quotation;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(['email' => 'admin@example.com'], [
            'name' => 'Admin User', 'mobile' => '9999999999', 'password' => 'admin123', 'role' => 'admin', 'status' => 'Active'
        ]);

        $customer = User::firstOrCreate(['email' => 'customer@example.com'], [
            'name' => 'Demo Customer', 'mobile' => '8888888888', 'password' => 'customer123', 'role' => 'customer', 'status' => 'Active'
        ]);

        Address::firstOrCreate(['user_id' => $customer->id, 'is_default' => true], [
            'address' => 'Demo address, Paradip, Odisha', 'city' => 'Paradip', 'state' => 'Odisha', 'pincode' => '754142', 'landmark' => 'Near temple'
        ]);

        PoojaPacket::firstOrCreate(['packet_name' => 'Daily Basic Pooja Packet'], [
            'description' => 'Marigold, tulsi, bel patra and rose petals delivered every morning.',
            'included_flowers' => ['Marigold', 'Tulsi', 'Bel Patra', 'Rose Petals'],
            'monthly_price' => 999, 'weekly_price' => 299, 'daily_quantity' => '1 packet daily', 'package_type' => 'Monthly', 'status' => 'Active'
        ]);

        PoojaPacket::firstOrCreate(['packet_name' => 'Daily Premium Pooja Packet'], [
            'description' => 'Premium pooja flowers with lotus and jasmine for daily worship.',
            'included_flowers' => ['Marigold', 'Tulsi', 'Lotus', 'Jasmine', 'Rose Petals'],
            'monthly_price' => 1499, 'weekly_price' => 399, 'daily_quantity' => '1 premium packet daily', 'package_type' => 'Monthly', 'status' => 'Active'
        ]);

        $flowers = [
            ['Marigold', 'Pooja', 80, '500g', 'Fresh orange marigold flowers'],
            ['Rose', 'Decoration', 120, '20 pieces', 'Fresh red rose flowers'],
            ['Jasmine', 'Pooja', 60, '1 packet', 'Fresh jasmine packet'],
            ['Lotus', 'Pooja', 150, '5 pieces', 'Fresh lotus flowers'],
            ['Garland', 'Decoration', 200, '2 pieces', 'Ready-made flower garlands'],
        ];
        foreach ($flowers as [$name, $category, $price, $unit, $description]) {
            FlowerProduct::firstOrCreate(['flower_name' => $name], [
                'category' => $category, 'price' => $price, 'unit' => $unit, 'stock_status' => 'In Stock', 'description' => $description, 'status' => 'Active'
            ]);
        }

        Staff::firstOrCreate(['email' => 'delivery@example.com'], ['name' => 'Delivery Boy', 'mobile' => '7777777777', 'password' => 'staff123', 'role' => 'Delivery Boy', 'status' => 'Active']);
        Staff::firstOrCreate(['email' => 'decorator@example.com'], ['name' => 'Decorator Staff', 'mobile' => '6666666666', 'password' => 'staff123', 'role' => 'Decorator', 'status' => 'Active']);

        $booking = EventBooking::firstOrCreate(['user_id' => $customer->id, 'event_type' => 'Wedding'], [
            'event_date' => now()->addDays(20)->toDateString(), 'event_time' => '10:00', 'venue_address' => 'Demo marriage mandap',
            'budget' => 25000, 'requirement' => 'Stage and entrance flower decoration', 'booking_status' => 'Quotation Sent'
        ]);
        Quotation::firstOrCreate(['booking_id' => $booking->id], [
            'decoration_details' => 'Stage backdrop, entrance gate, flower pillars and mandap decoration.',
            'total_amount' => 30000, 'advance_amount' => 10000, 'balance_amount' => 20000, 'terms' => 'Advance required to confirm booking.', 'quotation_status' => 'Sent'
        ]);

        $settings = [
            'default_morning_delivery_time' => '06:00 - 08:00',
            'delivery_charge' => '40',
            'minimum_order_amount' => '100',
            'company_name' => 'Flower Delivery',
            'support_number' => '9999999999',
        ];
        foreach ($settings as $key => $value) {
            AppSetting::updateOrCreate(['setting_key' => $key], ['setting_value' => $value]);
        }
    }
}
