{{-- ヘッダーの検索欄、ナビを表示するコンポーネント --}}
<div class="header__search">
    <form action="/" method="GET" class="header__search-form">
        @if (request('tab'))
            <input type="hidden" name="tab" value="{{ request('tab') }}">
        @endif
        <input type="text" name="search" placeholder="なにをお探しですか？" class="header__search-input"
            value="{{ request('search') }}">
        <button type="submit" class="header__search-button">
            <i class="fa-solid fa-magnifying-glass header__search-icon"></i>
        </button>
    </form>
</div>
<div class="header__nav">
    @auth
        <form class="header__nav-form" action="/logout" method="post">
            @csrf
            <button type="submit" class="header__nav-button">ログアウト</button>
        </form>
    @else
        <a href="/login" class="header__nav-button">ログイン</a>
    @endauth
    <a href="/mypage" class="header__nav-link">マイページ</a>
    <a href="/sell" class="header__nav-link header__nav-link--sell">出品</a>
</div>

<style>
    /* ヘッダー検索・ナビゲーションスタイル */
    .header__search {
        flex: 1;
        max-width: 500px;
    }

    .header__search-form {
        width: 100%;
        position: relative;
        display: flex;
        align-items: center;
    }

    .header__search-input {
        width: 100%;
        height: 50px;
        padding: 10px 60px 10px 30px;
        border: none;
        border-radius: 4px;
        font-size: 24px;
        font-weight: 400;
    }

    .header__search-button {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        padding: 8px;
        border-radius: 4px;
    }

    .header__search-icon {
        font-size: 18px;
    }

    .header__search-button:hover {
        background-color: #f5f5f5;
    }

    .header__search-input::placeholder {
        color: #000000;
    }

    .header__nav {
        display: flex;
        gap: 20px;
        align-items: center;
        font-size: 24px;
        font-weight: 400;
    }

    .header__nav-link {
        color: #ffffff;
        text-decoration: none;
    }

    .header__nav-link--sell {
        background-color: #ffffff;
        color: #000000;
        border-radius: 4px;
        margin-right: 25px;
        padding: 5px 25px;
    }

    .header__nav-button {
        background-color: #000000;
        color: #ffffff;
        border: none;
        font-weight: 400;
        font-size: 24px;
        cursor: pointer;
        text-decoration: none;
        line-height: 1;
    }

    .header__nav-link:hover,
    .header__nav-button:hover {
        opacity: 0.8;
    }

    /* 1400px未満用スタイル */
    @media screen and (max-width: 1399px) {
        .header__search-input {
            height: 40px;
            padding: 8px 50px 8px 20px;
            font-size: 18px;
        }

        .header__search-button {
            right: 8px;
            padding: 6px;
        }

        .header__search-icon {
            font-size: 16px;
        }

        .header__nav {
            gap: 15px;
            font-size: 18px;
            justify-content: center;
            width: 100%;
        }

        .header__nav-link--sell {
            padding: 5px 20px;
            margin-right: 0;
            border-radius: 6px;
        }

        .header__nav-button {
            font-size: 18px;
        }
    }
</style>
