<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBorrowerTable extends Migration
{
    public function up()
    {
        Schema::create('borrower', function (Blueprint $table) {
            $table->id();
            $table->string('firstname', 50)->nullable();
            $table->string('middlename', 50)->nullable();
            $table->string('lastname', 50)->nullable();
            $table->string('f_h_name', 100);
            $table->string('gender', 20)->nullable();
            $table->date('birthdate')->nullable();
            $table->string('contact', 20)->nullable();
            $table->string('occupation', 50)->nullable();
            $table->text('occupation_address')->nullable();
            $table->text('remarks')->nullable();
            $table->text('avatar')->nullable();
            $table->text('aadhaar');
            $table->string('pan', 20);
            $table->string('voter', 20);
            $table->string('collector', 255);
            $table->date('time')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('borrower');
    }
}
