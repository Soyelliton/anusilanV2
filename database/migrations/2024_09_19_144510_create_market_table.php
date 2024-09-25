<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMarketTable extends Migration
{
    public function up()
    {
        Schema::create('market', function (Blueprint $table) {
            $table->id();
            $table->string('mname', 50);
        });
    }

    public function down()
    {
        Schema::dropIfExists('market');
    }
}
