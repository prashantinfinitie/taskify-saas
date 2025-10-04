<?php

namespace App\Services\PaymentGateways;

use App\Models\Transaction;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Ixudra\Curl\Facades\Curl;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

class PhonePeService
{
    protected $settings;

    public function __construct()
    {
        $this->settings = get_settings('phone_pe_settings'); // from DB or config

    }

    /**
     * Fetch OAuth Access Token for PhonePe V2
     */
    protected function getAccessToken()
    {
        // Use cache to avoid frequent token requests
        // return Cache::remember('phonepe_access_token', 50 * 60, function () {
            $client_id = $this->settings['client_id'];
            $client_secret = $this->settings['client_secret'];
            $client_version = $this->settings['client_version'] ?? '1';

            $url = $this->settings['phonepe_mode'] === 'production'
                ? 'https://api.phonepe.com/apis/identity-manager/v1/oauth/token'
                : 'https://api-preprod.phonepe.com/apis/pg-sandbox/v1/oauth/token';

            $response = Curl::to($url)
                ->withHeader('Content-Type: application/x-www-form-urlencoded')
                ->withData([
                    'client_id' => $client_id,
                    'client_secret' => $client_secret,
                    'client_version' => $client_version,
                    'grant_type' => 'client_credentials',
                ])
                ->post();

            $data = json_decode($response, true);

            if (isset($data['access_token'])) {
                return $data['access_token'];
            }

            Log::error('PhonePe token fetch failed', ['response' => $data]);
            return null;
        // });
    }

    /**
     * Initiate Payment Request to PhonePe V2
     */
    public function initiatePayment(array $planData, string $transaction_id)
    {
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            return ['success' => false, 'message' => 'PhonePe access token error'];
        }

        $payload = [
            'merchantOrderId' => $transaction_id,
            'amount' => $planData['finalPrice'] * 100,
            'expireAfter' => 1200,
            'metaInfo' => [
                'udf1' => $planData['plan_id'] ?? '',
                'udf2' => auth()->user()->email ?? '',
            ],
            'paymentFlow' => [
                'type' => 'PG_CHECKOUT',
                'message' => 'Taskify Plan Payment',
                'merchantUrls' => [
                    'redirectUrl' => route('phone_pe_redirect')
                ],
            ],
        ];

        $url = $this->settings['phonepe_mode'] === 'production'
            ? 'https://api.phonepe.com/apis/pg/checkout/v2/pay'
            : 'https://api-preprod.phonepe.com/apis/pg-sandbox/checkout/v2/pay';

        $response = Curl::to($url)
            ->withHeader('Authorization: O-Bearer ' . $accessToken)
            ->withHeader('Content-Type: application/json')
            ->withData(json_encode($payload))
            ->post();

        $result = json_decode($response, true);

        if (isset($result['redirectUrl'])) {
            return [
                'success' => true,
                'redirectUrl' => $result['redirectUrl'],
                'orderId' => $result['orderId'],
                'transaction_id' => $transaction_id,
                'payment_method' => 'phonepe',
            ];
        }

        Log::error('PhonePe payment initiation failed', ['response' => $result]);
        return ['success' => false, 'message' => 'PhonePe request failed', 'details' => $result];
    }

    /**
     * Verify Payment Status from PhonePe
     */
    public function verifyPaymentStatus(string $merchantOrderId)
    {
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            return false;
        }

        $url = $this->settings['phonepe_mode'] === 'production'
            ? "https://api.phonepe.com/apis/pg/checkout/v2/order/{$merchantOrderId}/status"
            : "https://api-preprod.phonepe.com/apis/pg-sandbox/checkout/v2/order/{$merchantOrderId}/status";

        $response = Curl::to($url)
            ->withHeader('Authorization: O-Bearer ' . $accessToken)
            ->withHeader('Content-Type: application/json')
            ->get();

        $data = json_decode($response, true);
        Log::info('PhonePe V2 Status Check', [
            'merchantOrderId' => $merchantOrderId,
            'response' => $data
        ]);

        if (isset($data['state']) && $data['state'] === 'COMPLETED') {
            $transaction = Transaction::where('transaction_id', $merchantOrderId)->first();

            if ($transaction) {
                $subscription = Subscription::find($transaction->subscription_id);
                if ($subscription) {
                    $subscription->status = 'active';
                    $subscription->save();
                }

                $transaction->status = 'completed';
                $transaction->save();
            }

            return true;
        }

        return false;
    }

    /**
     * Handle Payment Redirection
     */
    public function handleRedirect($request)
    {
        $merchantOrderId = $request->get('moid') ?? $request->get('orderid');
        $status = $this->verifyPaymentStatus($merchantOrderId);

        return [
            'status' => $status ? 'completed' : 'failed',
            'transaction_id' => $merchantOrderId
        ];
    }

    /**
     * Webhook/S2S Callback Handler (V2 - Secure SHA256 Authorization)
     */
    public function verifyWebhookV2(Request $request)
    {
        Log::info('PhonePe V2 Webhook Incoming', $request->all());

        try {
            // Validate Authorization Header
            $incomingHash = $request->header('Authorization');
            $expectedHash = hash('sha256', $this->settings['webhook_username'] . ':' . $this->settings['webhook_password']);

            if ($incomingHash !== $expectedHash) {
                Log::error('PhonePe Webhook: Invalid Authorization Header', [
                    'expected' => $expectedHash,
                    'incoming' => $incomingHash,
                ]);
                return response('Unauthorized', 401);
            }

            $payload = $request->input('payload');
            if (!$payload || !isset($payload['merchantOrderId'], $payload['state'])) {
                Log::error('PhonePe Webhook: Invalid Payload Structure', ['payload' => $payload]);
                return response('Invalid payload', 400);
            }

            $merchantOrderId = $payload['merchantOrderId'];
            $state = $payload['state'];

            Log::info('PhonePe Order State Received', [
                'orderId' => $merchantOrderId,
                'state' => $state
            ]);

            $transaction = Transaction::where('transaction_id', $merchantOrderId)->first();
            if (!$transaction) {
                Log::error('PhonePe Webhook: Transaction Not Found', ['transaction_id' => $merchantOrderId]);
                return response('Transaction not found', 404);
            }

            if ($state === 'COMPLETED') {
                $subscription = Subscription::find($transaction->subscription_id);
                if ($subscription) {
                    $subscription->status = 'active';
                    $subscription->save();
                }

                $transaction->status = 'completed';
                $transaction->save();

                Log::info('PhonePe Payment Completed', ['transaction_id' => $merchantOrderId]);
                return response('OK', 200);
            } elseif ($state === 'FAILED') {
                $transaction->status = 'failed';
                $transaction->save();

                Log::info('PhonePe Payment Failed', ['transaction_id' => $merchantOrderId]);
                return response('Payment failed', 200);
            }

            Log::warning('PhonePe Webhook: Unhandled State', ['state' => $state]);
            return response('Unknown state', 400);
        } catch (\Exception $e) {
            Log::error('PhonePe Webhook Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response('Webhook error', 500);
        }
    }
}
