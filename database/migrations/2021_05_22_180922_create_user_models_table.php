<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserModelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_models', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('firstName');
            $table->string('lastName');
            $table->string('password');
            $table->string('email')->unique();
            $table->string('work')->nullable();
            $table->enum('gender',['Male','Female','Non'])->nullable();
            $table->string('pictureProfile');
            $table->string('pictureWall');
            $table->Date('birth')->nullable();
            $table->string('about')->nullable();
            $table->text('remember_token')->nullable();
            $table->unsignedBigInteger('regionmodel_id')->unsigned();
            $table->foreign('regionmodel_id')->references('id')->on('region_models');
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
        Schema::dropIfExists('user_models');
    }
}
