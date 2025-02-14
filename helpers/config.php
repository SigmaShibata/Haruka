<?php
    // DB接続設定
    define('DSN', 'sqlsrv:server=10.32.97.1\sotsu;database=23jn02_G01');
    define('DB_USER', '23jn02_G01');
    define('DB_PASSWORD', '23jn02_G01');

    define('BASE_URL', '/http://10.32.97.1/SOTSU/2024/23JN02/G01/1%e7%8f%ad%e4%bd%9c%e6%a5%ad%e3%83%95%e3%82%a9%e3%83%ab%e3%83%80/%e5%ae%8c%e6%88%90%e7%94%bb%e9%9d%a2/'); // ルートパスを設定
    define('PASS_HENKOU_PATH', BASE_URL . 'パスワード変更/'); // アセットフォルダのパス