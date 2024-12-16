<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    // indexアクション（お気に入り一覧ページ）
    // 未ログインのユーザーは会員側のお気に入り一覧ページにアクセスできない
    public function test_guest_cannot_access_favorite_index()
    {
        $response = $this->get(route('favorites.index'));

        $response->assertRedirect(route('login'));
    }

    // ログイン済みの無料会員は会員側のお気に入り一覧ページにアクセスできない
    public function test_regular_user_cannot_access_favorite_index()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('favorites.index'));

        $response->assertRedirect(route('subscription.create'));
    }

    // ログイン済みの有料会員は会員側のお気に入り一覧ページにアクセスできる
    public function test_premium_user_can_access_favorite_index()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QTekHP1x9xomPwVGawxXAOm')->create('pm_card_visa'); // 有料プランを設定
        $this->actingAs($user);

        $this->assertTrue($user->subscribed('premium_plan'));

        $response = $this->get(route('favorites.index'));
        $response->assertStatus(200);
    }

    // ログイン済みの管理者は会員側のお気に入り一覧ページにアクセスできない
    public function test_admin_cannot_access_favorite_index()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $this->actingAs($admin, 'admin');

        $response = $this->get(route('favorites.index'));

        $response->assertRedirect(route('admin.home'));
    }

    // storeアクション（お気に入り追加機能）
    // 未ログインのユーザーはお気に入りに追加できない
    public function test_guest_cannot_add_favorite()
    {
        $restaurant = Restaurant::factory()->create();

        $response = $this->post(route('favorites.store', $restaurant->id));

        $response->assertRedirect(route('login'));
    }

    // ログイン済みの無料会員はお気に入りに追加できない
    public function test_regular_user_cannot_add_favorite()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $restaurant = Restaurant::factory()->create();

        $response = $this->post(route('favorites.store', $restaurant->id));

        $response->assertRedirect(route('subscription.create'));
    }

    // ログイン済みの有料会員はお気に入りに追加できる
    public function test_premium_user_can_add_favorite()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QTekHP1x9xomPwVGawxXAOm')->create('pm_card_visa'); // 有料プランを設定
        $this->actingAs($user);

        $this->assertTrue($user->subscribed('premium_plan'));

        $restaurant = Restaurant::factory()->create();

        $response = $this->post(route('favorites.store', $restaurant->id));

        $response->assertStatus(302);
        $response->assertSessionHas('flash_message', 'お気に入りに追加しました。');
    }

    // ログイン済みの管理者はお気に入りに追加できない
    public function test_admin_cannot_add_favorite()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $this->actingAs($admin, 'admin');

        $restaurant = Restaurant::factory()->create();
        $response = $this->post(route('favorites.store', $restaurant->id));

        $response->assertRedirect(route('admin.home'));
    }


    // destroyアクション（お気に入り解除機能）
    // 未ログインのユーザーはお気に入りを解除できない
    public function test_guest_cannot_remove_favorite()
    {
        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create();

        $user->favorite_restaurants()->attach($restaurant->id);

        $response = $this->delete(route('favorites.destroy', ['restaurant_id' => $restaurant->id]));

        $response->assertRedirect(route('login'));
    }

    // ログイン済みの無料会員はお気に入りを解除できない
    public function test_regular_user_cannot_remove_favorite()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $restaurant = Restaurant::factory()->create();

        $user->favorite_restaurants()->attach($restaurant->id);

        $response = $this->delete(route('favorites.destroy', ['restaurant_id' => $restaurant->id]));

        $response->assertRedirect(route('subscription.create'));
    }

    // ログイン済みの有料会員はお気に入りを解除できる
    public function test_premium_user_can_remove_favorite()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QTekHP1x9xomPwVGawxXAOm')->create('pm_card_visa');
        $this->actingAs($user);

        $restaurant = Restaurant::factory()->create();
        $user->favorite_restaurants()->attach($restaurant->id);

        $response = $this->delete(route('favorites.destroy', $restaurant->id));

        $response->assertStatus(302);
    }

    // ログイン済みの管理者はお気に入りを解除できない
    public function test_admin_cannot_remove_favorite()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $this->actingAs($admin, 'admin');

        $restaurant = Restaurant::factory()->create();

        $response = $this->delete(route('favorites.destroy', $restaurant->id));

        $response->assertRedirect(route('admin.home'));
    }
}
