<?php

namespace Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCredentialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('credentials', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('professional_id');
            $table->enum('credential_type', ['license', 'certification', 'id', 'vaccination', 'background_check']);
            $table->string('credential_name', 255);
            $table->string('credential_number', 100)->nullable();
            $table->string('issuing_authority', 255);
            $table->date('issue_date');
            $table->date('expiration_date');
            $table->string('document_url', 255);
            $table->enum('verification_status', ['pending', 'verified', 'rejected', 'expired'])->default('pending');
            $table->timestamps();

            $table->foreign('professional_id')->references('id')->on('healthcare_professionals')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('credentials');
    }
}
