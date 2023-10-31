<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFrindModelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('frind_models', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('order',['Accept','Non']);
            $table->unsignedBigInteger('usermodel_id')->unsigned();
            $table->foreign('usermodel_id')->references('id')->on('user_models');
            $table->unsignedBigInteger('usermodelx_id')->unsigned();
            $table->foreign('usermodelx_id')->references('id')->on('user_models');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('frind_models');
    }
}
