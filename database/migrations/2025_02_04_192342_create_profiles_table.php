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
            // $table->float('height', 6, 2);
            $table->date('dob');
            // $table->string('meta_location');
            $table->string('here_for');
            $table->string('connect_with');
            $table->string('image');
            $table->string('phone');
            $table->string('city');
            $table->string('country');
            $table->text('favorites')->default('');
            $table->string('visibility')->default('public');
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
