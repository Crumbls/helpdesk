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
        Schema::create('helpdesk_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_type_id')->constrained('helpdesk_ticket_types')->cascadeOnDelete();
            $table->foreignId('ticket_status_id')->constrained('helpdesk_ticket_statuses')->cascadeOnDelete();
            $table->foreignId('submitter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('helpdesk_departments')->nullOnDelete();
            $table->foreignId('priority_id')->nullable()->constrained('helpdesk_priorities')->nullOnDelete();
            $table->foreignId('parent_ticket_id')->nullable()->constrained('helpdesk_tickets')->nullOnDelete();
            $table->string('title');
            $table->text('description');
            $table->text('resolution')->nullable();
            $table->string('source')->default('web');
            $table->timestamp('due_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('helpdesk_tickets');
    }
};
