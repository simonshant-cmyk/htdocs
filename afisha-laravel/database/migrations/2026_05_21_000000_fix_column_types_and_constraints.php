<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. users.email: text → varchar(190) + unique index ──────────────
        // Nullify duplicate emails — keep lowest user_id, clear the rest
        DB::statement('
            UPDATE users u1
            JOIN users u2 ON u1.email = u2.email AND u1.user_id > u2.user_id
            SET u1.email = NULL
            WHERE u1.email IS NOT NULL
        ');
        Schema::table('users', function (Blueprint $table) {
            $table->string('email', 190)->nullable()->change();
        });
        $this->addUniqueIfMissing('users', 'uq_users_email', 'email');

        // ── 2. organization.email: text → varchar(190) + unique index ────────
        Schema::table('organization', function (Blueprint $table) {
            $table->string('email', 190)->nullable()->change();
        });
        $this->addUniqueIfMissing('organization', 'uq_org_email', 'email');

        // ── 3. organization.inn: text → varchar(12) ──────────────────────────
        Schema::table('organization', function (Blueprint $table) {
            $table->string('inn', 12)->nullable()->change();
        });

        // ── 4. tickets.status: varchar → enum ────────────────────────────────
        DB::statement("ALTER TABLE tickets MODIFY COLUMN status ENUM('cart','paid','return_pending','returned') NOT NULL DEFAULT 'cart'");

        // ── 5. reviews.rating: int → tinyint unsigned (1–5) ─────────────────
        DB::statement('ALTER TABLE reviews MODIFY COLUMN rating TINYINT UNSIGNED NULL');

        // ── 6. events.status_id: nullable → NOT NULL DEFAULT 4 (pending) ────
        DB::statement('ALTER TABLE events DROP FOREIGN KEY events_ibfk_4');
        DB::statement('ALTER TABLE events MODIFY COLUMN status_id INT NOT NULL DEFAULT 4');
        DB::statement('ALTER TABLE events ADD CONSTRAINT events_ibfk_4 FOREIGN KEY (status_id) REFERENCES statuses(status_id)');

        // ── 7. reviews.user_id: nullable → NOT NULL ──────────────────────────
        DB::statement('ALTER TABLE reviews MODIFY COLUMN user_id INT NOT NULL');

        // ── 8. favorites: unique composite indexes ───────────────────────────
        // Remove duplicate rows first (keep lowest favorite_id)
        DB::statement('
            DELETE f1 FROM favorites f1
            INNER JOIN favorites f2
            WHERE f1.favorite_id > f2.favorite_id
              AND f1.user_id = f2.user_id
              AND f1.event_id IS NOT NULL AND f1.event_id = f2.event_id
        ');
        DB::statement('
            DELETE f1 FROM favorites f1
            INNER JOIN favorites f2
            WHERE f1.favorite_id > f2.favorite_id
              AND f1.user_id = f2.user_id
              AND f1.venue_id IS NOT NULL AND f1.venue_id = f2.venue_id
        ');

        $this->addUniqueIfMissing('favorites', 'uq_fav_user_event', ['user_id', 'event_id']);
        $this->addUniqueIfMissing('favorites', 'uq_fav_user_venue', ['user_id', 'venue_id']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('email')->nullable()->change();
            $table->dropIndex('uq_users_email');
        });

        Schema::table('organization', function (Blueprint $table) {
            $table->text('email')->nullable()->change();
            $table->dropIndex('uq_org_email');
            $table->text('inn')->nullable()->change();
        });

        DB::statement("ALTER TABLE tickets MODIFY COLUMN status VARCHAR(20) NULL DEFAULT 'cart'");
        DB::statement('ALTER TABLE reviews MODIFY COLUMN rating INT NULL');
        DB::statement('ALTER TABLE events DROP FOREIGN KEY events_ibfk_4');
        DB::statement('ALTER TABLE events MODIFY COLUMN status_id INT NULL');
        DB::statement('ALTER TABLE events ADD CONSTRAINT events_ibfk_4 FOREIGN KEY (status_id) REFERENCES statuses(status_id)');
        DB::statement('ALTER TABLE reviews MODIFY COLUMN user_id INT NULL');

        Schema::table('favorites', function (Blueprint $table) {
            $table->dropIndex('uq_fav_user_event');
            $table->dropIndex('uq_fav_user_venue');
        });
    }

    private function addUniqueIfMissing(string $table, string $indexName, string|array $columns): void
    {
        $cols    = is_array($columns) ? implode(',', $columns) : $columns;
        $exists  = DB::select("
            SELECT COUNT(*) as cnt
            FROM INFORMATION_SCHEMA.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND INDEX_NAME = ?
        ", [$table, $indexName]);

        if (($exists[0]->cnt ?? 0) === 0) {
            DB::statement("ALTER TABLE `{$table}` ADD UNIQUE INDEX `{$indexName}` ({$cols})");
        }
    }
};
