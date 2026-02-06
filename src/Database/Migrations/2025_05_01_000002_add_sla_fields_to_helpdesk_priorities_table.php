<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('helpdesk_priorities', function (Blueprint $table) {
            $table->integer('sla_response_hours')->nullable()->after('is_default');
            $table->integer('sla_resolution_hours')->nullable()->after('sla_response_hours');
        });
    }
};
