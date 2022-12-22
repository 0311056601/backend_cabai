<?php

namespace App\Http\Controllers\API;


use App\Models\GapoktanGudangExpiredDetail;
use App\Models\GapoktanGudangExpired;
use App\Http\Controllers\Controller;
use App\Models\GapoktanGudangDetail;
use App\Models\ProdukSiapJualDetail;
use App\Models\MHargaCabaiPetani;
use App\Models\MHargaPengemasan;
use App\Models\GapoktanGudang;
use App\Models\LogRequestData;
use App\Models\TransaksiCabai;
use App\Models\ProdukSiapJual;
use App\Models\RequestProduk;
use App\Models\ProdukPetani;
use Illuminate\Http\Request;
use App\Models\TransaksiLog;
use App\Models\RequestData;
use App\Models\Keranjang;
use App\Models\MExpired;
use App\Models\Profile;
use App\Models\Lahan;
use App\Models\MBank;
use App\Models\User;
use Carbon\Carbon;

class ExternalController extends Controller
{
    public function getDataQRProduk($produkId) {
        $data = ProdukSiapJual::where('id', $produkId)->with('getDetail', 'getImg')->first();
        $detail = ProdukSiapJualDetail::where('produk_siap_jual', $produkId)->first();
        $petani = ProdukPetani::where('id', $detail->produk_petani)->with('getImage', 'getCreator')->first();
        $userP = User::where('id', $detail->petani)->first();
        $lahan = Lahan::where('user_id', $userP->id)->with('getImg')->get();

        if($data) {
            $this->successStatus       = 200;
            $success['success']        = true;
            $success['produkJual']     = $data;
            $success['ProdukPetani']   = $petani;
            $success['lahan']          = $lahan;

            return response()->json($success, $this->successStatus);
        } else {
            $this->successStatus       = 200;
            $success['success']        = false;
            $success['message']        = "Data tidak ditemukan";

            return response()->json($success, $this->successStatus);
        }
    }

    public function getMarketGapoktan() {

        // $data = ProdukSiapJual::where('status_tayang', 1)->orderBy('updated_at', 'desc')->with('getDetail', 'getImg')->get();
        $gapoktan = User::where('role', 'gapoktan')->with('getProfile')->get();
        // $data = User::orderBy('id', 'desc')->get();

        if($gapoktan) {

            $this->successStatus       = 200;
            $success['success']        = true;
            // $success['data']           = $data;
            $success['gapoktan']       = $gapoktan;
    
            return response()->json($success, $this->successStatus);
            
        } else {
            
            $this->successStatus       = 200;
            $success['success']        = true;
            $success['gapoktan']       = [];
    
            return response()->json($success, $this->successStatus);

        }

    }

    public function getProdukGapoktanSelected($gapoktanId) {

        $data = ProdukSiapJual::where('gapoktan_id', $gapoktanId)->where('status_tayang', 1)->orderBy('updated_at', 'desc')->with('getDetail', 'getImg')->get();
        $gapoktan = User::where('id', $gapoktanId)->with('getProfile')->first();

        if($data) {

            $this->successStatus       = 200;
            $success['success']        = true;
            $success['data']           = $data;
            $success['gapoktan']       = $gapoktan;
    
            return response()->json($success, $this->successStatus);
            
        } else {
            
            $this->successStatus       = 200;
            $success['success']        = true;
            $success['data']           = [];
    
            return response()->json($success, $this->successStatus);

        }

    }

    public function getTransaksiQR($noTx) {

        $keranjang = Keranjang::where('no_transaksi', $noTx)->first();
        $transaksi = TransaksiCabai::where('keranjang_id', $keranjang->id)->with('getPembeli', 'getKeranjang', 'getProduk')->first();
        $produk = ProdukSiapJual::where('id', $transaksi->produk_id)->with('getDetail', 'getImg')->first();
        $produkDetail = ProdukSiapJualDetail::where('produk_siap_jual', $produk->id)->first();
        // $petani = User::where('id', $produkDetail->petani)->with('getProfile', 'getLahan')->first();
        $petani = User::where('id', $produkDetail->petani)->with('getProfile', 'getLahan')->first();
        $lahan = Lahan::where('user_id', $petani->id)->with('getImg')->get();
        $produkHash = TransaksiLog::where('produk_id', $produk->id)->where('smartcontract', 'produk')->first();
        $transaksiHash = TransaksiLog::where('produk_id', $transaksi->id)->where('smartcontract', 'transaksi')->first();
        $pembeli = Profile::where('user_id', $transaksi->user_id)->with('getUser')->first();
        $gapoktan = Profile::where('user_id', $keranjang->gapoktan_id)->with('getUser')->first();

        $this->successStatus       = 200;
        $success['success']        = true;
        $success['keranjang']      = $keranjang;
        $success['transaksi']      = $transaksi;
        $success['produk']         = $produk;
        $success['petani']         = $petani;
        $success['lahan']          = $lahan;
        $success['produkHash']     = $produkHash;
        $success['transaksiHash']  = $transaksiHash;
        $success['pembeli']        = $pembeli;
        $success['gapoktan']       = $gapoktan;

        return response()->json($success, $this->successStatus);

    }

