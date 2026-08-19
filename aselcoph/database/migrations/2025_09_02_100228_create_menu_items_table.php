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
        if (Schema::hasTable('menu_items')) {
            return;
        }

        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')
                ->constrained('menus')
                ->cascadeOnDelete();

            // Self-referential for submenus (null = root item)
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('menu_items')
                ->cascadeOnDelete();

            $table->string('label');                   // Text shown in UI
            $table->string('icon')->nullable();        // Optional icon name/class

            // How to resolve the link
            $table->enum('link_type', ['url', 'route'])->default('url');

            // If link_type = 'url'
            $table->string('custom_url')->nullable();  // e.g. https://example.com/page

            // If link_type = 'route'
            $table->string('route_name')->nullable();  // e.g. 'projects.show'
            $table->json('route_params')->nullable();  // e.g. {"project":"123"}

            $table->enum('target', ['_self', '_blank'])->default('_self');
            $table->integer('order')->default(0);      // Sort within same parent
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['menu_id', 'parent_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
