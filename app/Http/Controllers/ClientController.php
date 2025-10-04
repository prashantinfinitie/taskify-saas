<?php

namespace App\Http\Controllers;

use Throwable;
use App\Models\Task;
use App\Models\User;
use App\Models\Client;
use App\Models\Project;
use App\Models\Template;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Services\DeletionService;
use App\Notifications\VerifyEmail;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Notifications\AccountCreation;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Illuminate\Validation\ValidationException;

class ClientController extends Controller
{



    protected $workspace;
protected $user;

public function __construct()
{
    $this->middleware(function ($request, $next) {
        // Use helper function to get workspace ID
        $workspaceId = getWorkspaceId();
        $this->workspace = Workspace::find($workspaceId);
        // dd($this->workspace);

        $this->user = getAuthenticatedUser();

        return $next($request);
    });
}
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $workspace = Workspace::find(session()->get('workspace_id'));
        $clients = $workspace->clients ?? [];
        return view('clients.clients', ['clients' => $clients]);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('clients.create_client');
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

   /**
 * Create a new client.
 *
 * This endpoint is used to create a new client, either for internal purposes or for general usage.
 * The client can be created with optional email verification and notification settings.
 * The request must include a `workspace-id` in the headers to identify which workspace the client belongs to.
 *
 * @group Client Management
 *
 *
 * @header workspace_id 2
 * @bodyParam first_name string required The first name of the client. Example: John
 * @bodyParam last_name string required The last name of the client. Example: Doe
 * @bodyParam company string The company name of the client. Example: Acme Corp
 * @bodyParam email string required The email address of the client. Must be unique. Example: john@example.com
 * @bodyParam phone string The phone number of the client. Example: 1234567890
 * @bodyParam country_code string The phone country code. Example: +1
 * @bodyParam password string required_if:internal_purpose,off The password for the client (min 6 characters). Required unless internal_purpose is on. Example: password123
 * @bodyParam password_confirmation string Same as password. Required if password is present. Example: password123
 * @bodyParam address string The address of the client. Example: 123 Main St
 * @bodyParam city string The city of the client. Example: New York
 * @bodyParam state string The state of the client. Example: NY
 * @bodyParam country string The country of the client. Example: USA
 * @bodyParam zip string The ZIP/postal code. Example: 10001
 * @bodyParam dob string The date of birth in the configured date format. Example: 1990-01-01
 * @bodyParam doj string The date of joining in the configured date format. Example: 2023-01-01
 * @bodyParam country_iso_code string ISO country code. Example: US
 * @bodyParam internal_purpose boolean Whether the client is for internal purpose only. Example: on
 * @bodyParam require_ev boolean Should email verification be required. Only applicable if user has permission. Example: 0
 * @bodyParam status boolean Should the client be activated immediately. Only applicable if user has permission. Example: 1
 *
 * @bodyParam example {
 *   "first_name": "John",
 *   "last_name": "Doe",
 *   "company": "Acme Corp",
 *   "email": "john@example.com",
 *   "phone": "1234567890",
 *   "country_code": "+1",
 *   "password": "password123",
 *   "password_confirmation": "password123",
 *   "address": "123 Main St",
 *   "city": "New York",
 *   "state": "NY",
 *   "country": "USA",
 *   "zip": "10001",
 *   "dob": "1990-01-01",
 *   "doj": "2023-01-01",
 *   "country_iso_code": "US",
 *   "internal_purpose": "on",
 *   "require_ev": 0,
 *   "status": 1
 * }
 *
 * @response 200 {
 *   "error": false,
 *   "message": "Client created successfully.",
 *   "data": {
 *     "id": 23,
 *     "first_name": "John",
 *     "last_name": "Doe",
 *     ...
 *   }
 * }
 *
 * @response 422 {
 *   "error": true,
 *   "message": "The email has already been taken in users or clients.",
 *   "errors": {
 *     "email": ["The email has already been taken in users or clients."]
 *   }
 * }
 *
 * @response 400 {
 *   "error": true,
 *   "message": "Invalid or missing workspace.",
 *   "workspace_id": null
 * }
 *
 * @response 500 {
 *   "error": true,
 *   "message": "Client could not be created.",
 *   "error": "Exception message here",
 *   "line": 120,
 *   "file": "ClientController.php"
 * }
 */


