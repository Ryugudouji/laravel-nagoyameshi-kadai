<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ReservationTest extends TestCase
{
    use RefreshDatabase;

    // indexアクション（予約一覧ページ）
    // 未ログインのユーザーは会員側の予約一覧ページにアクセスできない
    public function test_guest_cannot_access_reservation_index()
    {
        $response = $this->get(route('reservations.index'));

        $response->assertRedirect(route('login'));
    }

    // ログイン済みの無料会員は会員側の予約一覧ページにアクセスできない
    public function test_regular_user_cannot_access_reservation_index()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('reservations.index'));

        $response->assertRedirect(route('subscription.create'));
    }

    // ログイン済みの有料会員は会員側の予約一覧ページにアクセスできる
    public function test_premium_user_can_access_reservation_index()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QTekHP1x9xomPwVGawxXAOm')->create('pm_card_visa'); // 有料プランを設定
        $this->actingAs($user);

        $this->assertTrue($user->subscribed('premium_plan'));

        $response = $this->get(route('reservations.index'));
        $response->assertStatus(200);
    }

    // ログイン済みの管理者は会員側の予約一覧ページにアクセスできない
    public function test_admin_cannot_access_reservation_index()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $this->actingAs($admin, 'admin');

        $response = $this->get(route('reservations.index'));

        $response->assertRedirect(route('admin.home'));
    }


    // createアクション（予約ページ）
    // 未ログインのユーザーは会員側の予約ページにアクセスできない
    public function test_guest_cannot_access_reservation_create()
    {
        $restaurant = Restaurant::factory()->create();

        $response = $this->get(route('restaurants.reservations.create', $restaurant));

        $response->assertRedirect(route('login'));
    }

    // ログイン済みの無料会員は会員側の予約ページにアクセスできない
    public function test_regular_user_cannot_access_reservation_create()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $restaurant = Restaurant::factory()->create();
        $response = $this->get(route('restaurants.reservations.create', $restaurant));

        $response->assertRedirect(route('subscription.create'));
    }

    // ログイン済みの有料会員は会員側の予約ページにアクセスできる
    public function test_premium_user_can_access_reservation_create()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QTekHP1x9xomPwVGawxXAOm')->create('pm_card_visa'); // 有料プランを設定
        $this->actingAs($user);

        $this->assertTrue($user->subscribed('premium_plan'));

        $restaurant = Restaurant::factory()->create();
        $response = $this->get(route('restaurants.reservations.create', $restaurant));

        $response->assertStatus(200);
    }

    // ログイン済みの管理者は会員側の予約ページにアクセスできない
    public function test_admin_cannot_access_reservation_create()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $this->actingAs($admin, 'admin');

        $restaurant = Restaurant::factory()->create();
        $response = $this->get(route('restaurants.reservations.create', $restaurant));

        $response->assertRedirect(route('admin.home'));
    }


    // storeアクション（予約機能）
    // 未ログインのユーザーは予約できない
    public function test_guest_cannot_store_reservation()
    {
        $restaurant = Restaurant::factory()->create();

        $response = $this->post(route('restaurants.reservations.store', $restaurant), [
            'reserved_datetime' => now(),
            'number_of_people' => 2,
        ]);

        $response->assertRedirect(route('login'));
    }

    // ログイン済みの無料会員は予約できない
    public function test_regular_user_cannot_store_reservation()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $restaurant = Restaurant::factory()->create();

        $response = $this->post(route('restaurants.reservations.store', $restaurant), [
            'reserved_datetime' => now(),
            'number_of_people' => 2,
        ]);

        $response->assertRedirect(route('subscription.create'));
    }

    // ログイン済みの有料会員は予約できる
    public function test_premium_user_can_store_reservation()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $user->newSubscription('premium_plan', 'price_1QTekHP1x9xomPwVGawxXAOm')->create('pm_card_visa');

        $this->assertTrue($user->subscribed('premium_plan'));

        $restaurant = Restaurant::factory()->create();
        $now = Carbon::now();
        $response = $this->post(route('restaurants.reservations.store', ['restaurant' => $restaurant]), [
            'restaurant_id' => $restaurant->id,
            'reservation_date' => $now->format('Y-m-d'),
            'reservation_time' => $now->format('H:i'),
            'number_of_people' => 2,
        ]);

        $response->assertRedirect(route('reservations.index'));
    }

    // ログイン済みの管理者は予約できない
    public function test_admin_cannot_store_reservation()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $this->actingAs($admin, 'admin');

        $restaurant = Restaurant::factory()->create();
        $response = $this->post(route('restaurants.reservations.store', $restaurant), [
            'reserved_datetime' => now(),
            'number_of_people' => 2,
        ]);

        $response->assertRedirect(route('admin.home'));
    }

    // destroyアクション（予約キャンセル機能）
    // 未ログインのユーザーは予約をキャンセルできない
    public function test_guest_cannot_cancel_reservation()
    {
        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create();
        $reservation = Reservation::factory()->create([
            'user_id' => $user->id,
            'restaurant_id' => $restaurant->id
        ]);

        $response = $this->delete(route('reservations.destroy', ['reservation' => $reservation]));

        $response->assertRedirect(route('login'));
    }

    // ログイン済みの無料会員は予約をキャンセルできない
    public function test_regular_user_cannot_cancel_reservation()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $restaurant = Restaurant::factory()->create();
        $reservation = Reservation::factory()->create([
            'user_id' => $user->id,
            'restaurant_id' => $restaurant->id
        ]);

        $response = $this->delete(route('reservations.destroy', ['reservation' => $reservation]));

        $response->assertRedirect(route('subscription.create'));
    }

    // ログイン済みの有料会員は他人の予約をキャンセルできない
    public function test_premium_user_cannot_cancel_reservation()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QTekHP1x9xomPwVGawxXAOm')->create('pm_card_visa');
        $this->actingAs($user);

        $restaurant = Restaurant::factory()->create();
        $otherUser = User::factory()->create(); // 他のユーザー
        $reservation = Reservation::factory()->create([
            'user_id' => $otherUser->id,
            'restaurant_id' => $restaurant->id
        ]);

        $response = $this->delete(route('reservations.destroy', ['reservation' => $reservation]));

        $response->assertRedirect(route('reservations.index'));
    }

    // ログイン済みの有料会員は自身の予約をキャンセルできる
    public function test_premium_user_can_cancel_own_reservation()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QTekHP1x9xomPwVGawxXAOm')->create('pm_card_visa');
        $this->actingAs($user);

        $restaurant = Restaurant::factory()->create();
        $reservation = Reservation::factory()->create([
            'user_id' => $user->id,
            'restaurant_id' => $restaurant->id
        ]);

        $response = $this->delete(route('reservations.destroy', ['reservation' => $reservation]));

        $response->assertRedirect(route('reservations.index'));
    }

    // ログイン済みの管理者は予約をキャンセルできない
    public function test_admin_cannot_cancel_reservation()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $this->actingAs($admin, 'admin');

        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create(); // 他のユーザー
        $reservation = Reservation::factory()->create([
            'user_id' => $user->id,
            'restaurant_id' => $restaurant->id
        ]);

        $response = $this->delete(route('reservations.destroy', ['reservation' => $reservation]));

        $response->assertRedirect(route('admin.home'));
    }
}
