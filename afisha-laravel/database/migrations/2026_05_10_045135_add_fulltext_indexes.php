<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE events ADD FULLTEXT ft_events (title, description)');
        DB::statement('ALTER TABLE venues ADD FULLTEXT ft_venues (name, description, address)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE events DROP INDEX ft_events');
        DB::statement('ALTER TABLE venues DROP INDEX ft_venues');
    }
};
