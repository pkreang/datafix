<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submission_activity_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('document_form_submissions')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 32);  // created/updated/submitted/printed/duplicated/deleted
            $table->json('meta')->nullable(); // optional extras (ref_no at time of action, etc.)
            $table->timestamp('created_at')->nullable();

            $table->index(['submission_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_activity_log');
    }
};
