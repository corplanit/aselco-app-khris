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
       Schema::create('billing_uploads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_link_id')->constrained()->onDelete('cascade'); // link to account
            $table->string('file_path'); // path to the uploaded PDF
            $table->decimal('amount', 10, 2);
            $table->date('billing_date');
            $table->foreignId('uploaded_by')->constrained('users'); // staff who uploaded
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_uploads');
    }
};
