<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('org_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('organization_id');
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['user_id', 'organization_id']);
            $table->index('user_id');
            $table->index('organization_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('org_subscriptions');
    }
};
