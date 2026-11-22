<?php

namespace Tests\Feature;

use App\Mail\EnquiryAcknowledgement;
use App\Mail\EnquiryReceived;
use App\Models\Enquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EnquiryTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'type' => 'counselling',
            'full_name' => 'Aryan Sharma',
            'phone' => '+91 98765 43210',
            'email' => 'Aryan@Example.com',
            'qualification' => "Bachelor's Degree",
            'gpa' => '8.2 CGPA',
            'preferred_course' => 'Computer Science & AI',
            'preferred_country' => 'Germany',
            'preferred_intake' => 'Winter Intake',
        ], $overrides);
    }

    public function test_modal_enquiry_is_stored_and_emails_are_sent(): void
    {
        Mail::fake();

        $response = $this->postJson('/enquiries', $this->payload())
            ->assertCreated()
            ->assertJson(['ok' => true, 'name' => 'Aryan']);

        $id = Enquiry::firstOrFail()->id;
        $response->assertJsonPath('reference', 'EF-'.str_pad((string) $id, 5, '0', STR_PAD_LEFT));

        $this->assertDatabaseHas('enquiries', [
            'type' => 'counselling',
            'email' => 'aryan@example.com',
            'preferred_country' => 'Germany',
            'status' => 'new',
        ]);
        Mail::assertSent(EnquiryReceived::class, fn ($m) => $m->hasTo(config('site.notify_email')));
        Mail::assertSent(EnquiryAcknowledgement::class, fn ($m) => $m->hasTo('aryan@example.com'));
    }

    public function test_required_fields_are_validated(): void
    {
        $this->postJson('/enquiries', ['type' => 'evaluation'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['full_name', 'phone', 'email', 'gpa']);

        $this->assertDatabaseCount('enquiries', 0);
    }

    public function test_options_must_come_from_the_allowed_lists(): void
    {
        $this->postJson('/enquiries', $this->payload([
            'type' => 'spam',
            'preferred_country' => 'Atlantis',
            'phone' => 'call me maybe',
            'email' => 'not-an-email',
        ]))->assertUnprocessable()->assertJsonValidationErrors(['type', 'preferred_country', 'phone', 'email']);
    }

    public function test_plain_form_post_redirects_back_with_success_message(): void
    {
        Mail::fake();

        $this->from('/contact')
            ->post('/enquiries', $this->payload(['type' => 'contact', 'message' => 'Hello']))
            ->assertRedirect('/contact#enquiry')
            ->assertSessionHas('enquiry_success', 'Aryan');

        $this->assertSame('contact', Enquiry::first()->type);
    }

    public function test_honeypot_submissions_are_discarded_silently(): void
    {
        Mail::fake();

        $this->postJson('/enquiries', $this->payload(['website' => 'http://spam.test']))->assertCreated();

        $this->assertDatabaseCount('enquiries', 0);
        Mail::assertNothingSent();
    }

    public function test_submissions_are_rate_limited(): void
    {
        Mail::fake();

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/enquiries', $this->payload())->assertCreated();
        }
        $this->postJson('/enquiries', $this->payload())->assertTooManyRequests();
    }
}
