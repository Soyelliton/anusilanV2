<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBorrowerAddressTable extends Migration
{
    public function up()
    {
        Schema::create('borrower_address', function (Blueprint $table) {
            $table->foreignId('borrower_id')->constrained('borrower')->onDelete('cascade')->onUpdate('cascade');
            $table->string('address1', 50)->nullable();
            $table->string('address2', 50)->nullable();
            $table->string('city', 50)->nullable();
            $table->string('province', 50)->nullable();
            $table->integer('zipcode')->nullable();
            $table->string('country', 50)->nullable();
            $table->text('post');
            $table->text('police_station');
            $table->string('occupation_landmark', 255);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('borrower_address');
    }
}
