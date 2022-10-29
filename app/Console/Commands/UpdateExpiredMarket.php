<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProdukSiapJual;
use App\Models\MExpired;
use App\Models\User;
use Carbon\Carbon;
use DB;

class UpdateExpiredMarket extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @string
     */
    protected $signature = 'cek:produkexpired';

    /**
     * The console command description.
     *
     * @string
     */
    protected $description = 'Produk di market telah dicek';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $date = Carbon::now(); // get tanggal sekarang
        $now = $date->format('ymd'); // ubah formatnya tanggal sekarang
        $sekarang = intval($now); // ubah format tanggal menjadi integer

        $gapoktan = User::where('role','gapoktan')->get(); // get data gapoktan

        try{

            foreach($gapoktan as $g) { // loop gaopktan

                $expired = MExpired::where('gapoktan_id', $g->id)->where('status', 'Aktif')->first(); // get data expired per gapoktan

                $success['expiredDalam']        = $expired->expired;

                $produk = ProdukSiapJual::where('status_tayang', 1)->where('gapoktan_id', $g->id)->get(); // get produk gapoktan yang tayang di market

                foreach($produk as $p) { // loop produk gapoktan

                    $tanggalProduk = date("y-m-d", strtotime($p->tanggal_pengemasan)); // format tanggal
                    // $tanggalKemas = (int)$tanggalProduk;
                    $tanggalKemas = intval(str_replace("-",'',$tanggalProduk));
                    $tglExpired = date('ymd', strtotime($tanggalProduk . ' +'.$expired->expired.' day'));
                    
                    if($sekarang > intval($tglExpired)) { // jika tanggal sekarang lebih besar / lewat dari tanggal expired
                        $p->status_tayang = 3; // masuk gudang expired
                        $p->save();
                    }
                }

            }

        } catch (RequestException $e) {
            return response()->json($e);
        }

        return 0;

    }
}
