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
            'projects',
            'tasks',
            'meetings',
            'todos',
            'notes',
            'leave_requests',
            'payslips',
            'allowances',
            'deductions',
            'time_trackers',
            'taxes',
            'units',
            'items',
            'expenses',
            'payments',
            'activity_logs',
            'leads',
            'lead_stages',
            'lead_sources',
            'scheduled_emails',
            'email_templates',
            'candidates',
            'candidate_statuses',
            'interviews',
            'contract_types',
            'expense_types',
            'lead_forms'

        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'workspace_id')) {
                Schema::table($table, function (Blueprint $t) use ($table) {
                    // Drop any existing FK(s) on workspace_id
                    $foreignKeys = DB::select("
                        SELECT CONSTRAINT_NAME
                        FROM information_schema.KEY_COLUMN_USAGE
                        WHERE TABLE_NAME = ? AND COLUMN_NAME = 'workspace_id' AND CONSTRAINT_SCHEMA = DATABASE()
                    ", [$table]);

                    foreach ($foreignKeys as $fk) {
                        $t->dropForeign($fk->CONSTRAINT_NAME);
                    }

                    // Add new FK with cascade delete
                    $t->foreign('workspace_id')
                        ->references('id')->on('workspaces')
                        ->onDelete('cascade');
                });
            }
        }
    }

    public function down()
    {
        $tables = [
            'projects',
            'tasks',
            'meetings',
            'todos',
            'notes',
            'leave_requests',
            'payslips',
            'allowances',
            'deductions',
            'time_trackers',
            'taxes',
            'units',
            'items',
            'payments',
            'activity_logs',
            'leads',
            'lead_stages',
            'lead_sources',
            'scheduled_emails',
            'email_templates',
            'candidates',
            'candidate_statuses',
            'interviews',
            'contract_types',
            'expense_types',
            'lead_forms'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'workspace_id')) {
                Schema::table($table, function (Blueprint $t) use ($table) {
                    // Drop the cascade foreign key (if exists)
                    $foreignKeys = DB::select("
                        SELECT CONSTRAINT_NAME
                        FROM information_schema.KEY_COLUMN_USAGE
                        WHERE TABLE_NAME = ? AND COLUMN_NAME = 'workspace_id' AND CONSTRAINT_SCHEMA = DATABASE()
                    ", [$table]);

                    foreach ($foreignKeys as $fk) {
                        $t->dropForeign($fk->CONSTRAINT_NAME);
                    }

                    // Recreate FK without cascade (set null or restrict as per preference)
                    $t->foreign('workspace_id')
                        ->references('id')->on('workspaces')
                        ->onDelete('set null');
                });
            }
        }
    }
};
