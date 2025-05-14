<?php

namespace Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCredentialVerificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('credential_verifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('credential_id');
            $table->uuid('verified_by')->nullable();
            $table->timestamp('verification_date')->nullable();
            $table->enum('verification_method', ['manual', 'automated', 'third_party'])->nullable();
            $table->text('verification_notes')->nullable();
            $table->timestamps();

            $table->foreign('credential_id')->references('id')->on('credentials')->onDelete('cascade');
            $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('credential_verifications');
    }
}
