<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('announcements')) {
            return;
        }

        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->string('category', 32)->default('alert'); // billing | service | alert
            // all = every verified member; users = selected user ids; meter = resolve by meter_no
            $table->string('audience_type', 32)->default('all');
            $table->json('audience_user_ids')->nullable();
            $table->json('meter_numbers')->nullable();
            $table->string('status', 32)->default('draft'); // draft | published
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('sent_count')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'published_at']);
            $table->index('audience_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
