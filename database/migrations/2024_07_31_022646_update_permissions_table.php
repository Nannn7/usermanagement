<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;
    use Modules\Usermanagement\Models\PermissionGroup;

    return new class extends Migration {
        /**
         * Run the migrations.
         */
        public function up()
        : void
        {
            Schema::table('permissions', function ($table) {
                $table->string('module')->after('id')->nullable();
                $table->foreignIdFor(PermissionGroup::class);
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down()
        : void
        {
            Schema::withoutForeignKeyConstraints(function () {
                Schema::table('permissions', function (Blueprint $table) {
                    $table->dropColumn('module');
                    $table->dropColumn('permission_group_id');
                });
            });
        }
    };
