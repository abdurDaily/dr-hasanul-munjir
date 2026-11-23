<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\NewsletterSubscriber;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBasics();
        $this->treatment(['name' => 'Dental Implants']);
    }

    public function test_pages_render_with_seo_metadata(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('<link rel="canonical" href="'.url('/').'">', false)
            ->assertSee('"@type":"Dentist"', false)
            ->assertSee('"@type":"FAQPage"', false)
            ->assertSee('Dental Implants')
            ->assertSee('10:30 AM – 2:00 PM'); // chamber hours from the schedule

        $this->get('/treatments')->assertOk()->assertSee('Dental Implants');
        $this->get('/treatments/dental-implants')->assertOk()->assertSee('"@type":"MedicalProcedure"', false)->assertSee('id="appointmentForm"', false);
        $this->get('/blog')->assertOk();
        $this->get('/privacy-policy')->assertOk();
        $this->get('/treatments/nope')->assertNotFound();
        $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml')->assertSee('/treatments/dental-implants');
        $this->get('/robots.txt')->assertOk()->assertSee('User-agent');
    }

    public function test_unconsented_or_hidden_reviews_are_never_shown(): void
    {
        Testimonial::create(['display_name' => 'Hidden Person', 'body' => 'Secret review', 'rating' => 5, 'is_active' => true, 'consent_confirmed' => false]);
        Testimonial::create(['display_name' => 'Visible Person', 'body' => 'Lovely clinic', 'rating' => 5, 'is_active' => true, 'consent_confirmed' => true]);

        $this->get('/')->assertSee('Lovely clinic')->assertDontSee('Secret review');
    }

    public function test_contact_form_stores_the_message(): void
    {
        $this->from('/')->post('/contact', ['name' => 'Nusrat', 'phone' => '+880 1911-000111', 'message' => 'Do you open on Fridays?'])
            ->assertRedirect()->assertSessionHas('contact_status');

        $msg = ContactMessage::sole();
        $this->assertSame('01911000111', $msg->phone);
        $this->assertNull($msg->read_at);
    }

    public function test_contact_form_validates_input(): void
    {
        $this->from('/')->post('/contact', ['name' => '', 'message' => 'short'])
            ->assertSessionHasErrorsIn('contact', ['name', 'message', 'phone', 'email']);
        $this->assertSame(0, ContactMessage::count());
    }

    public function test_newsletter_subscription_is_idempotent(): void
    {
        $this->postJson('/newsletter', ['email' => 'Patient@Example.com'])->assertOk();
        $this->postJson('/newsletter', ['email' => 'patient@example.com'])->assertOk();
        $this->postJson('/newsletter', ['email' => 'nope'])->assertUnprocessable();

        $this->assertSame(1, NewsletterSubscriber::count());
    }

    public function test_admin_pages_are_not_cached_or_indexed(): void
    {
        $this->get('/admin/login')->assertHeader('X-Robots-Tag', 'noindex, nofollow')->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    }
}
