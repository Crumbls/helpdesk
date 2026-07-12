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
        Schema::create('helpdesk_department_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('helpdesk_departments')->cascadeOnDelete();
            UserForeignKey::add($table, 'user_id');
            $table->string('role')->default('agent');
            $table->boolean('assigned_only')->default(false);
            $table->timestamps();

            $table->unique(['department_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('helpdesk_department_user');
    }
};
