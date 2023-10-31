<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHaveCertificateModelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('have_certificate_models', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('usermodel_id')->unsigned();
            $table->foreign('usermodel_id')->references('id')->on('user_models');
            $table->unsignedBigInteger('certificatemodel_id')->unsigned();
            $table->foreign('certificatemodel_id')->references('id')->on('certificate_models');
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
        Schema::dropIfExists('have_certificate_models');
    }
}
