<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// api tanpa login
Route::post('v2/login', [App\Http\Controllers\API\LoginController::class, 'login']);
Route::post('v2/register', [App\Http\Controllers\API\LoginController::class, 'register']);
Route::post('v2/DaftarGapoktan', [App\Http\Controllers\API\LoginController::class, 'DaftarGapoktan']);
Route::get('v2/indexRequestData', [App\Http\Controllers\API\ExternalController::class, 'indexRequestData']);
Route::get('v2/getRD/{address}', [App\Http\Controllers\API\ExternalController::class, 'getRD']);
Route::post('v2/RequestData', [App\Http\Controllers\API\ExternalController::class, 'RequestData']);
Route::get('v2/get-produk-siap-jual/{produkId}', [App\Http\Controllers\API\ExternalController::class, 'getDataQRProduk']);
Route::get('v2/get-market-data', [App\Http\Controllers\API\ExternalController::class, 'getMarketGapoktan']);
Route::get('v2/get-market-produk/{gapoktanId}', [App\Http\Controllers\API\ExternalController::class, 'getProdukGapoktanSelected']);
Route::get('v2/getTransaksiQR/{noTx}', [App\Http\Controllers\API\ExternalController::class, 'getTransaksiQR']);
Route::get('v2/getTransaksiQR/request/{noTx}', [App\Http\Controllers\API\ExternalController::class, 'getTransaksiRequestQR']);
Route::get('v2/getDetailTransaksi/{noTx}', [App\Http\Controllers\API\ExternalController::class, 'getDetailTransaksi']);
Route::get('v2/GetInvoice/{noTx}', [App\Http\Controllers\API\ExternalController::class, 'GetInvoice']);
Route::get('v2/GetInvoice/request/{noTx}', [App\Http\Controllers\API\ExternalController::class, 'GetInvoiceRequest']);
Route::get('v2/getGapoktanList', [App\Http\Controllers\API\ExternalController::class, 'getGapoktanList']);
Route::get('v2/getGapoktanHarga/{gapoktanId}', [App\Http\Controllers\API\ExternalController::class, 'getGapoktanHarga']);
Route::post('v2/addLogRequestData', [App\Http\Controllers\API\ExternalController::class, 'addLogRequestData']);
Route::get('v2/getExpiredQR/{expiredId}', [App\Http\Controllers\API\ExternalController::class, 'getExpiredQR']);
Route::get('v2/getMasterBank', [App\Http\Controllers\API\ExternalController::class, 'getMasterBank']);
Route::get('v2/getDashboardLogin', [App\Http\Controllers\API\ExternalController::class, 'getDashboardLogin']);
Route::get('v2/gapoktan-get-data-gudang/{gapoktanId}', [App\Http\Controllers\API\ExternalController::class, 'getdataGudang']);

Route::get('v2/testing', [App\Http\Controllers\API\ExternalController::class, 'testing']);

