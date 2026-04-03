<?php

use Cooper\FilamentDcatFilters\Http\Controllers\ModalSelectController;
use Illuminate\Foundation\Auth\User;
use Illuminate\Routing\Controller;

// ---------------------------------------------------------------------------
// Structure tests (kept from original)
// ---------------------------------------------------------------------------

describe('ModalSelectController Structure', function () {
    it('can be instantiated', function () {
        $controller = new ModalSelectController;

        expect($controller)->toBeInstanceOf(ModalSelectController::class);
    });

    it('has fetchLabels method', function () {
        expect(method_exists(ModalSelectController::class, 'fetchLabels'))->toBeTrue();
    });

    it('is a controller class', function () {
        $controller = new ModalSelectController;

        expect($controller)->toBeInstanceOf(Controller::class);
    });

    it('has correct namespace', function () {
        $reflection = new ReflectionClass(ModalSelectController::class);

        expect($reflection->getNamespaceName())->toBe('Cooper\\FilamentDcatFilters\\Http\\Controllers');
    });

    it('fetchLabels accepts Request parameter', function () {
        $reflection = new ReflectionClass(ModalSelectController::class);
        $method = $reflection->getMethod('fetchLabels');
        $parameters = $method->getParameters();

        expect($parameters)->toHaveCount(1);
        expect($parameters[0]->getName())->toBe('request');
    });

    it('fetchLabels returns JsonResponse', function () {
        $reflection = new ReflectionClass(ModalSelectController::class);
        $method = $reflection->getMethod('fetchLabels');
        $returnType = $method->getReturnType();

        expect($returnType)->not->toBeNull();
        expect($returnType->getName())->toBe('Illuminate\\Http\\JsonResponse');
    });
});

// ---------------------------------------------------------------------------
// HTTP request tests
// ---------------------------------------------------------------------------

