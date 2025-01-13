<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('google_fonts_library', function (Blueprint $table) {
            $table->id();
            $table->string('family');
            $table->json('variants');
            $table->json('subsets');
            $table->string('category');
            $table->json('tags')->nullable();
            $table->boolean('variable')->default(false);
            $table->date('last_modified');
            $table->boolean('downloaded')->default(false);
            $table->string('location_uri')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('google_fonts_library');
    }
};
