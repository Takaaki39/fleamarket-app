<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            
            $table->string('name', 255)->nullable()->comment('名前');
            $table->string('email', 255)->unique()->nullable(false)->comment('メールアドレス');
            $table->timestamp('email_verified_at')->nullable()->comment('認証状態');
            $table->string('password', 255)->nullable(false)->comment('パスワード');

            // 独自追加カラム
            $table->string('postal_code', 255)->nullable()->comment('郵便番号');
            $table->string('address', 255)->nullable()->comment('住所');
            $table->string('building', 255)->nullable()->comment('建物名');
            $table->string('icon_img', 255)->nullable()->comment('アイコン');

            // Fortify が利用する remember_token
            $table->rememberToken();

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
        Schema::dropIfExists('users');
    }
}
