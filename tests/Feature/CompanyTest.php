<?php

namespace Tests\Feature;

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

    // 未ログインのユーザーは会員側の会社概要ページにアクセスできる
    public function test_guest_can_access_company_page()
    {
        Company::factory()->create();

        $response = $this->get(route('company.index'));

        $response->assertStatus(200);
    }

    // ログイン済みの一般ユーザーは会員側の会社概要ページにアクセスできる
    public function test_regular_user_can_access_company_page()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Company::factory()->create();

        $response = $this->get(route('company.index'));

        $response->assertStatus(200);
    }

    // ログイン済みの管理者は会員側の会社概要ページにアクセスできない
    public function test_admin_cannot_access_company_page()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $this->actingAs($admin, 'admin');

        $response = $this->get(route('company.index'));

        $response->assertRedirect(route('admin.home'));
    }
}
