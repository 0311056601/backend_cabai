<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class LahanTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('lahan')->delete();
        
        \DB::table('lahan')->insert(array (
            0 => 
            array (
                'alamat_lahan' => 'Bekasi Utara',
                'created_at' => '2022-02-10 06:30:34',
                'id' => 3,
                'latitude' => '-6.218471',
                'longitude' => '106.990606',
                'luas_lahan' => '160',
                'nama_lahan' => 'lahan nurjamin & nurlela',
                'status_kepemilikan' => 'Milik Sendiri',
                'updated_at' => '2022-02-10 08:05:55',
                'user_id' => 1,
            ),
            1 => 
            array (
                'alamat_lahan' => 'jalan bekasi no 9',
                'created_at' => '2022-02-15 13:33:49',
                'id' => 4,
                'latitude' => '-6.5522067',
                'longitude' => '106.7305796',
                'luas_lahan' => '100',
                'nama_lahan' => 'lahan nurjamin',
                'status_kepemilikan' => 'Milik Sendiri',
                'updated_at' => '2022-02-15 13:33:49',
                'user_id' => 1,
            ),
        ));
        
        
    }
}