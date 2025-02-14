<?php
    require_once '../helpers/KamokuDAO.php';
    require_once '../helpers/KadaiDAO.php';
    require_once '../helpers/MondaiTypeDAO.php';
    require_once '../helpers/MemberDAO.php';

    session_start();

    // loginしてない時はloginページに戻る
    if(empty($_SESSION['member']) || $_SESSION["member"]->user_type ==1){
        header('Location: ../login/login.php');
        exit;
    }
  
    // loginしているユーザーの情報を取得
    $member = $_SESSION["member"]; 
    $user_type = $member->user_type;  

    $kamokuDAO = new KamokuDAO();
    $kamoku_list = $kamokuDAO->get_kamoku();

    $kadaiDAO = new KadaiDAO();

    $mondaiTypeDAO = new MondaiTypeDAO();
    $mondaiType_list = $mondaiTypeDAO->get_mondai_type();

    // 直接にこのページに入れない
    if (!isset($_GET['kadai_id']) || empty($_GET['kadai_id'])) {
        header('Location: ../manage_task_list/kadaiList.php');
        exit;
    }

    // manage_task_listから課題IDを取得して該当する課題のデータを取得
    $kadai_id = (int)$_GET['kadai_id'];
    $kadai = $kadaiDAO->get_kadai_by_kadai_id($kadai_id);

    if (!$kadai) {
        echo "指定された課題が存在しません。manage_task_listから選択ください。";
        exit;
    }

    // 変更ボタンを押された場合
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
        $kadai_id = (int)$_POST['kadai_id'];
        $title = trim(str_replace('　', '', $_POST['title']));  //全角スペースや半角スペースをなくす
        $kamoku_id = $_POST['subjects'];
        $week = $_POST['weeks'];
        $kadai_type_id = $_POST['kadai_type'];
        $errs = [];

        if (empty($title)) {
            $errs['title'] = "タイトルを入力してください";
        }

        if ($title !=='' && $kadaiDAO->kadai_exists_except_current($kamoku_id, $week, $title, $kadai_id)) {
            $errs['kadai'] = "この課題はすでに登録されています。";   //エラーメッセージ表示
        }
    
        if(empty($errs)){
            // 更新課題の情報
            $kadaiDAO->update_kadai($kadai_id, $title, $kamoku_id, $week, $kadai_type_id);
        
            // 更新完了メッセージを表示して、リダイレクトする（manage_task_listのページURL）
            echo "<script>alert('課題が更新されました。'); window.location.href = '../manage_task_list/kadaiList.php';</script>";
            exit;
        }
    }

?>


<!DOCTYPE html>
<link href="kadai_change.css" rel="stylesheet" />
<html lang="ja">
    <head>
    <title>課題変更</title>
    <meta charset="UTF-8">
    </head>
    <body>
    <?php include "../head/head.php"; ?> 
<!-- nav -->
    <?php if($member->user_type == 1): include "../nav/nav_stu.php"; ?>
    <?php else: include "../nav/nav_tec.php"; ?>  
    <?php endif; ?>

    <main class="main-content">  
        
        <div class="kadaiChange-container">
        <h1 id="h1">課題変更</h1>
        <form action="" method="POST">
            <div class="yokonarabe">
            <input type="hidden" name="kadai_id" value="<?= htmlspecialchars($kadai->kadai_id) ?>">

                <h3>科目</h3>
                <select name="subjects" id="sub" >
                    <option value="" disabled>科目を選択してください</option>
                        <?php foreach ($kamoku_list as $kamoku) : ?>
                        <option value="<?= htmlspecialchars($kamoku->kamoku_id) ?>" <?= $kadai->kamoku_id == $kamoku->kamoku_id ? 'selected' : '' ?> >
                            <?= htmlspecialchars($kamoku->kamoku_name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="yokonarabe">
                <h3>タイトル</h3>
                <input type="text" name="title" id="title" value="<?= htmlspecialchars($kadai->title) ?>" placeholder="15字以内" maxlength="15">
            </div>

            <!-- エラーメッセージ表示 -->
            <span class="error <?= !empty($errs['title']) ? 'visible' : '' ?>"><?= @$errs['title']?></span>
            
            <div class="yokonarabe">
                <h3>Week</h3>
                <select name="weeks" id="week">
                    <option value="" disabled>Weekを選択してください</option>
                    <option value="Week01" <?= $kadai->kadai_week == "Week01" ? 'selected' : '' ?>>Week01</option>
                    <option value="Week02" <?= $kadai->kadai_week == "Week02" ? 'selected' : '' ?>>Week02</option>
                    <option value="Week03" <?= $kadai->kadai_week == "Week03" ? 'selected' : '' ?>>Week03</option>
                    <option value="Week04" <?= $kadai->kadai_week == "Week04" ? 'selected' : '' ?>>Week04</option>
                    <option value="Week05" <?= $kadai->kadai_week == "Week05" ? 'selected' : '' ?>>Week05</option>
                    <option value="Week06" <?= $kadai->kadai_week == "Week06" ? 'selected' : '' ?>>Week06</option>
                    <option value="Week07" <?= $kadai->kadai_week == "Week07" ? 'selected' : '' ?>>Week07</option>
                    <option value="Week08" <?= $kadai->kadai_week == "Week08" ? 'selected' : '' ?>>Week08</option>
                    <option value="Week09" <?= $kadai->kadai_week == "Week09" ? 'selected' : '' ?>>Week09</option>
                    <option value="Week10" <?= $kadai->kadai_week == "Week10" ? 'selected' : '' ?>>Week10</option>
                    <option value="Week11" <?= $kadai->kadai_week == "Week11" ? 'selected' : '' ?>>Week11</option>
                    <option value="Week12" <?= $kadai->kadai_week == "Week12" ? 'selected' : '' ?>>Week12</option>
                    <option value="Week13" <?= $kadai->kadai_week == "Week13" ? 'selected' : '' ?>>Week13</option>
                    <option value="Week14" <?= $kadai->kadai_week == "Week14" ? 'selected' : '' ?>>Week14</option>
                    <option value="Week15" <?= $kadai->kadai_week == "Week15" ? 'selected' : '' ?>>Week15</option>
                </select>
            </div>

            <div id="kadaitype">
                <?php foreach ($mondaiType_list as $mondaiType) : ?>
                    <input type="radio" name="kadai_type" value="<?= $mondaiType->kadai_type_id ?>" id="kadai_type_<?= $mondaiType->kadai_type_id ?>"
                        <?= $kadai->kadai_type_id == $mondaiType->kadai_type_id ? 'checked' : '' ?>>
                    <label for="kadai_type_<?= $mondaiType->kadai_type_id ?>"><?= $mondaiType->kadai_type_name ?></label>
                <?php endforeach; ?>
            </div>
    
            <!-- エラーメッセージ表示 -->
            <span class="error <?= !empty($errs['kadai']) ? 'visible' : '' ?>"><?= @$errs['kadai']?></span>

            <input id="henkou" type="submit" name="update" value="変更">
        </form>
        </div>   
    </main>      
</body>
<?php include "../head/background.php"; ?> 
</html>