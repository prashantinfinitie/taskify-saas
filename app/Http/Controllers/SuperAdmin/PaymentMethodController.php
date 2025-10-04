<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class PaymentMethodController extends Controller
{
    private const PAYMENT_SETTINGS = [
        'paypal' => [
            'variable' => 'pay_pal_settings',
            'rules' => [
                'paypal_client_id' => 'required',
                'paypal_secret_key' => 'required',
                'paypal_business_email' => 'required',
                'payment_mode' => 'required',
                'currency_code' => 'required',
                'notification_url' => 'nullable',
            ]
        ],
        'phonepe' => [
            'variable' => 'phone_pe_settings',
            'rules' => [
                'merchant_id' => 'required',
                'app_id' => 'required',
                'phonepe_mode' => 'required',
                'payment_endpoint_url' => 'required',
                'salt_index' => 'required',
                'salt_key' => 'required',
            ]
        ],
        'stripe' => [
            'variable' => 'stripe_settings',
            'rules' => [
                'stripe_publishable_key' => 'required',
                'stripe_secret_key' => 'required',
                'payment_mode' => 'required',
                'currency_code' => 'required',
                'payment_endpoint_url' => 'required',
                'stripe_webhook_secret_key' => 'required',
            ]
        ],
        'paystack' => [
            'variable' => 'paystack_settings',
            'rules' => [
                'paystack_key_id' => 'required',
                'paystack_secret_key' => 'required',
                'payment_endpoint_url' => 'required',
            ]
        ],
        'bank_transfer' => [
            'variable' => 'bank_transfer_settings',
            'rules' => [
                'bank_name' => 'required|string|max:255',
                'account_number' => 'required|numeric|digits_between:10,20',
                'account_name' => 'required|string|max:255',
                'bank_code' => 'required|alpha_num|max:15',
                'swift_code' => 'required|string|max:15',
                'extra_notes' => 'nullable',
            ]
        ]
    ];

    public function index()
    {
        $pay_pal_settings = get_settings('pay_pal_settings');
        $phone_pe_settings = get_settings('phone_pe_settings');
        $stripe_settings = get_settings('stripe_settings');
        $paystack_settings = get_settings('paystack_settings');
        $bank_transfer_settings = get_settings('bank_transfer_settings');
        return view('settings.payment_method_settings', compact('pay_pal_settings', 'phone_pe_settings', 'stripe_settings', 'paystack_settings', 'bank_transfer_settings'));
    }

    /**
     * Store payment gateway settings
     *
     * @param Request $request
     * @param string $gateway
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeSettings(Request $request, string $gateway)
    {
        if (!array_key_exists($gateway, self::PAYMENT_SETTINGS)) {
            return response()->json([
                'error' => true,
                'message' => 'Invalid payment gateway'
            ], 400);
        }

        $config = self::PAYMENT_SETTINGS[$gateway];

        $validator = Validator::make($request->all(), $config['rules']);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $this->updateOrCreateSetting(
            $config['variable'],
            $request->except('_token', '_method', 'redirect_url')
        );

        return response()->json([
            'error' => false,
            'message' => ucfirst(str_replace('_', ' ', $gateway)) . ' Settings Updated Successfully'
        ]);
    }

    /**
     * Route handlers for specific payment gateways
     */
    public function store_paypal_settings(Request $request)
    {
        return $this->storeSettings($request, 'paypal');
    }

    public function store_phonepe_settings(Request $request)
    {
        return $this->storeSettings($request, 'phonepe');
    }

    public function store_stripe_settings(Request $request)
    {
        return $this->storeSettings($request, 'stripe');
    }

    public function store_paystack_settings(Request $request)
    {
        return $this->storeSettings($request, 'paystack');
    }

    public function store_bank_transfer_settings(Request $request)
    {
        return $this->storeSettings($request, 'bank_transfer');
    }

    /**
     * Update or create a setting
     *
     * @param string $variable
     * @param array $value
     * @return void
     */
    private function updateOrCreateSetting(string $variable, array $value): void
    {
        Setting::updateOrCreate(
            ['variable' => $variable],
            [
                'variable' => $variable,
                'value' => json_encode($value)
            ]
        );
    }
}
