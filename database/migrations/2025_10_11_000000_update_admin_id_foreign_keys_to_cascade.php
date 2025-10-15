<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
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
            'lead_forms',
            'lead_stages',
            'lead_sources',
            'candidates',
            'candidate_statuses',
            'interviews',
            'team_members',
            'task_lists'
        ];

        Schema::disableForeignKeyConstraints();

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'admin_id')) {

                $foreignKeys = DB::select("
                    SELECT kcu.CONSTRAINT_NAME
                    FROM information_schema.KEY_COLUMN_USAGE kcu
                    JOIN information_schema.TABLE_CONSTRAINTS tc
                    ON kcu.CONSTRAINT_NAME = tc.CONSTRAINT_NAME
                    AND kcu.TABLE_NAME = tc.TABLE_NAME
                    AND kcu.CONSTRAINT_SCHEMA = tc.CONSTRAINT_SCHEMA
                    WHERE kcu.TABLE_NAME = ?
                    AND kcu.COLUMN_NAME = 'admin_id'
                    AND tc.CONSTRAINT_TYPE = 'FOREIGN KEY'
                    AND kcu.CONSTRAINT_SCHEMA = DATABASE()
                    ", [$table]);

                foreach ($foreignKeys as $fk) {
                    DB::statement("ALTER TABLE `$table` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
                }

                Schema::table($table, function (Blueprint $t) {
                    $t->foreign('admin_id')
                        ->references('id')->on('admins')
                        ->onDelete('cascade');
                });
            }
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
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
            'lead_forms',
            'lead_stages',
            'lead_sources',
            'candidates',
            'candidate_statuses',
            'interviews',
            'team_members',
            'task_lists'
        ];

        Schema::disableForeignKeyConstraints();

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'admin_id')) {

                $foreignKeys = DB::select("
                    SELECT kcu.CONSTRAINT_NAME
                    FROM information_schema.KEY_COLUMN_USAGE kcu
                    JOIN information_schema.TABLE_CONSTRAINTS tc
                    ON kcu.CONSTRAINT_NAME = tc.CONSTRAINT_NAME
                    AND kcu.TABLE_NAME = tc.TABLE_NAME
                    AND kcu.CONSTRAINT_SCHEMA = tc.CONSTRAINT_SCHEMA
                    WHERE kcu.TABLE_NAME = ?
                    AND kcu.COLUMN_NAME = 'admin_id'
                    AND tc.CONSTRAINT_TYPE = 'FOREIGN KEY'
                    AND kcu.CONSTRAINT_SCHEMA = DATABASE()
                    ", [$table]);

                foreach ($foreignKeys as $fk) {
                    DB::statement("ALTER TABLE `$table` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
                }

                Schema::table($table, function (Blueprint $t) {
                    $t->unsignedBigInteger('admin_id')->nullable()->change();
                    $t->foreign('admin_id')
                        ->references('id')->on('admins')
                        ->onDelete('set null');
                });
            }
        }

        Schema::enableForeignKeyConstraints();
    }
};
