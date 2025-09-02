<div class="header__search">
    <input type="text" placeholder="なにをお探しですか？" class="header__search-input">
</div>
<div class="header__nav">
    <form action="/logout" method="post">
        @csrf
        <button type="submit" class="header__nav-button">ログアウト</button>
    </form>
    <a href="#" class="header__nav-link">マイページ</a>
    <a href="#" class="header__nav-link header__nav-link--sell">出品</a>
</div>

<style>
    /* ヘッダー追加スタイル */
    .header__search {
        flex: 1;
        max-width: 500px;
    }

    .header__search-input {
        width: 100%;
        height: 50px;
        padding: 10px 30px;
        border: none;
        border-radius: 4px;
        font-size: 24px;
        font-weight: 400;
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
        padding: 5px 20px;
    }

    .header__nav-button {
        background-color: #000000;
        color: #ffffff;
        border: none;
        font-weight: 400;
        font-size: 24px;
        cursor: pointer;
    }

    .header__nav-link:hover,
    .header__nav-button:hover {
        opacity: 0.8;
    }
</style>
