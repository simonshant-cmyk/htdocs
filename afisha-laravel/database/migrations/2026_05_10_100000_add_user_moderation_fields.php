<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('status', ['active', 'warned', 'restricted', 'blocked'])->default('active')->after('role_id');
            $table->unsignedTinyInteger('warning_count')->default(0)->after('status');
            $table->dateTime('blocked_until')->nullable()->after('warning_count');
            $table->dateTime('restriction_until')->nullable()->after('blocked_until');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['status', 'warning_count', 'blocked_until', 'restriction_until']);
        });
    }
};
