<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    // indexアクション（カテゴリ一覧ページ）
    // 未ログインのユーザーは管理者側のカテゴリ一覧ページにアクセスできない
    public function test_guest_can_not_access_admin_categories_index()
    {
        $response = $this->get(route('admin.categories.index'));

        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの一般ユーザーは管理者側のカテゴリ一覧ページにアクセスできない
    public function test_regular_user_can_not_access_admin_categories_index()
    {
        // テストに必要なデータの準備
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.categories.index'));

        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの管理者は管理者側のカテゴリ一覧ページにアクセスできる
    public function test_admin_can_access_admin_categories_index()
    {
        // テストに必要なデータの準備
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.categories.index'));

        $response->assertStatus(200);
    }


    // storeアクション（カテゴリ登録機能）
    // 未ログインのユーザーはカテゴリを登録できない
    public function test_guest_can_not_access_admin_categories_store()
    {
        $categoryData = Category::factory()->make()->toArray();

        $response = $this->post(route('admin.categories.store'), $categoryData);

        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの一般ユーザーはカテゴリを登録できない
    public function test_regular_user_can_not_access_admin_categories_store()
    {
        // テストに必要なデータの準備
        $user = User::factory()->create();
        $categoryData = Category::factory()->make()->toArray();


        $response = $this->actingAs($user)->post(route('admin.categories.store', $categoryData));

        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの管理者はカテゴリを登録できる
    public function test_admin_can_access_admin_categories_store()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $categoryData = Category::factory()->make()->toArray();

        $response = $this->actingAs($admin, 'admin')->post(route('admin.categories.store', $categoryData));

        $response->assertRedirect(route('admin.categories.index'));
    }


    // updateアクション（カテゴリ更新機能）
    // 未ログインのユーザーはカテゴリを更新できない
    public function test_guest_can_not_access_admin_categories_update()
    {
        $category = Category::factory()->create();
        $categoryData =[
            'name' => 'アップデートテストname',
        ];

        $response = $this->put(route('admin.categories.update', $category),$categoryData);

        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの一般ユーザーはカテゴリを更新できない
    public function test_regular_user_can_not_access_admin_categories_update()
    {
        // テストに必要なデータの準備
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $categoryData =[
            'name' => 'アップデートテストname',
        ];

        // 検証したい処理の実行
        $response = $this->actingAs($user)->put(route('admin.categories.update', $category),$categoryData);

        // 処理した結果の検証
        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの管理者はカテゴリを更新できる
    public function test_admin_can_access_admin_categories_update()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $old_category = Category::factory()->create([
            'name' => '旧name',
        ]);

        $new_category_data = [
            'name' => 'アップデートテストname',
        ];

        $response = $this->actingAs($admin, 'admin')->patch(route('admin.categories.update', $old_category), $new_category_data);

        $response->assertRedirect(route('admin.categories.index', $old_category));

        // 新しいデータが正しくデータベースに保存されているか確認
        $this->assertDatabaseHas('categories', [
            'id' => $old_category->id,
            'name' => 'アップデートテストname',
        ]);

        // 旧データが正しく変更されたことを確認
        $this->assertDatabaseMissing('categories', [
            'id' => $old_category->id,
            'name' => '旧name',
        ]);

    }


    // destroyアクション（カテゴリ削除機能）
    // 未ログインのユーザーはカテゴリを削除できない
    public function test_guest_can_not_access_admin_categories_destroy()
    {
        $category = Category::factory()->create();

        $response = $this->delete(route('admin.categories.destroy', $category));

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの一般ユーザーはカテゴリを削除できない
    public function test_regular_user_can_not_access_admin_categories_destroy()
    {
        $user = User::factory()->create();

        $category = Category::factory()->create();

        $response = $this->actingAs($user)->delete(route('admin.categories.destroy', $category));

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの管理者はカテゴリを削除できる
    public function test_admin_can_access_admin_categories_destroy()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $category = Category::factory()->create();

        $response = $this->actingAs($admin, 'admin')->delete(route('admin.categories.destroy', $category));

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
        $response->assertRedirect(route('admin.categories.index'));
    }

}
