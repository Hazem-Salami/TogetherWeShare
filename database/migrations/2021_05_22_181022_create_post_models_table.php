<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostModelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('post_models', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('contentText')->nullable();
            $table->string('contentFile')->nullable();
            $table->boolean('hide');
            $table->enum('privacy',['Frinds','General']);
            $table->unsignedBigInteger('usermodel_id')->unsigned();
            $table->foreign('usermodel_id')->references('id')->on('user_models');
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
        Schema::dropIfExists('post_models');
    }
}
