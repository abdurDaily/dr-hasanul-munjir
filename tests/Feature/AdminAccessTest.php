<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBasics();
    }

    public function test_guests_are_sent_to_the_login_page(): void
    {
        $this->get('/admin')->assertRedirect(route('admin.login'));
        $this->get('/admin/appointments')->assertRedirect(route('admin.login'));
    }

    public function test_staff_can_log_in_and_out(): void
    {
        $user = User::factory()->role('Receptionist')->create(['email' => 'desk@example.com']);

        $this->post(route('admin.login.store'), ['email' => 'desk@example.com', 'password' => 'password'])->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->last_login_at);

        $this->post(route('admin.logout'))->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }

    public function test_wrong_password_and_inactive_accounts_cannot_log_in(): void
    {
        User::factory()->role('Receptionist')->create(['email' => 'desk@example.com']);
        User::factory()->role('Receptionist')->create(['email' => 'old@example.com', 'is_active' => false]);

        $this->post(route('admin.login.store'), ['email' => 'desk@example.com', 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->post(route('admin.login.store'), ['email' => 'old@example.com', 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_is_throttled(): void
    {
        foreach (range(1, 5) as $i) {
            $this->post(route('admin.login.store'), ['email' => 'x@example.com', 'password' => 'nope']);
        }
        $this->post(route('admin.login.store'), ['email' => 'x@example.com', 'password' => 'nope'])->assertTooManyRequests();
    }

    public function test_users_without_a_role_cannot_enter_the_admin(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
    }

    public function test_a_deactivated_user_is_logged_out_on_the_next_request(): void
    {
        $user = $this->staff('Receptionist');
        $user->update(['is_active' => false]);

        $this->actingAs($user)->get('/admin')->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }

    public function test_receptionist_permissions_are_enforced_on_the_server(): void
    {
        $this->actingAs($this->staff('Receptionist'));

        $this->get(route('admin.appointments.index'))->assertOk();
        $this->get(route('admin.schedule.index'))->assertOk();
        $this->get(route('admin.messages.index'))->assertOk();

        $this->get(route('admin.treatments.index'))->assertForbidden();
        $this->post(route('admin.treatments.store'), [])->assertForbidden();
        $this->get(route('admin.posts.index'))->assertForbidden();
        $this->get(route('admin.users.index'))->assertForbidden();
        $this->get(route('admin.settings.edit', 'general'))->assertForbidden();
        $this->get(route('admin.taxonomies.index', 'blog-tags'))->assertForbidden();
    }

    public function test_content_manager_cannot_see_patient_appointments(): void
    {
        $this->actingAs($this->staff('Content Manager'));

        $this->get(route('admin.treatments.index'))->assertOk();
        $this->get(route('admin.posts.index'))->assertOk();
        $this->get(route('admin.settings.edit', 'home'))->assertOk();

        $this->get(route('admin.appointments.index'))->assertForbidden();
        $this->get(route('admin.messages.index'))->assertForbidden();
        $this->get(route('admin.settings.edit', 'general'))->assertForbidden();
        $this->get('/admin')->assertOk()->assertDontSee('Weekly Schedule');
    }

    public function test_only_super_admins_can_modify_super_admin_accounts(): void
    {
        $super = $this->staff(User::SUPER_ADMIN);
        $manager = User::factory()->create();
        $manager->givePermissionTo('users.manage', 'dashboard.view');
        $manager->assignRole('Receptionist');

        $this->actingAs($manager)->get(route('admin.users.edit', $super))->assertForbidden();
        $this->put(route('admin.users.update', $super), ['name' => 'x', 'email' => $super->email, 'roles' => ['Receptionist'], 'is_active' => 1])->assertForbidden();
        $this->post(route('admin.users.store'), ['name' => 'Evil', 'email' => 'e@example.com', 'password' => 'Password1234', 'password_confirmation' => 'Password1234', 'roles' => [User::SUPER_ADMIN]])->assertForbidden();
    }

    public function test_the_last_super_admin_cannot_be_demoted(): void
    {
        $super = $this->staff(User::SUPER_ADMIN);

        $this->actingAs($super)->put(route('admin.users.update', $super), ['name' => $super->name, 'email' => $super->email, 'roles' => ['Receptionist'], 'is_active' => 1])
            ->assertSessionHas('error');
        $this->assertTrue($super->fresh()->hasRole(User::SUPER_ADMIN));
    }
}
