<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription; // Replace with your Subscription model
use Illuminate\Support\Facades\Log;

class CheckSubscriptions extends Command
{
    protected $signature = 'subscriptions:check';

    protected $description = 'Check subscription statuses and update as needed';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $subscriptions = Subscription::with('transactions')->where('ends_at', '<', now())->get();
        foreach ($subscriptions as $subscription) {
            $subscription->status = 'expired';
            $subscription->save();

            foreach ($subscription->transactions as $transaction) {
                $transaction->status = 'canceled';
                $transaction->save();
            }
        }
        Log::info('Subscription statuses checked and updated successfully.');
        $this->info('Subscription statuses checked and updated successfully.');
    }

}
