<?php
    require_once '../helpers/MemberDAO.php';
    require_once '../helpers/KamokuDAO.php';
    require_once '../helpers/KadaiDAO.php';
    require_once '../helpers/MondaiTypeDAO.php';

    // session_cache_limiter('private_no_expire'); //フォーム再送信なくせるけどデータ更新されない
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

    // すべてのmondaiType_idを取得
    $mondaiTypeDAO = new MondaiTypeDAO();
    $mondaiType_list = $mondaiTypeDAO->get_mondai_type();

    // 科目IDによってフィルタリング
    $selected_kamoku_id = isset($_POST['subjects']) && $_POST['subjects'] !== '' ? (int)$_POST['subjects'] : null;

    // kadaiDAOをインスタンス
    $kadaiDAO = new KadaiDAO();


    $kadai_list_by_kamoku = [];

    if ($selected_kamoku_id !== null) {
        // 科目に基づき課題を取得
        $kadai_list_by_kamoku = $kadaiDAO->get_kadai_with_type_by_kamoku_id($selected_kamoku_id);
    }else {
        // 科目が選択しなかったときの課題を取得
        $kadai_list_by_kamoku = $kadaiDAO->get_kadai_with_type_by_kamoku_id();
    }

    // 削除ボタンが押された場合の処理
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
        if (!empty($_POST['delete_ids'])) {

            $delete_ids = $_POST['delete_ids'];
            $failed_kadai = []; // 削除失敗リスト
            $err = [];
            $msg = [];
            
            // 一つずつ削除する
            foreach ($delete_ids as $id) {
                // 削除失敗時、科目名を記録
                if ($kadaiDAO->delete_kadai_by_id((int)$id) === false) {
                    $failed_kadai[] = $kadaiDAO->get_kadai_by_kadai_id((int)$id)->title;
                }
            }

            // 削除後にリストを更新
            if ($selected_kamoku_id !== null) {
                $kadai_list_by_kamoku = $kadaiDAO->get_kadai_with_type_by_kamoku_id($selected_kamoku_id);
            } else {
                $kadai_list_by_kamoku = $kadaiDAO->get_kadai_with_type_by_kamoku_id();
            }

            if (!empty($failed_kadai)) {
                $err['delete'] = "以下の課題に学生の課題提出が存在してるため削除できません: " 
                                . implode(", ", $failed_kadai);
            }

            if(empty($err)) {
                $msg['success'] = "削除が完了しました。";   //削除成功メッセージ表示
            }
        }
        else{
            $err['delete'] = "課題を選択してください。";
        }
    }
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>課題管理リスト</title>
    <link href="kadaiList.css" rel="stylesheet" />
    <script src="w3.js"></script>
</head>
<?php include "../head/head.php"; ?> 
    <!-- nav -->
        <?php if($member->user_type == 1): include "../nav/nav_stu.php"; ?>
        <?php else: include "../nav/nav_tec.php"; ?>  
        <?php endif; ?>

<body>
    <main class="main-content">  
    <div class="kadaiList-container">
    <h1>課題管理リスト</h1>

        <!-- フィルタフォーム -->
        <form action="" method="POST" id="subjects-form" class="form-inline">
            <select name="subjects" id="sub" onchange="document.getElementById('subjects-form').submit();">
                <option value="" <?= empty($selected_kamoku_id) ? 'selected' : '' ?>>すべての科目</option>
                <?php foreach ($kamoku_list as $kamoku): ?>
                    <option value="<?= htmlspecialchars($kamoku->kamoku_id) ?>" <?= $selected_kamoku_id == $kamoku->kamoku_id ? 'selected' : '' ?>>
                        <?= htmlspecialchars($kamoku->kamoku_name) ?>
                    </option>
                <?php endforeach; ?>
            </select>

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
        </form>

        <!-- エラーメッセージ表示 -->
        <span class="span" style="color:red"><?= @$err['delete']?></span>
        <!-- 削除成功メッセージ表示 -->
        <span class="span" style="color:#246b8f"><?= @$msg['success']?></span>

        <!-- データ表示テーブル -->
        <form action="" method="POST" id="delete-form">
            <input type="hidden" name="subjects" value="<?= $selected_kamoku_id === null ? '' : htmlspecialchars($selected_kamoku_id) ?>">
            <table border="1" align="center" class="sorttbl" id="myTable">
                <tr>
                    <th width="10%">削除選択</th>
                    <th width="10%">課題変更</th>
                    <th width="10%" onclick="w3.sortHTML('#myTable','.item', 'td:nth-child(1)')">Week<i class="fa fa-sort"></i></th>
                    <th width="20%" onclick="w3.sortHTML('#myTable','.item', 'td:nth-child(2)')">課題タイトル<i class="fa fa-sort"></i></th>
                    <th width="12%">課題タイプ</th>

                    <?php if (empty($selected_kamoku_id)): ?>  <!-- すべての科目を選択された時科目名欄表示 -->
                    <th width="50%">科目</th>
                    <?php endif; ?>
                </tr>

                <?php if (empty($kadai_list_by_kamoku)): ?>
                    <tr>
                        <td colspan="6" align="center">課題がありません。</td>
                    </tr>
                
                <?php else: ?>
                
                    <!-- 課題情報を取得 -->
                <?php foreach ($kadai_list_by_kamoku as $kadai): 
                    $kadai_id = htmlspecialchars($kadai['kadai_id']);

                    $kamoku_id = htmlspecialchars($kadaiDAO->get_kadai_by_kadai_id($kadai_id)->kamoku_id);
                    $kamoku_name = htmlspecialchars($kamokuDAO->get_kamokuName($kamoku_id));
                    ?>

                    <tr>
                        <td><input type="checkbox" class="checks" name="delete_ids[]" value="<?= htmlspecialchars($kadai['kadai_id']) ?>"></td>
                        <td><a href="../change_task/kadai_change.php?kadai_id=<?= htmlspecialchars($kadai['kadai_id']) ?>" class="kadaichange-link">変更</a></td>
                        <td align="center"><?= htmlspecialchars($kadai['kadai_week']) ?></td>
                        <td align="center"><?= htmlspecialchars($kadai['title']) ?></td>
                        <td align="center"><?= htmlspecialchars($kadai['kadai_type_name']) ?></td>
                        
                        <?php if (empty($selected_kamoku_id)): ?> <!-- すべての科目を選択された時科目名表示 -->
                            <td align="center"><?= $kamoku_name ?></td>
                         <?php endif; ?>
                         
                    </tr>
                <?php endforeach; ?>
                <?php endif; ?> 
            </table>

            

            <input id="sakujo" type="submit" name="delete" value="削除">
        </form>
        </div>
        </div>
    
    </main>  
</body>
<?php include "../head/background.php"; ?> 
</html>
