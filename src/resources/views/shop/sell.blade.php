@extends('layout.app')
@extends('layout.header')

@section('css')
<link rel="stylesheet" href="{{asset('css/shop/sell.css')}}">
@endsection

@section('content')
<main class="container">
  <h1 class="title">商品の出品</h1>

  <form action="{{ route('sell.store') }}" method="POST" enctype="multipart/form-data" novalidate>
    @csrf

    <!-- 商品画像 -->
    <section class="section">
      <h2>商品の詳細</h2>
      <div class="image-upload-wrapper">
        <div class="image-upload" id="imageUpload">
          <div class="image-preview" id="imagePreview"></div>
          <input type="file" name="img_url" id="image" accept="image/*" style="display: none;">
          <button type="button" id="btnSelect" class="btn-select">画像を選択する</button>
        </div>
        @error('img_url')
          <p class="error">{{ $message }}</p>
        @enderror
      </div>
    </section>

    <!-- 商品詳細 -->
    <section class="section">
      <h2>商品の詳細</h2>
      <div class="form-group">
        <label>カテゴリー</label>
        <div class="categories">
          @foreach($categories as $category)
            <span data-id="{{ $category->id }}">{{ $category->category_name }}</span>
          @endforeach
        </div>
        <!-- 選択されたカテゴリーを送る hidden -->
        <input type="hidden" name="categories" id="selectedCategories">
        @error('categories')
          <p class="error">{{ $message }}</p>
        @enderror
      </div>

      <div class="form-group">
        <label>商品の状態</label>
        <select name="condition" required>
          <option value=1>良好</option>
          <option value=2>目立った傷や汚れなし</option>
          <option value=3>やや傷や汚れあり</option>
          <option value=4>状態が悪い</option>
        </select>
        @error('condition')
          <p class="error">{{ $message }}</p>
        @enderror
      </div>
    </section>

    <!-- 商品名と説明 -->
    <section class="section">
      <h2>商品名と説明</h2>
      <div class="form-group">
        <label>商品名</label>
        <input type="text" name="name" required>
        @error('name')
          <p class="error">{{ $message }}</p>
        @enderror
      </div>
      <div class="form-group">
        <label>ブランド名</label>
        <input type="text" name="brand_name">
        @error('brand_name')
          <p class="error">{{ $message }}</p>
        @enderror
      </div>
      <div class="form-group">
        <label>商品の説明</label>
        <textarea name="description" required></textarea>
        @error('description')
          <p class="error">{{ $message }}</p>
        @enderror
      </div>
      <div class="form-group">
        <label>販売価格</label>
        <input type="number" name="price" placeholder="¥" required>
        @error('price')
          <p class="error">{{ $message }}</p>
        @enderror
      </div>
    </section>

    <!-- 出品ボタン -->
    <div class="form-group">
      <button type="submit" class="btn-submit">出品する</button>
    </div>
  </form>
</main>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const categories = document.querySelectorAll(".categories span");
  const hiddenInput = document.getElementById("selectedCategories");
  let selected = [];

  categories.forEach(category => {
    category.addEventListener("click", () => {
      const value = category.dataset.id; // ← IDを使う方が安全
      //const value = category.textContent; // カテゴリ名で送る場合はこれにする

      if (category.classList.contains("active")) {
        // 解除
        category.classList.remove("active");
        selected = selected.filter(item => item !== value);
      } else {
        // 選択
        category.classList.add("active");
        selected.push(value);
      }

      // hidden input に JSON 文字列でセット
      hiddenInput.value = JSON.stringify(selected);
    });
  });
});

const imageInput = document.getElementById("image");
const imagePreview = document.getElementById("imagePreview");
const imageUpload = document.getElementById("imageUpload");
const btnSelect = document.getElementById("btnSelect");

// ボタン押下で input[type=file] を開く
btnSelect.addEventListener("click", () => {
  imageInput.click();
});

// ファイル選択時の処理
imageInput.addEventListener("change", function(e) {
  const file = e.target.files[0];

  if (file) {
    const reader = new FileReader();
    reader.onload = function(e) {
      // プレビュー表示
      imagePreview.innerHTML = `<img src="${e.target.result}" alt="プレビュー画像">`;

      // ボタンを枠外に移動
      if (!btnSelect.classList.contains("outside")) {
        btnSelect.classList.add("outside");
        imageUpload.parentNode.appendChild(btnSelect); // 枠外に確実に移動
      }
    };
    reader.readAsDataURL(file);
  } else {
    // ファイル未選択時（またはキャンセル時）
    imagePreview.textContent = "";
    if (btnSelect.classList.contains("outside")) {
      btnSelect.classList.remove("outside");
      imageUpload.appendChild(btnSelect); // 枠内に戻す
    }
  }
});
</script>
@endsection
