<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('users')->delete();
        
        \DB::table('users')->insert(array (
            0 => 
            array (
                'created_at' => NULL,
                'email' => 'petani@cabai.com',
                'email_verified_at' => NULL,
                'id' => 1,
                'password' => '$2y$10$SDVYGxj4IJ7ZSdDRxb7L0uu1oODxLzZK.xI3Eexun0LARMGb706OO',
                'remember_token' => NULL,
                'role' => 'petani',
                'status' => '1',
                'updated_at' => NULL,
                'username' => 'petani',
            ),
            1 => 
            array (
                'created_at' => NULL,
                'email' => 'gapoktan@cabai.com',
                'email_verified_at' => NULL,
                'id' => 2,
                'password' => '$2y$10$SDVYGxj4IJ7ZSdDRxb7L0uu1oODxLzZK.xI3Eexun0LARMGb706OO',
                'remember_token' => NULL,
                'role' => 'gapoktan',
                'status' => '1',
                'updated_at' => NULL,
                'username' => 'gapoktan',
            ),
            2 => 
            array (
                'created_at' => NULL,
                'email' => 'konsumen@cabai.com',
                'email_verified_at' => NULL,
                'id' => 3,
                'password' => '$2y$10$SDVYGxj4IJ7ZSdDRxb7L0uu1oODxLzZK.xI3Eexun0LARMGb706OO',
                'remember_token' => NULL,
                'role' => 'konsumen',
                'status' => '1',
                'updated_at' => NULL,
                'username' => 'konsumen',
            ),
            3 => 
            array (
                'created_at' => '2022-02-01 16:07:44',
                'email' => 'parlan@petanicabai.com',
                'email_verified_at' => NULL,
                'id' => 4,
                'password' => '$2y$10$fMuOuVYvOc/LO91rc.3vvOqPCk6IVqbb1O0loaxWxA/lzorRbi9Oy',
                'remember_token' => NULL,
                'role' => 'petani',
                'status' => '1',
                'updated_at' => '2022-02-01 16:07:44',
                'username' => 'parlan',
            ),
            4 => 
            array (
                'created_at' => '2022-02-01 16:21:00',
                'email' => 'sudi@petanicabai.com',
                'email_verified_at' => NULL,
                'id' => 5,
                'password' => '$2y$10$YcSpZzZWvuT3.SCiXlH04OVgr.auuuGLEZmyxiWgOur0yv5q6k.lq',
                'remember_token' => NULL,
                'role' => 'petani',
                'status' => '1',
                'updated_at' => '2022-02-01 16:21:00',
                'username' => 'Sudi',
            ),
            5 => 
            array (
                'created_at' => '2022-02-01 16:22:21',
                'email' => 'diono@petanicabai.com',
                'email_verified_at' => NULL,
                'id' => 6,
                'password' => '$2y$10$wGzX13nvtctqvE1QtqhSK.TnGd9ZHJA6/V3rXOCy1oqT0eSdhSxFe',
                'remember_token' => NULL,
                'role' => 'petani',
                'status' => '1',
                'updated_at' => '2022-02-01 16:22:21',
                'username' => 'Diono',
            ),
            6 => 
            array (
                'created_at' => '2022-02-01 16:23:16',
                'email' => 'warmono@petanicabai.com',
                'email_verified_at' => NULL,
                'id' => 7,
                'password' => '$2y$10$ORN8KkHjJqvt64urSYoXqeuUNyG4MDZQvKb7CGinDFd/.tdSqq5z2',
                'remember_token' => NULL,
                'role' => 'petani',
                'status' => '1',
                'updated_at' => '2022-02-01 16:23:16',
                'username' => 'Warmono',
            ),
            7 => 
            array (
                'created_at' => '2022-02-01 16:24:39',
                'email' => 'sumarno@petanicabai.com',
                'email_verified_at' => NULL,
                'id' => 8,
                'password' => '$2y$10$Z0IzeV71aaIC1jTSFVwQT.1P3LhaxA6ymDonWYKnUz/Tgop8CHPjO',
                'remember_token' => NULL,
                'role' => 'petani',
                'status' => '1',
                'updated_at' => '2022-02-01 16:24:39',
                'username' => 'Sumarno',
            ),
            8 => 
            array (
                'created_at' => '2022-02-01 16:25:37',
                'email' => 'imamsuparyono@petanicabai.com',
                'email_verified_at' => NULL,
                'id' => 9,
                'password' => '$2y$10$KLXrR.QwPYHqz7BsoB.3KelALHtUAVEZclxbWF9Gg1vPioMNnDKxO',
                'remember_token' => NULL,
                'role' => 'petani',
                'status' => '1',
                'updated_at' => '2022-02-01 16:25:37',
                'username' => 'Imam Suparyono',
            ),
            9 => 
            array (
                'created_at' => '2022-02-01 16:26:22',
                'email' => 'budisuyadi@petanicabai.com',
                'email_verified_at' => NULL,
                'id' => 10,
                'password' => '$2y$10$au.NGLzIgERrqdckO6wlNOMB6KHWQccA/tQ1iAYjYkunkGuJb22NK',
                'remember_token' => NULL,
                'role' => 'petani',
                'status' => '1',
                'updated_at' => '2022-02-01 16:26:22',
                'username' => 'Budi Suyadi',
            ),
            10 => 
            array (
                'created_at' => '2022-02-01 16:28:32',
                'email' => 'budisantoso@petanicabai.com',
                'email_verified_at' => NULL,
                'id' => 11,
                'password' => '$2y$10$1IjXPdoIpCBnp48ZtUEu.uiGR4Jr/igk77InGCDJZemlDqX6EFapq',
                'remember_token' => NULL,
                'role' => 'petani',
                'status' => '1',
                'updated_at' => '2022-02-01 16:28:32',
                'username' => 'Budi Santoso',
            ),
            11 => 
            array (
                'created_at' => '2022-02-01 16:29:13',
                'email' => 'sutopo@petanicabai.com',
                'email_verified_at' => NULL,
                'id' => 12,
                'password' => '$2y$10$3KC1yULPot5xxc.HPftCVeunE/G7qrCsktAc4PuEEZLuk1.QU.DBm',
                'remember_token' => NULL,
                'role' => 'petani',
                'status' => '1',
                'updated_at' => '2022-02-01 16:29:13',
                'username' => 'Sutopo',
            ),
        ));
        
        
    }
}