    public function getDetailTransaksi($noTx) {

        $keranjang = Keranjang::where('no_transaksi', $noTx)->first();
        $transaksi = TransaksiCabai::where('keranjang_id', $keranjang->id)->first();

        if($keranjang && $transaksi) {

            $pembeli = Profile::where('user_id', $transaksi->user_id)->with('getUser')->first();
            $gapoktan = Profile::where('user_id', $keranjang->gapoktan_id)->with('getUser')->first();
            $produk = ProdukSiapJual::where('id', $transaksi->produk_id)->with('getDetail', 'getImg')->first();
            $hashTransaksi = TransaksiLog::where('produk_id', $produk->id)->where('smartcontract', 'transaksi')->first();
            $hashProduk = TransaksiLog::where('produk_id', $produk->id)->where('smartcontract', 'produk')->first();

            $this->successStatus       = 200;
            $success['success']        = true;
            $success['keranjang']      = $keranjang;
            $success['transaksi']      = $transaksi;
            $success['produk']         = $produk;
            $success['produkHash']     = $hashProduk;
            $success['transaksiHash']  = $hashTransaksi;
            $success['pembeli']        = $pembeli;
            $success['gapoktan']       = $gapoktan;

            return response()->json($success, $this->successStatus);

        } else {

            $this->successStatus       = 200;
            $success['success']        = true;
            $success['message']        = "Transaksi tidak ditemukan";

            return response()->json($success, $this->successStatus);

        }

    }

    public function GetInvoice($noTx) {
        
        $date = Carbon::now();
        $keranjang = Keranjang::where('no_transaksi', $noTx)->first();
        $produk = ProdukSiapJualDetail::where('produk_siap_jual', $keranjang->produk)->first();
        $transaksi = TransaksiCabai::where('keranjang_id', $keranjang->id)->with('getKeranjang', 'getProduk', 'getPembeli')->first();
        $gapoktan = User::where('id', $transaksi->gapoktan_id)->with('getProfile')->first();
        $pembeli = Profile::where('user_id', $transaksi->user_id)->with('getUser')->first();
        $sekarang = $date->format('Y-m-d');
        
        if($transaksi) {
            
            $cekSupplyDemand = CekSupplyDemand($sekarang, $gapoktan->id, $produk->kualitas_cabai);

            if($pembeli) {
                $this->successStatus       = 200;
                $success['success']        = true;
                $success['data']           = $transaksi;
                $success['pembeli']        = $pembeli;
                $success['gapoktan']       = $gapoktan;
                $success['supplyDemand']   = $cekSupplyDemand;

                return response()->json($success, $this->successStatus);
            } else {
                $this->successStatus       = 200;
                $success['success']        = true;
                $success['message']        = "Data pembeli tidak ditemukan";

                return response()->json($success, $this->successStatus);
            }

        } else {
            $this->successStatus       = 200;
            $success['success']        = true;
            $success['message']        = "Data invoice tidak ditemukan";

            return response()->json($success, $this->successStatus);
        }

    }

    public function getGapoktanList() {

        $gapoktan = User::where('role', 'gapoktan')->where('status', 1)->with('getProfile')->get();

        $this->successStatus       = 200;
        $success['success']        = true;
        $success['gapoktan']       = $gapoktan;

        return response()->json($success, $this->successStatus);

    }

    public function getGapoktanHarga($gapoktanId) {

        $now = Carbon::now(); // mencari waktu sekarang
        $sekarang = strtotime($now); // convert waktu sekarang ke integer

        $gapoktan = User::where('id', $gapoktanId)->with('getProfile')->first();
        $kualitas = MHargaCabaiPetani::where('gapoktan_id', $gapoktanId)->get();
        $pengemasan = MHargaPengemasan::where('gapoktan_id', $gapoktanId)->where('status', 1)->first();

        $this->successStatus       = 200;
        $success['success']        = true;
        $success['gapoktan']       = $gapoktan;
        $success['kualitas']       = $kualitas;
        $success['pengemasan']     = $pengemasan;

        return response()->json($success, $this->successStatus);

    }

