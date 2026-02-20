<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('helpdesk_tickets', function (Blueprint $table) {
            $table->string('reference', 20)->nullable()->unique()->after('id');
        });

        // Backfill existing tickets.
        $prefix = config('helpdesk.reference.prefix', 'HD');
        $pad = config('helpdesk.reference.pad', 5);

        DB::table('helpdesk_tickets')->whereNull('reference')->orderBy('id')->each(function ($ticket) use ($prefix, $pad) {
            DB::table('helpdesk_tickets')
                ->where('id', $ticket->id)
                ->update(['reference' => $prefix . '-' . str_pad((string) $ticket->id, $pad, '0', STR_PAD_LEFT)]);
        });
    }

    public function down(): void
    {
        Schema::table('helpdesk_tickets', function (Blueprint $table) {
            $table->dropColumn('reference');
        });
    }
};
