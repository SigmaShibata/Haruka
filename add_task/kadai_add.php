<?php
    require_once '../helpers/KamokuDAO.php';
    require_once '../helpers/KadaiDAO.php';
    require_once '../helpers/MondaiTypeDAO.php';
    require_once '../helpers/MemberDAO.php';

    session_start();

    if(empty($_SESSION['member']) || $_SESSION["member"]->user_type ==1){
        header('Location: ../login/login.php');
        exit;
    }
    // loginしているユーザーの情報を取得
    $member = $_SESSION["member"]; 

    //　すべての科目を取得
    $kamokuDAO = new KamokuDAO();
    $kamoku_list = $kamokuDAO->get_kamoku();

    // すべてのmondaiType_idを取得
    $mondaiTypeDAO = new MondaiTypeDAO();
    $mondaiType_list = $mondaiTypeDAO->get_mondai_type();

    //追加ボタンを押したら
    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        $kamoku_id = isset($_POST['subjects']) ? $_POST['subjects'] : null;
        $title = trim(str_replace('　', '', $_POST['title']));  //全角スペースや半角スペースをなくす
        $kadai_week = isset($_POST['weeks']) ? $_POST['weeks'] : null;
        $kadai_type = isset($_POST['kadai_type']) ? $_POST['kadai_type'] : null;

        //エラーメッセージ表示
        if (empty($kamoku_id)) {
            $errs['kamoku'] = "科目を選択してください";
        }
        if (empty($title)) {
            $errs['title'] = "タイトルを入力してください";
        }
        if (empty($kadai_week)) {
            $errs['week'] = "Weekを選択してください";
        }
        if (empty($kadai_type)) {
            $errs['kadai_type'] = "課題タイプを選択してください";
        }

        $kadaiDAO = new KadaiDAO();
        if ($kamoku_id !== null && $title !=='' && $kadai_week!== null && $kadaiDAO->kadai_exists($kamoku_id, $kadai_week, $title) === true) {
            $errs['kadai'] = " {$kadai_week}の{$title}はすでに登録されています。";   //エラーメッセージ表示
        }
        
          
        if(empty($errs)){
            $kadai = new Kadai();
            $kadai -> kamoku_id = $kamoku_id;
            $kadai -> title = $title;
            $kadai -> kadai_week = $kadai_week;
            $kadai -> kadai_type_id = $kadai_type;

            $kadaiDAO -> insert($kadai);
            
            $msg['success'] = "{$kadai_week}の{$title}が追加されました。";  //課題追加成功メッセージ表示
        }

    }
           
?>



<!DOCTYPE html>
<link href="kadai_add.css" rel="stylesheet" />
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>課題追加</title>
</head>
<body>
    <?php include "../head/head.php"; ?> 
<!-- nav -->
        <?php if($member->user_type == 1): include "../nav/nav_stu.php"; ?>
        <?php else: include "../nav/nav_tec.php"; ?>  
        <?php endif; ?>


