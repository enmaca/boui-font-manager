<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // dump($name, $subFamily, $subFamilyId, $fullName, $version, $weight, $postScriptName, $copyright, $type);
        Schema::create('fonts', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('active')->default(true);
            $table->json('tags')->nullable();
            $table->timestamps();
        });

        Schema::create('font_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('font_id')->constrained('fonts')->cascadeOnDelete();
            $table->string('sub_family')->index()->nullable();
            $table->string('sub_family_id')->index()->nullable();
            $table->string('full_name')->index()->nullable();
            $table->string('version')->index()->nullable();
            $table->string('weight')->index()->nullable();
            $table->string('post_script_name')->index()->nullable();
            $table->string('copyright')->index()->nullable();
            $table->string('type')->index()->nullable();
            $table->timestamps();
        });

        Schema::create('font_files', function (Blueprint $table) {
            $table->id();
            //$table->foreignId('font_variant_id')->constrained('font_variants')->cascadeOnDelete();
            $table->string('font_origin_type')->index()->nullable();
            $table->string('font_origin_id')->index()->nullable();
            $table->string('version')->default(1);
            $table->string('version_comments')->nullable();
            $table->boolean('default')->default(false);
            $table->string('original_name')->index()->nullable();
            $table->string('extension')->index()->nullable();
            $table->string('mime_type')->index()->nullable();
            $table->unsignedBigInteger('size')->index()->nullable();
            $table->string('uri');
            $table->boolean('local')->default(true);
            $table->timestamps();
        });

        Schema::create('font_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('font_category_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('font_id')->constrained('fonts')->cascadeOnDelete(); // Relación con la tabla fonts
            $table->foreignId('category_id')->constrained('font_categories')->cascadeOnDelete(); // Relación con font_categories
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('font_files');
        Schema::dropIfExists('font_variants');
        Schema::dropIfExists('font_category_details');
        Schema::dropIfExists('font_categories');
        Schema::dropIfExists('fonts');
    }
};
