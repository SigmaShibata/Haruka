<?php
  require_once '../helpers/MemberDAO.php';

  session_start();

 // ログインしてない時はログインページに戻る
 if(empty($_SESSION['member']) || $_SESSION["member"]->user_type ==1){
      header('Location: ../ログイン/login.php');
        exit;
    }

    // セッション取得
    $member = $_SESSION["member"]; 
    $session_user_type = $member->user_type;


if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $email = $_POST['email'];
    $password = $_POST['password'];
    $password2 = $_POST['password2'];
    $user_name = $_POST['user_name'];
    $user_type = $_POST['user_type'];
    $student_id = $email; // 教員の場合学籍番号はメール

    $memberDAO = new MemberDAO();


    //email exist check
    if($memberDAO->email_exists($email) ===true){
        $errs['email'] = "$email\n はすでに登録されています。";
    }

    //PW不一致
    if($password !== $password2){
        $errs['password'] = 'パスワードが一致しません。';
    }

    if(empty($errs)){
        $member = new Member();
        $member->email = $email;
        $member->password = $password;
        $member->user_name = $user_name;
        $member->user_type = $user_type;
        $member->student_id = $student_id;
        
        $memberDAO->insert($member);
        $msg['success'] = "$email\n が追加されました。";

    }
}

?>

<!DOCTYPE html>
<html> 
<head>
  <meta charset="utf-8">
  <link href="add_teacher.css" rel="stylesheet" />
  <title>アカウント追加</title>
</head>
  <?php include "../head/head.php"; ?>  <!-- header -->
  <!-- nav -->
  <?php if($session_user_type == 1): include "../nav/nav_stu.php"; ?>  
  <?php else: include "../nav/nav_tec.php"; ?>  
  <?php endif; ?>

<body>
  <main class="main-content">  
    <div class="addAccount-container">
      <form method="POST" action="add_teacher.php"> 
        <div>
          <h1>アカウント追加（教員）</h1>
          <p id="ppap">*は入力必須</p>
          <p id="type">ユーザー種別</p>
          <input type="button" onclick="location.href=''" id="teacher_btn" value="教員">
          <input type="button" onclick="location.href='./add_student.php'" id="stu_btn" value="学生">
          <br>
        </div> 
        <br>
        <div class="form-group">
          <label for="user_name">名前<span class="fc">*</span></label>
          <input type="text" id="user_name" name="user_name" value=
          "<?php if(!empty($errs)){echo isset($user_name) ? htmlspecialchars($user_name) : null;}?>" required>
          <!-- エラーの場合のみ入力した名前を残したまま表示 -->
        </div>
        <div class="form-group">
          <label for="email">メールアドレス<span class="fc">*</span></label>
          <input type="email" id="email" name="email" value=
          "<?php if(!empty($errs)){echo isset($email) ? htmlspecialchars($email) : null;}?>" required>
          <!-- エラーの場合のみ入力したメールアドレスを残したまま表示 -->
        </div>
        <div class="form-group">
          <label for="password1">パスワード（6文字以上）<span class="fc">*</span></label>
          <input type="password" id="password" name="password" minlength="6" required>
        </div>
        <div class="form-group">
          <label for="password2">パスワード再入力（6文字以上）<span class="fc">*</span></label>
          <input type="password" id="password2" name="password2" minlength="6" required>
        </div>

         <!-- エラーや登録成功メッセージ表示 -->
        <span class="success <?= !empty($msg['success']) ? 'visible' : '' ?>"><?= @$msg['success']?></span>
        <span class="error <?= !empty($errs['password']) ? 'visible' : '' ?>"><?= @$errs['password'] ?></span>
        <span class="error <?= !empty($errs['email']) ? 'visible' : '' ?>"><?= @$errs['email'] ?></span>
          
        <input type="hidden" name="user_type" value="2"> <!-- 教員のuser_type値 -->
        <button type="submit" id="add_btn" >追加</button>
      </form>
    </div>
  </main>  
</body>
<?php include "../head/background.php"; ?> 
</html>
