<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Laravel\Cashier\Exceptions\IncompletePayment;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    // createアクション（有料プラン登録ページ）・・・・・・・・・・・・・・・・・・・　create
    // 未ログインのユーザーは有料プラン登録ページにアクセスできない
    public function test_guest_cannot_access_subscription_create()
    {
        $response = $this->get(route('subscription.create'));

        $response->assertRedirect(route('login'));
    }

    // ログイン済みの無料会員は有料プラン登録ページにアクセスできる
    public function test_regular_user_can_access_subscription_create()
    {
        // 一般ユーザーを作成
        $user = User::factory()->create();

        // ログイン状態にする
        $this->actingAs($user);

        // 会員情報ページにアクセス
        $response = $this->get(route('subscription.create'));

        $response->assertStatus(200);
    }

    // ログイン済みの有料会員は有料プラン登録ページにアクセスできない
    public function test_premium_user_cannot_access_subscription_create()
    {
        $user = User::factory()->create();

        // サブスクリプションを有料プランで作成
        $user->newSubscription('premium_plan', 'price_1QTekHP1x9xomPwVGawxXAOm')->create('pm_card_visa');

        $this->actingAs($user);

        // 有料プラン登録ページにアクセス
        $response = $this->get(route('subscription.create'));

        // 有料会員なのでアクセスできないことを確認
        $response->assertRedirect(route('subscription.edit'));
    }

    // ログイン済みの管理者は有料プラン登録ページにアクセスできない
    public function test_admin_cannot_access_subscription_create()
    {
        // テストに必要なデータの準備
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $this->actingAs($admin, 'admin');

        // 有料プラン登録ページにアクセス
        $response = $this->get(route('subscription.create'));

        // 管理者なのでアクセスできないことを確認
        $response->assertRedirect(route('admin.home'));
    }


    // storeアクション（有料プラン登録機能）・・・・・・・・・・・・・・・・・・・　store
    // 未ログインのユーザーは有料プランに登録できない
    public function test_guest_cannot_register_for_premium_plan()
    {
        $request_parameter = [
            'paymentMethodId' => 'pm_card_visa'
        ];

        // 未ログインユーザーが有料プラン登録を試みる
        $response = $this->post(route('subscription.store'), $request_parameter);

        // リダイレクト先や適切なステータスコードを確認
        $response->assertRedirect(route('login'));
    }

    // ログイン済みの無料会員は有料プランに登録できる
    public function test_regular_user_can_register_for_premium_plan()
    {
        $user = User::factory()->create();
        // 無料会員としてログイン
        $this->actingAs($user);

        $request_parameter = [
            'paymentMethodId' => 'pm_card_visa',
        ];


        // 有料プラン登録を試みる
        $response = $this->post(route('subscription.store'), $request_parameter);

        // 最新の状態にリフレッシュ
        $user->refresh();
        $this->assertTrue($user->subscribed('premium_plan'));

        $response->assertRedirect(route('home'));

    }

    // ログイン済みの有料会員は有料プランに登録できない
    public function test_premium_user_cannot_register_for_premium_plan()
    {
        $user = User::factory()->create();

        // ユーザーにプレミアムプランを登録
        $user->newSubscription('premium_plan', 'price_1QTekHP1x9xomPwVGawxXAOm')->create('pm_card_visa');

        $this->actingAs($user);

        // サブスクリプションが既にプレミアムプランであることを確認
        $this->assertTrue($user->subscribed('premium_plan'));

        $request_parameter = [
            'paymentMethodId' => 'pm_card_visa'
        ];

        $response = $this->post(route('subscription.store'), $request_parameter);

        $response->assertRedirect(route('subscription.edit'));

        // 再度サブスクリプションがプレミアムプランのままであることを確認
        $this->assertTrue($user->subscribed('premium_plan'));
    }

    // ログイン済みの管理者は有料プランに登録できない
    public function test_admin_cannot_register_for_premium_plan()
    {
        // テストに必要なデータの準備
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $request_parameter = [
            'paymentMethodId' => 'pm_card_visa',
        ];

        // 管理者としてログイン
        $this->actingAs($admin, 'admin');

        // 有料プラン登録ページにアクセス
        $response = $this->post(route('subscription.store'), $request_parameter);

        // 管理者なのでアクセスできないことを確認
        $response->assertRedirect(route('admin.home'));
    }


    // editアクション（お支払い方法編集ページ）・・・・・・・・・・・・・・・・・・・　edit
    // 未ログインのユーザーはお支払い方法編集ページにアクセスできない
    public function test_guest_cannot_access_subscription_edit()
    {
        $response = $this->get(route('subscription.edit'));
        $response->assertRedirect(route('login'));
    }


    // ログイン済みの無料会員はお支払い方法編集ページにアクセスできない
    public function test_regular_user_can_access_subscription_edit()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('subscription.edit'));
        $response->assertRedirect(route('subscription.create'));
    }

    // ログイン済みの有料会員はお支払い方法編集ページにアクセスできる
    public function test_premium_user_can_access_subscription_edit()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QTekHP1x9xomPwVGawxXAOm')->create('pm_card_visa');
        $this->actingAs($user);

        $response = $this->get(route('subscription.edit'));
        $response->assertStatus(200);
    }

    // ログイン済みの管理者はお支払い方法編集ページにアクセスできない
    public function test_admin_cannot_access_subscription_edit()
    {
        // テストに必要なデータの準備
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $this->actingAs($admin, 'admin');

        $response = $this->get(route('subscription.edit'));
        $response->assertRedirect(route('admin.home'));
    }

    // updateアクション（お支払い方法更新機能）・・・・・・・・・・・・・・・・・・・　update
    // 未ログインのユーザーはお支払い方法を更新できない
    public function test_guest_cannot_update_payment()
    {
        $response = $this->post(route('subscription.update'), [
            'paymentMethodId' => 'pm_card_mastercard',
        ]);
        $response->assertRedirect(route('login'));
    }

    // ログイン済みの無料会員はお支払い方法を更新できない
    public function test_regular_user_cannot_update_payment()
    {
        $user = User::factory()->create(); // 無料会員
        $this->actingAs($user);

        $response = $this->post(route('subscription.update'), [
            'paymentMethodId' => 'pm_card_mastercard',
        ]);

        $response->assertRedirect(route('home'));
    }

    // ログイン済みの有料会員はお支払い方法を更新できる
    public function test_premium_user_can_update_payment()
    {
        $user = User::factory()->create();
        $user->createAsStripeCustomer();

        $this->actingAs($user);

        // ユーザーのデフォルト支払い方法を作成
        $user->addPaymentMethod('pm_card_visa');
        $user->updateDefaultPaymentMethod('pm_card_visa');

        $default_payment_method_id = $user->defaultPaymentMethod()->id;

        $response = $this->post(route('subscription.update'), [
            'paymentMethodId' => 'pm_card_mastercard', // テスト用カード
        ]);

        $response->assertRedirect(route('home'));

        // 更新後のデフォルト支払い方法のIDを取得
        $updated_payment_method_id = $user->defaultPaymentMethod()->id;

        // 元の支払い方法と異なることを確認
        $this->assertNotEquals($default_payment_method_id, $updated_payment_method_id);
    }

    // ログイン済みの管理者はお支払い方法を更新できない
    public function test_admin_cannot_update_payment()
    {
        // テストに必要なデータの準備
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $this->actingAs($admin, 'admin');

        $response = $this->post(route('subscription.update'), [
            'paymentMethodId' => 'pm_card_mastercard',
        ]);
        $response->assertRedirect(route('admin.home'));
    }


    // cancelアクション（有料プラン解約ページ）・・・・・・・・・・・・・・・・・・・　cancel
    // 未ログインのユーザーは有料プラン解約ページにアクセスできない
    public function test_guest_cannot_access_cancel()
    {
        $response = $this->get(route('subscription.cancel'));
        $response->assertRedirect(route('login'));
    }

    // ログイン済みの無料会員は有料プラン解約ページにアクセスできない
    public function test_regular_user_cannot_access_cancel()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('subscription.cancel'));
        $response->assertRedirect(route('subscription.create'));
    }

    // ログイン済みの有料会員は有料プランを解約できる
    public function test_premium_user_can_access_cancel()
    {
    $user = User::factory()->create();
    $user->newSubscription('premium_plan', 'price_1QTekHP1x9xomPwVGawxXAOm')->create('pm_card_mastercard');
    $this->actingAs($user);

    $response = $this->get(route('subscription.cancel'));
    $response->assertStatus(200);
    }

    // ログイン済みの管理者は有料プラン解約ページにアクセスできない
    public function test_admin_cannot_access_cancel()
    {
        // テストに必要なデータの準備
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $this->actingAs($admin, 'admin');

        $response = $this->get(route('subscription.cancel'));
        $response->assertRedirect(route('admin.home'));

    }

    // destroyアクション（有料プラン解約機能）・・・・・・・・・・・・・・・・・・・　destroy
    // 未ログインのユーザーは有料プランを解約できない
    public function test_guest_cannot_cancel_subscription()
    {
        $response = $this->post(route('subscription.destroy'));
        $response->assertRedirect(route('login'));
    }

    // ログイン済みの無料会員は有料プランを解約できない
    public function test_regular_user_cannot_cancel_subscription()
{
    $user = User::factory()->create();

    // Stripeカスタマーと有効な支払い方法を設定
    $user->createAsStripeCustomer();
    $user->addPaymentMethod('pm_card_visa');

    $this->actingAs($user);

    // サブスクリプションが存在しないことを確認
    try {
        $response = $this->delete(route('subscription.destroy'));
        $response->assertRedirect(route('subscription.create'));
    } catch (IncompletePayment $e) {
        // 支払いが不完全の場合の例外処理
        $this->assertInstanceOf(IncompletePayment::class, $e);
    }
}


    // ログイン済みの有料会員は有料プランを解約できる
    public function test_premium_user_can_cancel_subscription()
{
    $user = User::factory()->create();
    $user->createAsStripeCustomer();
    $user->newSubscription('premium_plan', 'price_1QTekHP1x9xomPwVGawxXAOm')->create('pm_card_visa');
    $this->actingAs($user);

    $response = $this->delete(route('subscription.destroy'));

    $user = $user->fresh();
    $this->assertFalse($user->subscribed('premium_plan'));

    $response->assertRedirect(route('home'));
}


    // ログイン済みの管理者は有料プランを解約できない
    public function test_admin_cannot_cancel_subscription()
    {
        // テストに必要なデータの準備
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $this->actingAs($admin, 'admin');

        $response = $this->post(route('subscription.destroy'));
        $response->assertRedirect(route('admin.home'));
        }
}
