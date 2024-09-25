<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoanTable extends Migration
{
    public function up()
    {
        Schema::create('loan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('borrower_id')->nullable()->constrained('borrower')->onDelete('cascade')->onUpdate('cascade');
            $table->string('loan_type', 20)->nullable();
            $table->integer('principal')->nullable();
            $table->integer('terms')->nullable();
            $table->string('terms2', 20)->nullable();
            $table->integer('interest')->nullable();
            $table->integer('penalty')->nullable();
            $table->date('date_started')->nullable();
            $table->date('maturity_date')->nullable();
            $table->decimal('monthly', 10, 2)->nullable();
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->text('co_maker')->nullable();
            $table->text('co_maker2')->nullable();
            $table->integer('status')->nullable();
            $table->integer('loan_no');
            $table->integer('extra_amount')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('loan');
    }
}