// api harus login ditaruh didalam sini
Route::group(['middleware' => 'auth:api'], function () { 

    Route::get('v2/get-all-user', [App\Http\Controllers\API\UserController::class, 'index']);
    Route::get('v2/change-status-petani/{userId}', [App\Http\Controllers\API\UserController::class, 'changeStatus']);
    Route::post('v2/daftar-akun-petani', [App\Http\Controllers\API\UserController::class, 'daftarPetani']);
    Route::get('v2/detail-user', [App\Http\Controllers\API\UserController::class, 'detailUser']);
    Route::post('v2/insert-profile', [App\Http\Controllers\API\UserController::class, 'insertProfile']);
    Route::get('v2/getNotif', [App\Http\Controllers\API\UserController::class, 'getNotif']);
    Route::get('v2/ListNotifikasi', [App\Http\Controllers\API\UserController::class, 'ListNotifikasi']);
    Route::get('v2/NotifikasiDetail/{notifId}', [App\Http\Controllers\API\UserController::class, 'NotifikasiDetail']);
    Route::get('v2/HapusNotif/{notifId}', [App\Http\Controllers\API\UserController::class, 'HapusNotif']);
    Route::get('v2/detailSaldo', [App\Http\Controllers\API\UserController::class, 'detailSaldo']);

    Route::get('v2/listLahan', [App\Http\Controllers\API\LahanController::class, 'listLahan']);
    Route::get('v2/listLahanWithPetaniId/{petaniId}', [App\Http\Controllers\API\LahanController::class, 'listLahanWithPetaniId']);
    Route::get('v2/DetailLahan/{lahanId}', [App\Http\Controllers\API\LahanController::class, 'DetailLahan']);
    Route::post('v2/simpanLahan', [App\Http\Controllers\API\LahanController::class, 'simpanLahan']);
    Route::get('v2/HapusLahan/{lahanId}', [App\Http\Controllers\API\LahanController::class, 'hapusLahan']);
    Route::post('v2/updateLahan/{lahanId}', [App\Http\Controllers\API\LahanController::class, 'updateLahan']);

    Route::get('v2/listProdukPetani', [App\Http\Controllers\API\ProdukController::class, 'listProdukPetani']);
    Route::get('v2/getTracePanen/{produkPetaniId}', [App\Http\Controllers\API\ProdukController::class, 'getTracePanen']);
    Route::post('v2/simpan-produk-petani', [App\Http\Controllers\API\ProdukController::class, 'addProdukPetani']);
    Route::post('v2/updateProdukPetani/{produkId}', [App\Http\Controllers\API\ProdukController::class, 'updateProdukPetani']);
    Route::get('v2/detailProdukPetani/{produkId}', [App\Http\Controllers\API\ProdukController::class, 'detailProdukPetani']);
    Route::get('v2/petani-hapus-produk/{produkId}', [App\Http\Controllers\API\ProdukController::class, 'hapusProdukPetani']);
    Route::get('v2/petani-kirim-produk/{produkId}', [App\Http\Controllers\API\ProdukController::class, 'petaniKirimPeroduk']);

    Route::get('v2/getDashboardPetaniHome', [App\Http\Controllers\API\PetaniController::class, 'index']);

    Route::get('v2/getDashboardGapoktan', [App\Http\Controllers\API\GapoktanController::class, 'index']);
    Route::get('v2/getDashboardSummaryGapoktan', [App\Http\Controllers\API\GapoktanController::class, 'chartSummary']);
    Route::get('v2/gapoktan-get-produk', [App\Http\Controllers\API\GapoktanController::class, 'listProdukPetani']);
    Route::get('v2/gapoktan-get-data-gudang', [App\Http\Controllers\API\GapoktanController::class, 'dataGudang']);
    Route::get('v2/gapoktan-konrimasi-produk/{produkId}', [App\Http\Controllers\API\GapoktanController::class, 'konfirmasiProdukPetani']);
    Route::post('v2/simpanHasilTimbang', [App\Http\Controllers\API\GapoktanController::class, 'SimpanHasilTimbangan']);
    Route::get('v2/getDataForProdukSiapJual', [App\Http\Controllers\API\GapoktanController::class, 'getDataForProdukSiapJual']);
    Route::get('v2/onChangeSelect/{data}', [App\Http\Controllers\API\GapoktanController::class, 'onChangeSelect']);
    Route::get('v2/onChangeTanggal/{petani}/{tanggal}', [App\Http\Controllers\API\GapoktanController::class, 'onChangeTanggal']);
    Route::get('v2/onChangeKualitas/{petani}/{tanggal}/{kualitas}', [App\Http\Controllers\API\GapoktanController::class, 'onChangeKualitas']);
    Route::post('v2/postProdukSiapJual', [App\Http\Controllers\API\GapoktanController::class, 'postProdukSiapJual']);
    Route::get('v2/HapusProdukSiapJual/{produkId}', [App\Http\Controllers\API\GapoktanController::class, 'HapusProdukSiapJual']);
    Route::get('v2/gapoktan-get-produk-siap-jual', [App\Http\Controllers\API\GapoktanController::class, 'listProdukSiapJual']);
    Route::post('v2/update-qr-produk-siap-jual/{produkId}', [App\Http\Controllers\API\GapoktanController::class, 'update_qr']);
    Route::post('v2/update-qr-transaksi/{transaksiId}', [App\Http\Controllers\API\GapoktanController::class, 'update_qr_transaksi']);
    Route::post('v2/add-transaction-hash', [App\Http\Controllers\API\GapoktanController::class, 'blockchainHashProduk']);
    Route::post('v2/addMinimalPembelian', [App\Http\Controllers\API\GapoktanController::class, 'addMinimalPembelian']);
    Route::get('v2/error-transaction-produk-blockchain/{produkId}', [App\Http\Controllers\API\GapoktanController::class, 'transactionProdukError']);
    Route::get('v2/error-transaction-transaksi-blockchain/{transaksiId}', [App\Http\Controllers\API\GapoktanController::class, 'transactionTransaksiError']);
    Route::get('v2/error-transaction-request-blockchain/{transaksiId}', [App\Http\Controllers\API\GapoktanController::class, 'transactionRequestError']);
    Route::get('v2/transaction-blockchain-log', [App\Http\Controllers\API\GapoktanController::class, 'getBlockchainLog']);
    Route::get('v2/ChangeStatusMHargaPengemasan/{id}', [App\Http\Controllers\API\GapoktanController::class, 'ChangeStatusMHargaPengemasan']);
    Route::get('v2/DeleteMHargaPengemasan/{id}', [App\Http\Controllers\API\GapoktanController::class, 'DeleteMHargaPengemasan']);
    Route::get('v2/ListTransaksi', [App\Http\Controllers\API\GapoktanController::class, 'ListTransaksi']);
    Route::get('v2/ListTransaksiHistory', [App\Http\Controllers\API\GapoktanController::class, 'ListTransaksiHistory']);
    Route::get('v2/UpdateKirimProduk/{transaksiId}/{estimasi}', [App\Http\Controllers\API\GapoktanController::class, 'UpdateKirimProduk']);
    Route::get('v2/getMasterHargaPengemasan', [App\Http\Controllers\API\GapoktanController::class, 'getMasterHargaPengemasan']);
    Route::post('v2/AddMHargaPengemasan', [App\Http\Controllers\API\GapoktanController::class, 'AddMHargaPengemasan']);
    Route::get('v2/getMasterHargaCabaiPetani', [App\Http\Controllers\API\GapoktanController::class, 'getMasterHargaCabaiPetani']);
    Route::post('v2/AddMHargaCabaiPetani', [App\Http\Controllers\API\GapoktanController::class, 'AddMHargaCabaiPetani']);
    Route::get('v2/ChangeStatusMHargaCabaiPetani/{id}', [App\Http\Controllers\API\GapoktanController::class, 'ChangeStatusMHargaCabaiPetani']);
    Route::get('v2/DeleteMHargaCabaiPetani/{id}', [App\Http\Controllers\API\GapoktanController::class, 'DeleteMHargaCabaiPetani']);
    Route::get('v2/ListTransaksiPermintaan', [App\Http\Controllers\API\GapoktanController::class, 'ListTransaksiPermintaan']);
    Route::get('v2/updateTransaksiRequest/{requestId}/{estimasi}', [App\Http\Controllers\API\GapoktanController::class, 'updateTransaksiRequest']);
    Route::post('v2/update-qr-transaksi/request/{transaksiId}', [App\Http\Controllers\API\GapoktanController::class, 'update_qr_transaksi_request']);
    Route::get('v2/list-request-data', [App\Http\Controllers\API\GapoktanController::class, 'getAdminLRD']);
    Route::post('v2/ApproveRequestData', [App\Http\Controllers\API\GapoktanController::class, 'ApproveRequestData']);
    Route::get('v2/getMasterExpired', [App\Http\Controllers\API\GapoktanController::class, 'getMasterExpired']);
    Route::post('v2/addMExpired', [App\Http\Controllers\API\GapoktanController::class, 'addMExpired']);
    Route::get('v2/ChangeStatusMExpired/{expiredId}', [App\Http\Controllers\API\GapoktanController::class, 'ChangeStatusMExpired']);
    Route::get('v2/DeleteMExpired/{expiredId}', [App\Http\Controllers\API\GapoktanController::class, 'DeleteMExpired']);
    Route::post('v2/gudangProcess', [App\Http\Controllers\API\GapoktanController::class, 'gudangProcess']);
    Route::get('v2/getListExpired', [App\Http\Controllers\API\GapoktanController::class, 'getListExpired']);
    Route::post('v2/update-qr-expired/{expiredId}', [App\Http\Controllers\API\GapoktanController::class, 'update_qr_expired']);
    Route::get('v2/hapus-data-expired/{expiredId}', [App\Http\Controllers\API\GapoktanController::class, 'HapusDataExpired']);
    Route::get('v2/ErrorTransactionExpired/{expiredId}', [App\Http\Controllers\API\GapoktanController::class, 'ErrorTransactionExpired']);
    Route::get('v2/getDataMinimal', [App\Http\Controllers\API\GapoktanController::class, 'getDataMinimal']);
    
    Route::post('v2/postKeranjang', [App\Http\Controllers\API\KonsumenController::class, 'postKeranjang']);
    Route::get('v2/getKeranjang', [App\Http\Controllers\API\KonsumenController::class, 'getKeranjang']);
    Route::post('v2/checkOutProduk', [App\Http\Controllers\API\KonsumenController::class, 'checkOutProduk']);
    Route::get('v2/getTransaksi', [App\Http\Controllers\API\KonsumenController::class, 'getTransaksi']);
    Route::get('v2/getTransaksiDetail/{transaksiId}', [App\Http\Controllers\API\KonsumenController::class, 'getTransaksiDetail']);
    Route::get('v2/updateTransaksiBayar/{transaksiId}', [App\Http\Controllers\API\KonsumenController::class, 'updateTransaksiBayar']);
    Route::get('v2/ListTransaksiHistoryKonsumen', [App\Http\Controllers\API\KonsumenController::class, 'ListTransaksiHistoryKonsumen']);
    Route::get('v2/KonfirmasiBarang/{transaksiId}', [App\Http\Controllers\API\KonsumenController::class, 'KonfirmasiBarang']);
    Route::get('v2/ListRequest', [App\Http\Controllers\API\KonsumenController::class, 'ListRequest']);
    Route::post('v2/SimpanRequestCabai', [App\Http\Controllers\API\KonsumenController::class, 'SimpanRequestCabai']);
    Route::get('v2/deleteRequestcabai/{requestId}', [App\Http\Controllers\API\KonsumenController::class, 'deleteRequestcabai']);
    Route::get('v2/KirimRequestProduk/{requestId}', [App\Http\Controllers\API\KonsumenController::class, 'KirimRequestProduk']);
    Route::get('v2/konsumenBayarPemesanan/{noTx}', [App\Http\Controllers\API\KonsumenController::class, 'konsumenBayarPemesanan']);
    Route::get('v2/getTransaksiDetailRequest/{requestId}', [App\Http\Controllers\API\KonsumenController::class, 'getTransaksiDetailRequest']);
    Route::get('v2/konsumenTerimaProdukRequest/{requestId}', [App\Http\Controllers\API\KonsumenController::class, 'konsumenTerimaProdukRequest']);
    Route::get('v2/getDashboardPetani', [App\Http\Controllers\API\KonsumenController::class, 'getDashboardPetani']);
    
});