<?php

namespace App\Http\Controllers\API;

use App\Models\GapoktanGudangExpiredDetail;
use App\Models\GapoktanGudangExpired;
use App\Http\Controllers\Controller;
use App\Models\GapoktanGudangDetail;
use App\Models\ProdukSiapJualDetail;
use App\Models\ProdukSiapJualImage;
use App\Models\MHargaCabaiPetani;
use App\Models\MHargaPengemasan;
use App\Models\MExpired;
use App\Models\ProdukPetaniImg;
use App\Models\ProdukSiapJual;
use App\Models\GapoktanGudang;
use App\Models\LogRequestData;
use App\Models\TransaksiCabai;
use App\Models\RequestProduk;
use App\Models\ProdukPetani;
use App\Models\TransaksiLog;
use Illuminate\Http\Request;
use App\Models\RequestData;
use App\Models\SaldoDetail;
use App\Models\Notifikasi;
use App\Models\Keranjang;
use App\Models\Profile;
use App\Models\Saldo;
use App\Models\User;
use Carbon\Carbon;
use Storage;
use Auth;
use File;
use DB;

class GapoktanController extends Controller
{
    public $successStatus = 401;

    // authorization
    function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();
            return $next($request);
        });
    }
    // end authorization

    public function index() {

        $user = Auth::user();
        // $petani = User::where('gapoktan', $user->id)->get();
        $petani = User::where('gapoktan', $user->id)->where('role', 'petani')->get()->count();
        $transaksi = TransaksiCabai::where('gapoktan_id', $user->id)->where('qrcode', '!=', null)->get();
        $transaksiRequest = RequestProduk::where('gapoktan_id', $user->id)->where('qrcode', '!=', null)->get();
        $TotalTransaksi = TransaksiCabai::where('gapoktan_id', $user->id)->where('qrcode', '!=', null)->get()->count();
        $TotalTtransaksiRequest = RequestProduk::where('gapoktan_id', $user->id)->where('qrcode', '!=', null)->get()->count();
        $saldoTotal = Saldo::where('gapoktan_id', $user->gapoktan)->where('user_id', $user->id)->orderBy('created_at', 'desc')->first(); // ambil data terbaru
        $saldoKeluar = Saldo::where('gapoktan_id', $user->gapoktan)->where('user_id', $user->id)->sum('saldo_out');

        // cek data master
        $hargaCabai = MHargaCabaiPetani::where('gapoktan_id', $user->gapoktan)->first();
        $hargaKemas = MHargaPengemasan::where('gapoktan_id', $user->gapoktan)->first();
        // end cek data master

        if($hargaCabai) {
            $success['masterHargaCabai']              = true;
        } else {
            $success['masterHargaCabai']              = false;
        }
        
        if($hargaKemas) {
            $success['masterHargaKemas']              = true;
        } else {
            $success['masterHargaKemas']              = false;
        }

        // data chart
        $gudang = GapoktanGudang::where('user_gapoktan', $user->id)->distinct()->get()->pluck('kualitas');

        $chart = [];
        foreach($gudang as $g) {
            $cek = GapoktanGudang::where('kualitas', $g)->where('status', 1)->sum('volume');

            if(in_array(['kualitas' => $g, 'jumlah' => intval($cek)], $chart)) {
                // jika sudah ada di array tidak perlu di masukan
            } else {
                array_push($chart, ['kualitas' => $g, 'jumlah' => intval($cek)]);
            }
        }

        $this->successStatus            = 200;
        $success['success']             = true;
        $success['DSaldo']              = $saldoTotal;
        $success['DSaldoOut']           = $saldoKeluar;
        $success['DTransaksi']          = $transaksi;
        $success['DTransaksiRequest']   = $transaksiRequest;
        $success['DTotalTransaksi']     = $TotalTransaksi + $TotalTtransaksiRequest;
        $success['DPetani']             = $petani;
        $success['DChart']              = $chart;

        return response()->json($success, $this->successStatus);

    }

    public function listProdukPetani() {
        $user = Auth::user();

        $produk = ProdukPetani::where('gapoktan_id', $user->gapoktan)->orderBy('created_at', 'desc')->with('getCreator', 'getImage')->get();

        $this->successStatus       = 200;
        $success['success']        = true;
        $success['produkPetani']   = $produk;

        return response()->json($success, $this->successStatus);
    }

    public function dataGudang() {
        $user = Auth::user();

        // get data gudang gapoktan
        $gudang = GapoktanGudang::where('user_gapoktan', $user->id)->where('status', 1)->orderBy('updated_at', 'desc')->with('getProduk', 'getGapoktan', 'getPetani')->get();
        $expired = MExpired::where('status', 'Aktif')->where('gapoktan_id', $user->gapoktan)->first();

        $this->successStatus       = 200;
        $success['success']        = true;
        $success['gudang']         = $gudang;
        $success['expired']        = $expired;

        return response()->json($success, $this->successStatus);
    }

    public function konfirmasiProdukPetani($produkId) {

        $konfirmasi = ProdukPetani::find($produkId);
        $user = Auth::user();

        if($konfirmasi) {

            $konfirmasi->status = 'Produk dikonfirmasi gapoktan';
            $konfirmasi->save();
            
            // insert gapoktan gudang
            $gudang = new GapoktanGudang();
            $gudang->user_gapoktan = $user->id;
            $gudang->petani = $konfirmasi->user_id;
            $gudang->produk_petani = $konfirmasi->id;
            $gudang->volume = $konfirmasi->volume;
            $gudang->kualitas = $konfirmasi->kualitas;
            $gudang->status = 1;
            $gudang->save();

            // insert detail gapoktan gudang
            $gudangDetail = new GapoktanGudangDetail();
            $gudangDetail->gapoktan_gudang_id = $gudang->id;
            $gudangDetail->petani_id = $gudang->petani;
            $gudangDetail->volume_in = $gudang->volume;
            $gudangDetail->status = 'Panen petani';
            $gudangDetail->save();


            $this->successStatus       = 200;
            $success['success']        = true;
            $success['gudang']         = $gudang;
            $success['konfirmasi']     = $konfirmasi;

            return response()->json($success, $this->successStatus);
        } else {
            $this->successStatus       = 404;
            $success['success']        = false;
            $success['message']        = 'Data tidak ditemukan';

            return response()->json($success, $this->successStatus);
        }
    }

    public function SimpanHasilTimbangan(Request $request) {

        $produk = ProdukPetani::find($request->id);

        if($produk) {
            $produk->volume = $request->volume;
            $produk->save();

            $this->successStatus       = 200;
            $success['success']        = true;
            $success['data']           = $produk;

            return response()->json($success, $this->successStatus);
        } else {
            $this->successStatus       = 404;
            $success['success']        = false;
            $success['message']        = 'Data tidak ditemukan';

            return response()->json($success, $this->successStatus);
        }

    }

    public function getDataForProdukSiapJual() {

        $user = Auth::user();

        $gapoktan = $user->id;

        $harga = MHargaPengemasan::where('gapoktan_id', $gapoktan)->where('status', 1)->first();
        $dataGudang = GapoktanGudang::where('status', 1)->orderBy('created_at', 'desc')->with('getProduk', 'getGapoktan', 'getPetani')->get();
        
        // get data petani
        $petaniGudang = [];

        $petani = User::where('gapoktan', $user->id)->where('role', 'petani')->where('status', 1)->get();
        $getPetaniGudang = DB::table('gapoktan_gudang')->select('petani')->distinct()->get();

        foreach($getPetaniGudang as $gpg) {
            $petani = User::where('gapoktan', $user->id)->where('role', 'petani')->where('status', 1)->where('id', $gpg->petani)->first();

            if($petani) {
                array_push($petaniGudang, $petani);
            }
        }
        // end get data petani

        $this->successStatus        = 200;
        $success['success']         = true;
        $success['dataGudang']      = $dataGudang;
        $success['petaniGudang']    = $petaniGudang;
        $success['hargaPengemasan'] = $harga;

        return response()->json($success, $this->successStatus);

    }

    public function onChangeSelect($data) {

        $tanggalPanen = [];

        $dataGudang = GapoktanGudang::where('status', 1)->where('petani', $data)->get();

        foreach($dataGudang as $dg) {
            $produk = ProdukPetani::find($dg->produk_petani);

            if(in_array($produk->tanggal_panen, $tanggalPanen)) {
                // jika sudah ada di array tidak perlu di masukan
            } else {
                array_push($tanggalPanen, $produk->tanggal_panen);
            }
        }
        
        $this->successStatus       = 200;
        $success['success']        = true;
        $success['tanggalPanen']   = $tanggalPanen;
        
        return response()->json($success, $this->successStatus);

    }

    public function onChangeTanggal($petani, $tanggal) {
        
        $data = ProdukPetani::where('user_id', $petani)->where('tanggal_panen', $tanggal)->where('volume', '!=', null)->get()->pluck('kualitas');

        $this->successStatus       = 200;
        $success['success']        = true;
        $success['kualitas']       = $data;
        
        return response()->json($success, $this->successStatus);

    }

    public function onChangeKualitas($petani, $tanggal, $kualitas) {

        $user = Auth::user();

        $produk = ProdukPetani::where('user_id', $petani)->where('tanggal_panen', $tanggal)->where('kualitas', str_replace("_"," ",$kualitas))->where('volume', '!=', null)->where('status', 'Produk dikonfirmasi gapoktan')->first();
        if($produk) {
            $gudang = GapoktanGudang::where('user_gapoktan', $user->id)->where('petani', $petani)->where('produk_petani', $produk->id)->where('status', 1)->with('getProduk')->first();

            $this->successStatus       = 200;
            $success['success']        = true;
            $success['gudang']         = $gudang;
            
            return response()->json($success, $this->successStatus);
        } else {
            $this->successStatus       = 404;
            $success['success']        = false;
            $success['message']        = "Tidak ditemukan";
            
            return response()->json($success, $this->successStatus);
        }

    }

    public function postProdukSiapJual(Request $request) {

        $user = Auth::user();

        $simpan = new ProdukSiapJual();
        $simpan->gapoktan_id = $user->gapoktan;
        $simpan->nama_produk = $request->nama_produk;
        $simpan->deskripsi_produk = $request->deskripsi;
        $simpan->tanggal_pengemasan = $request->tanggal_pengemasan;
        $simpan->jenis_produk = $request->jenis_produk;
        $simpan->harga_jual = $request->harga_jual;
        $simpan->biaya_packaging = $request->biaya_pengemasan;
        // $simpan->kualitas_kemasan = $request->kualitas;
        $simpan->volume = $request->volume;
        $simpan->status_tayang = 0;
        $simpan->save();

        if($request->jenis_produk == 'Single') {

            $produk = ProdukPetani::where('user_id', $request->petani)->where('tanggal_panen', $request->tanggal_panen)->where('kualitas', $request->kualitas_produk_panen)->where('volume', '!=', null)->where('status', 'Produk dikonfirmasi gapoktan')->first();
            $gudang = GapoktanGudang::where('user_gapoktan', $user->id)->where('petani', $request->petani)->where('produk_petani', $produk->id)->where('status', 1)->first();

            $detail = new ProdukSiapJualDetail();
            $detail->produk_siap_jual = $simpan->id;
            $detail->gudang_id = $gudang->id;
            $detail->petani = $request->petani;
            $detail->tanggal_panen = $request->tanggal_panen;
            $detail->kualitas_cabai = $request->kualitas_produk_panen;
            $detail->volume = $request->volume;
            $detail->produk_petani = $produk->id;
            $detail->save();

            if ($request->hasFile('files')) {
                $files = $request->file('files');
    
                foreach($files as $file) {
    
                    $name = $file->getClientOriginalName();
                    $size = $file->getSize();
    
                    $file->move(public_path("images/produk/siapJual/") . $simpan->id . '/', $name);

                    $image = new ProdukSiapJualImage();
                    $image->produk_siap_jual = $simpan->id;
                    $image->file = "images/produk/siapJual/" . $simpan->id . '/' . $name;
                    $image->size  = $size;
                    $image->save();    
                }
            }

            $gudang->volume = $gudang->volume - $simpan->volume;
            if($gudang->volume == 0) {
                // $gudang->delete();
                $gudang->status = 0;
                $gudang->save();
                
                // insert gudang detail
                $gudangDetail = new GapoktanGudangDetail();
                $gudangDetail->gapoktan_gudang_id = $gudang->id;
                $gudangDetail->petani_id = $request->petani;
                $gudangDetail->volume_out = $simpan->volume;
                $gudangDetail->status = 'Pembuatan Produk Market';
                $gudangDetail->save();

            } else {
                $gudang->save();

                // insert gudang detail
                $gudangDetail = new GapoktanGudangDetail();
                $gudangDetail->gapoktan_gudang_id = $gudang->id;
                $gudangDetail->petani_id = $request->petani;
                $gudangDetail->volume_out = $simpan->volume;
                $gudangDetail->status = 'Pembuatan Produk Market';
                $gudangDetail->save();
            }

            $this->successStatus   = 200;
            $success['success']    = true;
            $success['data']       = $simpan;
            
            return response()->json($success, $this->successStatus);

        } else {
            // belum bisa get data untuk jenis produk yang mix
        }
    }

    public function HapusProdukSiapJual($id) {

        $user = Auth::user();

        $data = ProdukSiapJual::where('id', $id)->where('gapoktan_id', $user->gapoktan)->first();
        $detail = ProdukSiapJualDetail::where('produk_siap_jual', $id)->first();
        $img = ProdukSiapJualImage::where('produk_siap_jual', $id)->get();

        if($data) {

            // update gudang
            $gudang = GapoktanGudang::where('user_gapoktan', $user->gapoktan)->where('produk_petani', $detail->produk_petani)->where('status', 1)->first();
            $gudang->volume = $gudang->volume + $data->volume;
            $gudang->save();

            // insert gudang detail
            $gudangDetail = new GapoktanGudangDetail();
            $gudangDetail->gapoktan_gudang_id = $gudang->id;
            $gudangDetail->petani_id = $gudang->petani;
            $gudangDetail->volume_in = $data->volume;
            $gudangDetail->status = 'Hapus Produk Market';
            $gudangDetail->save();

            // hapus detail
            $detail->delete();
            // hapus image
            ProdukSiapJualImage::where('produk_siap_jual', $id)->delete();
            // hapus data
            $data->delete();

            $this->successStatus       = 200;
            $success['success']        = true;
            
            return response()->json($success, $this->successStatus);

        } else {

            $this->successStatus       = 200;
            $success['success']        = true;
            $success['message']        = "Data tidak ditemukan";
            
            return response()->json($success, $this->successStatus);

        }

    }

    public function listProdukSiapJual() {

        $user = Auth::user();

        $data = ProdukSiapJual::orderBy('created_at', 'desc')->where('gapoktan_id', $user->gapoktan)->with('getDetail', 'getImg')->get();

        $this->successStatus   = 200;
        $success['success']    = true;
        $success['data']       = $data;
        
        return response()->json($success, $this->successStatus);
    }

    public function update_qr(Request $request, $produkId) {

        if (ProdukSiapJual::where('id', $produkId)->exists()) {
            $product = ProdukSiapJual::where('id', $produkId)->with('getDetail')->first();
            // $detail = ProdukSiapJualDetail::where('produk_siap_jual', $produkId)->with('getPetani', 'produkPetani')->first();

            if ($request->hasFile('files_qr')) {
                $files          = $request->file('files_qr');
                $files->move(public_path("images/product/") . $product->id . '/QR', $request->fileName_qr);
                $product->qrcode  = "images/product/" . $product->id . '/QR/' . $request->fileName_qr;
                $product->status_tayang  = 1;
                $product->save();
            }

            $this->successStatus   = 200;
            $success['success']    = true;
            $success['data']       = $product;
            // $success['detail']     = $detail;
            
            return response()->json($success, $this->successStatus);
        } else {
            return response()->json([
                "message" => "Product not found"
            ], 404);
        }
    }

    public function blockchainHashProduk(Request $request) {

        $user = Auth::user();

        $transaksi = new TransaksiLog();
        $transaksi->gapoktan_id = $user->gapoktan;
        $transaksi->smartcontract = $request->contract;
        $transaksi->hash = $request->t_hash;
        $transaksi->name = $request->name;
        $transaksi->produk_id = $request->product_id;
        $transaksi->save();

        $this->successStatus = 200;
        $success['success']  = true;
        $success['data']     = $transaksi;

        return response()->json($success, $this->successStatus);
    }

    public function transactionProdukError($produkId) {

        $data = ProdukSiapJual::find($produkId);
        $data->qrcode = null;
        $data->status_tayang = 0;
        $data->save();

        $this->successStatus    = 200;
        $success['success']     = true;
        $success['data']        = $data;

        return response()->json($success, $this->successStatus);

    }

    public function getBlockchainLog() {

        $user = Auth::user();

        // $produk = TransaksiLog::where('smartcontract', 'produk')->orderBy('created_at', 'desc')->get();
        // $transaksi = TransaksiLog::where('smartcontract', 'transaksi')->orderBy('created_at', 'desc')->get();
        $produk = TransaksiLog::where('smartcontract', 'produk')->where('gapoktan_id', $user->gapoktan)->orderBy('created_at', 'desc')->get();
        $transaksi = TransaksiLog::where('smartcontract', 'transaksi')->where('gapoktan_id', $user->gapoktan)->orderBy('created_at', 'desc')->get();
        $request = TransaksiLog::where('smartcontract', 'request')->where('gapoktan_id', $user->gapoktan)->orderBy('created_at', 'desc')->get();
        $expired = TransaksiLog::where('smartcontract', 'expired')->where('gapoktan_id', $user->gapoktan)->orderBy('created_at', 'desc')->get();

        $this->successStatus    = 200;
        $success['success']     = true;
        $success['produk']      = $produk;
        $success['transaksi']   = $transaksi;
        $success['request']     = $request;
        $success['expired']     = $expired;

        return response()->json($success, $this->successStatus);

    }

    public function getMasterHargaPengemasan() {

        $user = Auth::user();

        $data = MHargaPengemasan::orderBy('updated_at', 'desc')->where('gapoktan_id', $user->gapoktan)->get();

        $this->successStatus    = 200;
        $success['success']     = true;
        $success['data']        = $data;

        return response()->json($success, $this->successStatus);

    }

    public function AddMHargaPengemasan(Request $request) {

        $user = Auth::user();
        // $cek = MHargaPengemasan::where('harga', $request->harga.'.00')->first();
        $cek = MHargaPengemasan::where('gapoktan_id', $user->gapoktan)->where('harga', $request->harga.'.00')->first();

        if($cek){
        
            $this->successStatus    = 200;
            $success['success']     = false;
            $success['message']     = "Data harga sudah ada";

            return response()->json($success, $this->successStatus);

        } else {

            $data = new MHargaPengemasan();
            $data->gapoktan_id = $user->id;
            $data->harga = $request->harga;
            $data->status = 1;
            $data->save();

            $this->successStatus    = 200;
            $success['success']     = true;
            $success['data']        = $data;

            return response()->json($success, $this->successStatus);
        }

    }

    public function ChangeStatusMHargaPengemasan($id) {

        $cek = MHargaPengemasan::find($id);

        if($cek) {

            $user = Auth::user();

            if($cek->status == 1) {
                $cek->status = 0;
                $cek->save();
            } else {
                $cek->status = 1;
                $cek->save();
            }

            $this->successStatus    = 200;
            $success['success']     = false;
            $success['data']        = $cek;

            return response()->json($success, $this->successStatus);

        } else {
            $this->successStatus    = 200;
            $success['success']     = false;
            $success['message']     = "Data tidak ditemukan";

            return response()->json($success, $this->successStatus);
        }
    }

    public function DeleteMHargaPengemasan($id) {
        $cek = MHargaPengemasan::find($id);

        if($cek) {

            $cek->delete();

            $this->successStatus    = 200;
            $success['success']     = false;

            return response()->json($success, $this->successStatus);

        } else {
            $this->successStatus    = 200;
            $success['success']     = false;
            $success['message']     = "Data tidak ditemukan";

            return response()->json($success, $this->successStatus);
        }
    }

    public function ListTransaksi() {

        $login = Auth::user();
        // $list = TransaksiCabai::where('status_transaksi', 'Menunggu Konfirmasi Pembayaran')->where('progress_transaksi', 50)->with('getPembeli', 'getKeranjang', 'getProduk')->get();
        $list = TransaksiCabai::where('status_transaksi', 'Menunggu Konfirmasi Pembayaran')->where('progress_transaksi', 50)->where('gapoktan_id', $login->gapoktan)->with('getPembeli', 'getKeranjang', 'getProduk')->get();
        $user = Profile::where('user_id', $login->id)->with('getUser')->first();

        $this->successStatus    = 200;
        $success['success']     = false;
        $success['data']        = $list;
        $success['gapoktan']    = $user;

        return response()->json($success, $this->successStatus);

    }

    public function update_qr_transaksi(Request $request, $transaksiId) {

        if (TransaksiCabai::where('id', $transaksiId)->exists()) {

            $date = Carbon::now();
            $transaksi = TransaksiCabai::where('id', $transaksiId)->with('getPembeli', 'getKeranjang', 'getProduk')->first();
            $keranjang = Keranjang::where('id', $transaksi->keranjang_id)->first();
            $produk = ProdukSiapJualDetail::where('produk_siap_jual', $transaksi->produk_id)->first();
            $login = Auth::user();
            // $user = Profile::where('user_id', $login->id)->with('getUser')->first();
            $user = Profile::where('user_id', $login->gapoktan)->with('getUser')->first();
            $petani = User::where('id', $produk->petani)->with('getProfile', 'getLahan')->first();
            $produkHash = TransaksiLog::where('produk_id', $produk->id)->first();
            $pembeli = Profile::where('user_id', $transaksi->user_id)->with('getUser')->first();
            $sekarang = $date->format('Y-m-d');

            $snd = CekSupplyDemand($sekarang, $transaksi->gapoktan_id, $produk->kualitas_cabai);

            if($snd == true) { // jika harga naik 20%
                $transaksi->jumlah_pembayaran = intval($transaksi->harga) * 20 / 100 + intval($transaksi->harga);
            } else {
                $transaksi->jumlah_pembayaran = intval($transaksi->harga);
            }

            if ($request->hasFile('files_qr')) {

                $files          = $request->file('files_qr');
                $files->move(public_path("images/transaksi/") . $keranjang->no_transaksi . '/QR', $request->fileName_qr);

                $transaksi->qrcode  = "images/transaksi/" . $keranjang->no_transaksi . '/QR/' . $request->fileName_qr;
                $transaksi->status_pembayaran = 'Diterima Gapoktan';
                $transaksi->progress_transaksi = 70;
                $transaksi->status_transaksi = "Pembayaran Dikonfirmasi";
                $transaksi->save();
            
            }
            $transaksi->save();

            // bagi hasil pembelian
            // $hargaPetani = MHargaCabaiPetani::where('kualitas', $produk->kualitas_cabai)->where('status', 1)->first();
            $hargaPetani = MHargaCabaiPetani::where('gapoktan_id', $login->gapoktan)->where('kualitas', $produk->kualitas_cabai)->where('status', 1)->first();

                // bagi ke petani

                    $saldo1 = new Saldo();
                    $saldo1->user_id = $petani->id;
                    $saldo1->gapoktan_id = $petani->gapoktan;
                    $saldo1->saldo_in = intval($hargaPetani->harga_jual) * $produk->volume; // petani hanya dapat dari harga jual cabai dikali dengan volume pembelian
                    $cekSaldoSebelumnya = Saldo::where('user_id', $petani->id)->where('gapoktan_id', $petani->gapoktan)->orderBy('created_at', 'desc')->first();
                    
                    if($cekSaldoSebelumnya) { // jika saldo sebelumnya ada
                        
                        $saldo11->total_saldo = intval($cekSaldoSebelumnya->total_saldo) + (intval($hargaPetani->harga_jual) * $produk->volume);
                        
                    } else {
                        
                        $saldo1->total_saldo = intval($hargaPetani->harga_jual) * $produk->volume;
                        
                    }
                    $saldo1->save();

                    $saldoDetail = new SaldoDetail();
                    $saldoDetail->saldo_id = $saldo1->id;
                    $saldoDetail->user_id = $saldo1->user_id;
                    $saldoDetail->gapoktan = $saldo1->gapoktan_id;
                    $saldoDetail->no_transaksi = $keranjang->no_transaksi;
                    $saldoDetail->transaksi_volume_cabai = $produk->volume;
                    $saldoDetail->save();
                // end bagi ke petani

            
                // bagi ke gapoktan
                    $saldo2 = new Saldo();
                    $saldo2->user_id = $transaksi->gapoktan_id;
                    $saldo2->gapoktan_id = $transaksi->gapoktan_id;
                    // $saldo2->saldo_in = intval($transaksi->pembayaran - $saldo1->total_saldo;
                    $saldo2->saldo_in = $transaksi->jumlah_pembayaran - $saldo1->total_saldo;
                    $cekSaldoSebelumnya = Saldo::where('user_id', $keranjang->gapoktan_id)->where('gapoktan_id', $keranjang->gapoktan_id)->orderBy('created_at', 'desc')->first();

                    if($cekSaldoSebelumnya) { // jika saldo sebelumnya ada

                        $saldo2->total_saldo = $cekSaldoSebelumnya + ($transaksi->jumlah_pembayaran - $saldo1->total_saldo);
                        
                    } else {
                        
                        $saldo2->total_saldo = $transaksi->jumlah_pembayaran - $saldo1->total_saldo;

                    }
                    $saldo2->save();

                    $saldoDetail = new SaldoDetail();
                    $saldoDetail->saldo_id = $saldo2->id;
                    $saldoDetail->user_id = $saldo2->gapoktan_id;
                    $saldoDetail->gapoktan = $saldo2->gapoktan_id;
                    $saldoDetail->no_transaksi = $keranjang->no_transaksi;
                    $saldoDetail->transaksi_volume_cabai = $produk->volume;
                    $saldoDetail->save();
                // end bagi ke gapoktan
            // end bagi hasil pembelian
            
            // berikan notifikasi ke petani dan pembeli
            $notifPetani = new Notifikasi();
            $notifPetani->user_id = $produk->petani;
            $notifPetani->notifikasi = 'Pembeli '. $pembeli->nama. ' telah membeli produk cabai gapoktan dari hasil panen anda';
            $notifPetani->status = "Belum Dibaca";
            $notifPetani->save();

            $notifPetani = new Notifikasi();
            $notifPetani->user_id = $produk->petani;
            $notifPetani->notifikasi = 'Anda telah menerima saldo dari Pembelian '. $pembeli->nama. ' dengan nomor transaksi '.$keranjang->no_transaksi.' sebesar Rp. '.intval($hargaPetani->harga_jual) * $produk->volume;
            $notifPetani->status = "Belum Dibaca";
            $notifPetani->save();
            
            $notifPembeli = new Notifikasi();
            $notifPembeli->user_id = $transaksi->user_id;
            $notifPembeli->notifikasi = 'Transaksi anda telah dikonfirmasi oleh gapoktan';
            $notifPembeli->status = "Belum Dibaca";
            $notifPembeli->save();
            // end berikan notifikasi

            $this->successStatus   = 200;
            $success['success']    = true;
            $success['transaksi']  = $transaksi;
            $success['gapoktan']   = $user;
            $success['petani']     = $petani;
            $success['produkHash'] = $produkHash;

            return response()->json($success, $this->successStatus);
        } else {
            return response()->json([
                "message" => "Product not found"
            ], 404);
        }

    }

    public function transactionTransaksiError($transaksiId) {
        
        $data = TransaksiCabai::find($transaksiId);
        $data->qrcode = null;
        $data->status_pembayaran = null;
        $data->progress_transaksi = 50;
        $data->status_transaksi = "Menunggu Konfirmasi Pembayaran";
        $data->save();

        $this->successStatus    = 200;
        $success['success']     = true;
        $success['data']        = $data;

        return response()->json($success, $this->successStatus);

    }

    public function ListTransaksiHistory() {

        $user = Auth::user();

        // $data = TransaksiCabai::where('status_transaksi', 'Pembayaran Dikonfirmasi')->with('getPembeli', 'getKeranjang', 'getProduk')->get();
        // $data = TransaksiCabai::orderBy('updated_at', 'desc')->with('getPembeli', 'getKeranjang', 'getProduk')->get();
        $data = TransaksiCabai::orderBy('updated_at', 'desc')->where('gapoktan_id', $user->gapoktan)->with('getPembeli', 'getKeranjang', 'getProduk')->get();

        $this->successStatus   = 200;
        $success['success']    = true;
        $success['data']       = $data;
        
        return response()->json($success, $this->successStatus);

    }

    public function UpdateKirimProduk($transaksiId) {

        $data = TransaksiCabai::find($transaksiId);
        // $keranjang = Keranjang::where('produk', $data->keranjang_id)->first();
        $keranjang = Keranjang::where('id', $data->keranjang_id)->first();

        if($data && $keranjang) {

            $data->status_transaksi = "Produk Dikirim Oleh Gapoktan";
            $data->progress_transaksi = 80;
            $data->save();

            $notifPembeli = new Notifikasi();
            $notifPembeli->user_id = $data->user_id;
            $notifPembeli->notifikasi = 'Produk pembelian anda dengan transaksi '. $keranjang->no_transaksi . ' telah dikirim oleh gapoktan';
            $notifPembeli->status = "Belum Dibaca";
            $notifPembeli->save();

            $this->successStatus   = 200;
            $success['success']    = true;
            $success['data']       = $data;
            
            return response()->json($success, $this->successStatus);

        } else {

            $this->successStatus   = 200;
            $success['success']    = true;
            $success['message']    = "Data tidak ditemukan";
            
            return response()->json($success, $this->successStatus);

        }

    }

    public function getMasterHargaCabaiPetani() {

        $user = Auth::user();

        $data = MHargaCabaiPetani::where('gapoktan_id', $user->gapoktan)->orderBy('updated_at', 'desc')->get();
        $select = MHargaCabaiPetani::where('gapoktan_id', $user->gapoktan)->get();

        $this->successStatus    = 200;
        $success['success']     = true;
        $success['data']        = $data;
        $success['select']      = $select;

        return response()->json($success, $this->successStatus);

    }

    public function AddMHargaCabaiPetani(Request $request) {

        $user = Auth::user();

        $cek = MHargaCabaiPetani::where('gapoktan_id', $user->id)
            ->where('harga_dasar', $request->harga_dasar.'.00')
            ->where('kualitas', $request->kualitas)
            ->where('profit', $request->profit)
            ->first();

        if($cek){
        
            $this->successStatus    = 200;
            $success['success']     = false;
            $success['message']     = "Data harga sudah ada";

            return response()->json($success, $this->successStatus);

        } else {
            $cekKualitas = MHargaCabaiPetani::where('gapoktan_id', $user->id)->where('kualitas', $request->kualitas)->where('status', 1)->first();

            $data = new MHargaCabaiPetani();
            $data->gapoktan_id = $user->id;
            $data->harga_dasar = $request->harga_dasar;
            $data->kualitas = $request->kualitas;
            $data->profit = $request->profit;
            $data->harga_jual = $request->harga_jual;
            if($cekKualitas) {
                $data->status = 0;
            } else {
                $data->status = 1;
            }
            $data->save();

            $this->successStatus    = 200;
            $success['success']     = true;
            $success['data']        = $data;

            return response()->json($success, $this->successStatus);
        }

        
    }

    public function ChangeStatusMHargaCabaiPetani($id) {
        
        $cek = MHargaCabaiPetani::find($id);

        if($cek) {

            $user = Auth::user();

            if($cek->status == 1) {
                $cek->status = 0;
                $cek->save();
            } else {
                $cek->status = 1;
                $cek->save();
            }

            $this->successStatus    = 200;
            $success['success']     = false;
            $success['data']        = $cek;

            return response()->json($success, $this->successStatus);

        } else {
            $this->successStatus    = 200;
            $success['success']     = false;
            $success['message']     = "Data tidak ditemukan";

            return response()->json($success, $this->successStatus);
        }

    }

    public function DeleteMHargaCabaiPetani($id) {

        $cek = MHargaCabaiPetani::find($id);

        if($cek) {

            $cek->delete();

            $this->successStatus    = 200;
            $success['success']     = false;

            return response()->json($success, $this->successStatus);

        } else {
            $this->successStatus    = 200;
            $success['success']     = false;
            $success['message']     = "Data tidak ditemukan";

            return response()->json($success, $this->successStatus);
        }

    }

    public function ListTransaksiPermintaan() {

        $user = Auth::user();

        $data = RequestProduk::where('gapoktan_id', $user->gapoktan)->where('status', '!=', 'Pengajuan pemesanan cabai')->with('getKonsumen')->get();

        $this->successStatus       = 200;
        $success['success']        = true;
        $success['data']           = $data;

        return response()->json($success, $this->successStatus);

    }

    public function updateTransaksiRequest($requestId) {

        $data = RequestProduk::find($requestId);

        if($data) {

            // $hargaCabaiPetani = MHargaCabaiPetani::where('kualitas', $data->kualitas)->where('status', 1)->first();
            $hargaCabaiPetani = MHargaCabaiPetani::where('gapoktan_id', $data->gapoktan_id)->where('kualitas', $data->kualitas)->where('status', 1)->first();
            $hargaPengemasan = MHargaPengemasan::where('gapoktan_id', $data->gapoktan_id)->where('status', 1)->first();

            if($data->status == 'Menunggu konfirmasi gapoktan') {

                $data->status = 'Dikonfirmasi oleh gapoktan';
                $data->save();

                // kirim notifikasi ke konsumen
                $notifPetani = new Notifikasi();
                $notifPetani->user_id = $data->user_id;
                $notifPetani->notifikasi = 'Pemesanan anda dengan nomor '.$data->no_transaksi.' telah dikonfirmasi oleh gapoktan';
                $notifPetani->status = "Belum Dibaca";
                $notifPetani->save();
                // end notifikasi ke konsumen

                $this->successStatus       = 200;
                $success['success']        = true;

                return response()->json($success, $this->successStatus);

            } else if($data->status == 'Menunggu konfirmasi pembayaran') {

                // update gudang
                $gudang = GapoktanGudang::where('user_gapoktan', $data->gapoktan_id)->where('kualitas', $data->kualitas)->where('status', 1)->orderBy('created_at', 'asc')->get();

                $supply = GapoktanGudang::where('user_gapoktan', $data->gapoktan_id)->where('kualitas', $data->kualitas)->where('status', 1)->sum('volume');

                $demand = $data->volume;

                $v = $data->volume;

                foreach($gudang as $g) {

                    if($v > 0) { // jika volume request masih ada
                        if($v > $g->volume) { // jika volume request lebih besar dari salah satu data di gudang

                            $v = $v - $g->volume;

                            // insert gudang detail
                            $gudangDetail = new GapoktanGudangDetail();
                            $gudangDetail->gapoktan_gudang_id = $g->id;
                            $gudangDetail->no_transaksi = $data->no_transaksi;
                            $gudangDetail->petani_id = $g->petani;
                            $gudangDetail->gapoktan_id = $g->user_gapoktan;
                            $gudangDetail->konsumen_id = $data->user_id;
                            $gudangDetail->volume_out = $g->volume;
                            $gudangDetail->status = 'Pemesanan cabai';
                            $gudangDetail->save();

                            $g->volume = 0;
                            $g->status = 0;
                            $g->save();

                        } else { // jika volume request lebih dikurangi salah satu data di gudang

                            $g->volume = $g->volume - $v;
                            $g->save();

                            // insert gudang detail
                            $gudangDetail = new GapoktanGudangDetail();
                            $gudangDetail->gapoktan_gudang_id = $g->id;
                            $gudangDetail->no_transaksi = $data->no_transaksi;
                            $gudangDetail->petani_id = $g->petani;
                            $gudangDetail->gapoktan_id = $g->user_gapoktan;
                            $gudangDetail->konsumen_id = $data->user_id;
                            $gudangDetail->volume_out = $v;
                            $gudangDetail->status = 'Pemesanan cabai';
                            $gudangDetail->save();

                            $v = 0;

                        }

                    }

                }

                // end update gudang

                // pembagian saldo 
                $detailKeluar = GapoktanGudangDetail::where('no_transaksi', $data->no_transaksi)->where('volume_out', '!=', null)->get();
                $hargaTersisa = 0;
                foreach($detailKeluar as $dk) {

                    $cekSaldo = Saldo::where('user_id', $dk->petani_id)->where('gapoktan_id', $dk->gapoktan_id)->orderBy('created_at', 'desc')->first(); // get data saldo terakhir dari user yang di loop

                    $saldo = new Saldo();
                    $saldo->user_id = $dk->petani_id;
                    $saldo->gapoktan_id = $dk->gapoktan_id;
                    $saldo->saldo_in = $dk->volume_out * intval($hargaCabaiPetani->harga_jual);
                    if($cekSaldo) { // jika saldo tidak kosong
                        $saldo->total_saldo = intval($cekSaldo->total_saldo) + ($dk->volume_out * intval($hargaCabaiPetani->harga_jual));
                    } else {
                        $saldo->total_saldo = $dk->volume_out * intval($hargaCabaiPetani->harga_jual);
                    }
                    $saldo->save(); 

                    $saldoDetail = new SaldoDetail();
                    $saldoDetail->saldo_id = $saldo->id;
                    $saldoDetail->gapoktan = $dk->gapoktan_id;
                    $saldoDetail->user_id = $dk->petani_id;
                    $saldoDetail->no_transaksi = $dk->no_transaksi;
                    $saldoDetail->transaksi_volume_cabai = $dk->volume_out;
                    $saldoDetail->save();

                    $hargaTersisa += intval($saldo->saldo_in);
                }

                // saldo gapoktan
                $saldo = new Saldo();
                $saldo->user_id = $data->gapoktan_id;
                $saldo->gapoktan_id = $data->gapoktan_id;
                $saldo->saldo_in = intval($data->harga) - $hargaTersisa;
                if($cekSaldo) { // jika saldo tidak kosong
                    $saldo->total_saldo = intval($cekSaldo->total_saldo) + (intval($data->harga) - $hargaTersisa);
                } else {
                    $saldo->total_saldo = intval($data->harga) - $hargaTersisa;
                }
                $saldo->save(); 

                $saldoDetail = new SaldoDetail();
                $saldoDetail->saldo_id = $saldo->id;
                $saldoDetail->gapoktan = $data->gapoktan_id;
                $saldoDetail->user_id = $data->gapoktan_id;
                $saldoDetail->no_transaksi = $data->no_transaksi;
                $saldoDetail->transaksi_volume_cabai = $data->volume;
                $saldoDetail->save();
                // end saldo gapoktan


                // end pembagian saldo

                $data->status = 'Pembayaran telah dikonfirmasi';
                if($demand > $supply)  { // jika volume request lebih besar dari data gudang, maka volume final ambil seluruh dari data gudang
                    $data->volume_final = $supply;
                }
                $data->status = 'Pembayaran telah dikonfirmasi';
                $data->save();

                // kirim notifikasi ke konsumen
                $notifPetani = new Notifikasi();
                $notifPetani->user_id = $data->user_id;
                $notifPetani->notifikasi = 'Pembayaran anda untuk transaksi '.$data->no_transaksi.' telah dikonfirmasi oleh gapoktan';
                $notifPetani->status = "Belum Dibaca";
                $notifPetani->save();
                // end notifikasi ke konsumen

                $this->successStatus       = 200;
                $success['success']        = true;

                return response()->json($success, $this->successStatus);

            } else if($data->status == 'Pembayaran telah dikonfirmasi') {

                $data->status = 'Barang dalam masa pengiriman';
                $data->save();

                // kirim notifikasi ke konsumen
                $notifPetani = new Notifikasi();
                $notifPetani->user_id = $data->user_id;
                $notifPetani->notifikasi = 'Transaksi '.$data->no_transaksi.' telah dikonfirmasi dan sedang dalam proses pengiriman';
                $notifPetani->status = "Belum Dibaca";
                $notifPetani->save();
                // end notifikasi ke konsumen

                $this->successStatus       = 200;
                $success['success']        = true;

                return response()->json($success, $this->successStatus);

            }

        } else {

            $this->successStatus       = 200;
            $success['success']        = true;
            $success['message']        = "Data tidak ditemukan";

            return response()->json($success, $this->successStatus);

        }

    }

    public function update_qr_transaksi_request(Request $request, $transaksiId) {

        if (RequestProduk::where('id', $transaksiId)->exists()) {

            $transaksi = RequestProduk::where('id', $transaksiId)->first();
            $user = Profile::where('user_id', $transaksi->gapoktan_id)->with('getUser')->first();
            $pembeli = Profile::where('user_id', $transaksi->user_id)->with('getUser')->first();

            if ($request->hasFile('files_qr')) {

                $files          = $request->file('files_qr');
                $files->move(public_path("images/transaksi/") . $transaksi->no_transaksi . '/QR', $request->fileName_qr);

                $transaksi->qrcode  = "images/transaksi/" . $transaksi->no_transaksi . '/QR/' . $request->fileName_qr;
                $transaksi->save();

            }

            // berikan notifikasi ke pembeli
            $notifPembeli = new Notifikasi();
            $notifPembeli->user_id = $transaksi->user_id;
            $notifPembeli->notifikasi = 'Pembayaran anda telah dikonfirmasi oleh gapoktan';
            $notifPembeli->status = "Belum Dibaca";
            $notifPembeli->save();
            // end berikan notifikasi

            $this->successStatus   = 200;
            $success['success']    = true;
            $success['transaksi']  = $transaksi;
            $success['gapoktan']   = $user;
            $success['pembeli']    = $pembeli;
            
            return response()->json($success, $this->successStatus);
        } else {
            return response()->json([
                "message" => "Product not found"
            ], 404);
        }

    }

    public function transactionRequestError($transaksiId) {

        $data = RequestProduk::find($transaksiId);
        $data->qrcode = null;
        $data->status = "Menunggu Konfirmasi Pembayaran";
        $data->save();

        $this->successStatus    = 200;
        $success['success']     = true;
        $success['data']        = $data;

        return response()->json($success, $this->successStatus);

    }

    public function getAdminLRD() {

        $user = Auth::user();

        $data = RequestData::where('gapoktan', $user->id)->with('getGapoktan')->orderBy('updated_at', 'desc')->get();

        $this->successStatus    = 200;
        $success['success']     = true;
        $success['data']        = $data;

        return response()->json($success, $this->successStatus);

    }

    public function ApproveRequestData(Request $request) {

        $data = RequestData::find($request->requestDataId);
        $data->status = $request->status;
        $data->save();

        // insert log
        $log = new LogRequestData();
        $log->id_request_data = $data->id;
        $log->nama = $data->nama;
        $log->wallet = $data->wallet;
        $log->email = $data->email;
        $log->data = $data->data;
        $log->status = $request->status;
        $log->save();
        // end insert log

        $this->successStatus = 200;
        $success['data'] = $data;
        return response()->json($success, $this->successStatus);

    }

    public function getMasterExpired() {

        $user = Auth::user();

        $data = MExpired::where('gapoktan_id', $user->gapoktan)->get();

        if($data) {

            $this->successStatus    = 200;
            $success['data']        = $data;

            return response()->json($success, $this->successStatus);

        } else {

            $this->successStatus    = 200;
            $success['message']     = 'Data tidak ada';

            return response()->json($success, $this->successStatus);

        }


    }

    public function addMExpired(Request $request) {

        $user = Auth::user();

        $cek = MExpired::where('gapoktan_id', $user->gapoktan)-> where('expired', $request->expired)->first();

        if($cek) {

            $this->successStatus    = 200;
            $success['message']     = 'Data dengan lama expired yang diinput sudah ada.';

            return response()->json($success, $this->successStatus);

        } else {

            $cekStatus = MExpired::where('gapoktan_id', $user->gapoktan)->where('status', 'Aktif')->first();

            $data = new MExpired();
            $data->gapoktan_id = $user->gapoktan;
            $data->expired = $request->expired;
            if($cekStatus) {
                $data->status = 'Tidak Aktif';
            } else {
                $data->status = 'Aktif';
            }
            $data->save();

            $this->successStatus    = 200;
            $success['data']        = $data;

            return response()->json($success, $this->successStatus);

        }

    }

    public function ChangeStatusMExpired($expiredId) {

        $user = Auth::user();

        $data = MExpired::find($expiredId);

        if($data) {

            // ubah status pada data yang ingin diproses
            if($data->status == 'Aktif') {
                $data->status = 'Tidak Aktif';
                $data->save();
            } else { // jika data yang ingin diproses statusnya menjadi aktif

                // get data dengan status aktif selain data yang ingin diproses
                $exp = MExpired::where('gapoktan_id', $user->gapoktan)->where('id', '!=', $expiredId)->where('status', 'Aktif')->get();

                // ubah status data menjadi tidak aktif semua untuk data selain yang ingin diproses
                foreach($exp as $e) {
                    $e->status = 'Tidak Aktif';
                    $e->save();
                }

                $data->status = 'Aktif';
                $data->save();
            }

            $this->successStatus    = 200;
            $success['data']        = $data;

            return response()->json($success, $this->successStatus);

        } else {

            $this->successStatus    = 200;
            $success['message']     = 'Data tidak ditemukan';

            return response()->json($success, $this->successStatus);

        }

    }

    public function DeleteMExpired($expiredId) {

        $delete = MExpired::where('id', $expiredId)->delete();

        $this->successStatus    = 200;
        $success['message']     = 'Data berhasil dihapus';

        return response()->json($success, $this->successStatus);

    }

    public function gudangProcess(Request $request) {

        $user = Auth::user();

        $jml = 0;

        if ($request->has('expired')) {

            $dataId = $request->expired;
        
            foreach($dataId as $exp) {
                
                $getGudangData = GapoktanGudang::find($exp);
                $jml += $getGudangData->volume;

            }

            // insert data expired
            $simpan = new GapoktanGudangExpired();
            $simpan->gapoktan_id = $user->id;
            $simpan->jumlah_volume = $jml;
            $simpan->catatan = $request->catatan;
            $simpan->status = 'Belum Dipost ke Blockchain';
            $simpan->save();

            // insert data expired detail

            foreach($request->expired as $index => $exp) {
                
                $GudangData = GapoktanGudang::find($exp);
                $produkPetani = ProdukPetani::find($GudangData->produk_petani);
                
                $detail = new GapoktanGudangExpiredDetail();
                $detail->gapoktan_id = $user->id;
                $detail->gapoktan_gudang_expired_id = $simpan->id;
                $detail->gudang_id = $exp;
                $detail->petani = $GudangData->petani;
                $detail->tanggal_panen = $produkPetani->tanggal_panen;
                $detail->kualitas = $GudangData->kualitas;
                $detail->volume = $GudangData->volume;
                $detail->expired = $request->tglExp[$index];
                $detail->save();

                $GudangData->status = 0;
                $GudangData->save();

            }

            $this->successStatus    = 200;
            $success['data']        = $simpan;

            return response()->json($success, $this->successStatus);

        } else {

            $this->successStatus    = 200;
            $success['message']     = 'Ada kesalahan pada data yang dipilih.';
            $success['data']        = $request;

            return response()->json($success, $this->successStatus);

        }


    }

    public function getListExpired() {

        $user = Auth::user();

        $getdata = GapoktanGudangExpired::where('gapoktan_id', $user->id)->with('getDetail')->get();

        $this->successStatus    = 200;
        $success['data']        = $getdata;

        return response()->json($success, $this->successStatus);

    }


    public function update_qr_expired(Request $request, $expiredId) {

        if (GapoktanGudangExpired::where('id', $expiredId)->exists()) {
            $expired = GapoktanGudangExpired::where('id', $expiredId)->with('getDetail', 'getGapoktan')->first();
            // $detail = ProdukSiapJualDetail::where('produk_siap_jual', $produkId)->with('getPetani', 'produkPetani')->first();

            if ($request->hasFile('files_qr')) {
                $files          = $request->file('files_qr');
                $files->move(public_path("images/expired/") . $expired->id . '/QR', $request->fileName_qr);
                $expired->qrcode  = "images/expired/" . $expired->id . '/QR/' . $request->fileName_qr;
                $expired->status  = "Berhasil Dipost ke Blockchain";
                $expired->save();
            }

            $this->successStatus   = 200;
            $success['success']    = true;
            $success['data']       = $expired;
            
            return response()->json($success, $this->successStatus);
        } else {
            return response()->json([
                "message" => "Data not found"
            ], 404);
        }

    }


    public function ErrorTransactionExpired($expiredId) {

        $data = GapoktanGudangExpired::find($expiredId);
        $data->qrcode = null;
        $data->status = 'Belum Dipost ke Blockchain';
        $data->save();

        $this->successStatus    = 200;
        $success['success']     = true;
        $success['data']        = $data;

        return response()->json($success, $this->successStatus);

    }

    public function HapusDataExpired($expiredId) {

        $data = GapoktanGudangExpired::find($expiredId);

        $detail = GapoktanGudangExpiredDetail::where('gapoktan_gudang_expired_id', $expiredId)->get();

        foreach($detail as $d) {

            // tayangkan kembali di list gudang
            $retriveGudang = GapoktanGudang::find($d->gudang_id);
            $retriveGudang->status = 1;
            $retriveGudang->save();

            // hapus data expired detail
            $d->delete();

        }

        // hapus data expired
        $data->delete();

        $this->successStatus    = 200;
        $success['success']     = true;
        $success['data']        = $data;

        return response()->json($success, $this->successStatus);

    }


}
