<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Models\User;
use Illuminate\Http\Request;
use App\Services\DeletionService;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ManagerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $role = Role::where('name', 'manager')->first();
        $managers = $role ? $role->users : [];

        return view('superadmin.managers.index', ['managers' => $managers]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('superadmin.managers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // $validator =  $request->validate([
        //     'first_name' => 'required|string|regex:/^[^\d]+$/',
        //     'last_name' => 'required|string|regex:/^[^\d]+$/',
        //     'email' => 'required|email|unique:users,email',
        //     'phone' => 'required|string|regex:/^\d+$/|unique:users,phone',
        //     'password' => 'required|string|min:6|confirmed',
        //     'password_confirmation' => 'required',
        //     'country_code' => 'required'
        // ], [
        //     'first_name.required' => 'First name is required.',
        //     'first_name.string' => 'First name must be a string.',
        //     'first_name.regex' => 'First name cannot contain integers.',
        //     'last_name.required' => 'Last name is required.',
        //     'last_name.string' => 'Last name must be a string.',
        //     'last_name.regex' => 'Last name cannot contain integers.',
        //     'email.required' => 'Email is required.',
        //     'email.email' => 'Invalid email format.',
        //     'email.unique' => 'Email already exists.',
        //     'phone.required' => 'Phone number is required.',
        //     'phone.string' => 'Phone number must be a string.',
        //     'phone.unique' => 'Phone Number already exists.',
        //     'phone.regex' => 'Phone number can only contain digits.',
        //     'password.required' => 'Password is required.',
        //     'password.string' => 'Password must be a string.',
        //     'password.min' => 'Password must be at least 6 characters long.',
        //     'password.confirmed' => 'Password confirmation does not match.',
        // ]);

        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|regex:/^[^\d]+$/',
            'last_name' => 'required|string|regex:/^[^\d]+$/',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|regex:/^\d+$/|unique:users,phone',
            'password' => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required',
            'country_code' => 'required',
            'country_iso_code' => 'nullable',
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
            return response()->json([
                'error' => true,
                'errors' => $validator->errors(),
            ], 422);
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

        $user->assignRole('manager');
        return response()->json(['error' => false, 'message' => 'Manager registered successfully', 'redirect_url' => route('managers.index')], 201);
    }

    public function list()
    {
        // dd('here');
        $search = request('search');
        $sort = request('sort', 'id');
        $order = request('order', 'DESC');
        $limit = request('limit');

        // Find the "admin" role
        $managerRole = Role::where('name', 'manager')->first();

        if ($managerRole) {
            // Retrieve users with the "admin" role
            $managers = $managerRole->users();

            // Apply search filter
            if ($search) {
                $managers->where(function ($query) use ($search) {
                    $query->where('first_name', 'like', '%' . $search . '%')
                        ->orWhere('last_name', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            }

            // Apply sorting
            $managers->orderBy($sort, $order);

            // Pagination
            $managers = $managers->paginate($limit);

            // Transform the data as needed
            $managers = $managers->map(function ($manager) {
                $status = $manager->status == '1' ? '<span class="badge bg-label-primary">Active</span>' : '<span class="badge bg-label-danger">Not Active</span>';
                return [
                    'id' => $manager->id,
                    'first_name' => $manager->first_name,
                    'last_name' => $manager->last_name,
                    'email' => $manager->email,
                    'phone' => $manager->country_code . ' ' . $manager->phone,
                    'status' => $status,
                ];
            });

            return response()->json([
                'rows' => $managers,
                'total' => count($managers),
            ]);
        }

        return response()->json(['error' => 'Manager role not found'], 404);
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
        $manager = User::find($id);
        return view('superadmin.managers.edit', compact('manager'));
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
            'country_iso_code' => 'nullable',
            'password_confirmation' => 'nullable|required_with:password|same:password',
        ];
        $validatedData = $request->validate($rules);
        $manager = User::find($id);
        $manager->first_name = $validatedData['first_name'];
        $manager->last_name = $validatedData['last_name'];
        $manager->email = $validatedData['email'];
        $manager->phone = $validatedData['phone'];
        $manager->status = $validatedData['status'];
        $manager->country_code  = $validatedData['country_code'];
        $manager->country_iso_code  = $validatedData['country_iso_code'];
        if (isset($validatedData['password']) && !empty($validatedData['password'])) {
            $manager->password = bcrypt($validatedData['password']);
        }

        $manager->update();
        return response()->json(['error' => false, 'message' => 'Manager updated successfully', 'redirect_url' => route('managers.index')]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $response = DeletionService::delete(User::class, $id, 'Record');
        return response()->json(['error' => false, 'message' => 'Record deleted successfully.']);
    }
}
