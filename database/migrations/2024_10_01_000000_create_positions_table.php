<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tableNames = config('permission.table_names');

        if (empty($tableNames)) {
            throw new \Exception('Error: config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        }

        Schema::create('positions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code')->unique();
            $table->string('name');
            $table->integer('level');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table($tableNames['roles'], function (Blueprint $table) {
            $table->unsignedBigInteger('position_id')->nullable()->after('guard_name');
            $table->foreign('position_id')
                ->references('id')
                ->on('positions')
                ->nullOnDelete();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('position_id')->nullable()->after('sign');
            $table->foreign('position_id')
                ->references('id')
                ->on('positions')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');

        if (!empty($tableNames) && Schema::hasTable($tableNames['roles']) && Schema::hasColumn($tableNames['roles'], 'position_id')) {
            Schema::table($tableNames['roles'], function (Blueprint $table) {
                $table->dropForeign(['position_id']);
                $table->dropColumn('position_id');
            });
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'position_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['position_id']);
                $table->dropColumn('position_id');
            });
        }

        Schema::dropIfExists('positions');
    }
};
