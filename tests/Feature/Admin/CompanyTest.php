<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;


class CompanyTest extends TestCase
{
    use RefreshDatabase;

    // indexアクション（会社概要一覧ページ）
    // 未ログインのユーザーは管理者側の会社概要ページにアクセスできない
    public function test_guest_can_not_access_admin_company_index()
    {
        $response = $this->get(route('admin.company.index'));

        $response->assertRedirect(route('admin.login'));
    }


    // ログイン済みの一般ユーザーは管理者側の会社概要ページにアクセスできない
    public function test_regular_user_can_not_access_admin_company_index()
    {
        // テストに必要なデータの準備
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.company.index'));

        $response->assertRedirect(route('admin.login'));
    }


    // ログイン済みの管理者は管理者側の会社概要ページにアクセスできる
    public function test_admin_can_access_admin_company_index()
    {
        // テストに必要なデータの準備
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $company = Company::factory()->create();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.company.index'));

        $response->assertStatus(200);
    }


    // editアクション（店舗編集ページ）
    // 未ログインのユーザーは管理者側の会社概要編集ページにアクセスできない
    public function test_guest_can_not_access_admin_company_edit()
    {
        $company = Company::factory()->create();

        $response = $this->get(route('admin.company.edit', $company));

        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの一般ユーザーは管理者側の会社概要編集ページにアクセスできない
    public function test_regular_user_can_not_access_admin_company_edit()
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.company.edit', $company));

        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの管理者は管理者側の会社概要編集ページにアクセスできる
    public function test_admin_can_access_admin_company_edit()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $company = Company::factory()->create();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.company.edit', $company));

        $response->assertStatus(200);
    }


    // updateアクション（会社概要更新機能）
    // 未ログインのユーザーは会社概要を更新できない
    public function test_guest_can_not_access_admin_company_update()
    {
        $company = Company::factory()->create();
        $companyData =[
            'name' => 'アップデートテストname',
        ];

        // 検証したい処理の実行
        $response = $this->put(route('admin.company.update', $company), $companyData);

        // 処理した結果の検証
        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの一般ユーザーは会社概要を更新できない
    public function test_regular_user_can_not_access_admin_company_update()
    {
        // テストに必要なデータの準備
        $user = User::factory()->create();
        $company = Company::factory()->create();

        $companyData =[
            'name' => 'アップデートテストname',
        ];

        // 検証したい処理の実行
        $response = $this->actingAs($user)->put(route('admin.company.update', $company), $companyData);

        // 処理した結果の検証
        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの管理者は会社概要を更新できる
    public function test_admin_can_access_admin_company_update()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $old_company = Company::factory()->create([
            'name' => '旧name',
        ]);

        $new_company_data = [
            'name' => 'アップデートテストname',
            'postal_code' => '1234567',
            'address' => '名古屋市中区栄',
            'representative' => '代表者名',
            'establishment_date' => '2024-01-01',
            'capital' => 5000000,
            'business' => '飲食業',
            'number_of_employees' => 50,
        ];

        $response = $this->actingAs($admin, 'admin')->patch(route('admin.company.update', $old_company), $new_company_data);

        $response->assertRedirect(route('admin.company.index'));

        // 新しいデータが正しくデータベースに保存されているか確認
        $this->assertDatabaseHas('companies', [
            'id' => $old_company->id,
            'name' => 'アップデートテストname',
        ]);

        // 旧データが正しく変更されたことを確認
        $this->assertDatabaseMissing('companies', [
            'id' => $old_company->id,
            'name' => '旧name',
        ]);

    }


}
