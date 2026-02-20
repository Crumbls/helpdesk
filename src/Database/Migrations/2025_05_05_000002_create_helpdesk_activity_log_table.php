<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('helpdesk_activity_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('helpdesk_tickets')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 50); // created, status_changed, assigned, commented, merged, rated, attachment_added
            $table->string('description');
            $table->json('metadata')->nullable(); // old/new values, extra context
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('helpdesk_activity_log');
    }
};
