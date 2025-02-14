<?php
    require_once '../helpers/MemberDAO.php';
    require_once '../helpers/KamokuDAO.php';

    session_start();

    // ログインしてない時はログインページに戻る
    if(empty($_SESSION['member']) || $_SESSION["member"]->user_type ==1){
      header('Location: ../login/login.php'); 
        exit;
    }

    // ログインしているユーザーの情報を取得
    $member = $_SESSION["member"]; 
    $user_type = $member->user_type;

    // すべての科目を取得
    $kamokuDAO = new KamokuDAO();
    $kamoku_list = $kamokuDAO->get_kamoku();


    // 削除ボタンが押された場合の処理
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
        if (!empty($_POST['delete_ids'])) {

            $delete_ids = $_POST['delete_ids'];
            $failed_kamoku = []; // 削除失敗リスト
            $err = [];
            $msg = [];
            
            // 一つずつ削除する
            foreach ($delete_ids as $id) {
                // 削除失敗時、科目名を記録
                if ($kamokuDAO->delete_kamoku_by_id((int)$id) === false) {            
                    $failed_kamoku[] = $kamokuDAO->get_kamokuName((int)$id);
                }
            }

            // 削除後にリストを更新
            $kamoku_list = $kamokuDAO->get_kamoku();

            if (!empty($failed_kamoku)) {
                $err['delete'] = "以下の科目に課題が存在してるか、学生に履修登録されてるため削除できません: " 
                                . implode(", ", $failed_kamoku);
            }

            if(empty($_POST['delete_ids'])){
                $err['delete'] = "科目を選択してください。";
            }

            if(empty($err)) {
                $msg['success'] = "削除が完了しました。";   //削除成功メッセージ表示
            }

        }
        else{
            $err['delete'] = "科目を選択してください。";
        }
    }
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>科目管理リスト</title>
    <link href="kamokuList.css" rel="stylesheet" />
    <script src="w3.js"></script>
</head>
<body>

    <?php include "../head/head.php"; ?> 
    <!-- nav -->
        <?php if($member->user_type == 1): include "../nav/nav_stu.php"; ?>
        <?php else: include "../nav/nav_tec.php"; ?>  
        <?php endif; ?>

    <main class="main-content">  
    <!-- データ表示テーブル -->
    
    <form action="" method="POST" id="delete-form">
        <div class="kamokuList-container">
            <h1>科目管理リスト</h1>


            <table border="1" align="center" class="sorttbl" id="myTable">
                <input type="text" id="searchInput" placeholder="キーワードで検索🔍">
                <script>
                    document.getElementById('searchInput').addEventListener('keyup', function() {
                        let searchValue = this.value.toLowerCase();
                        let tableRows = document.getElementById('myTable').getElementsByTagName('tr');
                        
                        for (let i = 1; i < tableRows.length; i++) {
                            let rowText = tableRows[i].textContent.toLowerCase();
                            if (rowText.indexOf(searchValue) > -1) {
                                tableRows[i].style.display = '';
                            } else {
                                tableRows[i].style.display = 'none';
                            }
                        }
                    });
                </script>
                <!-- エラーメッセージ表示 -->
                <span class="span" style="color:red"><?= @$err['delete']?></span>
                <!-- 削除成功メッセージ表示 -->
                <span class="span" style="color:#246b8f"><?= @$msg['success']?></span>  
        
                <tr>
                    <th width="1%">削除選択</th>
                    <th width="1%">科目変更</th>
                    <th width="5%" onclick="w3.sortHTML('#myTable','.item', 'td:nth-child(1)')">科目名<i class="fa fa-sort"></i></th>
                </tr>
                <!-- 科目情報を取得 -->
                <?php foreach ($kamoku_list as $kamoku): ?>
                <tr>
                    <td><input type="checkbox" class="checks" name="delete_ids[]" value="<?= htmlspecialchars($kamoku->kamoku_id) ?>"></td>
                    <td><a href="../change_subject/kamoku_change.php?kamoku_id=<?= htmlspecialchars($kamoku->kamoku_id) ?>" class="kamokuchange-link">変更</a></td>
                    <td align="center"><?= htmlspecialchars($kamoku->kamoku_name) ?></td>
                </tr>
                <?php endforeach; ?>
            </table>

            <input id="sakujo" type="submit" name="delete" value="削除" onclick="return confirm_test()">
        </div>
    </form>
    
    </main>  
</body>
<?php include "../head/background.php"; ?> 
</html>