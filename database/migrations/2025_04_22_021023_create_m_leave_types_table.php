<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('m_leave_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('annual_allowance')->default(0);
            $table->boolean('requires_approval')->default(true);
            $table->integer('minimum_notice_days')->default(0);
            $table->integer('maximum_days_per_request')->nullable();
            $table->boolean('carried_forward')->default(false);
            $table->integer('carry_forward_max_days')->nullable();
            $table->boolean('requires_attachment')->default(false);
            $table->boolean('half_day_allowed')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_leave_types');
    }
};
