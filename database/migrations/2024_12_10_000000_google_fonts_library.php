<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('google_font_families', function (Blueprint $table) {
            $table->id();
            $table->string('family');
            $table->json('subsets');
            $table->string('category');
            $table->string('version')->nullable();
            $table->date('last_modified');
            $table->timestamps();
        });

        Schema::create('google_font_variants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('google_font_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('google_font_family_id')->constrained('google_font_families');
            $table->foreignId('google_font_variant_id')->constrained('google_font_variants');
            $table->boolean('downloaded')->default(false);
            $table->longText('uri');
            $table->timestamps();
        });

        Schema::create('google_font_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('google_font_family_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('google_font_family_id')->constrained('google_font_families');
            $table->foreignId('google_font_tag_id')->constrained('google_font_tags');
            $table->timestamps();
        });

    }

    public function down(): void
    {

        Schema::dropIfExists('google_font_family_tags');
        Schema::dropIfExists('google_font_files');
        Schema::dropIfExists('google_font_tags');
        Schema::dropIfExists('google_font_variants');
        Schema::dropIfExists('google_font_families');

    }
};
