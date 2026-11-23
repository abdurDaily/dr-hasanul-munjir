<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\ClinicPhoto;
use App\Models\Credential;
use App\Models\DentistProfile;
use App\Models\Faq;
use App\Models\FaqCategory;
use App\Models\GalleryCategory;
use App\Models\Highlight;
use App\Models\Treatment;
use App\Models\TreatmentCategory;
use App\Models\User;
use App\Services\ImageService;
use Illuminate\Database\Seeder;

/**
 * Content taken from the original static site. Images are re-encoded from the originals in
 * public/assets/images. Testimonials and before/after cases are intentionally NOT seeded:
 * only genuine, consented patient content may be published.
 */
class ContentSeeder extends Seeder
{
    private ImageService $images;

    public function run(ImageService $images): void
    {
        if (DentistProfile::query()->exists()) {
            return; // already seeded
        }
        $this->images = $images;

        $this->profile();
        $this->treatments();
        $this->credentials();
        $this->highlights();
        $this->clinicPhotos();
        $this->faqs();
        $this->blogDrafts();
    }

    private function img(string $file, string $dir, string $preset): string
    {
        return $this->images->store(public_path("assets/images/{$file}"), $dir, $preset);
    }

    private function profile(): void
    {
        DentistProfile::create([
            'full_name' => 'Dr. Hasanul Munjir',
            'credentials_line' => 'BDS (CU) · PGT (OMS)',
            'role_line' => 'Dr. Hasanul Munjir · BDS (CU) · PGT (OMS), Chattogram Medical College',
            'specialty_line' => 'Endodontics & Smile Designing',
            'bio' => "For more than a decade I've helped families in Chattogram feel calm in the dental chair. I explain every option, show you your X-rays, and only recommend what your teeth truly need.\n\nI'm specially trained in rotary endodontics and smile designing. Continued training — like the *Precision Endodontics: From Access to Obturation* hands-on program — keeps root canal treatment comfortable and predictable for my patients.",
            'qualifications_summary' => "BDS (University of Chittagong)\nPGT (OMS), Chattogram Medical College",
            'expertise_summary' => "Rotary endodontics, smile\ndesigning & restorations",
            'philosophy_quote' => 'The best dentistry is the kind you barely notice — painless today, and still healthy twenty years from now.',
            'bmdc_reg_no' => '14253',
            'years_experience' => 10,
            'specializations' => ['Rotary Endodontics', 'Smile Designing', 'Restorative Dentistry', 'Oral & Maxillofacial Surgery'],
            'portrait_image' => $this->img('dr-munjir-portrait.jpg', 'profile', 'portrait'),
            'hero_image' => $this->img('dr-munjir-hero.png', 'profile', 'hero'),
            'about_image' => $this->img('dr-munjir-treatment.jpg', 'profile', 'photo'),
            'about_image_alt' => "Dr. Munjir examining a patient's teeth with an ultrasonic scaler",
            'about_image_secondary' => $this->img('endodontics-training.jpg', 'profile', 'photo'),
            'about_image_secondary_alt' => 'Dr. Munjir receiving a certificate at a Precision Endodontics hands-on training program',
        ]);
    }

