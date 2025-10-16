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
            'lead_forms',
            'clients',
            'deduction_payslip',
            'payment_methods',
            'project_tag',
            'project_user',
            'status',
            'tags',
            'task_list',
            'task_user',
            'lead_form_fields',
            'estimates_invoice_item'
        ];

        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $t) use ($table) {
                // Add workspace_id column if missing
                if (!Schema::hasColumn($table, 'workspace_id')) {
                    $t->unsignedBigInteger('workspace_id')->nullable()->after('id');
                }
            });

            // Drop any existing FK(s) on workspace_id
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_NAME = ? AND COLUMN_NAME = 'workspace_id' AND CONSTRAINT_SCHEMA = DATABASE()
            ", [$table]);

            Schema::table($table, function (Blueprint $t) use ($foreignKeys) {
                foreach ($foreignKeys as $fk) {
                    $t->dropForeign($fk->CONSTRAINT_NAME);
                }
            });

            // Add new FK with cascade delete
            Schema::table($table, function (Blueprint $t) {
                $t->foreign('workspace_id')
                    ->references('id')
                    ->on('workspaces')
                    ->onDelete('cascade');
            });
        }


        // Override assigned_to constraint separately ---
        if (Schema::hasTable('lead_forms') && Schema::hasColumn('lead_forms', 'assigned_to')) {

            // Drop the existing constraint
            $foreignKeys = DB::select("
            SELECT kcu.CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE kcu
            JOIN information_schema.TABLE_CONSTRAINTS tc
                ON kcu.CONSTRAINT_NAME = tc.CONSTRAINT_NAME
                AND kcu.TABLE_NAME = tc.TABLE_NAME
                AND kcu.CONSTRAINT_SCHEMA = tc.CONSTRAINT_SCHEMA
            WHERE kcu.TABLE_NAME = 'lead_forms'
              AND kcu.COLUMN_NAME = 'assigned_to'
              AND tc.CONSTRAINT_TYPE = 'FOREIGN KEY'
              AND kcu.CONSTRAINT_SCHEMA = DATABASE()");

            foreach ($foreignKeys as $fk) {
                DB::statement("ALTER TABLE `lead_forms` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
            }

            Schema::table('lead_forms', function (Blueprint $table) {
                $table->foreign('assigned_to')
                    ->references('id')->on('users')
                    ->onDelete('cascade');
            });
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
            'lead_forms',
            'clients',
            'deduction_payslip',
            'payment_methods',
            'project_tag',
            'project_user',
            'status',
            'tags',
            'task_list',
            'task_user',
            'lead_form_fields',
            'estimates_invoice_item'
        ];

        foreach ($tables as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'workspace_id')) {
                continue;
            }

            // Drop cascade FK
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_NAME = ? AND COLUMN_NAME = 'workspace_id' AND CONSTRAINT_SCHEMA = DATABASE()
            ", [$table]);

            Schema::table($table, function (Blueprint $t) use ($foreignKeys) {
                foreach ($foreignKeys as $fk) {
                    $t->dropForeign($fk->CONSTRAINT_NAME);
                }

                // Recreate FK as SET NULL
                $t->foreign('workspace_id')
                    ->references('id')
                    ->on('workspaces')
                    ->onDelete('set null');
            });
        }


        // Override assigned_to constraint separately ---
        if (Schema::hasTable('lead_forms') && Schema::hasColumn('lead_forms', 'assigned_to')) {

            // Drop the existing constraint
            $foreignKeys = DB::select("
            SELECT kcu.CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE kcu
            JOIN information_schema.TABLE_CONSTRAINTS tc
                ON kcu.CONSTRAINT_NAME = tc.CONSTRAINT_NAME
                AND kcu.TABLE_NAME = tc.TABLE_NAME
                AND kcu.CONSTRAINT_SCHEMA = tc.CONSTRAINT_SCHEMA
               WHERE kcu.TABLE_NAME = 'lead_forms'
              AND kcu.COLUMN_NAME = 'assigned_to'
              AND tc.CONSTRAINT_TYPE = 'FOREIGN KEY'
              AND kcu.CONSTRAINT_SCHEMA = DATABASE()");

            foreach ($foreignKeys as $fk) {
                DB::statement("ALTER TABLE `lead_forms` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
            }

            Schema::table('lead_forms', function (Blueprint $table) {
                $table->foreign('assigned_to')
                    ->references('id')->on('users')
                    ->onDelete('restrict');
            });
        }
    }
};
