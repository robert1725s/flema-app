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
                'name' => 'admin',
                'email' => 'admin@hoge.com',
                'password' => Hash::make('12345678'),
                'filename' => 'admin_profile.jpg',
                'postal_code' => '222-2222',
                'address' => '東京都渋谷区2-2-2',
                'building' => 'タワマン',
            ],
            [
                'name' => 'test',
                'email' => 'test@hoge.com',
                'password' => Hash::make('12345678'),
                'postal_code' => null,
                'address' => null,
                'building' => null,
            ]
        ];

        $users = [];

        foreach ($usersData as $index => $userData) {
            $imagePath = null;

            // adminユーザーのみ画像を設定
            if ($userData['email'] === 'admin@hoge.com') {
                // Picsumから画像をダウンロード
                $picsumUrl = "https://picsum.photos/150/150?random=" . ($index + 1);
                $response = Http::timeout(5)->get($picsumUrl);

                if ($response->successful()) {
                    // storage/app/public/users に保存
                    $imagePath = 'users/' . $userData['filename'];
                    Storage::disk('public')->put($imagePath, $response->body());
                }
            }

            // データベース用のユーザーデータを準備
            $userData = [
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => $userData['password'],
                'image_path' => $imagePath,
                'email_verified_at' => null,
                'postal_code' => $userData['postal_code'],
                'address' => $userData['address'],
                'building' => $userData['building'],
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // adminユーザーのみメール認証済みにする
            if ($userData['email'] === 'admin@hoge.com') {
                $userData['email_verified_at'] = now();
            }

            $users[] = $userData;
        }

        DB::table('users')->insert($users);
    }
}
