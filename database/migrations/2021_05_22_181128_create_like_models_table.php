<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLikeModelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('like_models', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('opinion',['Like','Unlike']);
            $table->unsignedBigInteger('usermodel_id')->unsigned();
            $table->foreign('usermodel_id')->references('id')->on('user_models');
            $table->unsignedBigInteger('postmodel_id')->unsigned();
            $table->foreign('postmodel_id')->references('id')->on('post_models');
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
        Schema::dropIfExists('like_models');
    }
}
