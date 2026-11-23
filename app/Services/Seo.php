<?php

namespace App\Services;

use App\Models\BlogPost;
use App\Models\DentistProfile;
use App\Models\ScheduleSession;
use App\Models\Treatment;
use Illuminate\Support\Collection;

/**
 * Builds schema.org structured data for public pages.
 */
class Seo
{
    public function __construct(private readonly Settings $settings) {}

    public function dentistSchema(): array
    {
        $profile = DentistProfile::current();
        $s = $this->settings;
        $days = [0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'];

        $hours = ScheduleSession::query()->where('is_active', true)->get()
            ->groupBy(fn ($x) => substr($x->starts_at, 0, 5).'-'.substr($x->ends_at, 0, 5))
            ->map(fn ($group, $range) => [
                '@type' => 'OpeningHoursSpecification',
                'dayOfWeek' => $group->pluck('weekday')->unique()->sort()->map(fn ($d) => $days[$d])->values()->all(),
                'opens' => explode('-', $range)[0],
                'closes' => explode('-', $range)[1],
            ])->values()->all();

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Dentist',
            'name' => $s->get('clinic_name').' — '.$profile->full_name,
            'image' => $s->get('og_image') ? image_url($s->get('og_image'), 'og') : asset('assets/images/og-image.jpg'),
            'url' => route('home'),
            'telephone' => array_values(array_map(fn ($p) => substr(tel_href($p), 4), array_filter([$s->get('phone_primary'), $s->get('phone_secondary')]))),
            'email' => $s->get('email'),
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => trim(rtrim($s->get('address_line1', ''), ',').', '.$s->get('address_short', ''), ', '),
                'addressLocality' => $s->get('address_locality'),
                'addressCountry' => 'BD',
            ],
            'openingHoursSpecification' => $hours,
            'founder' => ['@type' => 'Person', 'name' => $profile->full_name, 'jobTitle' => 'Dentist', 'honorificSuffix' => $profile->credentials_line],
            'sameAs' => array_values(array_filter([$s->get('facebook_url'), $s->get('instagram_url'), $s->get('youtube_url')])) ?: null,
            'aggregateRating' => $s->get('google_rating') && $s->get('google_review_count') ? [
                '@type' => 'AggregateRating', 'ratingValue' => $s->get('google_rating'), 'reviewCount' => $s->get('google_review_count'),
            ] : null,
        ]);
    }

    public function faqSchema(Collection $faqs): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $faqs->map(fn ($f) => [
                '@type' => 'Question',
                'name' => $f->question,
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f->answer],
            ])->all(),
        ];
    }

    public function treatmentSchema(Treatment $t): array
    {
        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'MedicalProcedure',
            'name' => $t->name,
            'description' => $t->seo_description ?: $t->short_description,
            'url' => route('treatments.show', $t->slug),
            'image' => image_url($t->image, 'treatment'),
            'procedureType' => 'https://schema.org/TherapeuticProcedure',
        ]);
    }

    public function articleSchema(BlogPost $post): array
    {
        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $post->title,
            'description' => $post->meta_description ?: $post->excerpt,
            'image' => image_url($post->og_image ?: $post->image, $post->og_image ? 'og' : 'blog'),
            'datePublished' => $post->published_at?->toIso8601String(),
            'dateModified' => $post->updated_at?->toIso8601String(),
            'author' => ['@type' => 'Person', 'name' => $post->author?->name ?? DentistProfile::current()->full_name],
            'publisher' => ['@type' => 'Organization', 'name' => $this->settings->get('clinic_name'), 'logo' => ['@type' => 'ImageObject', 'url' => asset('assets/images/favicon.svg')]],
            'mainEntityOfPage' => route('blog.show', $post->slug),
        ]);
    }

    public function breadcrumbs(array $items): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($items)->values()->map(fn ($item, $i) => [
                '@type' => 'ListItem', 'position' => $i + 1, 'name' => $item[0], 'item' => $item[1],
            ])->all(),
        ];
    }
}
