<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!DB::table('statuses')->where('status_id', 5)->exists()) {
            DB::table('statuses')->insert([
                'status_id'   => 5,
                'status_name' => 'Черновик',
                'status_type' => 'event',
            ]);
        }
    }

    public function down(): void
    {
        DB::table('statuses')->where('status_id', 5)->delete();
    }
};
