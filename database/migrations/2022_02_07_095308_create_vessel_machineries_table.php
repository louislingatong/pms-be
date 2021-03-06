<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVesselMachineriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vessel_machineries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('vessel_id');
            $table->unsignedBigInteger('machinery_id');
            $table->unsignedBigInteger('incharge_rank_id');
            $table->unsignedBigInteger('machinery_model_id')->nullable();
            $table->unsignedBigInteger('machinery_maker_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('vessel_id')
                ->references('id')
                ->on('vessels')
                ->onDelete('cascade');
            $table->foreign('machinery_id')
                ->references('id')
                ->on('machineries')
                ->onDelete('cascade');
            $table->foreign('incharge_rank_id')
                ->references('id')
                ->on('ranks')
                ->onDelete('cascade');
            $table->foreign('machinery_model_id')
                ->references('id')
                ->on('machinery_models')
                ->onDelete('cascade');
            $table->foreign('machinery_maker_id')
                ->references('id')
                ->on('machinery_makers')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vessel_machineries');
    }
}
