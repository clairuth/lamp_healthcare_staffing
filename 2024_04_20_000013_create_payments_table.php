<?php

namespace Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('shift_application_id');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->enum('status', ['pending', 'in_escrow', 'completed', 'failed', 'refunded'])->default('pending');
            $table->uuid('payment_method_id')->nullable();
            $table->decimal('transaction_fee', 10, 2)->default(0);
            $table->uuid('payer_id');
            $table->uuid('payee_id');
            $table->timestamp('payment_date')->nullable();
            $table->timestamp('escrow_release_date')->nullable();
            $table->string('external_transaction_id', 255)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('shift_application_id')->references('id')->on('shift_applications')->onDelete('cascade');
            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->onDelete('set null');
            $table->foreign('payer_id')->references('id')->on('users');
            $table->foreign('payee_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
}
