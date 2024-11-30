<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class HomeTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */

    //  未ログインのユーザーは会員側のトップページにアクセスできる
    public function test_guest_can_access_member_homepage()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    // ログイン済みの一般ユーザーは会員側のトップページにアクセスできる
    public function test__regular_user_can_access_member_homepage()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
    }

    // ログイン済みの管理者は会員側のトップページにアクセスできない
    public function test_admin_cannot_access_member_homepage()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $response = $this->actingAs($admin, 'admin')->get('/');

        $response->assertStatus(302);

        $response->assertRedirect(route('admin.home'));
    }

}
