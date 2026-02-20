<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('helpdesk_tickets', function (Blueprint $table) {
            $table->string('submitter_name')->nullable()->after('submitter_id');
            $table->string('submitter_email')->nullable()->after('submitter_name');
            $table->string('submitter_phone')->nullable()->after('submitter_email');
            $table->string('submitter_company')->nullable()->after('submitter_phone');
            $table->json('metadata')->nullable()->after('source');
        });
    }

    public function down(): void
    {
        Schema::table('helpdesk_tickets', function (Blueprint $table) {
            $table->dropColumn([
                'submitter_name',
                'submitter_email',
                'submitter_phone',
                'submitter_company',
                'metadata',
            ]);
        });
    }
};
