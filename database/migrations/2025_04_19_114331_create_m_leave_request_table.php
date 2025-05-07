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
        Schema::create('m_leave_request', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('employee_id')->comment('References id in employees table');
            $table->date('start_date');
            $table->date('end_date');
            $table->text('reason');
            $table->uuid('leave_type_id')->comment('References id in leave_types table');
            $table->decimal('total_days', 5, 1)->default(0);
            $table->boolean('half_day')->default(false);
            $table->time('half_day_time')->nullable();
            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
                'cancelled',
                'in_progress',
                'completed'
            ])->default('pending');
            $table->uuid('approved_by')->nullable()->comment('References id in users table');
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('attachment_type')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('employee_id');
            $table->index(['start_date', 'end_date']);
            $table->index('status');
            $table->index('leave_type_id');
            $table->index('approved_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_leave_request');
    }
};
