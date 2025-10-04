<?php

namespace App\Http\Controllers;

use Throwable;
use App\Models\Task;
use App\Models\User;
use App\Models\Admin;
use App\Models\Client;
use App\Models\Project;
use App\Models\TaskUser;
use App\Models\Template;
use App\Models\Workspace;
use App\Models\TeamMember;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\DeletionService;
use GuzzleHttp\Promise\TaskQueue;
use App\Notifications\VerifyEmail;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Notifications\AccountCreation;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\RateLimiter;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Contracts\Role as ContractsRole;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Spatie\Permission\Traits\HasRoles;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $workspace = Workspace::find(session()->get('workspace_id'));
        $users = $workspace->users;
        $roles = Role::where('guard_name', 'web')
            ->whereNotIn('name', ['superadmin', 'admin', 'manager'])
            ->get();

        return view('users.users', ['users' => $users, 'roles' => $roles]);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles = Role::where('guard_name', 'web')
            ->whereNotIn('name', ['superadmin', 'admin', 'manager'])
            ->get();
        return view('users.create_user', ['roles' => $roles]);
    }

    /**
     * Create a new user
     *@group User Managemant
     * This endpoint allows admins to create a new user within a workspace.
     * Email uniqueness is checked across both users and clients.
     * Optionally, a verification email and account creation email are sent based on system configuration.
     *
     * @bodyParam first_name string required The user's first name. Example: John
     * @bodyParam last_name string required The user's last name. Example: Doe
     * @bodyParam email string required The user's unique email address. Must not exist in users or clients. Example: john.smith@example.com
     * @bodyParam password string required The password (minimum 6 characters). Example: secret123
     * @bodyParam password_confirmation string required Must match the password. Example: secret123
     * @bodyParam address string The user's address. Example: 123 Main St
     * @bodyParam phone string The user's phone number. Example: +1234567890
     * @bodyParam country_code string Country dialing code. Example: +91
     * @bodyParam city string The city. Example: Mumbai
     * @bodyParam state string The state. Example: Maharashtra
     * @bodyParam country string The country. Example: India
     * @bodyParam zip string The zip/postal code. Example: 400001
     * @bodyParam dob date The user's date of birth (in 'Y-m-d' format). Example: 1990-01-01
     * @bodyParam doj date The user's date of joining (in 'Y-m-d' format). Example: 2022-01-01
     * @bodyParam role string required The role to assign to the user. Example: admin
     * @bodyParam country_iso_code string Optional country ISO code. Example: IN
     * @bodyParam require_ev boolean Whether email verification is required. 1 = yes, 0 = no. Example: 1
     * @bodyParam status boolean Whether the user account should be active immediately. Example: 1
     *

     * @header Accept application/json
     * @header workspace_id 2
     *
     * @response 200 {
     *  "error": false,
     *  "message": "User created successfully.",
     *  "id": 54,
     *  "data": {
     *      "id": 54,
     *      "first_name": "John",
     *      "last_name": "Doe",
     *      "full_name": "John Doe",
     *      "email": "john.smith@example.com",
     *      "phone": "+1234567890",
     *      "address": "123 Main St",
     *      "country_code": "+91",
     *      "city": "Mumbai",
     *      "state": "Maharashtra",
     *      "country": "India",
     *      "zip": "400001",
     *      "dob": null,
     *      "doj": null,
     *      "role": "admin",
     *      "status": 1,
     *      "email_verified": false,
     *      "photo_url": "http://localhost:8000/storage/photos/no-image.jpg",
     *      "created_at": "2025-06-11 06:48:45",
     *      "updated_at": "2025-06-11 06:48:45",
     *      "require_ev": 1
     *  }
     * }
     *
     * @response 422 {
     *  "errors": {
     *      "email": ["The email has already been taken in users or clients."]
     *  }
     * }
     *
     * @response 500 {
     *  "error": true,
     *  "message": "User couldn't be created, please make sure email settings are operational."
     * }
     */


    public function store(Request $request, User $user)
    {
        $isApi = request()->get('isApi', false);
        try {

            $adminId = getAdminIdByUserRole();
            ini_set('max_execution_time', 300);
            $formFields = $request->validate([
                'first_name' => ['required'],
                'last_name' => ['required'],
                'email' => ['required', 'email', 'unique:users,email'],
                'password' => 'required|min:6',
                'password_confirmation' => 'required|same:password',
                'address' => 'nullable',
                'phone' => 'nullable',
                'country_code' => 'nullable',
                'city' => 'nullable',
                'state' => 'nullable',
                'country' => 'nullable',
                'zip' => 'nullable',
                'dob' => 'nullable',
                'doj' => 'nullable',
                'role' => 'required',
                'country_iso_code' => 'nullable',
                // 'profile' => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:2048'], // max:2048 = 2MB
            ]);
            $validator = Validator($formFields)->after(function ($validator) use ($request) {
                $email = $request->input('email');

                $existsInUsers = DB::table('users')->where('email', $email)->exists();
                $existsInClients = DB::table('clients')->where('email', $email)->exists();

                if ($existsInUsers || $existsInClients) {
                    $validator->errors()->add('email', 'The email has already been taken in users or clients.');
                }
            });
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $workspaceId =  session()->get('workspace_id') ?? $request->header('workspace_id');
            $workspace = \App\Models\Workspace::find($workspaceId);
            // dd($workspace);
            // dd($workspace);
            if ($request->input('dob')) {
                $dob = $request->input('dob');
                $formFields['dob'] = format_date($dob, false, app('php_date_format'), 'Y-m-d');
            }
            if ($request->input('doj')) {
                $doj = $request->input('doj');
                $formFields['doj'] = format_date($doj, false, app('php_date_format'), 'Y-m-d');
            }
            $password = $request->input('password');
            $formFields['password'] = bcrypt($password);
            if ($request->hasFile('photo')) {
                $formFields['photo'] = $request->file('photo')->store('photos', 'public');
            } else {
                $formFields['photo'] = 'photos/no-image.jpg';
            }
            // $require_ev = isAdminOrHasAllDataAccess() && $request->has('require_ev') && $request->input('require_ev') == 0 ? 0 : 1;
            // $status = getAuthenticatedUser()->hasRole('admin') && $request->has('status') && $request->input('status') == 1 ? 1 : 0;
            // if ($status == 1) {
            //     $formFields['status'] = 1;
            //     $formFields['email_verified_at'] = now()->tz(config('app.timezone'));
            // }
            $require_ev = isAdminOrHasAllDataAccess() && $request->has('require_ev') && $request->input('require_ev') == 0 ? 0 : 1;
            $status = isAdminOrHasAllDataAccess() && $request->has('status') && $request->input('status') == 1 ? 1 : 0;
            $formFields['email_verified_at'] = $require_ev == 0 ? now()->tz(config('app.timezone')) : null;
            $formFields['status'] = $status;
            // dd($formFields);

            $user = User::create($formFields);
            TeamMember::create([
                'admin_id' => $adminId,
                'user_id' => $user->id,
            ]);
            try {
                if ($require_ev == 1) {
                    $user->notify(new VerifyEmail($user));
                }
                $workspace->users()->attach($user->id);
                // dd($workspace);
                $role = Role::find($request->input('role'));
                $user->assignRole($role);
                if (isEmailConfigured()) {
                    $account_creation_template = Template::where('type', 'email')
                        ->where('name', 'account_creation')
                        ->first();
                    if (!$account_creation_template || ($account_creation_template->status !== 0)) {
                        $user->notify(new AccountCreation($user, $password));
                    }
                }
                Session::flash('message', 'User created successfully.');
                $data = formatUser($user);
                $data['require_ev'] = $require_ev;
                return formatApiResponse(
                    false,
                    'User created successfully.',
                    [
                        'id' => $user->id,
                        'data' => $data
                    ]
                );
            } catch (TransportExceptionInterface $e) {
                // Rollback user creation on email transport failure
                $user->delete();
                return response()->json(['error' => true, 'message' => 'User couldn\'t be created, please make sure email settings are operational.'], 500);
            } catch (Throwable $e) {
                // Rollback user creation on other errors
                $user->delete();

                return response()->json(['error' => true, 'message' => 'User couldn\'t be created, please try again later.'], 500);
            }
        } catch (ValidationException $e) {
            return formatApiValidationError($isApi, $e->errors());
        }
    }


    public function email_verification()
    {
        $user = getAuthenticatedUser();
        if (!$user->hasVerifiedEmail()) {
            return view('auth.verification-notice');
        } else {
            return redirect(route('home.index'));
        }
    }
    public function resend_verification_link(Request $request)
    {
        if (isEmailConfigured()) {
            $request->user()->sendEmailVerificationNotification();
            return back()->with('message', 'Verification link sent.');
        } else {
            return back()->with('error', 'Verification link couldn\'t sent.');
        }
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit_user($id)
    {
        $user = User::findOrFail($id);
        $roles = Role::where('guard_name', 'web')
            ->whereNotIn('name', ['superadmin', 'manager'])
            ->get();
        return view('users.edit_user', ['user' => $user, 'roles' => $roles]);
    }

    public function update_user(Request $request, $id)
    {

        $formFields = $request->validate([
            'first_name' => ['required'],
            'last_name' => ['required'],
            'email' => 'nullable|email|unique:users,email,' . $id,
            'phone' => 'nullable',
            'country_code' => 'nullable',
            'address' => 'nullable',
            'city' => 'nullable',
            'state' => 'nullable',
            'country' => 'nullable',
            'zip' => 'nullable',
            'dob' => 'nullable',
            'doj' => 'nullable',
            'password' => 'nullable|min:6',
            'password_confirmation' => 'required_with:password|same:password',
            'country_iso_code' => 'nullable',
            'upload' => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:2048'], // max:2048 = 2MB
        ]);

        $user = User::findOrFail($id);
        if ($request->input('dob')) {
            $dob = $request->input('dob');
            $formFields['dob'] = format_date($dob, false, app('php_date_format'), 'Y-m-d');
        }
        if ($request->input('doj')) {
            $doj = $request->input('doj');
            $formFields['doj'] = format_date($doj, false, app('php_date_format'), 'Y-m-d');
        }
        if ($request->hasFile('upload')) {
            if ($user->photo != 'photos/no-image.jpg' && $user->photo !== null)
                Storage::disk('public')->delete($user->photo);

            $formFields['photo'] = $request->file('upload')->store('photos', 'public');
        }

        $status = isAdminOrHasAllDataAccess() && $request->has('status') ? $request->input('status') : $user->status;
        $formFields['status'] = $status;

        if (isAdminOrHasAllDataAccess() && isset($formFields['password']) && !empty($formFields['password'])) {
            $formFields['password'] = bcrypt($formFields['password']);
        } else {
            unset($formFields['password']);
        }

        if (!($user->hasRole('admin'))) {
            $role = Role::find($request->input('role'));
            $user->syncRoles($role->name);
        }


        $user->update($formFields);

        Session::flash('message', 'Profile details updated successfully.');
        return response()->json(['error' => false, 'id' => $user->id, 'message' => 'User updated successfully.']);
    }

    /**
     * Update user details.
     *
     * This endpoint allows updating user profile information. The request must be sent in **raw JSON format**.
     *
     * 📎 Required Headers:
     * - `Authorization: Bearer {YOUR_API_TOKEN}`
     * - `workspace_id: {WORKSPACE_ID}`
     * - `Content-Type: application/json`
     *
     * @group User Managemant
     *
     * @authenticated
     *
     * @urlParam id integer required The ID of the user to update. Example: 18
     *

     * @header workspace_id 2
     * @header Content-Type application/json
     *
     * @bodyParam first_name string required The user's first name. Example: John
     * @bodyParam last_name string required The user's last name. Example: Doe
     * @bodyParam phone string nullable The user's phone number. Example: +1234567890
     * @bodyParam country_code string nullable Country calling code. Example: US
     * @bodyParam address string nullable Street address. Example: 123 Main St
     * @bodyParam city string nullable City name. Example: New York
     * @bodyParam state string nullable State name. Example: NY
     * @bodyParam country string nullable Country name. Example: USA
     * @bodyParam zip string nullable Zip or postal code. Example: 10001
     * @bodyParam dob date nullable Date of birth (Y-m-d). Example: 1990-01-01
     * @bodyParam doj date nullable Date of joining (Y-m-d). Example: 2020-01-01
     * @bodyParam password string nullable Minimum 6 characters to change password. Example: newsecret
     * @bodyParam password_confirmation string required_with:password Must match the password. Example: newsecret
     * @bodyParam country_iso_code string nullable ISO country code. Example: US
     * @bodyParam status boolean nullable Whether the user is active. Example: true
     * @bodyParam role string nullable Role to assign (not for admins). Example: admin
     *
     * @response 200 {
     *   "error": false,
     *   "message": "User updated successfully.",
     *   "id": 18,
     *   "data": {
     *     "id": 18,
     *     "first_name": "John",
     *     "last_name": "Doe",
     *     "full_name": "John Doe",
     *     "email": "john.doe27@example.com",
     *     "phone": "+1234567890",
     *     "address": "123 Main St",
     *     "country_code": "US",
     *     "city": "New York",
     *     "state": "NY",
     *     "country": "USA",
     *     "zip": "10001",
     *     "dob": null,
     *     "doj": null,
     *     "role": "admin",
     *     "status": true,
     *     "email_verified": true,
     *     "photo_url": "http://localhost:8000/storage/photos/no-image.jpg",
     *     "created_at": "2025-06-10 10:00:16",
     *     "updated_at": "2025-06-11 06:41:43"
     *   }
     * }
     *
     * @response 422 {
     *   "error": true,
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "first_name": ["The first name field is required."],
     *     "password": ["The password must be at least 6 characters."]
     *   }
     * }
     */

    public function update(Request $request, $id)
    {
        $isApi = $request->get('api', true);

        try {
            $formFields = $request->validate([
                'first_name' => ['required'],
                'last_name' => ['required'],
                'phone' => 'nullable',
                'country_code' => 'nullable',
                'address' => 'nullable',
                'city' => 'nullable',
                'state' => 'nullable',
                'country' => 'nullable',
                'zip' => 'nullable',
                'dob' => 'nullable',
                'doj' => 'nullable',
                'password' => 'nullable|min:6',
                'password_confirmation' => 'required_with:password|same:password',
                'country_iso_code' => 'nullable',
                'upload' => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:2048'],
                'role' => 'nullable|string',
                'status' => 'nullable|boolean'
            ]);

            $user = User::findOrFail($id);

            // Format date fields
            if ($request->filled('dob')) {
                $formFields['dob'] = format_date($request->input('dob'), false, app('php_date_format'), 'Y-m-d');
            }

            if ($request->filled('doj')) {
                $formFields['doj'] = format_date($request->input('doj'), false, app('php_date_format'), 'Y-m-d');
            }

            // Handle profile image
            if ($request->hasFile('upload')) {
                if ($user->photo && $user->photo !== 'photos/no-image.jpg') {
                    Storage::disk('public')->delete($user->photo);
                }

                $formFields['photo'] = $request->file('upload')->store('photos', 'public');
            }

            // Update password if provided
            if (isAdminOrHasAllDataAccess() && !empty($formFields['password'])) {
                $formFields['password'] = bcrypt($formFields['password']);
            } else {
                unset($formFields['password']);
            }

            // Status handling
            $formFields['status'] = isAdminOrHasAllDataAccess() && $request->has('status')
                ? $request->input('status')
                : $user->status;

            // Role assignment (skip if user is admin)
            if (!$user->hasRole('admin') && $request->filled('role')) {
                $role = Role::find($request->input('role'));
                $user->syncRoles($role);
            }

            $user->update($formFields);

            return formatApiResponse($isApi, 'User updated successfully.', [
                'id' => $user->id,
                'data' => formatUser($user),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return formatApiValidationError($isApi, $e->validator->errors());
        } catch (\Throwable $e) {
            return formatApiResponse($isApi, 'User could not be updated.', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
        }
    }

    public function update_photo(Request $request, $id)
    {
        if ($request->hasFile('upload')) {
            $old = User::findOrFail($id);
            if ($old->photo != 'photos/no-image.jpg' && $old->photo !== null)
                Storage::disk('public')->delete($old->photo);
            $formFields['photo'] = $request->file('upload')->store('photos', 'public');
            User::findOrFail($id)->update($formFields);
            return back()->with('message', 'Profile picture updated successfully.');
        } else {
            return back()->with('error', 'No profile picture selected.');
        }
    }

    /**
     * Delete a user
     *@group User Managemant
     * This endpoint deletes a user by their ID. It also removes all associated todos for the user. If the user does not exist, a 404 error is returned.

     * @header workspace_id 2
     * @urlParam id integer required The ID of the user to delete. Example: 6
     *
     * @response 200 {
     *   "error": false,
     *   "message": "User deleted successfully.",
     *   "id": "6",
     *   "title": "John Doe",
     *   "data": []
     * }
     *
     * @response 404 {
     *   "message": "No query results for model [App\\Models\\User] 999"
     * }
     *
     * @response 500 {
     *   "error": true,
     *   "message": "Failed to delete User due to internal server error."
     * }
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */

    public function delete_user($id)
    {
        $user = User::findOrFail($id);
        $response = DeletionService::delete(User::class, $id, 'User');
        $user->todos()->delete();
        return $response;
    }
    public function delete_multiple_user(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'ids' => 'required|array', // Ensure 'ids' is present and an array
            'ids.*' => 'integer|exists:users,id' // Ensure each ID in 'ids' is an integer and exists in the table
        ]);
        $ids = $validatedData['ids'];
        $deletedUsers = [];
        $deletedUserNames = [];
        // Perform deletion using validated IDs
        foreach ($ids as $id) {
            $user = User::findOrFail($id);
            if ($user) {
                $deletedUsers[] = $id;
                $deletedUserNames[] = $user->first_name . ' ' . $user->last_name;
                DeletionService::delete(User::class, $id, 'User');
                $user->todos()->delete();
            }
        }
        return response()->json(['error' => false, 'message' => 'User(s) deleted successfully.', 'id' => $deletedUsers, 'titles' => $deletedUserNames]);
    }
    public function logout(Request $request)
    {
        if (Auth::guard('web')->check()) {
            auth('web')->logout();
        } else {
            auth('client')->logout();
        }
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/')->with('message', 'Logged out successfully.');
    }

    /**
     * Login with Group Authentication
     *
     * This endpoint allows users to log in by providing their email, password, and the group name they belong to.
     * If the credentials are valid and the user belongs to the specified group, a token is returned.
     *@group User Authentication
     * @bodyParam email string required The user's email address. Example: admin@gmail.com
     * @bodyParam password string required The user's password. Example: password

     *
     * @response 200 {
     *   "status": true,
     *   "message": "Login successful.",
     *   "data": {
     *     "user": {
     *       "id": 1,
     *       "name": "John Doe",
     *       "email": "john@example.com",
     *       "group": "admin"
     *     },
     *     "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
     *   }
     * }
     *
     * @response 422 {
     *   "status": false,
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "email": ["The email field is required."],
     *     "password": ["The password field is required."],
     *     "group_name": ["The group name field is required."]
     *   }
     * }
     *
     * @response 401 {
     *   "status": false,
     *   "message": "Invalid email or password."
     * }
     *
     * @response 403 {
     *   "status": false,
     *   "message": "User does not belong to the required group."
     * }
     */


    public function login(Request $request)
    {
        if ($request->isMethod('get')) {
            return view('front-end.login');
        }

        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::guard('web')->attempt($credentials)) {
            return redirect()->route('dashboard');
        }

        if (Auth::guard('client')->attempt($credentials)) {
            return redirect()->route('client.dashboard'); // Optional for web clients
        }

        return redirect()->back()->withErrors(['email' => 'Invalid credentials']);
    }

    /**
     * login
     *@group User Authentication
     * This endpoint allows a user or client to authenticate using email and password. It applies rate limiting and returns a Bearer token upon successful login.
     *
     * @bodyParam email string required The email address of the account. Example: admin@gmail.com
     * @bodyParam password string required The password for the account. Example: 123456
     *
     * @response 200 {
     *   "error": false,
     *   "message": "User login successful",
     *   "access_token": "1|X1Y2Z3TOKENEXAMPLE",
     *   "token_type": "Bearer",
     *   "account_type": "user",
     *   "role": "admin",
     *   "user": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "johndoe@example.com",
     *     ...
     *   },
     *   "redirect_url": "http://yourapp.com/workspaces/edit/1"
     * }
     *
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "email": ["The email field is required."],
     *     "password": ["The password field is required."]
     *   }
     * }
     *
     * @response 429 {
     *   "error": true,
     *   "message": "Too many login attempts. Please try again in 60 seconds."
     * }
     *
     * @response 401 {
     *   "error": true,
     *   "message": "Invalid credentials. Please try again."
     * }
     *
     * @response 500 {
     *   "error": true,
     *   "message": "An unexpected error occurred. Please try again later."
     * }
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */


    public function authenticate(Request $request)
    {
        // Check if the request is coming from an API
        $isApi = $request->get('isApi', false); // Default to false if not provided

        // Check if reCAPTCHA is globally enabled
        $isRecaptchaEnabled = (int) $this->getSetting('enable_recaptcha', 0) === 1;

        // Validation rules
        $rules = [
            'email' => ['required', 'email'],
            'password' => 'required',
        ];

        // Only add reCAPTCHA validation if it's enabled and the request is not from an API
        if ($isRecaptchaEnabled && !$isApi) {
            $rules['g-recaptcha-response'] = 'required|captcha';
        }

        // Custom validation messages
        $messages = [
            'g-recaptcha-response.required' => 'reCAPTCHA verification is required.',
            'g-recaptcha-response.captcha' => 'reCAPTCHA verification failed.',
        ];

        // Validate request
        $credentials = $request->validate($rules, $messages);

        $maxLoginAttempts = (int) $this->getSetting('max_login_attempts', 5);
        $decayTime = (int) $this->getSetting('time_decay', 1) * 60;
        $throttleKey = Str::lower($credentials['email']) . '|' . $request->ip();

        if ($maxLoginAttempts > 0 && $this->hasTooManyLoginAttempts($throttleKey, $maxLoginAttempts)) {
            return $this->sendLockoutResponse($throttleKey);
        }

        $account = $this->findAccount($credentials['email']);
        if (!$account) {
            return $this->handleFailedLogin($throttleKey, $maxLoginAttempts, $decayTime);
        }

        $loginAttempt = $this->attemptLogin($account, $credentials['password']);
        if ($loginAttempt === true) {
            return $this->sendSuccessResponse($request, $account);
        } elseif (is_array($loginAttempt) && isset($loginAttempt['error'])) {
            return response()->json($loginAttempt);
        }

        return $this->handleFailedLogin($throttleKey, $maxLoginAttempts, $decayTime);
    }
 protected function sendSuccessResponse(Request $request, $account)
    {
        RateLimiter::clear($account->email . '|' . $request->ip());
        $account_type = $account instanceof \App\Models\User ? 'user' : 'client';
        $guard = $account_type === 'user' ? 'web' : 'client';
        auth($guard)->login($account);
        // Get primary workspace or fallback
        $workspace = $account->workspaces->where('is_primary', '1')->first()
            ?? $account->workspaces->first();
        // if (!$workspace) {
        //     return response()->json([
        //         'error' => true,
        //         'message' => 'No workspace found for this account. Please create a workspace to continue.',
        //         'redirect_url' => route('workspaces.create'),
        //     ], 400);
        // }
        $workspace_id = $workspace->id ?? '0';
        $locale = $account->lang ?? 'en';
        // :white_check_mark: Safe session handling: Only use session if available
        if ($request->hasSession()) {
            session()->put([
                'user_id' => $account->id,
                'workspace_id' => $workspace_id,
                'my_locale' => $locale,
                'locale' => $locale,
                'account_type' => $account_type
            ]);
            $request->session()->regenerate();
            Session::flash('message', 'Logged in successfully.');
        }
        // API: always return token
        $token = $account->createToken('auth_token')->plainTextToken;
        $role = $account_type === 'user' ? $account->getRoleNames()->first() : 'client';
        // Generate redirect URL
        $redirectUrl = route('workspaces.edit', ['id' => $workspace_id]);

        return formatApiResponse(false, ucfirst($role) . ' login successful', [
        'token' => $token,
        'data' => formatAccount($account, $workspace_id), // Pass workspace_id to formatAccount
    ]);

        // return response()->json([
        //     'error' => false,
        //     'message' => ucfirst($role) . ' login successful',
        //     'access_token' => $token,
        //     'token_type' => 'Bearer',
        //     'account_type' => $account_type,
        //     'role' => $role,
        //     'workspace_id' => $workspace_id,
        //     'user' => $account,
        //     'redirect_url' => $request->redirect_url ?? $redirectUrl,
        // ]);
    }




    protected function findAccount(string $email)
    {
        return User::where('email', $email)->first() ?? Client::where('email', $email)->first();
    }

    protected function attemptLogin($account, string $password)
    {

        if (!Hash::check($password, $account->password)) {
            return false;
        }

        // If the account is a User
        if ($account instanceof User) {
            // Check if the user is an admin or if the account is active (status = 1)
            if ($account->hasRole('admin') || $account->status == 1) {
                return true;
            } else {
                // Return a custom error message if the status is not 1
                return ['error' => true, 'message' => get_label('status_not_active', 'Your account is currently inactive. Please contact admin for assistance.')];
            }
        }

        // If the account is a Client
        if ($account instanceof Client) {
            if ($account->status == 1) {
                return true;
            } else {
                // Return a custom error message if the status is not 1
                return ['error' => true, 'message' => get_label('status_not_active', 'Your client account is currently inactive. Please contact admin for assistance.')];
            }
        }

        return false;
    }





    /**
     * Handle a failed login attempt.
     *
     * @param string $throttleKey The RateLimiter key to throttle the login attempts.
     * @param int $maxLoginAttempts The maximum number of login attempts to allow.
     * @param int $decayTime The number of seconds to delay the next login attempt.
     *
     * @return \Illuminate\Http\JsonResponse The JSON response containing the error message.
     */

    protected function handleFailedLogin($throttleKey, $maxLoginAttempts, $decayTime)
    {
        if ($maxLoginAttempts > 0) {
            RateLimiter::hit($throttleKey, $decayTime);
        }
        // dd($throttleKey , $maxLoginAttempts, $decayTime);

        return response()->json([
            'error' => true,
            'message' => 'Invalid credentials!'
        ]);
    }


    /**
     * Check if the given key has exceeded the maximum allowed login attempts.
     *
     * @param string $key The key to check for rate limiting.
     * @param int $maxAttempts The maximum allowed login attempts.
     *
     * @return bool Whether the given key has exceeded the maximum allowed login attempts.
     */

    protected function hasTooManyLoginAttempts($key, $maxAttempts)
    {
        return RateLimiter::tooManyAttempts($key, $maxAttempts);
    }


    /**
     * Sends a JSON response indicating that the user is locked out due to too many login attempts.
     *
     * @param string $key The key to check for rate limiting.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    protected function sendLockoutResponse($key)
    {
        $seconds = RateLimiter::availableIn($key);
        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;

        $timeString = '';
        if ($minutes > 0) {
            $timeString .= $minutes . ' minute' . ($minutes > 1 ? 's' : '');
            if ($remainingSeconds > 0) {
                $timeString .= ' and ';
            }
        }
        if ($remainingSeconds > 0 || $minutes == 0) {
            $timeString .= $remainingSeconds . ' second' . ($remainingSeconds != 1 ? 's' : '');
        }

        return response()->json([
            'error' => true,
            'message' => "Too many login attempts. Please try again in $timeString."
        ]);
    }

    protected function getSetting($name, $default = null)
    {
        $security_settings = get_settings('security_settings', true);
        return $security_settings[$name] ?? $default;
    }
    /**
     * Register a new user
     *
     * This endpoint allows a new user to register with first name, last name, email, phone, and password.
     * The system ensures the email and phone are unique across both users and clients.
     * Upon successful registration, the user is assigned the "admin" role, an admin record is created, and a token is issued.
     *
     * @group  User Authentication
     *
     * @bodyParam first_name string required The first name of the user. Must not contain numbers. Example: John
     * @bodyParam last_name string required The last name of the user. Must not contain numbers. Example: ramanandi
     * @bodyParam email string required Must be a valid email and unique across users and clients. Example: bhurabhai@example.com
     * @bodyParam phone string required Must be a string of digits and unique among users. Example: 9876543210
     * @bodyParam password string required Minimum 6 characters. Example: secret123
     * @bodyParam password_confirmation string required Must match the password. Example: secret123
     *
     * @response 200 scenario="Successful Registration" {
     *   "error": false,
     *   "message": "User registered successfully",
     *   "redirect_url": "http://localhost:8000/login",
     *   "access_token": "1|ABCDEF1234567890...",
     *   "token_type": "Bearer"
     * }
     *
     * @response 422 scenario="Validation Failed" {
     *   "error": true,
     *   "message": {
     *     "email": [
     *       "The email has already been taken in users or clients."
     *     ],
     *     "password": [
     *       "Password must be at least 6 characters long."
     *     ]
     *   }
     * }
     *
     * @response 500 scenario="Server Error" {
     *   "error": true,
     *   "message": "Something went wrong on the server."
     * }
     */


    public function register(Request $request)
    {
        // Check if the request is coming from an API
        $isApi = $request->get('isApi', false); // Default to false if not provided

        // Check if reCAPTCHA is globally enabled
        $isRecaptchaEnabled = (int) $this->getSetting('enable_recaptcha', 0) === 1;

        // Validation rules
        $rules = [
            'first_name' => 'required|string|regex:/^[^\d]+$/',
            'last_name' => 'required|string|regex:/^[^\d]+$/',
            'email' => [
                'required',
                'email',
                'unique:users,email',
                'unique:clients,email',
            ],
            'phone' => [
                'required',
                'string',
                'regex:/^\d+$/',
                'unique:users,phone',
            ],
            'password' => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required'
        ];

        // Only add reCAPTCHA validation if it's enabled and the request is not from an API
        if ($isRecaptchaEnabled && !$isApi) {
            $rules['g-recaptcha-response'] = 'required|captcha';
        }

        // Custom validation messages
        $messages = [
            'first_name.required' => 'First name is required.',
            'first_name.string' => 'First name must be a string.',
            'first_name.regex' => 'First name cannot contain numbers.',
            'last_name.required' => 'Last name is required.',
            'last_name.string' => 'Last name must be a string.',
            'last_name.regex' => 'Last name cannot contain numbers.',
            'email.required' => 'Email is required.',
            'email.email' => 'Invalid email format.',
            'email.unique' => 'The email has already been taken in users or clients.',
            'phone.required' => 'Phone number is required.',
            'phone.string' => 'Phone number must be a string.',
            'phone.unique' => 'The phone number has already been taken.',
            'phone.regex' => 'Phone number can only contain digits.',
            'password.required' => 'Password is required.',
            'password.string' => 'Password must be a string.',
            'password.min' => 'Password must be at least 6 characters long.',
            'password.confirmed' => 'Password confirmation does not match.',
            'g-recaptcha-response.required' => 'reCAPTCHA verification is required.',
            'g-recaptcha-response.captcha' => 'reCAPTCHA verification failed.',
        ];

        // Validate incoming request
        $validator = Validator::make($request->all(), $rules, $messages);

        // Check if validation failed
        if ($validator->fails()) {
            return response()->json(['error' => true, 'message' => $validator->errors()], 422);
        }

        // Create a new user
        $user = new User();
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->phone = $request->phone;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->status = '1';
        $user->email_verified_at = now()->tz(config('app.timezone'));
        $user->save();

        // Assign role to user
        $user->assignRole('admin');
        $admin = new Admin();
        $admin->user_id = $user->id;
        $admin->save();

        // Notify user if email configuration is set
        if (isEmailConfigured()) {
            $account_creation_template = Template::where('type', 'email')
                ->where('name', 'account_creation')
                ->first();

            if (!$account_creation_template || $account_creation_template->status !== 0) {
                $user->notify(new AccountCreation($user, $request->password));
            }
        }

        // Generate API Token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Return JSON response with token + existing message & redirect_url
        return response()->json([
            'error' => false,
            'message' => 'User registered successfully',
            'redirect_url' => route('login'),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }


    public function show($id)
    {
        $user = User::findOrFail($id);
        $workspace = Workspace::find(session()->get('workspace_id'));
        $projects = isAdminOrHasAllDataAccess() ? $workspace->projects : $user->projects;
        $tasks = isAdminOrHasAllDataAccess() ? $workspace->tasks->count() : $user->tasks->count();
        $users = $workspace->users;
        $clients = $workspace->clients;
        return view('users.user_profile', ['user' => $user, 'projects' => $projects, 'tasks' => $tasks, 'users' => $users, 'clients' => $clients, 'auth_user' => getAuthenticatedUser()]);
    }
    public function list()
    {
        $workspace = Workspace::find(session()->get('workspace_id'));
        $search = request('search');
        $sort = request('sort') ?: 'id';
        $order = request('order') ?: 'DESC';
        $type = request('type');
        $typeId = request('typeId');
        $status = isset($_REQUEST['status']) && $_REQUEST['status'] !== '' ? $_REQUEST['status'] : "";
        $role_ids = request('role_ids', []);

        if ($type && $typeId) {
            if ($type == 'project') {
                $project = Project::find($typeId);
                $users = $project->users();
            } elseif ($type == 'task') {
                $task = Task::find($typeId);
                $users = $task->users();
            } else {
                $users = $workspace->users();
            }
        } else {
            $users = $workspace->users();
        }

        // Ensure the search query does not introduce duplicates
        $users = $users->when($search, function ($query) use ($search) {
            return $query->where(function ($query) use ($search) {
                $query->where('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        });

        if ($status != '') {
            $users = $users->where('status', $status);
        }

        if (!empty($role_ids)) {
            $users = $users->whereHas('roles', function ($query) use ($role_ids) {
                $query->whereIn('roles.id', $role_ids);
            });
        }

        $totalusers = $users->count();

        $canEdit = checkPermission('edit_users');
        $canDelete = checkPermission('delete_users');
        $canManageProjects = checkPermission('manage_projects');
        $canManageTasks = checkPermission('manage_tasks');

        // Use distinct to avoid duplicates if any join condition or query causes duplicates
        $users = $users->select('users.*')
            ->distinct()
            ->leftJoin('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->orderByRaw("CASE WHEN roles.name = 'admin' THEN 0 ELSE 1 END")
            ->orderByRaw("CASE WHEN roles.name = 'admin' THEN users.id END ASC")
            ->orderBy($sort, $order)
            ->paginate(request("limit"))
            ->through(
                function ($user) use ($workspace, $canEdit, $canDelete, $canManageProjects, $canManageTasks) {
                    $actions = '';
                    if ($canEdit) {
                        $actions .= '<a href="' . route('users.edit', ['id' => $user->id]) . '" title="' . get_label('update', 'Update') . '">' .
                            '<i class="bx bx-edit mx-1"></i>' .
                            '</a>';
                    }

                    if ($canDelete) {
                        $actions .= '<button title="' . get_label('delete', 'Delete') . '" type="button" class="btn delete" data-id="' . $user->id . '" data-type="users">' .
                            '<i class="bx bx-trash text-danger mx-1"></i>' .
                            '</button>';
                    }
                    if (isAdminOrHasAllDataAccess()) {
                        $actions .=
                            '<a href="' . route('users.permissions', ['user' => $user->id]) . '" title="' . get_label('permissions', 'Permissions') . '">' .
                            '<i class="bx bxs-key mx-1"></i>' .
                            '</a>';
                    }
                    $actions = $actions ?: '-';

                    $projectsBadge = '<span class="badge rounded-pill bg-primary">' . (isAdminOrHasAllDataAccess('user', $user->id) ? count($workspace->projects) : count($user->projects)) . '</span>';
                    if ($canManageProjects) {
                        $projectsBadge = '<a href="javascript:void(0);" class="viewAssigned" data-type="projects" data-id="' . 'user_' . $user->id . '" data-user="' . $user->first_name . ' ' . $user->last_name . '">' .
                            $projectsBadge . '</a>';
                    }

                    $tasksBadge = '<span class="badge rounded-pill bg-primary">' . (isAdminOrHasAllDataAccess('user', $user->id) ? count($workspace->tasks) : count($user->tasks)) . '</span>';
                    if ($canManageTasks) {
                        $tasksBadge = '<a href="javascript:void(0);" class="viewAssigned" data-type="tasks" data-id="' . 'user_' . $user->id . '" data-user="' . $user->first_name . ' ' . $user->last_name . '">' .
                            $tasksBadge . '</a>';
                    }
                    $photoHtml = "<div class='avatar avatar-md pull-up' title='" . $user->first_name . " " . $user->last_name . "'>
                    <a href=' " . route('users.show', ['id' => $user->id]) . "'>
                        <img src='" . ($user->photo ? asset('storage/' . $user->photo) : asset('storage/photos/no-image.jpg')) . "' alt='Avatar' class='rounded-circle'>
                    </a>
                  </div>";

                    $statusBadge = $user->status === 1
                        ? '<span class="badge bg-success">' . get_label('active', 'Active') . '</span>'
                        : '<span class="badge bg-danger">' . get_label('deactive', 'Deactive') . '</span>';

                    $formattedHtml = '<div class="d-flex mt-2">' .
                        $photoHtml .
                        '<div class="mx-2">' .
                        '<h6 class="mb-1">' .
                        $user->first_name . ' ' . $user->last_name .
                        ' ' . $statusBadge .
                        '</h6>' .
                        '<p class="text-muted">' . $user->email . '</p>' .
                        '</div>' .
                        '</div>';

                    $phone = !empty($user->country_code) ? $user->country_code . ' ' . $user->phone : $user->phone;

                    return [
                        'id' => $user->id,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'role' => "<span class='badge bg-label-" . (isset(config('taskify.role_labels')[$user->getRoleNames()->first()]) ? config('taskify.role_labels')[$user->getRoleNames()->first()] : config('taskify.role_labels')['default']) . " me-1'>" . $user->getRoleNames()->first() . "</span>",
                        'email' => $user->email,
                        'phone' => $phone,
                        'profile' => $formattedHtml,
                        'status' => $user->status,
                        'created_at' => format_date($user->created_at, true),
                        'updated_at' => format_date($user->updated_at, true),
                        'assigned' => '<div class="d-flex justify-content-start align-items-center">' .
                            '<div class="text-center mx-4">' .
                            $projectsBadge .
                            '<div>' . get_label('projects', 'Projects') . '</div>' .
                            '</div>' .
                            '<div class="text-center">' .
                            $tasksBadge .
                            '<div>' . get_label('tasks', 'Tasks') . '</div>' .
                            '</div>' .
                            '</div>',
                        'actions' => $actions
                    ];
                }
            );

        return response()->json([
            "rows" => $users->items(),
            "total" => $totalusers,
        ]);
    }

    /**
     * Get users list or specific user
     * @group User Managemant
     * This API endpoint retrieves a list of users within the current workspace or a specific user by ID.
     * Supports filtering by search term, status, roles, type (project/task), sorting, and pagination.

     * @header workspace_id 2
     * @urlParam id integer optional The ID of the user to retrieve. Leave blank to get all users. Example: 5
     *
     * @queryParam search string optional Filter users by name, email, or phone. Example: John
     * @queryParam sort string optional Field to sort by. Default is `id`. Example: first_name
     * @queryParam order string optional Sort order: `ASC` or `DESC`. Default is `DESC`. Example: ASC
     * @queryParam status integer optional Filter users by status (1 for active, 0 for inactive). Example: 1
     * @queryParam role_ids array optional Filter users by one or more role IDs. Example: [1,2]
     * @queryParam type string optional Source of user relation (`project` or `task`). Example: project
     * @queryParam typeId integer optional ID of the related project or task. Required if `type` is provided. Example: 3
     * @queryParam limit integer optional Number of results per page. Example: 10
     * @queryParam isApi boolean optional Indicates API context (used internally). Default: true. Example: true
     *
     * @response 200 {
     *   "error": false,
     *   "message": "Users retrieved successfully",
     *   "total": 2,
     *   "data": [
     *     {
     *       "id": 1,
     *       "first_name": "John",
     *       "last_name": "Doe",
     *       "email": "john@example.com",
     *       "phone": "+91 9876543210",
     *       "status": 1,
     *       "created_at": "2024-01-01 10:00:00",
     *       "updated_at": "2024-06-01 09:00:00",
     *       "role": "Admin",
     *       "profile": "...",
     *       "assigned": "...",
     *       "actions": "..."
     *     }
     *   ]
     * }
     *
     * @response 200 {
     *   "error": false,
     *   "message": "User retrieved successfully",
     *   "data": {
     *     "id": 5,
     *     "first_name": "Jane",
     *     "last_name": "Smith",
     *     "email": "jane@example.com",
     *     "phone": "+91 1234567890",
     *     "status": 1,
     *     ...
     *   }
     * }
     *
     * @response 404 {
     *   "error": true,
     *   "message": "User not found",
     *   "data": []
     * }
     *
     * @response 500 {
     *   "error": true,
     *   "message": "Failed to retrieve users",
     *   "error": "Exception message"
     * }
     *
     * @param \Illuminate\Http\Request $request
     * @param int|null $id
     * @return \Illuminate\Http\JsonResponse
     */

    public function apiList(Request $request, $id = null)
    {
        $isApi = $request->get('isApi', true); // default to API mode

        try {
            $workspace = Workspace::find(getWorkspaceId());
            // dd(getWorkspaceId());
            if (!$workspace) {
                return formatApiResponse(true, 'Workspace not found', [], 404);
            }

            if ($id) {
                $user = $workspace->users()->where('users.id', $id)->first();
                // dd($workspace->users()->pluck('users.id'));

                // dd($user);

                if (!$user) {
                    return formatApiResponse(true, 'User not found', [], 404);
                }

                return formatApiResponse(false, 'User retrieved successfully', [
                    'data' => formatUser($user),
                ]);
            }

            $search = $request->get('search');
            $sort = $request->get('sort', 'id');
            $order = $request->get('order', 'DESC');
            $status = $request->get('status', '');
            $role_ids = $request->get('role_ids', []);
            $type = $request->get('type');
            $typeId = $request->get('typeId');

            if ($type && $typeId) {
                if ($type === 'project') {
                    $project = Project::find($typeId);
                    $users = $project ? $project->users() : $workspace->users();
                } elseif ($type === 'task') {
                    $task = Task::find($typeId);
                    $users = $task ? $task->users() : $workspace->users();
                } else {
                    $users = $workspace->users();
                }
            } else {
                $users = $workspace->users();
            }

            $users = $users->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', '%' . $search . '%')
                        ->orWhere('last_name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%');
                });
            });

            if ($status !== '') {
                $users->where('status', $status);
            }

            if (!empty($role_ids)) {
                $users->whereHas('roles', function ($q) use ($role_ids) {
                    $q->whereIn('roles.id', $role_ids);
                });
            }

            $total = $users->count();

            $users = $users->orderBy($sort, $order)
                ->paginate($request->get('limit'))
                ->through(fn($user) => formatUser($user));

            return formatApiResponse(false, 'Users retrieved successfully', [
                'total' => $total,
                'data' => $users->items()
            ]);
        } catch (ValidationException $e) {
            return formatApiValidationError($isApi, $e->errors());
        } catch (\Exception $e) {
            return formatApiResponse(true, 'Failed to retrieve users', [
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function permissions(Request $request, User $user)
    {
        $userId = $user->id;
        $role  = $user->roles[0]['name'];
        // dd($role);
        $role = Role::where('name', $role)->first();
        // Fetch permissions associated with the role
        // $rolePermissions = $role->permissions;
        $mergedPermissions = collect();
        // Loop through each role to merge its permissions
        $mergedPermissions = $mergedPermissions->merge($role->permissions);
        // If you also want to include permissions directly assigned to the user
        $mergedPermissions = $mergedPermissions->merge($user->permissions);
        return view('users.permissions', ['mergedPermissions' => $mergedPermissions, 'role' => $role, 'user' => $user]);
    }
    public function update_permissions(Request $request, User $user)
    {
        // dd($request, $request->permissions);
        $user->syncPermissions($request->permissions);
        return redirect()->back()->with(['message' => 'Permissions updated successfully']);
    }
    public function searchUsers(Request $request)
    {
        $query = $request->input('q');
        $page = $request->input('page', 1);
        $perPage = 10;
        $users = Workspace::find(session()->get('workspace_id'))->users();
        // If there is no query, return the first set of statuses

        $users = $users->when($query, function ($queryBuilder) use ($query) {
            $queryBuilder->where('first_name', 'like', '%' . $query . '%')
                ->orWhere('last_name', 'like', '%' . $query . '%');
        })
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get(['users.id', 'first_name', 'last_name']);

        // Prepare response for Select2
        $results = $users->map(function ($user) {
            return ['id' => $user->id, 'text' => ucwords($user->first_name . ' ' . $user->last_name)];
        });

        // Flag for more results
        $pagination = ['more' => $users->count() === $perPage];

        return response()->json([
            'items' => $results,
            'pagination' => $pagination
        ]);
    }
}
