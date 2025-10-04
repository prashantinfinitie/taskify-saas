<?php

namespace App\Http\Controllers\SuperAdmin;

use Carbon\Carbon;
use App\Models\Plan;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $general_settings = get_settings('general_settings');
        $currency_symbol = $general_settings['currency_symbol'];
        $subscriptions = Subscription::select('starts_at', DB::raw('count(*) as subscription_count'))->groupBy('starts_at')->get();
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $customerCounts = $adminRole->users()->orderBy('created_at')
                ->get()->count();
        }
        $thisMonthCustomerCount = $adminRole->users()->whereYear('created_at', Carbon::now()->year)->whereMonth('created_at', Carbon::now()->month)->count();

        $previousMonthCustomerCount = $adminRole->users()->whereYear('created_at', Carbon::now()->subMonth()->year)->whereMonth('created_at', Carbon::now()->subMonth()->month)->count();

        $percentageChange['customer'] = 0;

        if ($previousMonthCustomerCount != 0) {
            $percentageChange['customer'] = (($thisMonthCustomerCount - $previousMonthCustomerCount) / abs($previousMonthCustomerCount)) * 100;
        } else {
            $percentageChange['customer'] = ($thisMonthCustomerCount * 100);
        }



        $totalMonthlyRevenue = Transaction::whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('amount');

        // Calculate total revenue for the previous month
        $previousMonthRevenue = Transaction::whereYear('created_at', Carbon::now()->subMonth()->year)
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->sum('amount');

        // Calculate percentage change in revenue
        $percentageChange['revenue'] = 0;
        if ($previousMonthRevenue != 0) {
            $percentageChange['revenue'] = (($totalMonthlyRevenue - $previousMonthRevenue) / abs($previousMonthRevenue)) * 100;
        } else {
            $percentageChange['revenue'] = ($totalMonthlyRevenue * 100);
        }

        $totalPlans = Plan::count();

        $thisMonthActiveSubscription = Subscription::where('status', 'active')->whereYear('starts_at', Carbon::now()->year)->whereMonth('starts_at', Carbon::now()->month)->count();
        $previousMonthActiveSubscription = Subscription::where('status', 'active')->whereYear('starts_at', Carbon::now()->subMonth()->year)->whereMonth('starts_at', Carbon::now()->subMonth()->month)->count();

        $percentageChange['activeSubscription'] = 0;

        if ($previousMonthActiveSubscription != 0) {
            $percentageChange['activeSubscription'] = (($thisMonthActiveSubscription - $previousMonthActiveSubscription) / abs($previousMonthActiveSubscription)) * 100;
        } else {
            $percentageChange['activeSubscription'] = ($thisMonthActiveSubscription * 100);
        }
        $Statuses = [];

        foreach ($percentageChange as $key => $value) {
            if ($value > 0) {
                $Statuses[$key] = "text-success";
            } elseif ($value < 0) {
                $Statuses[$key] = "text-danger";
            } else {
                $Statuses[$key] = "text-muted";
            }
        }



        return view(
            'superadmin.dashborad.index',
            [
                'subscriptions' => $subscriptions,
                'customerCounts' => $customerCounts,
                'totalMonthlyRevenue' => format_currency($totalMonthlyRevenue),
                'percentageChange' => $percentageChange,
                'currency_symbol' => $currency_symbol,
                'thisMonthCustomerCount' => $thisMonthCustomerCount,
                'previousMonthCustomerCount' => $previousMonthCustomerCount,
                'totalPlans' => $totalPlans,
                'thisMonthActiveSubscription' => $thisMonthActiveSubscription,
                'previousMonthActiveSubscription' => $previousMonthActiveSubscription,
                'Statuses' => $Statuses,
            ]
        );
    }
    public function getCustomersMonthlyCount()
    {
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $customerCounts = $adminRole->users()
                ->selectRaw('COUNT(*) as count, MONTH(created_at) as month')
                ->groupBy('month')
                ->pluck('count', 'month')
                ->toArray();
        }

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        $customerCountsOrdered = [];

        // Fill in counts for each month in their actual order
        foreach ($months as $index => $month) {
            $customerCountsOrdered[] = $customerCounts[$index + 1] ?? 0;
        }

        return response()->json([
            'months' => $months,
            'customerCounts' => $customerCountsOrdered
        ]);
    }

    public function getRevenueData()
    {
        $transactions = Transaction::select('created_at', 'amount')->get();

        // Transforming the data into the required format for the chart
        $revenueData = [];
        foreach ($transactions as $transaction) {
            // Converting the created_at timestamp to milliseconds for ApexCharts
            $timestamp = strtotime($transaction->created_at) * 1000;
            $date = date('Y-m-d', strtotime($transaction->created_at)); // Format the date as needed
            $revenueData[] = ['timestamp' => $timestamp, 'date' => $date, 'amount' => ($transaction->amount)];
        }

        $decimal_point = get_settings('general_settings')['decimal_points_in_currency'];
        $response['revenueData'] = $revenueData;
        $response['decimal_point'] = $decimal_point;
        return response()->json($response);
    }
    public function getSubscriptionRateChart(Request $request) // Unused parameter for consistency
    {
        // Filter subscriptions for active subscriptions only (assuming this is the desired behavior)
        $subscriptions = Subscription::with('plan') // Eager load plan details
            // ->where('status', 'active')
            ->get();

        // Calculate and format data for the chart
        $chartData = [];
        $planNames = [];

        foreach ($subscriptions as $subscription) {
            $planName = $subscription->plan->name;
            $chargingPrice = $subscription->charging_price;

            if (!in_array($planName, array_keys($chartData))) {
                $chartData[$planName] = [];
                $planNames[] = $planName;
            }

            $chartData[$planName][] = $chargingPrice;
        }


        return response()->json([
            'chartData' => $chartData,
        ]);
    }
    public function getActiveSubscriptionsPerPlan()
    {
        $subscriptions = Subscription::with('plan')
            ->where('status', 'active')
            ->get();

        // Calculate the subscription count per plan
        $subscriptionCountPerPlan = [];

        foreach ($subscriptions as $subscription) {
            $planName = $subscription->plan->name;

            if (!array_key_exists($planName, $subscriptionCountPerPlan)) {
                $subscriptionCountPerPlan[$planName] = 0;
            }

            $subscriptionCountPerPlan[$planName]++;
        }

        return response()->json($subscriptionCountPerPlan);
    }

    public function getBestCustomers()
    {
        $search = request('search');
        $sort = request('sort', 'total_earnings');
        $order = request('order', 'DESC');
        $limit = request('limit');

        $bestCustomers = Subscription::with('user')
            ->select('user_id', DB::raw('SUM(charging_price) as total_earnings'))
            ->orderByDesc('total_earnings')
            ->groupBy('user_id')
            ->take($limit);

        // Apply search filter if search term is provided
        if ($search) {
            $bestCustomers
                // ->where('total_earnings', 'like', '%' . $search . '%')
                ->whereHas('user', function ($query) use ($search) {
                    $query->where('first_name', 'like', '%' . $search . '%')
                        ->orWhere('last_name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                })->orderBy($sort, $order);
        }

        // Retrieve the best customers after applying filters
        $bestCustomers = $bestCustomers->get()
            ->map(function ($item) {
                return [
                    'id' => $item->user->id,
                    'name' => $item->user->first_name . ' ' . $item->user->last_name,
                    'email' => $item->user->email,
                    'phone' => $item->user->phone,
                    'total_earnings' =>  "<span class = 'badge fw-bolder  bg-label-primary'> " . format_currency($item->total_earnings) . "</span>",
                ];
            });

        return response()->json([
            'rows' => $bestCustomers,
            'total' => $bestCustomers->count(),
        ]);
    }



    public function getRecentTransactions()
    {
        $search = request('search');
        $sort = request('sort', 'created_at');
        $order = request('order', 'DESC');
        $limit = request('limit', 10);

        $recentTransactions = Transaction::with('user')
            ->orderBy('created_at', 'DESC') // Explicitly order by created_at descending
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('amount', 'like', '%' . $search . '%')
                        ->orWhere('status', 'like', '%' . $search . '%')
                        ->orWhereHas('user', function ($query) use ($search) {
                            $query->where('first_name', 'like', '%' . $search . '%')
                                ->orWhere('last_name', 'like', '%' . $search . '%')
                                ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', '%' . $search . '%')
                                ->orWhere('email', 'like', '%' . $search . '%')
                                ->orWhere('phone', 'like', '%' . $search . '%');
                        });
                });
            })
            ->limit($limit)
            ->get()
            ->map(function ($transaction) {
                switch ($transaction->status) {
                    case 'pending':
                        $status = '<span class="badge bg-label-warning fw-bolder">Pending</span>';
                        break;
                    case 'completed':
                        $status = '<span class="badge bg-label-success fw-bolder">Completed</span>';
                        break;
                    case 'canceled':
                        $status = '<span class="badge bg-label-danger fw-bolder">Canceled</span>';
                        break;
                default:
                    $status = '<span class="badge bg-label-secondary fw-bolder">' . ucfirst($transaction->status) . '</span>';
                }

                return [
                'id' => $transaction->id,
                    'name' => $transaction->user->first_name . ' ' . $transaction->user->last_name,
                'payment_method' => ucfirst($transaction->payment_method),
                    'email' => $transaction->user->email,
                    'phone' => $transaction->user->phone,
                'amount' => format_currency($transaction->amount),
                    'status' => $status,
                'created_at' => format_date($transaction->created_at, true),
                ];
            });

        return response()->json([
            'rows' => $recentTransactions,
            'total' => $recentTransactions->count(),
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
}
