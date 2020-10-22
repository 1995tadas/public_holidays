<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class YearCombinationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('year_combinations', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->string('country');
            $table->string('region')->nullable();
            $table->integer('total');
            $table->integer('streak');
            $table->timestamps();

            $table->unique(['year', 'country', 'region']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('year_combinations');
    }
}
