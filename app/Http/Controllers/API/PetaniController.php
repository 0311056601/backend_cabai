<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ProdukSiapJualDetail;
use App\Models\ProdukSiapJualImage;
use App\Models\MHargaCabaiPetani;
use App\Models\MHargaPengemasan;
use App\Models\ProdukPetaniImg;
use App\Models\TransaksiCabai;
use App\Models\RequestProduk;
use App\Models\ProdukPetani;
use App\Models\TransaksiLog;
use Illuminate\Http\Request;
use App\Models\SaldoDetail;
use App\Models\Keranjang;
use App\Models\Profile;
use App\Models\Saldo;
use App\Models\Lahan;
use App\Models\User;
use Carbon\Carbon;
use Storage;
use Auth;
use File;
use DB;

class PetaniController extends Controller
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
        
        $lahan = Lahan::where('user_id', $user->id)->count();
        $panen = ProdukPetani::where('user_id', $user->id)->count();
        $saldoTotal = Saldo::where('gapoktan_id', $user->gapoktan)->where('user_id', $user->id)->orderBy('created_at', 'desc')->first(); // ambil data terbaru
        $saldoKeluar = Saldo::where('gapoktan_id', $user->gapoktan)->where('user_id', $user->id)->sum('saldo_out');

        // data chart
        $produk = ProdukPetani::where('user_id', $user->id)->distinct()->get()->pluck('kualitas');

        $chart = [];
        foreach($produk as $p) {
            $cek = ProdukPetani::where('kualitas', $p)->where('user_id', $user->id)->where('status', 'Produk dikonfirmasi gapoktan')->sum('volume');

            if(in_array(['kualitas' => $p, 'jumlah' => intval($cek)], $chart)) {
                // jika sudah ada di array tidak perlu di masukan
            } else {
                array_push($chart, ['kualitas' => $p, 'jumlah' => intval($cek)]);
            }
        }

        $this->successStatus            = 200;
        $success['success']             = true;
        $success['DLahan']              = $lahan;
        $success['DPanen']              = $panen;
        $success['DSaldo']              = $saldoTotal;
        $success['DSaldoOut']           = $saldoKeluar;
        $success['DChart']              = $chart;

        return response()->json($success, $this->successStatus);

    }
}
