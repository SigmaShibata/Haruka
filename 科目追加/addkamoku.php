<?php
    require_once '../helpers/KamokuDAO.php';
    require_once '../helpers/MemberDAO.php';

    session_start();
    
    // ログインしてない時はログインページに戻る
    if(empty($_SESSION['member']) || $_SESSION["member"]->user_type ==1){
      header('Location: ../ログイン/login.php');
        exit;
    }

    // セッション取得
    $member = $_SESSION["member"]; 
    $user_type = $member->user_type;

    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        $kamoku_name = isset($_POST['kamoku_name']) ? trim(str_replace('　', '', $_POST['kamoku_name'])) : null;

        if (empty($kamoku_name)) {
          $errs['input'] = "科目名を入力してください";
        }

        $kamokuDAO = new KamokuDAO();
        if(!empty($kamoku_name) && $kamokuDAO -> kamoku_exists($kamoku_name)){
          $errs['kamoku'] = "$kamoku_name\nはすでに登録されています。";   //エラーメッセージ表示
        }

        if(empty($errs)){
          $kamoku = new Kamoku();
          $kamoku -> kamoku_name = $kamoku_name;
  
          $kamokuDAO -> insert($kamoku);
          $msg['success'] = "$kamoku_name\n が追加されました。";  //登録成功メッセージ表示
        }

    }
?>


<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><link href="./addkamoku.css" rel="stylesheet">
  <title>科目の追加</title>
</head>
<body>
  <?php include "../head/head.php"; ?> 
<!-- nav -->
    <?php if($user_type == 1): include "../nav/nav_stu.php"; ?>  
    <?php else: include "../nav/nav_tec.php"; ?>  
    <?php endif; ?>
    
  <main class="main-content">  
      <div class="addKamoku-container">
        <h1>科目追加</h1>
        <form method="POST" action="">
          <div class="yokonarabe">
            <h3>科目名</h3>
            <input type="text" id="kamoku_name" name="kamoku_name" value="<?= htmlspecialchars($kamoku_name ?? '') ?>" maxlength="25">      
          </div>

            <!-- エラーや登録成功メッセージ表示 -->
            <span class="error <?= !empty($errs['input']) ? 'visible' : '' ?>"><?= @$errs['input']?></span>
            <span class="error <?= !empty($errs['kamoku']) ? 'visible' : '' ?>"><?= @$errs['kamoku']?></span>
            <span class="success <?= !empty($msg['success']) ? 'visible' : '' ?>"><?= @$msg['success']?></span>
            
          <input id="tuika" type="submit" value="追加">
          
          </form>
      </div>

      <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('form');
            form.addEventListener('submit', function () {
                // 禁用提交按钮，防止重复提交
                form.querySelector('input[type="submit"]').disabled = true;
            });
        });
    </script>
  </main>  
</body>
<?php include "../head/background.php"; ?> 
</html>