<?php
  require_once '../helpers/MemberDAO.php';
  require_once '../helpers/KadaiDAO.php';
  require_once '../helpers/KadaiAnswerDAO.php';
  require_once '../helpers/KamokuDAO.php';
  require_once '../helpers/StampUseDAO.php';

  session_start();

  if(empty($_SESSION['member']) || $_SESSION["member"]->user_type ==1){
    header('Location: ../ログイン/login.php');
    exit;
  }

  //直接アクセスしたら課題チェックリストに
  if ((!isset($_POST['title'])) && (!isset($_SESSION['message']))) {
    header('Location: ../課題チェックリスト/kadaiChecklist.php');
    exit;
  }

  // セッション取得
  $member = $_SESSION["member"]; 
  $user_type = $member->user_type;

  $kadaiAnswerDAO = new KadaiAnswerDAO();
  $kadaiDAO = new KadaiDAO();
  $memberDAO = new MemberDAO();
  
  $teacher_email= $member->email; //先生のメール取得

  $msg = [];

  if($_SERVER['REQUEST_METHOD'] === 'POST'){  //スタンプシートからの遷移や、再提出要求/スタンプOK/コメント提出ボタン押したあとの画面表示
      $kamokuDAO = new KamokuDAO();

      $kamoku_id = $_POST['kamoku_id']; 
      $kamoku_name = $kamokuDAO->get_kamokuName($kamoku_id); //科目IDから科目名を取得
      $kadai_week = $_POST['kadai_week'];
      $title = $_POST['title'];
      $stu_email = $_POST['stu_email'];

      
      $member = $memberDAO->get_member_by_email($stu_email);
      $student_id= $member->student_id;
      
      $_SESSION['kamoku_id'] = $kamoku_id; //PRGのGET用
      $_SESSION['kadai_week'] = $kadai_week;
      $_SESSION['title'] = $title;
      $_SESSION['stu_email'] = $stu_email;

      $kadai_id = $kadaiDAO->get_kadai_ID($kamoku_id, $kadai_week, $title); //科目ID、Week,タイトルでKadai ID取得

      if($kadaiAnswerDAO->kadaiAnswer_exist($stu_email, $kadai_id)){ //その学生のこの課題の回答ページもうすでに存在してる場合、DBから情報読み込む
        $kadaiAnswer = new KadaiAnswer();
        
        $kadaiAnswer = $kadaiAnswerDAO->get_kadaiAnswer($stu_email,$kadai_id);
        $source_code = $kadaiAnswer->source_code;
        $answer_status = $kadaiAnswer->answer_status;
        $is_stamp_check = $kadaiAnswer->is_stamp_check;
        $teacher_comment = $kadaiAnswer->teacher_comment;
      }

      else{ //提出が存在しない場合
        $answer_status = 1;
        $is_stamp_check = False;
      }

      if (isset($_POST['resubmit'])){   //再提出要求ボタン押した場合

              $answer_status = 3; //再提出要求に変更
              $is_stamp_check = False;
              $stamp_date = "";

              $kadaiAnswerDAO->kadaiAnswer_resubmit($stu_email, $kadai_id,  $answer_status, $is_stamp_check, $stamp_date);
              $_SESSION['message'] = "再提出要求が完了しました。";

              header("Location: " . $_SERVER['PHP_SELF']);
              exit();
      
      }

      if (isset($_POST['stampOK'])){  //スタンプOKボタン押した場合

        $stampUseDAO = new StampUseDAO();
        $answer_status = 2; //提出済み
        $is_stamp_check = True; //スタンプ済みに
        $stamp_id = $stampUseDAO->get_stampID_by_email($teacher_email); //この先生が使ってるスタンプID取得

        $stamp_date = (new DateTime())->format("y/m/d"); //日付取得

        $kadaiAnswerDAO->kadaiAnswer_stampOK($stu_email, $kadai_id, $answer_status, $is_stamp_check, $stamp_id, $stamp_date); //スタンプ済みに更新
        $_SESSION['message'] = "スタンプOKしました。";

        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
      }

      if (isset($_POST['comment_btn'])){  //コメント送信押した場合
        $teacher_comment = $_POST['teacher_comment'];
        $kadaiAnswerDAO->comment_update($stu_email,$kadai_id,$teacher_comment); //スタンプ済みに更新

        $_SESSION['message'] = "コメントを送信しました";

        header("Location: " . $_SERVER['PHP_SELF']);
        exit();

      }

}

