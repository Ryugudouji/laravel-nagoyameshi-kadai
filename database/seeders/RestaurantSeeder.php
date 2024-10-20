<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Restaurant;

class RestaurantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // RestaurantFactoryクラスで定義した内容にもとづいてダミーデータを5つ生成し、
        // restaurantテーブルに追加する
        Restaurant::factory()->count(6)->create();
    }
}
