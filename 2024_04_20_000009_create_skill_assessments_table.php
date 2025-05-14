<?php

namespace Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSkillAssessmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('skill_assessments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('professional_id');
            $table->uuid('skill_id');
            $table->timestamp('assessment_date');
            $table->decimal('score', 5, 2);
            $table->decimal('max_score', 5, 2);
            $table->decimal('percentile', 5, 2)->nullable();
            $table->string('assessment_version', 50);
            $table->timestamps();

            $table->foreign('professional_id')->references('id')->on('healthcare_professionals')->onDelete('cascade');
            $table->foreign('skill_id')->references('id')->on('skills')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('skill_assessments');
    }
}
