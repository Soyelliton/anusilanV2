<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDailyTable extends Migration
{
    public function up()
    {
        Schema::create('daily', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->integer('market_in');
            $table->integer('market_out');
            $table->integer('market_exist');
            $table->integer('member_add');
            $table->integer('full_paid');
            $table->integer('exist_member');
            $table->integer('loan_amount');
            $table->integer('interest');
            $table->integer('realisable');
            $table->integer('realised');
            $table->string('outstanding', 255);
            $table->integer('cash');
        });
    }

    public function down()
    {
        Schema::dropIfExists('daily');
    }
}