    private function treatments(): void
    {
        $cats = collect(['Preventive & General', 'Cosmetic', 'Restorative', 'Surgical', 'Specialised Care'])
            ->mapWithKeys(fn ($name, $i) => [$name => TreatmentCategory::create(['name' => $name, 'sort_order' => $i])->id]);

        $rows = [
            ['General Dentistry', 'general', 'bi-shield-plus', 'Preventive & General', 'Check-ups, scaling, fillings and preventive care that keep small problems small.', "Dentist checking a patient's teeth with a dental mirror"],
            ['Cosmetic Dentistry', 'cosmetic', 'bi-gem', 'Cosmetic', 'Smile designing, veneers and bonding tailored to your face and natural features.', 'Curing light used during cosmetic tooth bonding'],
            ['Teeth Whitening', 'whitening', 'bi-brightness-high', 'Cosmetic', 'Safe, professionally supervised whitening for a brighter smile without sensitivity.', 'Patient receiving in-office teeth whitening with a whitening lamp'],
            ['Dental Implants', 'implants', 'bi-nut', 'Surgical', 'Permanent, natural-looking replacements for missing teeth that restore full function.', 'Model showing a dental implant screw between natural teeth'],
            ['Root Canal Treatment', 'root-canal', 'bi-activity', 'Restorative', 'Precision rotary endodontics that relieves pain and saves your natural tooth.', 'Dental X-ray showing tooth roots on a monitor'],
            ['Orthodontics & Braces', 'orthodontics', 'bi-grid-3x2-gap', 'Specialised Care', 'Metal, ceramic and clear aligner options to straighten teeth at any age.', 'Dental model with orthodontic braces'],
            ['Pediatric Dentistry', 'pediatric', 'bi-balloon-heart', 'Specialised Care', 'Friendly, fear-free visits that build healthy habits for little smiles.', 'Child having a gentle dental check-up'],
            ['Crowns & Bridges', 'crowns', 'bi-bricks', 'Restorative', 'Durable, colour-matched restorations that rebuild strength and appearance.', 'Dental model showing a crown and implant restoration'],
            ['Gum Disease Treatment', 'gum', 'bi-droplet-half', 'Specialised Care', 'Deep cleaning and periodontal therapy to stop bleeding gums and bone loss.', "Dentist examining a patient's gums and teeth"],
        ];

        foreach ($rows as $i => [$name, $file, $icon, $cat, $short, $alt]) {
            Treatment::create([
                'treatment_category_id' => $cats[$cat],
                'name' => $name,
                'short_description' => $short,
                'image' => $this->img("treatments/{$file}.jpg", 'treatments', 'treatment'),
                'image_alt' => $alt,
                'icon' => $icon,
                'sort_order' => $i + 1,
                'is_featured' => true,
                'is_speciality' => $file === 'root-canal',
            ]);
        }
    }

    private function credentials(): void
    {
        Credential::create([
            'type' => 'training', 'label' => 'Advanced Training',
            'title' => 'Precision Endodontics:', 'title_emphasis' => 'From Access to Obturation',
            'description' => 'A comprehensive hands-on training program in modern rotary root canal treatment — covering access, shaping, irrigation and obturation — completed with a Certificate of Achievement.',
            'completed_on' => '2026-06-17', 'date_note' => 'Exclusive workshop',
            'institution' => 'Well Park Residence Hotel', 'location' => 'Chattogram',
            'certificate_image' => $this->img('endodontics-training.jpg', 'credentials', 'certificate'),
            'icon' => 'bi-stars', 'is_featured' => true, 'show_on_timeline' => false, 'sort_order' => 0,
        ]);

        $steps = [
            ['education', 'Education', 'Bachelor of Dental Surgery', 'BDS, University of Chittagong (CU).', 'University of Chittagong', 'bi-mortarboard'],
            ['registration', 'Registration', 'BMDC Registered Dentist', 'Bangladesh Medical & Dental Council, Reg. No. 14253.', 'Bangladesh Medical & Dental Council', 'bi-patch-check'],
            ['education', 'Post-graduate', 'PGT — Oral & Maxillofacial Surgery', 'Post-graduate training at Chattogram Medical College.', 'Chattogram Medical College', 'bi-hospital'],
            ['training', 'Specialisation', 'Rotary Endodontics & Smile Designing', 'Special training in modern root canal and aesthetic dentistry.', null, 'bi-gem'],
            ['experience', 'Practice', 'Munjir Dental Care', 'Founder & lead dentist, Bahaddarhat, Chattogram.', 'Munjir Dental Care', 'bi-house-heart'],
        ];
        foreach ($steps as $i => [$type, $label, $title, $desc, $inst, $icon]) {
            Credential::create([
                'type' => $type, 'label' => $label, 'title' => $title, 'description' => $desc,
                'institution' => $inst, 'icon' => $icon, 'sort_order' => $i + 1,
            ]);
        }
    }

    private function highlights(): void
    {
        foreach ([
            ['bi-person-heart', 'Patient-Centred Approach', "You're heard first. Treatment starts only when you're comfortable."],
            ['bi-cpu', 'Modern Dental Technology', 'Digital X-rays, rotary endodontics and ultrasonic scaling.'],
            ['bi-clipboard2-pulse', 'Personalised Treatment Plans', 'Clear options, honest costs, no unnecessary procedures.'],
            ['bi-cup-hot', 'Comfortable Environment', 'A calm, sterile clinic designed to ease dental anxiety.'],
            ['bi-award', 'Experienced Professional', 'A decade of hands-on care and continuous advanced training.'],
            ['bi-infinity', 'Long-Term Oral Health', 'Prevention-led dentistry that protects your smile for life.'],
        ] as $i => [$icon, $title, $text]) {
            Highlight::create(['icon' => $icon, 'title' => $title, 'text' => $text, 'sort_order' => $i]);
        }
    }

