<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('app:generate-daily-deliveries', function () {
    $created = \App\Models\Subscription::generateTodayDeliveries();
    $this->info("Daily subscription deliveries generated: {$created}");
})->purpose('Generate today deliveries for active subscriptions');