describe('POST /filament-dcat-filters/fetch-labels', function () {
    /**
     * Return an authenticated user instance without needing a real database row.
     */
    beforeEach(function () {
        $user = new User;
        $user->id = 1;
        $user->name = 'Test User';
        $user->email = 'test@example.com';

        $this->actingAs($user);
    });

    describe('Validation (400)', function () {
        it('returns 400 when model field is missing', function () {
            $this->postJson('/filament-dcat-filters/fetch-labels', [
                'ids' => [1],
                'column' => 'name',
            ])->assertStatus(400)->assertJsonFragment(['error' => 'Invalid request']);
        });

        it('returns 400 when ids field is missing', function () {
            $this->postJson('/filament-dcat-filters/fetch-labels', [
                'model' => 'App\\Models\\User',
                'column' => 'name',
            ])->assertStatus(400)->assertJsonFragment(['error' => 'Invalid request']);
        });

        it('returns 400 when ids is not an array', function () {
            $this->postJson('/filament-dcat-filters/fetch-labels', [
                'model' => 'App\\Models\\User',
                'ids' => 'not-an-array',
                'column' => 'name',
            ])->assertStatus(400)->assertJsonFragment(['error' => 'Invalid request']);
        });

        it('returns 400 when column field is missing', function () {
            $this->postJson('/filament-dcat-filters/fetch-labels', [
                'model' => 'App\\Models\\User',
                'ids' => [1],
            ])->assertStatus(400)->assertJsonFragment(['error' => 'Invalid request']);
        });

        it('returns 400 when all fields are missing', function () {
            $this->postJson('/filament-dcat-filters/fetch-labels', [])
                ->assertStatus(400)
                ->assertJsonFragment(['error' => 'Invalid request'])
                ->assertJsonFragment(['labels' => []]);
        });

        it('returns 400 when column contains invalid characters', function () {
            $this->postJson('/filament-dcat-filters/fetch-labels', [
                'model' => 'App\\Models\\User',
                'ids' => [1],
                'column' => 'name; DROP TABLE users--',
            ])->assertStatus(400)->assertJsonFragment(['error' => 'Invalid request']);
        });
    });

    describe('Invalid model class (400)', function () {
        it('returns 400 when model class does not exist', function () {
            config(['filament-dcat-filters.allowed_models' => ['NonExistent\\Model\\Foo']]);

            $this->postJson('/filament-dcat-filters/fetch-labels', [
                'model' => 'NonExistent\\Model\\Foo',
                'ids' => [1],
                'column' => 'name',
            ])->assertStatus(400)->assertJsonFragment(['error' => 'Invalid model class']);
        });

        it('returns 400 when model is not an Eloquent model', function () {
            config(['filament-dcat-filters.allowed_models' => ['stdClass']]);

            $this->postJson('/filament-dcat-filters/fetch-labels', [
                'model' => 'stdClass',
                'ids' => [1],
                'column' => 'name',
            ])->assertStatus(400)->assertJsonFragment(['error' => 'Invalid model class']);
        });
    });

    describe('Authorization (403)', function () {
        it('returns 403 when allowed_models config is empty', function () {
            config(['filament-dcat-filters.allowed_models' => []]);

            $this->postJson('/filament-dcat-filters/fetch-labels', [
                'model' => User::class,
                'ids' => [1],
                'column' => 'name',
            ])->assertStatus(403)->assertJsonFragment(['error' => 'No models configured']);
        });

        it('returns 403 when model is not in the allowed list', function () {
            config(['filament-dcat-filters.allowed_models' => ['App\\Models\\Post']]);

            $this->postJson('/filament-dcat-filters/fetch-labels', [
                'model' => User::class,
                'ids' => [1],
                'column' => 'name',
            ])->assertStatus(403)->assertJsonFragment(['error' => 'Model not allowed']);
        });
    });

    describe('Authentication middleware', function () {
        it('returns 401 when not authenticated', function () {
            // Log out so there is no authenticated user for this request
            $this->app['auth']->logout();

            // postJson sends Accept: application/json so Laravel returns 401 (not a redirect)
            $this->postJson('/filament-dcat-filters/fetch-labels', [
                'model' => User::class,
                'ids' => [1],
                'column' => 'name',
            ])->assertUnauthorized();
        });

        it('is guarded by the auth middleware', function () {
            $route = $this->app['router']->getRoutes()->getByName('filament-dcat-filters.fetch-labels');

            expect($route)->not->toBeNull();
            expect($route->middleware())->toContain('auth');
        });
    });

    describe('Successful response (200)', function () {
        it('returns 200 with labels when model is allowed and db returns results', function () {
            // Use the in-memory SQLite DB to create a users table and seed a row
            $this->app['db']->statement('CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT NOT NULL,
                password TEXT NOT NULL,
                remember_token TEXT,
                email_verified_at DATETIME,
                created_at DATETIME,
                updated_at DATETIME
            )');

            $this->app['db']->table('users')->insert([
                'name' => 'Alice',
                'email' => 'alice@example.com',
                'password' => bcrypt('password'),
            ]);

            $insertedId = $this->app['db']->table('users')->where('email', 'alice@example.com')->value('id');

            config(['filament-dcat-filters.allowed_models' => [User::class]]);

            $this->postJson('/filament-dcat-filters/fetch-labels', [
                'model' => User::class,
                'ids' => [$insertedId],
                'column' => 'name',
            ])->assertStatus(200)
                ->assertJsonStructure(['labels'])
                ->assertJsonFragment(['labels' => ['Alice']]);
        });

        it('returns 200 with empty labels when no matching ids found', function () {
            $this->app['db']->statement('CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT NOT NULL,
                password TEXT NOT NULL,
                remember_token TEXT,
                email_verified_at DATETIME,
                created_at DATETIME,
                updated_at DATETIME
            )');

            config(['filament-dcat-filters.allowed_models' => [User::class]]);

            $this->postJson('/filament-dcat-filters/fetch-labels', [
                'model' => User::class,
                'ids' => [99999],
                'column' => 'name',
            ])->assertStatus(200)
                ->assertJsonFragment(['labels' => [null]]);
        });

        it('returns labels as ordered array matching input ids order', function () {
            $this->app['db']->statement('CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT NOT NULL,
                password TEXT NOT NULL,
                remember_token TEXT,
                email_verified_at DATETIME,
                created_at DATETIME,
                updated_at DATETIME
            )');

            $this->app['db']->table('users')->insert([
                ['name' => 'Zara', 'email' => 'zara@example.com', 'password' => bcrypt('pw')],
                ['name' => 'Adam', 'email' => 'adam@example.com', 'password' => bcrypt('pw')],
            ]);

            $zaraId = $this->app['db']->table('users')->where('email', 'zara@example.com')->value('id');
            $adamId = $this->app['db']->table('users')->where('email', 'adam@example.com')->value('id');

            config(['filament-dcat-filters.allowed_models' => [User::class]]);

            // Request ids in reverse insertion order
            $response = $this->postJson('/filament-dcat-filters/fetch-labels', [
                'model' => User::class,
                'ids' => [$adamId, $zaraId],
                'column' => 'name',
            ]);

            $response->assertStatus(200);
            $labels = $response->json('labels');

            // Labels must follow input ids order, not database order
            expect($labels)->toBeArray()
                ->and($labels[0])->toBe('Adam')
                ->and($labels[1])->toBe('Zara');
        });

        it('returns null for missing ids in ordered array', function () {
            $this->app['db']->statement('CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT NOT NULL,
                password TEXT NOT NULL,
                remember_token TEXT,
                email_verified_at DATETIME,
                created_at DATETIME,
                updated_at DATETIME
            )');

            $this->app['db']->table('users')->insert([
                'name' => 'Carol', 'email' => 'carol@example.com', 'password' => bcrypt('pw'),
            ]);

            $carolId = $this->app['db']->table('users')->where('email', 'carol@example.com')->value('id');

            config(['filament-dcat-filters.allowed_models' => [User::class]]);

            $response = $this->postJson('/filament-dcat-filters/fetch-labels', [
                'model' => User::class,
                'ids' => [99999, $carolId, 88888],
                'column' => 'name',
            ]);

            $response->assertStatus(200);
            $labels = $response->json('labels');

            expect($labels)->toBeArray()
                ->and($labels)->toHaveCount(3)
                ->and($labels[0])->toBeNull()
                ->and($labels[1])->toBe('Carol')
                ->and($labels[2])->toBeNull();
        });

        it('uses custom keyColumn when provided', function () {
            $this->app['db']->statement('CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT NOT NULL,
                password TEXT NOT NULL,
                remember_token TEXT,
                email_verified_at DATETIME,
                created_at DATETIME,
                updated_at DATETIME
            )');

            $this->app['db']->table('users')->insert([
                'name' => 'Bob',
                'email' => 'bob@example.com',
                'password' => bcrypt('password'),
            ]);

            $insertedId = $this->app['db']->table('users')->where('email', 'bob@example.com')->value('id');

            config(['filament-dcat-filters.allowed_models' => [User::class]]);

            $this->postJson('/filament-dcat-filters/fetch-labels', [
                'model' => User::class,
                'ids' => [$insertedId],
                'column' => 'name',
                'keyColumn' => 'id',
            ])->assertStatus(200)
                ->assertJsonStructure(['labels']);
        });
    });
});
