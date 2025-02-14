<?php
    require_once '../helpers/MemberDAO.php';
    require_once '../helpers/KamokuDAO.php';
    require_once '../helpers/KadaiDAO.php';
    require_once '../helpers/MondaiTypeDAO.php';
    require_once '../helpers/KadaiAnswerDAO.php';

    // session_cache_limiter('private_no_expire'); //フォーム再送信なくせるけどデータ更新されない
    session_start();
    
    // loginしてない時はloginページに戻る
    if(empty($_SESSION['member']) || $_SESSION["member"]->user_type ==1){
      header('Location: ../login/login.php'); 
        exit;
    }

    // loginしているユーザーの情報を取得
    $member = $_SESSION["member"]; 
    $user_type = $member->user_type;  

    // すべての科目を取得
    $kamokuDAO = new KamokuDAO();
    $kamoku_list = $kamokuDAO->get_kamoku();

    // kadaiDAOをインスタンス
    $kadaiDAO = new KadaiDAO();

    // すべてのmondaiType_idを取得
    $mondaiTypeDAO = new MondaiTypeDAO();
    $mondaiType_list = $mondaiTypeDAO->get_mondai_type();

    // KadaiAnswerDAO、memberDAOをインスタンス
    $kadaiAnswerDAO = new KadaiAnswerDAO();
    $memberDAO = new MemberDAO();

    // 科目ID or クラスによってフィルタリング
    $selected_kamoku_id = isset($_POST['subjects']) && $_POST['subjects'] !== '' ? (int)$_POST['subjects'] : null;
    $selected_classes = isset($_POST['classes']) ? $_POST['classes'] : [];

    $kadai_answers_filtered = [];
    $kadai_answers_filtered = $kadaiAnswerDAO->get_kadai_answers_by_kamoku_or_class($selected_kamoku_id, $selected_classes);
    
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta property="og:url" content="https://lpeg.info/html/table_list_sort.html">
    <meta property="og:image" content="https://lpeg.info/images/table_list_sort_img1.jpg">
    <title>課題チェックリスト</title>
    <link href="kadaiChecklist.css" rel="stylesheet" />
    <script src="w3.js"></script>
</head>

<body>
    <?php include "../head/head.php"; ?> 
<!-- nav -->
        <?php if($user_type == 1): include "../nav/nav_stu.php"; ?>  
        <?php else: include "../nav/nav_tec.php"; ?>  
        <?php endif; ?>
        
