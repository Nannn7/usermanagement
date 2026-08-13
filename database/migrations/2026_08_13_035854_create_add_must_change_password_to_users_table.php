<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Non-destructive: kolom baru default false, jadi user existing (yang sudah
     * pernah login & mengganti password sendiri) TIDAK ikut ke-flag wajib reset.
     * Flag ini hanya di-set true untuk user baru yang dibuat dengan password
     * default oleh admin, atau saat admin me-reset password user yang sudah ada
     * (lihat ApprovalRequestService::applyApprovedRelations()).
     */
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'must_change_password')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('must_change_password')->default(false)->after('password');
                $table->timestamp('password_changed_at')->nullable()->after('must_change_password');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['must_change_password', 'password_changed_at']);
        });
    }
};
