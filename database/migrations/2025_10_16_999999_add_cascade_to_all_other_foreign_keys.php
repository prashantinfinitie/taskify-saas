<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Define tables and their foreign keys that need cascade
        // Only including columns that DON'T already have cascade or have restrict
        $foreignKeyConfigs = [
            'allowance_payslip' => [
                // These have NO constraint currently
                ['column' => 'allowance_id', 'references' => 'id', 'on' => 'allowances', 'onDelete' => 'cascade'],
                ['column' => 'payslip_id', 'references' => 'id', 'on' => 'payslips', 'onDelete' => 'cascade'],
            ],
            'expenses' => [
                // user_id and workspace_id already have cascade, skip them
                // Only expense_type_id has NO constraint
                ['column' => 'expense_type_id', 'references' => 'id', 'on' => 'expense_types', 'onDelete' => 'cascade'],
            ],
            'payslips' => [
                // user_id and workspace_id already have cascade, skip them
                // Only payment_method_id has NO constraint
                ['column' => 'payment_method_id', 'references' => 'id', 'on' => 'payment_methods', 'onDelete' => 'cascade'],
            ],
            'contracts' => [
                // workspace_id, project_id, client_id already have cascade, skip them
                // Only contract_type_id has NO constraint
                ['column' => 'contract_type_id', 'references' => 'id', 'on' => 'contract_types', 'onDelete' => 'cascade'],
            ],
            'lead_forms' => [
                // workspace_id and created_by already have cascade, skip them
                // admin_id has NO constraint
                ['column' => 'admin_id', 'references' => 'id', 'on' => 'users', 'onDelete' => 'cascade'],
                // source_id, stage_id, assigned_to have restrict constraint
                ['column' => 'source_id', 'references' => 'id', 'on' => 'lead_sources', 'onDelete' => 'cascade'],
                ['column' => 'stage_id', 'references' => 'id', 'on' => 'lead_stages', 'onDelete' => 'cascade'],
                // assigned_to is handled in workspace migration, skip it
            ],
        ];

        foreach ($foreignKeyConfigs as $table => $foreignKeys) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            foreach ($foreignKeys as $fkConfig) {
                $column = $fkConfig['column'];

                // Skip if column doesn't exist
                if (!Schema::hasColumn($table, $column)) {
                    continue;
                }

                // Skip workspace_id as it's handled by the workspace migration
                if ($column === 'workspace_id') {
                    continue;
                }

                // Skip assigned_to in lead_forms as it's handled by workspace migration
                if ($table === 'lead_forms' && $column === 'assigned_to') {
                    continue;
                }

                // Clean up orphaned records before adding foreign key
                $this->cleanOrphanedRecords($table, $column, $fkConfig['on']);

                // Get existing foreign keys for this column
                $existingFKs = DB::select("
                    SELECT kcu.CONSTRAINT_NAME
                    FROM information_schema.KEY_COLUMN_USAGE kcu
                    JOIN information_schema.TABLE_CONSTRAINTS tc
                        ON kcu.CONSTRAINT_NAME = tc.CONSTRAINT_NAME
                        AND kcu.TABLE_NAME = tc.TABLE_NAME
                        AND kcu.CONSTRAINT_SCHEMA = tc.CONSTRAINT_SCHEMA
                    WHERE kcu.TABLE_NAME = ?
                      AND kcu.COLUMN_NAME = ?
                      AND tc.CONSTRAINT_TYPE = 'FOREIGN KEY'
                      AND kcu.CONSTRAINT_SCHEMA = DATABASE()
                ", [$table, $column]);

                // Drop existing foreign keys (if any)
                foreach ($existingFKs as $fk) {
                    DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
                }

                // Add new foreign key with cascade
                Schema::table($table, function (Blueprint $t) use ($fkConfig) {
                    $t->foreign($fkConfig['column'])
                        ->references($fkConfig['references'])
                        ->on($fkConfig['on'])
                        ->onDelete($fkConfig['onDelete']);
                });
            }
        }
    }

    /**
     * Clean up orphaned records that don't have valid foreign key references
     */
    private function cleanOrphanedRecords($table, $column, $referencedTable)
    {
        // Delete or nullify orphaned records
        DB::statement("
            DELETE FROM `{$table}`
            WHERE `{$column}` IS NOT NULL
            AND `{$column}` NOT IN (SELECT `id` FROM `{$referencedTable}`)
        ");
    }

    public function down()
    {
        // Restore original state - remove cascade where there was no constraint,
        // restore restrict where there was restrict
        $foreignKeyConfigs = [
            'allowance_payslip' => [
                // Originally had NO constraint - remove foreign key entirely
                ['column' => 'allowance_id', 'remove' => true],
                ['column' => 'payslip_id', 'remove' => true],
            ],
            'expenses' => [
                // Originally had NO constraint - remove foreign key entirely
                ['column' => 'expense_type_id', 'remove' => true],
            ],
            'payslips' => [
                // Originally had NO constraint - remove foreign key entirely
                ['column' => 'payment_method_id', 'remove' => true],
            ],
            'contracts' => [
                // Originally had NO constraint - remove foreign key entirely
                ['column' => 'contract_type_id', 'remove' => true],
            ],
            'lead_forms' => [
                // Originally had NO constraint - remove foreign key entirely
                ['column' => 'admin_id', 'remove' => true],
                // Originally had restrict - restore restrict
                ['column' => 'source_id', 'references' => 'id', 'on' => 'lead_sources', 'onDelete' => 'restrict'],
                ['column' => 'stage_id', 'references' => 'id', 'on' => 'lead_stages', 'onDelete' => 'restrict'],
            ],
        ];

        foreach ($foreignKeyConfigs as $table => $foreignKeys) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            foreach ($foreignKeys as $fkConfig) {
                $column = $fkConfig['column'];

                if (!Schema::hasColumn($table, $column)) {
                    continue;
                }

                // Skip workspace_id and assigned_to as they're handled elsewhere
                if ($column === 'workspace_id' || ($table === 'lead_forms' && $column === 'assigned_to')) {
                    continue;
                }

                // Get existing foreign keys
                $existingFKs = DB::select("
                    SELECT kcu.CONSTRAINT_NAME
                    FROM information_schema.KEY_COLUMN_USAGE kcu
                    JOIN information_schema.TABLE_CONSTRAINTS tc
                        ON kcu.CONSTRAINT_NAME = tc.CONSTRAINT_NAME
                        AND kcu.TABLE_NAME = tc.TABLE_NAME
                        AND kcu.CONSTRAINT_SCHEMA = tc.CONSTRAINT_SCHEMA
                    WHERE kcu.TABLE_NAME = ?
                      AND kcu.COLUMN_NAME = ?
                      AND tc.CONSTRAINT_TYPE = 'FOREIGN KEY'
                      AND kcu.CONSTRAINT_SCHEMA = DATABASE()
                ", [$table, $column]);

                // Drop cascade foreign keys
                foreach ($existingFKs as $fk) {
                    DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
                }

                // If should restore (not just remove), add it back
                if (!isset($fkConfig['remove']) || !$fkConfig['remove']) {
                    Schema::table($table, function (Blueprint $t) use ($fkConfig) {
                        $t->foreign($fkConfig['column'])
                            ->references($fkConfig['references'])
                            ->on($fkConfig['on'])
                            ->onDelete($fkConfig['onDelete']);
                    });
                }
            }
        }
    }
};
