<?php
    require_once '../helpers/MemberDAO.php';
    require_once '../helpers/KamokuDAO.php';
    require_once '../helpers/UserKamokuDAO.php';

    session_start();

    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/error.log'); // 将日志保存到当前文件夹下的 error.log 文件

    // ログインしてない時はログインページに戻る
    if(empty($_SESSION['member']) || $_SESSION["member"]->user_type ==1){
        header('Location: ../ログイン/login.php');
        exit;
    }
  
    // ログインしているユーザーの情報を取得
    $member = $_SESSION["member"]; 
    $user_type = $member->user_type;

    // すべての学生を取得
    $memberDAO = new MemberDAO();
    $students = $memberDAO->get_all_student();
  
    // すべての科目を取得
    $kamokuDAO = new KamokuDAO();
    $kamoku_list = $kamokuDAO->get_kamoku();

    if (empty($kamoku_list)) {
        // エラー処理: 科目が存在しない場合
        die('科目が登録されていません。管理者に連絡してください。');
    }
    
    // POSTデータから選択された科目IDを取得（デフォルトは最初の科目）
    $selected_kamoku_id = isset($_POST['subjects']) && $_POST['subjects'] !== '' ? (int)$_POST['subjects'] : $kamoku_list[0]->kamoku_id;

    // 選択された科目に登録済みのユーザーを取得  
    $userKamokuDAO = new UserKamokuDAO();
    $existing_users = $userKamokuDAO->get_users_by_kamoku($selected_kamoku_id);

    // POSTリクエストがある場合の処理
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? ''; // $actionを初期化、取得

        if ($action === 'change_subject') {
            // 科目を切り替える場合の処理
            $selected_kamoku_id = (int)$_POST['subjects'];


        } elseif ($action === 'update_students') {
            // 学生の更新処理
            $selected_students = $_POST['selected_students'] ?? [];
    
            // 新しい学生を科目に登録
            foreach ($selected_students as $selected_email) {
                if (!in_array($selected_email, $existing_users)) {   //in_array()は $selected_email が $existing_users の中に存在するかチェックする。returnは true/false
                    $userKamokuDAO->add_user_to_kamoku($selected_kamoku_id, $selected_email);
                }
            }
            // 削除
            foreach ($existing_users as $existing_email) {
                if (!in_array($existing_email, $selected_students)) {
                    $userKamokuDAO->remove_user_from_kamoku($selected_kamoku_id, $existing_email);
                }
            }
            // 更新後に最新の登録済みユーザーを取得
            $existing_users = $userKamokuDAO->get_users_by_kamoku($selected_kamoku_id);

            //成功メッセージ表示
            $msg['success'] = "更新が完了しました。";  
            echo "<script>alert('更新が完了しました。');</script>";
        }
    }
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>履修登録</title>
    <link href="kamokuUser.css" rel="stylesheet" />
    <script src="w3.js"></script>
