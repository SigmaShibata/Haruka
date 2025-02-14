<?php
    require_once '../helpers/MemberDAO.php';
    require_once '../helpers/KamokuDAO.php';
    require_once '../helpers/KadaiDAO.php';
    require_once '../helpers/MondaiTypeDAO.php';
    require_once '../helpers/KadaiAnswerDAO.php';
    require_once '../helpers/UserKamokuDAO.php';

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

    // kadaiDAOをインスタンス
    $kadaiDAO = new KadaiDAO();

    // すべてのmondaiType_idを取得
    $mondaiTypeDAO = new MondaiTypeDAO();
    $mondaiType_list = $mondaiTypeDAO->get_mondai_type();

    // KadaiAnswerDAO、memberDAOをインスタンス
    $kadaiAnswerDAO = new KadaiAnswerDAO();
    $memberDAO = new MemberDAO();

    // 科目ID or クラスによってフィルタリング
    $selected_kamoku_id = isset($_POST['subjects']) && $_POST['subjects'] !== '' ? (int)$_POST['subjects'] : ($kamoku_list[0]->kamoku_id ?? null);
    $selected_classes = isset($_POST['classes']) ? $_POST['classes'] : [];


    $students_filtered = [];
    $students_filtered = $memberDAO->get_students_by_kamoku_or_class($selected_kamoku_id, $selected_classes);


?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta property="og:url" content="https://lpeg.info/html/table_list_sort.html">
    <meta property="og:image" content="https://lpeg.info/images/table_list_sort_img1.jpg">
    <title>学生課題進捗一覧</title>
    <link href="student_list.css" rel="stylesheet" />
    <script src="w3.js"></script>
</head>
<body>
    <?php include "../head/head.php"; ?> 
    <!-- nav -->
    <?php if($member->user_type == 1): include "../nav/nav_stu.php"; ?>
    <?php else: include "../nav/nav_tec.php"; ?>  
    <?php endif; ?>

    <main class="main-content">  
        <div class="stu-container">
            <h1>学生課題進捗一覧</h1>
            <div id="div">
                <!-- フィルタフォーム -->
                <form action="" method="POST" id="filter-form" class="form-inline">
                    <select name="subjects" id="sub" onchange="document.getElementById('filter-form').submit();">
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
                        <input type="checkbox" class="checks" name="classes[]" value="<?= $key ?>" 
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
                
            </div>

            <script>
            // クラスのチェックボックスの変更を監視してフォームを送信する
                document.querySelectorAll("input[name='classes[]']").forEach(el => {
                    el.addEventListener('change', function () {
                        document.getElementById('filter-form').submit();
                    });
                });
            </script>

            <!-- データ表示テーブル -->
            <table border="1" align="center" class="sorttbl" id="myTable">           
                <tr>
                    <th>学籍番号</th>
                    <th>名前</th>
                    <?php foreach ($mondaiType_list as $mondaiType): ?>
                        <th>
                                <?= htmlspecialchars($mondaiType->kadai_type_name) ?>
                        </th>        
                    <?php endforeach; ?>
                </tr>
                
            
                <?php foreach ($students_filtered as $student): 
                    // 学生情報を取得
                    $student_info = $memberDAO->get_member_by_email($student->email);


                    $student_id = htmlspecialchars($student_info->student_id);
                    $student_name = htmlspecialchars($student_info->user_name);
                
                ?>

                <tr onclick="location.href='../stamp_sheet/stamp_sheet.php?kamoku_id=<?= htmlspecialchars($selected_kamoku_id) ?>&stu_email=<?= htmlspecialchars($student_info->email) ?>'">
                    <td align="center"><?= $student_id ?></td>
                    <td align="center"><?= $student_name ?></td>
                    <input type="hidden" id="stu_email" name="stu_email" value=<?= htmlspecialchars($student_info->email)?>> 
                    <!-- 学生のメールスタンプシートに渡す -->

                    <?php foreach ($mondaiType_list as $mondaiType): 
                        $total_kadai = $kadaiDAO->get_total_kadai_by_type($selected_kamoku_id, $mondaiType->kadai_type_id);
                        $completed_kadai = $kadaiAnswerDAO->get_completed_kadai_by_student_and_type($selected_kamoku_id, $mondaiType->kadai_type_id, $student->email,);
                    ?>
                        <td align="center">
                            <?= htmlspecialchars($completed_kadai) ?>/<?= htmlspecialchars($total_kadai) ?><br>
                            <!-- 進捗メーター全部完成が緑、4割以上は黄色、それ以下は赤（多分） -->
                            <meter id="progress" min="0" max=<?=$total_kadai?> low=<?=$total_kadai*0.4?> high=<?=$total_kadai*0.99?> optimum=<?=$total_kadai?> value=<?= $completed_kadai ?>></meter>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
            </table>
        </div>
        </form>
    </main>  
</body>
<?php include "../head/background.php"; ?> 
</html>
