<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Extra images shown on a treatment's detail page (e.g. stages of a condition, the procedure). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treatment_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('treatment_id')->constrained()->cascadeOnDelete();
            $table->string('image');
            $table->string('caption', 160)->nullable();
            $table->string('alt')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['treatment_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treatment_images');
    }
};
