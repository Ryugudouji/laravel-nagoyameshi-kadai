<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RestaurantTest extends TestCase
{

    use RefreshDatabase;
    /**
     * A basic feature test example.
     */

    //  未ログインのユーザーは会員側の店舗一覧ページにアクセスできる
    public function test_guest_can_access_restaurant_index()
    {

        $response = $this->get(route('restaurants.index'));

        $response->assertStatus(200);
    }


    // ログイン済みの一般ユーザーは会員側の店舗一覧ページにアクセスできる
    public function test_regular_user_can_access_restaurant_index()
    {
        // 一般ユーザーを作成
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('restaurants.index'));

        $response->assertStatus(200);
    }


    // ログイン済みの管理者は会員側の店舗一覧ページにアクセスできない
    public function test_admin_cannot_access_user_index()
    {
        // テストに必要なデータの準備
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $response = $this->actingAs($admin, 'admin')->get(route('restaurants.index'));

        $response->assertRedirect(route('admin.home'));
    }


    // show
    // 未ログインのユーザーは会員側の店舗詳細ページにアクセスできる
    public function test_guest_can_access_restaurant_show()
    {
        $restaurant = Restaurant::factory()->create();

        $response = $this->get(route('restaurants.show', $restaurant -> id));

        $response->assertStatus(200);
    }

    // ログイン済みの一般ユーザーは会員側の店舗詳細ページにアクセスできる
    public function test_regular_user_can_access_restaurant_show()
    {

        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($user)->get(route('restaurants.show', $restaurant -> id));

        $response->assertStatus(200);
    }

    // ログイン済みの管理者は会員側の店舗詳細ページにアクセスできない
    public function test_admin_cannot_access_user_show()
    {
        // テストに必要なデータの準備
        $restaurant = Restaurant::factory()->create();

        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $response = $this->actingAs($admin, 'admin')->get(route('restaurants.show', $restaurant -> id));

        $response->assertRedirect(route('admin.home'));
    }
}
