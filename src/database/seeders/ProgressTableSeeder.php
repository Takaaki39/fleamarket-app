<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProgressTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $param = [
            'customer_id'          => 2,
            'user_id'          => 1,
            'item_id'          => 1,
        ];
        DB::table('progresses')->insert($param);

        $param = [
            'customer_id'          => 2,
            'user_id'          => 1,
            'item_id'          => 2,
        ];
        DB::table('progresses')->insert($param);

        $param = [
            'customer_id'          => 1,
            'user_id'          => 2,
            'item_id'          => 6,
        ];
        DB::table('progresses')->insert($param);
    }
}