    public function GetInvoiceRequest($noTx) {

        $data = RequestProduk::where('no_transaksi', $noTx)->first();
        $hargaCabai = MHargaCabaiPetani::where('gapoktan_id', $data->gapoktan_id)->where('kualitas', $data->kualitas)->first();
        $hargaPeckaging = MHargaPengemasan::where('gapoktan_id', $data->gapoktan_id)->where('status', 1)->first();
        $gapoktan = User::where('id', $data->gapoktan_id)->with('getProfile')->first();
        $pembeli = Profile::where('user_id', $data->user_id)->with('getUser')->first();

        if(($data->volume * $hargaCabai->harga_jual) + ($data->volume * $hargaPeckaging->harga) == intval($data->harga)) {
            $supplyDemand = 0;
        } else {
            $supplyDemand = 1;
        }
        
        if($data) {

            if($pembeli) {
                $this->successStatus       = 200;
                $success['success']        = true;
                $success['data']           = $data;
                $success['pembeli']        = $pembeli;
                $success['gapoktan']       = $gapoktan;
                $success['hargaCabai']     = $hargaCabai;
                $success['hargaPeckaging'] = $hargaPeckaging;
                $success['supplyDemand']   = $supplyDemand;
                // $success['cekHarga1']   = ($data->volume * $hargaCabai->harga_jual) + ($data->volume * $hargaPeckaging->harga);
                // $success['cekHarga2']   = intval($data->harga);

                return response()->json($success, $this->successStatus);

            } else {
                $this->successStatus       = 200;
                $success['success']        = true;
                $success['message']        = "Data pembeli tidak ditemukan";

                return response()->json($success, $this->successStatus);

            }

        } else {
            $this->successStatus       = 200;
            $success['success']        = true;
            $success['message']        = "Data invoice tidak ditemukan";

            return response()->json($success, $this->successStatus);
        }

    }

    public function getTransaksiRequestQR($noTx) {

        $transaksi = RequestProduk::where('no_transaksi', $noTx)->first();
        $transaksiHash = TransaksiLog::where('name', $noTx)->where('smartcontract', 'request')->first();
        $pembeli = Profile::where('user_id', $transaksi->user_id)->with('getUser')->first();
        $gapoktan = Profile::where('user_id', $transaksi->gapoktan_id)->with('getUser')->first();
        $detailCabai = GapoktanGudangDetail::where('no_transaksi', $noTx)->with('getPetani', 'getGapoktan', 'getKonsumen')->get();

        $this->successStatus       = 200;
        $success['success']        = true;
        $success['transaksi']      = $transaksi;
        $success['transaksiHash']  = $transaksiHash;
        $success['pembeli']        = $pembeli;
        $success['gapoktan']       = $gapoktan;
        $success['detailCabai']    = $detailCabai;

        return response()->json($success, $this->successStatus);

    }

    public function indexRequestData() {

        $gapoktan = User::where('role', 'gapoktan')->where('status', 1)->get();

        $this->successStatus       = 200;
        $success['success']        = true;
        $success['gapoktan']       = $gapoktan;

        return response()->json($success, $this->successStatus);

    }

    public function RequestData(Request $request) {

        $simpan = new RequestData();
        $simpan->nama = $request->nama;
        $simpan->alamat = $request->alamat;
        $simpan->email = $request->email;
        $simpan->gapoktan = $request->gapoktan;
        $simpan->data = $request->data;
        $simpan->wallet = $request->signer;
        $simpan->status = 'Menunggu Konfirmasi';
        $simpan->save();

        $this->successStatus       = 200;
        $success['success']        = true;
        $success['gapoktan']       = $simpan;

        return response()->json($success, $this->successStatus);

    }

    public function getRD($address) {

        $cek = RequestData::where('wallet', $address)->first();

        if($cek) {
            
            $data = RequestData::where('wallet', $address)->with('getGapoktan')->orderBy('updated_at', 'desc')->get();

            $this->successStatus       = 200;
            $success['success']        = true;
            $success['data']           = $data;

            return response()->json($success, $this->successStatus);

        } else {

            $this->successStatus       = 200;
            $success['success']        = true;
            $success['message']        = 'Belum ada data yang direquest';

            return response()->json($success, $this->successStatus);

        }

    }

