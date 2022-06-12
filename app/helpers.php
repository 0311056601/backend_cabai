<?php

use Illuminate\Support\Arr;
use App\Models\Keranjang;
use App\Models\TransaksiCabai;
use App\Models\GapoktanGudang;
use App\Models\RequestProduk;
use App\Models\MHargaCabaiPetani;
use App\Models\MHargaPengemasan;

function CekSupplyDemand($tanggalSekarang, $gapoktan, $kualitas) {
   
    // get data supply
    $supply = GapoktanGudang::where('user_gapoktan', $gapoktan)->where('kualitas', $kualitas)->where('status', 1)->sum('volume');

    // get data demand
    // $demand = RequestProduk::where('gapoktan_id', $gapoktan)->where('kualitas', $kualitas)->where('tanggal_pembelian', $tanggalSekarang)->where('status', 'dikonfirmasi gapoktan')->sum('volume');
    $demand = RequestProduk::where('gapoktan_id', $gapoktan)->where('kualitas', $kualitas)->where('tanggal_pembelian', $tanggalSekarang)->sum('volume');

    // data harga
    // $hargacabai = MHargaCabaiPetani::where('gapoktan_id', $gapoktan)->where('kualitas', $kualitas)->where('status', 1)->first();
    // $hargapackaging = MHargaPengemasan::where('status', 1)->where('gapoktan_id', $gapoktan)->first();


    // $data = compact(
    //     'supply',
    //     'demand'
    // );

    // perhitungan supply demand
    if($supply < $demand) {


        return true;

        // harga naik 20%
        // return harga naik 20%

    } else {

        return false;

        // harga tetap
        // return harga tetap

    }


}

function getHargaPesananCabai($noTx) {

    $dataTransaksi = RequestProduk::where('no_transaksi', $noTx)->first();
   
    // get data supply
    $supply = GapoktanGudang::where('user_gapoktan', $dataTransaksi->gapoktan_id)->where('kualitas', $dataTransaksi->kualitas)->where('status', 1)->sum('volume');

    // get data demand
    $demand = RequestProduk::where('gapoktan_id', $dataTransaksi->gapoktan_id)->where('kualitas', $dataTransaksi->kualitas)->where('tanggal_pembelian', $dataTransaksi->tanggal_pembelian)->sum('volume');

    // data harga
    $hargacabai = MHargaCabaiPetani::where('gapoktan_id', $dataTransaksi->gapoktan_id)->where('kualitas', $dataTransaksi->kualitas)->where('status', 1)->first()->harga_jual;
    $hargapackaging = MHargaPengemasan::where('status', 1)->where('gapoktan_id', $dataTransaksi->gapoktan_id)->first()->harga;

    $harga = ($hargacabai * $dataTransaksi->volume) + ($hargapackaging * $dataTransaksi->volume);

    // perhitungan supply demand
    if($supply < $demand) {        

        // harga naik 20%
        $harga = ($harga * 20 / 100) + $harga;

    }

    return $harga;


}