<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>


<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ナビゲーションメニュー</title>
    <link href="../nav/nav.css" rel="stylesheet">
    <!-- <link href="https://fonts.googleapis.com/css2?family=Sour+Gummy&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&display=swap" rel="stylesheet"> -->

</head>

<body>
<div class="menu-container">
    <nav class="vertical-nav">
    <ul>
        <li><a href="../check_list/kadaiChecklist.php" class="<?= $currentPage == 'kadaiChecklist.php' ? 'active' : '' ?>">課題チェックリスト</a></li>
        <li><a href="../task_progress_list/student_list.php" class="<?= $currentPage == 'student_list.php' ? 'active' : '' ?>">学生課題進捗一覧</a></li>

        <details <?= in_array($currentPage, ['addkamoku.php', 'kamokuList.php']) ? 'open' : '' ?>>
            <summary>科目管理</summary>
            <li><a href="../add_subject/addkamoku.php" id="inside-link" class="<?= $currentPage == 'addkamoku.php' ? 'active' : '' ?>">科目追加</a></li>
            <li><a href="../manage_subject_list/kamokuList.php" id="inside-link" class="<?= $currentPage == 'kamokuList.php' ? 'active' : '' ?>">科目変更削除</a></li>
        </details>

        <details <?= in_array($currentPage, ['kadai_add.php', 'kadaiList.php']) ? 'open' : '' ?>>
            <summary>課題管理</summary>
            <li><a href="../add_task/kadai_add.php" id="inside-link" class="<?= $currentPage == 'kadai_add.php' ? 'active' : '' ?>">課題追加</a></li>
            <li><a href="../manage_task_list/kadaiList.php" id="inside-link" class="<?= $currentPage == 'kadaiList.php' ? 'active' : '' ?>">課題変更削除</a></li>
        </details>

        <li><a href="../manage_registration/kamokuUser.php" class="<?= $currentPage == 'kamokuUser.php' ? 'active' : '' ?>">履修登録</a></li>
        <li><a href="../manage_stamp/stamp.php" class="<?= $currentPage == 'stamp.php' ? 'active' : '' ?>">スタンプ管理</a></li>
        <li><a href="../upload_csv/CSV_upload_teacher.php" class="<?= $currentPage == 'CSV_upload_teacher.php' || $currentPage == 'CSV_upload_student.php' ? 'active' : '' ?>">CSV一括登録</a></li>
        <li><a href="../add_account/add_teacher.php" class="<?= $currentPage == 'add_teacher.php' || $currentPage == 'add_student.php' ? 'active' : '' ?>">アカウント追加</a></li>
        <li><a href="../change_password/passhenkou.php" class="<?= $currentPage == 'passhenkou.php' ? 'active' : '' ?>">パスワード変更</a></li>
    </ul>

        <div class="clock"> <!-- 時計 -->
            <p class="clock-date"></p>
            <p class="clock-time"></p>
        </div>

        <!-- 時計のJavaScript -->
        <script src="../nav/clock.js"></script>
    </nav>
   
</div>
</body>
</html>