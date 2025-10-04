<?php

namespace App\Services\PaymentGateways;

use App\Models\Subscription;
use App\Models\Transaction;
use Illuminate\Support\Facades\File;

class PaypalService
{
    public function initiatePayment(array $planData)
    {
        $transaction_id = uniqid();
        $paypal_settings = get_settings('pay_pal_settings');

        $res = $this->createSubscription($transaction_id, json_encode($planData), "pending");

        if ($res) {
            return [
                'client_id' => $paypal_settings['paypal_client_id'],
                'finalPrice' => $planData['finalPrice'],
                'success_url' => route('paypal.success'),
                'payment_method' => 'paypal',
                'transaction_id' => $transaction_id,
            ];
        }

        return ['error' => 'Error occurred while initiating PayPal payment'];
    }

    public function handleSuccess($request)
    {
        File::append('Log.txt', '[' . now() . '] PayPal Success Callback: ' . json_encode($request->all()) . "\n");

        $status = $request->status === 'COMPLETED'
            ? 'PAYMENT_SUCCESS'
            : 'PAYMENT_ERROR';

        return response()->json([
            'redirectUrl' => route('payment_successful', ['data' => $status])
        ]);
    }

    public function handleWebhook(array $payload)
    {
        File::append('Log.txt', '[' . now() . '] PayPal Webhook Triggered: ' . json_encode($payload) . "\n");

        try {
            $purchaseUnits = $payload['resource']['purchase_units'];
            $reference_id = $purchaseUnits[0]['reference_id'];
            $event = $payload['event_type'];

            File::append('Log.txt', '[' . now() . '] PayPal Event: ' . $event . ', Transaction ID: ' . $reference_id . "\n");

            if ($event === 'CHECKOUT.ORDER.APPROVED') {
                $transaction = Transaction::where('transaction_id', $reference_id)->first();

                if ($transaction) {
                    $subscription = Subscription::find($transaction->subscription_id);

                    if ($subscription) {
                        $subscription->update(['status' => 'active']);
                        $transaction->update(['status' => 'completed']);

                        File::append('Log.txt', '[' . now() . '] PayPal Payment Success\n');
                        return response()->json(['success' => true, 'message' => 'Payment Successful']);
                    }
                }

                File::append('Log.txt', '[' . now() . '] PayPal Transaction Not Found\n');
                return response()->json(['success' => false, 'message' => 'Transaction not found']);
            }

            return response()->json(['success' => false, 'message' => 'Unhandled event type']);
        } catch (\Exception $e) {
            File::append('Log.txt', '[' . now() . '] PayPal Webhook Error: ' . $e->getMessage() . "\n");
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function createSubscription($transaction_id, $planData, $status)
    {
        // Replace this with your actual subscription creation logic
        return \App\Models\Subscription::create([
            'transaction_id' => $transaction_id,
            'status' => $status,
            'meta' => $planData,
        ]);
    }
}