    public function store(Request $request)
    {
        $isApi = true;

        try {
            $adminId = getAdminIdByUserRole();
            ini_set('max_execution_time', 300);

            $internal_purpose = $request->has('internal_purpose') && $request->input('internal_purpose') == 'on' ? 1 : 0;

            $formFields = $request->validate([
                'first_name' => 'required',
                'last_name' => 'required',
                'company' => 'nullable',
                'email' => ['required', 'email', 'unique:clients,email'],
                'phone' => 'nullable',
                'country_code' => 'nullable',
                'password' => $internal_purpose ? 'nullable|confirmed|min:6' : 'required|min:6',
                'password_confirmation' => $internal_purpose ? 'nullable' : 'required_with:password|same:password',
                'address' => 'nullable',
                'city' => 'nullable',
                'state' => 'nullable',
                'country' => 'nullable',
                'zip' => 'nullable',
                'dob' => 'nullable',
                'doj' => 'nullable',
                'country_iso_code' => 'nullable',
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
                return formatApiValidationError($isApi, $validator->errors());
            }

            if (!$internal_purpose && $request->input('password')) {
                $password = $request->input('password');
                $formFields['password'] = bcrypt($password);
            }

            $formFields['internal_purpose'] = $internal_purpose;
            $formFields['photo'] = $request->hasFile('profile')
                ? $request->file('profile')->store('photos', 'public')
                : 'photos/no-image.jpg';

            if ($request->input('dob')) {
                $formFields['dob'] = format_date($request->input('dob'), false, app('php_date_format'), 'Y-m-d');
            }

            if ($request->input('doj')) {
                $formFields['doj'] = format_date($request->input('doj'), false, app('php_date_format'), 'Y-m-d');
            }

            $role_id = Role::where('name', 'client')->first()->id;

            // Use helper to get workspace ID
            $workspaceId = getWorkspaceId();
            $workspace = Workspace::find($workspaceId);

            if (!$workspace) {
                return formatApiResponse(true, 'Invalid or missing workspace.', [
                    'workspace_id' => $workspaceId
                ]);
            }

            if (!$workspace) {
                return formatApiResponse(true, 'Workspace not found or not set in session.', [
                    'workspace_id' => $workspaceId
                ]);
            }

            $require_ev = isAdminOrHasAllDataAccess() && $request->has('require_ev') && $request->input('require_ev') == 0 ? 0 : 1;
            $status = !$internal_purpose && isAdminOrHasAllDataAccess() && $request->has('status') && $request->input('status') == 1 ? 1 : 0;

            if (!$internal_purpose && $require_ev == 0) {
                $formFields['email_verified_at'] = now()->tz(config('app.timezone'));
            }

            $formFields['status'] = $status;
            $formFields['admin_id'] = $adminId;

            $client = Client::create($formFields);

            if (!$internal_purpose && $require_ev == 1) {
                $client->notify(new VerifyEmail($client));
                $client->update(['email_verification_mail_sent' => 1]);
            } else {
                $client->update(['email_verification_mail_sent' => 0]);
            }

            $workspace->clients()->attach($client->id);
            $client->assignRole($role_id);

            if (!$internal_purpose && isEmailConfigured()) {
                $account_creation_template = Template::where('type', 'email')
                    ->where('name', 'account_creation')
                    ->first();

                if (!$account_creation_template || $account_creation_template->status !== 0) {
                    $client->notify(new AccountCreation($client, $password ?? null));
                    $client->update(['acct_create_mail_sent' => 1]);
                } else {
                    $client->update(['acct_create_mail_sent' => 0]);
                }
            } else {
                $client->update(['acct_create_mail_sent' => 0]);
            }

            return formatApiResponse(false, 'Client created successfully.', [
                'data' => formatClient($client)
            ]);
        } catch (TransportExceptionInterface $e) {
            if (isset($client)) {
                $client->delete();
            }

            return formatApiResponse(true, 'Email delivery failed.', [
                'error' => $e->getMessage()
            ]);
        } catch (Throwable $e) {
            if (isset($client)) {
                $client->delete();
            }

            return formatApiResponse(true, 'Client could not be created.', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $workspace = Workspace::find(session()->get('workspace_id'));
        $client = Client::findOrFail($id);
        $projects = $client->projects;
        $tasks = $client->tasks()->count();
        $users = $workspace->users;
        $clients = $workspace->clients;
        return view('clients.client_profile', ['client' => $client, 'projects' => $projects, 'tasks' => $tasks, 'users' => $users, 'clients' => $clients, 'auth_user' => getAuthenticatedUser()]);
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $client = Client::findOrFail($id);
        return view('clients.update_client')->with('client', $client);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
/**
 * Update a client
 *
 * This endpoint updates the details of an existing client, including profile information,
 * status, internal usage flag, password, and triggers account creation or email verification emails if needed.
 *
 * @group Client Management
 *
 * @urlParam id integer required The ID of the client to update. Example: 5
 *
 * @header workspace_id: 2
 *
 * @bodyParam first_name string required The client's first name. Example: John
 * @bodyParam last_name string required The client's last name. Example: Doe
 * @bodyParam company string The client's company name. Example: Acme Corp
 * @bodyParam email string required Must be a valid and unique email address. Example: john.doe@example.com
 * @bodyParam phone string The client's phone number. Example: +1234567890
 * @bodyParam country_code string The country code for the phone number. Example: +91
 * @bodyParam address string The client's address. Example: 123 Main St
 * @bodyParam city string The client's city. Example: Mumbai
 * @bodyParam state string The client's state. Example: Maharashtra
 * @bodyParam country string The client's country. Example: India
 * @bodyParam zip string The client's postal code. Example: 400001
 * @bodyParam dob date The client's date of birth (YYYY-MM-DD). Example: 1990-05-01
 * @bodyParam doj date The client's date of joining (YYYY-MM-DD). Example: 2023-04-15
 * @bodyParam country_iso_code string The ISO code of the client's country. Example: IN
 * @bodyParam status integer The client's status (1 = active, 0 = inactive). Example: 1
 * @bodyParam require_ev integer Set to 1 to send email verification, 0 to skip. Example: 1
 *
 * @response 200 {
 *   "error": false,
 *   "message": "Client details updated successfully.",
 *   "data": {
 *     "id": 5,
 *     "first_name": "John",
 *     "last_name": "Doe",
 *     ...
 *   }
 * }
 *
 * @response 422 {
 *   "message": "The given data was invalid.",
 *   "errors": {
 *     "email": ["The email has already been taken."]
 *   }
 * }
 */

    public function update(Request $request, $id)
{
    $isApi = true;

    try {
        ini_set('max_execution_time', 300);

        $client = Client::findOrFail($id);
        $internal_purpose = $request->has('internal_purpose') && $request->input('internal_purpose') == 'on' ? 1 : 0;

        if ($internal_purpose && $request->has('password') && !empty($request->input('password'))) {
            $request->merge(['password' => null]);
        }

        $rules = [
            'first_name' => 'required',
            'last_name' => 'required',
            'company' => 'nullable',
            'email' => ['required', Rule::unique('clients')->ignore($id)],
            'phone' => 'nullable',
            'country_code' => 'nullable',
            'address' => 'nullable',
            'city' => 'nullable',
            'state' => 'nullable',
            'country' => 'nullable',
            'zip' => 'nullable',
            'dob' => 'nullable',
            'doj' => 'nullable',
            'country_iso_code' => 'nullable',
            'password' => $internal_purpose || $client->password ? 'nullable' : 'required|min:6',
            'password_confirmation' => 'required_with:password|same:password',
        ];

        $formFields = $request->validate($rules);

        if ($request->hasFile('upload')) {
            if ($client->photo !== 'photos/no-image.jpg' && $client->photo !== null) {
                Storage::disk('public')->delete($client->photo);
            }
            $formFields['photo'] = $request->file('upload')->store('photos', 'public');
        }

        $status = $internal_purpose
            ? $client->status
            : (isAdminOrHasAllDataAccess() && $request->has('status')
                ? $request->input('status')
                : $client->status);

        $formFields['status'] = $status;

        if (!$internal_purpose && isAdminOrHasAllDataAccess() && !empty($formFields['password'])) {
            $password = $formFields['password'];
            $formFields['password'] = bcrypt($formFields['password']);
        } else {
            unset($formFields['password']);
        }

        $formFields['internal_purpose'] = $internal_purpose;
        $client->update($formFields);

        // Email Verification & Account Creation Notifications
        $require_ev = (!$internal_purpose && $client->email_verified_at === null && $client->email_verification_mail_sent === 0)
            ? (isAdminOrHasAllDataAccess() && $request->input('require_ev') == 0 ? 0 : 1)
            : 0;

        $send_account_creation_email = (!$internal_purpose && $client->acct_create_mail_sent === 0) ? 1 : 0;

        if (!$internal_purpose) {
            if ($require_ev == 0) {
                $client->update(['email_verified_at' => now()->tz(config('app.timezone')), 'email_verification_mail_sent' => 1]);
            } elseif ($require_ev == 1) {
                $client->notify(new VerifyEmail($client));
                $client->update(['email_verification_mail_sent' => 1]);
            }

            if ($send_account_creation_email && isEmailConfigured()) {
                $account_creation_template = Template::where('type', 'email')
                    ->where('name', 'account_creation')
                    ->first();

                if (!$account_creation_template || $account_creation_template->status !== 0) {
                    $client->notify(new AccountCreation($client, $password ?? null));
                    $client->update(['acct_create_mail_sent' => 1]);
                }
            }
        }

        return formatApiResponse(false, 'Client details updated successfully.', [
            'data' => formatClient($client)
        ]);

    } catch (ValidationException $e) {
        return formatApiValidationError($isApi, $e->errors());
    } catch (TransportExceptionInterface $e) {
        return formatApiResponse(true, 'Email delivery failed.', [
            'error' => $e->getMessage()
        ]);
    } catch (Throwable $e) {
        dd($e);
        return formatApiResponse(true, 'Client could not be updated.', [
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile()
        ]);
    }
}

 /**
 * Delete a client.
 *@group Client Management
 * This endpoint deletes a specific client by their ID.
 * It removes the client from the database along with all their associated todos,
 * and uses a reusable deletion service for standardized deletion handling.
 *
 * @authenticated
 * @header workspace_id 2
 * @urlParam client int required The ID of the client to delete. Example: 15
 *
 * @response 200 {
 *   "error": false,
 *   "message": "Client deleted successfully.",
 *   "id": 28,
 *   "title": "hrdeep Raa",
 *   "data": []
 * }
 *
 * @response 404 {
 *   "error": true,
 *   "message": "Client not found.",
 *   "error": "No query results for model [App\\Models\\Client] 15"
 * }
 *
 * @response 500 {
 *   "error": true,
 *   "message": "Client could not be deleted.",
 *   "error": "Exception message",
 *   "line": 123,
 *   "file": "path/to/file"
 * }
 */

    public function destroy($id)
{
    $isApi = true;

    try {
        $client = Client::findOrFail($id);

        // Delete related todos
        $client->todos()->delete();

        // Call reusable deletion service
        $response = DeletionService::delete(Client::class, $id, 'Client');

        return $response;
    } catch (Throwable $e) {
        return formatApiResponse($isApi, 'Client could not be deleted.', [
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile()
        ], 500);
    }
}

    /**
     * Delete multiple clients.
     *
     * Deletes multiple clients by their IDs along with their associated todos.
     *
     * @group Client Management
     * @header workspace_id 2
     * @bodyParam ids array required An array of client IDs to delete. Example: [1, 2, 3]
     * @bodyParam ids.* integer required Each client ID must exist in the clients table.
     *
     * @response 200 {
     *   "error": false,
     *   "message": "Clients(s) deleted successfully.",
     *   "id": [1, 2, 3],
     *   "titles": ["John Doe", "Jane Smith", "Alice Johnson"]
     * }
     *
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "ids": ["The ids field is required."],
     *     "ids.0": ["The selected ids.0 is invalid."]
     *   }
     * }
     *
     * @response 404 {
     *   "error": true,
     *   "message": "Client not found."
     * }
     */
    public function destroy_multiple(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'ids' => 'required|array', // Ensure 'ids' is present and an array
            'ids.*' => 'integer|exists:clients,id' // Ensure each ID in 'ids' is an integer and exists in the table
        ]);
        $ids = $validatedData['ids'];
        $deletedClients = [];
        $deletedClientNames = [];
        // Perform deletion using validated IDs
        foreach ($ids as $id) {
            $client = Client::findOrFail($id);
            if ($client) {
                $deletedClients[] = $id;
                $deletedClientNames[] = $client->first_name . ' ' . $client->last_name;
                DeletionService::delete(Client::class, $id, 'Client');
                $client->todos()->delete();
            }
        }
        return response()->json(['error' => false, 'message' => 'Clients(s) deleted successfully.', 'id' => $deletedClients, 'titles' => $deletedClientNames]);
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
        $internal_purpose = isset($_REQUEST['internal_purpose']) && $_REQUEST['internal_purpose'] !== '' ? $_REQUEST['internal_purpose'] : "";
        if ($type && $typeId) {
            if ($type == 'project') {
                $project = Project::find($typeId);
                $clients = $project->clients();
            } elseif ($type == 'task') {
                $task = Task::find($typeId);
                $clients = $task->project->clients();
            } else {
                $clients = $workspace->clients();
            }
        } else {
            $clients = $workspace->clients();
        }
        $clients = $clients->when($search, function ($query) use ($search) {
            return $query->where(function ($query) use ($search) {
                $query->where('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhere('company', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%');
            });
        });
        if ($status != '') {
            $clients = $clients->where('status', $status);
        }
        if ($internal_purpose != '') {
            $clients = $clients->where('internal_purpose', $internal_purpose);
        }
        $totalclients = $clients->count();
        $canEdit = checkPermission('edit_clients');
        $canDelete = checkPermission('delete_clients');
        $clients = $clients->select('clients.*')
            ->distinct()
            ->orderBy($sort, $order)
            ->paginate(request('limit'))
            ->through(function ($client) use ($workspace, $canEdit, $canDelete) {
                $actions = '';
                if ($canEdit) {
                $actions .= '<a href="' . route('clients.edit', ['id' => $client->id]) . '" title="' . get_label('update', 'Update') . '">' .
                        '<i class="bx bx-edit mx-1"></i>' .
                        '</a>';
            }
                if ($canDelete) {
                    $actions .= '<button title="' . get_label('delete', 'Delete') . '" type="button" class="btn delete" data-id="' . $client->id . '" data-type="clients">' .
                        '<i class="bx bx-trash text-danger mx-1"></i>' .
                        '</button>';
            }
            if (isAdminOrHasAllDataAccess()) {
                $actions .= '<a href="' . route('clients.permissions', ['client' => $client->id]) . '" title="' . get_label('permissions', 'Permissions') . '">' .
                '<i class="bx bxs-key mx-1"></i>' .
                '</a>';
            }
            $actions = $actions ?: '-';
            $badge = '';
            $badge = $client->status === 1 ? '<span class="badge bg-success">' . get_label('active', 'Active') . '</span>' : '<span class="badge bg-danger">' . get_label('deactive', 'Deactive') . '</span>';
            $profileHtml = "<div class='avatar avatar-md pull-up' title='{$client->first_name} {$client->last_name}'><a href='" . route('clients.profile', ['id' => $client->id]) . "'><img src='" . ($client->photo ? asset('storage/' . $client->photo) : asset('storage/photos/no-image.jpg')) .
            "' alt='Avatar' class='rounded-circle'></a></div>";
                $formattedHtml = '<div class="d-flex mt-2">' .
                    $profileHtml .
                    '<div class="mx-2">' .
                    '<h6 class="mb-1">' .
                    $client->first_name . ' ' . $client->last_name . ' ' .
                    $badge .
                    '</h6>' .
            '<span class="text-muted">' . $client->email . '</span>';
                if ($client->internal_purpose == 1) {
                    $formattedHtml .= '<span class="badge bg-info ms-2">' . get_label('internal_purpose', 'Internal Purpose') . '</span>';
            }
                $formattedHtml .= '</div>' .
            '</div>';
            $phone = !empty($client->country_code) ? $client->country_code . ' ' . $client->phone : $client->phone;
            return [
                    'id' => $client->id,
                    'first_name' => $client->first_name,
                    'last_name' => $client->last_name,
                    'company' => $client->company,
                    'email' => $client->email,
                'phone' => $phone,
                'profile' => $formattedHtml,
                'status' => $client->status,
                'internal_purpose' => $client->internal_purpose,
                'created_at' => format_date($client->created_at, true),
                'updated_at' => format_date($client->updated_at, true),
                'assigned' => '<div class="d-flex justify-content-start align-items-center">' .
                '<div class="text-center mx-4">' .
                '<a href="javascript:void(0);" class="viewAssigned" data-type="projects" data-id="' . 'client_' . $client->id . '" data-client="' . $client->first_name . ' ' . $client->last_name . '">' .
                '<span class="badge rounded-pill bg-primary">' . (isAdminOrHasAllDataAccess('client', $client->id) ? count($workspace->projects) : count($client->projects)) . '</span>' .
                '</a>' .
                '<div>' . get_label('projects', 'Projects') . '</div>' .
                '</div>' .
                '<div class="text-center">' .
                '<a href="javascript:void(0);" class="viewAssigned" data-type="tasks" data-id="' . 'client_' . $client->id . '" data-client="' . $client->first_name . ' ' . $client->last_name . '">' .
                '<span class="badge rounded-pill bg-primary">' . (isAdminOrHasAllDataAccess('client', $client->id) ? count($workspace->tasks) : $client->tasks()->count()) . '</span>' .
                '</a>' .
                '<div>' . get_label('tasks', 'Tasks') . '</div>' .
                '</div>' .
                '</div>',
                'actions' => $actions
                ];
            });
        return response()->json([
            'rows' => $clients->items(),
            'total' => $totalclients,
        ]);
    }
    public function verify_email(EmailVerificationRequest $request)
    {
        // Fulfill the email verification process
        $request->fulfill();

        // Get the authenticated user
        $user = $request->user();

        // Check if the user's email is verified
        if ($user->hasVerifiedEmail()) {
            // Update the user's status to 'verified' (assuming 1 means verified)
            $user->status = 1;
            $user->save();
        }

        // Redirect with success message
        return redirect(route('home.index'))->with('message', 'Email verified successfully.');
    }

    public function get($id)
    {
        $client = Client::findOrFail($id);
        return response()->json(['client' => $client]);
    }
    public function permissions(Request $request, Client $client)
    {
        $clientId = $client->id;
        $role = $client->roles[0]['name'];
        $role = Role::where('name', $role)->first();
        $mergedPermissions = collect();
        // Loop through each role to merge its permissions
        $mergedPermissions = $mergedPermissions->merge($role->permissions);
        // If you also want to include permissions directly assigned to the user
        $mergedPermissions = $mergedPermissions->merge($client->permissions);
        return view('clients.permissions', ['client' => $client, 'mergedPermissions' => $mergedPermissions, 'role' => $role]);
    }
    public function update_permissions(Request $request, Client $client)
    {
        $client->syncPermissions($request->permissions);
        return redirect()->back()->with(['message' => 'Permissions updated successfully']);
    }
    public function searchClients(Request $request)
    {
        $query = $request->input('q');
        $page = $request->input('page', 1);
        $perPage = 10;
        $clients = Workspace::find(session()->get('workspace_id'))->clients();

        // If there is no query, return the first set of statuses
        $clients = $clients->when($query, function ($queryBuilder) use ($query) {
            $queryBuilder->where('first_name', 'like', '%' . $query . '%')
                ->orWhere('last_name', 'like', '%' . $query . '%');
        })
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get(['clients.id', 'first_name', 'last_name']);
        $clients = $clients->unique('id');


        // Prepare response for Select2
        $results = $clients->map(function ($client) {
            return ['id' => $client->id, 'text' => ucwords($client->first_name . ' ' . $client->last_name)];
        });

        // Flag for more results
        $pagination = ['more' => $clients->count() === $perPage];

        return response()->json([
            'items' => $results,
            'pagination' => $pagination
        ]);
    }
    /**
     * @group Client Management
     *
     * List or Retrieve Clients
     *
     * This endpoint is used to retrieve a list of all clients for the current workspace,
     * or a single client if an ID is provided. It supports searching, sorting, and pagination.
     *
     * Requires a `workspace_id` header to identify the current workspace context.
     * @header workspace_id 2
     * @urlParam id int Optional. The ID of the client to retrieve. If not provided, a paginated list of clients will be returned.
     *
     * @queryParam isApi boolean Optional. Indicates if the request is from an API context. Default: false. Example: true
     * @queryParam search string Optional. Search clients by first name, last name, company, email, or phone. Example: john
     * @queryParam sort string Optional. Field to sort by. Default: id. Example: first_name
     * @queryParam order string Optional. Sort direction: ASC or DESC. Default: DESC. Example: ASC
     * @queryParam limit int Optional. Number of clients per page. Default: 10. Example: 15
     *
     *
     *
     * @response 200 {
     *   "success": false,
     *   "message": "Clients retrieved successfully",
     *   "data": {
     *     "total": 2,
     *     "data": [
     *       {
     *         "id": 1,
     *         "first_name": "John",
     *         "last_name": "Doe",
     *         "email": "john@example.com",
     *         "company": "Acme Inc",
     *         ...
     *       }
     *     ],
     *     "pagination": {
     *       "current_page": 1,
     *       "last_page": 1,
     *       "per_page": 10,
     *       "total": 2
     *     }
     *   }
     * }
     *
     * @response 404 {
     *   "error": "Client not found"
     * }
     *
     * @response 400 {
     *   "error": "Workspace ID header missing"
     * }
     *
     * @response 500 {
     *   "success": true,
     *   "message": "Client couldn't be retrieved.",
     *   "data": {
     *     "error": "Detailed error message",
     *     "line": 123,
     *     "file": "path/to/file.php"
     *   }
     * }
     */

    public function apiList(Request $request, $id = null)
    {
        $isApi = $request->get('isApi', false);

        try {
            $workspaceId = $request->header('workspace_id');

            if (!$workspaceId) {
                return response()->json(['error' => 'Workspace ID header missing'], 400);
            }

            $workspace = \App\Models\Workspace::find($workspaceId);
            if (!$workspace) {
                return response()->json(['error' => 'Workspace not found'], 404);
            }

            // If an ID is provided, return that specific client
            if ($id !== null) {
                $client = \App\Models\Client::where('id', $id)
                    ->where('admin_id', $workspace->admin_id)
                    ->first();

                if (!$client) {
                    return response()->json(['error' => 'Client not found'], 404);
                }

                return formatApiResponse(
                    false,
                    'Client retrieved successfully',
                    [
                        'total' => 1,
                        'data' => [formatClient($client)],
                    ]
                );
            }

            // Otherwise, list all clients for the workspace admin
            $clientsQuery = \App\Models\Client::where('admin_id', $workspace->admin_id);

            // Search filter
            $search = $request->get('search');
            if ($search) {
                $clientsQuery->where(function ($query) use ($search) {
                    $query->where('first_name', 'like', "%$search%")
                        ->orWhere('last_name', 'like', "%$search%")
                        ->orWhere('company', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%")
                        ->orWhere('phone', 'like', "%$search%");
                });
            }

            // Sorting and pagination
            $sort = $request->get('sort', 'id');
            $order = $request->get('order', 'DESC');
            $per_page = $request->get('per_page', 10);

            $clients = $clientsQuery->orderBy($sort, $order)->paginate($per_page);

            // Format the client data
            $formattedClients = $clients->getCollection()->map(function ($client) {
                return formatClient($client);
            });

            return formatApiResponse(
                false,
                'Clients retrieved successfully',
                [
                    'total' => $clients->total(),
                    'data' => $formattedClients,
                    // 'pagination' => [
                    //     'current_page' => $clients->currentPage(),
                    //     'last_page' => $clients->lastPage(),
                    //     'per_page' => $clients->perPage(),
                    //     'total' => $clients->total(),
                    // ],
                ]
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return formatApiValidationError($isApi, $e->errors());
        } catch (\Exception $e) {
            return formatApiResponse(
                true,
                'Client couldn\'t be retrieved.',
                [
                    'error' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile()
                ]
            );
        }
    }
}
