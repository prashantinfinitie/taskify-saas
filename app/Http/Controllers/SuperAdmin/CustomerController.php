<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Models\User;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Services\DeletionService;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $adminRole = Role::where('name', 'admin')->first();

        if ($adminRole) {
            $customers = $adminRole->users()->get();
        }


        return view('superadmin.customers.index', ['customers' => $customers]);
    }
    public function list()
    {
        $search = request('search');
        $sort = request('sort', 'id');
        $order = request('order', 'DESC');
        $limit = request('limit');

        // Find the "admin" role
        $adminRole = Role::where('name', 'admin')->first();

        if ($adminRole) {
            // Retrieve users with the "admin" role
            $customers = $adminRole->users();

            // Apply search filter
            if ($search) {
                $customers->where(function ($query) use ($search) {
                    $query->where('first_name', 'like', '%' . $search . '%')
                        ->orWhere('last_name', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            }

            // Apply sorting
            $total = $customers->count();
            $customers->orderBy($sort, $order);
            // Pagination
            $customers = $customers->paginate($limit);

            // Transform the data as needed
            $customers = $customers->map(function ($customer) {
                $status = $customer->status == '1' ? '<span class="badge bg-label-primary">Active</span>' : '<span class="badge bg-label-danger">Not Active</span>';
                return [
                    'id' => $customer->id,
                    'first_name' => $customer->first_name,
                    'last_name' => $customer->last_name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'status' => $status,
                ];
            });

            return response()->json([
                'rows' => $customers,
                'total' => $total,
            ]);
        }

        return response()->json(['error' => 'Admin role not found'], 404);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('superadmin.customers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {


        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|regex:/^[^\d]+$/',
            'last_name' => 'required|string|regex:/^[^\d]+$/',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|regex:/^\d+$/|unique:users,phone',
            'password' => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required',
            'country_code' => 'required'
        ], [
            'first_name.required' => 'First name is required.',
            'first_name.string' => 'First name must be a string.',
            'first_name.regex' => 'First name cannot contain integers.',
            'last_name.required' => 'Last name is required.',
            'last_name.string' => 'Last name must be a string.',
            'last_name.regex' => 'Last name cannot contain integers.',
            'email.required' => 'Email is required.',
            'email.email' => 'Invalid email format.',
            'email.unique' => 'Email already exists.',
            'phone.required' => 'Phone number is required.',
            'phone.string' => 'Phone number must be a string.',
            'phone.unique' => 'Phone Number already exists.',
            'phone.regex' => 'Phone number can only contain digits.',
            'password.required' => 'Password is required.',
            'password.string' => 'Password must be a string.',
            'password.min' => 'Password must be at least 6 characters long.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => true, 'message' => $validator->errors()], 422);
        }

        // Create a new user
        $user = new User();
        $user->first_name = $request->first_name;
        $user->last_name =  $request->last_name;
        $user->phone = $request->phone;
        $user->email = $request->email;
        $user->country_code = $request->country_code;
        $user->country_iso_code = $request->country_iso_code;
        $user->password = bcrypt($request->password);
        $user->status = '1';
        $user->email_verified_at = now()->tz(config('app.timezone'));
        $user->save();

        $user->assignRole('admin');
        // Create a new admin with the user ID
        $admin = new Admin();
        $admin->user_id = $user->id;

        $admin->save();

        return response()->json(['error' => false, 'message' => 'Admin registered successfully', 'redirect_url' => route('customers.index')], 201);
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

        $customer = User::find($id);
        return view('superadmin.customers.edit', compact('customer'));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $rules = [
            'first_name' => ['required'],
            'last_name' => ['required'],
            'email' => [
                'required',
                'email',
                'unique:users,email,' . $id,
            ],
            'phone' => [
                'required',
                'string',
                'regex:/^\d+$/',
                'unique:users,phone,' . $id,
            ],
            'status' => ['required'],
            'password' => 'nullable|min:6',
            'country_code' => 'required',
            'password_confirmation' => 'nullable|required_with:password|same:password',
            'country_iso_code' => 'nullable',
        ];
        $validatedData = $request->validate($rules);
        $customer = User::find($id);
        $customer->first_name = $validatedData['first_name'];
        $customer->last_name = $validatedData['last_name'];
        $customer->email = $validatedData['email'];
        $customer->phone = $validatedData['phone'];
        $customer->status = $validatedData['status'];
        $customer->country_code  = $validatedData['country_code'];
        $customer->country_iso_code  = $validatedData['country_iso_code'];
        if (isset($validatedData['password']) && !empty($validatedData['password'])) {
            $customer->password = bcrypt($validatedData['password']);
        }

        $customer->update();
        return response()->json(['error' => false, 'message' => 'Customer updated successfully', 'redirect_url' => route('customers.index')]);



    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $response = DeletionService::delete(User::class, $id, 'Record');
        return response()->json(['error' => false, 'message' => 'Record deleted successfully.']);
        // return response()->json(['success' => true, 'message' => 'Deleted record successfully', 'redirect_url' => route('customers.index')]);
    }
    public function destroy_multiple(Request $request)
    {
        // Validate the incoming reques t

        $validatedData = $request->validate([
            'ids' => 'required|array', // Ensure 'ids' is present and an array
            'ids.*' => 'integer|exists:users,id' // Ensure each ID in 'ids' is an integer and exists in the table
        ]);

        $ids = $validatedData['ids'];
        // Perform deletion using validated IDs
        foreach ($ids as $id) {
            DeletionService::delete(User::class, $id, 'Record');
        }

        return response()->json(['error' => false, 'message' => 'Record(s) deleted successfully.']);
    }
}
