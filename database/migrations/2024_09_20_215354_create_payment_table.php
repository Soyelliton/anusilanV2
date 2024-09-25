<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentTable extends Migration
{
    public function up()
    {
        Schema::create('payment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->nullable()->constrained('loan')->onDelete('cascade')->onUpdate('cascade');
            $table->date('due_date')->nullable();
            $table->decimal('due', 10, 2)->nullable();
            $table->decimal('p_interest', 10, 2)->nullable();
            $table->decimal('p_penalty', 10, 2)->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->decimal('balance', 10, 2)->nullable();
            $table->string('remarks', 100)->nullable();
            $table->string('status', 20)->nullable();
            $table->date('date')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment');
    }
}
