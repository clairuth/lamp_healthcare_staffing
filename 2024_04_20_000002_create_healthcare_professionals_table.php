<?php

namespace Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHealthcareProfessionalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('healthcare_professionals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->enum('professional_type', ['RN', 'LPN', 'LVN', 'CNA', 'STNA', 'CMA', 'Med-Tech', 'OR Tech', 'Rad Tech', 'ER RN', 'ICU/NICU RN', 'OR RN', 'PREOP/PACU RN', 'L&D RN']);
            $table->integer('years_experience');
            $table->text('bio')->nullable();
            $table->decimal('hourly_rate_min', 10, 2)->nullable();
            $table->enum('availability_status', ['available', 'unavailable', 'limited'])->default('available');
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('total_shifts_completed')->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('healthcare_professionals');
    }
}
