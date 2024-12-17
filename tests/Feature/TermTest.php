<?php

namespace Tests\Feature;

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

    // 未ログインのユーザーは会員側の利用規約ページにアクセスできる
    public function test_guest_can_access_term_page()
    {
        Term::factory()->create();

        $response = $this->get(route('terms.index'));

        $response->assertStatus(200);
    }

    // ログイン済みの一般ユーザーは会員側の利用規約ページにアクセスできる
    public function test_regular_user_can_access_term_page()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Term::factory()->create();

        $response = $this->get(route('terms.index'));

        $response->assertStatus(200);
    }

    // ログイン済みの管理者は会員側の利用規約ページにアクセスできない
    public function test_admin_cannot_access_term_page()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $this->actingAs($admin, 'admin');

        $response = $this->get(route('terms.index'));

        $response->assertRedirect(route('admin.home'));
    }
}
