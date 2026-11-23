<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\SmileCase;
use App\Models\Testimonial;
use App\Models\Treatment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBasics();
        Storage::fake('public');
        $this->actingAs($this->staff('Content Manager'));
    }

    private function treatmentData(array $overrides = []): array
    {
        return [
            'name' => 'Teeth Whitening', 'short_description' => 'Brighter smile.', 'description' => '<p>Safe whitening.</p>',
            'image' => UploadedFile::fake()->image('whitening.jpg', 1200, 800), 'image_alt' => 'Whitening lamp',
            'icon' => 'bi-brightness-high', 'sort_order' => 1, 'is_active' => 1, 'is_featured' => 1, 'is_speciality' => 0, ...$overrides,
        ];
    }

    public function test_treatment_crud_with_responsive_webp_images(): void
    {
        $this->post(route('admin.treatments.store'), $this->treatmentData())->assertRedirect()->assertSessionHas('success');

        $t = Treatment::sole();
        $this->assertSame('teeth-whitening', $t->slug);
        foreach ([640, 960] as $w) {
            Storage::disk('public')->assertExists("{$t->image}-{$w}.webp");
        }
        $this->get(route('treatments.show', $t->slug))->assertOk()->assertSee('Safe whitening.')->assertSee('Book this treatment');

        $old = $t->image;
        $this->put(route('admin.treatments.update', $t), $this->treatmentData(['name' => 'Whitening', 'slug' => 'whitening', 'image' => UploadedFile::fake()->image('new.png', 900, 600)]))
            ->assertSessionHas('success');
        $t->refresh();
        $this->assertSame('whitening', $t->slug);
        Storage::disk('public')->assertMissing("{$old}-640.webp"); // replaced image cleaned up

        $this->delete(route('admin.treatments.destroy', $t))->assertRedirect();
        $this->assertSoftDeleted($t);
        $this->get(route('treatments.show', 'whitening'))->assertNotFound();
    }

    public function test_treatment_gallery_images_can_be_added_captioned_and_removed(): void
    {
        $this->post(route('admin.treatments.store'), $this->treatmentData([
            'gallery_new' => [UploadedFile::fake()->image('stage1.jpg', 1200, 900), UploadedFile::fake()->image('stage2.png', 1000, 750)],
        ]))->assertSessionHasNoErrors();

        $t = Treatment::sole();
        [$first, $second] = $t->images()->get()->all();
        foreach ([600, 900, 1400] as $w) {
            Storage::disk('public')->assertExists("{$first->image}-{$w}.webp");
        }

        // Caption + reorder one, remove the other, add a third
        $this->put(route('admin.treatments.update', $t), $this->treatmentData([
            'image' => null,
            'gallery' => [
                $first->id => ['caption' => 'Early gum inflammation', 'alt' => 'Red, swollen gums', 'sort_order' => 5],
                $second->id => ['caption' => '', 'delete' => 1],
            ],
            'gallery_new' => [UploadedFile::fake()->image('stage3.jpg', 900, 700)],
        ]))->assertSessionHas('success');

        $images = $t->images()->get();
        $this->assertCount(2, $images);
        $this->assertSame('Early gum inflammation', $images->firstWhere('id', $first->id)->caption);
        $this->assertSame([$first->id, $images->last()->id], $images->pluck('id')->all());
        $this->assertGreaterThan(5, $images->last()->sort_order); // new uploads go after existing ones
        $this->assertDatabaseMissing('treatment_images', ['id' => $second->id]);
        Storage::disk('public')->assertMissing("{$second->image}-600.webp");

        // Shown on the public page with an enlarge button
        $this->get(route('treatments.show', $t->slug))->assertOk()
            ->assertSee('Early gum inflammation')->assertSee('id="lightbox"', false)->assertSee('data-lightbox=', false);
    }

    public function test_gallery_rejects_bad_files_and_other_treatments_images(): void
    {
        $this->post(route('admin.treatments.store'), $this->treatmentData([
            'gallery_new' => [UploadedFile::fake()->create('notes.pdf', 50, 'application/pdf')],
        ]))->assertSessionHasErrors('gallery_new.0');
        $this->assertSame(0, Treatment::count());

        $this->post(route('admin.treatments.store'), $this->treatmentData(['gallery_new' => [UploadedFile::fake()->image('a.jpg', 800, 600)]]));
        $this->post(route('admin.treatments.store'), $this->treatmentData(['name' => 'Implants', 'slug' => 'implants']));
        [$a, $b] = Treatment::query()->orderBy('id')->get()->all();
        $foreign = $a->images()->sole();

        // Editing treatment B cannot delete treatment A's image
        $this->put(route('admin.treatments.update', $b), $this->treatmentData(['name' => 'Implants', 'slug' => 'implants', 'image' => null, 'gallery' => [$foreign->id => ['delete' => 1]]]));
        $this->assertDatabaseHas('treatment_images', ['id' => $foreign->id]);
    }

    public function test_treatment_cards_link_to_the_detail_page(): void
    {
        $this->post(route('admin.treatments.store'), $this->treatmentData());

        $this->get('/')->assertSee('View details')->assertSee(route('treatments.show', 'teeth-whitening'));
    }

    public function test_trashed_treatments_and_posts_can_be_restored(): void
    {
        $this->post(route('admin.treatments.store'), $this->treatmentData());
        $t = Treatment::sole();
        $this->delete(route('admin.treatments.destroy', $t));

        $this->get(route('admin.treatments.index', ['trashed' => 1]))->assertOk()->assertSee('Teeth Whitening')->assertSee('Restore');
        $this->patch(route('admin.treatments.restore', $t->id))->assertRedirect(route('admin.treatments.edit', $t));
        $this->assertNotSoftDeleted($t);
        $this->get(route('treatments.show', $t->slug))->assertOk();

        $this->post(route('admin.posts.store'), ['title' => 'Old news', 'body' => '<p>x</p>', 'status' => 'draft', 'image' => UploadedFile::fake()->image('p.jpg', 800, 600)]);
        $post = BlogPost::sole();
        $this->delete(route('admin.posts.destroy', $post));
        $this->patch(route('admin.posts.restore', $post->id));
        $this->assertNotSoftDeleted($post);

        // Permanent delete only works from Trash, and removes the image files
        $this->delete(route('admin.posts.force-destroy', $post->id))->assertNotFound();
        $this->delete(route('admin.posts.destroy', $post));
        $this->delete(route('admin.posts.force-destroy', $post->id))->assertSessionHas('success');
        $this->assertDatabaseMissing('blog_posts', ['id' => $post->id]);
        Storage::disk('public')->assertMissing("{$post->image}-700.webp");
    }

    public function test_image_uploads_are_validated(): void
    {
        $this->post(route('admin.treatments.store'), $this->treatmentData(['image' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf')]))
            ->assertSessionHasErrors('image');
        $this->post(route('admin.treatments.store'), $this->treatmentData(['image' => UploadedFile::fake()->image('huge.jpg', 1200, 800)->size(9000)]))
            ->assertSessionHasErrors('image');
        $this->post(route('admin.treatments.store'), $this->treatmentData(['image' => UploadedFile::fake()->create('shell.php.jpg', 10, 'text/x-php')]))
            ->assertSessionHasErrors('image');
        $this->post(route('admin.treatments.store'), $this->treatmentData(['icon' => 'bi-not-allowed']))->assertSessionHasErrors('icon');

        $this->assertSame(0, Treatment::count());
    }

    public function test_rich_text_is_sanitised(): void
    {
        $this->post(route('admin.treatments.store'), $this->treatmentData([
            'description' => '<p onclick="alert(1)">Hi<script>alert(1)</script></p><a href="javascript:alert(1)">x</a><img src=x onerror=alert(1)>',
        ]));

        $html = Treatment::sole()->description;
        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('onclick', $html);
        $this->assertStringNotContainsString('onerror', $html);
        $this->assertStringNotContainsString('javascript:', $html);
    }

    public function test_blog_drafts_stay_private_until_published(): void
    {
        $data = ['title' => 'Healthy Gums 101', 'excerpt' => 'Gum care basics.', 'body' => '<p>Floss daily.</p>', 'status' => 'draft', 'tags' => 'gums, Prevention, gums'];

        $this->post(route('admin.posts.store'), $data)->assertRedirect();
        $post = BlogPost::sole();
        $this->assertSame('healthy-gums-101', $post->slug);
        $this->assertSame(2, $post->tags()->count()); // duplicate tag ignored
        $this->get(route('blog.show', $post->slug))->assertNotFound();

        $this->put(route('admin.posts.update', $post), [...$data, 'status' => 'published'])->assertSessionHas('success');
        $this->assertNotNull($post->fresh()->published_at);
        $this->get(route('blog.show', $post->slug))->assertOk()->assertSee('Floss daily.')->assertSee('"@type":"BlogPosting"', false);
        $this->get(route('blog.index'))->assertSee('Healthy Gums 101');
        $this->get(route('sitemap'))->assertSee(route('blog.show', $post->slug));
    }

    public function test_scheduled_posts_are_hidden_until_their_date(): void
    {
        $this->post(route('admin.posts.store'), ['title' => 'Coming soon', 'body' => '<p>Text</p>', 'status' => 'published', 'published_at' => now()->addDay()->format('Y-m-d\TH:i')]);

        $this->get(route('blog.show', 'coming-soon'))->assertNotFound();
    }

    public function test_publishing_an_empty_post_is_refused(): void
    {
        $this->post(route('admin.posts.store'), ['title' => 'Empty', 'body' => '<p><br></p>', 'status' => 'published'])->assertSessionHasErrors('body');
    }

    public function test_patient_content_cannot_be_published_without_consent(): void
    {
        $case = [
            'title' => 'Whitening', 'before_image' => UploadedFile::fake()->image('b.jpg', 800, 600), 'after_image' => UploadedFile::fake()->image('a.jpg', 800, 600),
            'sort_order' => 0, 'is_active' => 1, 'consent_confirmed' => 0,
        ];
        $this->actingAs($this->staff('Content Manager'))->post(route('admin.smile-cases.store'), $case)->assertSessionHasErrors('is_active');
        $this->post(route('admin.smile-cases.store'), [...$case, 'consent_confirmed' => 1, 'consent_date' => '2026-01-10'])->assertRedirect();
        $this->assertSame(1, SmileCase::query()->published()->count());

        $review = ['display_name' => 'Karim H.', 'body' => 'Great care.', 'rating' => 5, 'source' => 'google', 'sort_order' => 0, 'is_active' => 1, 'consent_confirmed' => 0];
        $this->post(route('admin.testimonials.store'), $review)->assertSessionHasErrors('is_active');
        $this->assertSame(0, Testimonial::count());
    }
}
