<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Auth\Models\User;
use Modules\Performance\Models\PerformanceCycle;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

describe('Performance Cycle Feature Tests', function () {

    function setupCycleContext(array $permissions = []): User
    {
        $user = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'hr-admin', 'guard_name' => 'api']);

        foreach ($permissions as $permName) {
            $perm = Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'api']);
            $role->givePermissionTo($perm);
        }

        $user->assignRole($role);

        return $user;
    }

    test('unauthenticated users cannot access performance cycle endpoints', function () {
        getJson('/api/v1/performance-cycles')->assertStatus(401);
        postJson('/api/v1/performance-cycles', [])->assertStatus(401);
        postJson('/api/v1/performance-cycles/1/activate')->assertStatus(401);
        postJson('/api/v1/performance-cycles/1/close')->assertStatus(401);
    });

    test('authenticated user with permission can list performance cycles', function () {
        $user = setupCycleContext();
        PerformanceCycle::factory()->count(3)->create();

        actingAs($user, 'sanctum')
            ->getJson('/api/v1/performance-cycles')
            ->assertStatus(200)
            ->assertJsonStructure(['data', 'links', 'meta']);
    });

    test('authenticated user with create permission can create a new cycle', function () {
        $user = setupCycleContext(['performance-cycles.create']);

        $payload = [
            'name' => 'Q4 Performance Review 2026',
            'starts_at' => now()->addDay()->toDateString(),
            'ends_at' => now()->addMonths(3)->toDateString(),
        ];

        actingAs($user, 'sanctum')
            ->postJson('/api/v1/performance-cycles', $payload)
            ->assertStatus(201)
            ->assertJson([
                'message' => 'Performance cycle created successfully.',
            ]);

        assertDatabaseHas('performance_cycles', [
            'name' => 'Q4 Performance Review 2026',
        ]);
    });

    test('can activate a performance cycle', function () {
        $user = setupCycleContext(['performance-cycles.activate']);
        $cycle = PerformanceCycle::factory()->create(['status' => 'draft']);

        actingAs($user, 'sanctum')
            ->postJson("/api/v1/performance-cycles/{$cycle->id}/activate")
            ->assertStatus(200)
            ->assertJson([
                'message' => 'Performance cycle activated successfully.',
            ]);

        assertDatabaseHas('performance_cycles', [
            'id' => $cycle->id,
            'status' => 'active',
        ]);
    });

    test('returns 422 when activating an already active cycle', function () {
        $user = setupCycleContext(['performance-cycles.activate']);
        $cycle = PerformanceCycle::factory()->create(['status' => 'active']);

        actingAs($user, 'sanctum')
            ->postJson("/api/v1/performance-cycles/{$cycle->id}/activate")
            ->assertStatus(422)
            ->assertJson([
                'message' => 'Cycle is already active.',
            ]);
    });

    test('can close a performance cycle', function () {
        $user = setupCycleContext(['performance-cycles.close']);
        $cycle = PerformanceCycle::factory()->create(['status' => 'active']);

        actingAs($user, 'sanctum')
            ->postJson("/api/v1/performance-cycles/{$cycle->id}/close")
            ->assertStatus(200)
            ->assertJson([
                'message' => 'Performance cycle closed successfully.',
            ]);

        assertDatabaseHas('performance_cycles', [
            'id' => $cycle->id,
            'status' => 'closed',
        ]);
    });

    test('returns 422 when closing an already closed cycle', function () {
        $user = setupCycleContext(['performance-cycles.close']);
        $cycle = PerformanceCycle::factory()->create(['status' => 'closed']);

        actingAs($user, 'sanctum')
            ->postJson("/api/v1/performance-cycles/{$cycle->id}/close")
            ->assertStatus(422)
            ->assertJson([
                'message' => 'Cycle is already closed.',
            ]);
    });
});
