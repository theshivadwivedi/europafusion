<?php

namespace Tests\Feature;

use App\Models\Enquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->forceFill(['is_admin' => true])->save();

        return $user;
    }

    private function enquiry(): Enquiry
    {
        return Enquiry::create([
            'type' => 'evaluation', 'full_name' => 'Meera Nair', 'email' => 'meera@example.com',
            'phone' => '+91 90000 11111', 'gpa' => '78%', 'preferred_country' => 'Germany',
        ]);
    }

    public function test_guests_are_sent_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/enquiries')->assertRedirect('/admin/login');
    }

    public function test_non_admin_users_are_rejected(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_admin_can_log_in_with_valid_credentials_only(): void
    {
        $admin = $this->admin();

        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'password'])->assertRedirect('/admin');
        $this->assertAuthenticatedAs($admin);
    }

    public function test_admin_can_manage_enquiries(): void
    {
        $enquiry = $this->enquiry();
        $admin = $this->admin();

        $this->actingAs($admin)->get('/admin')->assertOk()->assertSee('Meera Nair');
        $this->actingAs($admin)->get('/admin/enquiries?q=Meera')->assertOk()->assertSee('meera@example.com');
        $this->actingAs($admin)->get("/admin/enquiries/{$enquiry->id}")->assertOk()->assertSee('78%');

        $this->actingAs($admin)->patch("/admin/enquiries/{$enquiry->id}", ['status' => 'contacted', 'admin_notes' => 'Called'])
            ->assertRedirect();
        $this->assertSame('contacted', $enquiry->fresh()->status);

        $csv = $this->actingAs($admin)->get('/admin/enquiries/export');
        $csv->assertOk()->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Meera Nair', $csv->streamedContent());

        $this->actingAs($admin)->delete("/admin/enquiries/{$enquiry->id}")->assertRedirect('/admin/enquiries');
        $this->assertModelMissing($enquiry);
    }

    public function test_csv_export_neutralises_spreadsheet_formulas(): void
    {
        Enquiry::create(['type' => 'contact', 'full_name' => '=HYPERLINK("http://evil")', 'email' => 'x@example.com', 'phone' => '+91 90000 00000', 'gpa' => '1']);

        $body = $this->actingAs($this->admin())->get('/admin/enquiries/export')->streamedContent();
        $this->assertStringContainsString("'=HYPERLINK", $body);
    }
}
