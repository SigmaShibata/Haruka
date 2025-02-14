<?php
    require_once '../helpers/KamokuDAO.php';
    require_once '../helpers/MemberDAO.php';

    session_start();
    
       // セッション取得
       $member = $_SESSION["member"]; 
       $user_type = $member->user_type; 

    // ログインしてない時はログインページに戻る
    if(empty($_SESSION['member']) || $_SESSION["member"]->user_type ==1){
        header('Location: ../ログイン/login.php');
        exit;
    }

    // 直接にこのページに入れない
    if (!isset($_GET['kamoku_id']) || empty($_GET['kamoku_id'])) {
        header('Location: ../科目管理リスト/kamokuList.php');
        exit;
    }

    // 科目管理リストから科目IDを取得して該当する科目のデータを取得
    $kamoku_id = (int)$_GET['kamoku_id'];
    $kamokuDAO = new KamokuDAO();
    $kamoku = $kamokuDAO->get_kamoku_by_kamoku_id($kamoku_id);

    if (!$kamoku) {
        echo "指定された科目が存在しません。課題管理リストから選択ください。";
        exit;
    }

    // 変更ボタンを押された場合
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
        $kamoku_id = (int)$_POST['kamoku_id'];
        $kamoku_name = trim(str_replace('　', '', $_POST['kamoku_name']));  //全角スペースや半角スペースをなくす
        $errs = [];

        if (empty($kamoku_name)) {
            $errs['input'] = "科目名を入力してください";
        }

        if(!empty($kamoku_name) && $kamokuDAO -> kamoku_exists_exactMatch($kamoku_name)){
            $errs['kamoku'] = "$kamoku_name\nはすでに登録されています。";   //エラーメッセージ表示
        }
    
        if(empty($errs)){
        // 更新科目の情報
        $kamokuDAO->update_kamoku($kamoku_id, $kamoku_name);
    
        // 更新完了メッセージを表示して、リダイレクトする（科目管理リストのページURL）
        echo "<script>alert('科目が更新されました。'); window.location.href = '../科目管理リスト/kamokuList.php';</script>";
        }
    }

?>

<!DOCTYPE html>
    <link href="kamoku_change.css" rel="stylesheet" />

<html lang="ja">
    <head>
    <title>科目変更</title>
    <meta charset="UTF-8">
    </head>
    <body>
    <?php include "../head/head.php"; ?> 
    <!-- nav -->
    <?php if($member->user_type == 1): include "../nav/nav_stu.php"; ?>
    <?php else: include "../nav/nav_tec.php"; ?>  
    <?php endif; ?>

    <main class="main-content">  
        <div class="kamokuChange-container">
            <h1 id="h1">科目変更</h1>
            <form action="" method="POST">
            <div class="yokonarabe">
                <h3>科目名</h3>
                <input type="hidden" name="kamoku_id" value="<?= htmlspecialchars($kamoku->kamoku_id) ?>">
                <input type="text" name="kamoku_name" id="name" align="center" value= "<?= htmlspecialchars($kamoku->kamoku_name) ?>" maxlength="25">
            </div>
                <!-- エラーメッセージ表示 -->
                <span class="error <?= !empty($errs['input']) ? 'visible' : '' ?>"><?= @$errs['input']?></span>
                <span class="error <?= !empty($errs['kamoku']) ? 'visible' : '' ?>"><?= @$errs['kamoku']?></span>

                <input id="henkou" type="submit" name="update" value="変更">
            <form>
        </div>    
        </main>     
    </body>
    <?php include "../head/background.php"; ?> 
</html>