<?php

namespace Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShiftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('facility_id');
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->enum('required_role', ['RN', 'LPN', 'LVN', 'CNA', 'STNA', 'CMA', 'Med-Tech', 'OR Tech', 'Rad Tech', 'ER RN', 'ICU/NICU RN', 'OR RN', 'PREOP/PACU RN', 'L&D RN']);
            $table->timestamp('start_time');
            $table->timestamp('end_time');
            $table->decimal('hourly_rate', 10, 2);
            $table->enum('status', ['open', 'filled', 'in_progress', 'completed', 'cancelled'])->default('open');
            $table->boolean('is_urgent')->default(false);
            $table->text('required_skills')->nullable(); // JSON array of skill IDs
            $table->integer('min_experience_years')->default(0);
            $table->integer('max_applicants')->default(10);
            $table->timestamps();

            $table->foreign('facility_id')->references('id')->on('facilities')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shifts');
    }
}
