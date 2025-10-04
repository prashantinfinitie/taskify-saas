<?php

namespace App\Http\Controllers\SuperAdmin;

use Carbon\Carbon;
use App\Models\Plan;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Services\DeletionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SubscriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $subscriptions = Subscription::all();
        $plans = Plan::where('status', 'active')->get();
        return view('superadmin.subscriptions.list', ['subscriptions' => $subscriptions, 'plans' => $plans]);
    }
    public function list()
    {
        $search = request('search');
        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        $plan_id = request('plan_id');
        $status = request('status');
        $subscriptions = Subscription::orderBy($sort, $order);
        if ($plan_id) {
            $subscriptions = $subscriptions->where('plan_id', $plan_id);
        }
        if ($status) {
            $subscriptions = $subscriptions->where('status', $status);
        }
        if ($search) {
            $subscriptions = $subscriptions->where(function ($query) use ($search) {
                $query->where('payment_method', 'like', '%' . $search . '%')
                    ->orWhere('id', 'like', '%' . $search . '%')
                    ->orWhere('status', 'like', '%' . $search . '%')
                    ->orWhere('charging_price', 'like', '%' . $search . '%')
                    ->orWhereHas('user', function ($query) use ($search) {
                        $query->where('first_name', 'like', '%' . $search . '%')
                            ->orWhere('last_name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('plan', function ($query) use ($search) {
                        $query->where('name', 'like', '%' . $search . '%');
                    });
            });
        }
        $total = $subscriptions->count();
        $subscriptions = $subscriptions->paginate(request("limit"));
        $subscriptions = $subscriptions->map(function ($subscription) {
            $user = $subscription->user;
            $plan = $subscription->plan;
            switch ($subscription->status) {
                case 'active':
                    $statusBadge = '<span class="badge bg-label-primary">Active</span>';
                    break;
                case 'inactive':
                    $statusBadge = '<span class="badge bg-label-danger">Inactive</span>';
                    break;
                case 'pending':

                    $statusBadge = '<span class="badge bg-label-warning">Pending</span>';
                    // Add verify button if payment method is bank_transfer and status is pending
                    if ($subscription->payment_method === 'bank_transfer') {
                        $statusBadge .= '
                    <button class="btn btn-sm btn-success ms-2 verify-payment"
                            data-subscription-id="' . $subscription->id . '"
                            title="Verify Payment">
                        <i class="bx bxs-check-circle"></i> ' . get_label("verify_payment", "Verify Payment") .
                        ' </button>';
                    }

                    break;
                default:
                    $statusBadge = '<span class="badge bg-label-secondary">' . ucfirst($subscription->status) . '</span>';
                    break;
            }
            // Extract the modules array from the features array
            $featuresArray = json_decode($subscription->features, true);
            $modules = isset($featuresArray['modules']) ? $featuresArray['modules'] : [];

            // Define the other attributes
            $otherAttributes = [
                'Max Projects' => ($featuresArray['max_projects'] == '-1' ? 'Unlimited' : $featuresArray['max_projects']) ?? '',
                'Max Team Members' => ($featuresArray['max_team_members'] == '-1' ? 'Unlimited' : $featuresArray['max_team_members']) ?? '',
                'Max Workspaces' => ($featuresArray['max_workspaces'] == '-1' ? 'Unlimited' : $featuresArray['max_workspaces']) ?? '',
                'Max Clients' => ($featuresArray['max_clients'] == '-1' ? 'Unlimited' : $featuresArray['max_clients']) ?? ''
            ];
            // Generate the list items for all attributes
            $listItems = '';
            foreach ($otherAttributes as $attribute => $value) {
                $listItems .= '<li><strong>' . $attribute . ':</strong> ' . $value . '</li>';
            }
            // Generate the list items for modules
            $modulesListItems = '<li><strong>Modules:</strong></li>';
            $modulesListItems .= '<ul>';
            foreach ($modules as $module) {
                $capitalizedModule = ucfirst($module);
                $modulesListItems .= '<li>' . $capitalizedModule . '</li>';
            }
            $modulesListItems .= '</ul>';
            // Wrap all list items in a ul element
            $list = '<ul>' . $listItems . $modulesListItems . '</ul>';
            return [
                'id' => $subscription->id,
                'user_name' => $user->first_name . ' ' . $user->last_name,
                'plan_name' => ucfirst($plan->name),
                'tenure' => ucfirst($subscription->tenure),
                'start_date' => format_date($subscription->starts_at),
                'end_date' => format_date($subscription->ends_at),
                'payment_method' => $subscription->payment_method === 'bank_transfer'
                    ? ucwords(str_replace('_', ' ', $subscription->payment_method)) .
                    ' (<a href="#" class="view-documents" data-subscription-id="' . $subscription->id . '">' .
                    get_label("view_documents", "View Documents") . '</a>)'
                    : ucwords(str_replace('_', ' ', $subscription->payment_method)),


                'features' => $list,
                'charging_price' => format_currency($subscription->charging_price),
                'charging_currency' => $subscription->charging_currency,
                'status' => $statusBadge,
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
        // Fetch users with admin role
        $users = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->get();
        $currency_symbol = (get_settings('general_settings')['currency_symbol']);
        return view("superadmin.subscriptions.create", ["plans" => $plans, "users" => $users, "currency_symbol" => $currency_symbol]);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plan_id' => 'required|exists:plans,id',
            'user_id' => 'required|exists:users,id',
            'tenure' => 'required|in:monthly,yearly,lifetime',
            'start_date' => 'required|string',
            'end_date' => 'required|string',
            'payment_method' => 'required|in:offline,bank_transfer,payment_gateway',
            'features' => 'required|string',
            'charging_price' => 'required',
            'charging_currency' => 'required',
            'transaction_id' => 'required',
        ]);

        // Check if the validation fails
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check for existing active subscription
        $existingSubscription = Subscription::where('user_id', $request->user_id)
            ->where('status', 'active')
            ->first();

        if ($existingSubscription) {
            return response()->json(['error' => 'User already has an active subscription'], 422);
        }

        try {
            // Get the system date format
            $php_date_format = get_php_date_time_format();

            // Detailed logging
            // dd("Date Processing", [
            //     'start_date_input' => $request->start_date,
            //     'end_date_input' => $request->end_date,
            //     'system_date_format' => $php_date_format
            // ]);

            // Parse dates with error handling
            $startDate = $this->parseDate($request->start_date, $php_date_format);
            $endDate = $this->parseDate($request->end_date, $php_date_format);
            if ($request->tenure === 'lifetime') {
                $endDate = now()->addYears(100);
            }

            // Determine subscription status
            $currentDate = now();
            $status = ($currentDate->gte($startDate) && $currentDate->lte($endDate))
            ? 'active'
            : 'inactive';

            // Create subscription
            $subscription = Subscription::create([
                'plan_id' => $request->plan_id,
                'user_id' => $request->user_id,
                'tenure' => $request->tenure,
                'starts_at' => $startDate->format('Y-m-d'),
                'ends_at' => $endDate->format('Y-m-d'),
                'payment_method' => $request->payment_method,
                'features' => $request->features,
                'charging_price' => $request->charging_price,
                'charging_currency' => $request->charging_currency,
                'status' => $status
            ]);

            // Create transaction
            $transaction = Transaction::create([
                'user_id' => $subscription->user_id,
                'subscription_id' => $subscription->id,
                'amount' => $subscription->charging_price,
                'currency' => $subscription->charging_currency,
                'payment_method' => $subscription->payment_method,
                'status' => "completed",
                'transaction_id' => $request->transaction_id
            ]);

            // Log successful creation
            Log::info("Subscription Created", [
                'subscription_id' => $subscription->id,
                'user_id' => $subscription->user_id
            ]);

            return response()->json([
                'success' => 'Subscription created successfully',
                'redirect_url' => route('subscriptions.index')
            ], 200);
        } catch (\Exception $e) {
            // Comprehensive error logging
            Log::error("Subscription Creation Error", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input_data' => $request->all()
            ]);

            return response()->json([
                'error' => 'Failed to create subscription: ' . $e->getMessage()
            ], 500);
        }
    }

    // Helper method to parse dates with improved handling
    private function parseDate($dateString, $format)
    {
        // Remove any leading zeros from the year
        $dateString = preg_replace('/^0+/', '', $dateString);

        // Parse the date
        $date = Carbon::createFromFormat($format, $dateString);

        // Handle two-digit years
        if ($date->year < 100) {
            $date->year = ($date->year < 50 ? 2000 : 1900) + $date->year;
        }

        return $date;
    }
    /**
     * Display the specified resource.
     */
    public function get(string $id)
    {
        $subscription = Subscription::with(['user', 'plan', 'transactions'])->findOrFail($id);
        return response()->json(['subscription' => $subscription,]);
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $subscription = Subscription::findOrFail($id);
        $users = User::where('id', $subscription->user_id)->get();
        $plans = Plan::where('status', 'active')->get();
        $currency_symbol = (get_settings('general_settings')['currency_symbol']);
        return view('superadmin.subscriptions.upgrade', ['subscription' => $subscription, 'plans' => $plans, 'users' => $users, 'currency_symbol' => $currency_symbol]);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'plan_id' => 'required|exists:plans,id',
            'user_id' => 'required|exists:users,id',
            'tenure' => 'required|in:monthly,yearly,lifetime',
            'start_date' => 'required|string',
            'end_date' => 'required|string',
            'payment_method' => 'required|in:offline,bank_transfer,payment_gateway',
            'features' => 'required|string',
            'charging_price' => 'required',
            'charging_currency' => 'required',
            'transaction_id' => 'required',
        ]);
        // Check if the validation fails
        if ($validator->fails()) {
            // Return validation errors
            return response()->json(['errors' => $validator->errors()], 422);
        }
        // Find the current subscription by ID
        $subscription = Subscription::findOrFail($id);
        // End the current subscription by setting its end date to the current date
        $subscription->ends_at = now()->toDateString();
        $subscription->status = 'inactive';
        $subscription->save();
        $currentDate = now();

        // Define dynamic date format (can be dynamic based on user settings or input)
        $php_date_format = get_php_date_time_format(); // e.g., 'm-d-Y' or 'd-m-Y'

        // Convert start date and end date strings to Carbon objects using the correct format
        $startDate = Carbon::createFromFormat($php_date_format, $request->start_date);
        $endDate = Carbon::createFromFormat($php_date_format, $request->end_date);

        // Check if the current date is between the start and end dates
        if ($currentDate->gte($startDate) && $currentDate->lte($endDate)) {
            $status = 'active';
        } else {
            $status = 'inactive';
        }

        // Format the dates for storage or further use
        $startDate = $startDate->format('Y-m-d');
        $endDate = $endDate->format('Y-m-d');
        // Create a new subscription record with the provided data
        $newSubscription = new Subscription();
        $newSubscription->plan_id = $request->plan_id;
        $newSubscription->user_id = $request->user_id;
        $newSubscription->tenure = $request->tenure;
        $newSubscription->starts_at = $startDate;
        $newSubscription->ends_at = $endDate;
        $newSubscription->payment_method = $request->payment_method;
        $newSubscription->features = $request->features;
        $newSubscription->charging_price = $request->charging_price;
        $newSubscription->charging_currency = $request->charging_currency;
        $newSubscription->status = $status; // Assuming the new subscription is active
        $newSubscription->save();
        $transaction = new Transaction();
        $transaction->user_id = $newSubscription->user_id;
        $transaction->subscription_id = $newSubscription->id;
        $transaction->amount = $newSubscription->charging_price;
        $transaction->currency = $newSubscription->charging_currency;
        $transaction->status = "completed";
        $transaction->payment_method = $newSubscription->payment_method;
        $transaction->transaction_id =   $request->transaction_id;
        $transaction->save();
        // Return a success response
        return response()->json(['success' => 'Subscription updated successfully', 'redirect_url' => route('subscriptions.index')], 200);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $response = DeletionService::delete(Subscription::class, $id, 'Record');
        return $response;
    }
    public function destroy_multiple(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'ids' => 'required|array', // Ensure 'ids' is present and an array
            'ids.*' => 'integer|exists:activity_logs,id' // Ensure each ID in 'ids' is an integer and exists in the table
        ]);
        $ids = $validatedData['ids'];
        // Perform deletion using validated IDs
        foreach ($ids as $id) {
            DeletionService::delete(Subscription::class, $id, 'Record');
        }
        return response()->json(['error' => false, 'message' => 'Record(s) deleted successfully.']);
    }

    public function fetchDocuments(Subscription $subscription)
    {
        $documents = $subscription->bankTransferDocuments()->get()->map(function ($document) {

            return [
                'id' => $document->id,
                'name' => basename($document->document_path),
                'created_at' => $document->created_at,
                'url' => asset('storage/' . $document->document_path), // Assuming you store images in storage
                // Add thumbnail URL if you have separate thumbnails
                'thumbnail' => Storage::url($document->thumbnail_path) // Optional
            ];
        });

        return response()->json([
            'success' => true,
            'documents' => $documents
        ]);
    }
    public function verifyPayment(Subscription $subscription)
    {
        try {
            DB::beginTransaction();

            // Check if transaction exists
            $transaction = Transaction::where('subscription_id', $subscription->id)
                ->where('status', 'pending')
                ->latest()
                ->first();

            if (!$transaction) {
                throw new \Exception('No pending transaction found for this subscription.');
            }

            // Update subscription status
            $subscription->status = 'active';
            $subscription->save();

            // Update transaction status
            $transaction->status = 'completed';
            $transaction->save();



            DB::commit();

            return response()->json([
                'error' => false,
                'message' => 'Payment verified successfully',
                'redirect_url' => route('subscriptions.index')
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => true,
                'message' => 'Failed to verify payment: ' . $e->getMessage()
            ], 500);
        }
    }
}
