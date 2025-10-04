<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdminIdToMultipleTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Define an array of tables to add the admin_id column
        $tables = [
            'activity_logs',
            'allowances',
            'allowance_payslip',
            'clients',
            'client_meeting',
            'client_project',
            'client_workspace',
            'contracts',
            'contract_types',
            'deductions',
            'deduction_payslip',
            'leave_editors',
            'leave_requests',
            'meetings',
            'meeting_user',
            'notes',
            'payment_methods',
            'payslips',
            'projects',
            'project_tag',
            'project_user',
            'statuses',
            'tags',
            'tasks',
            'task_user',
            'time_trackers',
            'todos',
            'user_workspace',
            'workspaces',
        ];

        // Loop through each table and add the admin_id column
        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                if (!Schema::hasColumn($table->getTable(), 'admin_id')) {
                    $table->unsignedBigInteger('admin_id')->nullable()->after('id');;
                    $table->foreign('admin_id')->references('id')->on('admins')->onDelete('set null');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Define an array of tables to drop the admin_id column and foreign key constraint
        $tables = [
            'activity_logs',
            'allowances',
            'allowance_payslip',
            'clients',
            'client_meeting',
            'client_project',
            'client_workspace',
            'contracts',
            'contract_types',
            'deductions',
            'deduction_payslip',
            'leave_editors',
            'leave_requests',
            'meetings',
            'meeting_user',
            'notes',
            'payment_methods',
            'payslips',
            'projects',
            'project_tag',
            'project_user',
            'statuses',
            'tags',
            'tasks',
            'task_user',
            'time_trackers',
            'todos',
            'user_workspace',
            'workspaces',
        ];

        // Loop through each table and drop the admin_id column and foreign key constraint
        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                if (Schema::hasColumn($table->getTable(), 'admin_id')) {
                    $table->dropForeign(['admin_id']);
                    $table->dropColumn('admin_id');
                }
            });
        }
    }
}
