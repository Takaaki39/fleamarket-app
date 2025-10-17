<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id(); // bigint unsigned + primary key + auto increment
            $table->string('name', 255)->comment('商品名');
            $table->unsignedInteger('price')->comment('価格（円）');
            $table->string('brand_name', 255)->nullable()->comment('ブランド名');
            $table->text('description')->comment('商品説明');
            $table->string('img_url', 255)->comment('画像URL');

            // condition
            // 1:新品, 2:良好, 3:やや傷あり, 4:状態悪い
            $table->tinyInteger('condition')
                  ->unsigned()
                  ->comment('状態: 1〜4');

            $table->timestamps();

            $table->index('condition');
            $table->index('brand_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('items');
    }
}
