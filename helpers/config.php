<?php
    // DB接続設定
    define('DSN', 'sqlsrv:server = tcp:23jn0240db.database.windows.net,1433; Database = 23jn0240DB');
    define('DB_USER', 'jndb');
    define('DB_PASSWORD', 'Pa$$word1234');

    define('BASE_URL', 'https://23jn0240jecsheet.azurewebsites.net/'); // ルートパスを設定
    define('PASS_HENKOU_PATH', BASE_URL . 'change_password/'); // アセットフォルダのパス