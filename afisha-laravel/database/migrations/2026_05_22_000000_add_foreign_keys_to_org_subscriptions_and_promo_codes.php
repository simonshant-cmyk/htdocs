<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('org_subscriptions', function (Blueprint $table) {
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('organization_id')->references('organization_id')->on('organization')->onDelete('cascade');
        });

        Schema::table('promo_codes', function (Blueprint $table) {
            $table->foreign('organization_id')->references('organization_id')->on('organization')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('org_subscriptions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['organization_id']);
        });

        Schema::table('promo_codes', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
        });
    }
};
