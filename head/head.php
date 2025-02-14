<?php
// ヘッダーのスタンプいらなかったらここ全部消していい
 require_once '../helpers/StampUseDAO.php';

$member = $_SESSION["member"];
$user_type = $member->user_type;

$session_email = $member->email;

if ($user_type == 2){
  $stampUseDAO = new StampUseDAO();
  $stamp_id = $stampUseDAO->get_stampID_by_email($session_email);
}

?>

<!doctype html>
  <html lang="ja">
  <head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../head/head.css">
  </head>
  <body>
    <header>
      <img src="../head/logo_v2.png" alt="logo" class="logo">

<!-- ヘッダーの現在使用中スタンプ -->
      <?php if ($user_type == 2):?>
        <img src="../スタンプ管理画面/image_<?= htmlspecialchars($stamp_id) ?>.png" class="headCat">
      <?php endif; ?>
      
       <nav class="nav">
        <ul class="menu-group">
          <li class="menu-item"><a href="../ログイン/logout.php"><img src="../head/ログアウトcat.png" class="logout-img" width ="140px"></a></li>
          
        </ul>
      </nav>
    </header>
  </body>
  </html>