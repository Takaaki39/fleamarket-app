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
            $table->id();

            $table->string('name')->comment('商品名');
            $table->unsignedInteger('price')->comment('価格(円)');
            $table->string('brand_name')->nullable()->comment('ブランド名');
            $table->text('description')->comment('商品説明');
            $table->string('img_url')->nullable()->comment('商品画像URl');
            $table->enum('condition', ['good', 'fair', 'poor', 'bad'])
                  ->default('good')
                  ->comment('コンディション');

            $table->timestamps();

            // よく使いそうな項目にインデックス
            $table->index('brand_name');
            $table->index('condition');
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