<main class="main-content">  
    <div class="kadaiCheck-container">

    <h1>課題チェックリスト</h1>
    <div id="div">
        <!-- フィルタフォーム -->
        <form action="" method="POST" id="filter-form" class="form-inline">
            <select name="subjects" id="sub" onchange="document.getElementById('filter-form').submit();">
                <option value="" <?= empty($selected_kamoku_id) ? 'selected' : '' ?>>すべての科目</option>
                <?php foreach ($kamoku_list as $kamoku): ?>
                    <option value="<?= htmlspecialchars($kamoku->kamoku_id) ?>" <?= $selected_kamoku_id == $kamoku->kamoku_id ? 'selected' : '' ?>>
                        <?= htmlspecialchars($kamoku->kamoku_name) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- クラスフィルタ -->
            <?php 
            $classes = ["01" => "1組", "02" => "2組", "03" => "3組", "04" => "4組"];
            foreach ($classes as $key => $label): 
            ?>
                <label>
                <input type="checkbox" class="checks"name="classes[]" value="<?= $key ?>" 
                    <?= in_array($key, $selected_classes) ? 'checked' : '' ?>>
                <?= $label ?>
            </label>
            <?php endforeach; ?>

            <input type="text" id="searchInput" placeholder="キーワードで検索🔍">
            
            <script>
                document.getElementById('searchInput').addEventListener('keyup', function () {
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
    </div>
    
<script>
    // キャッシュされたページが表示されないようにする
    if (performance.navigation.type === 2) {
        location.reload(true);
    }
</script>

    <script>
        // クラスのチェックボックスの変更を監視してフォームを送信する
        document.querySelectorAll("input[name='classes[]']").forEach(el => {
            el.addEventListener('change', function () {
                document.getElementById('filter-form').submit();
            });
        });
    </script>

<script>
    // hiddenのPOST情報を持って課題チェックページへの遷移
    function hiddenPost(kadaiId) {
        const form = document.getElementById(kadaiId); 
        if (form) {
            form.submit();
        } 
    }
</script>

    <!-- データ表示テーブル -->
    <table border="1" align="center" class="sorttbl" id="myTable">
        <tr>
            <th>学籍番号</th>
            <th>名前</th>
            <th>Week</th>
            <th>課題</th>
            <th>課題タイプ</th>
            <?php if (empty($selected_kamoku_id)): ?> <!-- すべての科目を選択された時科目名欄表示 -->
                <th>科目</th>
            <?php endif; ?>
        </tr>
        
        <?php if (empty($kadai_answers_filtered)): ?>
            <tr>
                <td colspan="6" align="center">条件に一致するデータはありません。</td>
            </tr>
        <?php else: ?>
            <?php foreach ($kadai_answers_filtered as $kadaiAnswer): 
                // 学生情報と課題情報を取得
                $student_info = $memberDAO->get_member_by_email($kadaiAnswer->email);
                $kadai_info = $kadaiDAO->get_kadai_by_kadai_id($kadaiAnswer->kadai_id);

                if ($student_info && $kadai_info):
                    $student_id = htmlspecialchars($student_info->student_id);
                    $student_name = htmlspecialchars($student_info->user_name);
                    $kadai_title = htmlspecialchars($kadai_info->title);
                    $kadai_week = htmlspecialchars($kadai_info->kadai_week);
                    $kadai_type_id = htmlspecialchars($kadai_info->kadai_type_id);
                    $kadai_id = htmlspecialchars($kadai_info->kadai_id);

                    $kamoku_id = htmlspecialchars($kadaiDAO->get_kadai_by_kadai_id($kadai_id)->kamoku_id);
                    $kamoku_name = htmlspecialchars($kamokuDAO->get_kamokuName($kamoku_id));

                    // 課題タイプを取得
                    $kadai_type_name = $mondaiTypeDAO->get_mondai_type_name_by_id($kadai_type_id); 
            ?>
            <tr onclick="redirectToKadaiCheck(
                '<?= $kamoku_id ?>', 
                '<?= $kadai_week ?>', 
                '<?= $kadai_title ?>', 
                '<?= $student_info->email ?>')">
                <td align="center"><?= $student_id ?></td>
                <td align="center"><?= $student_name ?></td>
                <td align="center"><?= $kadai_week ?></td>
                <td align="center"><?= $kadai_title ?></td>
                <td align="center">
                    <?php if ($kadai_type_id == 1): ?>
                        <span style="color:green"><?= htmlspecialchars($kadai_type_name) ?></span>
                    <?php else: ?>
                        <?= htmlspecialchars($kadai_type_name) ?>
                    <?php endif; ?>
                </td>
                <?php if (empty($selected_kamoku_id)): ?> <!-- すべての科目を選択された時科目名表示 -->
                    <td align="center"><?= $kamoku_name ?></td>
                <?php endif; ?>
            </tr>
            <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>

    <!-- JavaScript -->
    <script>
        function redirectToKadaiCheck(kamokuId, kadaiWeek, title, stuEmail) {
            // form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '../check_resubmission/kadai_check.php';

            // hidden
            const fields = { kamoku_id: kamokuId, kadai_week: kadaiWeek, title: title, stu_email: stuEmail };
            for (const [key, value] of Object.entries(fields)) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                form.appendChild(input);
            }

            document.body.appendChild(form);
            form.submit();
        }
    </script>
        
        
    </div>
    </main>  
    <?php include "../head/background.php"; ?> 
</body>
 
</html>