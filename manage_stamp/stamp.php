<?php
    require_once '../helpers/MemberDAO.php';
    require_once '../helpers/StampDAO.php';
    require_once '../helpers/StampUseDAO.php';

    session_start();

     // ログインしてない時はログインページに戻る
     if(empty($_SESSION['member']) || $_SESSION["member"]->user_type ==1){
      header('Location: ../login/login.php');
        exit;
    }

    // セッション取得
    $member = $_SESSION["member"]; 
    $user_type = $member->user_type;


    // すべてのstampを取得、画面に表示
    $stampDAO = new StampDAO();
    $stamp_list = $stampDAO->get_stamp();


    // 変更ボタン押したら、セッションからメールアドレス取得
    if($_SERVER['REQUEST_METHOD'] === 'POST'){
      $stamp_id = isset($_POST['stamp_id']) ? $_POST['stamp_id'] : null;
      $email = $member->email;  //セッションからメールアドレス取得

      //エラーメッセージ表示
      if (empty($stamp_id)) {
        $errs['stamp'] = "スタンプを選択してください";
      }
      else{
        $stampUseDAO = new StampUseDAO();
        $stampUse = new StampUse();
  
        $stampUse -> stamp_id = $stamp_id;
        $stampUse -> email = $email;
  
        $stampUseDAO -> update($email, $stamp_id);
        $msg['success'] = "スタンプが変更されました。";  //変更成功メッセージ表示
      }

    }

?>

<!DOCTYPE html>
<html> 
<head>
<meta charset="utf-8">
<link href="stamp.css" rel="stylesheet" />
<title>スタンプ管理</title>
</head>
<body>
  <?php include "../head/head.php"; ?>  
 <!-- nav -->
    <?php if($user_type == 1): include "../nav/nav_stu.php"; ?>  
    <?php else: include "../nav/nav_tec.php"; ?>  
    <?php endif; ?>

<main class="main-content">  
  <div class="stamp-container">
    <h1>スタンプ管理</h1>
    <form method="POST" action="">
      <div class="form-group">
        <table border="1" align="center" id="myTable">
          <?php
            $colCount = 0;
            echo "<tr>";
            foreach ($stamp_list as $stamp) {
              echo "<td>";
              echo '<label>';
              echo '<input type="radio" name="stamp_id" value="' . htmlspecialchars($stamp->stamp_id) . '">';
              echo '<img src="' . htmlspecialchars($stamp->stamp_image) . '" width="150px">';
              echo '</label>';
              echo "</td>";
              $colCount++;
              
              if ($colCount % 3 === 0) {
                echo "</tr><tr>";
              }
            }
            while ($colCount % 3 !== 0) {
              echo "<td></td>";
              $colCount++;
            }
            echo "</tr>";
          ?>
        </table>
        
        <!-- エラーや変更成功メッセージ表示 -->
        <span class="span" style="color:red"><?= @$errs['stamp']?></span>
        <span class="span" style="color:#246b8f"><?= @$msg['success']?></span>
      </div>
      <button type="submit" id="henko">変更</button>
    </form>
  </div>
  </main>  
</body>
<?php include "../head/background.php"; ?> 
</html>
