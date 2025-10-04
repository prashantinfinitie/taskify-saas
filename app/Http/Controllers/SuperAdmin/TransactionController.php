<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::whereHas('roles', function ($user) {
            $user->where('name', 'admin');
        })->get();

        return view('superadmin.transactions.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function list()
    {
        $search = request('search');
        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        $transactions = Transaction::orderBy($sort, $order);
        $user_id = request('user_id');
        if ($user_id) {
            $transactions = $transactions->where('user_id', $user_id);
        }
        if ($search) {
            $transactions = $transactions->where(function ($query) use ($search) {
                $query->where('payment_method', 'like', '%' . $search . '%')
                    ->orWhere('id', 'like', '%' . $search . '%')
                    ->orWhere('amount', 'like', '%' . $search . '%')
                    ->orWhere('currency', 'like', '%' . $search . '%')

                    ->orWhereHas('user', function ($query) use ($search) {
                        $query->where('first_name', 'like', '%' . $search . '%')
                    ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', '%' . $search . '%')
                            ->orWhere('last_name', 'like', '%' . $search . '%');
                    });
            });
        }
        $total = $transactions->count();
        $transactions = $transactions->paginate(request("limit"));
        $transactions = $transactions->map(function ($transaction) {
            $user = $transaction->user;
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
                'user_name' => ucwords($user->first_name . ' ' . $user->last_name),
                'payment_method' => ucwords(str_replace('_', ' ', $transaction->payment_method)),
                'amount' => format_currency($transaction->amount),
                'currency' => $transaction->currency,
                'status' => $status,
                'transaction_id' => $transaction->transaction_id,
                'created_at' => format_date($transaction->created_at),

            ];
        });

        return response()->json([
            "rows" => $transactions,
            "total" => $total,
        ]);
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
