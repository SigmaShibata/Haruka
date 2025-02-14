<?php
  require_once '../helpers/MemberDAO.php';
  require_once '../helpers/KadaiDAO.php';
  require_once '../helpers/KadaiAnswerDAO.php';
  require_once '../helpers/KamokuDAO.php';

  session_start();

  if(empty($_SESSION['member'])){
    header('Location: ../login/login.php');
    exit;
  }

  //直接アクセスしたらスタンプシートに
  if ((!isset($_POST['title'])) && (!isset($_SESSION['message'])))  { 
    header('Location: ../stamp_sheet/stamp_sheet.php');
    exit;
}
  $member = $_SESSION['member'];
  $user_type = $member->user_type;

  $kadaiAnswerDAO = new KadaiAnswerDAO();
  $kadaiDAO = new KadaiDAO();
  $email = $member->email;
  $msg = [];


  if($_SERVER['REQUEST_METHOD'] === 'POST'){  //stamp_sheetからの遷移や、提出/取り消しボタン/押したあとの画面表示
      $kamokuDAO = new KamokuDAO();

      $kamoku_id = $_POST['kamoku_id']; 
      $kamoku_name = $kamokuDAO->get_kamokuName($kamoku_id); //科目IDから科目名を取得
      $kadai_week = $_POST['kadai_week'];
      $title = $_POST['title'];


      $_SESSION['kamoku_id'] = $kamoku_id; //PRGのGET用
      $_SESSION['kadai_week'] = $kadai_week;
      $_SESSION['title'] = $title;

      $kadai_id = $kadaiDAO->get_kadai_ID($kamoku_id, $kadai_week, $title); //科目ID、Week,タイトルでKadai ID取得


    if($kadaiAnswerDAO->kadaiAnswer_exist($email, $kadai_id)){ //その学生のこの課題の回答ページもうすでに存在してる場合、DBから情報読み込む
      $kadaiAnswer = new KadaiAnswer();
      
      $kadaiAnswer = $kadaiAnswerDAO->get_kadaiAnswer($email,$kadai_id);
      $source_code = $kadaiAnswer->source_code;
      $answer_status = $kadaiAnswer->answer_status;
      $is_stamp_check = $kadaiAnswer->is_stamp_check;
      $teacher_comment = $kadaiAnswer->teacher_comment;
    }
 

    else{ //提出が存在しない場合
      $answer_status = 1;
      $is_stamp_check = False;
    }

    if (isset($_POST['submit'])){   //提出ボタン押した場合

      if(trim(str_replace("　", "", $_POST['source_code'])) !== ""){  //ソースコード空じゃない場合
        
          $source_code = $_POST['source_code'];
          $answer_status = 2; //提出済に変更
          $is_stamp_check = False;
          $stamp_id = NULL; //スタンプまだ押されてないのでIDない

          
          if($kadaiAnswerDAO->kadaiAnswer_exist($email, $kadai_id) == false){ //回答が存在しない場合、回答をデータベースにinsert

            $kadaiAnswer = new KadaiAnswer();
            $kadaiAnswer->email = $email;
            $kadaiAnswer->kadai_id = $kadai_id;
            $kadaiAnswer->source_code = $source_code;
            $kadaiAnswer->answer_status = $answer_status;
            $kadaiAnswer->is_stamp_check = $is_stamp_check;
            $kadaiAnswer->stamp_id = $stamp_id;
            
            $kadaiAnswerDAO->kadaiAnswer_submit($kadaiAnswer);
            $_SESSION['message'] = "課題が提出されました！";
      
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
          }

          else{  //課題回答がすでに存在してる場合source code回答をアップデート

            $kadaiAnswerDAO->kadaiAnswer_update($email, $kadai_id, $source_code, $answer_status); //回答SourceCode更新
            $_SESSION['message'] = "課題が提出されました！";
      
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
            
          }
      }
      else{
        $answer_status = 1;
        $is_stamp_check = False;
        $msg['err'] = "ソースコードが空です。";
      }
    
    }

    if (isset($_POST['cancelSubmit'])){  //取り消しボタン押した場合

      $answer_status = 1; //取り消しボタン押したら未提出になる
      $source_code = $_POST['source_code'];
      $kadaiAnswerDAO->kadaiSubmit_cancel($email, $kadai_id, $answer_status);

      $kadaiAnswer = $kadaiAnswerDAO->get_kadaiAnswer($email,$kadai_id);
      $is_stamp_check = False;

      $_SESSION['message'] = "提出を取り消しました。";
      
      header("Location: " . $_SERVER['PHP_SELF']);
      exit();
    }

}

