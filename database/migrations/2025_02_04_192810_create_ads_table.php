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
        Schema::create('ads', function (Blueprint $table) {
            $table->id();
            $table->string('image');
            $table->string('target_url');
            $table->decimal('amount', 10, 2);
            $table->string('action');
            $table->string('regions');
            $table->string('screen');
            $table->date('start');
            $table->date('end');
            $table->unsignedBigInteger('no_of_clicks')->default(0);
            $table->unsignedBigInteger('no_of_views')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ads');
    }
};
