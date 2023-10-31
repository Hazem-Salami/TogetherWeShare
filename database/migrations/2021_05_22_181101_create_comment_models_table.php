<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommentModelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comment_models', function (Blueprint $table) {
            $table->bigIncrements('id');
  
            $table->string('contentText')->nullable();
            $table->string('contentFile')->nullable();
            $table->unsignedBigInteger('usermodel_id')->unsigned();
            $table->foreign('usermodel_id')->references('id')->on('user_models');
            $table->unsignedBigInteger('postmodel_id')->unsigned();
            $table->foreign('postmodel_id')->references('id')->on('post_models');
            $table->unsignedBigInteger('commentmodel_id')->unsigned()->nullable();
            $table->foreign('commentmodel_id')->references('id')->on('comment_models');
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
        Schema::dropIfExists('comment_models');
    }
}
