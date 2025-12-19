<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionChatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_chats', function (Blueprint $table) {
            $table->id();

            $table->foreignId('transaction_id')
                ->constrained('transactions')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('message');
            $table->string('image', 255)->nullable()->comment('画像');

            $table->boolean('is_customer')
                ->comment('true: 購入者 / false: 出品者');

            // 閲覧済みかどうか
            $table->boolean('is_read')
                ->default(false)
                ->comment('true: 閲覧済み / false: 未閲覧');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transaction_chats');
    }
}
