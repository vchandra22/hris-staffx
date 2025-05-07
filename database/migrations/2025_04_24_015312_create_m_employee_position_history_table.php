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
        Schema::create('m_employee_position_history', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('employee_id')->comment('References id in m_employees table');
            $table->uuid('position_id')->comment('References id in m_positions table');
            $table->uuid('department_id')->comment('References id in m_departments table');
            $table->date('start_date')->comment('Start date of the position');
            $table->date('end_date')->nullable()->comment('End date of the position, null if current');
            $table->boolean('is_current')->default(true)->comment('Flag to mark if this is the current position');
            $table->decimal('salary', 10, 2)->nullable()->comment('Salary at this position');
            $table->text('notes')->nullable()->comment('Additional notes about the position change');
            $table->uuid('approved_by')->nullable()->comment('User who approved the position change');
            $table->uuid('created_by')->nullable()->comment('User who created the record');
            $table->timestamps();
            $table->softDeletes();

            $table->index('employee_id');
            $table->index('position_id');
            $table->index('department_id');
            $table->index('start_date');
            $table->index('end_date');
            $table->index('is_current');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_employee_position_history');
    }
};
