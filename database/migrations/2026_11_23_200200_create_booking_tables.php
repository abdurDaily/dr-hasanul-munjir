<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');                    // "In-Clinic Visit"
            $table->string('short_label', 40);         // "In-Clinic"
            $table->string('slug')->unique();
            $table->string('icon', 60);
            $table->string('note')->nullable();        // shown in the booking summary
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Weekly chamber hours. weekday: 0 = Sunday … 6 = Saturday (Carbon::dayOfWeek)
        Schema::create('schedule_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('weekday');
            $table->string('name', 40);                // Morning / Evening
            $table->time('starts_at');
            $table->time('ends_at');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index('weekday');
        });

        // Recurring breaks inside a session; weekday null = every day
        Schema::create('schedule_breaks', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('weekday')->nullable();
            $table->time('starts_at');
            $table->time('ends_at');
            $table->string('label', 80)->nullable();
            $table->timestamps();
        });

        // Holidays, leave, closures (inclusive range)
        Schema::create('unavailable_dates', function (Blueprint $table) {
            $table->id();
            $table->date('starts_on');
            $table->date('ends_on');
            $table->string('type', 20)->default('holiday');
            $table->string('reason')->nullable();
            $table->timestamps();
            $table->index(['starts_on', 'ends_on']);
        });

        Schema::create('blocked_slots', function (Blueprint $table) {
            $table->id();
            $table->date('date')->index();
            $table->time('starts_at');
            $table->time('ends_at');
            $table->string('reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20)->unique();
            $table->foreignId('appointment_type_id')->constrained()->restrictOnDelete();
            $table->foreignId('treatment_id')->nullable()->constrained()->nullOnDelete();
            $table->date('appointment_date');
            $table->time('start_time');
            $table->time('end_time');
            // "Y-m-d H:i" while the appointment holds its slot (pending/confirmed/completed/no-show),
            // NULL once cancelled or rejected. The unique index is the final guard against double booking.
            $table->string('slot_key', 20)->nullable()->unique();
            $table->string('status', 20)->default('pending');
            $table->string('patient_name', 120);
            $table->string('patient_phone', 20);
            $table->string('patient_email')->nullable();
            $table->text('message')->nullable();
            $table->string('source', 20)->default('website');
            $table->string('ip_address', 45)->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['appointment_date', 'start_time']);
            $table->index(['status', 'appointment_date']);
            $table->index('patient_phone');
        });

        Schema::create('appointment_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 30);
            $table->string('from_status', 20)->nullable();
            $table->string('to_status', 20)->nullable();
            $table->text('note')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('subject', 150)->nullable();
            $table->text('message');
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('read_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('status', 20)->default('subscribed');
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['newsletter_subscribers', 'contact_messages', 'appointment_activities', 'appointments',
            'blocked_slots', 'unavailable_dates', 'schedule_breaks', 'schedule_sessions', 'appointment_types'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
