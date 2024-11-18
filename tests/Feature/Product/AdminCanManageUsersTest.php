<?php

namespace Tests\Feature\User;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;

class AdminCanManageUsersTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function admin_can_manage_users()
    {
        // Tworzymy admina i zwykłego użytkownika
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create([
            'role' => 'user',
        ]);

        // Admin może zobaczyć listę użytkowników
        $response = $this->actingAs($admin)->get('/admin/manage-users');
        $response->assertStatus(200);
        $response->assertViewIs('admin.manage-users');
        $response->assertViewHas('users', function ($users) use ($user) {
            return $users->contains($user);
        });

        // Admin może zaktualizować użytkownika
        $response = $this->actingAs($admin)->put('/admin/user/' . $user->id, [
            'name' => 'UpdatedName',
            'lastname' => 'UpdatedLastname',
            'email' => $user->email,
            'role' => 'user',
            'isActive' => true,
        ]);

        $response->assertStatus(200); // Oczekiwany status z JSON
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'UpdatedName',
            'lastname' => 'UpdatedLastname',
        ]);
    }
}
