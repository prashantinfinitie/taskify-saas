<?php

namespace App\Http\Controllers;

use PayPal;
use Stripe\Event;
use Carbon\Carbon;
use Stripe\Webhook;
use App\Models\Plan;
use App\Models\User;
use Stripe\PaymentIntent;
use App\Models\Transaction;
use PayPal\Rest\ApiContext;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use Ixudra\Curl\Facades\Curl;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Models\BankTransferDocument;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use PayPal\Auth\OAuthTokenCredential;
use Stripe\Exception\ApiErrorException;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Unicodeveloper\Paystack\Facades\Paystack;
use App\Services\PaymentGateways\PhonePeService;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Stripe\Exception\SignatureVerificationException;
use Illuminate\Support\Facades\Session as FacadesSession;

class SubscriptionPlan extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $remainingDays = "";
        $activeSubscription = Subscription::where(['user_id' => Auth::id(), 'status' => 'active'])
            ->first();
        if ($activeSubscription) {
            $endDate = Carbon::parse($activeSubscription->ends_at);
            $currentDate = Carbon::now();
            // Calculate the difference in days
            $remainingDays = $endDate->diffInDays($currentDate);
        }
        $subscriptions = Subscription::where('user_id', Auth::id())->orderBy('id', 'DESC')->get();
        return view('subscription-plan.index', compact('activeSubscription', 'remainingDays', 'subscriptions'));
    }
    public function transactionsList()
    {
        // Get the ID of the currently authenticated admin user
        $adminUserId = Auth::id();
        $search = request('search');
        $sort = request('sort', 'id');
        $order = request(
            'order',
            'DESC'
        );
        // Query transactions associated with the admin user
        $transactions = Transaction::with(['user', 'subscription.plan'])
            ->where('user_id', $adminUserId)
        ->orderBy(
            $sort,
            $order
        );
        // Apply search filter if provided
        if ($search) {
            $transactions->where(function ($query) use ($search) {
                $query->where('payment_method', 'like', '%' . $search . '%')
                    ->orWhere('id', 'like', '%' . $search . '%')
                    ->orWhere('amount', 'like', '%' . $search . '%')
                    ->orWhere('currency', 'like', '%' . $search . '%')
                    ->orWhereHas('user', function ($query) use ($search) {
                        $query->where('first_name', 'like', '%' . $search . '%')
                            ->orWhere('last_name', 'like', '%' . $search . '%');
                    });
            });
        }
        // Paginate the results and prepare data for response
        $total = $transactions->count();
        $transactions = $transactions->paginate(request('limit'));
        // Map the transaction data
        $transactions = $transactions->map(function ($transaction) {
            $user = $transaction->user;
            $subscription = $transaction->subscription;
            switch ($transaction->status) {
                case 'pending':
                    $status = '<span class="badge bg-label-warning">Pending</span>';
                    break;
                case 'completed':
                    $status = '<span class="badge bg-label-success">Completed</span>';
                    break;
                case 'canceled':
                    $status = '<span class="badge bg-label-danger">Canceled</span>';
                    break;
            }
            return [
                'id' => $transaction->id,
                'user_id' => $transaction->user_id,
                'subscription_id' => $transaction->subscription_id,
                'plan_name' => $subscription->plan->name,
                'user_name' => $user->first_name . ' ' . $user->last_name,
                'payment_method' => ucwords(str_replace('_', ' ', $transaction->payment_method)),
                'amount' => format_currency($transaction->amount),
                'currency' => $transaction->currency,
                'transaction_id' => $transaction->transaction_id,
                'status' => $status,
                'created_at' => format_date($transaction->created_at), // Use appropriate date format
            ];
        });
        // Return JSON response
        return response()->json([
            "rows" => $transactions,
            "total" => $total,
        ]);
    }
    public function subscriptionHistory()
    {
        $search = trim(request('search'));
        $sort = request('sort', 'id');
        $order = request('order', 'DESC');
        $subscriptionsQuery = Subscription::with('user', 'plan')
            ->where('user_id', Auth::id());
        if ($search) {
            $subscriptionsQuery->where(function ($query) use ($search) {
                $query->where('payment_method', 'like', '%' . $search . '%')
                    ->orWhere('id', 'like', '%' . $search . '%')
                    ->orWhere('status', 'like', '%' . $search . '%')
                    ->orWhere('charging_price', 'like', '%' . $search . '%')
                    ->orWhereHas('user', function ($query) use ($search) {
                    $query->whereRaw('LOWER(first_name) LIKE ?', ['%' . strtolower($search) . '%'])
                        ->orWhereRaw('LOWER(last_name) LIKE ?', ['%' . strtolower($search) . '%'])
                        ->orWhereRaw("CONCAT(LOWER(first_name), ' ', LOWER(last_name)) LIKE ?", ['%' . strtolower($search) . '%']);
                    })
                    ->orWhereHas('plan', function ($query) use ($search) {
                    $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%']);
                    });
            });
        }
        $subscriptionsQuery->orderBy($sort, $order);
        // Debugging
        Log::info('Search term: ' . $search);
        Log::info('SQL: ' . $subscriptionsQuery->toSql());
        Log::info('Bindings: ' . json_encode($subscriptionsQuery->getBindings()));
        $total = $subscriptionsQuery->count();
        $subscriptions = $subscriptionsQuery->get();
        $subscriptions = $subscriptions->map(function ($subscription) {
            $user = $subscription->user;
            $plan = $subscription->plan;
            switch ($subscription->status) {
                case 'pending':
                    $status = '<span class="badge bg-label-warning">Pending</span>';
                    break;
                case 'active':
                    $status = '<span class="badge bg-label-success">Active</span>';
                    break;
                case 'expired':
                    $status = '<span class="badge bg-label-danger">Expired</span>';
                    break;
                case 'inactive':
                    $status = '<span class="badge bg-label-danger">Inactive</span>';
                    break;
                default:
                    $status = '<span class="badge bg-label-secondary">Unknown</span>';
            }
            $featuresArray = json_decode($subscription->features, true);
            $modules = isset($featuresArray['modules']) ? $featuresArray['modules'] : [];
            $otherAttributes = [
                get_label('max_projects', 'Max Projects') => ($featuresArray['max_projects'] == -1) ? get_label('unlimited', 'Unlimited') : $featuresArray['max_projects'],
                get_label('max_team_members', 'Max Team Members') => ($featuresArray['max_team_members'] == -1) ? get_label('unlimited', 'Unlimited') : $featuresArray['max_team_members'],
                get_label('max_workspaces', 'Max Workspaces') => ($featuresArray['max_workspaces'] == -1) ? get_label('unlimited', 'Unlimited') : $featuresArray['max_workspaces'],
                get_label('max_clients', 'Max Clients') => ($featuresArray['max_clients'] == -1) ? get_label('unlimited', 'Unlimited') : $featuresArray['max_clients'],
            ];
            $listItems = '';
            foreach ($otherAttributes as $attribute => $value) {
                $listItems .= '<li><strong>' . $attribute . ':</strong> ' . $value . '</li>';
            }
            $modulesListItems = '<li><strong> ' . get_label('modules', 'Modules') . ' :</strong></li><ul>';
            foreach ($modules as $module) {
                $capitalizedModule = ucfirst($module);
                $modulesListItems .= '<li>' . get_label($module, $capitalizedModule) . '</li>';
            }
            $modulesListItems .= '</ul>';
            $list = '<ul>' . $listItems . $modulesListItems . '</ul>';
            return [
                'id' => $subscription->id,
                'user_name' => $user->first_name . ' ' . $user->last_name,
                'plan_name' => ucfirst($plan->name),
                'tenure' => ucfirst($subscription->tenure),
                'start_date' => $subscription->starts_at,
                'end_date' => $subscription->ends_at,
                'payment_method' => $subscription->payment_method === 'bank_transfer'
                ? ucwords(str_replace('_', ' ', $subscription->payment_method)) . ' (<a href="#" data-bs-toggle="modal" data-bs-target="#uploadDocumentModal" data-subscription-id="' . $subscription->id . '">Upload Document</a>)'
                : ucwords(str_replace('_', ' ', $subscription->payment_method)),
                'features' => $list,
                'charging_price' => format_currency($subscription->charging_price),
                'charging_currency' => $subscription->charging_currency,
                'status' => $status,
            ];
        });
        return response()->json([
            "rows" => $subscriptions,
            "total" => $total,
        ]);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $plans = Plan::where('status', 'active')->get();
        $currency_symbol = (get_settings('general_settings')['currency_symbol']);
        return view('subscription-plan.buy-plan', ['plans' => $plans, 'currency_symbol' => $currency_symbol]);
    }
    /**
     * Store a newly created resource in storage.
     */
    private const PAYMENT_METHODS = [
        'free_plan',
        'phonepe',
        'stripe',
        'paystack',
        'paypal',
        'bank_transfer',
    ];

    private const TENURE_DURATIONS = [
        'monthly' => 'addMonth',
        'yearly' => 'addYear',
        'lifetime' => ['addYears', 100]
    ];

    public function store(Request $request)
    {
        try {
            // Validate request
            $validated = $this->validateRequest($request);
            if ($validated->fails()) {
                return response()->json(['errors' => $validated->errors()], 422);
            }

            // Check for existing subscription
            if ($this->hasActiveSubscription($request->user_id)) {
                return response()->json([
                    'error' => 'User already has an active subscription'
                ], 422);
            }

            // Get plan and user
            $plan = Plan::findOrFail($request->plan_id);
            $user = User::findOrFail($request->user_id);

            // Calculate dates and price
            $subscriptionDates = $this->calculateSubscriptionDates($request->tenure);
            $finalPrice = $this->calculateFinalPrice($plan, $request->tenure);

            // Prepare subscription data
            $planData = $this->preparePlanData($plan, $user, $request, $finalPrice, $subscriptionDates);

            // Store data in session
            session()->put('plan_data', $planData);

            // Handle payment
            return $this->handlePayment($request->payment_method, $planData);
        } catch (\Exception $e) {

            return response()->json([
                'error' => 'Subscription creation failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function validateRequest(Request $request)
    {

        return Validator::make($request->all(), [
            'plan_id' => 'required|exists:plans,id',
            'user_id' => 'required|exists:users,id',
            'tenure' => 'required|in:monthly,yearly,lifetime',
            'payment_method' => 'required|in:' . implode(',', self::PAYMENT_METHODS)
        ]);
    }

    private function hasActiveSubscription($userId)
    {
        return Subscription::where('user_id', $userId)
            ->where('status', 'active')
            ->exists();
    }

    private function calculateSubscriptionDates($tenure)
    {
        $startDate = Carbon::now();
        $endDate = $startDate->copy();

        if ($tenure === 'lifetime') {
            $endDate->{self::TENURE_DURATIONS[$tenure][0]}(self::TENURE_DURATIONS[$tenure][1]);
        } else {
            $endDate->{self::TENURE_DURATIONS[$tenure]}();
        }

        return [
            'start' => $startDate,
            'end' => $endDate->endOfDay(),
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString()
        ];
    }

    private function preparePlanData($plan, $user, $request, $finalPrice, $dates)
    {
        return [
            'finalPrice' => $finalPrice,
            'plan_id' => $plan->id,
            'user_id' => $user->id,
            'plan_name' => $plan->name,
            'mobile_number' => $user->phone,
            'user_email' => $user->email,
            'tenure' => $request->tenure,
            'currency_symbol' => get_settings('general_settings')['currency_symbol'],
            'plan_features' => json_encode($this->createPlanFeatures($plan)),
            'end_date' => $dates['end'],
            'start_date' => $dates['start'],
            'payment_method' => $request->payment_method,
        ];
    }

    private function handlePayment($paymentMethod, $planData)
    {
        if ($paymentMethod === 'free_plan') {
            return $this->processFreeSubscription($planData);
        }

        $paymentMethods = [
            'phonepe' => 'phone_pe',
            'stripe' => 'stripe',
            'paystack' => 'paystack',
            'paypal' => 'paypal',
            'bank_transfer' => 'bank_transfer',
        ];

        if (isset($paymentMethods[$paymentMethod])) {
            $method = $paymentMethods[$paymentMethod];
            return $this->$method($planData);
        }

        throw new \InvalidArgumentException('Invalid payment method');
    }

    private function processFreeSubscription($planData)
    {

        $subscription = Subscription::create([
            'plan_id' => $planData['plan_id'],
            'user_id' => $planData['user_id'],
            'tenure' => $planData['tenure'],
            'starts_at' => $planData['start_date'],
            'ends_at' => $planData['end_date'],
            'payment_method' => $planData['payment_method'],
            'features' => $planData['plan_features'],
            'charging_price' => $planData['finalPrice'],
            'charging_currency' => $planData['currency_symbol'],
            'status' => 'active'
        ]);

        Transaction::create([
            'user_id' => $subscription->user_id,
            'subscription_id' => $subscription->id,
            'amount' => $subscription->charging_price,
            'currency' => $subscription->charging_currency,
            'payment_method' => $subscription->payment_method,
            'status' => 'completed',
            'transaction_id' => uniqid()
        ]);

        return response()->json([
            'redirect_url' => route('subscription-plan.index'),
            'error' => 'false',
            'message' => 'Subscription added successfully',
            'payment_method' => 'free_plan'
        ], 201);
    }
    public function phone_pe($Plandata)
    {
        $PhonePeService = new PhonePeService();
        $transaction_id = uniqid();
        $res =  $this->createSubscription($transaction_id, json_encode($Plandata), $status = "pending");
        $phonePe_settings = get_settings('phone_pe_settings');
        $planId = $Plandata['plan_id'];
        $userId = $Plandata['user_id'];
        if ($res) {

            $response = $PhonePeService->initiatePayment($Plandata, $transaction_id);
            if (isset($response['error'])) {
                return response()->json(['error'=>true,'message' => $response['error']], 422);
            }
            $response['payment_method'] = 'phonepe';
            $response['plan_id'] = $planId;
            $response['user_id'] = $userId;
            return response()->json($response);
        }
    }
    public function phone_pe_webhook(Request $request)
    {
        $PhonePeService = new PhonePeService();
        return $PhonePeService->verifyWebhookV2($request);
    }
    public function phone_pe_redirect(Request $request)
    {
        $PhonePeService = new PhonePeService();
        $data = $PhonePeService->handleRedirect($request);

        if ($data['status'] == 'completed') {
            $data['status'] = "PAYMENT_SUCCESS";
            $data['transaction_id'] = $data['transaction_id'];
        } else {
            $data['status'] = "PAYMENT_FAILED";
            $data['transaction_id'] = $data['transaction_id'];
        }
        return view('subscription-plan.payment_successfull', ['data' => $data]);
    }
    public function paystack($Plandata)
    {
        $transaction_id = uniqid();
        $paystack_settings = get_settings('paystack_settings');
        $res = $this->createSubscription($transaction_id, json_encode($Plandata), $status = "pending");
        if ($res) {
            $data = array(
                "amount" => $Plandata['finalPrice'] * 100,
                "reference" => $transaction_id,
                "email" => $Plandata['user_email'],
                "currency" => "NGN",
                'callback_url' => route('paystack.success'),
                'metadata' => json_encode([
                    "cancel_action" => route('paystack.cancel'),
                    'user_id' => $Plandata['user_id'],
                    'plan_id' => $Plandata['plan_id'],
                    'plan_data' => json_encode($Plandata),
                ]), // Convert array to JSON string
                "orderID" => uniqid(),
            );
            return Response::json([
                'publicKey' => $paystack_settings['paystack_key_id'],
                'payment_method' => 'paystack',
                'email' => $Plandata['user_email'],
                'amount' => $data['amount'],
                'currency' => $data['currency'],
                'reference' => $data['reference'],
                'metadata' => $data['metadata'],
            ]);
        }
    }
    public function paystack_payment_cancel(Request $request)
    {
        return redirect()->back()->with(['error' => 'Paystack Transaction Cancelled']);
    }
    public function paystack_webhook(Request $request)
    {
        try {
            // Extract transaction reference from webhook data
            $data = $request->input('data');
            $reference = $data['reference'];
            File::append('Log.txt', '[' . date('Y-m-d H:i:s') . '] Transaction Data: ' . json_encode($data) . "\n");
            // Check if the transaction reference has already been processed
            // Verify transaction with Paystack API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('paystack.secretKey'),
            ])->get("https://api.paystack.co/transaction/verify/$reference");
            // Check if the request was successful
            if ($response->successful()) {
                $transactionData = $response->json();
                if ($transactionData['status'] === true && $transactionData['data']['status'] === 'success') {
                    File::append('Log.txt', '[' . date('Y-m-d H:i:s') . '] Transaction Response: ' . json_encode($transactionData) . "\n");
                    $transaction = Transaction::where('transaction_id', $reference)->first();
                    if ($transaction !== null) {
                        $subscription = Subscription::findOrFail($transaction->subscription_id);
                        $subscription->status = 'active';
                        $subscription->save();
                        $transaction->status = "completed";
                        $transaction->save();
                        File::append('Log.txt', '[' . date('Y-m-d H:i:s') . '] Paystack webhook request processed successfully.' . "\n");
                        return response()->json(['status' => 'success', 'message' => 'Webhook processed successfully'], 200);
                    } else {
                        File::append('Log.txt', '[' . date('Y-m-d H:i:s') . '] Failed to create subscription for Paystack reference: ' . json_encode($transactionData['data']['reference']) . "\n");
                        return response()->json(['status' => 'error', 'message' => 'Failed to create subscription'], 500);
                    }
                } else {
                    // Transaction verification failed
                    File::append('Log.txt', '[' . date('Y-m-d H:i:s') . '] Paystack transaction verification failed: ' . json_encode($transactionData['message']) . "\n");
                    return response()->json(['status' => 'error', 'message' => 'Transaction verification failed'], 400);
                }
            } else {
                // Error occurred while communicating with Paystack API
                File::append('Log.txt', '[' . date('Y-m-d H:i:s') . '] Error verifying Paystack transaction: ' . json_encode($response->body()) . "\n");
                return response()->json(['status' => 'error', 'message' => 'Error verifying transaction'], 500);
            }
        } catch (\Exception $e) {
            // Log the exception and any relevant information
            File::append('Log.txt', '[' . date('Y-m-d H:i:s') . '] Error processing Paystack webhook request: ' . $e->getMessage() . "\n");
            File::append('Log.txt', '[' . date('Y-m-d H:i:s') . '] Request data: ' . json_encode($request->all()) . "\n");
            return response()->json(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }
    public function paystack_payment_success()
    {
        $paymentDetails = Paystack::getPaymentData();
        if ($paymentDetails['status'] && $paymentDetails['message'] == "Verification successful") {
            $data['status'] = "PAYMENT_SUCCESS";
            return view('subscription-plan.payment_successfull', ['data' => $data]);
        } else {
            $data['status'] = "PAYMENT_ERROR";
            return view('subscription-plan.payment_successfull', ['data' => $data]);
        }
    }
    public function stripe($planData)
    {
        $stripe_settings = get_settings('stripe_settings');
        \Stripe\Stripe::setApiKey($stripe_settings['stripe_secret_key']);
        try {
            $response = Session::create([
                'ui_mode' => 'embedded',
                'line_items' => [[
                    'price_data' => [
                        'currency' => $stripe_settings['currency_code'],
                        'product_data' => [
                            'name' => "Subscription for   " . $planData['plan_name'],
                        ],
                        'unit_amount' => $planData['finalPrice'] * 100,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                "return_url" => route('stripe.success') . '?session_id={CHECKOUT_SESSION_ID}',
                "metadata" => $planData,
            ]);
        } catch (\Exception $e) {
            // Log any exceptions that occur during transaction retrieval
            echo  "Error fetching transaction: " . $e->getMessage();
            return false;
        }
        $response['payment_method'] = 'stripe';
        $response['clientSecret'] = $stripe_settings['stripe_secret_key'];
        $response['publicKey'] = $stripe_settings['stripe_publishable_key'];
        return $response;
    }
    public function stripe_success(Request $request)
    {
        $sessionId = $request->query('session_id');
        $stripe_settings = get_settings('stripe_settings');
        \Stripe\Stripe::setApiKey($stripe_settings['stripe_secret_key']);
        try {
            $checkout_session = Session::retrieve($sessionId);
            $data['status'] = "PAYMENT_SUCCESS";
            // Check if the payment was successful
            if ($checkout_session->payment_status === 'paid') {
                // Payment was successful, handle further processing here
                return view('subscription-plan.payment_successfull', ['data' => $data]); // Or redirect to a success page
            } else {
                $data['status'] = "PAYMENT_ERROR";
                return view('subscription-plan.payment_successfull', ['data' => $data]); // Or redirect to a success page
                // Payment was not successful, handle accordingly
                // return view('payment.error', ['message' => 'Payment was not successful.']);
            }
        } catch (\Exception $e) {
            // Handle Stripe API errors
            return view('payment.error', ['message' => 'An error occurred while verifying the payment.']);
        }
    }
    public function stripe_webhook(Request $request)
    {
        $payload = $request->getContent();
        // You should verify the signature to ensure the webhook is from Stripe
        $stripe_settings = get_settings('stripe_settings');
        \Stripe\Stripe::setApiKey($stripe_settings['stripe_secret_key']);
        $webhook_secret = "whsec_pjJyOISMdR9uusWCCrcGVDb3ScPiubwt";  //live
        // $webhook_secret = "whsec_3ieo0MBZ7NjLdPLeWav6eljTgrL2XjRj"; //local
        try {
            $event = Webhook::constructEvent(
                $payload,
                $request->header('Stripe-Signature'),
                $webhook_secret
            );
            File::append('Log.txt', "Event Object" . "\n");
        } catch (SignatureVerificationException $e) {
            // Log the signature verification failure
            File::append('Log.txt', '[' . date('Y-m-d H:i:s') . '] Stripe Webhook Signature Verification Failed: ' . $e->getMessage() . "\n");
            return response()->json(['error' => 'Signature Verification Failed'], 400);
        }
        try {
            File::append('Log.txt', "Event Inside Try Block: " . $event->type . "\n");
            if ($event->type === 'checkout.session.completed') {
                $session = $event->data->object;
                // Retrieve the session ID from the event
                $session_id = $session->id;
                // Log the session ID
                File::append('Log.txt', '[' . date('Y-m-d H:i:s') . '] Checkout Session ID: ' . $session_id . "\n");
                try {
                    // Retrieve the session from Stripe
                    $session = Session::retrieve($session_id);;
                    // Verify that the payment was successful
                    if ($session->payment_status === 'paid') {
                        // Payment was successful, handle your business logic here
                        // For example, update database, send confirmation email, etc.
                        $planData = $session['metadata'];
                        $this->createSubscription($session['payment_intent'], json_encode($planData), $status = "active");
                        // Log the successful payment
                        File::append('Log.txt', '[' . date('Y-m-d H:i:s') . '] Payment Successful for Session ID: ' . $session_id . PHP_EOL . "\n");
                        return response()->json(['success' => true, 'message' => 'Payment successful']);
                    } else {
                        // Payment failed or not yet completed
                        return response()->json(['success' => false, 'message' => 'Payment failed or not yet completed']);
                    }
                } catch (ApiErrorException $e) {
                    // Handle any errors from the Stripe API
                    // Log the API error
                    File::append('Log.txt', '[' . date('Y-m-d H:i:s') . '] Stripe API Error: ' . $e->getMessage() . "\n");
                    return response()->json(['success' => false, 'message' => $e->getMessage()]);
                }
            }
        } catch (\Exception $e) {
            // Log any other errors
            File::append('Log.txt', "Error: " . $e->getMessage() . "\n");
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    public function paypal($Plandata)
    {
        $transaction_id = uniqid();
        $paypal_settings = get_settings('pay_pal_settings');
        $res = $this->createSubscription($transaction_id, json_encode($Plandata), $status = "pending");
        if ($res) {
            $response['client_id'] = $paypal_settings['paypal_client_id'];
            $response['finalPrice'] = $Plandata['finalPrice'];
            $response['success_url'] = route('paypal.success');
            $response['payment_method'] = "paypal";
            $response['transaction_id'] = $transaction_id;
            return $response;
        } else {
            return redirect()->back()->with(['error'  => 'Error Occured']);
        }
    }
    public function paypal_success(Request $request)
    {
        File::append('Log.txt', '[' . date('Y-m-d H:i:s') . '] Paypal Success Arrived: ' . json_encode($request) . "\n");
        if ($request->status == "COMPLETED") {
            $status = "PAYMENT_SUCCESS";
        } else {
            $status = "PAYMENT_ERROR";
        }
        // Construct route parameters
        $routeParams = ['data' => $status];
        // Return JSON response with redirect URL
        return new JsonResponse(['redirectUrl' => route('payment_successful', $routeParams)]);
    }
    public function paypal_webhook(Request $request)
    {
        File::append('Log.txt', '[' . date('Y-m-d H:i:s') . '] Paypal Webhook Arrived: ' . "\n");
        $data = $request->all();
        $purchaseUnits = $data['resource']['purchase_units'];
        $reference_id = $purchaseUnits[0]['reference_id'];
        File::append('Log.txt', '[' . date('Y-m-d H:i:s') . '] Paypal Webhook Transaction Id : ' . $reference_id .  "\n");
        $event = $data['event_type'];
        File::append('Log.txt', '[' . date('Y-m-d H:i:s') . '] Paypal Webhook Event : ' . $event .  "\n");
        if ($event = 'CHECKOUT.ORDER.APPROVED') {
            $transaction = Transaction::where('transaction_id', $reference_id)->first();
            if ($transaction) {
                $subscription = Subscription::findOrFail($transaction->subscription_id);
                $subscription->status = "active";
                $subscription->save();
                $transaction->status = "completed";
                $transaction->save();
                File::append('Log.txt', '[' . date('Y-m-d H:i:s') . '] Paypal Payment SuccessFull: ' . "\n");
                return response()->json(['success' => true, 'message' => 'Payment Successful']);
            } else {
                File::append('Log.txt', '[' . date('Y-m-d H:i:s') . '] Paypal Payment Failed: ' . "\n");
                return response()->json(['success' => false, 'message' => 'Payment Failed']);
            }
        }
    }
    public function payment_success_view(Request $request)
    {
        $data = [];
        $data['status'] = $request->segment(5); // Assuming 'PAYMENT_SUCCESS' is the fifth segment in the URL
        return view('subscription-plan.payment_successfull', ['data' => $data]);
    }

    public function bank_transfer($Plandata)
    {
        $transaction_id = uniqid();
        $res = $this->createSubscription($transaction_id, json_encode($Plandata), $status = "pending");
        if ($res) {
            $response['finalPrice'] = $Plandata['finalPrice'];
            $response['payment_method'] = "bank_transfer";
            $response['transaction_id'] = $transaction_id;
            $response['bank_transfer_settings'] = json_encode(get_settings('bank_transfer_settings'));
            $response['currency'] = $Plandata['currency_symbol'];
            $response['redirect_url'] = route('subscription-plan.index');
            return $response;
        }
    }
    private function calculateFinalPrice($plan, $tenure)
    {
        switch ($tenure) {
            case 'monthly':
                return $plan->monthly_discounted_price > 0 ? $plan->monthly_discounted_price : $plan->monthly_price;
            case 'yearly':
                return $plan->yearly_discounted_price > 0 ? $plan->yearly_discounted_price : $plan->yearly_price;
            case 'lifetime':
                return $plan->lifetime_discounted_price > 0 ? $plan->lifetime_discounted_price : $plan->lifetime_price;
            default:
                return 0;
        }
    }
    private function createPlanFeatures($plan)
    {
        return [
            'max_clients' => $plan->max_clients,
            'max_projects' => $plan->max_projects,
            'max_team_members' => $plan->max_team_members,
            'max_workspaces' => $plan->max_worksapces,
            'modules' => json_decode($plan->modules),
        ];
    }
    private function createSubscription($transaction_id = null, $planData = null, $status = null)
    {
        if ($status == null) {
            $status = 'active';
        }
        if ($status == 'active') {
            $trnxStatus = "completed";
        } else {
            $trnxStatus = "pending";
        }
        try {
            $plan_data = json_decode($planData, true);
            // Check if plan_data exists and has the required fields
            if ($plan_data && isset($plan_data['plan_id'], $plan_data['user_id'], $plan_data['tenure'], $plan_data['start_date'], $plan_data['end_date'], $plan_data['plan_features'], $plan_data['currency_symbol'], $plan_data['finalPrice'], $plan_data['payment_method'])) {
                $subscription = new Subscription();
                $subscription->plan_id = $plan_data['plan_id'];
                $subscription->user_id =  $plan_data['user_id'];
                $subscription->tenure = $plan_data['tenure'];
                $subscription->starts_at = now()->parse($plan_data['start_date']); // Convert ISO 8601 string to datetime
                $subscription->ends_at = now()->parse($plan_data['end_date']); // Convert ISO 8601 string to datetime
                $subscription->features = $plan_data['plan_features'];
                $subscription->charging_currency = $plan_data['currency_symbol'];
                $subscription->charging_price = $plan_data['finalPrice'];
                $subscription->payment_method = $plan_data['payment_method'];
                $subscription->status = $status;
                $subscription->save();
                $transaction = new Transaction();
                $transaction->subscription_id = $subscription->id;
                $transaction->amount = $plan_data['finalPrice'];
                $transaction->currency = $plan_data['currency_symbol'];
                $transaction->user_id = $plan_data['user_id'];
                $transaction->payment_method = $plan_data['payment_method'];
                $transaction->transaction_id = $transaction_id;
                $transaction->status = $trnxStatus;
                $transaction->save();
                return true;
            } else {
                // Log an error if plan_data is missing required fields
                Log::error('Plan data is missing required fields.');
                Log::info(json_encode($plan_data));
                return false;
            }
        } catch (\Exception $e) {
            // Log any exceptions that occur during subscription creation
            Log::error('Error creating subscription: ' . $e->getMessage());
            return false;
        }
    }


    /**
     * Display the specified resource.
     */
    public function show($id, $tenure)
    {
        $plan = Plan::findorFail($id);
        $currency_symbol = (get_settings('general_settings')['currency_symbol']);
        $paypal_settings = get_settings('pay_pal_settings');
        $modules = config('taskify.modules');
        return view('subscription-plan.checkout', ['plan' => $plan, 'modules' => $modules, 'tenure' => $tenure, 'currency_symbol' => $currency_symbol, 'paypal_settings' => $paypal_settings]);
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
    public function upload_bank_transfer_document(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'subscription_id' => 'required|exists:subscriptions,id',
            'document' => 'required|file|mimes:jpg,jpeg,png|max:2048', // Adjust file types and size as needed
        ]);

        // Retrieve the validated subscription ID
        $subscriptionId = $request->input('subscription_id');

        // Store the uploaded document
        if ($request->hasFile('document')) {
            $filePath = $request->file('document')->store('bank_transfer_documents', 'public');

            // Save the file path to the database (assuming a column for it exists)
            BankTransferDocument::create([
                'subscription_id' => $request->input('subscription_id'),
                'document_path' => $filePath,
            ]);
            return response()->json(['error' => false, 'message' => 'Document uploaded successfully']);
        }

        return response()->json(['error' => true, 'message' => 'Document upload failed'], 400);
    }
}
