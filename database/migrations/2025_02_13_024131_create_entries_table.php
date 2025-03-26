<?php

use App\Models\Competition;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('entries', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Competition::class)->constrained()->cascadeOnDelete();
            $table->string('url');
            $table->string('media_id')->nullable()->unique();
            $table->string('is_image')->default(true);
            $table->string('status')->default('approved');
            $table->unsignedbigInteger('no_of_engagements')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'competition_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entries');
    }
};
