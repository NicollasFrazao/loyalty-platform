<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Customers (who sign in by phone), the one-time codes sent to phones, and the companies each
     * customer follows (L2).
     */
    public function up(): void
    {
        Schema::create('customers', static function (Blueprint $table): void {
            $table->id();
            // Null once the customer deleted the account: the row stays for the ledger (L4).
            $table->string('phone', 20)->nullable()->unique();
            $table->string('name', 100)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('document', 20)->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('one_time_codes', static function (Blueprint $table): void {
            $table->id();
            $table->string('phone', 20)->index();
            $table->string('code_hash', 64);
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamp('created_at');
        });

        Schema::create('company_customers', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->timestamp('followed_at');
            $table->unique(['company_id', 'customer_id']);
        });
    }

    /**
     * Drops the tables.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_customers');
        Schema::dropIfExists('one_time_codes');
        Schema::dropIfExists('customers');
    }
};
