<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('permissions') || !Schema::hasColumn('permissions', 'permission_group_id')) {
            return;
        }

        if ($this->foreignKeyExists('permissions', 'permissions_permission_group_id_foreign')) {
            return;
        }

        Schema::table('permissions', function (Blueprint $table) {
            $table->foreign('permission_group_id', 'permissions_permission_group_id_foreign')
                ->references('id')
                ->on('permission_groups');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('permissions')) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->dropForeign('permissions_permission_group_id_foreign');
            });
        }
    }

    private function foreignKeyExists(string $table, string $name): bool
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            $sql = "select 1 from information_schema.table_constraints where table_schema = current_schema() and table_name = ? and constraint_name = ? limit 1";
            return !empty(DB::select($sql, [$table, $name]));
        }

        if ($driver === 'mysql' || $driver === 'mariadb') {
            $dbName = DB::connection()->getDatabaseName();
            $sql = "select 1 from information_schema.table_constraints where table_schema = ? and table_name = ? and constraint_name = ? limit 1";
            return !empty(DB::select($sql, [$dbName, $table, $name]));
        }

        return false;
    }
};
