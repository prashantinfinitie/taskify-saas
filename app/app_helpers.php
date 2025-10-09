<?php

use App\Models\Tax;
use App\Models\Task;
use App\Models\User;
use App\Models\Admin;
use App\Models\Candidate;
use App\Models\Client;
use App\Models\Status;
use App\Models\Update;
use App\Models\Project;
use App\Models\Setting;
use App\Models\Template;
use App\Models\TeamMember;
use App\Models\LeaveEditor;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Models\LeaveRequest;
use App\Models\Notification;
use App\Models\Subscription;
use Chatify\ChatifyMessenger;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use App\Models\UserClientPreference;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Request;
use Twilio\Rest\Client as TwilioClient;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\RequestException;
use App\Notifications\AssignmentNotification;
use Illuminate\Support\Facades\Session;
use Netflie\WhatsAppCloudApi\WhatsAppCloudApi;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use app\Models\Workspace;


if (!function_exists('get_timezone_array')) {
    // 1.Get Time Zone
    function get_timezone_array()
    {
        $list = DateTimeZone::listAbbreviations();
        $idents = DateTimeZone::listIdentifiers();
        $data = $offset = $added = array();
        foreach ($list as $abbr => $info) {
            foreach ($info as $zone) {
                if (
                    !empty($zone['timezone_id'])
                    and
                    !in_array($zone['timezone_id'], $added)
                    and
                    in_array($zone['timezone_id'], $idents)
                ) {
                    $z = new DateTimeZone($zone['timezone_id']);
                    $c = new DateTime("", $z);
                    $zone['time'] = $c->format('h:i A');
                    $offset[] = $zone['offset'] = $z->getOffset($c);
                    $data[] = $zone;
                    $added[] = $zone['timezone_id'];
                }
            }
        }
        array_multisort($offset, SORT_ASC, $data);
        $i = 0;
        $temp = array();
        foreach ($data as $key => $row) {
            $temp[0] = $row['time'];
            $temp[1] = formatOffset($row['offset']);
            $temp[2] = $row['timezone_id'];
            $options[$i++] = $temp;
        }
        return $options;
    }
}
if (!function_exists('formatOffset')) {
    function formatOffset($offset)
    {
        $hours = $offset / 3600;
        $remainder = $offset % 3600;
        $sign = $hours > 0 ? '+' : '-';
        $hour = (int) abs($hours);
        $minutes = (int) abs($remainder / 60);
        if ($hour == 0 and $minutes == 0) {
            $sign = ' ';
        }
        return $sign . str_pad($hour, 2, '0', STR_PAD_LEFT) . ':' . str_pad($minutes, 2, '0');
    }
}
if (!function_exists('relativeTime')) {
    function relativeTime($time)
    {
        if (!ctype_digit($time))
            $time = strtotime($time);
        $d[0] = array(1, "second");
        $d[1] = array(60, "minute");
        $d[2] = array(3600, "hour");
        $d[3] = array(86400, "day");
        $d[4] = array(604800, "week");
        $d[5] = array(2592000, "month");
        $d[6] = array(31104000, "year");
        $w = array();
        $return = "";
        $now = time();
        $diff = ($now - $time);
        $secondsLeft = $diff;
        for ($i = 6; $i > -1; $i--) {
            $w[$i] = intval($secondsLeft / $d[$i][0]);
            $secondsLeft -= ($w[$i] * $d[$i][0]);
            if ($w[$i] != 0) {
                $return .= abs($w[$i]) . " " . $d[$i][1] . (($w[$i] > 1) ? 's' : '') . " ";
            }
        }
        $return .= ($diff > 0) ? "ago" : "left";
        return $return;
    }
}
if (!function_exists('get_settings')) {
    function get_settings($variable)
    {
        $fetched_data = Setting::all()->where('variable', $variable)->values();
        if (isset($fetched_data[0]['value']) && !empty($fetched_data[0]['value'])) {
            if (isJson($fetched_data[0]['value'])) {
                $fetched_data = json_decode($fetched_data[0]['value'], true);
            }
            return $fetched_data;
        }
    }
}
if (!function_exists('isJson')) {
    function isJson($string)
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
if (!function_exists('create_label')) {
    function create_label($variable, $title = '', $locale = '')
    {
        if ($title == '') {
            $title = $variable;
        }
        return "
            <div class='mb-3 col-md-6'>
                        <label class='form-label' for='end_date'>$title</label>
                        <div class='input-group input-group-merge'>
                            <input type='text' name='$variable' class='form-control' value='" . get_label($variable, $title, $locale) . "'>
                        </div>
                    </div>
            ";
    }
}
if (!function_exists('get_label')) {
    function get_label($label, $default, $locale = '')
    {
        if (Lang::has('labels.' . $label, $locale)) {
            return trans('labels.' . $label, [], $locale);
        } else {
            return $default;
        }
    }
}
if (!function_exists('empty_state')) {
    function empty_state($url)
    {
        return "
    <div class='card text-center'>
    <div class='card-body'>
        <div class='misc-wrapper'>
            <h2 class='mb-2 mx-2'>Data Not Found </h2>
            <p class='mb-4 mx-2'>Oops! 😖 Data doesn't exists.</p>
            <a href='/$url' class='btn btn-primary'>Create now</a>
            <div class='mt-3'>
                <img src='../assets/img/illustrations/page-misc-error-light.png' alt='page-misc-error-light' width='500' class='img-fluid' data-app-dark-img='illustrations/page-misc-error-dark.png' data-app-light-img='illustrations/page-misc-error-light.png' />
            </div>
        </div>
    </div>
</div>";
    }
}
// if (!function_exists('format_date')) {
//     function format_date($date, $time = false, $from_format = null, $to_format = null, $apply_timezone = true)
//     {
//         if ($date) {
//             $from_format = $from_format ?? 'Y-m-d';
//             $to_format = $to_format ?? get_php_date_time_format();
//             $time_format = get_php_date_time_format(true);
//             if ($time) {
//                 if ($apply_timezone) {
//                     if (!$date instanceof \Carbon\Carbon) {
//                         $dateObj = \Carbon\Carbon::createFromFormat($from_format . ' Y-m-d H:i:s', 'Y-m-d H:i:s', $date)
//                             ->setTimezone(config('app.timezone'));
//                     } else {
//                         $dateObj = $date->setTimezone(config('app.timezone'));
//                     }
//                 } else {
//                     if (!$date instanceof \Carbon\Carbon) {
//                         $dateObj = \Carbon\Carbon::createFromFormat($from_format . ' H:i:s', $date);
//                     } else {
//                         $dateObj = $date;
//                     }
//                 }
//             } else {
//                 if (!$date instanceof \Carbon\Carbon) {
//                     $dateObj = \Carbon\Carbon::createFromFormat($from_format, $date);
//                 } else {
//                     $dateObj = $date;
//                 }
//             }
//             $timeFormat = $time ? ' ' . $time_format : '';
//             $date = $dateObj->format($to_format . $timeFormat);
//             return $date;
//         } else {
//             return '-';
//         }
//     }
// }
if (!function_exists('format_date')) {
    function format_date($date, $time = false, $from_format = null, $to_format = null, $apply_timezone = true)
    {
        if (!$date) {
            return '-';
        }

        $from_format = $from_format ?? 'Y-m-d';
        $to_format = $to_format ?? config('app.php_date_format', 'Y-m-d'); // fallback format
        $time_format = config('app.php_time_format', 'H:i:s');

        try {
            if (!$date instanceof \Carbon\Carbon) {
                if ($time) {
                    // parse date + time string
                    $dateObj = \Carbon\Carbon::createFromFormat(
                        $from_format . ' ' . $time_format,
                        $date
                    );
                } else {
                    // parse date only string
                    $dateObj = \Carbon\Carbon::createFromFormat($from_format, $date);
                }
            } else {
                $dateObj = $date;
            }

            if ($apply_timezone) {
                $dateObj->setTimezone(config('app.timezone'));
            }

            $formatString = $to_format;
            if ($time) {
                $formatString .= ' ' . $time_format;
            }

            return $dateObj->format($formatString);
        } catch (\Exception $e) {
            // If parsing fails, return original date or fallback
            return $date;
        }
    }
}

if (!function_exists('getAuthenticatedUser')) {
    function getAuthenticatedUser($idOnly = false, $withPrefix = false)
    {
        // dd(auth());
        $prefix = '';
        // Check the 'web' guard (users)
        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            $prefix = 'u_';
        }
        // Check the 'clients' guard (clients)
        elseif (Auth::guard('client')->check()) {
            $user = Auth::guard('client')->user();
            $prefix = 'c_';
        } elseif (Auth::guard('sanctum')->check()) {
            $user = Auth::guard('sanctum')->user();
            $prefix = 's_';
        }
        // No user is authenticated
        else {
            return null;
        }
        if ($idOnly) {
            if ($withPrefix) {
                return $prefix . $user->id;
            } else {
                return $user->id;
            }
        }
        return $user;
    }
}
if (!function_exists('isUser')) {
    function isUser()
    {
        return Auth::guard('web')->check(); // Assuming 'role' is a field in the user model.
    }
}
if (!function_exists('isClient')) {
    function isClient()
    {
        return Auth::guard('client')->check(); // Assuming 'role' is a field in the user model.
    }
}
if (!function_exists('generateUniqueSlug')) {
    function generateUniqueSlug($title, $model, $id = null)
    {
        $slug = Str::slug($title);
        $count = 2;
        // If an ID is provided, add a where clause to exclude it
        if ($id !== null) {
            while ($model::where('slug', $slug)->where('id', '!=', $id)->exists()) {
                $slug = Str::slug($title) . '-' . $count;
                $count++;
            }
        } else {
            while ($model::where('slug', $slug)->exists()) {
                $slug = Str::slug($title) . '-' . $count;
                $count++;
            }
        }
        return $slug;
    }
}
if (!function_exists('duplicateRecord')) {
    function duplicateRecord($model, $id, $relatedTables = [], $title = '')
    {
        $eagerLoadRelations = $relatedTables;
        $eagerLoadRelations = array_filter($eagerLoadRelations, function ($table) {
            return $table !== 'project_tasks'; // Exclude from eager loading
        });

        // Eager load the related tables excluding 'project_tasks'
        $originalRecord = $model::with($eagerLoadRelations)->find($id);
        if (!$originalRecord) {
            return false; // Record not found
        }
        // Start a new database transaction to ensure data consistency
        DB::beginTransaction();

        try {
            // Duplicate the original record
            $duplicateRecord = $originalRecord->replicate();
            // Set the title if provided
            if (!empty($title)) {
                $duplicateRecord->title = $title;
            }
            $duplicateRecord->save();

            foreach ($relatedTables as $relatedTable) {
                if ($relatedTable === 'projects') {
                    foreach ($originalRecord->$relatedTable as $project) {
                        // Duplicate the project
                        $duplicateProject = $project->replicate();
                        $duplicateProject->workspace_id = $duplicateRecord->id; // Set the new workspace ID
                        $duplicateProject->save();
                        // Attach project users
                        foreach ($project->users as $user) {
                            $duplicateProject->users()->attach($user->id);
                        }

                        // Attach project clients
                        foreach ($project->clients as $client) {
                            $duplicateProject->clients()->attach($client->id);
                        }
                        // Duplicate the project's tasks
                        if (in_array('project_tasks', $relatedTables)) {
                            foreach ($project->tasks as $task) {
                                $duplicateTask = $task->replicate();
                                $duplicateTask->workspace_id = $duplicateRecord->id;
                                $duplicateTask->project_id = $duplicateProject->id; // Set the new project ID
                                $duplicateTask->save();


                                // Duplicate task's users (if applicable)
                                foreach ($task->users as $user) {
                                    $duplicateTask->users()->attach($user->id);
                                }
                            }
                        }
                    }
                }
                if ($relatedTable === 'tasks') {
                    // Handle 'tasks' relationship separately
                    foreach ($originalRecord->$relatedTable as $task) {
                        // Duplicate the related task
                        $duplicateTask = $task->replicate();
                        $duplicateTask->project_id = $duplicateRecord->id;
                        $duplicateTask->save();
                        foreach ($task->users as $user) {
                            // Attach the duplicated user to the duplicated task
                            $duplicateTask->users()->attach($user->id);
                        }
                    }
                }
                if ($relatedTable === 'meetings') {
                    foreach ($originalRecord->$relatedTable as $meeting) {
                        $duplicateMeeting = $meeting->replicate();
                        $duplicateMeeting->workspace_id = $duplicateRecord->id; // Set the new workspace ID
                        $duplicateMeeting->save();

                        // Duplicate meeting's users
                        foreach ($meeting->users as $user) {
                            $duplicateMeeting->users()->attach($user->id);
                        }

                        // Duplicate meeting's clients
                        foreach ($meeting->clients as $client) {
                            $duplicateMeeting->clients()->attach($client->id);
                        }
                    }
                }
                if ($relatedTable === 'todos') {
                    // Duplicate todos
                    foreach ($originalRecord->$relatedTable as $todo) {
                        $duplicateTodo = $todo->replicate();
                        $duplicateTodo->workspace_id = $duplicateRecord->id; // Set the new workspace ID

                        $duplicateTodo->creator_type = $todo->creator_type; // Keep original creator type
                        $duplicateTodo->creator_id = $todo->creator_id;     // Keep original creator ID

                        $duplicateTodo->save();
                    }
                }
                if ($relatedTable === 'notes') {
                    foreach ($originalRecord->$relatedTable as $note) {
                        $duplicateNote = $note->replicate();
                        $duplicateNote->workspace_id = $duplicateRecord->id; // Set the new workspace ID
                        $duplicateNote->creator_id = $note->creator_id;      // Retain the creator_id
                        $duplicateNote->save();
                    }
                }
            }
            // Handle many-to-many relationships separately
            if (in_array('users', $relatedTables)) {
                $originalRecord->users()->each(function ($user) use ($duplicateRecord) {
                    $duplicateRecord->users()->attach($user->id);
                });
            }

            if (in_array('clients', $relatedTables)) {
                $originalRecord->clients()->each(function ($client) use ($duplicateRecord) {
                    $duplicateRecord->clients()->attach($client->id);
                });
            }

            if (in_array('tags', $relatedTables)) {
                $originalRecord->tags()->each(function ($tag) use ($duplicateRecord) {
                    $duplicateRecord->tags()->attach($tag->id);
                });
            }

            // Commit the transaction
            DB::commit();

            return $duplicateRecord;
        } catch (\Exception $e) {
            // Handle any exceptions and rollback the transaction on failure
            DB::rollback();
            return false;
        }
    }
}
if (!function_exists('is_admin_or_leave_editor')) {
    function is_admin_or_leave_editor($user = null)
    {
        if (!$user) {
            $user = getAuthenticatedUser();
        }
        // Check if the user is an admin or a leave editor based on their presence in the leave_editors table
        if ($user->hasRole('admin') || LeaveEditor::where('user_id', $user->id)->exists()) {
            // dd($user->hasRole('admin'), LeaveEditor::where('user_id', $user->id)->exists());
            return true;
        }
        return false;
    }
}
if (!function_exists('get_php_date_format')) {
    function get_php_date_format()
    {
        $general_settings = get_settings('general_settings');
        $date_format = $general_settings['date_format'] ?? 'DD-MM-YYYY|d-m-Y';
        $date_format = explode('|', $date_format);
        return $date_format[1];
    }
}
if (!function_exists('get_system_update_info')) {
    function get_system_update_info()
    {
        $updatePath = Config::get('constants.UPDATE_PATH');
        $updaterPath = $updatePath . 'updater.json';
        $subDirectory = (File::exists($updaterPath) && File::exists($updatePath . 'update/updater.json')) ? 'update/' : '';
        if (File::exists($updaterPath) || File::exists($updatePath . $subDirectory . 'updater.json')) {
            $updaterFilePath = File::exists($updaterPath) ? $updaterPath : $updatePath . $subDirectory . 'updater.json';
            $updaterContents = File::get($updaterFilePath);
            // Check if the file contains valid JSON data
            if (!json_decode($updaterContents)) {
                throw new \RuntimeException('Invalid JSON content in updater.json');
            }
            $linesArray = json_decode($updaterContents, true);
            if (!isset($linesArray['version'], $linesArray['previous'], $linesArray['manual_queries'], $linesArray['query_path'])) {
                throw new \RuntimeException('Invalid JSON structure in updater.json');
            }
        } else {
            throw new \RuntimeException('updater.json does not exist');
        }
        $dbCurrentVersion = Update::latest()->first();
        $data['db_current_version'] = $dbCurrentVersion ? $dbCurrentVersion->version : '1.0.0';
        if ($data['db_current_version'] == $linesArray['version']) {
            $data['updated_error'] = true;
            $data['message'] = 'Oops!. This version is already updated into your system. Try another one.';
            return $data;
        }
        if ($data['db_current_version'] == $linesArray['previous']) {
            $data['file_current_version'] = $linesArray['version'];
        } else {
            $data['sequence_error'] = true;
            $data['message'] = 'Oops!. Update must performed in sequence.';
            return $data;
        }
        $data['query'] = $linesArray['manual_queries'];
        $data['query_path'] = $linesArray['query_path'];
        return $data;
    }
}
if (!function_exists('escape_array')) {
    function escape_array($array)
    {
        if (empty($array)) {
            return $array;
        }
        $db = DB::connection()->getPdo();
        if (is_array($array)) {
            return array_map(function ($value) use ($db) {
                return $db->quote($value);
            }, $array);
        } else {
            // Handle single non-array value
            return $db->quote($array);
        }
    }
}
if (!function_exists('isEmailConfigured')) {
    function isEmailConfigured()
    {
        $email_settings = get_settings('email_settings');
        // dd($email_settings);
        if (
            isset($email_settings['email']) && !empty($email_settings['email']) &&
            isset($email_settings['password']) && !empty($email_settings['password']) &&
            isset($email_settings['smtp_host']) && !empty($email_settings['smtp_host']) &&
            isset($email_settings['smtp_port']) && !empty($email_settings['smtp_port'])
        ) {
            return true;
        } else {
            return false;
        }
    }
}
if (!function_exists('get_current_version')) {
    function get_current_version()
    {
        $dbCurrentVersion = Update::latest()->first();

        return $dbCurrentVersion ? $dbCurrentVersion->version : '1.0.0';
    }
}
if (!function_exists('isAdminOrHasAllDataAccess')) {
    function isAdminOrHasAllDataAccess($type = null, $id = null)
    {
        if ($type == 'user' && $id !== null) {
            $user = User::find($id);
            if ($user) {
                return $user->hasRole('admin') || $user->can('access_all_data') ? true : false;
            }
        } elseif ($type == 'client' && $id !== null) {
            $client = Client::find($id);
            if ($client) {
                return $client->hasRole('admin') || $client->can('access_all_data') ? true : false;
            }
        } elseif ($type == null && $id == null) {
            return getAuthenticatedUser()->hasRole('admin') || getAuthenticatedUser()->can('access_all_data') ? true : false;
        }
        return false;
    }
}
if (!function_exists('getControllerNames')) {
    function getControllerNames()
    {
        $controllersPath = app_path('Http/Controllers');
        $files = File::files($controllersPath);
        $excludedControllers = [
            'ActivityLogController',
            'Controller',
            'HomeController',
            'InstallerController',
            'LanguageController',
            'ProfileController',
            'RolesController',
            'SearchController',
            'SettingsController',
            'UpdaterController',
        ];
        $controllerNames = [];
        foreach ($files as $file) {
            $fileName = pathinfo($file, PATHINFO_FILENAME);
            // Skip controllers in the excluded list
            if (in_array($fileName, $excludedControllers)) {
                continue;
            }
            if (str_ends_with($fileName, 'Controller')) {
                // Convert to singular form, snake_case, and remove 'Controller' suffix
                $controllerName = Str::snake(Str::singular(str_replace('Controller', '', $fileName)));
                $controllerNames[] = $controllerName;
            }
        }
        // Add manually defined types
        $manuallyDefinedTypes = [
            'contract_type',
            'media'
            // Add more types as needed
        ];
        $controllerNames = array_merge($controllerNames, $manuallyDefinedTypes);
        return $controllerNames;
    }
    if (!function_exists('formatSize')) {
        function formatSize($bytes)
        {
            $units = ['B', 'KB', 'MB', 'GB', 'TB'];
            $i = 0;
            while ($bytes >= 1024 && $i < count($units) - 1) {
                $bytes /= 1024;
                $i++;
            }
            return round($bytes, 2) . ' ' . $units[$i];
        }
    }
}
if (!function_exists('getAdminIdByUserRole')) {
    function getAdminIdByUserRole()
    {
        $user = getAuthenticatedUser();
        // dd($user);
        if ($user) {
            $roles = $user->roles;
            foreach ($roles as $role) {
                switch ($role->name) {
                    case 'admin':
                        // If the user is an admin, fetch the admin ID directly
                        $admin = Admin::where('user_id', $user->id)->first();
                        return $admin ? $admin->id : null;
                    case 'member':
                        // If the user is a member, fetch the admin ID from the team member table
                        $teamMember = TeamMember::where('user_id', $user->id)->first();
                        return $teamMember ? $teamMember->admin_id : null;
                    case 'client':
                        // If the user is a client, fetch the admin ID from the client table
                        $client = Client::where('id', $user->id)->first();
                        return $client ? $client->admin_id : null;
                    default:
                        // For any other roles, fetch the admin ID from the team member table
                        $teamMember = TeamMember::where('user_id', $user->id)->first();
                        return $teamMember ? $teamMember->admin_id : null;
                }
            }
        }
        // dd($user);

        return null; // Return null if user is not logged in or has no role
    }
}
if (!function_exists('getSuperAdmin')) {
    function getSuperAdmin()
    {
        $role = Role::where('name', 'superadmin')->first();
        $superadmin = $role->users->first();
        return $superadmin;
    }
}
if (!function_exists('get_subscriptionFeatures')) {
    function get_subscriptionModules()
    {
        $user = getAuthenticatedUser();
        if ($user->hasRole('admin')) {
            $subscription = Subscription::where(['user_id' => Auth::user()->id, 'status' => 'active',])->first();
        } else {
            $adminID = getAdminIdByUserRole();
            $user = Admin::findOrFail($adminID);
            $subscription = Subscription::where(['user_id' => $user->user_id, 'status' => 'active',])->first();
        }
        if ($subscription) {
            $features = json_decode($subscription->features);
            $modules = $features->modules;
            return $modules;
        } else {
            $modules = array();
            return $modules;
        }
    }
}
if (!function_exists('getStatusColor')) {
    function getStatusColor($status)
    {
        switch ($status) {
            case 'sent':
                return 'primary';
            case 'accepted':
            case 'fully_paid':
                return 'success';
            case 'draft':
                return 'secondary';
            case 'declined':
            case 'due':
                return 'danger';
            case 'expired':
            case 'partially_paid':
                return 'warning';
            case 'not_specified':
                return 'secondary';
            default:
                return 'info';
        }
    }
}
if (!function_exists('getStatusCount')) {
    function getStatusCount($status, $type)
    {
        $query = DB::table('estimates_invoices')->where('type', $type);
        if (!empty($status)) {
            $query->where('status', $status);
        }
        return $query->count();
    }
}
if (!function_exists('generate_description_openrouter')) {
    /**
     * Generates a project/task description using OpenRouter's API.
     *
     * @param string $prompt The input for generating the description.
     * @param string|null $apiKey Optional API key to override settings
     * @return array{error: bool, data?: string, message?: string} Response array with status and data/message
     */
    function generate_description_openrouter(string $prompt, $apiKey = null): array
    {
        // Get settings from database
        $settings = get_ai_settings('openrouter');

        // Use provided API key or get from settings
        $apiKey = $apiKey ?: $settings['openrouter_api_key'] ?? null;

        if (empty($apiKey)) {
            Log::error('Missing OpenRouter API key');
            return [
                'error' => true,
                'message' => 'System configuration error: Missing API key.',
            ];
        }

        // Get dynamic settings
        $endpoint = $settings['openrouter_endpoint'] ?? 'https://openrouter.ai/api/v1/chat/completions';
        $model = $settings['openrouter_model'] ?? 'nousresearch/deephermes-3-mistral-24b-preview:free';
        $systemPrompt = $settings['openrouter_system_prompt'] ?? 'You are a helpful assistant that writes concise, professional project or task descriptions.';
        $temperature = $settings['openrouter_temperature'] ?? 0.7;
        $maxTokens = $settings['openrouter_max_tokens'] ?? 1024;
        $topP = $settings['openrouter_top_p'] ?? 0.95;
        $frequencyPenalty = $settings['openrouter_frequency_penalty'] ?? 0;
        $presencePenalty = $settings['openrouter_presence_penalty'] ?? 0;
        $timeout = $settings['request_timeout'] ?? 15;
        $maxRetries = $settings['max_retries'] ?? 2;

        // Apply prompt formatting if configured
        $formattedPrompt = $prompt;
        if (!empty($settings['default_prompt_prefix'])) {
            $formattedPrompt = $settings['default_prompt_prefix'] . ' ' . $formattedPrompt;
        }
        if (!empty($settings['default_prompt_suffix'])) {
            $formattedPrompt .= ' ' . $settings['default_prompt_suffix'];
        }

        // Check prompt length
        $maxPromptLength = $settings['max_prompt_length'] ?? 1000;
        if (empty($formattedPrompt) || strlen($formattedPrompt) > $maxPromptLength) {
            return [
                'error' => true,
                'message' => "Invalid prompt length. Must be between 1 and {$maxPromptLength} characters.",
            ];
        }

        $client = new \GuzzleHttp\Client(['timeout' => $timeout]);
        $attempt = 0;

        while ($attempt < $maxRetries) {
            try {
                $response = $client->post($endpoint, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $apiKey,
                        'HTTP-Referer' => config('app.url'),
                        'X-Title' => 'Taskify', // Optional: Name your app
                        'Content-Type' => 'application/json',
                    ],
                    'json' => [
                        'model' => $model,
                        'messages' => [
                            ['role' => 'system', 'content' => $systemPrompt],
                            ['role' => 'user', 'content' => $formattedPrompt],
                        ],
                        'temperature' => (float)$temperature,
                        'max_tokens' => (int)$maxTokens,
                        'top_p' => (float)$topP,
                        'frequency_penalty' => (float)$frequencyPenalty,
                        'presence_penalty' => (float)$presencePenalty,
                    ],
                ]);

                $body = json_decode($response->getBody(), true);

                if (isset($body['choices'][0]['message']['content'])) {
                    return [
                        'error' => false,
                        'data' => $body['choices'][0]['message']['content'],
                    ];
                }

                return [
                    'error' => true,
                    'message' => $body['error']['message'],
                ];
            } catch (\Exception $e) {
                $attempt++;
                if ($attempt >= $maxRetries) {
                    Log::error('OpenRouter API Error', [
                        'message' => $e->getMessage(),
                    ]);

                    // Try fallback if enabled
                    if (
                        !empty($settings['enable_fallback']) && $settings['enable_fallback'] &&
                        !empty($settings['fallback_provider']) && $settings['fallback_provider'] === 'gemini'
                    ) {
                        $fallbackResult = generate_description_gemini($prompt);
                        if (!$fallbackResult['error']) {
                            // Add note that fallback was used
                            $fallbackResult['data'] = '[Generated using fallback provider] ' . $fallbackResult['data'];
                        }
                        return $fallbackResult;
                    }

                    return [
                        'error' => true,
                        'message' => 'An error occurred while generating the description using OpenRouter API.',
                    ];
                }

                // Wait before retrying
                $retryDelay = $settings['retry_delay'] ?? 1;
                sleep($retryDelay);
            }
        }

        // Should not reach here, but just in case
        return [
            'error' => true,
            'message' => 'Failed to generate description after multiple attempts.',
        ];
    }
}

