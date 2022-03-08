<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMachineriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('machineries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('vessel_department_id');
            $table->string('code_name')->nullable();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('vessel_department_id')
                ->references('id')
                ->on('vessel_departments')
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
        Schema::dropIfExists('machineries');
    }
}
