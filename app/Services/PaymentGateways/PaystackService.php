<?php

namespace App\Services\PaymentGateways;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Unicodeveloper\Paystack\Facades\Paystack;

class PaystackService
{
    public function initiatePayment(array $planData, string $transactionId)
    {
        $paystack_settings = get_settings('paystack_settings');

        $data = [
            "amount" => $planData['finalPrice'] * 100,
            "reference" => $transactionId,
            "email" => $planData['user_email'],
            "currency" => "NGN",
            'callback_url' => route('paystack.success'),
            'metadata' => json_encode([
                "cancel_action" => route('paystack.cancel'),
                'user_id' => $planData['user_id'],
                'plan_id' => $planData['plan_id'],
                'plan_data' => json_encode($planData),
            ]),
            "orderID" => uniqid(),
        ];

        return Response::json([
            'publicKey' => $paystack_settings['paystack_key_id'],
            'payment_method' => 'paystack',
            'email' => $planData['user_email'],
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'reference' => $data['reference'],
            'metadata' => $data['metadata'],
        ]);
    }

    public function handleWebhook(array $input)
    {
        try {
            $data = $input['data'];
            $reference = $data['reference'];

            File::append('Log.txt', '[' . now() . '] Paystack Webhook Received: ' . json_encode($data) . "\n");

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('paystack.secretKey'),
            ])->get("https://api.paystack.co/transaction/verify/{$reference}");

            if ($response->successful()) {
                $transactionData = $response->json();

                if ($transactionData['status'] && $transactionData['data']['status'] === 'success') {
                    $transaction = \App\Models\Transaction::where('transaction_id', $reference)->first();

                    if ($transaction) {
                        $subscription = \App\Models\Subscription::findOrFail($transaction->subscription_id);
                        $subscription->status = 'active';
                        $subscription->save();

                        $transaction->status = 'completed';
                        $transaction->save();

                        File::append('Log.txt', '[' . now() . '] Paystack Payment Success for: ' . $reference . "\n");

                        return response()->json(['status' => 'success'], 200);
                    }

                    File::append('Log.txt', '[' . now() . '] Transaction not found for reference: ' . $reference . "\n");
                }
            }

            File::append('Log.txt', '[' . now() . '] Paystack Webhook Verification Failed: ' . $response->body() . "\n");
            return response()->json(['status' => 'error'], 400);
        } catch (\Exception $e) {
            File::append('Log.txt', '[' . now() . '] Paystack Webhook Error: ' . $e->getMessage() . "\n");
            return response()->json(['status' => 'error'], 500);
        }
    }

    public function handleSuccess()
    {
        $paymentDetails = Paystack::getPaymentData();

        $data = [
            'status' => ($paymentDetails['status'] && $paymentDetails['message'] === 'Verification successful')
                ? 'PAYMENT_SUCCESS'
                : 'PAYMENT_ERROR',
        ];

        return view('subscription-plan.payment_successfull', ['data' => $data]);
    }

    public function handleCancel()
    {
        return redirect()->back()->with(['error' => 'Paystack Transaction Cancelled']);
    }
}