elseif($_SERVER['REQUEST_METHOD'] === 'GET'){  // PRGのセッション表示1回

  $kamokuDAO = new KamokuDAO();

  $kamoku_id = $_SESSION['kamoku_id'];
  $kadai_week = $_SESSION['kadai_week'];
  $title = $_SESSION['title'];
  $stu_email= $_SESSION['stu_email'];

  unset($_SESSION['kamoku_id']);
  unset($_SESSION['kadai_week']);
  unset($_SESSION['title']);
  unset($_SESSION['stu_email']);
  
  $member = $memberDAO->get_member_by_email($stu_email);
  $student_id= $member->student_id;
  $kadai_id = $kadaiDAO->get_kadai_ID($kamoku_id, $kadai_week, $title);
  $kamoku_name = $kamokuDAO->get_kamokuName($kamoku_id);

  if($kadaiAnswerDAO->kadaiAnswer_exist($stu_email, $kadai_id)){ //その学生のこの課題の回答ページもうすでに存在してる場合、DBから情報読み込む
    $kadaiAnswer = new KadaiAnswer();
    
    $kadaiAnswer = $kadaiAnswerDAO->get_kadaiAnswer($stu_email,$kadai_id);
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
<link href="kadai_check.css" rel="stylesheet" />

<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>課題チェック</title>
    <?php include "../head/head.php"; ?>  <!-- header -->
    </head>
<!-- nav -->
    <?php if($user_type == 1): include "../nav/nav_stu.php"; ?>  
    <?php else: include "../nav/nav_tec.php"; ?>  
    <?php endif; ?>

    <body>
    <main class="main-content">  

        <div class="kadaiCheck-container">
        <h1>課題チェック</h1>
            <form method="POST" action=""> 
            <div class="input-container">
              <input type="text" id="student_id" name="student_id" value="<?= htmlspecialchars($student_id); ?>" readonly>
              <input type="text" id="kamoku" name="kamoku_name" value="<?= htmlspecialchars($kamoku_name); ?>" readonly>
              <input type="text" id="week" name="kadai_week" value="<?= htmlspecialchars($kadai_week); ?>" readonly>
              <input type="text" id="title" name="title" value="<?= htmlspecialchars($title); 
               //Stepup課題は★をつける
              if($kadaiDAO->get_kadai_by_kadai_id($kadai_id)->kadai_type_id ==2): 
                echo "★";
              endif;?>" readonly>
              <input type="hidden" name="kamoku_id" value="<?= htmlspecialchars($kamokuDAO->get_kamokuID($kamoku_name)) ?>">
              <input type="hidden" name="stu_email" value="<?= htmlspecialchars($stu_email) ?>">
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
                  <?php if ($is_stamp_check === False): ?>
                    <span style="color:red">未</span>
                  <?php else: ?>
                    <span style="color:green">スタンプ済み</span>
                  <?php endif; ?> 
          </div>

          
            <p class="pre">提出ソースコード:</p>
            
            <!-- セッションからメッセージを取得して表示 -->
            <?php if (!empty($_SESSION['message'])): ?>
                  <span class="msg <?= !empty($_SESSION['message']) ? 'visible' : '' ?>">
                      <?= htmlspecialchars($_SESSION['message']) ?>
                  </span>
                  <?php unset($_SESSION['message']); // メッセージを一度表示したら削除 ?>
                <?php endif; ?>

            <div class="source-comment-container">
                <form method="POST" action="">
                    <textarea id="source" name="source_code" rows="35" placeholder="ソースコードを入力" readonly><?= isset($source_code) ? htmlspecialchars($source_code) : '' ?></textarea>

                    <!-- 課題が未提出の場合はスタンプOKや再提出押せないように -->
                      <input id="saiteisyutu" name="resubmit" type="submit" value="再提出要求" <?php if ($answer_status == 1): ?> disabled <?php endif;?> >
                      <input id="okok" name="stampOK" type="submit" value="スタンプOK" <?php if ($answer_status == 1): ?> disabled <?php endif;?> >

                        <!-- 課題が完全未提出の場合はコメント欄隠す -->
                          <div id="comment">
                            <?php if ($answer_status != 1): ?>
                              <p class="pre">教員コメント欄:</p>
                              <textarea name="teacher_comment" rows="10" placeholder="学生へのアドバイスやコメント"><?= isset($teacher_comment) ? htmlspecialchars($teacher_comment) : '' ?></textarea>
                              <span class="success <?= !empty($msg['cmt']) ? 'visible' : '' ?>"><?= @$msg['cmt']?></span>
                              <input id="comment_btn" name="comment_btn" type="submit" value="送信">
                            <?php endif;?>
                          </div>     
                        
            </div>
            
        </div>
         </form>
      </main>  
    </body>
    <?php include "../head/background.php"; ?> 
</html>