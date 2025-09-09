<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UsersTableSeeder::class);
        Item::factory(35)->create();
        $this->call(CategoriesTableSeeder::class);
        $this->call(FavoritesTableSeeder::class);
    }
}
