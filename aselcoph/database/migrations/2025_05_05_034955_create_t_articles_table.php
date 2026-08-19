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
        Schema::create('t_articles', function (Blueprint $table) {
            $table->id('post_id');
            $table->string('post_title');
            $table->string('post_category');
            $table->text('post_content')->nullable();
            $table->text('post_comment')->nullable();
            $table->string('post_thumbnail')->nullable();
            $table->boolean('post_isActive')->default(true);
            $table->unsignedBigInteger('post_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_articles');
    }
};
