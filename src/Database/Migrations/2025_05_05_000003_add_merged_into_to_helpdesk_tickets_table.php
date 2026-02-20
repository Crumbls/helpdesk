<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('helpdesk_tickets', function (Blueprint $table) {
            $table->foreignId('merged_into_ticket_id')->nullable()->after('parent_ticket_id')
                ->constrained('helpdesk_tickets')->nullOnDelete();
            $table->timestamp('merged_at')->nullable()->after('closed_at');
        });
    }

    public function down(): void
    {
        Schema::table('helpdesk_tickets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('merged_into_ticket_id');
            $table->dropColumn('merged_at');
        });
    }
};
