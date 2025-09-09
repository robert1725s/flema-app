<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $param = [
            'name' => 'kaede',
            'email' => 'esfe@gmail.coe',
            'password' => Hash::make('12345678'),
        ];
        DB::table('users')->insert($param);
        $param = [
            'name' => 'koki',
            'email' => 'admin@hoge.com',
            'password' => Hash::make('12345678'),
        ];
        DB::table('users')->insert($param);
        $param = [
            'name' => 'PONI',
            'email' => 'poni@hoge.com',
            'password' => Hash::make('12345678'),
        ];
        DB::table('users')->insert($param);
        $param = [
            'name' => 'あきら',
            'email' => 'akira@hoge.com',
            'password' => Hash::make('12345678'),
        ];
        DB::table('users')->insert($param);
    }
}
