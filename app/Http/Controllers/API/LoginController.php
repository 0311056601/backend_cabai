<?php

namespace App\Http\Controllers\API;

use App\Jobs\SendEmailRegisterGapoktanJob;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Profile;
use App\Models\User;
use Auth;

class LoginController extends Controller {

    public $successStatus = 401;

    public function login(Request $request) {
        $success['success']     = false;
        $success['token']       = null;

        $cekUser = User::where('email', request('email'))->where('status', 1)->first();

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password], $request->get('remember'))) {

            if($cekUser) {

                $this->successStatus    = 200;
                $success['success']     = true;
                $user                   = User::where('email', request('email'))->where('status', 1)->first();
                $success['token']       = $user->createToken('MyApp')->accessToken;
                $success['user']        = $user;

            } else {

                $success['success']     = false;
                $success['token']       = null;
                $success['user_detail'] = null;
                $success['message']     = 'User tidak aktif';

            }
        } else {

            $success['success']     = false;
            $success['token']       = null;
            $success['user_detail'] = null;
            $success['message']     = 'User tidak ditemukan';

        }
        return response()->json($success, $this->successStatus);
    }

    public function register(Request $request) {

        if($request->passwordK == $request->password) {

            $cekUsername = User::where('username', $request->username)->first();
            $cekEmail = User::where('email', $request->email)->first();

            if($cekUsername || $cekEmail) {

                if($cekUsername) {
                    $success['success']     = false;
                    $success['token']       = null;
                    $success['user_detail'] = null;
                    $success['message']     = 'Username sudah digunakan';

                    return response()->json($success, $this->successStatus);
                } else if($cekEmail) {
                    $success['success']     = false;
                    $success['token']       = null;
                    $success['user_detail'] = null;
                    $success['message']     = 'Email sudah digunakan';

                    return response()->json($success, $this->successStatus);
                }

            } else {

                $user = new User();
                $user->username = $request->username;
                $user->email = $request->email;
                $user->password = Hash::make($request->password);
                $user->role = 'konsumen';
                $user->status = 1;
                $user->save();

                if($user) {
                    $this->successStatus    = 200;
                    $success['success']     = true;

                    return response()->json($success, $this->successStatus);
                } else {
                    // $this->successStatus    = 200;
                    $success['success']     = false;
                    $success['message']     = 'Gagal menyimpan data';

                    return response()->json($success, $this->successStatus);
                }

            }

        } else {

            $success['success']     = false;
            $success['token']       = null;
            $success['user_detail'] = null;
            $success['message']     = 'Password tidak sama dengan konfirmasi';

            return response()->json($success, $this->successStatus);

        }
    }

    public function DaftarGapoktan(Request $request) {

        $cekEmailUser = User::where('email', $request->email)->first();
        $cekNamaUser = User::where('username', $request->nama)->first();

        if($cekEmailUser) {

            $this->successStatus    = 200;
            $success['success']     = true;
            $success['message']     = "Email telah terdaftar";

            return response()->json($success, $this->successStatus);

        } else if($cekNamaUser) {

            $this->successStatus    = 200;
            $success['success']     = true;
            $success['message']     = "Username telah terdaftar";

            return response()->json($success, $this->successStatus);

        } else {

            $gapoktan = new User();
            $gapoktan->username = $request->nama;
            $gapoktan->email = $request->email;
            $gapoktan->password = Hash::make($request->password);
            $gapoktan->role = 'gapoktan';
            $gapoktan->status = 0;
            $gapoktan->save();

            $profile = new Profile();
            $profile->role = 'gapoktan';
            $profile->user_id = $gapoktan->id;
            $profile->nama = $gapoktan->username;
            $profile->kontak = $request->kontak;
            $profile->alamat = $request->alamat;
            $profile->save();

            dispatch(new SendEmailRegisterGapoktanJob($gapoktan->email,$gapoktan));

            $this->successStatus    = 200;
            $success['success']     = true;

            return response()->json($success, $this->successStatus);

        }

    }

    public function konfirmasiLink($encryptemail) {

        $data = Crypt::decryptString($encryptemail);

        $user = User::where('role', 'gapoktan')->where('email', $data)->first();

        $user->gapoktan = $user->id;
        $user->status = 1;
        $user->save();

        return view('konfirmasiSuccess');

    }
}
