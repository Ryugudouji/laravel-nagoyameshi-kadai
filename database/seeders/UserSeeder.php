<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // User::factory()->count(100)->create();

        User::firstOrcreate(
            ['email' => 'user@example.com'], // ユーザーを特定する条件
            [
            'name' => '会員一郎',
            'kana' => 'カイインイチロウ',
            'email' => 'user@example.com',
            'password' => Hash::make('user1'),
            'postal_code' => '1234567',
            'address' => '愛知県',
            'phone_number' => '09012345678',
            'occupation' => '料理人',
        ]);
    }
}