elseif($_SERVER['REQUEST_METHOD'] === 'GET'){  // PRGのセッション表示1回

    $kamokuDAO = new KamokuDAO();

    $kamoku_id = $_SESSION['kamoku_id'];
    $kadai_week = $_SESSION['kadai_week'];
    $title = $_SESSION['title'];

    unset($_SESSION['kamoku_id']);
    unset($_SESSION['kadai_week']);
    unset($_SESSION['title']);

    $kadai_id = $kadaiDAO->get_kadai_ID($kamoku_id, $kadai_week, $title);

    $kamoku_name = $kamokuDAO->get_kamokuName($kamoku_id);

    if($kadaiAnswerDAO->kadaiAnswer_exist($email, $kadai_id)){ //その学生のこの課題の回答ページもうすでに存在してる場合、DBから情報読み込む
      $kadaiAnswer = new KadaiAnswer();
      
      $kadaiAnswer = $kadaiAnswerDAO->get_kadaiAnswer($email,$kadai_id);
      $source_code = $kadaiAnswer->source_code;
      $answer_status = $kadaiAnswer->answer_status;
      $is_stamp_check = $kadaiAnswer->is_stamp_check;
      $teacher_comment = $kadaiAnswer->teacher_comment;
    }

    else{ //提出が存在しない場合
      $answer_status = 1;
      $is_stamp_check = False;
    }
}

?>


<!DOCTYPE html>
<link href="kadai_submit.css" rel="stylesheet" />

<html lang="ja">
<head>
    <meta charset="UTF-8">
    <?php include "../head/head.php"; ?>  <!-- header -->
    <title>課題提出</title>
    </head>
<!-- nav -->
          <?php if($user_type == 1): include "../nav/nav_stu.php"; ?>  
          <?php else: include "../nav/nav_tec.php"; ?>  
          <?php endif; ?>
    <body>
        <main class="main-content">  
        <div class="kadaiSubmit-container">
        
        <h1>課題提出</h1>
        <form method="POST" action=""> 
        <div class="input-container">
            <input type="text" id="kamoku" name="kamoku_name"  value="<?=  htmlspecialchars($kamoku_name); ?>" readonly>
            <input type="text" id="week" name="kadai_week" value="<?= htmlspecialchars($kadai_week); ?>"readonly>
            <input type="text" id="title" name="title" value="<?= htmlspecialchars($title); 
            //Stepup課題は★をつける
            if($kadaiDAO->get_kadai_by_kadai_id($kadai_id)->kadai_type_id ==2): 
              echo "★";
            endif;?>" readonly>
            <input type="hidden" name="kamoku_id" value="<?= htmlspecialchars($kamokuDAO->get_kamokuID($kamoku_name)) ?>">
            <input type="hidden" name="title" value="<?= htmlspecialchars($title) ?>">
        </div>
        
        <div class="status-container">
            <p class="te">提出状態:
                  <?php if (isset($answer_status) && $answer_status == 1): ?>
                    <span style="color:red">未提出</span>
                  <?php elseif (isset($answer_status) && $answer_status == 2): ?>
                    <span style="color:green">提出済み</span>
                  <?php else: ?>
                    <span style="color:red">再提出必要</span>
                  <?php endif; ?>       
        

            <p id="su">スタンプ状態:
                  <?php if (isset($is_stamp_check) && $is_stamp_check === False): ?>
                    <span style="color:red">未</span>
                  <?php else: ?>
                    <span style="color:green">スタンプ済み</span>
                  <?php endif; ?> 
        </div>
                  
            <p class="pre">提出ソースコード:</p>

                 <?php if (isset($msg['err'])): ?>
                  <span class="error <?= !empty($msg['err']) ? 'visible' : '' ?>"><?= @$msg['err']?></span>
                <?php endif; ?> 

                <!-- セッションからメッセージを取得して表示 -->
                <?php if (!empty($_SESSION['message'])): ?>
                  <span class="success <?= !empty($_SESSION['message']) ? 'visible' : '' ?>">
                      <?= htmlspecialchars($_SESSION['message']) ?>
                  </span>
                  <?php unset($_SESSION['message']); // メッセージを一度表示したら削除 ?>
                <?php endif; ?>

            <div class="source-comment-container">
              <!-- 提出済み/スタンプ済みの場合ソースコードReadonlyにする -->
                <textarea id="source" name="source_code" rows="35" placeholder="ソースコードを入力"
                  <?php if ((isset($answer_status) && $answer_status == 2) || isset($is_stamp_check) && $is_stamp_check == True): ?>readonly <?php endif; ?>><?= isset($source_code) ? htmlspecialchars($source_code) : '' ?></textarea>
                    
                    <br>
                    <!-- ボタンの表示 -->
                  <?php if ($answer_status == 1 || $answer_status == 3 && $is_stamp_check == False): ?>
                    <input id="teisyutu" name="submit" type="submit" value="提出"><br>
                <?php endif; ?>
                <?php if ($answer_status == 2 && $is_stamp_check == False): ?>
                    <input id="torikeshi" name="cancelSubmit"  type="submit" value="提出取り消し"><br>
                <?php endif; ?>

                  
              <!-- 先生コメント欄表示 -->
                  <div id = "comment">
                    <p class="pre">教員コメント欄:</p>
                    <textarea name="teacher_comment" rows="10" readonly><?= isset($teacher_comment) ? htmlspecialchars($teacher_comment) : '' ?></textarea>
                  </div>
            </div>
          </div>
        </form>
    </body>
    <?php include "../head/background.php"; ?> 
</html>