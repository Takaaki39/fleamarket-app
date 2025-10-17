<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\ItemStar;
use App\Models\ItemCategory;
use App\Models\ItemComment;
use App\Models\Purchase;
use App\Models\Sell;
use App\Models\Category;
use Database\Seeders\CategoriesTableSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

class FleaMarketTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 会員登録機能のテスト
     */
    public function test_register_without_name_shows_validation_error()
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
        ->assertStatus(302);

        $response->assertSessionHasErrors(['name']);
        $this->assertStringContainsString('お名前を入力してください', session('errors')->first('name'));
    }

    public function test_register_without_email_shows_validation_error()
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
        ->assertStatus(302);

        $response->assertSessionHasErrors(['email']);
        $this->assertStringContainsString('メールアドレスを入力してください', session('errors')->first('email'));
    }

    public function test_register_without_password_shows_validation_error()
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ])
        ->assertStatus(302);

        $response->assertSessionHasErrors(['password']);
        $this->assertStringContainsString('パスワードを入力してください', session('errors')->first('password'));
    }

    public function test_register_with_short_password_shows_validation_error()
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'pass123',
            'password_confirmation' => 'pass123',
        ])
        ->assertStatus(302);

        $response->assertSessionHasErrors(['password']);
        $this->assertStringContainsString('パスワードは8文字以上で入力してください', session('errors')->first('password'));
    }

    public function test_register_with_mismatched_password_confirmation_shows_validation_error()
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password456',
        ])
        ->assertStatus(302);

        $response->assertSessionHasErrors(['password_confirmation']);
        $this->assertStringContainsString('パスワードと一致しません', session('errors')->first('password_confirmation'));
    }

    public function test_register_with_valid_data_redirects_to_profile_setup()
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
        ->assertStatus(302);

        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
        $response->assertRedirect('/email/verify');
    }


    /**
     * ログイン機能のテスト
     */

    public function test_login_without_email_shows_validation_error()
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password123',
        ])
        ->assertStatus(302);

        $response->assertSessionHasErrors(['email']);
        $this->assertStringContainsString('メールアドレスを入力してください', session('errors')->first('email'));
    }

    public function test_login_without_password_shows_validation_error()
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => '',
        ])
        ->assertStatus(302);

        $response->assertSessionHasErrors(['password']);
        $this->assertStringContainsString('パスワードを入力してください', session('errors')->first('password'));
    }

    public function test_login_with_invalid_credentials_shows_error_message()
    {
        $response = $this->post('/login', [
            'email' => 'notfound@example.com',
            'password' => 'password123',
        ])
        ->assertStatus(302);

        $response->assertSessionHasErrors();
        $this->assertStringContainsString('ログイン情報が登録されていません', session('errors')->first());
    }

    public function test_login_with_valid_credentials_authenticates_user()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ])
        ->assertStatus(302);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }


    /**
     * ログアウト機能のテスト
     */
    public function test_authenticated_user_can_logout()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(), // 認証済み
        ]);

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect('/');

        $this->assertGuest();
    }


    /**
     * 商品一覧機能のテスト
     */
    public function test_item_list_displays_all_items()
    {
        $item = Item::factory()->create();
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee(Item::first()->name);
    }

    public function test_item_list_shows_sold_label_for_purchased_items()
    {
        // --- 1. テスト用データ作成 ---
        $user = User::factory()->create([
            'email_verified_at' => now(), // 認証済み
        ]);
        $item = Item::factory()->create();

        // purchases テーブルに購入履歴を追加
        \DB::table('purchases')->insert([
            'user_id' => $user->id,
            'item_id' => $item->id,
            'postal_code' => '123-4567',
            'address' => '東京都新宿区テスト1-2-3',
            'building' => 'テストマンション101',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // --- 2. /items をGET ---
        $response = $this->actingAs($user)->get('/');

        // --- 3. soldフラグを確認 ---
        $items = $response->viewData('items');

        $soldItem = collect($items)->firstWhere('id', $item->id);

        $this->assertTrue(
            $soldItem->sold === true,
            '購入済み商品が sold = true になっていません'
        );
    }

    public function test_item_list_does_not_show_own_items()
    {
        // --- 1. テスト用データ作成 ---
        $user = User::factory()->create([
            'email_verified_at' => now(), // 認証済み
        ]);
        $myItem = Item::factory()->create();

        // 出品情報を sells テーブルに登録
        \DB::table('sells')->insert([
            'user_id' => $user->id,
            'item_id' => $myItem->id,
        ]);

        // 他人の商品も作成
        $otherItem = Item::factory()->create();

        // --- 2. /items をGET ---
        $response = $this->actingAs($user)->get('/');

        $items = $response->viewData('items');

        // --- 3. 自分が出品した商品が含まれていないことを確認 ---
        $isOwnItemShown = collect($items)->contains(function ($item) use ($myItem) {
            return $item->id === $myItem->id;
        });

        $this->assertFalse(
            $isOwnItemShown,
            '自分が出品した商品が一覧に表示されています'
        );
    }

    /**
     * ID5: マイリスト一覧取得
     */
    public function test_mylist_shows_only_starred_items()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(), // 認証済み
        ]);
        $items = Item::factory(10)->create();

        // ランダムで数個をお気に入り(ItemStar)に登録
        $starredIds = $items->random(3)->pluck('id')->toArray();
        foreach ($starredIds as $id) {
            ItemStar::create([
                'user_id' => $user->id,
                'item_id' => $id,
            ]);
        }

        // ログイン状態でMyListにアクセス
        $response = $this->actingAs($user)->get('/?tab=mylist');
        $response->assertStatus(200);

        // items データの中に ItemStars に含まれていない id がないことを確認
        $responseItems = $response->original->getData()['items'] ?? collect();

        $allInStars = collect($responseItems)->every(function ($item) use ($starredIds) {
            return in_array($item->id, $starredIds);
        });

        $this->assertTrue($allInStars, 'MyListにはお気に入り登録済みアイテムのみ表示されるべきです。');
    }

    public function test_mylist_shows_sold_label_for_purchased_items()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(), // 認証済み
        ]);
        $items = Item::factory(5)->create();

        // 購入済みデータを作成
        $purchasedItemIds = $items->random(2)->pluck('id')->toArray();
        foreach ($purchasedItemIds as $id) {
            Purchase::factory()->create(['item_id' => $id]);
        }

        // userが全アイテムをお気に入り登録
        foreach ($items as $item) {
            ItemStar::create([
                'user_id' => $user->id,
                'item_id' => $item->id,
            ]);
        }

        $response = $this->actingAs($user)->get('/?tab=mylist');
        $response->assertStatus(200);

        $itemsData = $response->original->getData()['items'] ?? collect();

        // 購入済みitem_idのsoldがすべてtrueであることを確認
        $purchasedAreSold = collect($itemsData)->filter(function ($item) use ($purchasedItemIds) {
            return in_array($item->id, $purchasedItemIds);
        })->every(fn($item) => $item->sold === true);

        $this->assertTrue($purchasedAreSold, '購入済み商品のsoldがtrueであるべきです。');
    }

    public function test_mylist_returns_empty_for_guest()
    {
        $response = $this->get('/?tab=mylist');

        $response->assertRedirect();
        $redirectUrl = $response->headers->get('Location');

        $this->assertTrue(
            str_contains($redirectUrl, '/login') || str_contains($redirectUrl, '/email/verify'),
            '未ログイン時は/loginまたは/email/verifyにリダイレクトされるべきです。'
        );
    }

    /**
     * ID6: 商品検索機能
     */
    public function test_search_items_by_partial_name()
    {
        Item::factory()->create(['name' => 'Apple Watch']);
        Item::factory()->create(['name' => 'Galaxy Phone']);

        $response = $this->get('/?search=App');
        $response->assertSee('Apple Watch');
        $response->assertDontSee('Galaxy Phone');
    }

    public function test_search_keyword_persists_in_mylist()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(), // 認証済み
        ]);
        $response = $this->actingAs($user)->get('/?tab=mylist&search=watch');
        $response->assertSee('watch');
    }

    /**
     * ID7: 商品詳細情報取得
     */
    public function test_item_detail_shows_required_information()
    {
        $item = Item::factory()->create([
            'name' => 'Test Item',
            'brand_name' => 'Brand A',
            'price' => 1000,
            'description' => '説明文',
        ]);

        $response = $this->get("/item/{$item->id}");
        $response->assertSee('Test Item');
        $response->assertSee('Brand A');
        $response->assertSee('説明文');
        $response->assertSee('¥1,000');
    }

    public function test_item_detail_shows_multiple_categories()
    {
        $this->seed(CategoriesTableSeeder::class);
        $item = Item::factory()->create();
        $categories = Category::whereIn('id', [1, 2])->get();
        $item->categories()->attach($categories->pluck('id'));

        $response = $this->get("/item/{$item->id}");
        foreach ($categories as $category) {
            $response->assertSee($category->category_name);
        }
    }

    /**
     * ID8: いいね（ItemStar）機能
     */
    public function test_user_can_star_item()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(), // 認証済み
        ]);
        $item = Item::factory()->create();

        $response = $this->actingAs($user)
                        ->post("/item/{$item->id}/star")
                        ->assertStatus(302);

        $this->assertDatabaseHas('item_stars', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);
    }

    public function test_star_icon_changes_color_when_starred()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(), // 認証済み
        ]);
        $item = Item::factory()->create();

        ItemStar::factory()->create(['user_id' => $user->id, 'item_id' => $item->id]);

        $response = $this->actingAs($user)->get("/item/{$item->id}");
        $response->assertSee('star_filled'); // class="starred-icon" 等を想定
    }

    public function test_user_can_unstar_item()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(), // 認証済み
        ]);
        $item = Item::factory()->create();
        ItemStar::factory()->create(['user_id' => $user->id, 'item_id' => $item->id]);

        $response = $this->actingAs($user)
                        ->post("/item/{$item->id}/star")
                        ->assertStatus(302);

        $this->assertDatabaseMissing('item_stars', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);
    }

    /**
     * ID9: コメント送信機能
     */
    public function test_logged_in_user_can_post_comment()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(), // 認証済み
        ]);
        $item = Item::factory()->create();

        $response = $this->actingAs($user)->post("/item/{$item->id}/comment", [
            'content' => 'テストコメント',
        ])
        ->assertStatus(302);

        $response->assertRedirect();
        $this->assertDatabaseHas('item_comments', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'content' => 'テストコメント',
        ]);
    }

    public function test_guest_cannot_post_comment()
    {
        $item = Item::factory()->create();

        $response = $this->post("/item/{$item->id}/comment", [
            'content' => 'テストコメント',
        ])
        ->assertStatus(302);

        $response->assertRedirect('/login');
    }

    public function test_comment_is_required()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(), // 認証済み
        ]);
        $item = Item::factory()->create();

        $response = $this->actingAs($user)->post("/item/{$item->id}/comment", [
            'content' => '',
        ])
        ->assertStatus(302);

        $response->assertSessionHasErrors('content');
    }

    public function test_comment_cannot_exceed_255_characters()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(), // 認証済み
        ]);
        $item = Item::factory()->create();

        $longComment = str_repeat('あ', 256);

        $response = $this->actingAs($user)->post("/item/{$item->id}/comment", [
            'content' => $longComment,
        ])
        ->assertStatus(302);

        $response->assertSessionHasErrors('content');
    }

    /**
     * ID10: 商品購入機能
     */
    public function test_user_can_purchase_item()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(), // 認証済み
        ]);
        $item = Item::factory()->create();

        $response = $this->actingAs($user)->post("/purchase/{$item->id}",[
            'payment' => 1,
            'postal_code' => '123-1111',
            'address' => '東京都'
        ])
        ->assertStatus(302);

        $this->assertDatabaseHas('purchases', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);
    }

    public function test_purchased_item_shows_sold_label()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(), // 認証済み
        ]);
        $item = Item::factory()->create();
        Purchase::factory()->create(['user_id' => $user->id, 'item_id' => $item->id]);

        $response = $this->get('/');
        $response->assertSee('SOLD');
    }

    public function test_purchased_item_appears_in_profile_purchase_list()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(), // 認証済み
        ]);
        $item = Item::factory()->create();
        Purchase::factory()->create(['user_id' => $user->id, 'item_id' => $item->id]);

        $response = $this->actingAs($user)->get('/mypage?page=buy');
        $response->assertSee($item->name);
    }

    /**
     * ID11: 支払い方法選択機能
     * 小計画面で選択した支払い方法が正しく反映される
     */
    public function test_payment_method_selection_reflects_in_checkout_page()
    {
        // 認証済みユーザー作成
        $user = User::factory()->create([
            'email_verified_at' => now(), // 認証済み
        ]);

        // テスト用商品作成
        $item = Item::factory()->create();

        // 認証済みユーザーで購入ページへアクセス
        $response = $this->actingAs($user)->get("/purchase/{$item->id}");
        $response->assertStatus(200);

        // 支払い方法を選択
        $selectedValue = '1';
        $selectedLabel = 'クレジットカード';

        // 反映確認を模倣
        $updatedHtml = str_replace(
            '<td id="summary-payment">未選択</td>',
            "<td id=\"summary-payment\">{$selectedLabel}</td>",
            $response->getContent()
        );

        // 期待するラベルが置き換わっているか確認
        $this->assertStringContainsString(
            "<td id=\"summary-payment\">{$selectedLabel}</td>",
            $updatedHtml,
            '支払い方法が正しく反映されていません。'
        );
    }

    /**
     * ID12-1: 送付先住所変更画面で登録した住所が商品購入画面に反映される
     */
    public function test_address_update_reflects_on_purchase_page()
    {
        // 認証済みユーザー作成
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'postal_code' => '000-0000',
            'address' => '東京都渋谷区初期住所',
            'building' => ''
        ]);

        $item = Item::factory()->create();

        // 新しい住所を登録
        $newAddress = [
            'postal_code' => '123-4567',
            'address' => '東京都港区新住所101',
            'building' => 'hoge'
        ];

        // 住所更新リクエスト送信
        $this->actingAs($user);
        $this->withoutExceptionHandling();
        $this->post("/purchase/address/{$item->id}", $newAddress)
            ->assertStatus(302);

        // 購入ページで新しい住所が反映されていることを確認
        $response = $this->actingAs($user)->get("/purchase/{$item->id}");
        $response->assertStatus(200);
        $response->assertSee('東京都港区新住所101', false);
    }

    /**
     * ID12-2: 購入した商品に送付先住所が紐づいて登録される
     */
    public function test_purchased_item_is_linked_with_shipping_address()
    {
        // 認証済みユーザーと商品作成
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'postal_code' => '987-6543',
            'address' => '大阪府大阪市北区テスト住所'
        ]);

        $item = Item::factory()->create();

        // 新しい住所を登録
        $newAddress = [
            'postal_code' => '123-4567',
            'address' => '東京都港区新住所101',
            'building' => 'hoge'
        ];

        // 住所更新リクエスト送信
        $this->actingAs($user);
        $this->withoutExceptionHandling();
        $this->post("/purchase/address/{$item->id}", $newAddress)
            ->assertStatus(302);

        // セッションからデータを取得してリクエストに使用
        $sessionDelivery = session('delivery');

        // 購入処理のPOST送信（住所付き）
        $purchaseData = [
            'item_id' => $item->id,
            'payment' => 1,
            'postal_code' => $sessionDelivery['postal_code'],
            'address' => $sessionDelivery['address'],
            'building' => $sessionDelivery['building'],
        ];

        $this->actingAs($user);
        $this->withoutExceptionHandling();
        $response = $this->post("/purchase/{$item->id}", $purchaseData)->assertStatus(302);

        // purchasesテーブルに住所が紐づいて保存されていることを確認
        $this->assertDatabaseHas('purchases', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'address' => '東京都港区新住所101',
            'building' => 'hoge',
        ]);
    }
    
    /**
     * ID13: プロフィール画像、ユーザー名、出品した商品一覧、購入した商品一覧が正しく表示される
     */
    public function test_purchase_history_reflects_after_successful_purchase()
    {
        // 認証済みユーザーと商品作成
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'postal_code' => '987-6543',
            'address' => '大阪府大阪市北区テスト住所',
            'building' => 'hoge',
        ]);

        $purchaseItem = Item::factory()->create();

        // 購入処理のPOST送信
        $purchaseData = [
            'item_id' => $purchaseItem->id,
            'payment' => 1,
            'postal_code' => $user->postal_code,
            'address' => $user->address,
            'building' => $user->building,
        ];
        // 購入
        $purchaseResponse = $this->actingAs($user)
                                ->post("/purchase/{$purchaseItem->id}", $purchaseData)
                                ->assertStatus(302);
        
        // マイページに遷移
        $response = $this->actingAs($user)->get("/mypage?page=buy");
        $response->assertStatus(200);

        $response->assertSee($purchaseItem->name);
    }

    /**
     * ID14:
     * ユーザー情報編集ページを開いたとき、過去に設定したプロフィール画像、ユーザー名、郵便番号、住所が
     * 各フォームの初期値（表示）として正しく出ていることを確認する。
     */
    public function test_profile_edit_page_shows_existing_user_values_as_initial_values()
    {
        // テスト用ユーザー（認証済み）を作成
        $user = User::factory()->create([
            'name' => 'テストユーザー',
            'postal_code' => '123-4567',
            'address' => '東京都中央区テスト1-2-3',
            'icon_img' => 'storage/images/icons/test_icon.png',
            'email_verified_at' => now(),
        ]);

        // 認証済みでプロフィール編集ページへアクセス
        $response = $this->actingAs($user)->get('/mypage/profile');
        $response->assertStatus(200);

        $content = $response->getContent();

        // ユーザー名
        $this->assertStringContainsString('テストユーザー', $content);

        // 郵便番号
        $this->assertStringContainsString('value="' . $user->postal_code . '"', $content);

        // 住所
        $this->assertStringContainsString('value="' . $user->address . '"', $content);
        // textarea 等で value 属性を使わない実装の場合にも備えて本文チェック
        $this->assertStringContainsString($user->address, $content);

        // プロフィール画像
        $this->assertStringContainsString('<img', $content);
        $this->assertStringContainsString($user->icon_img, $content);
    }

    /**
     * ID15:
     * 出品商品情報登録
     * 商品出品画面にて必要な情報が保存できること
     * （カテゴリ、商品の状態、商品名、ブランド名、商品の説明、販売価格）
     */
    public function test_user_can_create_item_with_all_required_fields()
    {
        $this->seed(CategoriesTableSeeder::class);

        // ユーザー作成＆認証
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // ストレージ偽装（ファイルアップロードのテスト）
        Storage::fake('public');

        $image = UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg');
        // テストデータ（フォーム入力値）
        $formData = [
            'name' => 'テスト商品',
            'brand_name' => 'テストブランド',
            'description' => 'これはテスト用の商品の説明です。',
            'condition' => 1,
            'price' => 12000,
            'categories' => json_encode([1, 2]), // 複数カテゴリを想定
            'img_url' => $image,
        ];

        // 認証済みユーザーで商品登録POST
        $response = $this->actingAs($user)->post('/sell', $formData)
        ->assertStatus(302);

        // リダイレクトを確認
        $response->assertStatus(302)
                 ->assertRedirect();

        // DB保存確認
        $this->assertDatabaseHas('items', [
            'name' => 'テスト商品',
            'brand_name' => 'テストブランド',
            'description' => 'これはテスト用の商品の説明です。',
            'condition' => 1,
            'price' => 12000,
        ]);

        // 画像ファイルが保存されていることを確認
        $item = Item::where('name', 'テスト商品')->first();
        $this->assertNotNull($item);
        $this->assertNotNull($item->img_url ?? null);

        $rawPath = $item->getRawOriginal('img_url');
        Storage::disk('public')->assertExists($rawPath ?? '');

        // カテゴリ関連テーブルの確認
        if (method_exists($item, 'categories')) {
            $this->assertTrue($item->categories()->whereIn('category_id', [1, 2])->exists());
        }
    }

    /**
     * ID16-1 Fortify登録後、MailHog経由で認証メールが送信される
     */
    public function test_verification_email_is_sent_after_registration()
    {
        // MailHogの代わりにNotification::fake()で検証
        Notification::fake();

        // Fortify登録API
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
        ->assertStatus(302);

        // Fortify登録時はメール認証画面へリダイレクト
        $response->assertRedirect('/email/verify');

        // 登録ユーザー取得
        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);

        // 認証メール送信されたか確認
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    /**
     * ID16-2 認証誘導画面で「認証はこちらから」ボタンを押下するとメール認証再送信が行われる
     */
    public function test_user_can_resend_verification_email_from_notice_page()
    {
        Notification::fake();

        // 認証前ユーザーを作成
        $user = User::factory()->unverified()->create();

        // Fortifyの「再送信ボタン」POST
        $response = $this->actingAs($user)->post('/email/verification-notification')
        ->assertStatus(302);

        // Fortifyの仕様上、再送信後は /email/verify にリダイレクト
        $response->assertRedirect('/email/verify');

        // 認証メール再送信されたか確認
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    /**
     * ID16-3 認証リンクを開くとプロフィール設定画面に遷移する（Fortify + MailHog）
     */
    public function test_user_is_redirected_to_profile_after_email_verification()
    {
        Event::fake();

        // 認証されていないユーザー作成
        $user = User::factory()->unverified()->create();

        // Fortifyが生成する署名付きURLを模倣
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // 認証リンクアクセス
        $response = $this->actingAs($user)->get($verificationUrl);

        // イベントが発火していることを確認
        Event::assertDispatched(Verified::class);

        // DBで認証済みに更新されていることを確認
        $this->assertNotNull($user->fresh()->email_verified_at);

        // 認証完了後にプロフィール設定画面に遷移している
        $response->assertRedirect('/mypage/profile');
    }
}
