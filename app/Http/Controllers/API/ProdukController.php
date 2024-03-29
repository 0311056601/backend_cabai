<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ProdukSiapJualDetail;
use App\Models\ProdukPetaniImg;
use App\Models\TransaksiCabai;
use Illuminate\Http\Request;
use App\Models\ProdukPetani;
use App\Models\Profile;
use App\Models\Lahan;
use Storage;
use Auth;
use File;
use DB;

class ProdukController extends Controller
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

    public function listProdukPetani() {
        $user = Auth::user();

        $produk = ProdukPetani::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();

        $this->successStatus       = 200;
        $success['success']        = true;
        $success['produkPetani']   = $produk;

        return response()->json($success, $this->successStatus);
    }

    public function addProdukPetani(Request $request) {

        $user = Auth::user();

        if($user) {
            $simpanProduk = new ProdukPetani();
            $simpanProduk->user_id = $user->id;
            $simpanProduk->gapoktan_id = $user->gapoktan;
            $simpanProduk->tanggal_panen = $request->tanggal_panen;
            $simpanProduk->kualitas = $request->kualitas;
            $simpanProduk->harga = $request->harga;
            $simpanProduk->volume = $request->volume;
            $simpanProduk->status = "Produk Dibuat Petani";
            $simpanProduk->save();

            if ($request->hasFile('files')) {
                $files = $request->file('files');
    
                foreach($files as $file) {
    
                    $name = $file->getClientOriginalName();
                    $size = $file->getSize();
    
                    $file->move(public_path("images/produk/") . $simpanProduk->id . '/', $name);
    
                    $image = new ProdukPetaniImg();
                    $image->produk_id = $simpanProduk->id;
                    $image->image = "images/produk/" . $simpanProduk->id . '/' . $name;
                    $image->size  = $size;
    
                    $image->save();
                }
            }

            $this->successStatus       = 200;
            $success['success']        = true;
            $success['produkPetani']   = $simpanProduk;

            return response()->json($success, $this->successStatus);

        } else {

            $this->successStatus       = 200;
            $success['success']        = false;
            $success['message']        = "User login bermasalah";

            return response()->json($success, $this->successStatus);

        }
    }

    public function updateProdukPetani(Request $request) {

    }

    public function detailProdukPetani($produkId) {
        $produk = ProdukPetani::where('id',$produkId)->with('getImage', 'getCreator')->first();

        if($produk) {

            $this->successStatus       = 200;
            $success['success']        = true;
            $success['data']           = $produk;

            return response()->json($success, $this->successStatus);
        } else {
            $this->successStatus       = 200;
            $success['success']        = false;
            $success['message']        = "Produk tidak ditemukan";

            return response()->json($success, $this->successStatus);
        }
    }

    public function hapusProdukPetani($produkId) {
        $produk = ProdukPetani::find($produkId);

        if($produk) {
            $delete = ProdukPetani::where('id', $produkId)->first()->delete();

            $this->successStatus       = 200;
            $success['success']        = true;

            return response()->json($success, $this->successStatus);
        } else {
            $this->successStatus       = 200;
            $success['success']        = false;
            $success['message']        = "Produk tidak ditemukan";

            return response()->json($success, $this->successStatus);
        }
    }

    public function petaniKirimPeroduk($produkId) {

        $produk = ProdukPetani::find($produkId);

        if($produk) {

            // $produk->status = "Produk Dikirim ke Gapoktan";
            $produk->status = "Menunggu Dimasukan ke Gudang oleh Gapoktan";
            $produk->save();

            $this->successStatus       = 200;
            $success['success']        = true;
            $success['produkPetani']   = $produk;

            return response()->json($success, $this->successStatus);
        } else {
            $this->successStatus       = 200;
            $success['success']        = false;
            $success['message']        = "Produk tidak ditemukan";

            return response()->json($success, $this->successStatus);
        }

    }

    public function getTracePanen($produkPanenId) {

        $user = Auth::user();
        $produkPetani = ProdukPetani::where('id', $produkPanenId)->with('getImage')->first();
        $profile = Profile::where('user_id', $user->id)->first();
        $lahan = Lahan::where('user_id', $user->id)->with('getImg')->first();
        $produk = ProdukSiapJualDetail::where('produk_petani', $produkPanenId)->with('ProdukSiapJual', 'ProdukSiapJualImage')->get();

        $arr = [];
        foreach($produk as $p) {
            $transaksi = TransaksiCabai::where('produk_id', $p->produk_siap_jual)->with('getPembeli', 'getProfilePembeli', 'getGapoktan', 'getProfileGapoktan', 'getKeranjang', 'getProduk')->first();
            if($transaksi){
                array_push($arr, $transaksi);
            }
        }

        $this->successStatus       = 200;
        $success['success']        = true;
        $success['user']           = $user;
        $success['profile']        = $profile;
        $success['produkPetani']   = $produkPetani;
        $success['lahan']          = $lahan;
        $success['produk']         = $produk;
        $success['transaksi']      = $arr;

        return response()->json($success, $this->successStatus);

    }
}
