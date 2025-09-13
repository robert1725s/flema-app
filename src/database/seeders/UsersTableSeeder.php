<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $usersData = [
            [
                'name' => 'kaede',
                'email' => 'esfe@gmail.coe',
                'password' => Hash::make('12345678'),
                'filename' => 'kaede_profile.jpg',
            ],
            [
                'name' => 'koki',
                'email' => 'admin@hoge.com',
                'password' => Hash::make('12345678'),
                'filename' => 'koki_profile.jpg',
            ],
            [
                'name' => 'PONI',
                'email' => 'poni@hoge.com',
                'password' => Hash::make('12345678'),
                'filename' => 'poni_profile.jpg',
            ],
            [
                'name' => 'あきら',
                'email' => 'akira@hoge.com',
                'password' => Hash::make('12345678'),
                'filename' => 'akira_profile.jpg',
            ],
        ];

        $users = [];

        foreach ($usersData as $index => $userData) {

            // Picsumから画像をダウンロード（各ユーザーに異なる画像）
            $picsumUrl = "https://picsum.photos/150/150?random=" . ($index + 1);
            $response = Http::timeout(10)->get($picsumUrl);

            if ($response->successful()) {
                // storage/app/public/users に保存
                $imagePath = 'users/' . $userData['filename'];
                Storage::disk('public')->put($imagePath, $response->body());

                // データベース用のユーザーデータを準備
                $users[] = [
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'password' => $userData['password'],
                    'image_path' => $imagePath,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('users')->insert($users);
    }
}
