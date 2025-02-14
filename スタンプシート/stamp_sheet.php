<?php 

require_once '../helpers/MemberDAO.php';
require_once '../helpers/KadaiAnswerDAO.php';
require_once '../helpers/KadaiDAO.php';
require_once '../helpers/KamokuDAO.php';
require_once '../helpers/UserKamokuDAO.php';

session_cache_limiter('private_no_expire'); //フォーム再送信なくせるけどデータ更新されない？
session_start();

// ログインしてない時はログインページに戻る
if (empty($_SESSION["member"])) {
   header('Location: ../ログイン/login.php');
   exit;
}

// セッション取得
$member = $_SESSION["member"];
$user_type = $member->user_type;

$session_email = $member->email; //セッションからメール取得

//先生の場合は課題進捗ページから学生のメール取得、学生の場合はセッションからメール取得
$stu_email = (isset($_GET['stu_email'])&&($user_type == 2)) ? $_GET['stu_email'] : $session_email; 


$KadaiAnswerDAO =  new KadaiAnswerDAO();
$kadaiDAO = new KadaiDAO();
$kamokuDAO = new KamokuDAO();
$UserKamokuDAO = new UserKamokuDAO();
$MemberDAO = new MemberDAO();

$kamoku_list = $UserKamokuDAO->get_userKamoku_by_email($stu_email);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kamoku_name'])) {

   if($kamoku_list){ //ドロップダウンで選んだ科目ID取得
      $kamoku_id = isset($_POST['kamoku_name']) ? (int)$_POST['kamoku_name'] : $kamoku_list[0]->kamoku_id;
   }

} elseif (isset($_GET['kamoku_id'])) { //学生進捗一覧ページから科目IDと学生メール取得
   $kamoku_id = (int)$_GET['kamoku_id'];

} 
else {
   $kamoku_id = $kamoku_list[0]->kamoku_id ?? null; // デフォルト値
}

?>

<!DOCTYPE html>
<html>

<head>
   <meta charset="utf-8">
   <link href="stamp_sheet.css" rel="stylesheet" />
   <title>スタンプシート</title>
</head>
<body>
<!-- <script src="w3.js"></script> -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<!-- header -->
<?php include "../head/head.php"; ?> 
<!-- nav -->
<?php if ($user_type == 1):
   include "../nav/nav_stu.php"; ?>
<?php else:
   include "../nav/nav_tec.php"; ?>
<?php endif; ?>

<main class="main-content">
   <div class="sutamp-container">
      <h1>JECSheet</h1>
               
      <script>   //ドロップダウン選択したら表示更新
      $(function(){
         $("#sub").change(function(){
            $("#subjects-form").submit();
         });
      });
      </script>

      <!-- 先生の場合、スタンプシートの持ち主の学籍番号と名前表示 -->
      <div class="stu_name">
         <?php if ($user_type == 2): ?>
            <?= htmlspecialchars($MemberDAO->get_member_by_email($stu_email)->student_id); ?> <?= htmlspecialchars($MemberDAO->get_member_by_email($stu_email)->user_name); ?>
         <?php endif;?>
      </div>
      
      <!-- 科目ドロップダウンフィルタフォーム -->
      <form action="" method="POST" id="subjects-form">  
         <select name="kamoku_name" id="sub">
            <?php foreach ($kamoku_list as $kamoku): ?>
               <option value="<?= htmlspecialchars($kamoku->kamoku_id) ?>" <?= $kamoku_id == $kamoku->kamoku_id ? 'selected' : '' ?>>
                  <?= htmlspecialchars($kamoku->kamoku_name) ?>
               </option>
            <?php endforeach; ?>
         </select>
      </form>
      
      <br><p class="stepup">★ StepUp課題</p>
  
<script>
    // hiddenのPOST情報を持って課題提出ページや課題チェックページへ遷移
    function hiddenPost(kadaiId) {
        const form = document.getElementById(kadaiId); 
        if (form) {
            form.submit();
        } 
    }
</script>

<!-- <script>
    // キャッシュされたページが表示されないようにするやつ
    if (performance.navigation.type === 2) {
        location.reload(true);
    }
