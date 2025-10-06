<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class ItemTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $param = [
            'name'          => '腕時計',
            'price'         => '15000',
            'brand_name'    => 'Rolax',
            'description'   => 'スタイリッシュなデザインのメンズ腕時計',
            'img_url'       => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Armani+Mens+Clock.jpg',
            'condition'     => 'good',
        ];
        DB::table('items')->insert($param);

        $param = [
            'name'          => 'HDD',
            'price'         => '5000',
            'brand_name'    => '西芝',
            'description'   => '高速で信頼性の高いハードディスク',
            'img_url'       => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/HDD+Hard+Disk.jpg',
            'condition'     => 'fair',
        ];
        DB::table('items')->insert($param);
        
        $param = [
            'name'          => '玉ねぎ3束',
            'price'         => '300',
            'brand_name'    => 'なし',
            'description'   => '新鮮な玉ねぎ３束のセット',
            'img_url'       => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/iLoveIMG+d.jpg',
            'condition'     => 'poor',
        ];
        DB::table('items')->insert($param);
        
        $param = [
            'name'          => '革靴',
            'price'         => '4000',
            'brand_name'    => '',
            'description'   => 'クラシックなデザインの革靴',
            'img_url'       => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Leather+Shoes+Product+Photo.jpg',
            'condition'     => 'bad',
        ];
        DB::table('items')->insert($param);
        
        $param = [
            'name'          => 'ノートPC',
            'price'         => '45000',
            'brand_name'    => '',
            'description'   => '高税能なノートパソコン',
            'img_url'       => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Living+Room+Laptop.jpg',
            'condition'     => 'good',
        ];
        DB::table('items')->insert($param);
        
        $param = [
            'name'          => 'マイク',
            'price'         => '8000',
            'brand_name'    => 'なし',
            'description'   => '高音質のレコーディング用マイク',
            'img_url'       => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Music+Mic+4632231.jpg',
            'condition'     => 'fair',
        ];
        DB::table('items')->insert($param);
        
        $param = [
            'name'          => 'ショルダーバッグ',
            'price'         => '3500',
            'brand_name'    => '',
            'description'   => 'おしゃれなショルダーバッグ',
            'img_url'       => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Purse+fashion+pocket.jpg',
            'condition'     => 'poor',
        ];
        DB::table('items')->insert($param);
        
        $param = [
            'name'          => 'タンブラー',
            'price'         => '500',
            'brand_name'    => 'なし',
            'description'   => '使いやすいタンブラー',
            'img_url'       => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Tumbler+souvenir.jpg',
            'condition'     => 'bad',
        ];
        DB::table('items')->insert($param);
        
        $param = [
            'name'          => 'コーヒーミル',
            'price'         => '4000',
            'brand_name'    => 'Starbacks',
            'description'   => '手動のコーヒーミル',
            'img_url'       => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Waitress+with+Coffee+Grinder.jpg',
            'condition'     => 'good',
        ];
        DB::table('items')->insert($param);
        
        $param = [
            'name'          => 'メイクセット',
            'price'         => '2500',
            'brand_name'    => '',
            'description'   => '便利なメイクアップセット',
            'img_url'       => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/%E5%A4%96%E5%87%BA%E3%83%A1%E3%82%A4%E3%82%AF%E3%82%A2%E3%83%83%E3%83%95%E3%82%9A%E3%82%BB%E3%83%83%E3%83%88.jpg',
            'condition'     => 'fair',
        ];
        DB::table('items')->insert($param);
    }
}
