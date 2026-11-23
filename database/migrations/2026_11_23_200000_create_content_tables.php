<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Website content: settings, dentist profile, treatments, credentials,
 * "why choose me" highlights, galleries, testimonials and FAQs.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->timestamps();
        });

        Schema::create('dentist_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('credentials_line')->nullable();   // "BDS (CU) · PGT (OMS)"
            $table->string('role_line')->nullable();          // long line under the About heading
            $table->string('specialty_line')->nullable();     // "Endodontics & Smile Designing"
            $table->text('bio')->nullable();                  // paragraphs; <em> allowed
            $table->text('qualifications_summary')->nullable();
            $table->text('expertise_summary')->nullable();
            $table->string('philosophy_quote', 500)->nullable();
            $table->string('bmdc_reg_no', 30)->nullable();
            $table->unsignedSmallInteger('years_experience')->nullable();
            $table->json('specializations')->nullable();
            $table->string('portrait_image')->nullable();     // small round photo (booking summary)
            $table->string('hero_image')->nullable();
            $table->string('about_image')->nullable();
            $table->string('about_image_alt')->nullable();
            $table->string('about_image_secondary')->nullable();
            $table->string('about_image_secondary_alt')->nullable();
            $table->timestamps();
        });

        Schema::create('treatment_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('treatments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('treatment_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('short_description', 300);
            $table->longText('description')->nullable();
            $table->string('image')->nullable();
            $table->string('image_alt')->nullable();
            $table->string('icon', 60)->default('bi-shield-plus');
            $table->string('duration', 60)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_speciality')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('seo_title')->nullable();
            $table->string('seo_description', 320)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['is_active', 'sort_order']);
        });

        // Education, registration, experience, certifications, training, workshops, achievements
        Schema::create('credentials', function (Blueprint $table) {
            $table->id();
            $table->string('type', 30)->index();
            $table->string('label', 60)->nullable();          // overrides the type name on the timeline
            $table->string('title');
            $table->string('title_emphasis')->nullable();     // rendered in the italic accent font
            $table->string('institution')->nullable();
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->date('completed_on')->nullable();
            $table->string('date_note', 120)->nullable();     // e.g. "Exclusive workshop"
            $table->string('certificate_image')->nullable();
            $table->string('icon', 60)->default('bi-patch-check');
            $table->boolean('is_featured')->default(false);
            $table->boolean('show_on_timeline')->default(true);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('highlights', function (Blueprint $table) {
            $table->id();
            $table->string('icon', 60);
            $table->string('title');
            $table->string('text', 300);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('gallery_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('clinic_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('image');
            $table->string('title');                          // caption
            $table->string('subtitle')->nullable();
            $table->string('alt');
            $table->boolean('show_in_clinic')->default(true); // "The Clinic" bento
            $table->boolean('show_in_slider')->default(false); // "Why choose me" slider
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // Before/after cases. Publishing requires recorded patient consent.
        Schema::create('smile_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('treatment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('before_image');
            $table->string('after_image');
            $table->string('duration', 60)->nullable();
            $table->boolean('consent_confirmed')->default(false);
            $table->date('consent_date')->nullable();
            $table->string('consent_note', 500)->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('treatment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('display_name', 80);
            $table->string('avatar')->nullable();
            $table->text('body');
            $table->decimal('rating', 2, 1)->default(5);
            $table->date('reviewed_on')->nullable();
            $table->string('source', 20)->default('google');
            $table->boolean('consent_confirmed')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('faq_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faq_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('question');
            $table->text('answer');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['faqs', 'faq_categories', 'testimonials', 'smile_cases', 'clinic_photos', 'gallery_categories',
            'highlights', 'credentials', 'treatments', 'treatment_categories', 'dentist_profiles', 'settings'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