</script> -->

      <?php  
      if($kamoku_list): //学生が1科目以上履修登録されてる場合
         if($kadaiDAO->get_kadai_with_type_by_kamoku_id($kamoku_id)):   //選択された科目に課題存在してればWeek表示   

            $total_week = 15;  //表示するWeek数

            //ループでWeek取得
            for ($week_no = 1; $week_no < $total_week + 1; $week_no++):
               $week = sprintf('Week%02d', $week_no); //1を01に変更

               $kadai_list_by_kamoku_week = [];
               $kadai_list_by_kamoku_week = $kadaiDAO->get_kadai_by_kamoku_week($kamoku_id, $week); //そのWeekの課題取得

               $max_col = 5; // 一行表示する課題数
               $col_idx = 0;?>

               <h3> <?= htmlspecialchars($week) ?> </h3>
               <?php if($kadai_list_by_kamoku_week): //そのWeekに課題存在してればテーブル表示?>

                  <table id="test-table" border="1" align="center" width="20%">
                     <tr>
                        <?php foreach ($kadai_list_by_kamoku_week as $kadai): //そのWeekの課題表示
                           
                           $kadai_id = htmlspecialchars($kadai['kadai_id']);
                           $title = $kadai['title'];

                           $stuPost = "stuPost_{$kadai_id}";
                           $teacherPost = "teacherPost_{$kadai_id}";?>

                           <!-- hiddenで課題提出や課題チェックページにPOSTする -->
                           <form id="<?= $stuPost ?>" action="../課題提出/kadai_submit.php" method="POST">
                              <input type="hidden" name="kamoku_id" value="<?= htmlspecialchars($kamoku_id) ?>">
                              <input type="hidden" name="kadai_week" value="<?= htmlspecialchars($week) ?>">
                              <input type="hidden" name="title" value="<?= htmlspecialchars($title) ?>">
                              <input type="hidden" name="stu_email" value="<?= htmlspecialchars($stu_email) ?>">
                           </form>

                           <form id="<?= $teacherPost ?>" action="../課題チェック_再提出要求/kadai_check.php" method="POST">
                              <input type="hidden" name="kamoku_id" value="<?= htmlspecialchars($kamoku_id) ?>">
                              <input type="hidden" name="kadai_week" value="<?= htmlspecialchars($week) ?>">
                              <input type="hidden" name="title" value="<?= htmlspecialchars($title) ?>">
                              <input type="hidden" name="stu_email" value="<?= htmlspecialchars($stu_email) ?>">
                           </form>
                           

                           <td> <!-- 箱を課題で埋める -->

                           <!--学生の場合、課題提出ページへ  -->
                           <?php if ($user_type == 1): // ?>
                              <a href="#" onclick="event.preventDefault(); hiddenPost('<?= $stuPost ?>') " >
                           <!--先生の場合、課題チェックページへ-->
                           <?php else: ?>
                              <a href="#" onclick="event.preventDefault(); hiddenPost('<?= $teacherPost ?>') " >
                           <?php endif;?> 

                           <!--課題タイトル表示, StepUpは★付ける-->
                           <?php if($kadai['kadai_type_id'] ==2): ?> 
                              <?=htmlspecialchars($title)."★"?> 
                              <?php else: ?>
                              <?=htmlspecialchars($title)?> 
                           <?php endif; ?>              

                           <!--課題回答が存在してる場合、回答状態によって画像や文字表示-->
                           <?php
                           if ($KadaiAnswerDAO->kadaiAnswer_exist($stu_email, $kadai_id) === True):
                              $kadaiAnswerInfo = $KadaiAnswerDAO->get_kadaiAnswer($stu_email, $kadai_id);
                              $answer_status = $kadaiAnswerInfo->answer_status;
                              $is_stamp_check = $kadaiAnswerInfo->is_stamp_check;

                              if ($is_stamp_check === False): ?>
                                 <img src="./sheet.file/white.png" class="cell"> 

                                 <?php if ($answer_status == 2): ?>
                                    
                                    提出済
                                 <?php elseif ($answer_status == 3): ?>
                                    <span style="color:red">再提出必要</span>
                                 <?php else: ?>
                                    未提出
                                 <?php endif; ?>

                              <?php else:
                                 $stamp_id = $kadaiAnswerInfo->stamp_id;
                                 $stamp_date = $kadaiAnswerInfo->stamp_date; ?>
                                 <img src="../スタンプ管理画面/image_<?= htmlspecialchars($stamp_id) ?>.png" class="cell">
                                 <span style="color:green"><?= htmlspecialchars($stamp_date)?> 完</span>
                              <?php endif; ?>

                           <?php else: ?>
                              <img src="./sheet.file/white.png" class="cell">
                              未提出
                           <?php endif; ?>

                           </a>

                           </td>
                           <?php 
                           $col_idx++; //箱1個埋めたらカウント+1

                           if ($col_idx % $max_col == 0): //5カラム度に改行 ?>
                              </tr>
                                 <?php if ($col_idx < count($kadai_list_by_kamoku_week)): // 次の課題がある場合、新しい行作る ?>
                              <tr>
                              <?php endif; ?>
                           <?php endif; ?>
                        <?php endforeach; //そのWeekの課題表示End

                        if ($col_idx % $max_col != 0):             
                           while ($col_idx % $max_col != 0): ?> <!-- 行の残りを空の箱で埋める -->
                              <td></td> 
                              <?php $col_idx++; ?> <!--箱1個埋めたらカウント+1  -->
                           <?php endwhile; ?>

                        </tr> <!-- 最後の行を閉じる -->
                        <?php endif; ?>
                  </table>
               
               <?php else: ?>  <!-- そのWeek課題ない場合メッセージ表示 -->
                  <p style="font-size: 16px; text-align: center;">表示できる課題がありません。</p>
               <?php endif; ?> 

            <?php endfor; //WeekのForループ ?>
         
         <?php else:?>  <!-- その科目に課題存在しない場合 -->
            <br>
            <img src="./sheet.file/ぬるぽきゃっと.png" class="empty_kadai" size="100%"><br>
            <p style="font-size: 28px; text-align:center;">まだ課題が追加されてません。</p>
         <?php endif; ?>  

      <?php else:?> <!-- 学生が1科目も履修登録されてない場合 -->
         <img src="./sheet.file/ぬるぽきゃっと.png" class="empty_kadai" size="100%"><br>
         <p style="font-size: 28px; text-align:center;">履修科目が割り当てられていません。</p>
      <?php endif;?>

   </div>
      </form>
   <?php include "../head/background.php"; ?> 
</main>

</body>
<!-- 晩ごはんは松屋 -->
</html>