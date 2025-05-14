<?php

namespace Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfessionalSkillsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('professional_skills', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('professional_id');
            $table->uuid('skill_id');
            $table->enum('proficiency_level', ['beginner', 'intermediate', 'advanced', 'expert'])->default('beginner');
            $table->integer('years_experience')->default(0);
            $table->boolean('is_verified')->default(false);
            $table->timestamps();

            $table->foreign('professional_id')->references('id')->on('healthcare_professionals')->onDelete('cascade');
            $table->foreign('skill_id')->references('id')->on('skills')->onDelete('cascade');
            
            // Ensure a professional can't have the same skill twice
            $table->unique(['professional_id', 'skill_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('professional_skills');
    }
}
