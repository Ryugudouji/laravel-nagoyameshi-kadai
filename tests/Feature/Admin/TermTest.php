<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TermTest extends TestCase
{
    use RefreshDatabase;

    // indexアクション（利用規約一覧ページ）
    // 未ログインのユーザーは管理者側の利用規約ページにアクセスできない
    public function test_guest_can_not_access_admin_terms_index()
    {
        $response = $this->get(route('admin.terms.index'));

        $response->assertRedirect(route('admin.login'));
    }


    // ログイン済みの一般ユーザーは管理者側の利用規約ページにアクセスできない
    public function test_regular_user_can_not_access_admin_terms_index()
    {
        // テストに必要なデータの準備
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.terms.index'));

        $response->assertRedirect(route('admin.login'));
    }


    // ログイン済みの管理者は管理者側の利用規約ページにアクセスできる
    public function test_admin_can_access_admin_terms_index()
    {
        // テストに必要なデータの準備
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $term = Term::factory()->create();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.terms.index'));

        $response->assertStatus(200);
    }


    // editアクション（店舗編集ページ）
    // 未ログインのユーザーは管理者側の利用規約編集ページにアクセスできない
    public function test_guest_can_not_access_admin_terms_edit()
    {
        $term = Term::factory()->create();

        $response = $this->get(route('admin.terms.edit', $term));

        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの一般ユーザーは管理者側の利用規約編集ページにアクセスできない
    public function test_regular_user_can_not_access_admin_terms_edit()
    {
        $user = User::factory()->create();
        $term = Term::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.terms.edit', $term));

        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの管理者は管理者側の利用規約編集ページにアクセスできる
    public function test_admin_can_access_admin_terms_edit()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $term = Term::factory()->create();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.terms.edit', $term));

        $response->assertStatus(200);
    }


    // updateアクション（利用規約更新機能）
    // 未ログインのユーザーは利用規約を更新できない
    public function test_guest_can_not_access_admin_terms_update()
    {
        $term = Term::factory()->create();
        $termData =[
            'content' => 'アップデートテストcontent',
        ];

        // 検証したい処理の実行
        $response = $this->put(route('admin.terms.update', $term), $termData);

        // 処理した結果の検証
        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの一般ユーザーは利用規約を更新できない
    public function test_regular_user_can_not_access_admin_terms_update()
    {
        // テストに必要なデータの準備
        $user = User::factory()->create();
        $term = Term::factory()->create();

        $termData =[
            'content' => 'アップデートテストcontent',
        ];

        // 検証したい処理の実行
        $response = $this->actingAs($user)->put(route('admin.terms.update', $term), $termData);

        // 処理した結果の検証
        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの管理者は利用規約を更新できる
    public function test_admin_can_access_admin_terms_update()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $old_term = Term::factory()->create([
            'content' => '旧content',
        ]);

        $new_term_data = [
            'content' => 'アップデートテストcontent',
        ];

        $response = $this->actingAs($admin, 'admin')->patch(route('admin.terms.update', $old_term->id), $new_term_data);

        $response->assertRedirect(route('admin.terms.index'));

        // 新しいデータが正しくデータベースに保存されているか確認
        $this->assertDatabaseHas('terms', [
            'id' => $old_term->id,
            'content' => 'アップデートテストcontent',
        ]);

        // 旧データが正しく変更されたことを確認
        $this->assertDatabaseMissing('terms', [
            'id' => $old_term->id,
            'content' => '旧content',
        ]);

    }


}
