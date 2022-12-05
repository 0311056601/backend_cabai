<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LahanImg;
use App\Models\Profile;
use App\Models\Lahan;
use App\Models\User;
use Storage;
use Auth;
use File;
use DB;

class LahanController extends Controller {

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

    public function DetailLahan($lahanId) {

        $lahan = Lahan::where('id', $lahanId)->with('getImg')->first();

        if($lahan) {

            $this->successStatus       = 200;
            $success['success']        = true;
            $success['lahan']          = $lahan;

            return response()->json($success, $this->successStatus);

        } else {

            $this->successStatus       = 200;
            $success['success']        = false;
            $success['message']        = 'Data lahan tidak ditemukan';

            return response()->json($success, $this->successStatus);

        }

    }

    public function listLahan() {

        $user = Auth::user();
        $lahan = Lahan::where('user_id', $user->id)->get();

        $this->successStatus       = 200;
        $success['success']        = true;
        $success['user']           = $user;
        $success['lahan']          = $lahan;

        return response()->json($success, $this->successStatus);

    }

    public function listLahanWithPetaniId($petaniId) {

        $lahan = Lahan::where('user_id', $petaniId)->get();

        $this->successStatus       = 200;
        $success['success']        = true;
        $success['lahan']          = $lahan;

        return response()->json($success, $this->successStatus);

    }

    public function simpanLahan(Request $request) {

        $user = Auth::user();

        if($user) {

            $lahan = new Lahan;
            $lahan->user_id = $user->id;
            $lahan->nama_lahan = $request->nama_lahan;
            $lahan->luas_lahan = $request->luas_lahan;
            $lahan->status_kepemilikan = $request->status_kepemilikan;
            $lahan->alamat_lahan = $request->alamat_lahan;
            if($request->latitude) {
                $lahan->latitude = $request->latitude;
            }
            if($request->longitude) {
                $lahan->longitude = $request->longitude;
            }
            $lahan->save();

            if ($request->hasFile('files')) {
                $files = $request->file('files');
    
                foreach($files as $file) {
    
                    $name = $file->getClientOriginalName();
                    $size = $file->getSize();
    
                    $file->move(public_path("images/lahan/") . $lahan->id . '/', $name);
    
                    $image = new LahanImg();
                    $image->lahan_id = $lahan->id;
                    $image->image = "images/lahan/" . $lahan->id . '/' . $name;
                    $image->size  = $size;
    
                    $image->save();
                }
            }

            $this->successStatus       = 200;
            $success['success']        = true;
            $success['lahan']          = $lahan;

            return response()->json($success, $this->successStatus);

        } else {
            $this->successStatus       = 200;
            $success['success']        = false;
            $success['message']        = 'User tidak diteumakn';

            return response()->json($success, $this->successStatus);
        }

    }

    public function hapusLahan($lahanId) {

        $user = Auth::user();

        $cekLahan = Lahan::where('user_id', $user->id)->where('id', $lahanId)->first();

        if($cekLahan) {
            $cekLahan->delete();

            $this->successStatus       = 200;
            $success['success']        = true;

            return response()->json($success, $this->successStatus);
        } else {
            $this->successStatus       = 200;
            $success['success']        = false;
            $success['message']        = 'Lahan tidak bisa dihapus, karena lahan milik user lain';

            return response()->json($success, $this->successStatus);
        }

    }

    public function updateLahan(Request $request, $lahanId) {

        $user = Auth::user();

        $lahan = Lahan::find($lahanId);

        if($lahan->user_id == $user->id) {

            $lahan->nama_lahan = $request->nama_lahan;
            $lahan->luas_lahan = $request->luas_lahan;
            $lahan->status_kepemilikan = $request->status_kepemilikan;
            $lahan->alamat_lahan = $request->alamat_lahan;
            $lahan->latitude = $request->latitude;
            $lahan->longitude = $request->longitude;
            $lahan->save();

            $this->successStatus       = 200;
            $success['success']        = true;
            $success['lahan']          = $lahan;
            $success['user']           = $user;

            return response()->json($success, $this->successStatus);

        } else {

            $this->successStatus       = 200;
            $success['success']        = false;
            $success['message']        = 'Lahan tidak bisa diupdate, karena lahan milik user lain';

            return response()->json($success, $this->successStatus);

        }

    }

}