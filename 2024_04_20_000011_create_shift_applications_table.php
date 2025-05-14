<?php

namespace Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShiftApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shift_applications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('shift_id');
            $table->uuid('professional_id');
            $table->enum('application_status', ['pending', 'accepted', 'rejected', 'withdrawn'])->default('pending');
            $table->timestamp('application_date')->useCurrent();
            $table->text('notes')->nullable();
            $table->timestamp('check_in_time')->nullable();
            $table->timestamp('check_out_time')->nullable();
            $table->integer('facility_rating')->nullable();
            $table->text('facility_review')->nullable();
            $table->integer('professional_rating')->nullable();
            $table->text('professional_review')->nullable();
            $table->timestamps();

            $table->foreign('shift_id')->references('id')->on('shifts')->onDelete('cascade');
            $table->foreign('professional_id')->references('id')->on('healthcare_professionals')->onDelete('cascade');
            
            // Ensure a professional can't apply to the same shift twice
            $table->unique(['shift_id', 'professional_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shift_applications');
    }
}
