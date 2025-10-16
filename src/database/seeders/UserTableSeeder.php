<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $param = [
            'name'              => 'TestUser1',
            'email'             => 'test_user1@example.com',
            'email_verified_at' => now(),
            'password'          => Hash::make('password'),
            'postal_code'       => '111-1111',
            'address'           => '埼玉県鶴ヶ島市若葉1-1-1',
            'building'          => ''
        ];
        DB::table('users')->insert($param);
        
        $param = [
            'name'              => 'TestUser2',
            'email'             => 'test_user2@example.com',
            'email_verified_at' => now(),
            'password'          => Hash::make('password'),
            'postal_code'       => '222-1111',
            'address'           => '青森県八戸市白銀1-1-1',
            'building'          => '根城パレス201'
        ];
        DB::table('users')->insert($param);
    }
}
