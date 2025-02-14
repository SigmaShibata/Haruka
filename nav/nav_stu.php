<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>


<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ナビゲーションメニュー</title>
    <link href="../nav/nav.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">

</head>
  <div class="menu-container">
    <nav class="vertical-nav">
        <ul>
            <li><a href="../スタンプシート/stamp_sheet.php" class="<?= $currentPage == 'stamp_sheet.php' ? 'active' : '' ?>">スタンプシート</a></li>
            <li><a href="../パスワード変更/passhenkou.php" class="<?= $currentPage == 'passhenkou.php' ? 'active' : '' ?>">パスワード変更</a></li>
        </ul>
     
<!-- ナビの猫 -->
          <img src="../head/winkcat.png" class="navCat" >
        

        <div class="clock"> <!-- 時計 -->
            <p class="clock-date"></p>
            <p class="clock-time"></p>
        </div>

        <!-- 時計のJavaScript-->
        <script src="../nav/clock.js"></script>
        
    </nav>
  </div>
</html>