    public function addLogRequestData(Request $request) {

        $log = new LogRequestData;
        $log->id_request_data = $request->request_data_id;
        $log->nama = $request->name;
        $log->wallet = $request->wallet;
        $log->email = $request->email;
        $log->data = $request->data;
        $log->status = 'Berhasil Diminta';
        $log->response = $request->response . ' Data';
        $log->save();

        // ubah status request data
        $data = RequestData::find($request->request_data_id);
        $data->status = 'Berhasil Diminta';
        $data->save();

        $this->successStatus       = 200;
        $success['success']        = true;
        $success['message']        = 'Belum ada data yang direquest';

        return response()->json($success, $this->successStatus);

    }

    public function getExpiredQR($expiredId) {

        $data = GapoktanGudangExpired::where('id', $expiredId)->with('getGapoktan')->first();
        $detail = GapoktanGudangExpiredDetail::where('gapoktan_gudang_expired_id', $expiredId)->with('getGudang', 'getPetani')->get();
        $transaksiHash = TransaksiLog::where('produk_id', $data->id)->where('smartcontract', 'expired')->first();

        $this->successStatus       = 200;
        $success['success']        = true;
        $success['data']           = $data;
        $success['detail']         = $detail;
        $success['transaksiHash']  = $transaksiHash;

        return response()->json($success, $this->successStatus);


    }

    public function getMasterBank() {

        $data = MBank::orderBy('nama', 'asc')->get();

        $this->successStatus       = 200;
        $success['success']        = true;
        $success['data']           = $data;

        return response()->json($success, $this->successStatus);

    }

    public function getDashboardLogin() {

        $data = [];
        
        for($i=0; $i < 7; $i++) {
            $tgl = Carbon::now();

            
            if($i != 0) {
                $t = $tgl->add(-$i, 'day');
                // get data all jumlah produk market
                $dataProduk = ProdukSiapJual::where('tanggal_pengemasan', $t->format('Y-m-d'))->get()->sum('volume');
    
                // get data all permintaan cabai
                $dataPermintaan = RequestProduk::where('tanggal_pembelian', 'like', '%'.$t->format('Y-m-d').'%')->get()->sum('volume');

                array_push($data, ['tanggal' => $t->format('d/M'), 'produk' => $dataProduk, 'kebutuhan' => $dataPermintaan, 'no' => $i+1]);
            } else {
                // get data all jumlah produk market
                $dataProduk = ProdukSiapJual::where('tanggal_pengemasan', $tgl->format('Y-m-d'))->get()->sum('volume');
    
                // get data all permintaan cabai
                $dataPermintaan = RequestProduk::where('tanggal_pembelian', 'like', '%'.$tgl->format('Y-m-d').'%')->get()->sum('volume');

                array_push($data, ['tanggal' => $tgl->format('d/M'), 'produk' => $dataProduk, 'kebutuhan' => $dataPermintaan, 'no' => $i+1]);
            }

        }

        $this->successStatus       = 200;
        $success['success']        = true;
        $success['data']           = $data;

        return response()->json($success, $this->successStatus);

    }

    public function getdataGudang($gapkotanId) {

        // get data gudang gapoktan
        // $gudang = GapoktanGudang::where('user_gapoktan', $gapkotanId)->where('status', 1)->orderBy('updated_at', 'desc')->get();
        $expired = MExpired::where('status', 'Aktif')->where('gapoktan_id', $gapkotanId)->first();

        $gudang = GapoktanGudang::where('user_gapoktan', $gapkotanId)->where('status', 1)->orderBy('updated_at', 'desc')->with('getProduk')->get();

        $dataSuper = 0;
        $data1 = 0;
        $data2 = 0;
        $currentDate = Carbon::now();
        $now = $currentDate->format('Y-m-d');

        foreach($gudang as $g) {

            // get data expired
            $expire = date('Y-m-d', strtotime($g->getProduk->tanggal_panen. ' + '.$expired->expired.' days'));

            // jika hari ini belum expired maka data dimasukkan
            if(strtotime($now) <= strtotime($expire)) {

                if($g->kualitas == 'Kelas Super') {
                    $dataSuper += $g->volume;
                } else if($g->kualitas == 'Kelas 1') {
                    $data1 += $g->volume;
                } else if($g->kualitas == 'Kelas 2') {
                    $data2 += $g->volume;
                }

            }

        }

        $data = compact(
            'dataSuper',
            'data1',
            'data2',
        );

        $this->successStatus = 200;
        $success['success']  = true;
        $success['data']     = $data;

        return response()->json($success, $this->successStatus);

    }


}
