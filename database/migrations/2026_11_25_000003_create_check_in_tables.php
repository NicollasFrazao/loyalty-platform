<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Each company's check-in rules and check-ins, its levels, and each customer's level and spend at
     * the companies they follow (L3).
     */
    public function up(): void
    {
        Schema::create('check_in_settings', static function (Blueprint $table): void {
            $table->foreignId('company_id')->primary()->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('cooldown_minutes');
            $table->boolean('requires_confirmation');
            // The fence is off while these are null.
            $table->decimal('fence_latitude', 9, 6)->nullable();
            $table->decimal('fence_longitude', 9, 6)->nullable();
            $table->unsignedSmallInteger('fence_radius_metres')->nullable();
        });

        Schema::create('check_ins', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20);
            $table->decimal('latitude', 9, 6)->nullable();
            $table->decimal('longitude', 9, 6)->nullable();
            $table->timestamp('checked_in_at');
            $table->index(['company_id', 'customer_id', 'checked_in_at']);
            $table->index(['company_id', 'status']);
        });

        Schema::create('level_ladders', static function (Blueprint $table): void {
            $table->foreignId('company_id')->primary()->constrained()->cascadeOnDelete();
            $table->string('metric', 20);
        });

        Schema::create('levels', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('level_ladders', 'company_id')->cascadeOnDelete();
            $table->unsignedTinyInteger('position');
            $table->string('name', 100);
            $table->unsignedBigInteger('threshold');
            $table->unsignedSmallInteger('cashback_basis_points');
            $table->unique(['company_id', 'position']);
        });

        Schema::table('company_customers', static function (Blueprint $table): void {
            // The level last reached, by name; null until the first check-in counts.
            $table->string('level', 100)->nullable();
            // Written by the wallet (L4); zero until then.
            $table->unsignedBigInteger('spent_cents')->default(0);
        });
    }

    /**
     * Drops the tables and columns.
     */
    public function down(): void
    {
        Schema::table('company_customers', static function (Blueprint $table): void {
            $table->dropColumn(['level', 'spent_cents']);
        });
        Schema::dropIfExists('levels');
        Schema::dropIfExists('level_ladders');
        Schema::dropIfExists('check_ins');
        Schema::dropIfExists('check_in_settings');
    }
};