</head>
<body>
    <?php include "../head/head.php"; ?> 
   <!-- nav -->
        <?php if($member->user_type == 1): include "../nav/nav_stu.php"; ?>
        <?php else: include "../nav/nav_tec.php"; ?>  
        <?php endif; ?>

    <main class="main-content">  
    <div class="kamokuUser-container">
        <h1>履修登録</h1>
        <!-- 科目選択フォーム -->
        <form action="" method="POST" id="subjects-form" class="form-inline">
            <select name="subjects" id="sub" onchange="setActionAndSubmit('change_subject')">
                <?php foreach ($kamoku_list as $kamoku): ?>
                    <option value="<?= htmlspecialchars($kamoku->kamoku_id) ?>" <?= $selected_kamoku_id == $kamoku->kamoku_id ? 'selected' : '' ?>>
                        <?= htmlspecialchars($kamoku->kamoku_name) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <!-- フォームの提出を分ける 学生更新 -->
            <input type="hidden" name="action" id="action" value="update_students">


            <!-- 検索機能 -->
            <input type="text" id="searchInput" placeholder="キーワードで検索🔍">
            <script>
                document.getElementById('searchInput').addEventListener('keyup', function() {
                    let searchValue = this.value.toLowerCase();
                    let tableRows = document.getElementById('myTable').getElementsByTagName('tr');

                    for (let i = 1; i < tableRows.length; i++) {
                        let rowText = tableRows[i].textContent.toLowerCase();

                        if (rowText.indexOf(searchValue) > -1) {
                            tableRows[i].style.display = '';
                        }
                        else {
                            tableRows[i].style.display = 'none';
                        }
                    }
                });
            </script>

            <p class="setumei">
                ※既に登録されている学生にはチェックがついています。<br>
                　科目を選び、追加する学生にチェックを入れて「更新」を押してください。<br>
                　チェックを外して「更新」を押すと、登録が解除されます。
            </p>
        
            <table border="1" align="center" class="sorttbl" id="myTable">
                <tr>
                    <th><input type="checkbox" id="checksAll"></th>
                    <th>学籍番号</th>
                    <th>名前</th>
                    <th>ユーザ種別</th>
                </tr>
                <?php foreach ($students as $student): ?>
                    <tr>
                        <td align="center">
                            <input type="checkbox" class="checks" name="selected_students[]" value="<?= htmlspecialchars($student->email) ?>" 
                                <?= in_array($student->email, $existing_users) ? 'checked' : '' ?>>  <!-- 自動チェック機能 -->
                        </td>
                        <td align="center"><?= htmlspecialchars($student->student_id) ?></td>
                        <td align="center"><?= htmlspecialchars($student->user_name) ?></td>
                        <td align="center"><?= $student->user_type == 1 ? '学生' : '教師' ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
            
            <!-- 更新成功メッセージ表示 -->
            <span style="color:#246b8f"><?= @$msg['success']?></span>

            <input id="update-button" type="submit" value="更新">
        </form>

        <script>
            // 科目選択の際にフォームを送信
            function setActionAndSubmit(action) {
                document.getElementById('action').value = action;
                document.getElementById('subjects-form').submit();
            }
        </script>
    </div>

    

    <!-- チェックボックス機能 -->
    <script>
        // 全選機能：現在表示されている行だけに適用
        document.getElementById('checksAll').addEventListener('click', function() {
            const isChecked = this.checked;
            const tableRows = document.getElementById('myTable').getElementsByTagName('tr');

            // 全ての行をループし、表示中の行だけをチェック
            for (let i = 1; i < tableRows.length; i++) { // ヘッダー行をスキップ
                if (tableRows[i].style.display !== 'none') {    // 表示中の行
                    const checkbox = tableRows[i].querySelector('input[type="checkbox"]');
                    if (checkbox) {
                        checkbox.checked = isChecked;   // 全選または全解除
                    }
                }
            }
        });

        // 個別のチェックボックスをクリックした時、全選チェックボックスの状態を更新
        document.querySelectorAll('.checks').forEach(checkbox => { 
            checkbox.addEventListener('click', function() {
                const tableRows = document.getElementById('myTable').getElementsByTagName('tr');
                let allVisibleChecked = true;   // 表示中の行が全てチェックされているか

                // 表示中の行のチェック状態を確認
                for (let i = 1; i < tableRows.length; i++) {   // ヘッダー行をスキップ
                    if (tableRows[i].style.display !== 'none') {    // 表示中の行
                        const checkbox = tableRows[i].querySelector('input[type="checkbox"]');
                        if (checkbox && !checkbox.checked) {    // チェックされていない項目があれば
                            allVisibleChecked = false;  // 全選ではない
                            break;
                        }
                    }
                }

                // 全選チェックボックスの状態を更新
                document.getElementById('checksAll').checked = allVisibleChecked;
            });
        });
    </script>
   </main>  
</body>
<?php include "../head/background.php"; ?> 
</html>
