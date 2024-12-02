<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    // indexアクション（会員情報ページ）
    // 未ログインのユーザーは会員情報ページにアクセスできない
    public function test_guest_cannot_access_user_index()
    {
        $response = $this->get(route('user.index'));

        $response->assertRedirect(route('login'));
    }

    // ログイン済みの一般ユーザーは会員情報ページにアクセスできる
    public function test__regular_user_can_access_user_index()
    {
        // 一般ユーザーを作成
        $user = User::factory()->create();

        // ログイン状態にする
        $this->actingAs($user);

        // 会員情報ページにアクセス
        $response = $this->get(route('user.index'));

        $response->assertStatus(200);
        $response->assertViewIs('user.index');

    }

    // ログイン済みの管理者は会員側の会員情報ページにアクセスできない
    public function test_admin_cannot_access_user_index()
    {
        // テストに必要なデータの準備
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        // 管理者としてログイン
        $this->actingAs($admin, 'admin');

        // 会員情報ページにアクセス
        $response = $this->get(route('user.index'));

        $response->assertRedirect(route('home'));
    }


    // editアクション（会員情報編集ページ）
    // 未ログインのユーザーは会員側の会員情報編集ページにアクセスできない
    public function test_guest_cannot_access_user_edit()
    {
        $user = User::factory()->create();

        $response = $this->get(route('user.edit', $user->id));

        $response->assertRedirect(route('login'));

    }

    // ログイン済みの一般ユーザーは会員側の他人の会員情報編集ページにアクセスできない
    public function test_regular_user_cannot_access_other_users_edit()
    {
        $loggedInUser = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->actingAs($loggedInUser);

        $response = $this->get(route('user.edit', $otherUser->id));

        $response->assertRedirect(route('user.index'));
    }

    // ログイン済みの一般ユーザーは会員側の自身の会員情報編集ページにアクセスできる
    public function test_regular_user_can_access_own_edit()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('user.edit', $user->id));

        $response->assertOk()
            ->assertViewIs('user.edit')
            ->assertViewHas('user', $user);
    }

    // ログイン済みの管理者は会員側の会員情報編集ページにアクセスできない
    public function test_admin_cannot_access_user_edit_page()
    {
        $user = User::factory()->create();

        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $this->actingAs($admin, 'admin');

        $response = $this->get(route('user.edit', $user->id));

        $response->assertRedirect(route('home'));
    }


    // updateアクション（会員情報更新機能）
    // 未ログインのユーザーは会員情報を更新できない
    public function test_guest_cannot_update_user()
    {
        $user = User::factory()->create();
        $updateData = ['name' => 'updated_name'];

        $response = $this->patch(route('user.update', $user->id), $updateData);

        $response->assertRedirect(route('login')); // ログインページにリダイレクト
        $this->assertDatabaseMissing('users', $updateData); // 更新されていないことを確認
    }

    // ログイン済みの一般ユーザーは他人の会員情報を更新できない
    public function test_regular_user_cannot_update_other_users()
    {
        $loggedInUser = User::factory()->create();
        $otherUser = User::factory()->create();
        $updateData = [
            'name' => '名前更新',
            'kana' => 'ナマエコウシン',
            'email' => 'testuser@sample.com',
            'postal_code' => '1234567',
            'address' => '岐阜県岐阜市',
            'phone_number' => '09012345678',
            'birthday' => '1990-01-01',
            'occupation' => 'エンジニア',
        ];

        $this->actingAs($loggedInUser);

        $response = $this->patch(route('user.update', $otherUser->id), $updateData);

        $response->assertRedirect(route('user.index')); // 自身の会員情報ページにリダイレクト
    }

    // ログイン済みの一般ユーザーは自身の会員情報を更新できる
    public function test_regular_user_can_update_own()
    {
        $loggedInUser = User::factory()->create();

        $updateData = [
            'name' => '名前更新',
            'kana' => 'ナマエコウシン',
            'email' => 'testuser@sample.com',
            'postal_code' => '1234567',
            'address' => '岐阜県岐阜市',
            'phone_number' => '09012345678',
            'birthday' => '19900101',
            'occupation' => 'エンジニア',
            ];

        $this->actingAs($loggedInUser);

        $response = $this->patch(route('user.update', $loggedInUser->id), $updateData);

        $response->assertRedirect(route('user.index')); // 更新後に会員情報ページにリダイレクト
        $this->assertDatabaseHas('users', array_merge(['id' => $loggedInUser->id], $updateData)); // 更新されたことを確認
    }

    // ログイン済みの管理者は会員情報を更新できない
    public function test_admin_cannot_update_user()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $this->actingAs($admin, 'admin');

        $user = User::factory()->create();
        $updateData = ['name' => '名前更新'];

        // ユーザーを更新しようとする
        $response = $this->patch(route('user.update', $user->id), $updateData);

        // 更新が失敗したことを確認
        $response->assertRedirect(route('user.index'));
        $this->assertDatabaseMissing('users', ['id' => $user->id, 'name' => '名前更新']);
    }

}