if (!function_exists('generate_description_gemini')) {
    /**
     * Generates a project/task description using Gemini API.
     *
     * @param string $prompt The input for generating the description.
     * @param string|null $apiKey Optional API key to override settings
     * @return array{error: bool, data?: string, message?: string} Response array with status and data/message
     */
    function generate_description_gemini(string $prompt, $apiKey = null)
    {
        try {
            // Get settings from database
            $settings = get_ai_settings('gemini');

            // Use provided API key or get from settings
            $apiKey = $apiKey ?: $settings['gemini_api_key'] ?? null;

            if (empty($apiKey)) {
                Log::error('Missing Gemini API key');
                return [
                    'error' => true,
                    'message' => 'System configuration error: Missing API key.',
                ];
            }

            // Get dynamic settings
            $model = $settings['gemini_model'] ?? 'gemini-2.0-flash';
            $endpointTemplate = $settings['gemini_endpoint'] ?? 'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent';
            $endpoint = sprintf($endpointTemplate, $model);

            if (strpos($endpoint, '?key=') === false) {
                $endpoint .= '?key=' . $apiKey;
            }

            $temperature = $settings['gemini_temperature'] ?? 0.7;
            $topK = $settings['gemini_top_k'] ?? 40;
            $topP = $settings['gemini_top_p'] ?? 0.95;
            $maxOutputTokens = $settings['gemini_max_output_tokens'] ?? 1024;
            $timeout = $settings['request_timeout'] ?? 15;
            $maxRetries = $settings['max_retries'] ?? 2;

            // Rate limiting settings
            $MAX_REQUESTS_PER_MINUTE = $settings['rate_limit_per_minute'] ?? 15;
            $MAX_REQUESTS_PER_DAY = $settings['rate_limit_per_day'] ?? 1500;

            $userId = auth()->user()?->id ?? request()->ip();
            $minuteKey = "gemini_rate_minute_{$userId}";
            $dayKey = "gemini_rate_day_{$userId}";
            $currentTime = now();

            $minuteRequests = Cache::get($minuteKey, 0);
            if ($minuteRequests >= $MAX_REQUESTS_PER_MINUTE) {
                $retryAfter = 60 - $currentTime->second;
                return [
                    'error' => true,
                    'message' => "Rate limit exceeded. Please try again in {$retryAfter} seconds.",
                ];
            }

            $dayRequests = Cache::get($dayKey, 0);
            if ($dayRequests >= $MAX_REQUESTS_PER_DAY) {
                $tomorrow = $currentTime->addDay()->startOfDay();
                $hoursRemaining = $currentTime->diffInHours($tomorrow);
                return [
                    'error' => true,
                    'message' => "Daily limit exceeded. Please try again in {$hoursRemaining} hours.",
                ];
            }

            // Apply prompt formatting if configured
            $formattedPrompt = $prompt;
            if (!empty($settings['default_prompt_prefix'])) {
                $formattedPrompt = $settings['default_prompt_prefix'] . ' ' . $formattedPrompt;
            }
            if (!empty($settings['default_prompt_suffix'])) {
                $formattedPrompt .= ' ' . $settings['default_prompt_suffix'];
            }

            // Set default prompt prefix for Gemini if not specified
            if (strpos($formattedPrompt, "Generate a concise") === false) {
                $formattedPrompt = "Generate a concise, professional description for the following: {$formattedPrompt}";
            }

            $maxPromptLength = $settings['max_prompt_length'] ?? 1000;
            if (empty($formattedPrompt) || strlen($formattedPrompt) > $maxPromptLength) {
                return [
                    'error' => true,
                    'message' => "Invalid prompt length. Must be between 1 and {$maxPromptLength} characters.",
                ];
            }

            $client = new \GuzzleHttp\Client(['timeout' => $timeout]);
            $attempt = 0;

            while ($attempt < $maxRetries) {
                try {
                    $response = $client->post($endpoint, [
                        'headers' => [
                            'Content-Type' => 'application/json',
                        ],
                        'json' => [
                            'contents' => [
                                [
                                    'parts' => [
                                        [
                                            'text' => $formattedPrompt
                                        ]
                                    ]
                                ]
                            ],
                            'generationConfig' => [
                                'temperature' => (float)$temperature,
                                'topK' => (int)$topK,
                                'topP' => (float)$topP,
                                'maxOutputTokens' => (int)$maxOutputTokens,
                            ]
                        ]
                    ]);

                    $result = json_decode($response->getBody(), true);

                    if (!isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                        return [
                            'error' => true,
                            'message' => 'Invalid API response. Please Contact Support'
                        ];
                    }

                    Cache::put($minuteKey, $minuteRequests + 1, now()->addMinutes(1));
                    Cache::put($dayKey, $dayRequests + 1, now()->addDays(1));

                    return [
                        'error' => false,
                        'data' => $result['candidates'][0]['content']['parts'][0]['text'],
                    ];
                } catch (\Exception $e) {
                    $attempt++;
                    if ($attempt >= $maxRetries) {
                        Log::error('Gemini API Error', [
                            'message' => $e->getMessage(),
                        ]);

                        // Try fallback if enabled
                        if (
                            !empty($settings['enable_fallback']) && $settings['enable_fallback'] &&
                            !empty($settings['fallback_provider']) && $settings['fallback_provider'] === 'openrouter'
                        ) {
                            $fallbackResult = generate_description_openrouter($prompt);
                            if (!$fallbackResult['error']) {
                                // Add note that fallback was used
                                $fallbackResult['data'] = '[Generated using fallback provider] ' . $fallbackResult['data'];
                            }
                            return $fallbackResult;
                        }

                        return [
                            'error' => true,
                            'message' => 'Failed to generate description. Please try again later.',
                        ];
                    }

                    // Wait before retrying
                    $retryDelay = $settings['retry_delay'] ?? 1;
                    sleep($retryDelay);
                }
            }
        } catch (\Exception $e) {
            Log::critical('Unexpected Error in generate_description_gemini', [
                'error' => $e->getMessage(),
            ]);

            return [
                'error' => true,
                'message' => 'An unexpected error occurred. Please try again later.',
            ];
        }
    }
}
if (!function_exists('generate_description')) {
    /**
     * Determines which AI model to use and generates a description.
     *
     * @param string $prompt The input for generating the description.
     * @return mixed The generated description or error response.
     */
    function generate_description(string $prompt)
    {
        $ai_model_settings = get_settings('ai_model_settings');

        $selectedModel = $ai_model_settings['is_active']; // Assume this is stored in app settings

        if ($selectedModel === 'openrouter') {
            Log::info('Creating Description Using Openrouter AI Model/API');
            return generate_description_openrouter($prompt, $ai_model_settings['openrouter_api_key']);
        } elseif ($selectedModel === 'gemini') {
            Log::info('Creating Description Using Google Gemini AI Model/API');
            return generate_description_gemini($prompt, $ai_model_settings['gemini_api_key']);
        } else {

            return [
                'error' => true,
                'message' => 'Invalid AI model selected. Please update your settings.'
            ];
        }
    }
}

