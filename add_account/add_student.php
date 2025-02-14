<?php
require_once '../helpers/MemberDAO.php';

session_start();

 // loginしてない時はloginページに戻る
 if(empty($_SESSION['member']) || $_SESSION["member"]->user_type ==1){
      header('Location: ../login/login.php');
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
    $student_id = $_POST['student_id'];

    $memberDAO = new MemberDAO();


    //email exist check
    if($memberDAO->email_exists($email) ===true){
        $errs['email'] = "{$email} はすでに登録されています。";
    }

    //学籍番号チェック
      if($memberDAO->studentId_exists($student_id) ===true){
        $errs['stuID'] = "学籍番号 {$student_id} はすでに登録されています。";
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
        $msg['success'] = "{$email} が追加されました。";

    }
    
}


?>

<!DOCTYPE html>
<html> 
<head>
  <meta charset="utf-8">
  <link href="add_student.css" rel="stylesheet" />
  <title>アカウント追加</title>
</head>
  <?php include "../head/head.php"; ?>  <!-- header -->
  <!-- nav -->
  <?php if($session_user_type == 1): include "../nav/nav_stu.php"; ?>  
  <?php else: include "../nav/nav_tec.php"; ?>  
  <?php endif; ?>
<body>
  <main class="main-content">  
    <div class="stuAddAccount-container">
      <form method="POST" action="add_student.php"> 
        <div>
          <h1>アカウント追加（学生）</h1>
          <p id="ppap">*は入力必須</p>
          <p id="type">ユーザー種別</p>
          <input type="button" onclick="location.href='./add_teacher.php'" id="teacher_btn" value="教員">
          <input type="button" onclick="location.href=''" id="stu_btn" value="学生">
          <br>
        </div>
        <br>
        <div class="form-group">
          <label for="student_id">学籍番号<span class="fc">*</span></label>
          <input type="text" id="student_id" name="student_id" value=
          "<?php if(!empty($errs)){echo isset($student_id) ? htmlspecialchars($student_id) : null;}?>"  required> 
          <!-- エラーの場合のみ入力した学籍番号を残したまま表示 -->
        </div>

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

        <?php if(isset($msg['success'])): ?>
          <span class="success <?= !empty($msg['success']) ? 'visible' : '' ?>"><?= @$msg['success']?></span> <!-- エラーや登録成功メッセージ表示 -->
        <?php endif; ?>
        <span class="error <?= !empty($errs['password']) ? 'visible' : '' ?>"><?= @$errs['password'] ?></span>
        <span class="error <?= !empty($errs['email']) ? 'visible' : '' ?>"><?= @$errs['email'] ?></span>
        <span class="error <?= !empty($errs['stuID']) ? 'visible' : '' ?>"><?= @$errs['stuID'] ?></span>
      
        <input type="hidden" name="user_type" value="1"> <!-- 学生のuser_type値 -->
        <button type="submit" id="add_btn">追加</button>
      </form>
    </div>
  </main>  
</body>
<?php include "../head/background.php"; ?> 
</html>
