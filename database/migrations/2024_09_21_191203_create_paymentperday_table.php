<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentperdayTable extends Migration
{
    public function up()
    {
        Schema::create('paymentperday', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('loan')->onDelete('cascade');
            $table->date('date');
            $table->integer('perdaypayment');
            $table->integer('remainingbalance');
            $table->integer('daily_collect')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('paymentperday');
    }
}