<main class="main-content">  
    <div class="addKadai-container">
         <h1>課題追加</h1>
        <form action="" method="POST">
            <div class="yokonarabe">
                <h3>科目</h3>
                <select name="subjects" id="sub" >
                    <option value="" disabled <?= empty($kamoku_id) ? 'selected' : '' ?>>科目を選択してください</option>
                    <?php foreach ($kamoku_list as $kamoku) : ?>
                        <option value="<?= htmlspecialchars($kamoku->kamoku_id) ?>" <?= (isset($kamoku_id) && $kamoku_id == $kamoku->kamoku_id) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($kamoku->kamoku_name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- エラーメッセージ表示 -->
            <span class="error <?= !empty($errs['kamoku']) ? 'visible' : '' ?>"><?= @$errs['kamoku']?></span>

            <div class="yokonarabe">
                <h3>課題タイトル</h3>
                <input type="text" id="title" name="title" value="<?= htmlspecialchars($title ?? '') ?>" placeholder="15文字以内" maxlength="15" >
            </div>
            <!-- エラーメッセージ表示 -->
            <span class="error <?= !empty($errs['title']) ? 'visible' : '' ?>"><?= @$errs['title']?></span>

            <div class="yokonarabe">
                <h3>Week</h3>
                <select name="weeks" id="week" >
                    <option value="" disabled selected>Weekを選択してください</option>
                    <option value="Week01" <?= (isset($kadai_week) && $kadai_week === "Week01") ? 'selected' : '' ?>>Week01</option>
                    <option value="Week02" <?= (isset($kadai_week) && $kadai_week === "Week02") ? 'selected' : '' ?>>Week02</option>
                    <option value="Week03" <?= (isset($kadai_week) && $kadai_week === "Week03") ? 'selected' : '' ?>>Week03</option>
                    <option value="Week04" <?= (isset($kadai_week) && $kadai_week === "Week04") ? 'selected' : '' ?>>Week04</option>
                    <option value="Week05" <?= (isset($kadai_week) && $kadai_week === "Week05") ? 'selected' : '' ?>>Week05</option>
                    <option value="Week06" <?= (isset($kadai_week) && $kadai_week === "Week06") ? 'selected' : '' ?>>Week06</option>
                    <option value="Week07" <?= (isset($kadai_week) && $kadai_week === "Week07") ? 'selected' : '' ?>>Week07</option>
                    <option value="Week08" <?= (isset($kadai_week) && $kadai_week === "Week08") ? 'selected' : '' ?>>Week08</option>
                    <option value="Week09" <?= (isset($kadai_week) && $kadai_week === "Week09") ? 'selected' : '' ?>>Week09</option>
                    <option value="Week10" <?= (isset($kadai_week) && $kadai_week === "Week10") ? 'selected' : '' ?>>Week10</option>
                    <option value="Week11" <?= (isset($kadai_week) && $kadai_week === "Week11") ? 'selected' : '' ?>>Week11</option>
                    <option value="Week12" <?= (isset($kadai_week) && $kadai_week === "Week12") ? 'selected' : '' ?>>Week12</option>
                    <option value="Week13" <?= (isset($kadai_week) && $kadai_week === "Week13") ? 'selected' : '' ?>>Week13</option>
                    <option value="Week14" <?= (isset($kadai_week) && $kadai_week === "Week14") ? 'selected' : '' ?>>Week14</option>
                    <option value="Week15" <?= (isset($kadai_week) && $kadai_week === "Week15") ? 'selected' : '' ?>>Week15</option>
                </select>  
            </div>

            <!-- エラーメッセージ表示 -->
            <span class="error <?= !empty($errs['week']) ? 'visible' : '' ?>"><?= @$errs['week']?></span>
            
            <div id="kadaitype">
            <!-- 課題タイプ -->
            <?php foreach ($mondaiType_list as $mondaiType) : ?>
                    <input type="radio" id="kadai_type_<?= $mondaiType->kadai_type_id ?>" name="kadai_type" 
                        value="<?= $mondaiType->kadai_type_id ?>" <?= (isset($kadai_type) && $kadai_type == $mondaiType->kadai_type_id) ? 'checked' : '' ?>>
                    <label for="kadai_type_<?= $mondaiType->kadai_type_id ?>"><?= $mondaiType->kadai_type_name ?></label>
             
            <?php endforeach; ?>
            </div>
                <!-- エラーメッセージ表示 -->
                <span class="error <?= !empty($errs['kadai_type']) ? 'visible' : '' ?>"><?= @$errs['kadai_type']?></span>

               <input id="tuika" type="submit" value="追加">

                <!-- エラーや追加成功メッセージ表示 -->
                <span class="error <?= !empty($errs['kadai']) ? 'visible' : '' ?>"><?= @$errs['kadai']?></span>
                <span class="success <?= !empty($msg['success']) ? 'visible' : '' ?>"><?= @$msg['success']?></span>
        </form>
    </div>       
    </main>  
</body>
<?php include "../head/background.php"; ?> 