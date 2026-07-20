<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;
    use Modules\Basicdata\Models\Branch;

    return new class extends Migration {
        /**
         * Run the migrations.
         */
        public function up()
        : void
        {
            Schema::table('users', function (Blueprint $table) {
                $table->string('nik')->nullable()->after('email');
                $table->foreignIdFor(Branch::class)->nullable()->after('nik')->constrained('branches');
                $table->string('sign')->nullable()->after('branch_id');
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down()
        : void
        {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('sign');
                $table->dropColumn('nik');
                $table->dropForeign(['branch_id']);
                $table->dropColumn('branch_id');
            });
        }
    };
