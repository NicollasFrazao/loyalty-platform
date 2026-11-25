<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Companies (the tenants), the modules each enabled, and the company and role of each staff member
     * (the framework's `users`, who sign in to the back office).
     */
    public function up(): void
    {
        Schema::create('companies', static function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100);
            $table->string('plan', 20);
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });

        Schema::create('company_modules', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('module', 30);
            $table->unique(['company_id', 'module']);
        });

        Schema::table('users', static function (Blueprint $table): void {
            $table->foreignId('company_id')->after('id')->constrained()->cascadeOnDelete();
            $table->string('role', 20)->after('password');
        });
    }

    /**
     * Drops the tables and the columns.
     */
    public function down(): void
    {
        Schema::table('users', static function (Blueprint $table): void {
            $table->dropConstrainedForeignId('company_id');
            $table->dropColumn('role');
        });
        Schema::dropIfExists('company_modules');
        Schema::dropIfExists('companies');
    }
};
