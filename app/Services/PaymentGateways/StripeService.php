<?php

namespace App\Services\PaymentGateways;

use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\Checkout\Session;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Exception\ApiErrorException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class StripeService
{
    public function initiatePayment(array $planData)
    {
        $stripe_settings = get_settings('stripe_settings');
        Stripe::setApiKey($stripe_settings['stripe_secret_key']);

        try {
            $session = Session::create([
                'ui_mode' => 'embedded',
                'line_items' => [[
                    'price_data' => [
                        'currency' => $stripe_settings['currency_code'],
                        'product_data' => [
                            'name' => "Subscription for " . $planData['plan_name'],
                        ],
                        'unit_amount' => $planData['finalPrice'] * 100,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                "return_url" => route('stripe.success') . '?session_id={CHECKOUT_SESSION_ID}',
                "metadata" => $planData,
            ]);

            return [
                'payment_method' => 'stripe',
                'clientSecret' => $stripe_settings['stripe_secret_key'],
                'publicKey' => $stripe_settings['stripe_publishable_key'],
                'session' => $session,
            ];
        } catch (\Exception $e) {
            File::append('Log.txt', '[' . now() . '] Stripe Initiate Error: ' . $e->getMessage() . "\n");
            return ['error' => 'Error fetching transaction: ' . $e->getMessage()];
        }
    }

    public function handleSuccess($sessionId)
    {
        $stripe_settings = get_settings('stripe_settings');
        Stripe::setApiKey($stripe_settings['stripe_secret_key']);

        try {
            $checkout_session = Session::retrieve($sessionId);

            $data['status'] = $checkout_session->payment_status === 'paid'
                ? 'PAYMENT_SUCCESS'
                : 'PAYMENT_ERROR';

            return view('subscription-plan.payment_successfull', ['data' => $data]);
        } catch (\Exception $e) {
            return view('payment.error', ['message' => 'An error occurred while verifying the payment.']);
        }
    }

    public function handleWebhook(string $payload, string $signature)
    {
        $stripe_settings = get_settings('stripe_settings');
        Stripe::setApiKey($stripe_settings['stripe_secret_key']);
        $webhook_secret = "whsec_pjJyOISMdR9uusWCCrcGVDb3ScPiubwt"; // Replace with your actual

        try {
            $event = Webhook::constructEvent($payload, $signature, $webhook_secret);
            File::append('Log.txt', "Stripe Webhook Event: {$event->type}\n");

            if ($event->type === 'checkout.session.completed') {
                $session = $event->data->object;
                $session_id = $session->id;

                $session = Session::retrieve($session_id);

                if ($session->payment_status === 'paid') {
                    $planData = $session->metadata;

                    // Create subscription from controller or inject logic here
                    \App\Models\Subscription::create([
                        'user_id' => $planData['user_id'],
                        'plan_id' => $planData['plan_id'],
                        'transaction_id' => $session->payment_intent,
                        'status' => 'active',
                        'meta' => json_encode($planData),
                    ]);

                    File::append('Log.txt', '[' . now() . '] Stripe Payment Successful: ' . $session_id . "\n");

                    return response()->json(['success' => true, 'message' => 'Payment successful']);
                }

                return response()->json(['success' => false, 'message' => 'Payment not completed']);
            }
        } catch (SignatureVerificationException $e) {
            File::append('Log.txt', '[' . now() . '] Stripe Signature Verification Failed: ' . $e->getMessage() . "\n");
            return response()->json(['error' => 'Signature Verification Failed'], 400);
        } catch (ApiErrorException $e) {
            File::append('Log.txt', '[' . now() . '] Stripe API Error: ' . $e->getMessage() . "\n");
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        } catch (\Exception $e) {
            File::append('Log.txt', '[' . now() . '] Stripe Webhook Error: ' . $e->getMessage() . "\n");
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
