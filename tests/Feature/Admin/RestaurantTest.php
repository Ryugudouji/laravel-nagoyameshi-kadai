<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RestaurantTest extends TestCase
{
    use RefreshDatabase;

    // indexアクション（店舗一覧ページ）
    // 未ログインのユーザーは管理者側の店舗一覧ページにアクセスできない
    public function test_guest_can_not_access_admin_restaurants_index()
    {
        $response = $this->get(route('admin.restaurants.index'));

        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの一般ユーザーは管理者側の店舗一覧ページにアクセスできない
    public function test_regular_user_can_not_access_admin_restaurants_index()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.restaurants.index'));

        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの管理者は管理者側の店舗一覧ページにアクセスできる
    public function test_admin_can_access_admin_restaurants_index()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.restaurants.index'));

        $response->assertStatus(200);
    }

    // showアクション（店舗詳細ページ）
    // 未ログインのユーザーは管理者側の店舗詳細ページにアクセスできない
    public function test_guest_can_not_access_admin_restaurants_show()
    {
        $restaurant = Restaurant::factory()->create();

        $response = $this->get(route('admin.restaurants.show', $restaurant));

        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの一般ユーザーは管理者側の店舗詳細ページにアクセスできない
    public function test_regular_user_can_not_access_admin_restaurants_show()
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.restaurants.show', $restaurant));

        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの管理者は管理者側の店舗詳細ページにアクセスできる
    public function test_admin_can_access_admin_restaurants_show()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.restaurants.show', $restaurant));

        $response->assertStatus(200);
    }


    // createアクション（店舗登録ページ）
    // 未ログインのユーザーは管理者側の店舗登録ページにアクセスできない
    public function test_guest_can_not_access_admin_restaurants_create()
    {
        $response = $this->get(route('admin.restaurants.create'));

        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの一般ユーザーは管理者側の店舗登録ページにアクセスできない
    public function test_regular_user_can_not_access_admin_restaurants_create()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.restaurants.create'));

        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの管理者は管理者側の店舗登録ページにアクセスできる
    public function test_admin_can_access_admin_restaurants_create()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.restaurants.create'));

        $response->assertStatus(200);
    }


    // storeアクション（店舗登録機能）
    // 未ログインのユーザーは店舗を登録できない
    public function test_guest_can_not_access_admin_restaurants_store()
    {
        $restaurantData = Restaurant::factory()->make()->toArray();

        $response = $this->post(route('admin.restaurants.store'), $restaurantData);

        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの一般ユーザーは店舗を登録できない
    public function test_regular_user_can_not_access_admin_restaurants_store()
    {
        $user = User::factory()->create();
        $restaurantData = Restaurant::factory()->make()->toArray();

        $response = $this->actingAs($user)->post(route('admin.restaurants.store', $restaurantData));

        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの管理者は店舗を登録できる
    public function test_admin_can_access_admin_restaurants_store()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $categories = Category::factory()->count(3)->create();
        $category_Ids = $categories->pluck('id')->toArray();
        $restaurantData = Restaurant::factory()->make()->toArray();
        $restaurantData['category_ids'] = $category_Ids;

        $response = $this->actingAs($admin, 'admin')->post(route('admin.restaurants.store', $restaurantData));

        $response->assertRedirect(route('admin.restaurants.index'));
    }


    // editアクション（店舗編集ページ）
    // 未ログインのユーザーは管理者側の店舗編集ページにアクセスできない
    public function test_guest_can_not_access_admin_restaurants_edit()
    {
        $restaurant = Restaurant::factory()->create();

        $response = $this->get(route('admin.restaurants.edit', $restaurant));

        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの一般ユーザーは管理者側の店舗編集ページにアクセスできない
    public function test_regular_user_can_not_access_admin_restaurants_edit()
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.restaurants.edit', $restaurant));

        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの管理者は管理者側の店舗編集ページにアクセスできる
    public function test_admin_can_access_admin_restaurants_edit()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.restaurants.edit', $restaurant));

        $response->assertStatus(200);
    }


    // updateアクション（店舗更新機能）
    // 未ログインのユーザーは店舗を更新できない
    public function test_guest_can_not_access_admin_restaurants_update()
    {
        $restaurant = Restaurant::factory()->create();
        $restaurantData =[
            'name' => 'アップデートテストname',
            'description' => 'アップデートテストdescription',
        ];

        $response = $this->put(route('admin.restaurants.update', $restaurant));

        $response->assertRedirect(route('admin.login'));
    }

    // PUT /admin/restaurants/{id}
    // ログイン済みの一般ユーザーは店舗を更新できない
    public function test_regular_user_can_not_access_admin_restaurants_update()
    {
        // テストに必要なデータの準備
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();

        $restaurantData = [
            'name' => 'アップデートテストname',
            'description' => 'アップデートテストdescription',
        ];

        // 検証したい処理の実行
        $response = $this->actingAs($user)->put(route('admin.restaurants.update', $restaurant), $restaurantData);

        // 処理した結果の検証
        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの管理者は店舗を更新できる
    public function test_admin_can_access_admin_restaurants_update()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        // 既存データとしてレストランデータにカテゴリを3つ紐づけて作成しておく
        $oldRestaurant = Restaurant::factory()->hasAttached(Category::factory()->count(3))->create([
            'name' => '旧name',
            'description' => '旧description',
        ]);

        // 更新用のカテゴリデータを別途作成する
        $categories = Category::factory()->count(3)->create();

        $newRestaurantData = [
            'name' => 'アップデートテストname',
            'description' => 'アップデートテストdescription',
            'lowest_price' => $oldRestaurant->lowest_price,
            'highest_price' => $oldRestaurant->highest_price,
            'postal_code' => $oldRestaurant->postal_code,
            'address' => $oldRestaurant->address,
            'opening_time' => $oldRestaurant->opening_time,
            'closing_time' => $oldRestaurant->closing_time,
            'seating_capacity' => $oldRestaurant->seating_capacity,
            'category_ids' => $categories->pluck('id')->toArray(),//更新用のカテゴリのIDをセットする
        ];

        $response = $this->actingAs($admin, 'admin')->put(route('admin.restaurants.update', $oldRestaurant), $newRestaurantData);

        $response->assertRedirect(route('admin.restaurants.show', $oldRestaurant));

        // 新しいデータが正しくデータベースに保存されているか確認
        $this->assertDatabaseHas('restaurants', [
            'id' => $oldRestaurant->id,
            'name' => 'アップデートテストname',
            'description' => 'アップデートテストdescription',
        ]);

        // 旧データが正しく変更されたことを確認
        $this->assertDatabaseMissing('restaurants', [
            'id' => $oldRestaurant->id,
            'name' => '旧name',
            'description' => '旧description',
        ]);
    }


    // destroyアクション（店舗削除機能）
    // 未ログインのユーザーは店舗を削除できない
    public function test_guest_can_not_access_admin_restaurants_destroy()
    {
        $restaurant = Restaurant::factory()->create();

        $response = $this->delete(route('admin.restaurants.destroy', $restaurant));

        $response->assertRedirect(route('admin.login'));

        // データベースに店舗がまだ存在することを確認
        $this->assertDatabaseHas('restaurants', [
            'id' => $restaurant->id,
        ]);
    }

    // ログイン済みの一般ユーザーは店舗を削除できない
    public function test_regular_user_can_not_access_admin_restaurants_destroy()
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($user)->delete(route('admin.restaurants.destroy', $restaurant));

        $response->assertRedirect(route('admin.login'));

        // データベースに店舗がまだ存在することを確認
        $this->assertDatabaseHas('restaurants', [
            'id' => $restaurant->id,
        ]);
    }

    // ログイン済みの管理者は店舗を削除できる
    public function test_admin_can_access_admin_restaurants_destroy()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($admin, 'admin')->delete(route('admin.restaurants.destroy', $restaurant));

        // データベースから店舗が削除されたことを確認
        $this->assertDatabaseMissing('restaurants', [
            'id' => $restaurant->id,
        ]);

        $response->assertRedirect(route('admin.restaurants.index'));
    }


}
