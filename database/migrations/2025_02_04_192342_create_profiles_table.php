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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->string('username')->unique();
            $table->string('firstname');
            $table->string('lastname');
            $table->text('bio')->nullable();
            $table->string('gender');
            $table->date('dob');
            $table->json('location')->nullable();
            $table->string('here_for');
            $table->string('connect_with');
            $table->string('image');
            $table->string('phone');
            $table->string('city');
            $table->string('country');
            $table->unsignedBigInteger('profile_views')->default(0);
            $table->unsignedInteger('weekly_profile_views')->default(0);
            $table->text('favorites')->nullable();
            $table->text('discarded')->nullable();
            $table->text('saved_posts')->nullable();
            $table->string('visibility')->default('public');
            $table->boolean('show_dating_section')->default(true);
            $table->boolean('email_notifications')->default(true);
            $table->boolean('receive_messages')->default(true);
            $table->boolean('online_status')->default(true);
            $table->boolean('top_picks')->default(true);
            $table->boolean('enable_discovery')->default(true);
            $table->boolean('global')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
