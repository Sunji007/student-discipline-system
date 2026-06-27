<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\RolePermission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

class PermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed some basic permissions
        RolePermission::create([
            'PermissionID' => (string) Str::uuid(),
            'Role' => 'ครู',
            'ModuleName' => 'messages',
            'CanAccess' => 1
        ]);

        RolePermission::create([
            'PermissionID' => (string) Str::uuid(),
            'Role' => 'ครู',
            'ModuleName' => 'permissions',
            'CanAccess' => 0
        ]);
    }

    public function test_user_can_access_module_if_allowed_in_database()
    {
        $user = User::factory()->create([
            'Role' => 'ครู',
        ]);

        $this->assertTrue($user->canAccess('messages'));
        $this->assertFalse($user->canAccess('permissions'));
    }

    public function test_user_is_blocked_from_route_if_module_permission_is_disabled()
    {
        // Set up test route with middleware inside the test
        $this->app['router']->get('/test-messages', function () {
            return response('Access Allowed', 200);
        })->middleware(['web', 'permission:messages']);

        $this->app['router']->get('/test-permissions', function () {
            return response('Access Allowed', 200);
        })->middleware(['web', 'permission:permissions']);

        $user = User::factory()->create([
            'Role' => 'ครู',
        ]);

        // Act as teacher user
        $response1 = $this->actingAs($user)->get('/test-messages');
        $response1->assertStatus(200);

        $response2 = $this->actingAs($user)->get('/test-permissions');
        $response2->assertStatus(403);
    }
}
