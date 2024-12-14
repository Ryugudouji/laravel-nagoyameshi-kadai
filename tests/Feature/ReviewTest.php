<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Restaurant;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;


    // indexアクション（レビュー一覧ページ）
    // 未ログインのユーザーは会員側のレビュー一覧ページにアクセスできない
    public function test_guest_cannot_access_review_index()
    {
        $restaurant = Restaurant::factory()->create();

        $response = $this->get(route('restaurants.reviews.index', $restaurant));

        $response->assertRedirect(route('login'));
    }

    // ログイン済みの無料会員は会員側のレビュー一覧ページにアクセスできる
    public function test_regular_user_can_access_review_index()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $restaurant = Restaurant::factory()->create();

        $response = $this->get(route('restaurants.reviews.index', $restaurant));
        $response->assertRedirect(route('subscription.create'));
    }

    // ログイン済みの有料会員は会員側のレビュー一覧ページにアクセスできる
    public function test_premium_user_can_access_review_index()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QTekHP1x9xomPwVGawxXAOm')->create('pm_card_visa');
        $this->actingAs($user);

        $this->assertTrue($user->subscribed('premium_plan'));

        $restaurant = Restaurant::factory()->create();
        $response = $this->get(route('restaurants.reviews.index', $restaurant));
        $response->assertStatus(200);
    }

    // ログイン済みの管理者は会員側のレビュー一覧ページにアクセスできない
    public function test_admin_cannot_access_review_index()
    {
        // テストに必要なデータの準備
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $this->actingAs($admin, 'admin');

        // レビュー一覧ページにアクセス
        $restaurant = Restaurant::factory()->create();
        $response = $this->get(route('restaurants.reviews.index', $restaurant));

        $response->assertRedirect(route('admin.home'));
    }

    // createアクション（レビュー投稿ページ）
    // 未ログインのユーザーは会員側のレビュー投稿ページにアクセスできない
    public function test_guest_cannot_access_review_create()
    {
        $restaurant = Restaurant::factory()->create();

        $response = $this->get(route('restaurants.reviews.create', $restaurant));

        $response->assertRedirect(route('login'));
    }

    // ログイン済みの無料会員は会員側のレビュー投稿ページにアクセスできない
    public function test_regular_user_cannot_access_review_create()
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('restaurants.reviews.create', $restaurant));

        $response->assertRedirect(route('subscription.create'));
    }

    // ログイン済みの有料会員は会員側のレビュー投稿ページにアクセスできる
    public function test_premium_user_can_access_review_create()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QTekHP1x9xomPwVGawxXAOm')->create('pm_card_visa');
        $this->actingAs($user);

        $this->assertTrue($user->subscribed('premium_plan'));

        $restaurant = Restaurant::factory()->create();
        $response = $this->get(route('restaurants.reviews.create', $restaurant));
        $response->assertStatus(200);
    }

    // ログイン済みの管理者は会員側のレビュー投稿ページにアクセスできない
    public function test_admin_cannot_access_review_create()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $this->actingAs($admin, 'admin');

        $restaurant = Restaurant::factory()->create();
        $response = $this->get(route('restaurants.reviews.create', $restaurant));

        $response->assertRedirect(route('admin.home'));
    }


    // storeアクション（レビュー投稿機能）
    // 未ログインのユーザーはレビューを投稿できない
    public function test_guest_cannot_post_review()
    {
        $restaurant = Restaurant::factory()->create();

        // 未ログインでレビュー投稿を試みる
        $response = $this->post(route('restaurants.reviews.store', $restaurant), [
            'score' => 5,
            'content' => 'テストのレビューコメント'
        ]);

        $response->assertRedirect(route('login'));
    }

    // ログイン済みの無料会員はレビューを投稿できない
    public function test_regular_user_cannot_post_review()
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        $this->actingAs($user);

        // 未ログインでレビュー投稿を試みる
        $response = $this->post(route('restaurants.reviews.store', $restaurant), [
            'score' => 5,
            'content' => 'テストのレビューコメント'
        ]);

        $response->assertRedirect(route('subscription.create'));
    }

    // ログイン済みの有料会員はレビューを投稿できる
    public function test_premium_user_can_post_review()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QTekHP1x9xomPwVGawxXAOm')->create('pm_card_visa');
        $this->actingAs($user);

        $this->assertTrue($user->subscribed('premium_plan'));

        $restaurant = Restaurant::factory()->create();

        $response = $this->post(route('restaurants.reviews.store', $restaurant), [
            'score' => 5,
            'content' => 'テストのレビューコメント'
        ]);

        $response->assertRedirect(route('restaurants.reviews.index', $restaurant));
    }

    // ログイン済みの管理者はレビューを投稿できない
    public function test_admin_cannot_post_review()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $this->actingAs($admin, 'admin');

        $restaurant = Restaurant::factory()->create();

        // 管理者がレビュー投稿を試みる
        $response = $this->post(route('restaurants.reviews.store', $restaurant), [
            'score' => 5,
            'content' => 'テストのレビューコメント'
        ]);

        $response->assertRedirect(route('admin.home'));
    }


    // editアクション（レビュー編集ページ）
    // 未ログインのユーザーは会員側のレビュー編集ページにアクセスできない
    public function test_guest_cannot_access_review_edit()
    {
        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create();
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);

        $response = $this->get(route('restaurants.reviews.edit', [$restaurant, $review]));

        $response->assertRedirect(route('login'));
    }

    // ログイン済みの無料会員はレビュー編集ページにアクセスできない
    public function test_regular_user_cannot_access_review_edit()
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        $this->actingAs($user);

        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);

        $response = $this->get(route('restaurants.reviews.edit', [$restaurant, $review]));

        $response->assertRedirect(route('subscription.create'));
    }

    // ログイン済みの有料会員は会員側の他人のレビュー編集ページにアクセスできない
    public function test_premium_user_cannot_access_review_edit()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QTekHP1x9xomPwVGawxXAOm')->create('pm_card_visa');
        $this->actingAs($user);

        $restaurant = Restaurant::factory()->create();
        $otherUser = User::factory()->create(); // 他のユーザー
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $otherUser->id
        ]);

        // 他人のレビュー編集ページにはアクセスできない
        $response = $this->get(route('restaurants.reviews.edit', [$restaurant, $review]));
        $response->assertRedirect(route('restaurants.reviews.index', $restaurant));
    }

    // ログイン済みの有料会員は会員側の自身のレビュー編集ページにアクセスできる
    public function test_premium_user_can_access_own_review_edit()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QTekHP1x9xomPwVGawxXAOm')->create('pm_card_visa');
        $this->actingAs($user);

        $restaurant = Restaurant::factory()->create();
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);

        // 自身のレビュー編集ページにはアクセスできる
        $response = $this->get(route('restaurants.reviews.edit', [$restaurant, $review]));
        $response->assertStatus(200);
    }

    // ログイン済みの管理者は会員側のレビュー編集ページにアクセスできない
    public function test_admin_cannot_access_review_edit()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $this->actingAs($admin, 'admin');

        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create(); // 他のユーザー
        $review = Review::factory()->create([
        'restaurant_id' => $restaurant->id,
        'user_id' => $user->id
        ]);

        // 管理者はレビュー編集ページにアクセスできない
        $response = $this->get(route('restaurants.reviews.edit', [$restaurant, $review]));
        $response->assertRedirect(route('admin.home'));
    }


    // updateアクション（レビュー更新機能）
    // 未ログインのユーザーはレビューを更新できない
    public function test_guest_cannot_update_review()
    {
        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create();
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);

        // ログインしていない状態でレビューを更新しようとすると、ログインページにリダイレクトされる
        $response = $this->put(route('restaurants.reviews.update', [$restaurant, $review]), [
            'score' => 5,
            'content' => 'テストのレビューコメント'
        ]);
        $response->assertRedirect(route('login'));
    }

    // ログイン済みの無料会員はレビューを更新できない
    public function test_regular_user_cannot_update_review()
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        $this->actingAs($user);

        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);

        $response = $this->put(route('restaurants.reviews.update', [$restaurant, $review]), [
            'score' => 5,
            'content' => 'テストのレビューコメント'
        ]);
        $response->assertRedirect(route('subscription.create'));
    }

    // ログイン済みの有料会員は他人のレビューを更新できない
    public function test_premium_user_cannot_update_review()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QTekHP1x9xomPwVGawxXAOm')->create('pm_card_visa');
        $this->actingAs($user);

        $restaurant = Restaurant::factory()->create();
        $otherUser = User::factory()->create(); // 他のユーザー
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $otherUser->id
        ]);

        // 他人のレビューを更新できない
        $response = $this->put(route('restaurants.reviews.update', [$restaurant, $review]), [
            'score' => 5,
            'content' => 'テストのレビューコメント'
        ]);
        $response->assertRedirect(route('restaurants.reviews.index', $restaurant));
    }

    // ログイン済みの有料会員は自身のレビューを更新できる
    public function test_premium_user_can_update_own_review()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QTekHP1x9xomPwVGawxXAOm')->create('pm_card_visa');
        $this->actingAs($user);

        $restaurant = Restaurant::factory()->create();
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);

        // 自身のレビューを更新できる
        $response = $this->put(route('restaurants.reviews.update', [$restaurant, $review]), [
            'score' => 5,
            'content' => 'テストのレビューコメント'
        ]);

        $response->assertRedirect(route('restaurants.reviews.index', $restaurant));
    }

    // ログイン済みの管理者はレビューを更新できない
    public function test_admin_cannot_update_review()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $this->actingAs($admin, 'admin');

        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create(); // 他のユーザー
        $review = Review::factory()->create([
        'restaurant_id' => $restaurant->id,
        'user_id' => $user->id
        ]);

        $response = $this->put(route('restaurants.reviews.update', [$restaurant, $review]), [
            'score' => 5,
            'content' => 'テストのレビューコメント'
        ]);
        $response->assertRedirect(route('admin.home'));
    }

    // destroyアクション（レビュー削除機能）
    // 未ログインのユーザーはレビューを削除できない
    public function test_guest_cannot_delete_review()
    {
        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create();
        $review = Review::factory()->create([
        'restaurant_id' => $restaurant->id,
        'user_id' => $user->id, // ユーザーを指定
    ]);

        // 未ログインユーザーがレビューを削除しようとする
        $response = $this->delete(route('restaurants.reviews.destroy', [$restaurant, $review]));

        // ログインページにリダイレクトされることを確認
        $response->assertRedirect(route('login'));
    }

    // ログイン済みの無料会員はレビューを削除できない
    public function test_regular_user_cannot_delete_review()
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        $this->actingAs($user);

        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);

        $response = $this->delete(route('restaurants.reviews.destroy', [$restaurant, $review]));

        $response->assertRedirect(route('subscription.create'));
    }

    // ログイン済みの有料会員は他人のレビューを削除できない
    public function test_premium_user_cannot_delete_review()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QTekHP1x9xomPwVGawxXAOm')->create('pm_card_visa');
        $this->actingAs($user);

        $restaurant = Restaurant::factory()->create();
        $otherUser = User::factory()->create(); // 他のユーザー
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $otherUser->id
        ]);

        // 有料会員が他人のレビューを削除しようとする
        $response = $this->delete(route('restaurants.reviews.destroy', [$restaurant, $review]));

        $response->assertRedirect(route('restaurants.reviews.index', $restaurant));
    }

    // ログイン済みの有料会員は自身のレビューを削除できる
    public function test_premium_user_can_delete_own_review()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QTekHP1x9xomPwVGawxXAOm')->create('pm_card_visa');
        $this->actingAs($user);

        $restaurant = Restaurant::factory()->create();
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);

        // 有料会員が自身のレビューを削除できる
        $response = $this->delete(route('restaurants.reviews.destroy', [$restaurant, $review]));

        $response->assertRedirect(route('restaurants.reviews.index', $restaurant));
    }

    // ログイン済みの管理者はレビューを削除できない
    public function test_admin_cannot_delete_review()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $this->actingAs($admin, 'admin');

        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create(); // 他のユーザー
        $review = Review::factory()->create([
        'restaurant_id' => $restaurant->id,
        'user_id' => $user->id
        ]);

        $response = $this->delete(route('restaurants.reviews.destroy', [$restaurant, $review]));

        $response->assertRedirect(route('admin.home'));
    }
}