    private function clinicPhotos(): void
    {
        $cat = collect(['Treatment Room', 'Modern Equipment', 'Patient Care', 'Training', 'Clinic Interior'])
            ->mapWithKeys(fn ($name, $i) => [$name => GalleryCategory::create(['name' => $name, 'sort_order' => $i])->id]);

        $photos = [
            ['clinic-treatment-room.jpg', 'Treatment Room', 'Modern clinic', 'Clean, calm & sterilised', 'Bright treatment room with modern dental chair and digital smile display', true, 4],
            ['dr-munjir-procedure.jpg', 'Modern Equipment', 'Digital imaging', 'See exactly what we see', 'Dr. Munjir performing a procedure with digital imaging on a laptop beside the chair', true, 1],
            ['dr-munjir-scaling.jpg', 'Patient Care', 'Ultrasonic scaling', 'Gentle, thorough cleaning', 'Dr. Munjir performing ultrasonic scaling for a patient', true, 0],
            ['dr-munjir-treatment.jpg', 'Patient Care', 'Patient-first care', 'Comfort at every step', 'Dr. Munjir carefully treating a relaxed patient', false, 2],
            ['endodontics-training.jpg', 'Training', 'Always learning', 'Advanced endodontic training', 'Dr. Munjir receiving a certificate of achievement in endodontics', false, 3],
        ];
        foreach ($photos as [$file, $category, $title, $subtitle, $alt, $inClinic, $order]) {
            ClinicPhoto::create([
                'gallery_category_id' => $cat[$category],
                'image' => $this->img($file, 'clinic', 'photo'),
                'title' => $title, 'subtitle' => $subtitle, 'alt' => $alt,
                'show_in_clinic' => $inClinic, 'show_in_slider' => true,
                'sort_order' => $file === 'clinic-treatment-room.jpg' ? 0 : $order + 1,
            ]);
        }
    }

    private function faqs(): void
    {
        $general = FaqCategory::create(['name' => 'General', 'sort_order' => 0]);
        $rows = [
            ['How often should I visit a dentist?', 'For most people, a check-up and professional cleaning every six months is ideal. If you have gum disease or a high risk of decay, we may suggest more frequent visits.'],
            ['Is teeth whitening safe?', 'Yes — when supervised by a dentist. Professional whitening uses controlled concentrations and protects your gums. Temporary sensitivity is possible but usually settles within a day or two.'],
            ['Does root canal treatment hurt?', 'With modern anaesthesia and rotary instruments, a root canal feels much like a regular filling. Most patients find it relieves the pain they came in with.'],
            ['How long do dental implants last?', 'With good oral hygiene and regular check-ups, implants can last 20 years or more. The crown on top may need replacing after 10–15 years due to normal wear.'],
            ['What should I do during a dental emergency?', 'Call us right away. For a knocked-out tooth, hold it by the crown, rinse gently and keep it in milk. For swelling, use a cold compress and avoid heat. Severe facial swelling with fever needs urgent care.'],
            ['How can I book an appointment?', 'Use the appointment section above, call 01690-106079 or 01992-761901, or message on WhatsApp. Our team will confirm a time that suits you.'],
        ];
        foreach ($rows as $i => [$q, $a]) {
            Faq::create(['faq_category_id' => $general->id, 'question' => $q, 'answer' => $a, 'sort_order' => $i]);
        }
    }

    /** The three article ideas from the static site, as drafts for the doctor to write. */
    private function blogDrafts(): void
    {
        $author = User::query()->first();
        foreach ([
            ['Prevention', '7 Essential Tips for Healthy Teeth', 'Simple daily habits that protect enamel, gums and your wallet.'],
            ['Check-ups', 'When Should You Visit a Dentist?', "Warning signs you shouldn't ignore — and how often to come in."],
            ['Cosmetic', 'Common Myths About Teeth Whitening', 'Does whitening damage enamel? We separate fact from fiction.'],
        ] as [$cat, $title, $excerpt]) {
            BlogPost::create([
                'blog_category_id' => BlogCategory::firstOrCreate(['name' => $cat])->id,
                'author_id' => $author?->id,
                'title' => $title,
                'excerpt' => $excerpt,
                'status' => 'draft',
            ]);
        }
    }
}
