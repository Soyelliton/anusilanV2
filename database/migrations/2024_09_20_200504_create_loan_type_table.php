<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoanTypeTable extends Migration
{
    public function up()
    {
        Schema::create('loan_type', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->nullable();
            $table->integer('interest')->nullable();
            $table->integer('terms')->nullable();
            $table->string('terms2', 20)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('loan_type');
    }
}
