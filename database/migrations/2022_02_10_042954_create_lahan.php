<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLahan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lahan', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->string('nama_lahan');
            $table->string('luas_lahan');
            $table->string('status_kepemilikan'); // sewa atau punya sendiri
            $table->text('alamat_lahan');
            $table->text('latitude');
            $table->text('longitude');
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
        Schema::dropIfExists('lahan');
    }
}