if (!function_exists('get_ai_settings')) {
    /**
     * Retrieve AI model settings from the database
     *
     * @param string|null $provider Specific provider to get settings for
     * @return array AI settings from the database with defaults applied
     */
    function get_ai_settings(?string $provider = null): array
    {
        $settings = Setting::where('variable', 'ai_model_settings')->first();

        if (!$settings) {
            // Return default settings if none found
            return [
                'is_active' => 'openrouter',
                'openrouter_endpoint' => 'https://openrouter.ai/api/v1/chat/completions',
                'openrouter_system_prompt' => 'You are a helpful assistant that writes concise, professional project or task descriptions.',
                'openrouter_temperature' => 0.7,
                'openrouter_max_tokens' => 1024,
                'openrouter_top_p' => 0.95,

                'gemini_endpoint' => 'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent',
                'gemini_temperature' => 0.7,
                'gemini_top_k' => 40,
                'gemini_top_p' => 0.95,
                'gemini_max_output_tokens' => 1024,

                'rate_limit_per_minute' => 15,
                'rate_limit_per_day' => 1500,
                'max_retries' => 2,
                'retry_delay' => 1,
                'request_timeout' => 15,
                'max_prompt_length' => 1000,
            ];
        }

        $settings = json_decode($settings->value, true);

        // If a specific provider is requested, only return those settings
        if ($provider) {
            $providerSettings = [];

            // Get all settings that belong to the requested provider
            foreach ($settings as $key => $value) {
                if (strpos($key, $provider) === 0 || !str_contains($key, 'openrouter_') && !str_contains($key, 'gemini_')) {
                    $providerSettings[$key] = $value;
                }
            }

            // Add global settings that aren't provider-specific
            $globalKeys = [
                'is_active',
                'rate_limit_per_minute',
                'rate_limit_per_day',
                'max_retries',
                'retry_delay',
                'request_timeout',
                'max_prompt_length',
                'enable_fallback',
                'fallback_provider'
            ];

            foreach ($globalKeys as $key) {
                if (isset($settings[$key])) {
                    $providerSettings[$key] = $settings[$key];
                }
            }

            return $providerSettings;
        }

        return $settings;
    }
}
if (!function_exists('format_currency')) {
    function format_currency($amount, $is_currency_symbol = 1)
    {
        $general_settings = get_settings('general_settings');
        $currency_symbol = $general_settings['currency_symbol'] ?? '₹';
        $currency_format = $general_settings['currency_formate'] ?? 'comma_separated';
        $decimal_points = intval($general_settings['decimal_points_in_currency'] ?? '2');
        $currency_symbol_position = $general_settings['currency_symbol_position'] ?? 'before';
        // Determine the appropriate separators based on the currency format
        $thousands_separator = ($currency_format == 'comma_separated') ? ',' : '.';
        // Format the amount with the determined separators
        // dd(number_format($amount, $decimal_points, '.', $thousands_separator));
        $formatted_amount = number_format($amount, $decimal_points, '.', $thousands_separator);
        if ($is_currency_symbol) {
            // Format currency symbol position
            if ($currency_symbol_position === 'before') {
                $currency_amount = $currency_symbol . ' ' . $formatted_amount;
            } else {
                $currency_amount = $formatted_amount . ' ' . $currency_symbol;
            }
            return $currency_amount;
        }
        return $formatted_amount;
    }
}
function get_tax_data($tax_id, $total_amount, $currency_symbol = 0)
{
    // Check if tax_id is not empty
    if ($tax_id != '') {
        // Retrieve tax data from the database using the tax_id
        $tax = Tax::find($tax_id);
        // Check if tax data is found
        if ($tax) {
            // Get tax rate and type
            $taxRate = $tax->amount;
            $taxType = $tax->type;
            // Calculate tax amount based on tax rate and type
            $taxAmount = 0;
            $disp_tax = '';
            if ($taxType == 'percentage') {
                $taxAmount = ($total_amount * $tax->percentage) / 100;
                $disp_tax = format_currency($taxAmount, $currency_symbol) . '(' . $tax->percentage . '%)';
            } elseif ($taxType == 'amount') {
                $taxAmount = $taxRate;
                $disp_tax = format_currency($taxAmount, $currency_symbol);
            }
            // Return the calculated tax data
            return [
                'taxAmount' => $taxAmount,
                'taxType' => $taxType,
                'dispTax' => $disp_tax,
            ];
        }
    }
    // Return empty data if tax_id is empty or tax data is not found
    return [
        'taxAmount' => 0,
        'taxType' => '',
        'dispTax' => '',
    ];
}
if (!function_exists('format_budget')) {
    function format_budget($amount)
    {
        // Check if the input is numeric or can be converted to a numeric value.
        if (!is_numeric($amount)) {
            // If the input is not numeric, return null or handle the error as needed.
            return null;
        }
        // Remove non-numeric characters from the input string.
        $amount = preg_replace('/[^0-9.]/', '', $amount);
        // Convert the input to a float.
        $amount = (float) $amount;
        // Define suffixes for thousands, millions, etc.
        $suffixes = ['', 'K', 'M', 'B', 'T'];
        // Determine the appropriate suffix and divide the amount accordingly.
        $suffixIndex = 0;
        while ($amount >= 1000 && $suffixIndex < count($suffixes) - 1) {
            $amount /= 1000;
            $suffixIndex++;
        }
        // Format the amount with the determined suffix.
        return number_format($amount, 2) . $suffixes[$suffixIndex];
    }
}
if (!function_exists('canSetStatus')) {
    function canSetStatus($status)
    {
        static $user = null;
        static $isAdminOrHasAllDataAccess = null;
        if ($user === null) {
            $user = getAuthenticatedUser();
        }
        if ($isAdminOrHasAllDataAccess === null) {
            $isAdminOrHasAllDataAccess = isAdminOrHasAllDataAccess();
        }
        // Check if the user has permission for this status
        $hasPermission = $status->roles->contains($user->roles->first()->id) || $isAdminOrHasAllDataAccess;
        return $hasPermission;
    }
}
if (!function_exists('checkPermission')) {
    function checkPermission($permission)
    {
        static $user = null;
        if ($user === null) {
            $user = getAuthenticatedUser();
        }
        return $user->can($permission);
    }
}
if (!function_exists('getUserPreferences')) {
    function getUserPreferences($table, $column = 'visible_columns', $userId = null)
    {
        if ($userId === null) {
            $userId = getAuthenticatedUser(true, true);
        }
        $result = UserClientPreference::where('user_id', $userId)
            ->where('table_name', $table)
            ->first();
        switch ($column) {
            case 'default_view':
                // if ($table == 'projects') {
                //     // dd($result->default_view);
                //     switch ($result->default_view) {
                //         case 'list':
                //             return 'list';
                //         case 'kanban_view':
                //             return 'kanban_view';
                //         case 'grid':
                //             return 'grid';
                //         default:
                //             return 'projects'; // or handle unexpected cases
                //     }
                // }
                if ($table == 'projects') {
                    return $result && $result->default_view
                        ? ($result->default_view == 'list' ? 'list'
                            : ($result->default_view == 'kanban_view' ? 'kanban_view'
                                : ($result->default_view == 'grid' ? 'grid'
                                    : 'projects')))
                        : 'projects';
                } elseif ($table == 'meetings') {
                    return $result->default_view ?? 'list';
                } elseif ($table == 'tasks') {
                    return $result && $result->default_view
                        ? ($result->default_view == 'draggable' ? 'tasks/draggable'
                            : ($result->default_view == 'calendar-view' ? 'tasks/calendar-view'
                                : ($result->default_view == 'group-by-task-list' ? 'tasks/group-by-task-list'
                                    : 'tasks')))
                        : 'tasks';
                }

                break;
            case 'visible_columns':
                return $result && $result->visible_columns ? $result->visible_columns : [];
                break;
            case 'enabled_notifications':
            case 'enabled_notifications':
                if ($result) {
                    if ($result->enabled_notifications === null) {
                        return null;
                    }
                    return json_decode($result->enabled_notifications, true);
                }
                return [];
                break;
                break;
            default:
                return null;
                break;
        }
    }
}
if (!function_exists('getOrdinalSuffix')) {
    function getOrdinalSuffix($number)
    {
        if (!in_array(($number % 100), [11, 12, 13])) {
            switch ($number % 10) {
                case 1:
                    return 'st';
                case 2:
                    return 'nd';
                case 3:
                    return 'rd';
            }
        }
        return 'th';
    }
}
if (!function_exists('get_php_date_time_format')) {
    function get_php_date_time_format($timeFormat = false)
    {
        $general_settings = get_settings('general_settings');
        if ($timeFormat) {
            return $general_settings['time_format'] ?? 'H:i:s';
        } else {
            $date_format = $general_settings['date_format'] ?? 'DD-MM-YYYY|d-m-Y';
            $date_format = explode('|', $date_format);
            return $date_format[1];
        }
    }
}
// Process all type of the notfications
if (!function_exists('processNotifications')) {
    /**
     * Process all type of the notfications
     *
     * @param array $data Notification data
     * @param array $recipients Recipients of the notification
     * @return void
     */
    function processNotifications($data, $recipients)
    {
        // dd($data);

        // Define an array of types for which email notifications should be sent
        $smsNotificationTypes = ['project_assignment', 'project_status_updation', 'task_assignment', 'task_status_updation', 'workspace_assignment', 'meeting_assignment', 'leave_request_creation', 'leave_request_status_updation', 'team_member_on_leave_alert', 'project_issue', 'announcement'];
        $emailNotificationTypes = ['project_assignment', 'project_status_updation', 'interview_assignment', 'interview_status_update', 'task_assignment', 'task_status_updation', 'workspace_assignment', 'meeting_assignment', 'leave_request_creation', 'leave_request_status_updation', 'team_member_on_leave_alert', 'project_issue', 'announcement'];
        if (!empty($recipients)) {
            $mapping = [
                'task_status_updation' => 'task',
                'project_status_updation' => 'project',
                'leave_request_creation' => 'leave_request',
                'leave_request_status_updation' => 'leave_request',
                'team_member_on_leave_alert' => 'leave_request',

            ];
            $type = $mapping[$data['type']] ?? $data['type'];
            $template = getNotificationTemplate($data['type'], 'system');
            if (!$template || ($template->status !== 0)) {
                $notification = Notification::create([
                    'workspace_id' => getWorkspaceId(),
                    'from_id' => isClient() ? 'c_' . session()->get('user_id') : 'u_' . session()->get('user_id'),
                    'type' => $type,
                    'type_id' => $data['type_id'],
                    'action' => $data['action'],
                    'title' => getTitle($data),
                    'message' => get_message($data, NULL, 'system'),
                ]);
            }
            // Exclude creator from receiving notification
            $loggedInUserId = isClient() ? 'c_' . getAuthenticatedUser()->id : 'u_' . getAuthenticatedUser()->id;
            $recipients = array_diff($recipients, [$loggedInUserId]);
            $recipients = array_unique($recipients);
            foreach ($recipients as $recipient) {
                $enabledNotifications = getUserPreferences('notification_preference', 'enabled_notifications', $recipient);
                $recipientId = substr($recipient, 2);
                if (substr($recipient, 0, 2) === 'u_') {
                    $recipientModel = User::find($recipientId);
                } elseif (substr($recipient, 0, 2) === 'c_') {
                    $recipientModel = Client::find($recipientId);
                } elseif (substr($recipient, 0, 2) === 'ca') {
                    $recipientModel = Candidate::find($recipientId);
                }

                // Check if recipient was found
                if ($recipientModel) {
                    if (!$template || ($template->status !== 0)) {
                        if (
                            (is_array($enabledNotifications) && empty($enabledNotifications)) || (
                                is_array($enabledNotifications) && (
                                    in_array('system_' . $data['type'] . '_assignment', $enabledNotifications) ||
                                    in_array('system_' . $data['type'], $enabledNotifications)
                                )
                            )
                        ) {

                            $recipientModel->notifications()->attach($notification->id);
                        }
                    }
                    if (in_array($data['type'] . '_assignment', $emailNotificationTypes) || in_array($data['type'], $emailNotificationTypes)) {
                        if (
                            (is_array($enabledNotifications) && empty($enabledNotifications)) || (
                                is_array($enabledNotifications) && (
                                    in_array('email_' . $data['type'] . '_assignment', $enabledNotifications) ||
                                    in_array('email_' . $data['type'], $enabledNotifications)
                                )
                            )
                        ) {
                            try {
                                sendEmailNotification($recipientModel, $data);
                            } catch (\Exception $e) {
                                // dd($e->getMessage());
                                Log::error('Email Notification Error: ' . $e->getMessage());
                            } catch (TransportExceptionInterface $e) {
                                Log::error('Email Notification Transport Error: ' . $e->getMessage());
                            } catch (Throwable $e) {
                                Log::error('Email Notification Throwable Error: ' . $e->getMessage());
                                // Catch any other throwable, including non-Exception errors
                            }
                        }
                    }
                    if (in_array($data['type'] . '_assignment', $smsNotificationTypes) || in_array($data['type'], $smsNotificationTypes)) {
                        if (
                            (is_array($enabledNotifications) && empty($enabledNotifications)) || (
                                is_array($enabledNotifications) && (
                                    in_array('sms_' . $data['type'] . '_assignment', $enabledNotifications) ||
                                    in_array('sms_' . $data['type'], $enabledNotifications)
                                )
                            )
                        ) {
                            try {
                                sendSMSNotification($data, $recipientModel);
                            } catch (\Exception $e) {
                                Log::error('SMS Notification Error' . $e->getMessage());
                            }
                        }
                    }
                    if (
                        (is_array($enabledNotifications) && empty($enabledNotifications)) || (
                            is_array($enabledNotifications) && (
                                in_array('whatsapp_' . $data['type'] . '_assignment', $enabledNotifications) ||
                                in_array('whatsapp_' . $data['type'], $enabledNotifications)
                            )
                        )
                    ) {
                        try {
                            sendWhatsAppNotification($data, $recipientModel);
                        } catch (\Exception $e) {
                            Log::error('WhatsApp Notification Error: ' . $e->getMessage());
                        }
                    }
                    if (
                        (is_array($enabledNotifications) && empty($enabledNotifications)) || (
                            is_array($enabledNotifications) && (
                                in_array('slack_' . $data['type'] . '_assignment', $enabledNotifications) ||
                                in_array('slack_' . $data['type'], $enabledNotifications)
                            )
                        )
                    ) {
                        try {
                            // dd($data, $recipientModel);
                            sendSlackNotification($data, $recipientModel);
                        } catch (\Exception $e) {

                            Log::error('Slack Notification Error: ' . $e->getMessage());
                        }
                    }
                }
            }
        }
    }
}
if (!function_exists('sendEmailNotification')) {
    function sendEmailNotification($recipientModel, $data)
    {
        $template = getNotificationTemplate($data['type']);
        if (!$template || ($template->status !== 0)) {
            $recipientModel->notify(new AssignmentNotification($recipientModel, $data));
        }
    }
}
if (!function_exists('sendSlackNotification')) {
    function sendSlackNotification($data, $recipient)
    {
        $template = getNotificationTemplate($data['type'], 'slack');
        if (!$template || ($template->status !== 0)) {
            send_slack_notification($data, $recipient);
        }
    }
}
if (!function_exists('sendSMSNotification')) {
    function sendSMSNotification($data, $recipient)
    {
        $template = getNotificationTemplate($data['type'], 'sms');
        if (!$template || ($template->status !== 0)) {
            send_sms($data, $recipient);
        }
    }
}
if (!function_exists('sendWhatsAppNotification')) {
    function sendWhatsAppNotification($data, $recipient)
    {
        $template = getNotificationTemplate($data['type'], 'whatsapp');
        if (!$template || ($template->status !== 0)) {
            send_whatsapp_notification($data, $recipient);
        }
    }
}
if (!function_exists('getNotificationTemplate')) {
    /**
     * Retrieves the notification template based on the given type and medium.
     *
     * This function queries the Template model to find a template record that matches
     * the provided type and medium (either 'email' or 'sms'). It first attempts to find
     * a template with the name pattern '{type}_assignment'. If not found, it searches
     * for a template with the name corresponding to the type. Returns the first matching
     * template or null if no match is found.
     *
     * @param string $type The type of the notification (e.g., 'project', 'task').
     * @param string $emailOrSMS The medium of the notification, either 'email' or 'sms'.
     *                           Defaults to 'email'.
     *
     * @return \App\Models\Template|null The notification template or null if not found.
     */

    function getNotificationTemplate($type, $emailOrSMS = 'email')
    {
        $template = Template::where('type', $emailOrSMS)
            ->where('name', $type . '_assignment')
            ->first();
        if (!$template) {
            // If template with $type . '_assignment' name not found, check for template with $type name
            $template = Template::where('type', $emailOrSMS)
                ->where('name', $type)
                ->first();
        }
        return $template;
    }
}
if (!function_exists('send_sms')) {
    /**
     * Sends an SMS message using predefined settings.
     *
     * This function constructs and sends an SMS message via a specified SMS gateway.
     * It retrieves the message content using the get_message function and formats
     * it according to the gateway's requirements, including body, header, and params.
     * The settings for the SMS gateway are retrieved from the application's configuration.
     *
     * @param array $itemData The data required to construct the message content.
     * @param object $recipient The recipient object containing phone number and country code.
     *
     * @return void
     */

    function send_sms($itemData, $recipient)
    {
        // print_r($recipient);
        $msg = get_message($itemData, $recipient);
        try {
            $sms_gateway_settings = get_settings('sms_gateway_settings', true);
            $data = [
                "base_url" => $sms_gateway_settings['base_url'],
                "sms_gateway_method" => $sms_gateway_settings['sms_gateway_method']
            ];
            $data["body"] = [];
            if (isset($sms_gateway_settings["body_formdata"])) {
                foreach ($sms_gateway_settings["body_formdata"] as $key => $value) {
                    $value = parse_sms($value, $recipient->phone, $msg, $recipient->country_code);
                    $data["body"][$key] = $value;
                }
            }
            $data["header"] = [];
            if (isset($sms_gateway_settings["header_data"])) {
                foreach ($sms_gateway_settings["header_data"] as $key => $value) {
                    $value = parse_sms($value, $recipient->phone, $msg, $recipient->country_code);
                    $data["header"][] = $key . ": " . $value;
                }
            }
            $data["params"] = [];
            if (isset($sms_gateway_settings["params_data"])) {
                foreach ($sms_gateway_settings["params_data"] as $key => $value) {
                    $value = parse_sms($value, $recipient->phone, $msg, $recipient->country_code);
                    $data["params"][$key] = $value;
                }
            }
            $response = curl_sms($data["base_url"], $data["sms_gateway_method"], $data["body"], $data["header"]);
            // print_r($response);
        } catch (Exception $e) {
            // Handle the exception
            // echo 'Error: ' . $e->getMessage();
        }
    }
}
if (!function_exists('send_whatsapp_notification')) {
    /**
     * Sends a WhatsApp notification using a predefined template.
     *
     * This function constructs and sends a WhatsApp message via the Facebook Graph API,
     * using the 'taskify_saas_notification' template. It replaces placeholders in the
     * template with the provided message and company title. The function logs the success
     * or failure of the message sending process.
     *
     * @param array $itemData The data required to construct the message content.
     * @param object $recipient The recipient object containing phone number and country code.
     *
     * @return void
     */

    function send_whatsapp_notification($itemData, $recipient)
    {
        $msg = get_message($itemData, $recipient, 'whatsapp');
        $whatsapp_settings = get_settings('whatsapp_settings', true);
        $general_settings = get_settings('general_settings');
        $company_title = $general_settings['company_title'] ?? 'Taskify';
        $client = new GuzzleHttpClient();
        try {
            $response = $client->post('https://graph.facebook.com/v20.0/' . $whatsapp_settings['whatsapp_phone_number_id'] . '/messages', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $whatsapp_settings['whatsapp_access_token'],
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $recipient->country_code . $recipient->phone,
                    'type' => 'template',
                    'template' => [
                        'name' => 'taskify_saas_notification',
                        'language' => [
                            'code' => 'en'
                        ],
                        'components' => [
                            [
                                'type' => 'body',
                                'parameters' => [
                                    [
                                        'type' => 'text',
                                        'text' => $msg  // This will replace {{1}}
                                    ],
                                    [
                                        'type' => 'text',
                                        'text' => $company_title  // This will replace {{2}}
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
            ]);
            $data = json_decode($response->getBody(), true);
            Log::info("Message sent successfully. Response: " . print_r($data, true));
        } catch (RequestException $e) {
            Log::error("Error sending message: " . $e->getMessage());
            if ($e->hasResponse()) {
                Log::error("Response: " . $e->getResponse()->getBody()->getContents());
            }
        }
    }
}
if (!function_exists('send_slack_notification')) {
    /**
     * Send a Slack direct message to a user, given an item data array and a recipient object.
     *
     * @param array $itemData An array containing the item data.
     * @param object $recipient A user object containing the email address of the recipient.
     *
     * @return void
     */
    function send_slack_notification($itemData, $recipient)
    {
        $msg = get_message($itemData, $recipient);
        $slack_settings = get_settings('slack_settings');
        // dd($itemData, $recipient, $msg, $slack_settings);
        $botToken = $slack_settings['slack_bot_token'];
        // Create a Guzzle client for Slack API
        $client = new GuzzleHttpClient([
            'base_uri' => 'https://slack.com/api/',
            'headers' => [
                'Authorization' => 'Bearer ' . $botToken,
                'Content-Type' => 'application/json',
            ],
        ]);
        // Step 4: Look up the Slack user ID by email
        // dd($recipient);
        $email = $recipient->email;
        // dd($itemData, $recipient);
        // $email = 'infinitietechnologies10@gmail.com';
        $userId = get_slack_user_id_by_email($client, $email);
        if ($userId) {
            // Step 5: Prepare the message payload
            // Assuming template has a 'content' field
            $slackMessage = [
                'channel' => $userId,
                'text' => $msg,
                'username' => 'Taskify Notification',
                'icon_emoji' => ':office:',
            ];
            try {
                // Step 6: Send the Slack message
                $response = $client->post('chat.postMessage', [
                    'json' => $slackMessage
                ]);
                $responseBody = json_decode(
                    $response->getBody(),
                    true
                );
                if ($responseBody['ok']) {
                    Log::info('Slack DM sent successfully to user: ' . $userId);
                } else {
                    Log::warning('Failed to send Slack DM to user ' . $userId . ': ' . $responseBody['error']);
                }
            } catch (\Exception $e) {
                Log::error('Error sending Slack DM to user: ' . $userId . ', Error: ' . $e->getMessage());
            }
        } else {
            Log::warning('Slack user ID not found for email: ' . $email);
        }
    }
}
/**
 * Helper function to get Slack user ID by email
 */
function get_slack_user_id_by_email($client, $email)
{
    // dd($email);
    try {
        $response = $client->get('users.lookupByEmail', [
            'query' => ['email' => $email]
        ]);
        $body = json_decode($response->getBody(), true);
        if ($body['ok'] === true) {
            return $body['user']['id']; // Return Slack User ID
        } else {
            Log::error("Failed to get Slack user ID: " . $body['error']);
        }
    } catch (\Exception $e) {
        Log::error('Error getting Slack user ID for email ' . $email . ': ' . $e->getMessage());
    }
}
if (!function_exists('curl_sms')) {
    /**
     * Perform a curl request to the specified URL
     *
     * @param string $url The URL to make the request to
     * @param string $method The HTTP method to use (default: GET)
     * @param array $data The data to send with the request (default: empty array)
     * @param array $headers The headers to send with the request (default: empty array)
     *
     * @return array An associative array with the following keys:
     *     - body: The response body as a JSON-decoded array
     *     - http_code: The HTTP status code of the response
     */
    function curl_sms($url, $method = 'GET', $data = [], $headers = [])
    {
        $ch = curl_init();
        $curl_options = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded',
            )
        );
        if (count($headers) != 0) {
            $curl_options[CURLOPT_HTTPHEADER] = $headers;
        }
        if (strtolower($method) == 'post') {
            $curl_options[CURLOPT_POST] = 1;
            $curl_options[CURLOPT_POSTFIELDS] = http_build_query($data);
        } else {
            $curl_options[CURLOPT_CUSTOMREQUEST] = 'GET';
        }
        curl_setopt_array($ch, $curl_options);
        $result = array(
            'body' => json_decode(curl_exec($ch), true),
            'http_code' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
        );
        return $result;
    }
}
if (!function_exists('parse_sms')) {
    /**
     * Parses a given SMS template and replaces placeholders with actual values.
     *
     * This function is a placeholder and should be replaced with actual SMS parsing logic.
     *
     * @param string $template The SMS template containing placeholders.
     * @param string $phone The recipient's phone number.
     * @param string $msg The message content.
     * @param string $country_code The recipient's country code.
     *
     * @return string The parsed SMS template with placeholders replaced by actual values.
     */
    function parse_sms($template, $phone, $msg, $country_code)
    {
        // Implement your parsing logic here
        // This is just a placeholder
        return str_replace(['{only_mobile_number}', '{message}', '{country_code}'], [$phone, $msg, $country_code], $template);
    }
}
if (!function_exists('get_message')) {
    /**
     * Generates a notification message based on the provided data, recipient, and type.
     *
     * This function retrieves a template based on the notification type and data,
     * and fills in placeholders with the appropriate content for the recipient.
     *
     * @param array $data An associative array containing notification details, such as type, type_id, type_title, etc.
     * @param object $recipient The recipient object that contains recipient details, such as first_name, last_name, and email.
     * @param string $type The type of notification (e.g., 'sms', 'system', 'slack'), default is 'sms'.
     *
     * @return string The generated notification message with placeholders replaced by actual values.
     */

    function get_message($data, $recipient, $type = 'sms')
    {
        static $authUser = null;
        static $company_title = null;
        if ($authUser === null) {
            $authUser = getAuthenticatedUser();
        }
        if ($company_title === null) {
            $general_settings = get_settings('general_settings');
            $company_title = $general_settings['company_title'] ?? 'Taskify-SaaS';
        }
        $siteUrl = request()->getSchemeAndHttpHost() . '/master-panel';
        $fetched_data = Template::where('type', $type)
            ->where('name', $data['type'] . '_assignment')
            ->first();
        if (!$fetched_data) {
            // If template with $this->data['type'] . '_assignment' name not found, check for template with $this->data['type'] name
            $fetched_data = Template::where('type', $type)
                ->where('name', $data['type'])
                ->first();
        }
        $templateContent = 'Default Content';
        $contentPlaceholders = []; // Initialize outside the switch
        // Customize content based on type
        if ($type === 'system') {
            switch ($data['type']) {
                case 'project':
                    $contentPlaceholders = [
                        '{PROJECT_ID}' => $data['type_id'],
                        '{PROJECT_TITLE}' => $data['type_title'],
                        '{ASSIGNEE_FIRST_NAME}' => $authUser->first_name,
                        '{ASSIGNEE_LAST_NAME}' => $authUser->last_name,
                        '{COMPANY_TITLE}' => $company_title,
                        '{PROJECT_URL}' => $siteUrl . '/' . $data['access_url']
                    ];
                    $templateContent = '{ASSIGNEE_FIRST_NAME} {ASSIGNEE_LAST_NAME} assigned you new project: {PROJECT_TITLE}, ID:#{PROJECT_ID}.';
                    break;
                case 'project_status_updation':
                    $contentPlaceholders = [
                        '{PROJECT_ID}' => $data['type_id'],
                        '{PROJECT_TITLE}' => $data['type_title'],
                        '{UPDATER_FIRST_NAME}' => $data['updater_first_name'],
                        '{UPDATER_LAST_NAME}' => $data['updater_last_name'],
                        '{OLD_STATUS}' => $data['old_status'],
                        '{NEW_STATUS}' => $data['new_status'],
                        '{PROJECT_URL}' => $siteUrl . '/' . $data['access_url'],
                        '{COMPANY_TITLE}' => $company_title
                    ];
                    $templateContent = '{UPDATER_FIRST_NAME} {UPDATER_LAST_NAME} has updated the status of project {PROJECT_TITLE}, ID:#{PROJECT_ID}, from {OLD_STATUS} to {NEW_STATUS}.';
                    break;
                case 'project_issue':
                    $contentPlaceholders = [
                        '{ISSUE_ID}' => $data['type_id'],
                        '{ISSUE_TITLE}' => $data['type_title'],
                        '{STATUS}' => $data['status'],
                        '{CREATOR_FIRST_NAME}' => $data['creator_first_name'],
                        '{CREATOR_LAST_NAME}' => $data['creator_last_name'],
                        '{ACCESS_URL}' => $siteUrl . '/' . $data['access_url'],

                        '{COMPANY_TITLE}' => $company_title
                    ];
                    $templateContent = '{CREATOR_FIRST_NAME} {CREATOR_LAST_NAME} has Assigned you new issue: {ISSUE_TITLE}, ID:#{ISSUE_ID} ,Status : {STATUS}';
                    break;
                case 'task':
                    $contentPlaceholders = [
                        '{TASK_ID}' => $data['type_id'],
                        '{TASK_TITLE}' => $data['type_title'],
                        '{ASSIGNEE_FIRST_NAME}' => $authUser->first_name,
                        '{ASSIGNEE_LAST_NAME}' => $authUser->last_name,
                        '{COMPANY_TITLE}' => $company_title,
                        '{TASK_URL}' => $siteUrl . '/' . $data['access_url']
                    ];
                    $templateContent = '{ASSIGNEE_FIRST_NAME} {ASSIGNEE_LAST_NAME} assigned you new task: {TASK_TITLE}, ID:#{TASK_ID}.';
                    break;
                case 'task_status_updation':
                    $contentPlaceholders = [
                        '{TASK_ID}' => $data['type_id'],
                        '{TASK_TITLE}' => $data['type_title'],
                        '{UPDATER_FIRST_NAME}' => $data['updater_first_name'],
                        '{UPDATER_LAST_NAME}' => $data['updater_last_name'],
                        '{OLD_STATUS}' => $data['old_status'],
                        '{NEW_STATUS}' => $data['new_status'],
                        '{TASK_URL}' => $siteUrl . '/' . $data['access_url'],
                        '{COMPANY_TITLE}' => $company_title
                    ];
                    $templateContent = '{UPDATER_FIRST_NAME} {UPDATER_LAST_NAME} has updated the status of task {TASK_TITLE}, ID:#{TASK_ID}, from {OLD_STATUS} to {NEW_STATUS}.';
                    break;
                case 'workspace':
                    $contentPlaceholders = [
                        '{WORKSPACE_ID}' => $data['type_id'],
                        '{WORKSPACE_TITLE}' => $data['type_title'],
                        '{ASSIGNEE_FIRST_NAME}' => $authUser->first_name,
                        '{ASSIGNEE_LAST_NAME}' => $authUser->last_name,
                        '{COMPANY_TITLE}' => $company_title,
                        '{WORKSPACE_URL}' => $siteUrl . '/workspaces'
                    ];
                    $templateContent = '{ASSIGNEE_FIRST_NAME} {ASSIGNEE_LAST_NAME} added you in a new workspace {WORKSPACE_TITLE}, ID:#{WORKSPACE_ID}.';
                    break;
                case 'meeting':
                    $contentPlaceholders = [
                        '{MEETING_ID}' => $data['type_id'],
                        '{MEETING_TITLE}' => $data['type_title'],
                        '{ASSIGNEE_FIRST_NAME}' => $authUser->first_name,
                        '{ASSIGNEE_LAST_NAME}' => $authUser->last_name,
                        '{COMPANY_TITLE}' => $company_title,
                        '{MEETING_URL}' => $siteUrl . '/meetings'
                    ];
                    $templateContent = '{ASSIGNEE_FIRST_NAME} {ASSIGNEE_LAST_NAME} added you in a new meeting {MEETING_TITLE}, ID:#{MEETING_ID}.';
                    break;
                case 'leave_request_creation':
                    $contentPlaceholders = [
                        '{ID}' => $data['type_id'],
                        '{REQUESTEE_FIRST_NAME}' => $data['team_member_first_name'],
                        '{REQUESTEE_LAST_NAME}' => $data['team_member_last_name'],
                        '{TYPE}' => $data['leave_type'],
                        '{FROM}' => $data['from'],
                        '{TO}' => $data['to'],
                        '{DURATION}' => $data['duration'],
                        '{REASON}' => $data['reason'],
                        '{STATUS}' => $data['status'],
                        '{COMPANY_TITLE}' => $company_title
                    ];
                    $templateContent = 'New Leave Request ID:#{ID} Has Been Created By {REQUESTEE_FIRST_NAME} {REQUESTEE_LAST_NAME}.';
                    break;
                case 'leave_request_status_updation':
                    $contentPlaceholders = [
                        '{ID}' => $data['type_id'],
                        '{REQUESTEE_FIRST_NAME}' => $data['team_member_first_name'],
                        '{REQUESTEE_LAST_NAME}' => $data['team_member_last_name'],
                        '{TYPE}' => $data['leave_type'],
                        '{FROM}' => $data['from'],
                        '{TO}' => $data['to'],
                        '{DURATION}' => $data['duration'],
                        '{REASON}' => $data['reason'],
                        '{OLD_STATUS}' => $data['old_status'],
                        '{NEW_STATUS}' => $data['new_status'],
                        '{COMPANY_TITLE}' => $company_title
                    ];
                    $templateContent = 'Leave Request ID:#{ID} Status Updated From {OLD_STATUS} To {NEW_STATUS}.';
                    break;
                case 'team_member_on_leave_alert':
                    $contentPlaceholders = [
                        '{ID}' => $data['type_id'],
                        '{REQUESTEE_FIRST_NAME}' => $data['team_member_first_name'],
                        '{REQUESTEE_LAST_NAME}' => $data['team_member_last_name'],
                        '{TYPE}' => $data['leave_type'],
                        '{FROM}' => $data['from'],
                        '{TO}' => $data['to'],
                        '{DURATION}' => $data['duration'],
                        '{COMPANY_TITLE}' => $company_title
                    ];
                    $templateContent = '{REQUESTEE_FIRST_NAME} {REQUESTEE_LAST_NAME} will be on {TYPE} leave from {FROM} to {TO}.';
                    break;
                case 'announcement':
                    $contentPlaceholders = [
                        '{ANNOUNCEMENT_ID}' => $data['type_id'],
                        '{ANNOUNCEMENT_TITLE}' => $data['type_title'],
                        '{CREATOR_FIRST_NAME}' => $data['creator_first_name'],
                        '{CREATOR_LAST_NAME}' => $data['creator_last_name'],
                        '{COMPANY_TITLE}' => $company_title,
                        '{ACCESS_URL}' => $siteUrl . '/' . $data['access_url'],
                        '{CURRENT_YEAR}' => date('Y'),
                    ];
                    $templateContent = '{CREATOR_FIRST_NAME} {CREATOR_LAST_NAME} has made a new announcement titled "{ANNOUNCEMENT_TITLE}". Shared by {COMPANY_TITLE} ({CURRENT_YEAR}).';
                    break;
                case 'task_reminder':
                    $contentPlaceholders = [
                        '{TASK_ID}' => $data['type_id'],
                        '{TASK_TITLE}' => $data['type_title'],
                        '{TASK_URL}' => $siteUrl . '/' . $data['access_url'],
                        '{COMPANY_TITLE}' => $company_title,
                    ];
                    $templateContent = 'You have a task reminder for Task #{TASK_ID} - "{TASK_TITLE}". You can view the task here: {TASK_URL}.';
                    break;
                case 'recurring_task':
                    $contentPlaceholders = [
                        '{TASK_ID}' => $data['type_id'],
                        '{TASK_TITLE}' => $data['type_title'],
                        '{TASK_URL}' => $siteUrl . '/' . $data['access_url'],
                        '{COMPANY_TITLE}' => $company_title,
                    ];
                    $templateContent = 'The recurring task #{TASK_ID} - "{TASK_TITLE}" has been executed. You can view the new instance here: {TASK_URL}';
                    break;

                case 'todo_reminder':
                    $contentPlaceholders = [
                        '{TODO_ID}' => $data['type_id'],
                        '{TODO_TITLE}' => $data['type_title'],
                        '{TODO_URL}' => $siteUrl . '/' . $data['access_url'],
                        '{COMPANY_TITLE}' => $company_title,
                    ];
                    $templateContent = 'You have a todo reminder for Todo #{TODO_ID} - "{TODO_TITLE}". You can view the task here: {TODO_URL}.';
                    break;
            }
        } else if ($type === 'slack') {
            switch ($data['type']) {
                case 'project':
                    $contentPlaceholders = [
                        '{PROJECT_ID}' => $data['type_id'],
                        '{PROJECT_TITLE}' => $data['type_title'],
                        '{ASSIGNEE_FIRST_NAME}' => $authUser->first_name,
                        '{ASSIGNEE_LAST_NAME}' => $authUser->last_name,
                        '{COMPANY_TITLE}' => $company_title,
                        '{PROJECT_URL}' => $siteUrl . '/' . $data['access_url']
                    ];
                    $templateContent = '*New Project Assigned:* {PROJECT_TITLE}, ID: #{PROJECT_ID}. By {ASSIGNEE_FIRST_NAME} {ASSIGNEE_LAST_NAME}
You can find the project here :{PROJECT_URL}';
                    break;
                case 'project_status_updation':
                    $contentPlaceholders = [
                        '{PROJECT_ID}' => $data['type_id'],
                        '{PROJECT_TITLE}' => $data['type_title'],
                        '{UPDATER_FIRST_NAME}' => $data['updater_first_name'],
                        '{UPDATER_LAST_NAME}' => $data['updater_last_name'],
                        '{OLD_STATUS}' => $data['old_status'],
                        '{NEW_STATUS}' => $data['new_status'],
                        '{PROJECT_URL}' => $siteUrl . '/' . $data['access_url'],
                        '{COMPANY_TITLE}' => $company_title
                    ];
                    $templateContent = '*Project Status Updated:* By {UPDATER_FIRST_NAME} {UPDATER_LAST_NAME} , {PROJECT_TITLE}, ID: #{PROJECT_ID}. Status changed from `{OLD_STATUS}` to `{NEW_STATUS}`.
You can find the project here :{PROJECT_URL}';
                    break;
                case 'project_issue':
                    $contentPlaceholders = [
                        '{ISSUE_ID}' => $data['type_id'],
                        '{ISSUE_TITLE}' => $data['type_title'],
                        '{STATUS}' => $data['status'],
                        '{CREATOR_FIRST_NAME}' => $data['creator_first_name'],
                        '{CREATOR_LAST_NAME}' => $data['creator_last_name'],
                        '{ACCESS_URL}' => $siteUrl . '/' . $data['access_url'],
                        '{FIRST_NAME}' => $recipient->first_name,
                        '{LAST_NAME}' => $recipient->last_name,
                        '{COMPANY_TITLE}' => $company_title
                    ];
                    $templateContent = '{CREATOR_FIRST_NAME} {CREATOR_LAST_NAME} has Assigned you new issue: {ISSUE_TITLE}, ID:#{ISSUE_ID} ,Status : {STATUS}';
                    break;
                case 'task':
                    $contentPlaceholders = [
                        '{TASK_ID}' => $data['type_id'],
                        '{TASK_TITLE}' => $data['type_title'],
                        '{ASSIGNEE_FIRST_NAME}' => $authUser->first_name,
                        '{ASSIGNEE_LAST_NAME}' => $authUser->last_name,
                        '{COMPANY_TITLE}' => $company_title,
                        '{TASK_URL}' => $siteUrl . '/' . $data['access_url']
                    ];
                    $templateContent = '*New Task Assigned:* {TASK_TITLE}, ID: #{TASK_ID}. By {ASSIGNEE_FIRST_NAME} {ASSIGNEE_LAST_NAME}
You can find the task here : {TASK_URL}';
                    break;
                case 'task_status_updation':
                    $contentPlaceholders = [
                        '{TASK_ID}' => $data['type_id'],
                        '{TASK_TITLE}' => $data['type_title'],
                        '{UPDATER_FIRST_NAME}' => $data['updater_first_name'],
                        '{UPDATER_LAST_NAME}' => $data['updater_last_name'],
                        '{OLD_STATUS}' => $data['old_status'],
                        '{NEW_STATUS}' => $data['new_status'],
                        '{TASK_URL}' => $siteUrl . '/' . $data['access_url'],
                        '{COMPANY_TITLE}' => $company_title
                    ];
                    $templateContent = '*Task Status Updated:* By {UPDATER_FIRST_NAME} {UPDATER_LAST_NAME},  {TASK_TITLE}, ID: #{TASK_ID}. Status changed from `{OLD_STATUS}` to `{NEW_STATUS}`.
You can find the Task here : {TASK_URL}';
                    break;
                case 'workspace':
                    $contentPlaceholders = [
                        '{WORKSPACE_ID}' => $data['type_id'],
                        '{WORKSPACE_TITLE}' => $data['type_title'],
                        '{ASSIGNEE_FIRST_NAME}' => $authUser->first_name,
                        '{ASSIGNEE_LAST_NAME}' => $authUser->last_name,
                        '{COMPANY_TITLE}' => $company_title,
                        '{WORKSPACE_URL}' => $siteUrl . '/workspaces'
                    ];
                    $templateContent = '*New Workspace Added:* By {ASSIGNEE_FIRST_NAME} {ASSIGNEE_LAST_NAME},   {WORKSPACE_TITLE}, ID: #{WORKSPACE_ID}.
You can find the Workspace here : {WORKSPACE_URL}';
                    break;
                case 'meeting':
                    $contentPlaceholders = [
                        '{MEETING_ID}' => $data['type_id'],
                        '{MEETING_TITLE}' => $data['type_title'],
                        '{ASSIGNEE_FIRST_NAME}' => $authUser->first_name,
                        '{ASSIGNEE_LAST_NAME}' => $authUser->last_name,
                        '{COMPANY_TITLE}' => $company_title,
                        '{MEETING_URL}' => $siteUrl . '/meetings'
                    ];
                    $templateContent = 'New Meeting Scheduled:* By {ASSIGNEE_FIRST_NAME} {ASSIGNEE_LAST_NAME},  {MEETING_TITLE}, ID: #{MEETING_ID}.
You can find the Meeting here : {MEETING_URL}';
                    break;
                case 'leave_request_creation':
                    $contentPlaceholders = [
                        '{ID}' => $data['type_id'],
                        '{REQUESTEE_FIRST_NAME}' => $data['team_member_first_name'],
                        '{REQUESTEE_LAST_NAME}' => $data['team_member_last_name'],
                        '{TYPE}' => $data['leave_type'],
                        '{FROM}' => $data['from'],
                        '{TO}' => $data['to'],
                        '{DURATION}' => $data['duration'],
                        '{REASON}' => $data['reason'],
                        '{STATUS}' => $data['status'],
                        '{COMPANY_TITLE}' => $company_title
                    ];
                    $templateContent = '*New {TYPE} Leave Request Created:* ID: #{ID} By {REQUESTEE_FIRST_NAME} {REQUESTEE_LAST_NAME} for {REASON}.  From ( {FROM} ) -  To ( {TO} ).';
                    break;
                case 'leave_request_status_updation':
                    $contentPlaceholders = [
                        '{ID}' => $data['type_id'],
                        '{REQUESTEE_FIRST_NAME}' => $data['team_member_first_name'],
                        '{REQUESTEE_LAST_NAME}' => $data['team_member_last_name'],
                        '{TYPE}' => $data['leave_type'],
                        '{FROM}' => $data['from'],
                        '{TO}' => $data['to'],
                        '{DURATION}' => $data['duration'],
                        '{REASON}' => $data['reason'],
                        '{OLD_STATUS}' => $data['old_status'],
                        '{NEW_STATUS}' => $data['new_status'],
                        '{COMPANY_TITLE}' => $company_title
                    ];
                    $templateContent = '*Leave Request Status Updated:* For {REQUESTEE_FIRST_NAME} {REQUESTEE_LAST_NAME},  ID: #{ID}. Status changed from `{OLD_STATUS}` to `{NEW_STATUS}`.';
                    break;
                case 'team_member_on_leave_alert':
                    $contentPlaceholders = [
                        '{ID}' => $data['type_id'],
                        '{REQUESTEE_FIRST_NAME}' => $data['team_member_first_name'],
                        '{REQUESTEE_LAST_NAME}' => $data['team_member_last_name'],
                        '{TYPE}' => $data['leave_type'],
                        '{FROM}' => $data['from'],
                        '{TO}' => $data['to'],
                        '{DURATION}' => $data['duration'],
                        '{COMPANY_TITLE}' => $company_title
                    ];
                    $templateContent = '*Team Member Leave Alert:* {REQUESTEE_FIRST_NAME} {REQUESTEE_LAST_NAME} will be on {TYPE} leave from {FROM} to {TO}.';
                    break;
                case 'announcement':
                    $contentPlaceholders = [
                        '{ANNOUNCEMENT_ID}' => $data['type_id'],
                        '{ANNOUNCEMENT_TITLE}' => $data['type_title'],
                        '{CREATOR_FIRST_NAME}' => $data['creator_first_name'],
                        '{CREATOR_LAST_NAME}' => $data['creator_last_name'],
                        '{COMPANY_TITLE}' => $company_title,
                        '{ACCESS_URL}' => $siteUrl . '/' . $data['access_url'],
                        '{CURRENT_YEAR}' => date('Y'),
                    ];
                    $templateContent = '{CREATOR_FIRST_NAME} {CREATOR_LAST_NAME} has made a new announcement titled "{ANNOUNCEMENT_TITLE}". Shared by {COMPANY_TITLE} ({CURRENT_YEAR}).';
                    break;
                case 'task_reminder':
                    $contentPlaceholders = [
                        '{TASK_ID}' => $data['type_id'],
                        '{TASK_TITLE}' => $data['type_title'],
                        '{TASK_URL}' => $siteUrl . '/' . $data['access_url'],
                        '{COMPANY_TITLE}' => $company_title,
                    ];
                    $templateContent = 'You have a task reminder for Task #{TASK_ID} - "{TASK_TITLE}". You can view the task here: {TASK_URL}.';
                    break;
                case 'recurring_task':
                    $contentPlaceholders = [
                        '{TASK_ID}' => $data['type_id'],
                        '{TASK_TITLE}' => $data['type_title'],
                        '{TASK_URL}' => $siteUrl . '/' . $data['access_url'],
                        '{COMPANY_TITLE}' => $company_title,
                    ];
                    $templateContent = 'The recurring task #{TASK_ID} - "{TASK_TITLE}" has been executed. You can view the new instance here: {TASK_URL}';
                    break;
                case 'todo_reminder':
                    $contentPlaceholders = [
                        '{TODO_ID}' => $data['type_id'],
                        '{TODO_TITLE}' => $data['type_title'],
                        '{TODO_URL}' => $siteUrl . '/' . $data['access_url'],
                        '{COMPANY_TITLE}' => $company_title,
                    ];
                    $templateContent = 'You have a todo reminder for Todo #{TODO_ID} - "{TODO_TITLE}". You can view the task here: {TODO_URL}.';
                    break;
            }
        } else {
            switch ($data['type']) {
                case 'project':
                    $contentPlaceholders = [
                        '{PROJECT_ID}' => $data['type_id'],
                        '{PROJECT_TITLE}' => $data['type_title'],
                        '{FIRST_NAME}' => $recipient->first_name,
                        '{LAST_NAME}' => $recipient->last_name,
                        '{COMPANY_TITLE}' => $company_title,
                        '{PROJECT_URL}' => $siteUrl . '/' . $data['access_url'],
                        '{SITE_URL}' => $siteUrl
                    ];
                    $templateContent = 'Hello, {FIRST_NAME} {LAST_NAME} You have been assigned a new project {PROJECT_TITLE}, ID:#{PROJECT_ID}.';
                    break;
                case 'project_status_updation':
                    $contentPlaceholders = [
                        '{PROJECT_ID}' => $data['type_id'],
                        '{PROJECT_TITLE}' => $data['type_title'],
                        '{FIRST_NAME}' => $recipient->first_name,
                        '{LAST_NAME}' => $recipient->last_name,
                        '{UPDATER_FIRST_NAME}' => $data['updater_first_name'],
                        '{UPDATER_LAST_NAME}' => $data['updater_last_name'],
                        '{OLD_STATUS}' => $data['old_status'],
                        '{NEW_STATUS}' => $data['new_status'],
                        '{PROJECT_URL}' => $siteUrl . '/' . $data['access_url'],
                        '{SITE_URL}' => $siteUrl,
                        '{COMPANY_TITLE}' => $company_title
                    ];
                    $templateContent = '{UPDATER_FIRST_NAME} {UPDATER_LAST_NAME} has updated the status of project {PROJECT_TITLE}, ID:#{PROJECT_ID}, from {OLD_STATUS} to {NEW_STATUS}.';
                    break;
                case 'project_issue':
                    $contentPlaceholders = [
                        '{ISSUE_ID}' => $data['type_id'],
                        '{ISSUE_TITLE}' => $data['type_title'],
                        '{STATUS}' => $data['status'],
                        '{CREATOR_FIRST_NAME}' => $data['creator_first_name'],
                        '{CREATOR_LAST_NAME}' => $data['creator_last_name'],
                        '{ACCESS_URL}' => $siteUrl . '/' . $data['access_url'],
                        '{FIRST_NAME}' => $recipient->first_name,
                        '{LAST_NAME}' => $recipient->last_name,
                        '{COMPANY_TITLE}' => $company_title
                    ];
                    $templateContent = '{CREATOR_FIRST_NAME} {CREATOR_LAST_NAME} has Assigned you new issue: {ISSUE_TITLE}, ID:#{ISSUE_ID} ,Status : {STATUS}';
                    break;
                case 'task':
                    $contentPlaceholders = [
                        '{TASK_ID}' => $data['type_id'],
                        '{TASK_TITLE}' => $data['type_title'],
                        '{FIRST_NAME}' => $recipient->first_name,
                        '{LAST_NAME}' => $recipient->last_name,
                        '{COMPANY_TITLE}' => $company_title,
                        '{TASK_URL}' => $siteUrl . '/' . $data['access_url'],
                        '{SITE_URL}' => $siteUrl
                    ];
                    $templateContent = 'Hello, {FIRST_NAME} {LAST_NAME} You have been assigned a new task {TASK_TITLE}, ID:#{TASK_ID}.';
                    break;
                case 'task_status_updation':
                    $contentPlaceholders = [
                        '{TASK_ID}' => $data['type_id'],
                        '{TASK_TITLE}' => $data['type_title'],
                        '{FIRST_NAME}' => $recipient->first_name,
                        '{LAST_NAME}' => $recipient->last_name,
                        '{UPDATER_FIRST_NAME}' => $data['updater_first_name'],
                        '{UPDATER_LAST_NAME}' => $data['updater_last_name'],
                        '{OLD_STATUS}' => $data['old_status'],
                        '{NEW_STATUS}' => $data['new_status'],
                        '{TASK_URL}' => $siteUrl . '/' . $data['access_url'],
                        '{SITE_URL}' => $siteUrl,
                        '{COMPANY_TITLE}' => $company_title
                    ];
                    $templateContent = '{UPDATER_FIRST_NAME} {UPDATER_LAST_NAME} has updated the status of task {TASK_TITLE}, ID:#{TASK_ID}, from {OLD_STATUS} to {NEW_STATUS}.';
                    break;
                case 'workspace':
                    $contentPlaceholders = [
                        '{WORKSPACE_ID}' => $data['type_id'],
                        '{WORKSPACE_TITLE}' => $data['type_title'],
                        '{FIRST_NAME}' => $recipient->first_name,
                        '{LAST_NAME}' => $recipient->last_name,
                        '{COMPANY_TITLE}' => $company_title,
                        '{WORKSPACE_URL}' => $siteUrl . '/workspaces',
                        '{SITE_URL}' => $siteUrl
                    ];
                    $templateContent = 'Hello, {FIRST_NAME} {LAST_NAME} You have been added in a new workspace {WORKSPACE_TITLE}, ID:#{WORKSPACE_ID}.';
                    break;
                case 'meeting':
                    $contentPlaceholders = [
                        '{MEETING_ID}' => $data['type_id'],
                        '{MEETING_TITLE}' => $data['type_title'],
                        '{FIRST_NAME}' => $recipient->first_name,
                        '{LAST_NAME}' => $recipient->last_name,
                        '{COMPANY_TITLE}' => $company_title,
                        '{MEETING_URL}' => $siteUrl . '/meetings',
                        '{SITE_URL}' => $siteUrl
                    ];
                    $templateContent = 'Hello, {FIRST_NAME} {LAST_NAME} You have been added in a new meeting {MEETING_TITLE}, ID:#{MEETING_ID}.';
                    break;
                case 'leave_request_creation':
                    $contentPlaceholders = [
                        '{ID}' => $data['type_id'],
                        '{USER_FIRST_NAME}' => $recipient->first_name,
                        '{USER_LAST_NAME}' => $recipient->last_name,
                        '{REQUESTEE_FIRST_NAME}' => $data['team_member_first_name'],
                        '{REQUESTEE_LAST_NAME}' => $data['team_member_last_name'],
                        '{TYPE}' => $data['leave_type'],
                        '{FROM}' => $data['from'],
                        '{TO}' => $data['to'],
                        '{DURATION}' => $data['duration'],
                        '{REASON}' => $data['reason'],
                        '{STATUS}' => $data['status'],
                        '{COMPANY_TITLE}' => $company_title,
                        '{SITE_URL}' => $siteUrl,
                        '{CURRENT_YEAR}' => date('Y')
                    ];
                    $templateContent = 'New Leave Request ID:#{ID} Has Been Created By {REQUESTEE_FIRST_NAME} {REQUESTEE_LAST_NAME}.';
                    break;
                case 'leave_request_status_updation':
                    $contentPlaceholders = [
                        '{ID}' => $data['type_id'],
                        '{USER_FIRST_NAME}' => $recipient->first_name,
                        '{USER_LAST_NAME}' => $recipient->last_name,
                        '{REQUESTEE_FIRST_NAME}' => $data['team_member_first_name'],
                        '{REQUESTEE_LAST_NAME}' => $data['team_member_last_name'],
                        '{TYPE}' => $data['leave_type'],
                        '{FROM}' => $data['from'],
                        '{TO}' => $data['to'],
                        '{DURATION}' => $data['duration'],
                        '{REASON}' => $data['reason'],
                        '{OLD_STATUS}' => $data['old_status'],
                        '{NEW_STATUS}' => $data['new_status'],
                        '{COMPANY_TITLE}' => $company_title,
                        '{SITE_URL}' => $siteUrl,
                        '{CURRENT_YEAR}' => date('Y')
                    ];
                    $templateContent = 'Leave Request ID:#{ID} Status Updated From {OLD_STATUS} To {NEW_STATUS}.';
                    break;
                case 'team_member_on_leave_alert':
                    $contentPlaceholders = [
                        '{ID}' => $data['type_id'],
                        '{USER_FIRST_NAME}' => $recipient->first_name,
                        '{USER_LAST_NAME}' => $recipient->last_name,
                        '{REQUESTEE_FIRST_NAME}' => $data['team_member_first_name'],
                        '{REQUESTEE_LAST_NAME}' => $data['team_member_last_name'],
                        '{TYPE}' => $data['leave_type'],
                        '{FROM}' => $data['from'],
                        '{TO}' => $data['to'],
                        '{DURATION}' => $data['duration'],
                        '{COMPANY_TITLE}' => $company_title,
                        '{SITE_URL}' => $siteUrl,
                        '{CURRENT_YEAR}' => date('Y')
                    ];
                    $templateContent = '{REQUESTEE_FIRST_NAME} {REQUESTEE_LAST_NAME} will be on {TYPE} leave from {FROM} to {TO}.';
                    break;
                case 'announcement':
                    $contentPlaceholders = [
                        '{ANNOUNCEMENT_ID}' => $data['type_id'],
                        '{ANNOUNCEMENT_TITLE}' => $data['type_title'],
                        '{CREATOR_FIRST_NAME}' => $data['creator_first_name'],
                        '{CREATOR_LAST_NAME}' => $data['creator_last_name'],
                        '{COMPANY_TITLE}' => $company_title,
                        '{ACCESS_URL}' => $siteUrl . '/' . $data['access_url'],
                        '{CURRENT_YEAR}' => date('Y'),
                    ];
                    $templateContent = '{CREATOR_FIRST_NAME} {CREATOR_LAST_NAME} has made a new announcement titled "{ANNOUNCEMENT_TITLE}". Shared by {COMPANY_TITLE} ({CURRENT_YEAR}).';
                    break;
                case 'task_reminder':
                    $contentPlaceholders = [
                        '{TASK_ID}' => $data['type_id'],
                        '{TASK_TITLE}' => $data['type_title'],
                        '{TASK_URL}' => $siteUrl . '/' . $data['access_url'],
                        '{COMPANY_TITLE}' => $company_title,
                        '{SITE_URL}' => $siteUrl
                    ];
                    $templateContent = 'You have a task reminder for Task #{TASK_ID} - {TASK_TITLE}. You can view the task here: {TASK_URL}';
                    break;
                case 'recurring_task':
                    $contentPlaceholders = [
                        '{TASK_ID}' => $data['type_id'],
                        '{TASK_TITLE}' => $data['type_title'],
                        '{TASK_URL}' => $siteUrl . '/' . $data['access_url'],
                        '{COMPANY_TITLE}' => $company_title,
                    ];
                    $templateContent = 'The recurring task #{TASK_ID} - "{TASK_TITLE}" has been executed. You can view the new instance here: {TASK_URL}';
                    break;
                case 'todo_reminder':
                    $contentPlaceholders = [
                        '{TODO_ID}' => $data['type_id'],
                        '{TODO_TITLE}' => $data['type_title'],
                        '{TODO_URL}' => $siteUrl . '/' . $data['access_url'],
                        '{COMPANY_TITLE}' => $company_title,
                    ];
                    $templateContent = 'You have a todo reminder for Todo #{TODO_ID} - "{TODO_TITLE}". You can view the task here: {TODO_URL}.';
                    break;
            }
        }
        if (filled(Arr::get($fetched_data, 'content'))) {
            $templateContent = $fetched_data->content;
        }
        // Replace placeholders with actual values
        $content = str_replace(array_keys($contentPlaceholders), array_values($contentPlaceholders), $templateContent);
        return $content;
    }
}

if (!function_exists('getTitle')) {
    /**
     * Generates a title for notifications based on the data type and context.
     *
     * This function retrieves the authenticated user and company title and uses
     * them along with the provided data to generate a notification title. It
     * supports various types, including project, task, workspace, meeting,
     * leave requests, status updates, and announcements, customizing the subject
     * with relevant placeholders for each type.
     *
     * @param array $data An associative array containing data for generating the title.
     *                    Expected keys include 'type', 'type_id', 'type_title', and
     *                    other context-specific keys such as 'status', 'old_status',
     *                    'new_status', 'team_member_first_name', 'team_member_last_name',
     *                    'updater_first_name', 'updater_last_name', 'creator_first_name',
     *                    'creator_last_name', and 'access_url'.
     *
     * @return string The generated title with placeholders replaced by actual values.
     */

    function getTitle($data)
    {
        static $authUser = null;
        static $companyTitle = null;
        if ($authUser === null) {
            $authUser = getAuthenticatedUser();
        }
        if ($companyTitle === null) {
            $general_settings = get_settings('general_settings');
            $companyTitle = $general_settings['company_title'] ?? 'Taskify';
        }
        $fetched_data = Template::where('type', 'system')
            ->where('name', $data['type'] . '_assignment')
            ->first();
        if (!$fetched_data) {
            $fetched_data = Template::where('type', 'system')
                ->where('name', $data['type'])
                ->first();
        }
        $subject = 'Default Subject'; // Set a default subject
        $subjectPlaceholders = [];
        // Customize subject based on type
        switch ($data['type']) {
            case 'project':
                $subjectPlaceholders = [
                    '{PROJECT_ID}' => $data['type_id'],
                    '{PROJECT_TITLE}' => $data['type_title'],
                    '{ASSIGNEE_FIRST_NAME}' => $authUser->first_name,
                    '{ASSIGNEE_LAST_NAME}' => $authUser->last_name,
                    '{COMPANY_TITLE}' => $companyTitle
                ];
                break;
            case 'task':
                $subjectPlaceholders = [
                    '{TASK_ID}' => $data['type_id'],
                    '{TASK_TITLE}' => $data['type_title'],
                    '{ASSIGNEE_FIRST_NAME}' => $authUser->first_name,
                    '{ASSIGNEE_LAST_NAME}' => $authUser->last_name,
                    '{COMPANY_TITLE}' => $companyTitle
                ];
                break;
            case 'workspace':
                $subjectPlaceholders = [
                    '{WORKSPACE_ID}' => $data['type_id'],
                    '{WORKSPACE_TITLE}' => $data['type_title'],
                    '{ASSIGNEE_FIRST_NAME}' => $authUser->first_name,
                    '{ASSIGNEE_LAST_NAME}' => $authUser->last_name,
                    '{COMPANY_TITLE}' => $companyTitle
                ];
                break;
            case 'meeting':
                $subjectPlaceholders = [
                    '{MEETING_ID}' => $data['type_id'],
                    '{MEETING_TITLE}' => $data['type_title'],
                    '{ASSIGNEE_FIRST_NAME}' => $authUser->first_name,
                    '{ASSIGNEE_LAST_NAME}' => $authUser->last_name,
                    '{COMPANY_TITLE}' => $companyTitle
                ];
                break;
            case 'leave_request_creation':
                $subjectPlaceholders = [
                    '{ID}' => $data['type_id'],
                    '{STATUS}' => $data['status'],
                    '{REQUESTEE_FIRST_NAME}' => $data['team_member_first_name'],
                    '{REQUESTEE_LAST_NAME}' => $data['team_member_last_name'],
                    '{COMPANY_TITLE}' => $companyTitle
                ];
                break;
            case 'leave_request_status_updation':
                $subjectPlaceholders = [
                    '{ID}' => $data['type_id'],
                    '{OLD_STATUS}' => $data['old_status'],
                    '{NEW_STATUS}' => $data['new_status'],
                    '{COMPANY_TITLE}' => $companyTitle
                ];
                break;
            case 'team_member_on_leave_alert':
                $subjectPlaceholders = [
                    '{ID}' => $data['type_id'],
                    '{REQUESTEE_FIRST_NAME}' => $data['team_member_first_name'],
                    '{REQUESTEE_LAST_NAME}' => $data['team_member_last_name'],
                    '{COMPANY_TITLE}' => $companyTitle
                ];
                break;
            case 'project_status_updation':
                $subjectPlaceholders = [
                    '{PROJECT_ID}' => $data['type_id'],
                    '{PROJECT_TITLE}' => $data['type_title'],
                    '{UPDATER_FIRST_NAME}' => $data['updater_first_name'],
                    '{UPDATER_LAST_NAME}' => $data['updater_last_name'],
                    '{OLD_STATUS}' => $data['old_status'],
                    '{NEW_STATUS}' => $data['new_status'],
                    '{COMPANY_TITLE}' => $companyTitle
                ];
                break;
            case 'project_issue':
                $subjectPlaceholders = [
                    '{ISSUE_ID}' => $data['type_id'],
                    '{ISSUE_TITLE}' => $data['type_title'],
                    '{STATUS}' => $data['status'],
                    '{CREATOR_FIRST_NAME}' => $data['creator_first_name'],
                    '{CREATOR_LAST_NAME}' => $data['creator_last_name'],
                    '{ACCESS_URL}' => $data['access_url'],

                    '{COMPANY_TITLE}' => $companyTitle
                ];
                break;
            case 'task_status_updation':
                $subjectPlaceholders = [
                    '{TASK_ID}' => $data['type_id'],
                    '{TASK_TITLE}' => $data['type_title'],
                    '{UPDATER_FIRST_NAME}' => $data['updater_first_name'],
                    '{UPDATER_LAST_NAME}' => $data['updater_last_name'],
                    '{OLD_STATUS}' => $data['old_status'],
                    '{NEW_STATUS}' => $data['new_status'],
                    '{COMPANY_TITLE}' => $companyTitle
                ];
                break;
            case 'announcement':
                $subjectPlaceholders = [
                    '{ANNOUNCEMENT_ID}' => $data['type_id'],
                    '{ANNOUNCEMENT_TITLE}' => $data['type_title'],
                    '{CREATOR_FIRST_NAME}' => $data['creator_first_name'],
                    '{CREATOR_LAST_NAME}' => $data['creator_last_name'],
                    '{CURRENT_YEAR}' => date('Y'),
                ];
                break;
            case 'task_reminder':
            case 'recurring_task':
                $subjectPlaceholders = [
                    '{TASK_TITLE}' => $data['type_title'],
                    '{TASK_ID}' => $data['type_id'],
                    '{COMPANY_TITLE}' => $companyTitle,
                    '{CURRENT_YEAR}' => date('Y'),
                ];
                break;
        }
        if (filled(Arr::get($fetched_data, 'subject'))) {
            $subject = $fetched_data->subject;
        } else {
            if ($data['type'] == 'leave_request_creation') {
                $subject = 'Leave Requested';
            } elseif ($data['type'] == 'leave_request_status_updation') {
                $subject = 'Leave Request Status Updated';
            } elseif ($data['type'] == 'team_member_on_leave_alert') {
                $subject = 'Team Member on Leave Alert';
            } elseif ($data['type'] == 'project_status_updation') {
                $subject = 'Project Status Updated';
            } elseif ($data['type'] == 'task_status_updation') {
                $subject = 'Task Status Updated';
            } else {
                $subject = 'New ' . ucfirst($data['type']) . ' Assigned';
            }
        }
        $subject = str_replace(array_keys($subjectPlaceholders), array_values($subjectPlaceholders), $subject);
        return $subject;
    }
}
if (!function_exists('hasPrimaryWorkspace')) {
    /**
     * Checks if there is a primary workspace and returns its ID.
     *
     * @return int The ID of the primary workspace if it exists, otherwise 0.
     */

    function hasPrimaryWorkspace()
    {
        $primaryWorkspace = \App\Models\Workspace::where('is_primary', 1)->first();
        return $primaryWorkspace ? $primaryWorkspace->id : 0;
    }
}

if (!function_exists('replaceUserMentionsWithLinks')) {
    /**
     * Replace plain @mentions in the given content with HTML links to the user's profile.
     *
     * @param string $content
     * @return array [$modifiedContent, $mentionedUserIds]
     */
    function replaceUserMentionsWithLinks($content)
    {
        // Find all @mentions in the content
        preg_match_all('/@([A-Za-z]+\s[A-Za-z]+)/', $content, $matches);
        // Initialize modified content
        $modifiedContent = $content;
        $mentionedUserIds = [];
        // Check if any matches were found
        if (!empty($matches[1])) {
            foreach ($matches[1] as $fullName) {
                // Try to find the user by their full name (first_name + last_name)
                $user = User::where(DB::raw("CONCAT(first_name, ' ', last_name)"), '=', $fullName)->first();
                if ($user) {
                    // Add user ID to the list of mentioned user IDs
                    $mentionedUserIds[] = $user->id;
                    // Create a profile link for the mentioned user
                    $mentionLink = '<a target="blank" href="' . route('users.show', ['id' => $user->id]) . '">@' . $fullName . '</a>';
                    // Replace the plain @mention with the linked version
                    $modifiedContent = str_replace(
                        '@' . $fullName,
                        $mentionLink,
                        $modifiedContent
                    );
                }
            }
        }
        return [$modifiedContent, $mentionedUserIds];
    }
}
if (!function_exists('sendMentionNotification')) {
    /**
     * Send a notification to all users who were mentioned in a comment.
     *
     * @param \App\Models\Comment $comment The comment that was posted.
     * @param int[] $mentionedUserIds The IDs of the users who were mentioned.
     * @param int $workspaceId The ID of the workspace where the comment was posted.
     * @param int $currentUserId The ID of the user who posted the comment.
     *
     * @return void
     */
    function
    sendMentionNotification(
        $comment,
        $mentionedUserIds,
        $workspaceId,
        $currentUserId
    ) {
        // dd($mentionedUserIds);
        $mentionedUserIds = array_unique($mentionedUserIds);
        $moduleType = '';
        $url = '';
        switch ($comment->commentable_type) {
            case 'App\Models\Task':
                $moduleType = 'task';
                $url = route('tasks.info', ['id' => $comment->commentable_id]);
                break;
            case 'App\Models\Project':
                $moduleType = 'project';
                $url = route('projects.info', ['id' => $comment->commentable_id]);
                break;
            default:
                $moduleType = '';
                break;
        }
        $module = [];
        if ($moduleType) {
            switch ($moduleType) {
                case 'task':
                    $module = Task::find($comment->commentable_id);
                    break;
                case 'project':
                    $module = Project::find($comment->commentable_id);
                    break;
                default:
                    break;
            }
        }
        foreach ($mentionedUserIds as $userId) {
            $notification = Notification::create([
                'workspace_id' => $workspaceId,
                'from_id' => 'u_' . $currentUserId,
                'type' => $moduleType . '_comment_mention',
                'type_id' => $module->id,
                'action' => 'mentioned',
                'title' => 'You were mentioned in a comment',
                'message' => 'You were mentioned in a comment by ' . getAuthenticatedUser()->first_name . ' ' . getAuthenticatedUser()->last_name . ' in ' . ucfirst($moduleType) . ' #' . $module->title . '. Click <a href="' . $url . '">here</a> to view the comment.',
            ]);
            $notification->users()->attach($userId);
        }
    }
}
if (!function_exists('getDefaultViewRoute')) {
    /**
     * Get the default view route for a given entity (projects or tasks).
     *
     * @param string $entity
     * @return string
     */
    if (!function_exists('getDefaultViewRoute')) {
        /**
         * Get the default view route for a given entity (projects or tasks).
         *
         * @param string $entity
         * @return string
         */
        function getDefaultViewRoute($entity)
        {
            $defaultView = getUserPreferences($entity, 'default_view');
            // dd($entity);
            $routes = [
                'projects' => [
                    'list' => 'projects.list_view',
                    'grid' => 'projects.index',
                    'kanban_view' => 'projects.kanban_view',
                    'calendar-view' => 'projects.calendar_view'
                ],
                'tasks' => [
                    'tasks/draggable' => 'tasks.draggable',
                    'tasks/calendar-view' => 'tasks.calendar_view',
                    'tasks/group-by-task-list' => 'tasks.groupByTaskList',
                    'default' => 'tasks.index',
                ],
                'leave-requests' => [
                    'list' => 'leave_requests.index',
                    'calendar-view' => 'leave_requests.calendar_view',
                    'default' => 'leave_requests.index',
                ],
                'meetings' => [
                    'list' => 'meetings.index',
                    'calendar-view' => 'meetings.calendar_view',
                    'default' => 'meetings.index',
                ],
                'activity-log' => [
                    'list' => 'activity_log.index',
                    'calendar-view' => 'activity_log.calendar_view',
                    'default' => 'activity_log.index'
                ],
                'leads' => [
                    'list' => 'leads.index',
                    'kanban' => 'leads.kanban_view',
                    'default' => 'leads.index'
                ],
                'candidates' => [
                    'list' => 'candidate.index',
                    'kanban' => 'candidate.kanban_view',
                    'default' => 'candidate.index'
                ]

            ];
            return route($routes[$entity][$defaultView] ?? $routes[$entity]['default'] ?? 'projects.index');
        }
    }


    // Function for sending reminders for tasks or birthday or work anniversary
    if (!function_exists('sendReminderNotification')) {


        /**
         * Sends reminder notifications to the given recipients based on the given data.
         *
         * @param array $data The reminder data, must contain the type of reminder.
         * @param array $recipients The recipients of the notification, must contain the user or client IDs.
         * @return void
         */
        function sendReminderNotification($data, $recipients)
        {
            Log::info('Sending reminder notification to: ' . json_encode($recipients, JSON_PRETTY_PRINT) . 'With data: ' . json_encode($data, JSON_PRETTY_PRINT));
            if (empty($recipients)) {
                return;
            }

            // Define notification types
            $notificationTypes = ['task_reminder', 'project_reminder', 'leave_request_reminder', 'recurring_task', 'todo_reminder'];
            Log::debug('Checking notification type', ['type' => $data['type'], 'valid_types' => $notificationTypes]);
            // Get notification template based on the type
            $template = getNotificationTemplate($data['type'], 'system');
            if (!$template || $template->status !== 0) {
                $notification = createNotification($data);
            }

            // Process each recipient
            foreach (array_unique($recipients) as $recipient) {
                Log::info('Processing recipient', ['recipient_id' => $recipient]);
                $recipientModel = getRecipientModel($recipient);
                if ($recipientModel) {
                    Log::debug('Found recipient model', [
                        'recipient_type' => get_class($recipientModel),
                        'recipient_id' => $recipientModel->id
                    ]);
                    handleRecipientNotification($recipientModel, $notification, $template, $data, $notificationTypes);
                }
            }
        }

        /**
         * Creates a new notification from the given data.
         *
         * @param array $data An associative array containing the notification details,
         *                    including the 'type', 'type_id', and 'action'.
         * @return \App\Models\Notification The newly created notification instance.
         */
        function createNotification($data)
        {
            return Notification::create(
                [
                    'workspace_id' => $data['workspace_id'],
                    'from_id' => $data['from_id'],
                    'type' => $data['type'],
                    'type_id' => $data['type_id'],
                    'action' => $data['action'],
                    'title' => getTitle($data),
                    'message' => get_message($data, null, 'system'),
                ]
            );
        }

        /**
         * Given a recipient identifier, returns the corresponding model instance.
         *
         * A recipient identifier is a string that starts with either 'u_' for a user or
         * 'c_' for a client, followed by the numeric identifier of the user or client.
         * For example, 'u_1' refers to a user with identifier 1, and 'c_2' refers to a
         * client with identifier 2.
         *
         * @param string $recipient The recipient identifier.
         * @return \App\Models\User|\App\Models\Client|null The recipient model instance, or null if not found.
         */
        function getRecipientModel($recipient)
        {
            $recipientId = substr($recipient, 2);
            if (substr($recipient, 0, 2) === 'u_') {
                return User::find($recipientId);
            } elseif (substr($recipient, 0, 2) === 'c_') {
                return Client::find($recipientId);
            }
            return null;
        }

        /**
         * Handles a notification for a recipient based on their notification preferences.
         *
         * This function takes a recipient model, a notification, a template, data about the
         * notification, and an array of notification types. It checks the recipient's
         * preferences for the notification types and sends notifications accordingly.
         * If the notification is already attached to the recipient, it will not be attached again.
         *
         * @param mixed $recipientModel The recipient model to send the notification to.
         * @param mixed $notification The notification to be sent.
         * @param mixed $template The template to use for the notification.
         * @param array $data An associative array containing details about the notification.
         * @param array $notificationTypes An array of notification types to check for.
         */
        function handleRecipientNotification($recipientModel, $notification, $template, $data, $notificationTypes)
        {
            Log::info('Handling recipient notification', [
                'recipient_id' => $recipientModel->id,
                'notification_type' => $data['type']
            ]);
            $enabledNotifications = getUserPreferences('notification_preference', 'enabled_notifications', 'u_' . $recipientModel->id);

            // Attach the notification to the recipient
            attachNotificationIfNeeded($recipientModel, $notification, $template, $enabledNotifications, $data);
            Log::info('Starting notification delivery process', [
                'recipient_id' => $recipientModel->id,
                'notification_types' => $notificationTypes,
                'enabled_notifications' => $enabledNotifications
            ]);
            // Send notifications based on preferences
            sendEmailIfEnabled($recipientModel, $enabledNotifications, $data, $notificationTypes);
            sendSMSIfEnabled($recipientModel, $enabledNotifications, $data, $notificationTypes);
            sendWhatsAppIfEnabled($recipientModel, $enabledNotifications, $data, $notificationTypes);
            sendSlackIfEnabled($recipientModel, $enabledNotifications, $data, $notificationTypes);
        }

        /**
         * Attach a notification to the recipient if the recipient has enabled system notifications for the given type
         * of notification and the notification template is not found or is not enabled.
         *
         * @param mixed $recipientModel The recipient model (User or Client) to which the notification should be attached.
         * @param Notification $notification The notification to be attached to the recipient.
         * @param Template $template The notification template to be checked for enabled status.
         * @param array $enabledNotifications An array of enabled notification types for the recipient.
         * @param array $data The data for the notification, including the type of notification.
         */
        function attachNotificationIfNeeded($recipientModel, $notification, $template, $enabledNotifications, $data)
        {
            Log::debug('Checking if notification needs to be attached', [
                'recipient_id' => $recipientModel->id,
                'notification_id' => $notification ? $notification->id : null,
                'template_exists' => (bool) $template,
                'template_status' => $template ? $template->status : null
            ]);
            if (!$template || $template->status !== 0) {
                if (is_array($enabledNotifications) && (empty($enabledNotifications) || in_array('system_' . $data['type'], $enabledNotifications))) {
                    $recipientModel->notifications()->attach($notification->id);
                }
            }
        }

        /**
         * Send an email notification if the recipient has enabled email notifications for the given type of notification.
         *
         * @param mixed $recipientModel The recipient model (User or Client) to which the notification should be sent.
         * @param array $enabledNotifications An array of enabled notification types for the recipient.
         * @param array $data The notification data.
         * @param array $notificationTypes An array of notification types for which email notifications should be sent.
         * @return void
         */
        function sendEmailIfEnabled($recipientModel, $enabledNotifications, $data, $notificationTypes)
        {

            Log::debug('Checking email notification preferences', [
                'recipient_id' => $recipientModel->id,
                'notification_type' => $data['type'],
                'is_type_valid' => in_array($data['type'], $notificationTypes),
                'is_enabled' => isNotificationEnabled($enabledNotifications, 'email_' . $data['type'])
            ]);
            if (in_array($data['type'], $notificationTypes) && isNotificationEnabled($enabledNotifications, 'email_' . $data['type'])) {
                try {
                    sendEmailNotification($recipientModel, $data);
                } catch (\Exception $e) {
                    Log::error('Email Notification Error: ' . $e->getMessage());
                }
            }
        }

        /**
         * Send SMS notification if enabled.
         *
         * This function sends an SMS notification to the given recipient if the
         * notification type is enabled in the recipient's preferences.
         *
         * @param  \App\Models\User|\App\Models\Client  $recipientModel
         * @param  array  $enabledNotifications
         * @param  array  $data
         * @param  array  $notificationTypes
         * @return void
         */
        function sendSMSIfEnabled($recipientModel, $enabledNotifications, $data, $notificationTypes)
        {
            Log::debug('Checking SMS notification preferences', [
                'recipient_id' => $recipientModel->id,
                'notification_type' => $data['type'],
                'is_type_valid' => in_array($data['type'], $notificationTypes),
                'is_enabled' => isNotificationEnabled($enabledNotifications, 'sms_' . $data['type'])
            ]);
            if (in_array($data['type'], $notificationTypes) && isNotificationEnabled($enabledNotifications, 'sms_' . $data['type'])) {
                try {
                    sendSMSNotification($data, $recipientModel);
                } catch (\Exception $e) {
                    Log::error('SMS Notification Error: ' . $e->getMessage());
                }
            }
        }

        /**
         * Send WhatsApp notification if enabled.
         *
         * This function sends a WhatsApp notification to the given recipient if the
         * notification type is enabled in the recipient's preferences.
         *
         * @param  \App\Models\User|\App\Models\Client  $recipientModel
         * @param  array  $enabledNotifications
         * @param  array  $data
         * @param  array  $notificationTypes
         * @return void
         */
        function sendWhatsAppIfEnabled($recipientModel, $enabledNotifications, $data, $notificationTypes)
        {
            Log::debug('Checking WhatsApp notification preferences', [
                'recipient_id' => $recipientModel->id,
                'notification_type' => $data['type'],
                'is_type_valid' => in_array($data['type'], $notificationTypes),
                'is_enabled' => isNotificationEnabled($enabledNotifications, 'whatsapp_' . $data['type'])
            ]);
            if (in_array($data['type'], $notificationTypes) && isNotificationEnabled($enabledNotifications, 'whatsapp_' . $data['type'])) {
                try {
                    sendWhatsAppNotification($data, $recipientModel);
                } catch (\Exception $e) {
                    Log::error('WhatsApp Notification Error: ' . $e->getMessage());
                }
            }
        }

        /**
         * Send a Slack notification if the recipient has enabled Slack notifications for the given type.
         *
         * @param User|Client $recipientModel The recipient model to send the notification to.
         * @param array $enabledNotifications An array of enabled notification types.
         * @param array $data An associative array containing the notification details,
         *                    including the 'type', 'type_id', and 'action'.
         * @param array $notificationTypes An array of notification types.
         */
        function sendSlackIfEnabled($recipientModel, $enabledNotifications, $data, $notificationTypes)
        {
            Log::debug('Checking Slack notification preferences', [
                'recipient_id' => $recipientModel->id,
                'notification_type' => $data['type'],
                'is_type_valid' => in_array($data['type'], $notificationTypes),
                'is_enabled' => isNotificationEnabled($enabledNotifications, 'slack_' . $data['type'])
            ]);
            if (in_array($data['type'], $notificationTypes) && isNotificationEnabled($enabledNotifications, 'slack_' . $data['type'])) {
                try {
                    sendSlackNotification($data, $recipientModel);
                } catch (\Exception $e) {
                    Log::error('Slack Notification Error: ' . $e->getMessage());
                }
            }
        }

        /**
         * Check if a notification type is enabled for a user/client.
         *
         * @param array $enabledNotifications An array of enabled notification types.
         * @param string $type The notification type to check.
         * @return bool True if the notification type is enabled.
         */
        function isNotificationEnabled($enabledNotifications, $type)
        {

            return is_array($enabledNotifications) && (empty($enabledNotifications) || in_array($type, $enabledNotifications));
        }
    }
    if (!function_exists('getDefaultStatus')) {
        /**
         * Get the default status ID based on the given status name.
         *
         * @param string $statusName
         * @return object|null
         */
        function getDefaultStatus(string $statusName): ?object
        {
            // Fetch the default status using the Statuses model
            $status = Status::where('title', $statusName)
                ->where('is_default', 1) // Assuming there's an 'is_default' column
                ->first();

            // Return the ID if found, or null
            return $status ? $status : null;
        }
    }
    if (!function_exists('getWorkspaceId')) {
        function getWorkspaceId()
        {
            $workspaceId = 0;
            $authenticatedUser = getAuthenticatedUser();

            if ($authenticatedUser) {
                if (session()->has('workspace_id')) {
                    $workspaceId = session('workspace_id'); // Retrieve workspace_id from session
                } else {
                    $workspaceId = request()->header('workspace_id');
                }
            }
            return $workspaceId;
            // dd($workspaceId);
        }
    }
    if (!function_exists('getGuardName')) {
        function getGuardName()
        {
            static $guardName = null;

            // If the guard name is already determined, return it
            if ($guardName !== null) {
                return $guardName;
            }

            // Check the 'web' guard (users)
            if (Auth::guard('web')->check()) {
                $guardName = 'web';
            }
            // Check the 'client' guard (clients)
            elseif (Auth::guard('client')->check()) {
                $guardName = 'client';
            }
            // Check the 'sanctum' guard (API tokens)
            elseif (Auth::guard('sanctum')->check()) {
                $user = Auth::guard('sanctum')->user();

                // Determine if the sanctum user is a user or a client
                if ($user instanceof \App\Models\User) {
                    $guardName = 'web';
                } elseif ($user instanceof \App\Models\Client) {
                    $guardName = 'client';
                }
            }

            return $guardName;
        }
    }
    if (!function_exists('duplicateRecord')) {
        function duplicateRecord($model, $id, $relatedTables = [], $title = '')
        {
            $eagerLoadRelations = $relatedTables;
            $eagerLoadRelations = array_filter($eagerLoadRelations, function ($table) {
                return $table !== 'project_tasks'; // Exclude from eager loading
            });

            // Eager load the related tables excluding 'project_tasks'
            $originalRecord = $model::with($eagerLoadRelations)->find($id);
            if (!$originalRecord) {
                return false; // Record not found
            }
            // Start a new database transaction to ensure data consistency
            DB::beginTransaction();

            try {
                // Duplicate the original record
                $duplicateRecord = $originalRecord->replicate();
                // Set the title if provided
                if (!empty($title)) {
                    $duplicateRecord->title = $title;
                }
                $duplicateRecord->save();

                foreach ($relatedTables as $relatedTable) {
                    if ($relatedTable === 'projects') {
                        foreach ($originalRecord->$relatedTable as $project) {
                            // Duplicate the project
                            $duplicateProject = $project->replicate();
                            $duplicateProject->workspace_id = $duplicateRecord->id; // Set the new workspace ID
                            $duplicateProject->save();
                            // Attach project users
                            foreach ($project->users as $user) {
                                $duplicateProject->users()->attach($user->id);
                            }

                            // Attach project clients
                            foreach ($project->clients as $client) {
                                $duplicateProject->clients()->attach($client->id);
                            }
                            // Duplicate the project's tasks
                            if (in_array('project_tasks', $relatedTables)) {
                                foreach ($project->tasks as $task) {
                                    $duplicateTask = $task->replicate();
                                    $duplicateTask->workspace_id = $duplicateRecord->id;
                                    $duplicateTask->project_id = $duplicateProject->id; // Set the new project ID
                                    $duplicateTask->save();


                                    // Duplicate task's users (if applicable)
                                    foreach ($task->users as $user) {
                                        $duplicateTask->users()->attach($user->id);
                                    }
                                }
                            }
                        }
                    }
                    if ($relatedTable === 'tasks') {
                        // Handle 'tasks' relationship separately
                        foreach ($originalRecord->$relatedTable as $task) {
                            // Duplicate the related task
                            $duplicateTask = $task->replicate();
                            $duplicateTask->project_id = $duplicateRecord->id;
                            $duplicateTask->save();
                            foreach ($task->users as $user) {
                                // Attach the duplicated user to the duplicated task
                                $duplicateTask->users()->attach($user->id);
                            }
                        }
                    }
                    if ($relatedTable === 'meetings') {
                        foreach ($originalRecord->$relatedTable as $meeting) {
                            $duplicateMeeting = $meeting->replicate();
                            $duplicateMeeting->workspace_id = $duplicateRecord->id; // Set the new workspace ID
                            $duplicateMeeting->save();

                            // Duplicate meeting's users
                            foreach ($meeting->users as $user) {
                                $duplicateMeeting->users()->attach($user->id);
                            }

                            // Duplicate meeting's clients
                            foreach ($meeting->clients as $client) {
                                $duplicateMeeting->clients()->attach($client->id);
                            }
                        }
                    }
                    if ($relatedTable === 'todos') {
                        // Duplicate todos
                        foreach ($originalRecord->$relatedTable as $todo) {
                            $duplicateTodo = $todo->replicate();
                            $duplicateTodo->workspace_id = $duplicateRecord->id; // Set the new workspace ID

                            $duplicateTodo->creator_type = $todo->creator_type; // Keep original creator type
                            $duplicateTodo->creator_id = $todo->creator_id;     // Keep original creator ID

                            $duplicateTodo->save();
                        }
                    }
                    if ($relatedTable === 'notes') {
                        foreach ($originalRecord->$relatedTable as $note) {
                            $duplicateNote = $note->replicate();
                            $duplicateNote->workspace_id = $duplicateRecord->id; // Set the new workspace ID
                            $duplicateNote->creator_id = $note->creator_id;      // Retain the creator_id
                            $duplicateNote->save();
                        }
                    }
                }
                // Handle many-to-many relationships separately
                if (in_array('users', $relatedTables)) {
                    $originalRecord->users()->each(function ($user) use ($duplicateRecord) {
                        $duplicateRecord->users()->attach($user->id);
                    });
                }

                if (in_array('clients', $relatedTables)) {
                    $originalRecord->clients()->each(function ($client) use ($duplicateRecord) {
                        $duplicateRecord->clients()->attach($client->id);
                    });
                }

                if (in_array('tags', $relatedTables)) {
                    $originalRecord->tags()->each(function ($tag) use ($duplicateRecord) {
                        $duplicateRecord->tags()->attach($tag->id);
                    });
                }

                // Commit the transaction
                DB::commit();

                return $duplicateRecord;
            } catch (\Exception $e) {
                // Handle any exceptions and rollback the transaction on failure
                DB::rollback();
                return false;
            }
        }
    }

    if (!function_exists('getMenus')) {
        function getMenus()
        {
            $user = getAuthenticatedUser();
            $current_workspace_id = getWorkspaceId();
            $messenger = new ChatifyMessenger();
            $unread = $messenger->totalUnseenMessages();
            $pending_todos_count = $user->todos(0)->count();
            $ongoing_meetings_count = $user->meetings('ongoing')->count();
            $query = LeaveRequest::where('status', 'pending')
                ->where('workspace_id', $current_workspace_id);
            if (!is_admin_or_leave_editor()) {
                $query->where('user_id', $user->id);
            }
            $pendingLeaveRequestsCount = $query->count();
            return [
                [
                    'id' => 'dashboard',
                    'label' => get_label('dashboard', 'Dashboard'),
                    'url' => url('/master-panel/home'),
                    'icon' => 'bx bx-home-circle',
                    'class' => 'menu-item' . (Request::is('master-panel/home') ? ' active' : ''),
                    'category' => get_label('dashboard', 'Dashboard'),
                ],
                [
                    'id' => 'projects',
                    'label' => get_label('projects', 'Projects'),
                    'url' => route('projects.index'),
                    'icon' => 'bx bx-briefcase-alt-2',
                    'class' => 'menu-item' . (Request::is('master-panel/projects') || Request::is('master-panel/tags/*') || Request::is('master-panel/projects/*') || Request::is('master-panel/task-lists') ? ' active open' : ''),
                    'show' => ($user->can('manage_projects') || $user->can('manage_tags')) ? 1 : 0,
                    'category' => get_label('projects_and_tasks_management', 'Project and task management'),
                    'submenus' => [
                        [
                            'id' => 'manage_projects',
                            'label' => get_label('manage_projects', 'Manage projects'),
                            'url' => getDefaultViewRoute('projects'),
                            'class' => 'menu-item' . (Request::is('master-panel/projects') || (Request::is('master-panel/projects/*') && !Request::is('master-panel/projects/*/favorite') && !Request::is('master-panel/projects/favorite')) ? ' active' : ''),
                            'show' => ($user->can('manage_projects')) ? 1 : 0

                        ],
                        [
                            'id' => 'favorite_projects',
                            'label' => get_label('favorite_projects', 'Favorite projects'),
                            'url' => route('projects.index', ['type' => 'favorite']),
                            'class' => 'menu-item' . (Request::is('master-panel/projects/favorite') || Request::is('master-panel/projects/list/favorite') || Request::is('master-panel/projects/kanban/favorite') ? ' active' : ''),
                            'show' => ($user->can('manage_projects')) ? 1 : 0
                        ],

                        [
                            'id' => 'tags',
                            'label' => get_label('tags', 'Tags'),
                            'url' => route('tags.index'),
                            'class' => 'menu-item' . (Request::is('master-panel/tags/*') ? ' active' : ''),
                            'show' => ($user->can('manage_tags')) ? 1 : 0
                        ],
                        [
                            'id' => 'task_lists',
                            'label' => get_label('task_lists', 'Task Lists'),
                            'url' => route('task_lists.index'),
                            'class' => 'menu-item' . (Request::is('master-panel/task-lists') || Request::is('master-panel/task-lists/*') ? ' active' : ''),
                            'show' => ($user->can('manage_tasks')) ? 1 : 0
                        ]
                    ],
                ],
                [
                    'id' => 'tasks',
                    'label' => get_label('tasks', 'Tasks'),
                    'url' => getDefaultViewRoute('tasks'),
                    'icon' => 'bx bx-task',
                    'class' => 'menu-item' . (Request::is('master-panel/tasks') || Request::is('master-panel/tasks/*') ? ' active' : ''),
                    'show' => $user->can('manage_tasks') ? 1 : 0,
                    'category' => get_label('projects_and_tasks_management', 'Project and task management')

                ],
                [
                    'id' => 'statuses',
                    'label' => get_label('statuses', 'Statuses'),
                    'url' => route('status.index'),
                    'icon' => 'bx bx-grid-small',
                    'class' => 'menu-item' . (Request::is('master-panel/status/manage') ? ' active' : ''),
                    'show' => $user->can('manage_statuses') ? 1 : 0,
                    'category' => get_label('projects_and_tasks_management', 'Project and task management')
                ],
                [
                    'id' => 'priorities',
                    'label' => get_label('priorities', 'Priorities'),
                    'url' => route('priority.manage'),
                    'icon' => 'bx bx-up-arrow-alt ',
                    'class' => 'menu-item' . (Request::is('master-panel/priority/manage') ? ' active' : ''),
                    'show' => $user->can('manage_priorities') ? 1 : 0,
                    'category' => get_label('projects_and_tasks_management', 'Project and task management')
                ],
                [
                    'id' => 'workspaces',
                    'label' => get_label('workspaces', 'Workspaces'),
                    'url' => route('workspaces.index'),
                    'icon' => 'bx bx-check-square',
                    'class' => 'menu-item' . (Request::is('master-panel/workspaces') || Request::is('master-panel/workspaces/*') ? ' active' : ''),
                    'show' => $user->can('manage_workspaces') ? 1 : 0,
                    'category' => get_label('team', 'Team')
                ],
                [
                    'id' => 'chat',
                    'label' => get_label('chat', 'Chat'),
                    'url' => url('chat'),
                    'icon' => 'bx bx-chat',
                    'class' => 'menu-item' . (Request::is('chat') || Request::is('chat/*') ? ' active' : ''),
                    'badge' => ($unread > 0) ? '<span class="flex-shrink-0 badge badge-center bg-danger w-px-20 h-px-20">' . $unread . '</span>' : '',
                    'show' => Auth::guard('web')->check() ? 1 : 0,
                    'category' => get_label('team', 'Team')
                ],
                [
                    'id' => 'leads_management',
                    'label' => get_label('leads_management', 'Leads Management'),
                    'url' => '',
                    'icon' => 'bx bxs-phone-call',
                    'class' => 'menu-item ' . (Request::is('master-panel/lead-sources') || Request::is('master-panel/lead-sources/*') || Request::is('master-panel/lead-stages') || Request::is('master-panel/lead-stages/*') ||  Request::is('master-panel/lead-forms') || Request::is('master-panel/lead-forms/*')  || Request::is('master-panel/leads') || Request::is('master-panel/leads/*') ? 'active open' : ''),
                    'category' => get_label('utilities', 'Utilities'),
                    'show' =>  $user->can('manage_leads') ? 1 : 0,
                    'submenus' => [
                        [
                            'id' => 'lead_sources',
                            'label' => get_label('lead_sources', 'Lead Sources'),
                            'url' => route('lead-sources.index'),
                            'show' => $user->can('manage_leads') ? 1 : 0,
                            'class' => 'menu-item ' . (Request::is('master-panel/lead-sources') || Request::is('master-panel/lead-sources/*') ? 'active' : '')
                        ],
                        [
                            'id' => 'lead_stages',
                            'label' => get_label('lead_stages', 'Lead Stages'),
                            'url' => route('lead-stages.index'),
                            'show' => $user->can('manage_leads') ? 1 : 0,
                            'class' => 'menu-item ' . (Request::is('master-panel/lead-stages') || Request::is('master-panel/lead-stages/*') ? 'active' : '')
                        ],
                        [
                            'id' => 'leads',
                            'label' => get_label('leads', 'Leads'),
                            'url' => getDefaultViewRoute('leads'),
                            'show' => $user->can('manage_leads') ? 1 : 0,
                            'class' => 'menu-item ' . (Request::is('master-panel/leads') || (Request::is('master-panel/leads/*') && !Request::is('master-panel/leads/bulk-upload')) ? 'active' : '')
                        ],
                        [
                            'id' => 'lead_bulk_upload',
                            'label' => get_label('bulk_upload', 'Bulk Upload'),
                            'url' => route('leads.upload'),
                            'class' => 'menu-item' . (Request::is('master-panel/leads/bulk-upload') ? ' active' : ''),
                            'show' => $user->can('manage_leads') ? 1 : 0,
                        ],
                        [
                            'id' => 'lead_forms',
                            'label' => get_label('lead_forms', 'Lead Forms'),
                            'url' => route('lead-forms.index'),
                            'class' => 'menu-item' . (Request::is('master-panel/lead-forms') || Request::is('master-panel/lead-forms/*') ? ' active' : ''),
                            'show' => $user->can('manage_leads') ? 1 : 0,
                        ],
                    ]
                ],
                [
                    'id' => 'email',
                    'label' => get_label('email', 'Email'),
                    'class' => 'menu-item' . (Request::is('master-panel/emails') || Request::is('master-panel/emails/create') || Request::is('master-panel/email-templates') ? ' active open' : ''),
                    'show' => ($user->can('send_email') || $user->can('manage_email_template')) ? 1 : 0,
                    'icon' => 'bx bx-mail-send',
                    'category' => get_label('utilities', 'Utilities'),
                    'submenus' => [
                        [
                            'id' => 'send_email',
                            'label' => get_label('send_email', 'Send Email'),
                            'url' => route('emails.index'),
                            'class' => 'menu-item' . (Request::is('master-panel/emails') || Request::is('master-panel/emails/create') ? ' active' : ''),
                            'show' => $user->can('send_email') ? 1 : 0
                        ],
                        [
                            'id' => 'email_templates',
                            'label' => get_label('email_templates', 'Email Templates'),
                            'url' => route('email.templates'),
                            'class' => 'menu-item' . (Request::is('master-panel/email-templates') ? ' active' : ''),
                            'show' => $user->can('manage_email_template') ? 1 : 0
                        ],
                    ],

                ],
                [
                    'id' => 'general_file_manager',
                    'label' => get_label('general_file_manager', 'General File Manager'),
                    'url' => route('file_manager.index'),
                    'icon' => 'bx bx-folder-open',
                    'class' => 'menu-item' . (Request::is('master-panel/file-manager') || Request::is('master-panel/file-manager/*') ? ' active' : ''),
                    'badge' => ($unread > 0) ? '<span class="flex-shrink-0 badge badge-center bg-danger w-px-20 h-px-20">' . $unread . '</span>' : '',
                    'show' => Auth::guard('web')->check() ? 1 : 0,
                    'category' => get_label('utilities', 'Utilities')
                ],
                [
                    'id' => 'todos',
                    'label' => get_label('todos', 'Todos'),
                    'url' => route('todos.index'),
                    'icon' => 'bx bx-list-check',
                    'class' => 'menu-item' . (Request::is('master-panel/todos') || Request::is('master-panel/todos/*') ? ' active' : ''),
                    'badge' => ($pending_todos_count > 0) ? '<span class="flex-shrink-0 badge badge-center bg-danger w-px-20 h-px-20">' . $pending_todos_count . '</span>' : '',
                    'category' => get_label('utilities', 'Utilities')
                ],
                [
                    'id' => 'meetings',
                    'label' => get_label('meetings', 'Meetings'),
                    'url' => getDefaultViewRoute('meetings'),
                    'icon' => 'bx bx-shape-polygon',
                    'class' => 'menu-item' . (Request::is('master-panel/meetings') || Request::is('master-panel/meetings/*') ? ' active' : ''),
                    'badge' => ($ongoing_meetings_count > 0) ? '<span class="flex-shrink-0 badge badge-center bg-success w-px-20 h-px-20">' . $ongoing_meetings_count . '</span>' : '',
                    'show' => $user->can('manage_meetings') ? 1 : 0,
                    'category' => get_label('utilities', 'Utilities')
                ],
                [
                    'id' => 'users',
                    'label' => get_label('users', 'Users'),
                    'url' => route('users.index'),
                    'icon' => 'bx bx-group',
                    'class' => 'menu-item' . (Request::is('master-panel/users') || Request::is('master-panel/users/*') ? ' active' : ''),
                    'show' => $user->can('manage_users') ? 1 : 0,
                    'category' => get_label('team', 'Team')
                ],
                [
                    'id' => 'clients',
                    'label' => get_label('clients', 'Clients'),
                    'url' => route('clients.index'),
                    'icon' => 'bx bx-group',
                    'class' => 'menu-item' . (Request::is('master-panel/clients') || Request::is('master-panel/clients/*') ? ' active' : ''),
                    'show' => $user->can('manage_clients') ? 1 : 0,
                    'category' => get_label('team', 'Team')

                ],
                [
                    'id' => 'contracts',
                    'label' => get_label('contracts', 'Contracts'),
                    'url' => 'javascript:void(0)',
                    'icon' => 'bx bx-news',
                    'class' => 'menu-item' . (Request::is('master-panel/contracts') || Request::is('master-panel/contracts/*') ? ' active open' : ''),
                    'show' => ($user->can('manage_contracts') || $user->can('manage_contract_types')) ? 1 : 0,
                    'category' => get_label('finance', 'Finance'),
                    'submenus' => [
                        [
                            'id' => 'manage_contracts',
                            'label' => get_label('manage_contracts', 'Manage contracts'),
                            'url' => route('contracts.index'),
                            'class' => 'menu-item' . (Request::is('master-panel/contracts') ? ' active' : ''),
                            'show' => $user->can('manage_contracts') ? 1 : 0
                        ],
                        [
                            'id' => 'contract_types',
                            'label' => get_label('contract_types', 'Contract types'),
                            'url' => route('contracts.contract_types'),
                            'class' => 'menu-item' . (Request::is('master-panel/contracts/contract-types') ? ' active' : ''),
                            'show' => $user->can('manage_contract_types') ? 1 : 0
                        ],
                    ],
                ],
                [
                    'id' => 'payslips',
                    'label' => get_label('payslips', 'Payslips'),
                    'url' => 'javascript:void(0)',
                    'icon' => 'bx bx-box',
                    'class' => 'menu-item' . (Request::is('master-panel/payslips') || Request::is('master-panel/payslips/*') || Request::is('master-panel/allowances') || Request::is('master-panel/deductions') ? ' active open' : ''),
                    'show' => ($user->can('manage_payslips') || $user->can('manage_allowances') || $user->can('manage_deductions')) ? 1 : 0,
                    'category' => get_label('finance', 'Finance'),
                    'submenus' => [
                        [
                            'id' => 'manage_payslips',
                            'label' => get_label('manage_payslips', 'Manage payslips'),
                            'url' => route('payslips.index'),
                            'class' => 'menu-item' . (Request::is('master-panel/payslips') || Request::is('master-panel/payslips/*') ? ' active' : ''),
                            'show' => $user->can('manage_payslips') ? 1 : 0
                        ],
                        [
                            'id' => 'allowances',
                            'label' => get_label('allowances', 'Allowances'),
                            'url' => route('allowances.index'),
                            'class' => 'menu-item' . (Request::is('master-panel/allowances') ? ' active' : ''),
                            'show' => $user->can('manage_allowances') ? 1 : 0
                        ],
                        [
                            'id' => 'deductions',
                            'label' => get_label('deductions', 'Deductions'),
                            'url' => route('deductions.index'),
                            'class' => 'menu-item' . (Request::is('master-panel/deductions') ? ' active' : ''),
                            'show' => $user->can('manage_deductions') ? 1 : 0
                        ],
                    ],
                ],
                [
                    'id' => 'finance',
                    'label' => get_label(get_label('finance', 'Finance'), get_label('finance', 'Finance')),
                    'url' => 'javascript:void(0)',
                    'icon' => 'bx bx-box',
                    'class' => 'menu-item' . (Request::is('master-panel/estimates-invoices') || Request::is('master-panel/estimates-invoices/*') || Request::is('master-panel/taxes') || Request::is('master-panel/payment-methods') || Request::is('master-panel/payments') || Request::is('master-panel/units') || Request::is('master-panel/items') || Request::is('master-panel/expenses') || Request::is('master-panel/expenses/*') ? ' active open' : ''),
                    'show' => ($user->can('manage_estimates_invoices') || $user->can('manage_expenses') || $user->can('manage_payment_methods') ||
                        $user->can('manage_expense_types') || $user->can('manage_payments') || $user->can('manage_taxes') ||
                        $user->can('manage_units') || $user->can('manage_items')) ? 1 : 0,
                    'category' => get_label('finance', 'Finance'),

                    'submenus' => [
                        [
                            'id' => 'expenses',
                            'label' => get_label('expenses', 'Expenses'),
                            'url' => route('expenses.index'),
                            'class' => 'menu-item' . (Request::is('master-panel/expenses') ? ' active' : ''),
                            'show' => $user->can('manage_expenses') ? 1 : 0
                        ],
                        [
                            'id' => 'expense_types',
                            'label' => get_label('expense_types', 'Expense types'),
                            'url' => route('expenses-type.index'),
                            'class' => 'menu-item' . (Request::is('master-panel/expenses/expense-types') ? ' active' : ''),
                            'show' => $user->can('manage_expense_types') ? 1 : 0
                        ],
                        [
                            'id' => 'estimates_invoices',
                            'label' => get_label('estimates_invoices', 'Estimates/Invoices'),
                            'url' => route('estimates-invoices.index'),
                            'class' => 'menu-item' . (Request::is('master-panel/estimates-invoices') || Request::is('master-panel/estimates-invoices/*') ? ' active' : ''),
                            'show' => $user->can('manage_estimates_invoices') ? 1 : 0
                        ],
                        [
                            'id' => 'payments',
                            'label' => get_label('payments', 'Payments'),
                            'url' => route('payments.index'),
                            'class' => 'menu-item' . (Request::is('master-panel/payments') ? ' active' : ''),
                            'show' => $user->can('manage_payments') ? 1 : 0
                        ],
                        [
                            'id' => 'payment_methods',
                            'label' => get_label('payment_methods', 'Payment methods'),
                            'url' => route('paymentMethods.index'),
                            'class' => 'menu-item' . (Request::is('master-panel/payment-methods') ? ' active' : ''),
                            'show' => $user->can('manage_payment_methods') ? 1 : 0
                        ],
                        [
                            'id' => 'taxes',
                            'label' => get_label('taxes', 'Taxes'),
                            'url' => route('taxes.index'),
                            'class' => 'menu-item' . (Request::is('master-panel/taxes') ? ' active' : ''),
                            'show' => $user->can('manage_taxes') ? 1 : 0
                        ],
                        [
                            'id' => 'units',
                            'label' => get_label('units', 'Units'),
                            'url' => route('units.index'),
                            'class' => 'menu-item' . (Request::is('master-panel/units') ? ' active' : ''),
                            'show' => $user->can('manage_units') ? 1 : 0
                        ],
                        [
                            'id' => 'items',
                            'label' => get_label('items', 'Items'),
                            'url' => route('items.index'),
                            'class' => 'menu-item' . (Request::is('master-panel/items') ? ' active' : ''),
                            'show' => $user->can('manage_items') ? 1 : 0
                        ],
                    ],
                ],
                [
                    'id' => 'reports',
                    'label' => get_label('reports', 'Reports'),
                    'url' => 'javascript:void(0)',
                    'icon' => 'bx bx-file',
                    'class' => 'menu-item' . (Request::is('master-panel/reports') || Request::is('master-panel/reports/*') ? ' active open' : ''),
                    'show' => isAdminOrHasAllDataAccess() ? 1 : 0,
                    'category' => get_label('utilities', 'Utilities'),

                    'submenus' => [
                        [
                            'id' => 'projects_report',
                            'label' => get_label('projects', 'Projects'),
                            'url' => route('reports.projects-report'),
                            'class' => 'menu-item' . (Request::is('master-panel/reports/projects-report') ? ' active' : ''),
                            'show' => isAdminOrHasAllDataAccess() ? 1 : 0,
                        ],
                        [
                            'id' => 'tasks_report',
                            'label' => get_label('tasks', 'Tasks'),
                            'url' => route('reports.tasks-report'),
                            'class' => 'menu-item' . (Request::is('master-panel/reports/tasks-report') ? ' active' : ''),
                            'show' => isAdminOrHasAllDataAccess() ? 1 : 0,
                        ],
                        [
                            'id' => 'invoices_report',
                            'label' => get_label('invoices', 'Invoices'),
                            'url' => route('reports.invoices-report'),
                            'class' => 'menu-item' . (Request::is('master-panel/reports/invoices-report') ? ' active' : ''),
                            'show' => isAdminOrHasAllDataAccess() ? 1 : 0,
                        ],
                        [
                            'id' => 'income_vs_expense',
                            'label' => get_label('income_vs_expense', 'Income vs Expense'),
                            'url' => route('reports.income-vs-expense-report'),
                            'class' => 'menu-item' . (Request::is('master-panel/reports/income-vs-expense-report') ? ' active' : ''),
                            'show' => isAdminOrHasAllDataAccess() ? 1 : 0,
                        ],
                        [
                            'id' => 'leaves',
                            'label' => get_label('leaves', 'Leaves'),
                            'url' => route('reports.leaves-report'),
                            'class' => 'menu-item' . (Request::is('master-panel/reports/leaves-report') ? ' active' : ''),
                            'show' => isAdminOrHasAllDataAccess() ? 1 : 0,
                        ],
                        [
                            'id' => 'work_hours',
                            'label' => get_label('work_hours_report', 'Work Hours Report'),
                            'url' => route('reports.work-hours-report'),
                            'class' => 'menu-item' . (Request::is('master-panel/reports/work-hours-report') ? ' active' : ''),
                            'show' => isAdminOrHasAllDataAccess() ? 1 : 0,
                        ],
                        [
                            'id' => 'time_sheet',
                            'label' => get_label('time_sheet_report', 'Time Sheet Report'),
                            'url' => route('reports.time-sheet-report'),
                            'class' => 'menu-item' . (Request::is('master-panel/reports/time-sheet-report') ? ' active' : ''),
                            'show' => isAdminOrHasAllDataAccess() ? 1 : 0,
                        ],


                    ],
                ],
                [
                    'id' => 'hrms_management',
                    'label' => get_label('HRMS', 'HRMS'),
                    'icon' => 'bx bx-group',
                    'class' => 'menu-item' . (Request::is('master-panel/candidate*') || Request::is('master-panel/candidate_status*') || Request::is('master-panel/interviews*') ? ' active open' : ''),
                    'show' => ($user->can('manage_candidate') || $user->can('manage_candidate_status') || $user->can('manage_interview')) ? 1 : 0,
                    'category' => get_label('utilities', 'Utilities'),
                    'submenus' => [
                        [
                            'id' => 'candidates',
                            'label' => get_label('candidate', 'Candidates'),
                            'url' => getDefaultViewRoute('candidates'),
                            'class' => 'menu-item' . (Request::is('master-panel/candidate') || Request::is('master-panel/candidate/*') ? ' active' : ''),
                            'show' => $user->can('manage_candidate') ? 1 : 0,
                        ],
                        [
                            'id' => 'candidates_status',
                            'label' => get_label('candidate_status', 'Candidates Status'),
                            'url' => route('candidate_status.index'),
                            'class' => 'menu-item' . (Request::is('master-panel/candidate-status*') ? ' active' : ''),
                            'show' => $user->can('manage_candidate_status') ? 1 : 0,
                        ],
                        [
                            'id' => 'interviews',
                            'label' => get_label('interviews', 'Interviews'),
                            'url' => route('interviews.index'),
                            'class' => 'menu-item' . (Request::is('master-panel/interviews*') ? ' active' : ''),
                            'show' => $user->can('manage_interview') ? 1 : 0,
                        ],
                    ]
                ],
                [
                    'id' => 'notes',
                    'label' => get_label('notes', 'Notes'),
                    'url' => route('notes.index'),
                    'icon' => 'bx bx-notepad',
                    'class' => 'menu-item' . (Request::is('master-panel/notes') || Request::is('master-panel/notes/*') ? ' active' : ''),
                    'category' => get_label('utilities', 'Utilities')

                ],
                [
                    'id' => 'leave_requests',
                    'label' => get_label('leave_requests', 'Leave requests'),
                    'url' =>  getDefaultViewRoute('leave-requests'),
                    'icon' => 'bx bx-right-arrow-alt',
                    'class' => 'menu-item' . (Request::is('master-panel/leave-requests') || Request::is('master-panel/leave-requests/*') ? ' active' : ''),
                    'badge' => ($pendingLeaveRequestsCount > 0) ? '<span class="flex-shrink-0 badge badge-center bg-danger w-px-20 h-px-20">' . $pendingLeaveRequestsCount . '</span>' : '',
                    'show' => Auth::guard('web')->check() ? 1 : 0,
                    'category' => get_label('utilities', 'Utilities')

                ],
                [
                    'id' => 'activity_log',
                    'label' => get_label('activity_log', 'Activity log'),
                    'url' => getDefaultViewRoute('activity-log'),
                    'icon' => 'bx bx-line-chart',
                    'class' => 'menu-item' . (Request::is('master-panel/activity-log') || Request::is('master-panel/activity-log/*') ? ' active' : ''),
                    'show' => $user->can('manage_activity_log') ? 1 : 0,
                    'category' => get_label('utilities', 'Utilities')

                ],
                [
                    'id' => 'calendars',
                    'label' => get_label('calendars', 'Calendars'),
                    'icon' => 'bx bx-calendar',
                    'class' => 'menu-item' . (Request::is('calendars') || Request::is('calendars/*') ? ' active open' : ''),
                    'show' => 1,
                    'category' => get_label('utilities', 'Utilities'),
                    'submenus' => [
                        [
                            'id' => 'holiday_calendar',
                            'label' => get_label('holiday_calendar', 'Holiday calendar'),
                            'url' => route('calendars.holiday_calendar'),
                            'show' => 1,
                            'class' => 'menu-item' . (Request::is('calendars/holiday-calendar') ? ' active' : ''),
                        ],

                    ]
                ],

                // [
                //     'id' => 'subscription_plan',
                //     'label' => get_label('subscription_plan', 'Subscription Plan'),
                //     'url' => route('subscription-plan.index'),
                //     'icon' => 'bx bx-task',
                //     'class' => 'menu-item' . (Request::is('master-panel/subscription-plan') || Request::is('master-panel/subscription-plan/*') ? ' active' : ''),
                //     'show' => $user->hasRole('admin') ? 1 : 0,
                //     'category' => get_label('settings', 'Settings')
                // ],
                // [
                //     'id' => 'settings',
                //     'label' => get_label('settings', 'Settings'),
                //     'icon' => 'bx bx-cog',
                //     'class' => 'menu-item' . (Request::is('master-panel/settings') ? ' active' : ''),
                //     'show' => $user->hasRole('admin') ? 1 : 0,
                //     'url' => route('admin_settings.index'),
                //     'category' => 'settings',
                //     'submenus' => [
                //         [
                //             'id' => 'google_calendar',
                //             'label' => get_label('google_calendar', 'Google Calendar'),
                //             'url' => route('google_calendar.index'),
                //             'class' => 'menu-item' . (Request::is('settings/google-calendar') ? ' active' : ''),
                //         ],
                //     ]
                // ]
                [
                    'id' => 'settings',
                    'label' => get_label('settings', 'Settings'),
                    'icon' => 'bx bx-cog',
                    'class' => 'menu-item' . (Request::is('master-panel/settings*') ? ' active open' : ''),
                    'show' => $user->hasRole('admin') ? 1 : 0,
                    'url' => 'javascript:void(0)',
                    'category' => 'settings',
                    'submenus' => [
                        [
                            'id' => 'general_settings',
                            'label' => get_label('general_settings', 'General Settings'),
                            'url' => route('admin_settings.index'),
                            'class' => 'menu-item' . (Request::is('master-panel/settings') ? ' active' : ''),
                        ],
                        [
                            'id' => 'subscription_plan',
                            'label' => get_label('subscription_plan', 'Subscription plan'),
                            'url' => route('subscription-plan.index'),
                            'class' => 'menu-item' . (Request::is('master-panel/subscription-plan') || Request::is('master-panel/subscription-plan/*') ? ' active' : ''),
                        ],
                        [
                            'id' => 'google_calendar',
                            'label' => get_label('google_calendar', 'Google calendar'),
                            'url' => route('google_calendar.index'),
                            'class' => 'menu-item' . (Request::is('master-panel/settings/google-calendar') ? ' active' : ''),
                        ],
                        [
                            'id' => 'custom_fields',
                            'label' => get_label('custom_fields', 'Custom fields'),
                            'url' => route('custom_fields.index'),
                            'class' => 'menu-item' . (Request::is('master-panel/settings/custom-fields') ? ' active' : ''),
                        ],
                    ]
                ]


            ];
        }
    }
    if (!function_exists('formatLeadUserHtml')) {
        function formatLeadUserHtml($lead)
        {
            if (!$lead) {
                return "-";
            }

            // Check if the lead has phone and/or country code
            $makeCallIcon = '';
            if (!empty($lead->phone) || (!empty($lead->phone) && !empty($lead->country_code))) {
                $makeCallLink = 'tel:' . ($lead->country_code ? $lead->country_code . $lead->phone : $lead->phone);
                $makeCallIcon = '<a href="' . $makeCallLink . '" class="text-decoration-none" title="' . get_label('make_call', 'Make Call') . '">
                             <i class="bx bx-phone-call text-primary"></i>
                         </a>';
            }



            // Email & Mail Link
            $sendMailLink = 'mailto:' . $lead->email;
            $sendMailIcon = '<a href="' . $sendMailLink . '" class="text-decoration-none" title="' . get_label('send_mail', 'Send Mail') . '">
                        <i class="bx bx-envelope text-primary"></i>
                     </a>';

            return "<div class='d-flex justify-content-start align-items-center user-name'>

                <div class='d-flex flex-column'>
                    <span class='fw-semibold'>" . ucwords($lead->first_name . ' ' . $lead->last_name) . " {$makeCallIcon}</span>
                    <small class='text-muted'>{$lead->email} {$sendMailIcon}</small>
                </div>
            </div>";
        }
    }
    if (!function_exists('formatUserHtml')) {
        function formatUserHtml($user)
        {
            if (!$user) {
                return "-";
            }

            // Get the authenticated user
            $authenticatedUser = getAuthenticatedUser();

            // Get the guard name (web or client)
            $guardName = getGuardName();

            // Check if the authenticated user is the same as the user being displayed
            if (
                ($guardName === 'web' && $authenticatedUser->id === $user->id) ||
                ($guardName === 'client' && $authenticatedUser->id === $user->id)
            ) {
                // Don't show the "Make Call" option if it's the logged-in user
                $makeCallIcon = '';
            } else {
                // Check if the phone number or both phone and country code exist
                $makeCallIcon = '';
                if (!empty($user->phone) || (!empty($user->phone) && !empty($user->country_code))) {
                    $makeCallLink = 'tel:' . ($user->country_code ? $user->country_code . $user->phone : $user->phone);
                    $makeCallIcon = '<a href="' . $makeCallLink . '" class="text-decoration-none" title="' . get_label('make_call', 'Make Call') . '">
                                     <i class="bx bx-phone-call text-primary"></i>
                                   </a>';
                }
            }

            // If the user has 'manage_users' permission, return the full HTML with links
            $profileLink = route('users.show', ['id' => $user->id]);
            $photoUrl = $user->photo ? asset('storage/' . $user->photo) : asset('storage/photos/no-image.jpg');

            // Create the Send Mail link
            $sendMailLink = 'mailto:' . $user->email;
            $sendMailIcon = '<a href="' . $sendMailLink . '" class="text-decoration-none" title="' . get_label('send_mail', 'Send Mail') . '">
                            <i class="bx bx-envelope text-primary"></i>
                          </a>';

            return "<div class='d-flex justify-content-start align-items-center user-name'>
                    <div class='avatar-wrapper me-3'>
                        <div class='avatar avatar-sm pull-up'>
                            <a href='{$profileLink}'>
                                <img src='{$photoUrl}' alt='Photo' class='rounded-circle'>
                            </a>
                        </div>
                    </div>
                    <div class='d-flex flex-column'>
                        <span class='fw-semibold'>{$user->first_name} {$user->last_name} {$makeCallIcon}</span>
                        <small class='text-muted'>{$user->email} {$sendMailIcon}</small>
                    </div>
                </div>";
        }
    }


    if (!function_exists('getWorkspaceId')) {
        function getWorkspaceId()
        {
            $workspaceId = 0;
            $authenticatedUser = getAuthenticatedUser();

            if ($authenticatedUser) {
                if (session()->has('workspace_id')) {
                    $workspaceId = session('workspace_id'); // Retrieve workspace_id from session
                } else {
                    $workspaceId = request()->header('workspace_id');
                }
            }
            return $workspaceId;
        }
    }
    if (!function_exists('formatApiResponse')) {
        function formatApiResponse($error, $message, array $optionalParams = [], $statusCode = 200)
        {
            $response = [
                'error' => $error,
                'message' => $message

            ];

            // Merge optional parameters into the response if they are provided
            $response = array_merge($response, $optionalParams);

            return response()->json($response, $statusCode);
        }
    }

    // if (!function_exists('formateProjects')) {
    //     function formateProjects($project)

    //     {
    //         return [
    //             'id' => $project->id,
    //             'title' => $project->title,
    //             'description' => $project->description,
    //             'status' => optional($project->status)->title,
    //             'priority' => $project->priority,
    //             'tags' => $project->tags,
    //             'users' => $project->users,
    //             'clients' => $project->clients ,
    //             'tasks_count' =>$project->tasks->count(),
    //             'created_at' => optional($project->created_at)->toDateTimeString(),
    //         ];
    //     }
    // }
    // dd($projects);

    if (!function_exists('formatProject')) {
        function formatProject($project)
        {
            $auth_user = getAuthenticatedUser();
            return [
                'id' => $project->id ?? 0,
                // dd($project->id),
                'title' => $project->title ?? '',
                'task_count' => isAdminOrHasAllDataAccess() ? count($project->tasks) : $auth_user->project_tasks($project->id)->count() ?? 0,
                'status' => $project->status->title ?? '',
                'status_id' => $project->status->id ?? 0,
                'priority' => $project->priority ? $project->priority->title : '',
                'priority_id' => $project->priority ? $project->priority->id : 0,
                'users' => $project->users->map(function ($user) {
                    // dd($user);
                    return [
                        'id' => $user->id ?? 0,
                        'first_name' => $user->first_name ?? '',
                        'last_name' => $user->last_name ?? '',
                        'email' => $user->email ?? '',
                        'photo' => $user->photo ? asset('storage/' . $user->photo) : asset('storage/photos/no-image.jpg') ?? ''
                    ];
                }),
                'user_id' => $project->users->pluck('id')->toArray() ?? [],
                'clients' => $project->clients->map(function ($client) {
                    return [
                        'id' => $client->id ?? 0,
                        'first_name' => $client->first_name ?? '',
                        'last_name' => $client->last_name ?? '',
                        'email' => $client->email ?? '',
                        'photo' => $client->photo ? asset('storage/' . $client->photo) : asset('storage/photos/no-image.jpg') ?? ''
                    ];
                }),
                'client_id' => $project->clients->pluck('id')->toArray() ?? [],
                'tags' => $project->tags->map(function ($tag) {
                    return [
                        'id' => $tag->id ?? 0,
                        'title' => $tag->title ?? ''
                    ];
                }),
                'tag_ids' => $project->tags->pluck('id')->toArray() ?? [],
                'start_date' => $project->start_date ? format_date($project->start_date, to_format: 'Y-m-d') : 0000 - 00 - 00,
                'end_date' => $project->end_date ? format_date($project->end_date, to_format: 'Y-m-d') : 0000 - 00 - 00,
                'budget' => $project->budget ?? 0,
                // dd($project->budget),
                'task_accessibility' => $project->task_accessibility ?? 'everyone',
                'description' => $project->description ?? '',
                'note' => $project->note ?? '',
                // dd($project->note),
                'favorite' => $project->is_favorite ? 1 : 0,
                'client_can_discuss' => $project->client_can_discuss ?? false,

                'created_at' => format_date($project->created_at, to_format: 'Y-m-d'),
                'updated_at' => format_date($project->updated_at, to_format: 'Y-m-d'),
            ];
        }
    }
    if (!function_exists('formatComments')) {
        function formatComments($comment)
        {
            return [
                'id' => $comment->id,
                'content' => $comment->content,
                'user' => [
                    'id' => $comment->user?->id ?? 0,
                    'name' => $comment->user?->name ?? '',
                    'email' => $comment->user?->email ?? '',
                ],
                'attachments' => $comment->attachments->map(function ($attachment) {
                    return [
                        'file_name' => $attachment->file_name ?? '',
                        'file_url' => asset('storage/' . $attachment->file_path ?? ''),
                        'file_type' => $attachment->file_type ?? '',
                    ];
                }),
                'parent_id' => $comment->parent_id ?? 0,
                'created_at' => $comment->created_at->toDateTimeString() ?? '',
                'created_human' => $comment->created_at->diffForHumans() ?? '',
            ];
        }
    }
    if (!function_exists('formatTask')) {
        function formatTask($task)

        {

            $task->load('reminders', 'recurringTask');
            $reminder = $task->reminders[0] ?? '';
            $recurringTask = $task->recurringTask ?? [];
            $project = $task->project;
            return [
                'id' => $task->id,
                'workspace_id' => $task->workspace_id,
                'title' => $task->title,
                'status' => $task->status->title,
                'status_id' => $task->status->id,
                'priority' => $task->priority ? $task->priority->title : '',
                'priority_id' => $task->priority ? $task->priority->id : 0,
                'users' => $task->users->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'email' => $user->email,
                        'photo' => $user->photo ? asset('storage/' . $user->photo) : asset('storage/photos/no-image.jpg')
                    ];
                }),
                'user_id' => $task->users->pluck('id')->toArray(),
                'clients' => $task->project->clients->map(function ($client) {
                    return [
                        'id' => $client->id,
                        'first_name' => $client->first_name,
                        'last_name' => $client->last_name,
                        'email' => $client->email,
                        'photo' => $client->photo ? asset('storage/' . $client->photo) : asset('storage/photos/no-image.jpg')
                    ];
                }),
                'start_date' => $task->start_date ? format_date($task->start_date, to_format: 'Y-m-d') : 0000 - 00 - 00,
                'due_date' => $task->due_date ? format_date($task->due_date, to_format: 'Y-m-d') : 0000 - 00 - 00,
                'project' => $task->project->title,
                'project_id' => $task->project->id,
                'description' => $task->description,
                'note' => $task->note,
                'favorite' => $project->is_favorited ? 1 : 0,

                'client_can_discuss' => $task->client_can_discuss ?? false,
                'created_at' => format_date($task->created_at, to_format: 'Y-m-d'),
                'updated_at' => format_date($task->updated_at, to_format: 'Y-m-d'),
                'enable_reminder' => $reminder ? 1 : 0,
                'last_reminder_sent' => $reminder && $reminder->last_sent_at ? \Carbon\Carbon::parse($reminder->last_sent_at)->diffForHumans() : '',
                'frequency_type' => $reminder->frequency_type ?? '',
                'day_of_week'     => isset($reminder->day_of_week) ? (int)$reminder->day_of_week : 0,
                'day_of_month'    => isset($reminder->day_of_month) ? (int)$reminder->day_of_month : 0,
                'time_of_day'     => $reminder->time_of_day ?? '',

                'enable_recurring_task' => $recurringTask ? 1 : 0,
                'recurrence_frequency' => $recurringTask ? $recurringTask->frequency : '',
                'recurrence_day_of_week' => $recurringTask && $recurringTask->day_of_week ? (int)$recurringTask->day_of_week : 0,
                'recurrence_day_of_month' => $recurringTask && $recurringTask->day_of_month ? (int)$recurringTask->day_of_month : 0,
                'recurrence_month_of_year' => $recurringTask && $recurringTask->month_of_year ? (int)$recurringTask->month_of_year : 0,
                'recurrence_starts_from' => $recurringTask ? format_date($recurringTask->starts_from, to_format: 'Y-m-d') : 0000 - 00 - 00,
                'recurrence_occurrences' => $recurringTask && $recurringTask->number_of_occurrences ? (int)$recurringTask->number_of_occurrences : 0,
                'completed_occurrences' => $recurringTask && $recurringTask->completed_occurrences ? (int)$recurringTask->completed_occurrences : 0,
                'billing_type' => $task->billing_type,
                'task_list_id' => $task->task_list_id ?? 0,
                'completion_percentage' => $task->completion_percentage,


            ];
        }
    }
    if (!function_exists('formateMedia')) {
        function formateMedia($media)
        {
            return [
                'id' => $media->id ?? '',
                'name' => $media->name ?? '',
                'file_name' => $media->file_name ?? '',
                'file_size' => $media->file_size ?? '',
                'file_type' => $media->file_type ?? '',
                'created_at' => format_date($media->created_at, to_format: 'Y-m-d') ?? 0000 - 00 - 00,
                'updated_at' => format_date($media->updated_at, to_format: 'Y-m-d') ?? 0000 - 00 - 00,
            ];
        }

        if (!function_exists('formatClient')) {
            function formatClient($client, $isSignup = false)
            {
                return [
                    'id' => $client->id ?? 0,
                    'first_name' => $client->first_name ?? '',
                    'last_name' => $client->last_name,
                    'role' => $client->getRoleNames()->first() ?? 'client',
                    'company' => $client->company ?? '',
                    'email' => $client->email ?? '',
                    'phone' => $client->phone ?? '',
                    'country_code' => $client->country_code ?? '',
                    'country_iso_code' => $client->country_iso_code ?? '',
                    'password' => $client->password ?? '',
                    'password_confirmation' => $client->password ?? '',
                    'type' => 'client' ?? '',
                    'dob' => $client->dob ? format_date($client->dob, to_format: 'Y-m-d') : 0000 - 00 - 00,
                    'doj' => $client->doj ? format_date($client->doj, to_format: 'Y-m-d') : 0000 - 00 - 00,
                    'address' => $client->address ? $client->address : null,
                    'city' => $client->city ?? '',
                    'state' => $client->state ?? '',
                    'country' => $client->country ?? '',
                    'zip' => $client->zip ?? '',
                    'profile' => $client->photo ? asset('storage/' . $client->photo) : asset('storage/photos/no-image.jpg') ?? '',
                    'status' => $client->status ?? 'active',
                    'fcm_token' => $client->fcm_token ?? '',
                    'internal_purpose' => $client->internal_purpose ?? '',
                    'email_verification_mail_sent' => $client->email_verification_mail_sent ?? 0,
                    'email_verified_at' => $client->email_verified_at ?? 0000 - 00 - 00,
                    'created_at' => format_date($client->created_at, to_format: 'Y-m-d') ?? '',
                    'updated_at' => format_date($client->updated_at, to_format: 'Y-m-d') ?? '',
                    'assigned' => $isSignup ? [
                        'projects' => 0 ?? 0,
                        'tasks' => 0 ?? 0
                    ] : (
                        isAdminOrHasAllDataAccess('client', $client->id) ? [
                            'projects' => Workspace::find(getWorkspaceId())->projects()->count() ?? 0,
                            'tasks' => Workspace::find(getWorkspaceId())->tasks()->count() ?? 0,
                        ] : [
                            'projects' => $client->projects()->count() ?? 0,
                            'tasks' => $client->tasks()->count() ?? 0
                        ]
                    )
                ];
            }
        }
        if (!function_exists('formatPriority')) {
            /**
             * Format the Priority model for API response.
             *
             * @param \App\Models\Priority $priority
             * @return array
             */
            function formatPriority($priority)
            {
                return [
                    'id' => $priority->id ?? 0,
                    'title' => $priority->title ?? '',
                    'slug' => $priority->slug ?? '',
                    'color' => $priority->color ?? '',
                    'admin_id' => $priority->admin_id ?? 0,
                    'created_at' => $priority->created_at ? $priority->created_at->toDateTimeString() : '',
                    'updated_at' => $priority->updated_at ? $priority->updated_at->toDateTimeString() : '',
                ];
            }
        }
        if (!function_exists('formatUser')) {
            /**
             * Format the given user for API responses.
             *
             * @param \App\Models\User $user
             * @return array
             */
            function formatUser($user, $isSignup = false)
            {
                return [
                    'id' => $user->id ?? 0,
                    'first_name' => $user->first_name ?? '',
                    'last_name' => $user->last_name ?? '',
                    'full_name' => trim($user->first_name . ' ' . $user->last_name ?? ''),
                    'email' => $user->email ?? '',
                    'phone' => $user->phone ?? '',
                    'address' => $user->address ?? '',
                    'country_code' => $user->country_code ?? '',
                    'city' => $user->city ?? '',
                    'state' => $user->state ?? '',
                    'country' => $user->country ?? '',
                    'zip' => $user->zip ?? '',
                    'dob' => optional($user->dob)->format('Y-m-d') ?? '',
                    'doj' => optional($user->doj)->format('Y-m-d') ?? '',
                    'role' => $user->roles->pluck('name')->first(), // assuming one role per user
                    'status' => $user->status ?? '',
                    'email_verified' => !is_null($user->email_verified_at) ?? 0000 - 00 - 00,
                    'photo_url' => $user->photo
                        ? asset('storage/' . $user->photo)
                        : asset('storage/photos/no-image.jpg') ?? '',
                    'created_at' => $user->created_at ? $user->created_at->toDateTimeString() : '',
                    'updated_at' => $user->updated_at ? $user->updated_at->toDateTimeString() : '',
                    'assigned' => $isSignup ? [
                        'projects' => 0 ?? 0,
                        'tasks' => 0 ?? 0
                    ] : (
                        isAdminOrHasAllDataAccess('user', $user->id) ? [
                            'projects' => Workspace::find(getWorkspaceId())->projects()->count(),
                            'tasks' => Workspace::find(getWorkspaceId())->tasks()->count(),
                        ] : [
                            'projects' => $user->projects()->count(),
                            'tasks' => $user->tasks()->count()
                        ]
                    )
                ];
            }
        }
        if (!function_exists('formatAccount')) {
            /**
             * Format the given user for API responses.
             *
             * @param \App\Models\User $user
             * @param string|null $workspace_id
             * @return array
             */
            function formatAccount($user, $workspace_id = null)
            {
                return [
                    'user_id' => $user->id ?? 0,
                    'first_name' => $user->first_name ?? '',
                    'last_name' => $user->last_name ?? '',
                    'full_name' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')),
                    'email' => $user->email ?? '',
                    'phone' => $user->phone ?? '',
                    'country_code' => $user->country_code ?? '',
                    'role' => $user->roles->pluck('name')->first(),
                    'role_id' => $user->roles->pluck('id')->first(),
                    'status' => $user->status ?? '',
                    'email_verified' => !is_null($user->email_verified_at),
                    'workspace_id' => $workspace_id ?? '0',
                ];
            }
        }


        function formatWorkspace($workspace)
        {
            $workspace->load(['users', 'clients']); // Eager load relations

            return [
                'id' => $workspace->id ?? 0,
                'title' => $workspace->title ?? '',
                'is_primary' => (bool) $workspace->is_primary ?? false,
                'users' => $workspace->users->map(function ($user) {
                    return [
                        'id' => $user->id ?? 0,
                        'first_name' => $user->first_name ?? '',
                        'last_name' => $user->last_name ?? '',
                        'photo' => $user->photo ? asset('storage/' . $user->photo) : asset('storage/photos/no-image.jpg') ?? '',
                    ];
                })->values(),
                'clients' => $workspace->clients->map(function ($client) {
                    return [
                        'id' => $client->id ?? 0,
                        'first_name' => $client->first_name ?? '',
                        'last_name' => $client->last_name ?? '',
                        'photo' => $client->photo ? asset('storage/' . $client->photo) : asset('storage/photos/no-image.jpg') ?? '',
                    ];
                })->values(),
                'created_at' => format_date($workspace->created_at, true) ?? '',
                'updated_at' => format_date($workspace->updated_at, true) ?? '',
            ];
        }
        if (!function_exists('formatTodo')) {
            function formatTodo($todo)
            {

                return [
                    'id' => $todo->id ?? 0,
                    'title' => $todo->title ?? '',
                    'priority' => $todo->priority ?? '',
                    'is_completed' => $todo->is_completed ?? '',
                    'description' => $todo->description ?? '',
                    'workspace_id' => $todo->workspace_id ?? 0,
                    'admin_id' => $todo->admin_id ?? 0,
                    'creator' => [
                        'id' => $todo->creator->id ?? 0,
                        'first_name' => $todo->creator->first_name ?? '',
                        'last_name' => $todo->creator->last_name ?? '',
                        'photo' => isset($todo->creator->photo)
                            ? asset('storage/' . $todo->creator->photo)
                            : asset('storage/photos/no-image.jpg') ?? '',
                    ],
                    'created_at' => $todo->created_at ? $todo->created_at->format('Y-m-d H:i:s') : 0000 - 00 - 00 . ' 00:00:00',
                    'updated_at' => $todo->updated_at ? $todo->updated_at->format('Y-m-d H:i:s') : 0000 - 00 - 00 . ' 00:00:00',
                ];
            }
        }
        function formatIssue($issue)
        {
            return [
                'id' => $issue->id ?? 0,
                'project_id' => $issue->project_id ?? 0,
                'title' => $issue->title ?? '',
                'description' => $issue->description ?? '',
                'status' => $issue->status ?? '',

                // Creator info
                'created_by' => [
                    'id' => optional($issue->creator)->id ?? 0,
                    'first_name' => optional($issue->creator)->first_name ?? '',
                    'last_name' => optional($issue->creator)->last_name ?? '',
                    'email' => optional($issue->creator)->email ?? '',
                ],

                // Assigned users
                'assignees' => $issue->users->map(function ($user) {
                    return [
                        'id' => $user->id ?? 0,
                        'first_name' => $user->first_name ?? '',
                        'last_name' => $user->last_name ?? '',
                        'email' => $user->email ?? '',
                        'photo' => $user->photo ? asset('storage/' . $user->photo) : '',
                    ];
                })->values(),

                // Timestamps
                'created_at' => $issue->created_at ? $issue->created_at->toDateTimeString() : '',
                'updated_at' => $issue->updated_at ? $issue->updated_at->toDateTimeString() : '',
            ];
        }

        if (!function_exists('formatNotification')) {
            function formatNotification($notification)
            {

                $readAt = isset($notification->notification_user_read_at)
                    ? format_date($notification->notification_user_read_at, true)
                    : (isset($notification->client_notifications_read_at)
                        ? format_date($notification->client_notifications_read_at, true)
                        : (isset($notification->pivot) && isset($notification->pivot->read_at)
                            ? format_date($notification->pivot->read_at, true)
                            : null));
                $labelRead = get_label('read', 'Read');
                $labelUnread = get_label('unread', 'Unread');
                $status = is_null($readAt) ? $labelUnread : $labelRead;

                // Handle is_system logic, including pivot
                $isSystem = $notification->notification_user_is_system
                    ?? $notification->client_notifications_is_system
                    ?? ($notification->pivot->is_system ?? null);

                // Handle is_push logic, including pivot
                $isPush = $notification->notification_user_is_push
                    ?? $notification->client_notifications_is_push
                    ?? ($notification->pivot->is_push ?? null);

                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'users' => $notification->users->map(function ($user) {
                        return [
                            'id' => $user->id,
                            'first_name' => $user->first_name,
                            'last_name' => $user->last_name,
                            'email' => $user->email,
                            'photo' => $user->photo ? asset('storage/' . $user->photo) : asset('storage/photos/no-image.jpg')
                        ];
                    }),
                    'clients' => $notification->clients->map(function ($client) {
                        return [
                            'id' => $client->id,
                            'first_name' => $client->first_name,
                            'last_name' => $client->last_name,
                            'email' => $client->email,
                            'photo' => $client->photo ? asset('storage/' . $client->photo) : asset('storage/photos/no-image.jpg')
                        ];
                    }),
                    'type' => ucfirst(str_replace('_', ' ', $notification->type)),
                    'type_id' => $notification->type_id,
                    'message' => $notification->message,
                    'status' => $status,
                    'is_system' => $isSystem,
                    'is_push' => $isPush,
                    'read_at' => $readAt,
                    'created_at' => format_date($notification->created_at, to_format: 'Y-m-d'),
                    'updated_at' => format_date($notification->updated_at, to_format: 'Y-m-d')
                ];
            }
        }

        if (!function_exists('formatContracts')) {
            /**
             * Format a contract or collection of contracts for API response.
             *
             * @param \App\Models\Contract|\Illuminate\Support\Collection $contracts
             * @return array
             */
            function formatContracts($contracts)
            {
                // If it's a collection, map each contract
                if ($contracts instanceof \Illuminate\Support\Collection) {
                    return $contracts->map(function ($contract) {
                        return formatContracts($contract);
                    })->toArray();
                }

                // If it's a single contract
                return [
                    'id' => $contracts->id,
                    'title' => $contracts->title,
                    'value' => $contracts->value,
                    'start_date' => $contracts->start_date,
                    'end_date' => $contracts->end_date,
                    'client_id' => $contracts->client_id,
                    'project_id' => $contracts->project_id,
                    'contract_type_id' => $contracts->contract_type_id,
                    'description' => $contracts->description,
                    'created_by' => $contracts->created_by,
                    'workspace_id' => $contracts->workspace_id,
                    'admin_id' => $contracts->admin_id,
                    'created_at' => $contracts->created_at,
                    'updated_at' => $contracts->updated_at,
                    // Add more fields as needed
                ];
            }
        }

        if (!function_exists('formatAllowance')) {
            /**
             * Format an Allowance model or collection for API response.
             *
             * @param \App\Models\Allowance|\Illuminate\Support\Collection $allowance
             * @return array|array[]
             */
            function formatAllowance($allowance)
            {
                if ($allowance instanceof \Illuminate\Support\Collection || is_array($allowance)) {
                    return collect($allowance)->map(function ($item) {
                        return formatAllowance($item);
                    })->all();
                }

                return [
                    'id' => $allowance->id ?? 0,
                    'title' => $allowance->title ?? '',
                    'amount' => (float) $allowance->amount ?? 0.0,
                    'workspace_id' => $allowance->workspace_id ?? 0,
                    'admin_id' => $allowance->admin_id ?? 0,
                    'created_at' => $allowance->created_at ? $allowance->created_at->toDateTimeString() : 0000 - 00 - 00 . ' 00:00:00',
                    'updated_at' => $allowance->updated_at ? $allowance->updated_at->toDateTimeString() : 0000 - 00 - 00 . ' 00:00:00',
                ];
            }
        }

        if (!function_exists('formatDeduction')) {
            /**
             * Format a Deduction model or collection for API response.
             *
             * @param \App\Models\Deduction|\Illuminate\Support\Collection $deduction
             * @return array|array[]
             */
            function formatDeduction($deduction)
            {
                if ($deduction instanceof \Illuminate\Support\Collection || is_array($deduction)) {
                    return collect($deduction)->map(function ($item) {
                        return formatDeduction($item);
                    })->all();
                }

                return [
                    'id' => $deduction->id,
                    'title' => $deduction->title,
                    'type' => $deduction->type,
                    'amount' => $deduction->amount !== null ? (float) $deduction->amount : null,
                    'percentage' => $deduction->percentage !== null ? (float) $deduction->percentage : null,
                    'workspace_id' => $deduction->workspace_id,
                    'admin_id' => $deduction->admin_id,
                    'created_at' => $deduction->created_at ? $deduction->created_at->toDateTimeString() : null,
                    'updated_at' => $deduction->updated_at ? $deduction->updated_at->toDateTimeString() : null,
                ];
            }
        }

        if (!function_exists('formatPayslip')) {
            function formatPayslip($payslip)
            {
                return [
                    'id' => $payslip->id ?? 0,
                    'user_id' => $payslip->user_id ?? 0,
                    'user_name' => optional($payslip->user)->name ?? '',
                    'month' => $payslip->month ?? '',
                    'basic_salary' => (float) $payslip->basic_salary ?? 0.0,
                    'working_days' => (int) $payslip->working_days ?? 0,
                    'lop_days' => (int) $payslip->lop_days ?? 0,
                    'paid_days' => (int) $payslip->paid_days ?? 0,
                    'bonus' => (float) $payslip->bonus ?? 0.0,
                    'incentives' => (float) $payslip->incentives ?? 0.0,
                    'leave_deduction' => (float) $payslip->leave_deduction ?? 0.0,
                    'ot_hours' => (int) $payslip->ot_hours ?? 0,
                    'ot_rate' => (float) $payslip->ot_rate ?? 0.0,
                    'ot_payment' => (float) $payslip->ot_payment ?? 0.0,
                    'total_allowance' => (float) $payslip->total_allowance ?? 0.0,
                    'total_deductions' => (float) $payslip->total_deductions ?? 0.0,
                    'total_earnings' => (float) $payslip->total_earnings ?? 0.0,
                    'net_pay' => (float) $payslip->net_pay ?? 0.0,
                    'status' => (int) $payslip->status ?? 0,
                    'status_label' => $payslip->status == 1 ? 'Paid' : 'Unpaid' ?? '',
                    'payment_method_id' => $payslip->payment_method_id ?? 0,
                    'payment_method_name' => optional($payslip->payment_method)->name ?? '',
                    'payment_date' => $payslip->payment_date ? date('Y-m-d', strtotime($payslip->payment_date)) : 0000 - 00 - 00,
                    'note' => $payslip->note ?? '',
                    'allowances' => $payslip->allowances->pluck('name')->toArray() ?? [],
                    'deductions' => $payslip->deductions->pluck('name')->toArray() ?? [],
                    'created_by' => $payslip->created_by ?? 0,
                    'created_at' => $payslip->created_at ? $payslip->created_at->format('Y-m-d H:i:s') : 0000 - 00 - 00 . ' 00:00:00',
                ];
            }
        }

        if (!function_exists('formatExpense')) {
            function formatExpense($expense)
            {
                if (!$expense) {
                    return null;
                }

                return [
                    'id' => $expense->id,
                    'title' => $expense->title,
                    'amount' => number_format((float) $expense->amount, 2, '.', ''),
                    'expense_date' => $expense->expense_date,
                    'note' => $expense->note ?? '',
                    'expense_type_id' => $expense->expense_type_id,
                    'expense_type_name' => optional($expense->expenseType)->title, // assumes relation defined
                    'user_id' => $expense->user_id,
                    'user_name' => optional($expense->user_id)->name, // assumes relation defined
                    'workspace_id' => $expense->workspace_id,
                    'created_by' => $expense->created_by,
                    'created_at' => $expense->created_at ? $expense->created_at->format('Y-m-d H:i:s') : null,
                    'updated_at' => $expense->updated_at ? $expense->updated_at->format('Y-m-d H:i:s') : null,
                ];
            }
        }
        if (!function_exists('formatExpenseType')) {
            function formatExpenseType($expenseType)
            {
                if (!$expenseType) {
                    return null;
                }

                return [
                    'id' => $expenseType->id,
                    'title' => $expenseType->title,
                    'description' => $expenseType->description ?? '',
                    'workspace_id' => $expenseType->workspace_id,
                    'created_at' => optional($expenseType->created_at)->format('Y-m-d H:i:s'),
                    'updated_at' => optional($expenseType->updated_at)->format('Y-m-d H:i:s'),
                ];
            }
        }
        if (!function_exists('getFavoriteStatus')) {
            function getFavoriteStatus($id, $model = \App\Models\Favorite::class)
            {
                // Ensure the model is valid and exists
                if (!class_exists($model) || !$model::find($id)) {
                    return false; // Return false if the model class doesn't exist or the specific entity doesn't exist
                }

                // Get the authenticated user (either a User or a Client)
                $authUser = getAuthenticatedUser();

                // Get the favorite based on the provided model (e.g., Project, Task, etc.)
                $isFavorited = $authUser->favorites()
                    ->where('favoritable_type', $model)
                    ->where('favoritable_id', $id)
                    ->exists();

                return (int) $isFavorited;
            }
        }
        if (!function_exists('formatMeeting')) {
            function formatMeeting($meeting)
            {
                return [
                    'id' => $meeting->id ?? 0,
                    'title' => $meeting->title ?? '',
                    'start_date_time' => $meeting->start_date_time ?? '',
                    'end_date_time' => $meeting->end_date_time ?? '',
                    'workspace_id' => $meeting->workspace_id ?? 0,
                    'admin_id' => $meeting->admin_id ?? 0,
                    'user_id' => $meeting->user_id ?? 0,
                    'users' => $meeting->users->map(function ($user) {
                        return [
                            'id' => $user->id ?? 0,
                            'first_name' => $user->first_name ?? '',
                            'last_name' => $user->last_name ?? ''

                        ];
                    }),
                    'clients' => $meeting->clients->map(function ($client) {
                        return [
                            'id' => $client->id ?? 0,
                            'first_name' => $client->first_name ?? '',
                            'last_name' => $client->last_name ?? ''

                        ];
                    }),
                    'created_at' => $meeting->created_at ? $meeting->created_at->toDateTimeString() : '',
                    'updated_at' => $meeting->updated_at ? $meeting->updated_at->toDateTimeString() : '',
                ];
            }
        }
        if (!function_exists('formatNote')) {
            function formatNote($note)
            {
                return [
                    'id' => $note->id ?? 0,
                    'title' => $note->title ?? '',
                    'note_type' => $note->note_type ?? '',
                    'color' => $note->color ?? '',
                    'description' => $note->description ?? '',
                    'drawing_data' => $note->note_type === 'drawing' ? $note->drawing_data : '',
                    'creator_id' => $note->creator_id ?? 0,
                    'admin_id' => $note->admin_id ?? 0,
                    'workspace_id' => $note->workspace_id ?? 0,
                    'created_at' => format_date($note->created_at, true) ?? '',
                    'updated_at' => format_date($note->updated_at, true) ?? '',
                ];
            }
        }

        function sanitizeUTF8($value)
        {
            if (is_string($value)) {
                return mb_convert_encoding($value, 'UTF-8', 'UTF-8');
            }
            if (is_array($value)) {
                return array_map('sanitizeUTF8', $value);
            }
            return $value;
        }

        if (!function_exists('formatLeaveRequest')) {
            function formatLeaveRequest($leaveRequest)
            {
                if (!$leaveRequest) {
                    return null;
                }

                return [
                    'id' => $leaveRequest->id ?? 0,
                    'reason' => $leaveRequest->reason ?? '',
                    'from_date' => $leaveRequest->from_date ?? '',
                    'to_date' => $leaveRequest->to_date ?? '',
                    'from_time' => $leaveRequest->from_time ?? '',
                    'to_time' => $leaveRequest->to_time ?? '',
                    'status' => $leaveRequest->status ?? '',
                    'user_id' => $leaveRequest->user_id ?? 0,
                    'workspace_id' => $leaveRequest->workspace_id ?? 0,
                    'admin_id' => $leaveRequest->admin_id ?? 0,
                    'action_by' => $leaveRequest->action_by ?? 0,
                    'visible_to_all' => (bool) $leaveRequest->visible_to_all,
                    'created_at' => $leaveRequest->created_at ? $leaveRequest->created_at->toDateTimeString() : '',
                    'updated_at' => $leaveRequest->updated_at ? $leaveRequest->updated_at->toDateTimeString() : '',
                    // Optional: Include user details
                    'user' => $leaveRequest->user ? [
                        'id' => $leaveRequest->user->id ?? 0,
                        'name' => $leaveRequest->user->name ?? '',
                        'email' => $leaveRequest->user->email ?? '',
                    ] : null,
                    // Optional: Include visibility users if not visible to all
                    'visible_to_users' => !$leaveRequest->visible_to_all
                        ? $leaveRequest->visibleToUsers->map(function ($user) {
                            return [
                                'id' => $user->id ?? 0,
                                'name' => $user->name ?? '',
                                'email' => $user->email ?? '',
                            ];
                        })->toArray()
                        : [],
                ];
            }
        }


        if (!function_exists('formatApiValidationError')) {
            function formatApiValidationError($isApi, $errors, $defaultMessage = 'Validation errors occurred')
            {
                if ($isApi) {
                    $messages = collect($errors)->flatten()->implode("\n");
                    return response()->json([
                        'error' => true,
                        'message' => $messages,
                    ], 422);
                } else {
                    return response()->json([
                        'error' => true,
                        'message' => $defaultMessage,
                        'errors' => $errors,
                    ], 422);
                }
            }
        }
        if (!function_exists('validate_date_format_and_order')) {
            /**
             * Validate if a date matches the format specified and ensure the start date is before or equal to the end date.
             *
             * @param string|null $startDate
             * @param string|null $endDate
             * @param string|null $format
             * @param string $startDateLabel
             * @param string $endDateLabel
             * @param string $startDateKey
             * @param string $endDateKey
             * @return array
             */
            function validate_date_format_and_order(
                $startDate,
                $endDate,
                $format = null,
                $startDateLabel = 'start date',
                $endDateLabel = 'end date',
                $startDateKey = 'start_date',
                $endDateKey = 'end_date'
            ) {
                $matchFormat = $format ?? get_php_date_time_format();

                $errors = [];

                // Validate start date format
                if ($startDate && !validate_date_format($startDate, $matchFormat)) {
                    $errors[$startDateKey][] = 'The ' . $startDateLabel . ' does not follow the format set in settings.';
                }

                // Validate end date format
                if ($endDate && !validate_date_format($endDate, $matchFormat)) {
                    $errors[$endDateKey][] = 'The ' . $endDateLabel . ' does not follow the format set in settings.';
                }

                // Validate date order
                if ($startDate && $endDate) {
                    $parsedStartDate = \DateTime::createFromFormat($matchFormat, $startDate);
                    $parsedEndDate = \DateTime::createFromFormat($matchFormat, $endDate);

                    if ($parsedStartDate && $parsedEndDate && $parsedStartDate > $parsedEndDate) {
                        $errors[$startDateKey][] = 'The ' . $startDateLabel . ' must be before or equal to the ' . $endDateLabel . '.';
                    }
                }

                return $errors;
            }
        }
        if (!function_exists('validate_date_format')) {
            /**
             * Validate if a date matches the format specified in settings.
             *
             * @param string $date
             * @param string|null $format
             * @return bool
             */
            function validate_date_format($date, $format = null)
            {
                $format = $format ?? get_php_date_time_format();
                $parsedDate = \DateTime::createFromFormat($format, $date);
                return $parsedDate && $parsedDate->format($format) === $date;
            }
        }


        if (!function_exists('validate_currency_format')) {
            /**
             * Validates currency format (e.g., 1,000.00 or 1000.00).
             *
             * @param string|float|null $value
             * @param string $field
             * @return string|null Error message or null if valid
             */
            function validate_currency_format($value, $field = 'amount')
            {
                if (is_null($value) || $value === '') {
                    return null; // Allow nullable
                }

                // Remove commas to allow formatted input like "1,000.00"
                $unformatted = str_replace(',', '', $value);

                // Check if it's a valid number with optional 2 decimal places
                if (!preg_match('/^\d+(\.\d{1,2})?$/', $unformatted)) {
                    return ucfirst($field) . ' must be a valid currency format (e.g. 1000.00)';
                }

                return null; // Valid
            }
            if (!function_exists('formatUserHtml')) {
                function formatUserHtml($user)
                {
                    if (!$user) {
                        return "-";
                    }

                    // Get the authenticated user
                    $authenticatedUser = getAuthenticatedUser();

                    // Get the guard name (web or client)
                    $guardName = getGuardName();

                    // Check if the authenticated user is the same as the user being displayed
                    if (
                        ($guardName === 'web' && $authenticatedUser->id === $user->id) ||
                        ($guardName === 'client' && $authenticatedUser->id === $user->id)
                    ) {
                        // Don't show the "Make Call" option if it's the logged-in user
                        $makeCallIcon = '';
                    } else {
                        // Check if the phone number or both phone and country code exist
                        $makeCallIcon = '';
                        if (!empty($user->phone) || (!empty($user->phone) && !empty($user->country_code))) {
                            $makeCallLink = 'tel:' . ($user->country_code ? $user->country_code . $user->phone : $user->phone);
                            $makeCallIcon = '<a href="' . $makeCallLink . '" class="text-decoration-none" title="' . get_label('make_call', 'Make Call') . '">
                                     <i class="bx bx-phone-call text-primary"></i>
                                   </a>';
                        }
                    }

                    // If the user has 'manage_users' permission, return the full HTML with links
                    $profileLink = route('users.profile', ['id' => $user->id]);
                    $photoUrl = $user->photo ? asset('storage/' . $user->photo) : asset('storage/photos/no-image.jpg');

                    // Create the Send Mail link
                    $sendMailLink = 'mailto:' . $user->email;
                    $sendMailIcon = '<a href="' . $sendMailLink . '" class="text-decoration-none" title="' . get_label('send_mail', 'Send Mail') . '">
                            <i class="bx bx-envelope text-primary"></i>
                          </a>';

                    return "<div class='d-flex justify-content-start align-items-center user-name'>
                    <div class='avatar-wrapper me-3'>
                        <div class='avatar avatar-sm pull-up'>
                            <a href='{$profileLink}'>
                                <img src='{$photoUrl}' alt='Photo' class='rounded-circle'>
                            </a>
                        </div>
                    </div>
                    <div class='d-flex flex-column'>
                        <span class='fw-semibold'>{$user->first_name} {$user->last_name} {$makeCallIcon}</span>
                        <small class='text-muted'>{$user->email} {$sendMailIcon}</small>
                    </div>
                </div>";
                }
            }
            if (!function_exists('formatClientHtml')) {
                function formatClientHtml($client)
                {
                    if (!$client) {
                        return "-";
                    }

                    // Get the authenticated user
                    $authenticatedUser = getAuthenticatedUser();

                    // Get the guard name (web or client)
                    $guardName = getGuardName();

                    // Check if the authenticated user is the same as the client being displayed
                    if (
                        ($guardName === 'web' && $authenticatedUser->id === $client->id) ||
                        ($guardName === 'client' && $authenticatedUser->id === $client->id)
                    ) {
                        // Don't show the "Make Call" option if it's the logged-in client
                        $makeCallIcon = '';
                    } else {
                        // Check if the phone number or both phone and country code exist
                        $makeCallIcon = '';
                        if (!empty($client->phone) || (!empty($client->phone) && !empty($client->country_code))) {
                            $makeCallLink = 'tel:' . ($client->country_code ? $client->country_code . $client->phone : $client->phone);
                            $makeCallIcon = '<a href="' . $makeCallLink . '" class="text-decoration-none" title="' . get_label('make_call', 'Make Call') . '">
                                     <i class="bx bx-phone-call text-primary"></i>
                                   </a>';
                        }
                    }

                    // If the user has 'manage_clients' permission, return the full HTML with links
                    $profileLink = route('clients.profile', ['id' => $client->id]);
                    $photoUrl = $client->photo ? asset('storage/' . $client->photo) : asset('storage/photos/no-image.jpg');

                    // Create the Send Mail link
                    $sendMailLink = 'mailto:' . $client->email;
                    $sendMailIcon = '<a href="' . $sendMailLink . '" class="text-decoration-none" title="' . get_label('send_mail', 'Send Mail') . '">
                            <i class="bx bx-envelope text-primary"></i>
                          </a>';

                    return "<div class='d-flex justify-content-start align-items-center user-name'>
                    <div class='avatar-wrapper me-3'>
                        <div class='avatar avatar-sm pull-up'>
                            <a href='{$profileLink}'>
                                <img src='{$photoUrl}' alt='Photo' class='rounded-circle'>
                            </a>
                        </div>
                    </div>
                    <div class='d-flex flex-column'>
                        <span class='fw-semibold'>{$client->first_name} {$client->last_name} {$makeCallIcon}</span>
                        <small class='text-muted'>{$client->email} {$sendMailIcon}</small>
                    </div>
                </div>";
                }
            }
        }
    }
}

