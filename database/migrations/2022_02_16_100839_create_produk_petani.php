<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProdukPetani extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('produk_petani', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->date('tanggal_panen');
            $table->string('kualitas');
            $table->decimal('harga', 24,2);
            $table->bigInteger('volume');
            $table->text('status');
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
        Schema::dropIfExists('produk_petani');
    }
}
