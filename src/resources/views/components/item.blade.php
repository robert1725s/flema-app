{{-- 商品パネルを表示するコンポーネント --}}
<div class="item">
    <a href="/item/{{ $item->id }}" class="item__link">
        <div class="item__image">
            @if ($item->image_path)
                <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->name }}" class="item__image-img">
            @endif
            @if ($item->purchaser_id !== null)
                <div class="item__sold-label">SOLD</div>
            @endif
        </div>
        <div class="item__name">{{ Str::limit($item->name, 25, '...') }}</div>
    </a>
</div>

<style>
    .item {
        cursor: pointer;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .item:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .item__link {
        text-decoration: none;
        color: inherit;
        display: block;
    }

    .item__image {
        width: 100%;
        height: 280px;
        border-radius: 4px;
        border: none;
        overflow: hidden;
        position: relative;
    }

    .item__image-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .item__sold-label {
        position: absolute;
        top: 0;
        left: 0;
        background-color: #ff0000;
        border-radius: 4px;
        color: white;
        font-size: 16px;
        font-weight: bold;
        padding: 8px 20px;
        z-index: 1;
    }

    .item__name {
        font-size: 25px;
        font-weight: 400;
        color: #000000;
        padding: 10px;
    }

    /* 1400px以下用スタイル */
    @media screen and (max-width: 1399px) {
        .item__image {
            height: 250px;
        }
    }

    /* タブレット用レスポンシブ */
    @media screen and (max-width: 850px) {
        .item__sold-label {
            font-size: 14px;
            padding: 6px 16px;
        }

        .item__name {
            font-size: 20px;
            padding: 8px;
        }
    }
</style>
