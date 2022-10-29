<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MMinimalPembelian;
use App\Models\MHargaCabaiPetani;
use App\Models\MHargaPengemasan;
use App\Models\ProdukSiapJual;
use App\Models\TransaksiCabai;
use App\Models\RequestProduk;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Notifikasi;
use App\Models\Keranjang;
use App\Models\Profile;
use App\Models\User;
use Carbon\Carbon;
use Storage;
use Auth;
use File;
use DB;

class KonsumenController extends Controller
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

    public function postKeranjang(Request $request) {

        // $date = new DateTime($value);
        $date = Carbon::now();
        $tgl = $date->format('Ymd');
        $time = $date->format('His'); 

        $randomString = Str::random(4);

        // cek supply demand
        $getProduk = ProdukSiapJual::find($request->produk);
        if($getProduk) {

            // cek keranjang
            $cek = Keranjang::where('user', $request->user)->where('produk', $request->produk)->first();

            if($cek) {

                $this->successStatus       = 200;
                $success['success']        = true;
                $success['message']        = "Data sudah ada di keranjang atau telah diproses";

                return response()->json($success, $this->successStatus);
            } else {

                $simpan = new Keranjang();
                $simpan->gapoktan_id = $getProduk->gapoktan_id;
                $simpan->produk = $request->produk;
                $simpan->user = $request->user;
                $simpan->no_transaksi = 'TX-'.$tgl.strtoupper($randomString).$time;
                $simpan->status = 'Keranjang';
                $simpan->harga = $getProduk->harga_jual;
                $simpan->save();

                $this->successStatus       = 200;
                $success['success']        = true;
                $success['produkPetani']   = $simpan;

                return response()->json($success, $this->successStatus);
            }

        } else {

            $this->successStatus       = 200;
            $success['success']        = true;
            $success['message']        = "Data produk tidak ditemukan";

            return response()->json($success, $this->successStatus);

        }

    }

    public function getKeranjang() {

        $user = Auth::user();

        $data = Keranjang::where('user', $user->id)->where('status', 'Keranjang')->with('getProduk', 'getOwner')->get();

        $this->successStatus       = 200;
        $success['success']        = true;
        $success['data']           = $data;

        return response()->json($success, $this->successStatus);

    }

    public function checkOutProduk(Request $request) {

        $keranjang = Keranjang::find($request->keranjang_id);
        $produk = ProdukSiapJual::find($request->produk_id);
        $pembeli = Auth::user();

        $cek = TransaksiCabai::where('user_id', $pembeli->id)->where('gapoktan_id', $keranjang->gapoktan_id)->where('keranjang_id', $request->keranjang_id)->where('produk_id', $request->produk_id)->where('status_transaksi', 'Check Out')->first();

        if($cek) {

            $this->successStatus       = 200;
            $success['success']        = true;
            $success['message']        = "Anda telah melakukan checkout produk ". $produk->nama_produk . " sebelumnya";

            return response()->json($success, $this->successStatus);

        } else {
            $date = Carbon::now();

            // insert transaksi cabai
            $simpan = new TransaksiCabai();
            $simpan->gapoktan_id = $keranjang->gapoktan_id;
            $simpan->user_id = $pembeli->id;
            $simpan->keranjang_id = $request->keranjang_id;
            $simpan->produk_id = $request->produk_id;
            $simpan->status_transaksi = "Check Out";
            $simpan->progress_transaksi = 30;
            $simpan->est_sampai = $date->addDays(7)->format('Y-m-d'); // otomatis estimasi terisi 7 hari dihitung dari tanggal checkout dan bisa berubah ketika gapoktan menentukan pengiriman
            $simpan->save();

            // update keranjang
            $keranjang->status = 'Check Out';
            $keranjang->save();

            // insert notifikasi
            $notif = new Notifikasi();
            $notif->user_id = $keranjang->gapoktan_id;
            $notif->notifikasi = 'Pembeli '. $pembeli->username . ' Melakuakan check out pada produk '. $produk->nama_produk;
            $notif->status = 'Belum Dibaca';
            $notif->save();

            $this->successStatus       = 200;
            $success['success']        = true;

            return response()->json($success, $this->successStatus);

        }

    }

    public function getTransaksi() {

        $user = Auth::user();

        // $transaksi = TransaksiCabai::where('user_id', $user->id)->orderBy('updated_at', 'desc')->with('getKeranjang', 'getProduk')->get();
        $transaksi = TransaksiCabai::where('user_id', $user->id)->where('status_transaksi', 'Check Out')->orderBy('updated_at', 'desc')->with('getKeranjang', 'getProduk')->get();

        $this->successStatus       = 200;
        $success['success']        = true;
        $success['data']           = $transaksi;

        return response()->json($success, $this->successStatus);

    }

    public function getTransaksiDetail($transaksiId) {

        $user = Auth::user();

        $transaksi = TransaksiCabai::where('id', $transaksiId)->with('getKeranjang', 'getProduk', 'getPembeli')->first();
        $gapoktan = Profile::where('user_id', $transaksi->gapoktan_id)->with('getUser')->first();
        $profilPembeli = Profile::where('user_id', $user->id)->with('getUser')->first();

        if($transaksi) {

            if($profilPembeli) {
                $this->successStatus       = 200;
                $success['success']        = true;
                $success['data']           = $transaksi;
                $success['gapoktan']       = $gapoktan;
                $success['pembeli']        = $profilPembeli;

                return response()->json($success, $this->successStatus);
            } else {
                $this->successStatus       = 200;
                $success['success']        = true;
                $success['profile']        = true;
                $success['message']        = "Harap Isi Profile terlebih dahulu, karena data alamat dan data pembeli belum ada";

                return response()->json($success, $this->successStatus);
            }

        } else {
            $this->successStatus       = 200;
            $success['success']        = true;
            $success['message']        = "Data tidak ditemukan";

            return response()->json($success, $this->successStatus);
        }

    }

    public function updateTransaksiBayar($transaksiId) {

        $transaksi = TransaksiCabai::find($transaksiId);
        $produk = ProdukSiapJual::find($transaksi->produk_id);
        $pembeli = Auth::user();

        $cek = TransaksiCabai::where('user_id', $pembeli->id)->where('gapoktan_id', $produk->gapoktan_id)->where('id', $transaksi->id)->where('status_transaksi', 'Menunggu Konfirmasi Pembayaran')->first();

        if($cek){

            $this->successStatus       = 200;
            $success['success']        = true;
            $success['bayar']          = true;
            $success['message']        = "Silahkan untuk bayar biaya transaksi, abaikan jika sudah bayar!";

            return response()->json($success, $this->successStatus);

        } else {

            if($transaksi) {
                $transaksi->status_transaksi = "Menunggu Konfirmasi Pembayaran";
                $transaksi->progress_transaksi = 50;
                $transaksi->harga = $produk->harga_jual;
                $transaksi->save();

                // update produk agar berhenti tayang
                $produk->status_tayang = 2;
                $produk->save();

                // insert notifikasi
                $notif = new Notifikasi();
                $notif->user_id = $transaksi->gapoktan_id;
                $notif->notifikasi = 'Pembeli '. $pembeli->username . ' Melakuakan pembayaran pada produk '. $produk->nama_produk .' silahkan untuk dikonfirmasi';
                $notif->status = 'Belum Dibaca';
                $notif->save();
                // end insert notifikasi

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

    }

    public function ListTransaksiHistoryKonsumen() {

        $user = Auth::user();

        $data = TransaksiCabai::where('user_id', $user->id)->orderBy('updated_at', 'desc')->with('getPembeli', 'getKeranjang', 'getProduk')->get();

        $this->successStatus       = 200;
        $success['success']        = true;
        $success['data']           = $data;

        return response()->json($success, $this->successStatus);

    }

    public function KonfirmasiBarang($transaksiId) {

        $user = Auth::user();

        $data = TransaksiCabai::where('user_id', $user->id)->where('id', $transaksiId)->first();
        $keranjang = Keranjang::find($data->keranjang_id);

        if($data) {

            $data->status_transaksi = 'Barang telah diterima';
            $data->progress_transaksi = 100;
            $data->save();

            // insert notifikasi
            $notif = new Notifikasi();
            $notif->user_id = $data->gapoktan_id;
            $notif->notifikasi = 'Produk '. $keranjang->no_transaksi . ' telah diterima oleh konsumen';
            $notif->status = 'Belum Dibaca';
            $notif->save();
            // end insert notifikasi

            $this->successStatus       = 200;
            $success['success']        = true;
            $success['data']           = $data;

            return response()->json($success, $this->successStatus);

        } else {

            $this->successStatus       = 200;
            $success['success']        = true;
            $success['message']        = "Data tidak ditemukan";

            return response()->json($success, $this->successStatus);

        }

    }

    public function ListRequest() {

        $user = Auth::user();

        $list = RequestProduk::where('user_id', $user->id)->orderBy('updated_at', 'desc')->with('getKonsumen')->get();
        $date = Carbon::now();

        $this->successStatus       = 200;
        $success['success']        = true;
        $success['data']           = $list;
        $success['dateNow']        = $date->format('Y-m-d');

        return response()->json($success, $this->successStatus);

    }

    public function SimpanRequestCabai(Request $request) {

        $user = Auth::user();

        $cek = MMinimalPembelian::where('gapoktan', $request->gapoktan)->first();
        $gapoktan = User::where('id', $request->gapoktan)->first();

        if($cek) { // jika gapoktan telah mengisi minimal pembelian
            if($cek->minimal_pembelian > intval($request->volume)){ // jika minimal pembelian lebih besar dari jumlah request volume
                $this->successStatus       = 200;
                $success['success']        = true;
                $success['message']        = 'Minimal pembelian di '.$gapoktan->username.' harus lebih dari '.$cek->minimal_pembelian.' Kilo';

                return response()->json($success, $this->successStatus);
            }
        }

        $date = Carbon::now();
        $tgl = $date->format('Ymd');
        $time = $date->format('His'); 
        $randomString = Str::random(5);

        $hargaCabai = MHargaCabaiPetani::where('kualitas', $request->kualitas)->where('gapoktan_id', $request->gapoktan)->first();
        $hargakemas = MHargaPengemasan::where('gapoktan_id', $request->gapoktan)->where('status', 1)->first();

        $perkiraan = ($request->volume * $hargaCabai->harga_jual) + ($request->volume * $hargakemas->harga);

        $simpan = new RequestProduk();
        $simpan->no_transaksi = 'TXRP-'.$tgl.strtoupper($randomString).$time;
        $simpan->user_id = $user->id;
        // $simpan->gapoktan_id = 2; // masih salah karena gapoktan baru diset 1, harusnya bisa lebih dari 1
        $simpan->gapoktan_id = $request->gapoktan;
        $simpan->tanggal_pembelian = $request->tanggal;
        $simpan->volume = $request->volume;
        $simpan->kualitas = $request->kualitas;
        $simpan->catatan = $request->catatan;
        $simpan->status = 'Pengajuan pemesanan cabai';
        $simpan->perkiraan_harga = $perkiraan;
        $simpan->save();

        // insert notifikasi
        $notif = new Notifikasi();
        $notif->user_id = $simpan->gapoktan_id;
        $notif->notifikasi = 'Pengguna '. $user->username. ' telah melakukan pemesanan cabai untuk tanggal '. $simpan->tanggal_pembeilian;
        $notif->status = 'Belum Dibaca';
        $notif->save();
        // end insert notifikasi

        $this->successStatus       = 200;
        $success['success']        = true;

        return response()->json($success, $this->successStatus);

    }

    public function deleteRequestcabai($requestId) {

        $data = RequestProduk::find($requestId);

        if($data) {

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

    public function KirimRequestProduk($requestId) {

        $user = Auth::user();

        $data = RequestProduk::find($requestId);

        if($data && $user) {

            $data->status = 'Menunggu konfirmasi gapoktan';
            $data->save();

            // kirim gapoktan notifikasi
            $notif = new Notifikasi();
            $notif->user_id = $data->gapoktan_id;
            $notif->notifikasi = 'Pembeli '. $user->username . ' Melakuakan pemesanan produk sejumlah '. $data->volume .' Kg untuk tanggal ' . str_replace("00:00:00", "", $data->tanggal_pembelian);
            $notif->status = 'Belum Dibaca';
            $notif->save();

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

    public function konsumenBayarPemesanan($noTx) {

        $data = RequestProduk::where('no_transaksi', $noTx)->where('status', 'Dikonfirmasi oleh gapoktan')->first();
        $hargaCabai = MHargaCabaiPetani::where('gapoktan_id', $data->gapoktan_id)->where('kualitas', $data->kualitas)->first();
        $hargaKemas =  MHargaPengemasan::where('gapoktan_id', $data->gapoktan_id)->where('status', 1)->first();
        $getHarga = getHargaPesananCabai($noTx);

        $hitung = ($data->volume * $hargaCabai->harga_jual) + ($data->volume * $hargaKemas->harga);

        if($data) {

            $data->status = 'Menunggu konfirmasi pembayaran';
            $data->harga = $getHarga;
            if($getHarga == $hitung) {
                $data->supply_demand = 0;
            } else {
                $data->supply_demand = 1;
            }
            $data->save();

            $this->successStatus       = 200;
            $success['success']        = true;
            $success['data']           = $data;

            return response()->json($success, $this->successStatus);

        } else {

            $this->successStatus       = 200;
            $success['success']        = true;
            $success['message']        = "Data tidak ditemukan";

            return response()->json($success, $this->successStatus);

        }

    }

    public function getTransaksiDetailRequest($requestId) {

        $transaksi = RequestProduk::where('id', $requestId)->with('getKonsumen')->first();
        $gapoktan = Profile::where('user_id', $transaksi->gapoktan_id)->with('getUser')->first();
        $profilPembeli = Profile::where('user_id', $transaksi->user_id)->with('getUser')->first();

        if($transaksi) {

            if($profilPembeli) {
                $this->successStatus       = 200;
                $success['success']        = true;
                $success['data']           = $transaksi;
                $success['gapoktan']       = $gapoktan;
                $success['pembeli']        = $profilPembeli;

                return response()->json($success, $this->successStatus);
            } else {
                $this->successStatus       = 200;
                $success['success']        = true;
                $success['profile']        = true;
                $success['message']        = "Harap Isi Profile terlebih dahulu, karena data alamat dan data pembeli belum ada";

                return response()->json($success, $this->successStatus);
            }

        } else {
            $this->successStatus       = 200;
            $success['success']        = true;
            $success['message']        = "Data tidak ditemukan";

            return response()->json($success, $this->successStatus);
        }

    }

    public function konsumenTerimaProdukRequest($noTx) {

        $transaksi = RequestProduk::where('no_transaksi', $noTx)->first();

        if($transaksi) {

            $transaksi->status = 'Produk diterima konsumen';
            $transaksi->save();

            // kirim notifikasi ke gapoktan
            $notifPetani = new Notifikasi();
            $notifPetani->user_id = $transaksi->gapoktan_id;
            $notifPetani->notifikasi = 'Pemesanan cabai dengan nomor transaksi '.$transaksi->no_transaksi.' telah telah diterima oleh konsumen';
            $notifPetani->status = "Belum Dibaca";
            $notifPetani->save();
            // end notifikasi ke gapoktan

            $this->successStatus       = 200;
            $success['success']        = true;

            return response()->json($success, $this->successStatus);

        } else {

            $this->successStatus       = 200;
            $success['success']        = true;
            $success['message']        = "Data transaksi tidak ditemukan";
            
            return response()->json($success, $this->successStatus);

        }

    }

    public function getDashboardPetani() {

        $user = Auth::user();

        $keranjang = Keranjang::where('user', $user->id)->where('status', 'Keranjang')->with('getProduk')->orderBy('created_at', 'desc')->get();
        $transaksiMarket = TransaksiCabai::where('user_id', $user->id)->with('getProduk', 'getKeranjang')->orderBy('created_at', 'desc')->get();
        $transaksiMarketTotal = TransaksiCabai::where('user_id', $user->id)->with('getProduk', 'getKeranjang')->orderBy('created_at', 'desc')->get()->count();
        $transaksiRequest = RequestProduk::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();
        $transaksiRequestTotal = RequestProduk::where('user_id', $user->id)->orderBy('created_at', 'desc')->get()->count();

        $getGapoktan = User::where('role', 'gapoktan')->get();

        $chart = [];
        foreach($getGapoktan as $g) {
            $cek1 = DB::table('transaksi_cabai')
            ->select('transaksi_cabai.progress_transaksi', 'transaksi_cabai.gapoktan_id', 'transaksi_cabai.user_id', 'gapoktan_gudang.user_gapoktan')
            ->join('produk_siap_jual','produk_siap_jual.id','=','transaksi_cabai.produk_id')
            ->where('transaksi_cabai.user_id', $user->id)
            ->where('transaksi_cabai.gapoktan_id', $g->id)
            ->where('transaksi_cabai.progress_transaksi', '>', 60)
            ->sum('produk_siap_jual.volume');
            // $cek1 = TransaksiCabai::where('user_id', $user->id)->where('gapoktan_id', $g->id)->where('progress_transaksi', '>', 60)->sum();
            $cek2 = RequestProduk::where('user_id', $user->id)->where('gapoktan_id', $g->id)->where('status', 'Produk diterima konsumen')->sum('volume');

            if(in_array(['gapoktan' => $g->username, 'jumlah' => intval($cek1+$cek2)], $chart)) {
                // jika sudah ada di array tidak perlu di masukan
            } else {
                if(intval($cek1+$cek2) != 0) {
                    array_push($chart, ['gapoktan' => $g->username, 'jumlah' => intval($cek1+$cek2)]);
                }
            }
        }

        $this->successStatus                 = 200;
        $success['success']                  = true;
        $success['chart']                    = $chart;
        $success['keranjang']                = $keranjang;
        $success['transaksiMarket']          = $transaksiMarket;
        $success['transaksiMarketTotal']     = $transaksiMarketTotal;
        $success['transaksiRequest']         = $transaksiRequest;
        $success['transaksiRequestTotal']    = $transaksiRequestTotal;

        return response()->json($success, $this->successStatus);

    }

}
