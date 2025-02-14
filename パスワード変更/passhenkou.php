<?php
    require_once '../helpers/MemberDAO.php';

    $email = '';
    $errs = [];
    $msg = [];

    session_start();

    // セッション取得
    $member = $_SESSION["member"]; 
    $user_type = $member->user_type;
    $is_initial_login = $member->is_initial_login;

    //ログインしてない時はログインページに戻る
    if(empty($_SESSION["member"])){
      header('Location: ../ログイン/login.php'); 
      exit;
    }

    if($_SERVER['REQUEST_METHOD'] === 'POST'){ //変更ボタン押したら
        
        $email = $member->email; //セッションからメールアドレス取得
        $password = $_POST['password'];
        $password2 = $_POST['password2'];

        if($password !== $password2){
          $errs['password'] = 'パスワードが一致しません。';
      }
     
        if(empty($errs)){
            $memberDAO = new MemberDAO();
            $memberDAO->changePassword($email, $password);
            $msg['success'] = 'パスワードが変更されました。';

            session_regenerate_id(true);
            $_SESSION["member"] = $memberDAO->get_member($email, $password);
            $changed_flag = true;
        }
        
    }
?>
<!DOCTYPE html>
<html>
  <head>
    <!-- <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"> -->
    <link href="./passhenkou_files/passhenkou.css" rel="stylesheet">
    <title>パスワード変更</title>
</head>

<body>

<?php include "../head/head.php"; ?>  <!-- header -->

<!-- 初回PW変更後Nav表示 -->
<?php if(($is_initial_login === False) || isset($changed_flag)): ?>
        <?php if($member->user_type == 1): include "../nav/nav_stu.php"; ?>
        <?php else: include "../nav/nav_tec.php"; ?>  
        <?php endif; ?>
<?php endif;?> 

  <div class="login-container">

     <div class="character-container"> 
       <div class="character">
        <div class="pwbody">
          <img src="./passhenkou_files/hatenacat.png" alt="キャラ" class="jeccat"> 
        </div>
      </div> 
    </div>
    
   
    <h1>パスワード変更</h1>
    <form method="POST" action=""> 
      <div class="form-group">
        <input type="password" id="password" name="password" placeholder="新パスワード入力" required="" minlength="6"><br>
      </div>
      <div class="form-group">
        <input type="password" id="password" name="password2" placeholder="新パスワード再入力" required="" minlength="6"><br>
        <span style="color:red"><?= @$errs['password'] ?></span></td>
        <span style="color:#246b8f"><?= @$msg['success']?></span></td>
      </div>
      <button type="submit" id="loginbutton">変更</button>
    </form>
    
  </div>

  </body>
  </html>