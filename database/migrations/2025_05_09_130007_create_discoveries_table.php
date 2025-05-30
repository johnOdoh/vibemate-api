<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('discoveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->string('location')->default('current location');
            $table->unsignedSmallInteger('distance')->default(100);
            $table->unsignedSmallInteger('min_age')->default(18);
            $table->unsignedSmallInteger('max_age')->default(100);
            $table->unsignedSmallInteger('min_photo')->default(1);
            $table->string('open_to')->nullable();
            $table->string('languages')->nullable();
            $table->string('zodiac')->nullable();
            $table->string('education')->nullable();
            $table->string('family_plan')->nullable();
            $table->string('covid_vaccine')->nullable();
            $table->string('personality_style')->nullable();
            $table->string('love_style')->nullable();
            $table->string('communication_style')->nullable();
            $table->string('pets')->nullable();
            $table->string('drinking')->nullable();
            $table->string('smoking')->nullable();
            $table->string('communication')->nullable();
            $table->string('height')->nullable();
            $table->string('down_to_meet')->nullable();
            $table->string('employment_company')->nullable();
            $table->string('employment_type')->nullable();
            $table->string('employment_location')->nullable();
            $table->string('employment_role')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discoveries');
    }
};
