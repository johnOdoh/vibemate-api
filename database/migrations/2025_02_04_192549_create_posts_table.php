<?php

use App\Models\User;
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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->string('url')->nullable();
            $table->string('is_image')->default(true);
            $table->text('body')->nullable();
            $table->unsignedBigInteger('no_of_engagements')->default(0);
            $table->string('hashtags')->nullable();
            $table->string('privacy');
            $table->string('location')->nullable();
            $table->boolean('allow_comments');
            $table->boolean('allow_like_counts');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
