<?php

namespace Tests;

use App\Models\Event;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Боевая схема БД написана под MySQL (ENUM, FULLTEXT, INFORMATION_SCHEMA),
        // поэтому штатные миграции несовместимы со SQLite. Для тестов таблицы
        // строятся напрямую переносимым построителем Schema на in-memory SQLite.
        $this->buildSchema();
        $this->seedReferenceData();

        // В тестовой среде отключаем ограничитель частоты запросов
        // (throttle:5,1 на маршрутах авторизации), чтобы он не мешал прогону.
        $this->withoutMiddleware(ThrottleRequests::class);
    }

    private function buildSchema(): void
    {
        Schema::create('roles', function ($t) {
            $t->integer('role_id', true);
            $t->string('name', 50);
        });

        Schema::create('statuses', function ($t) {
            $t->integer('status_id', true);
            $t->string('status_name', 50);
        });

        Schema::create('organization_types', function ($t) {
            $t->integer('type_id', true);
            $t->string('name', 50);
        });

        Schema::create('categories', function ($t) {
            $t->integer('id', true);
            $t->string('name', 50);
        });

        Schema::create('users', function ($t) {
            $t->integer('user_id', true);
            $t->string('last_name', 100)->nullable();
            $t->string('first_name', 100)->nullable();
            $t->string('patronymic', 100)->nullable();
            $t->date('date_of_birth')->nullable();
            $t->string('email', 190)->nullable();
            $t->string('phone', 20);
            $t->string('avatar', 500)->nullable();
            $t->integer('role_id')->nullable();
            $t->string('status', 20)->default('active');
            $t->unsignedTinyInteger('warning_count')->default(0);
            $t->dateTime('blocked_until')->nullable();
            $t->dateTime('restriction_until')->nullable();
            $t->string('password_hash', 255);
            $t->boolean('pd_consent')->default(false);
        });

        Schema::create('organization', function ($t) {
            $t->integer('organization_id', true);
            $t->string('full_name', 200);
            $t->text('address')->nullable();
            $t->string('inn', 12)->nullable();
            $t->string('ogrn', 15)->nullable();
            $t->string('kpp', 9)->nullable();
            $t->string('phone', 20)->nullable();
            $t->string('website', 300)->nullable();
            $t->integer('type_id')->nullable();
            $t->integer('status_id')->nullable();
            $t->text('rejection_reason')->nullable();
            $t->string('password_hash', 255);
            $t->string('email', 190)->nullable();
            $t->string('image', 500)->nullable();
            $t->text('description')->nullable();
            $t->string('vk', 300)->nullable();
            $t->boolean('pd_consent')->default(false);
        });

        Schema::create('venues', function ($t) {
            $t->integer('venue_id', true);
            $t->string('name', 50);
            $t->text('address')->nullable();
            $t->integer('category_id')->nullable();
            $t->tinyInteger('age')->nullable();
            $t->text('description')->nullable();
            $t->text('image')->nullable();
            $t->integer('organization_id')->nullable();
            $t->decimal('latitude', 10, 7)->nullable();
            $t->decimal('longitude', 10, 7)->nullable();
            $t->text('gallery')->nullable();
        });

        Schema::create('events', function ($t) {
            $t->integer('event_id', true);
            $t->string('title', 255);
            $t->text('description')->nullable();
            $t->tinyInteger('age_restriction')->nullable();
            $t->dateTime('start_datetime')->nullable();
            $t->dateTime('end_datetime')->nullable();
            $t->decimal('price', 10, 2)->default(0);
            $t->unsignedInteger('capacity')->nullable();
            $t->text('image')->nullable();
            $t->text('gallery')->nullable();
            $t->integer('organization_id')->nullable();
            $t->integer('venue_id')->nullable();
            $t->integer('category_id')->nullable();
            $t->integer('status_id')->default(4);
        });

        Schema::create('reviews', function ($t) {
            $t->integer('review_id', true);
            $t->integer('user_id');
            $t->text('text')->nullable();
            $t->unsignedTinyInteger('rating')->nullable();
            $t->dateTime('created_at')->nullable();
            $t->integer('event_id')->nullable();
            $t->integer('venue_id')->nullable();
        });

        Schema::create('favorites', function ($t) {
            $t->integer('favorite_id', true);
            $t->integer('user_id')->nullable();
            $t->integer('event_id')->nullable();
            $t->integer('venue_id')->nullable();
            $t->dateTime('created_at')->nullable();
        });

        Schema::create('tickets', function ($t) {
            $t->integer('ticket_id', true);
            $t->integer('user_id');
            $t->integer('event_id');
            $t->integer('quantity')->default(1);
            $t->decimal('price', 10, 2);
            $t->string('status', 20)->default('cart');
            $t->string('payment_method', 50)->nullable();
            $t->dateTime('paid_at')->nullable();
            $t->dateTime('created_at')->nullable();
        });

        Schema::create('personal_access_tokens', function ($t) {
            $t->bigIncrements('id');
            $t->string('tokenable_type');
            $t->unsignedBigInteger('tokenable_id');
            $t->text('name');
            $t->string('token', 64)->unique();
            $t->text('abilities')->nullable();
            $t->timestamp('last_used_at')->nullable();
            $t->timestamp('expires_at')->nullable();
            $t->timestamps();
            $t->index(['tokenable_type', 'tokenable_id']);
        });

        Schema::create('audit_logs', function ($t) {
            $t->bigIncrements('log_id');
            $t->unsignedInteger('actor_id')->nullable();
            $t->unsignedTinyInteger('actor_role')->nullable();
            $t->string('action', 50);
            $t->string('target_type', 20)->nullable();
            $t->unsignedInteger('target_id')->nullable();
            $t->text('details')->nullable();
            $t->dateTime('created_at')->nullable();
        });

        Schema::create('password_resets', function ($t) {
            $t->string('email')->index();
            $t->string('token');
            $t->string('type', 10)->default('user');
            $t->dateTime('created_at')->nullable();
        });

        Schema::create('promo_codes', function ($t) {
            $t->id();
            $t->string('code', 50)->unique();
            $t->string('discount_type', 10)->default('percent');
            $t->decimal('discount_value', 8, 2);
            $t->unsignedInteger('max_uses')->nullable();
            $t->unsignedInteger('uses_count')->default(0);
            $t->dateTime('expires_at')->nullable();
            $t->integer('organization_id')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('org_subscriptions', function ($t) {
            $t->id();
            $t->integer('user_id');
            $t->integer('organization_id');
            $t->dateTime('created_at')->nullable();
            $t->unique(['user_id', 'organization_id']);
        });
    }

    private function seedReferenceData(): void
    {
        DB::table('roles')->insert([
            ['role_id' => 1, 'name' => 'Администратор'],
            ['role_id' => 2, 'name' => 'Пользователь'],
            ['role_id' => 3, 'name' => 'Модератор'],
        ]);
        DB::table('statuses')->insert([
            ['status_id' => 1, 'status_name' => 'Активно'],
            ['status_id' => 2, 'status_name' => 'Завершено'],
            ['status_id' => 3, 'status_name' => 'Отменено'],
            ['status_id' => 4, 'status_name' => 'Ожидает подтверждения'],
        ]);
        DB::table('organization_types')->insert([
            ['type_id' => 1, 'name' => 'ООО'],
            ['type_id' => 2, 'name' => 'ИП'],
            ['type_id' => 3, 'name' => 'НКО'],
            ['type_id' => 4, 'name' => 'Другое'],
        ]);
        DB::table('categories')->insert([
            ['id' => 1, 'name' => 'Концерты'],
            ['id' => 2, 'name' => 'Выставки'],
            ['id' => 3, 'name' => 'Театр'],
        ]);
    }

    // ── Хелперы создания сущностей для тестов ───────────────────────────────

    protected function createUser(array $attrs = []): User
    {
        return User::create(array_merge([
            'first_name'    => 'Иван',
            'last_name'     => 'Тестов',
            'phone'         => '+7900' . str_pad((string) random_int(0, 9999999), 7, '0', STR_PAD_LEFT),
            'email'         => 'user' . random_int(1, 999999) . '@example.com',
            'password_hash' => Hash::make('123456'),
            'pd_consent'    => true,
            'role_id'       => 2,
            'status'        => 'active',
        ], $attrs));
    }

    protected function createOrg(array $attrs = []): Organization
    {
        return Organization::create(array_merge([
            'full_name'     => 'ООО Тест',
            'email'         => 'org' . random_int(1, 999999) . '@example.com',
            'password_hash' => Hash::make('12345678'),
            'pd_consent'    => true,
            'status_id'     => 1,
            'type_id'       => 1,
        ], $attrs));
    }

    protected function createEvent(array $attrs = []): Event
    {
        return Event::create(array_merge([
            'title'          => 'Тестовое событие',
            'description'    => 'Описание',
            'start_datetime' => now()->addDays(5),
            'end_datetime'   => now()->addDays(5)->addHours(2),
            'price'          => 500,
            'capacity'       => 100,
            'category_id'    => 1,
            'status_id'      => 1,
        ], $attrs));
    }
}
