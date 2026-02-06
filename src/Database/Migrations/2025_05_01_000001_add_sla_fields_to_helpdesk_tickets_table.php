<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('helpdesk_tickets', function (Blueprint $table) {
            $table->timestamp('first_response_at')->nullable()->after('closed_at');
            $table->timestamp('sla_response_due_at')->nullable()->after('first_response_at');
            $table->timestamp('sla_resolution_due_at')->nullable()->after('sla_response_due_at');
            $table->boolean('sla_response_breached')->default(false)->after('sla_resolution_due_at');
            $table->boolean('sla_resolution_breached')->default(false)->after('sla_response_breached');
        });
    }
};
