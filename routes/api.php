<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SubscriptionPlanController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProjectsController;
use App\Http\Controllers\TasksController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\IssueController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\MeetingsController;
use App\Http\Controllers\NotesController;
use App\Http\Controllers\PriorityController;
use App\Http\Controllers\TodosController;
use App\Http\Controllers\WorkspacesController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\AllowancesController;
use App\Http\Controllers\DeductionsController;
use App\Http\Controllers\PayslipsController;
use App\Http\Controllers\ContractsController;
use App\Http\Controllers\ExpensesController;
use App\Http\Controllers\TagsController;
use App\Models\Priority;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
//user Authentication
Route::post('/register', [UserController::class, 'register'])->middleware('isApi');
Route::post('/login', [UserController::class, 'authenticate'])->middleware('isApi');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->middleware('isApi');
Route::post('/reset-password', [ForgotPasswordController::class, 'api_resetPassword']);

//roles and permissions
Route::get('/roles/{id?}', [RolesController::class, 'apiRolesIndex']);
Route::get('/permissions/{id?}', [RolesController::class, 'apiPermissionList']);
//settings
Route::get('/settings/{variable}', [SettingsController::class, 'show']);





Route::prefix('master-panel')->middleware(['multiguard', 'custom-verified', 'check.subscription', 'subscription.modules'])->group(function () {
    // Dashboard
    Route::get('/dashboardList', [HomeController::class, 'getStatistics']);
    Route::get('/upcoming-birthdays', [HomeController::class, 'api_upcoming_birthdays']);
    Route::get('/upcoming-work-anniversaries', [HomeController::class, 'api_upcoming_work_anniversaries']);//pending data not shown
    Route::get('/members-on-leave', [HomeController::class, 'members_on_leave']);//error

    //projects managemant

    Route::middleware(['has_workspace', 'customcan:manage_projects'])->group(function () {
        Route::get('/projects/{id?}', [ProjectsController::class, 'apiList']);
        Route::post('/projects', [ProjectsController::class, 'store'])->middleware(['customcan:create_projects', 'log.activity'])->name('projects.store');
        Route::put('/projects/{id}', [ProjectsController::class, 'update'])->middleware(['customcan:edit_projects', 'log.activity']);
    });

    Route::delete('/projects/{id}', [ProjectsController::class, 'destroy'])->middleware(['customcan:delete_projects', 'demo_restriction', 'checkAccess:App\Models\Project,projects,id,projects', 'log.activity']);
    Route::delete('/destroy-multiple-projects', [ProjectsController::class, 'destroy_multiple'])->middleware(['customcan:delete_projects', 'demo_restriction', 'log.activity'])->name('projects.delete_multiple');
    Route::post('/update_favorite/{id}', [ProjectsController::class, 'update_favorite']);
    Route::post('/projects/{id}/duplicate', [ProjectsController::class, 'duplicate'])->middleware(['customcan:create_projects', 'checkAccess:App\Models\Project,projects,id,projects', 'log.activity', 'check.maxCreate'])->name('projects.duplicate');
    //media
    Route::middleware(['customcan:manage_media'])->group(function () {
        Route::post('/projects/upload-media', [ProjectsController::class, 'upload_media']);
        Route::get('/projects/{id}/media', [ProjectsController::class, 'get_media']);
        Route::delete('/media/{id}', [ProjectsController::class, 'delete_media']);
        Route::post('/media/delete-multiple', [ProjectsController::class, 'delete_multiple_media']);
    });
    //milestones
    Route::post('/create-milestone', [ProjectsController::class, 'store_milestone']);//give workspaceid in the header
    Route::get('/get-milestones/{id?}', [ProjectsController::class, 'api_milestones']);
    // Route::get('/get-milestone/{id}', [ProjectsController::class, 'get_milestone']);
    Route::put('/update-milestone/{id}', [ProjectsController::class, 'update_milestone']);
    Route::delete('/delete-milestone/{id}', [ProjectsController::class, 'delete_milestone']);
    //status and performance
    Route::post('/save-view-preference', [ProjectsController::class, 'saveViewPreference']);
    Route::put('/update-status/{id}', [ProjectsController::class, 'update_status'])->middleware(['customcan:edit_projects', 'log.activity'])->name('update-project-status');
    Route::put('/update-priority/{id}', [ProjectsController::class, 'update_priority'])->middleware(['customcan:edit_projects', 'log.activity'])->name('update-project-priority');
    //comments
    Route::POST('/project/comments', [ProjectsController::class, 'comments']);
    Route::get('/project/comments/{id}', [ProjectsController::class, 'get_comment']);
    Route::put('/project/comments/{id}', [ProjectsController::class, 'update_comment']);
    Route::delete('/project/comments/{id}', [ProjectsController::class, 'destroy_comment']);
    // Route::delete('/comment-attachment/{id}', [ProjectsController::class, 'destroy_comment_attachment']);//pending
    Route::post('/update-module-dates', [ProjectsController::class, 'update_module_dates']);
    //issues
   Route::post('projects/{project}/issues', [IssueController::class, 'store']);
   Route::put('projects/issues/{id}', [IssueController::class, 'update']);
   Route::delete('projects/issues/{id}', [IssueController::class, 'destroy']);

   Route::get('/projects/issues/{id?}', [IssueController::class, 'apiList']);

     //Tasks managemant
    Route::middleware(['has_workspace', 'customcan:manage_tasks'])->group(function () {
        Route::get('/tasks/list-api/{id?}', [TasksController::class, 'listApi']);
        Route::post('/create-tasks', [TasksController::class, 'store']);
        Route::put('/update-tasks/{id}', [TasksController::class, 'update']);
        Route::delete('/delete-tasks/{id}', [TasksController::class, 'destroy']);
        Route::delete('/destroy-multiple-tasks', [TasksController::class, 'destroy_multiple']);
        //status and performance
       Route::post('/task/{id}/status/{newStatus}', [TasksController::class, 'update_status']);

        Route::post('tasks/{id}/duplicate', [TasksController::class, 'duplicate']);
        Route::post('/tasks/save-view-preference', [TasksController::class, 'saveViewPreference']);
        Route::post('/tasks/update-priority', [TasksController::class, 'update_priority']);


        //media
        Route::post('/tasks/upload-media', [TasksController::class, 'upload_media']);
        Route::get('/tasks/{id}/media', [TasksController::class, 'get_media']);//remove the filter it is a work url get http://localhost:8000/api/master-panel/tasks/25/media
        Route::delete('/tasks/media/{id}', [TasksController::class, 'delete_media']);
        Route::post('/tasks/media/delete-multiple', [TasksController::class, 'delete_multiple_media']);

        //calender
        Route::get('/calendar/{workspaceId}', [TasksController::class, 'get_calendar_data']);

        //comment
        Route::post('/comments-create',[TasksController::class,'comments']);
        Route::get('/comments/{id}', [TasksController::class, 'get_comment']);
        Route::put('/comments/{id}', [TasksController::class, 'update_comment']);
        Route::delete('/comments/{id}', [TasksController::class, 'destroy_comment']);
    });
    //status
    Route::get('/status/{id?}', [StatusController::class, 'listapi']);
    Route::post('/statuses', [StatusController::class, 'store'])->middleware((['customcan:create_status']));
    Route::put('/statuses/{id}', [StatusController::class, 'update'])->middleware(['customcan:edit_status']);
    Route::delete('/statuses/{id}', [StatusController::class, 'destroy'])->middleware(['customcan:delete_status']);
    Route::delete('/destroy-multiple-statuses', [StatusController::class, 'destroy_multiple']);
    //client
    Route::middleware(['has_workspace', 'customcan:manage_clients'])->group(function () {
        Route::get('/clients/{id?}', [ClientController::class, 'apiList']);
        Route::post('/clients', [ClientController::class, 'store'])->middleware(['customcan:create_clients', 'log.activity']);//give eeror of the eail settings
        Route::put('/clients/{client}', [ClientController::class, 'update'])->middleware(['customcan:edit_clients', 'demo_restriction', 'log.activity']);
        Route::delete('/clients/{id}', [ClientController::class, 'destroy'])->middleware(['customcan:delete_clients', 'demo_restriction', 'log.activity']);
        Route::delete('/destroy-multiple-clients', [ClientController::class, 'destroy_multiple']);
    //priority
    });
    Route::middleware(['customcan:manage_priorities'])->group(function () {
    Route::post('/priorities', [PriorityController::class, 'store'])->middleware(['customcan:create_priorities', 'demo_restriction', 'log.activity']);
    Route::put('/priorities/{priority}', [PriorityController::class, 'update'])->middleware(['customcan:edit_priorities', 'demo_restriction', 'log.activity']);
    Route::delete('/priorities/{priority}', [PriorityController::class, 'destroy'])->middleware(['customcan:delete_priorities', 'demo_restriction', 'log.activity']);
    Route::get('/priorities/{id?}', [PriorityController::class, 'apilist']);
    Route::delete('/multiple-delete',[PriorityController::class,'destroy_multiple']);
    });
    //user
     Route::middleware(['has_workspace', 'customcan:manage_users'])->group(function () {
        Route::post('/user', [UserController::class, 'store'])->middleware(['customcan:create_users', 'log.activity']);
        Route::put('/user/{id}',[UserController::class,'update'])->middleware(['customcan:edit_users', 'demo_restriction', 'log.activity']);
        Route::delete('/user/{id}',[UserController::class,'delete_user'])->middleware(['customcan:delete_users', 'demo_restriction', 'log.activity']);
        Route::get('/user/{id?}',[UserController::class,'apiList']);
        });

    //workspace
     Route::middleware(['customcan:manage_workspaces'])->group(function () {
        Route::post('/workspace',[WorkspacesController::class,'store'])->middleware(['check.maxCreate', 'customcan:create_workspaces', 'log.activity']);
        Route::put('/workspace/{id}',[WorkspacesController::class,'update'])->middleware(['customcan:edit_workspaces', 'demo_restriction', 'checkAccess:App\Models\Workspace,workspaces,id,workspaces', 'log.activity']);
        Route::delete('/workspace/{id}',[WorkspacesController::class,'destroy'])->middleware(['customcan:delete_workspaces', 'demo_restriction', 'checkAccess:App\Models\Workspace,workspaces,id,workspaces', 'log.activity']);
        Route::get('/workspace/{id?}',[workspacesController::class,'listapi']);
     });

    // tags
     Route::get('/tags/{id?}', [TagsController::class, 'apiList']);
     Route::post('/tags/store', [TagsController::class, 'store'])->middleware(['customcan:create_tags', 'log.activity']);
     Route::put('/tags/{id}', [TagsController::class, 'update'])->middleware(['customcan:edit_tags', 'log.activity']);
     Route::delete('/tags/{id}', [TagsController::class, 'destroy'])->middleware(['customcan:delete_tags', 'log.activity']);


     //todo
      Route::middleware(['has_workspace'])->group(function () {
        Route::post('/todo',[TodosController::class,'store'])->middleware(['log.activity']);
        Route::put('/todo/{id}',[TodosController::class,'update'])->middleware(['log.activity']);
        Route::delete('/todo/{id}',[TodosController::class,'destroy'])->middleware(['demo_restriction', 'log.activity']);
         Route::get('/todo/{id?}',[TodosController::class,'apilist']);
         Route::post('todo/update-status', [TodosController::class, 'update_status']);
         Route::get('/todo/{id?}',[TodosController::class,'listapi']);

      });
    //meetings
       Route::middleware(['has_workspace', 'customcan:manage_meetings'])->group(function () {
        Route::post('/meeting',[MeetingsController::class,'store'])->middleware(['customcan:create_meetings', 'log.activity']);
        Route::get('/meeting/{id?}',[MeetingsController::class,'listapi']);
        Route::put('/meeting/{id}',[MeetingsController::class,'update'])->middleware(['customcan:edit_meetings', 'checkAccess:App\Models\Meeting,meetings,id,meetings', 'log.activity']);
        Route::delete('/meeting/{id}',[MeetingsController::class,'destroy'])   ->middleware(['customcan:delete_meetings', 'demo_restriction', 'checkAccess:App\Models\Meeting,meetings,id,meetings', 'log.activity']);

       });
    //note
       Route::post('/note',[NotesController::class,'store'])->middleware('log.activity');
       Route::put('/note/{id}',[NotesController::class,'api_update'])->middleware('log.activity');
       Route::delete('/note/{id}',[NotesController::class,'api_destroy']);
       Route::get('/note/{id?}',[NotesController::class,'apilist']);
    //leave request
    Route::middleware(['admin_or_user'])->group(function () {
        Route::post('/leaverequest',[LeaveRequestController::class,'api_store']);
        Route::get('/leaverequest/{id?}',[LeaveRequestController::class,'listapi']);
        Route::put('/leaverequest/{id}',[LeaveRequestController::class,'update']);
        Route::delete('/leaverequest/{id}',[LeaveRequestController::class,'destroy']);

    });
    //Activity log
    Route::get('/activity-log/{id?}',[ActivityLogController::class,'listapi']);
    Route::delete('/activity-log/{id}',[ActivityLogController::class,'destroy'])->middleware(['demo_restriction', 'customcan:delete_activity_log']);
    //notifications
    Route::get('/notification/{id?}',[NotificationsController::class,'apiList']);
    Route::delete('/notification/{id}',[NotificationsController::class,'destroy']);
    Route::patch('/notifications/mark-as-read/{id?}', [NotificationsController::class, 'markAllAsReadApi']);


    //contracts
    Route::middleware(['has_workspace', 'customcan:manage_contracts'])->group(function () {
        Route::get('/contracts/{id?}', [ContractsController::class, 'listApi']);
        Route::post('/contracts', [ContractsController::class, 'store'])->middleware(['customcan:create_contracts', 'log.activity']);
        Route::put('/contracts/{id}', [ContractsController::class, 'update'])->middleware(['customcan:edit_contracts', 'log.activity']);
        Route::delete('/contracts/{id}', [ContractsController::class, 'destroy'])->middleware(['customcan:delete_contracts', 'log.activity']);

        //SIGNATURES PENDING....................

    //contract types
        Route::post('contract-types/store', [ContractsController::class, 'store_contract_type']);
        Route::get('contract-types/list', [ContractsController::class, 'contract_types_list_api']);
        Route::delete('/contract-types/{id}', [ContractsController::class, 'delete_contract_type'])->middleware(['customcan:delete_contracts', 'log.activity']);
        Route::put('/contracts-types/{id}', [ContractsController::class, 'update_contract_type']);

    });
    //Allowance
     Route::middleware(['customcan:manage_allowances'])->group(function () {

        Route::post('/allowances', [AllowancesController::class, 'store']);
        Route::get('/allowances/{id?}', [AllowancesController::class, 'apiList']);
        Route::delete('/allowances/{id}', [AllowancesController::class, 'destroy'])->middleware(['customcan:delete_allowances', 'demo_restriction', 'log.activity']);
        Route::put('/allowances/{id}', [AllowancesController::class, 'update']);
            });
    // Deductions
    Route::middleware(['customcan:manage_deductions'])->group(function () {
        Route::post('/deductions', [DeductionsController::class, 'store'])->middleware(['customcan:create_deductions', 'log.activity']);
        Route::get('/deductions/{id?}', [DeductionsController::class, 'listApi']);
        Route::put('/deductions/{id}', [DeductionsController::class, 'update'])->middleware(['customcan:edit_deductions', 'log.activity']);
        Route::delete('/deductions/{id}', [DeductionsController::class, 'destroy'])->middleware(['customcan:delete_deductions', 'demo_restriction', 'log.activity']);

        });

    //Payslips
    Route::middleware(['customcan:manage_payslips'])->group(function () {
        Route::post('/payslips', [PayslipsController::class, 'store'])->middleware(['customcan:create_payslips', 'log.activity']);
        Route::get('/payslips/{id?}', [PayslipsController::class, 'listApi']);
        Route::put('/payslips/{id}', [PayslipsController::class, 'update'])->middleware(['customcan:edit_payslips', 'log.activity']);
        Route::delete('/payslips/{id}', [PayslipsController::class, 'destroy'])->middleware(['customcan:delete_payslips', 'demo_restriction', 'log.activity']);
        });

    //Expenses
     Route::middleware(['customcan:manage_expenses'])->group(function () {
        Route::post('/expenses', [ExpensesController::class, 'store'])->middleware(['customcan:create_expenses', 'log.activity']);
        Route::get('/expenses/{id?}', [ExpensesController::class, 'apiList']);
        Route::put('/expenses/{id}', [ExpensesController::class, 'update'])->middleware(['customcan:edit_expenses', 'log.activity']);
        Route::delete('/expenses/{id}', [ExpensesController::class, 'destroy'])->middleware(['customcan:delete_expenses', 'demo_restriction', 'checkAccess:App\Models\Expense,expenses,id,expenses', 'log.activity']);
        });

    //Expenses Types
     Route::middleware(['customcan:manage_expense_types'])->group(function () {
        Route::post('/expense-type', [ExpensesController::class, 'store_expense_type'])->middleware(['customcan:create_expense_types', 'log.activity']);
        Route::put('/expense-type/{id}', [ExpensesController::class, 'update_expense_type'])->middleware(['customcan:edit_expense_types', 'log.activity']);
        Route::delete('/expense-type/{id}', [ExpensesController::class, 'delete_expense_type'])->middleware(['customcan:delete_system_notifications', 'demo_restriction']);
        Route::get('/expense-type/{id?}', [ExpensesController::class, 'Api_expense_types_list']);


         });        
 });
