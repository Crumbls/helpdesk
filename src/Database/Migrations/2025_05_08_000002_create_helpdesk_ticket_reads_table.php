<?php

declare(strict_types=1);

use Crumbls\HelpDesk\Support\UserForeignKey;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('helpdesk_ticket_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('helpdesk_tickets')->cascadeOnDelete();
            UserForeignKey::add($table, 'user_id');
            $table->timestamp('last_read_at');
            $table->timestamps();

            $table->unique(['ticket_id', 'user_id']);
            $table->index(['user_id', 'last_read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('helpdesk_ticket_reads');
    }
};
