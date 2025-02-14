<?php
    require_once '../helpers/MemberDAO.php';

    $email = '';
    $errs = [];

    session_start();

    if(!empty($_SESSION["member"])){
      $member = $_SESSION["member"]; //セッション取得

      if($member->is_initial_login === true){
        header('Location: ../パスワード変更/passhenkou.php'); //初期パスワード変更に
        exit;
      }

      elseif($member->user_type == 1){
        header('Location: ../スタンプシート/stamp_sheet.php'); //学生ページに
        exit;
      }

      else{
        header('Location: ../課題チェックリスト/kadaiChecklist.php'); //先生ページに
        exit;
      }    
    }

    if($_SERVER['REQUEST_METHOD'] === 'POST'){

        $email = $_POST['email'];
        $password = $_POST['password'];
     
        if(empty($errs)){
            $memberDAO = new MemberDAO();
            $member = $memberDAO->get_member($email, $password);
    
            if($member !== false){
                session_regenerate_id(true);
    
                $_SESSION["member"] = $member;
                
                if($member->is_initial_login === true){
                  header('Location: ../パスワード変更/passhenkou.php'); //初期パスワード変更ページに
                  exit;
                }
                
                elseif ($member->user_type == 1){
                    header('Location: ../スタンプシート/stamp_sheet.php'); //学生ページに
                    exit;
                }
                else{
                    header('Location: ../課題チェックリスト/kadaiChecklist.php'); //先生ページに
                    exit;
                }
            }
            else{
                $errs['password'] = 'メールアドレスまたは<br>パスワードに誤りがあります。';
            }
        }
        
    }
?>

<!DOCTYPE html>
<!-- saved from url=(0101)http://localhost/%e5%8d%92%e6%a5%ad%e5%88%b6%e4%bd%9c/%e3%83%ad%e3%82%b0%e3%82%a4%e3%83%b3/login.html -->
<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><link href="./login_files/login.css" rel="stylesheet">
<title>ログイン</title>
</head>
<body>
  <div id="hed">
    <?php include "../head/head2.php"; ?>  <!-- header -->
  </div>

<div class="login-container">
     <div class="character-container"> 
       <div class="character">
        <div class="body">
          <img src="./login_files/jeccatosuwari.png" alt="キャラ" class="jeccat"> 
        </div>
      </div> 
    </div>
    
    <h1>JECSheet</h1>
    <form method="POST" action=""> 
      <div class="form-group">
        <input type="email" id="email" name="email" placeholder="メールアドレス" required="">
      </div>
      <div class="form-group">
        <input type="password" id="password" name="password" placeholder="パスワード" minlength="6"required="">
      </div>
      

      <button type="submit" id="loginbutton">ログイン</button>
    </form>
    <div class="errorpass">
      <br>
    <span style="color:red"><?= @$errs['password'] ?></span></td>
     </div>
  </div>
 
</body></html>
