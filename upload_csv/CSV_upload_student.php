<?php
require_once '../helpers/DAO.php';
require_once '../helpers/MemberDAO.php';

session_start();
    
    // ログインしてない時はログインページに戻る
    if(empty($_SESSION['member']) || $_SESSION["member"]->user_type ==1){
      header('Location: ../login/login.php');
        exit;
    }

    // セッション取得
    $member = $_SESSION["member"]; 
    $session_user_type = $member->user_type;

$date = new DateTime();

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    // アップロードされたCSVファイルは$_FILES['csv_file']取得できる
    $fileName = $_FILES['csv_file']['name'];
    $fileTmpName = $_FILES['csv_file']['tmp_name'] ;

    $pattern = "/^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/";

    if($fileName !== ""){  // ファイルが選択されたら
        // ファイルパス
        $filePath = './csv/' . $fileName;

        // CSVファイルをcsvディレクトリに保存する
        move_uploaded_file($fileTmpName, $filePath);

        // csvディレクトリに保存したCSVファイルを読み込み、配列に置き換る。
        $data = array_map('str_getcsv', file($filePath));
        $msg['success'] = [];
        $msg['errors'] = [];
        

        // usersテーブルにデータを挿入する
        foreach ($data as $key => $row) {
            
            if(count($row) != 3){ //カラム数が不一致の場合フォーマット違いエラー
                $errs['errors'] = 'ファイルのフォーマットが違います。';
                break;
            }

            else{

                // 1行目はテーブルに入れたくないのでスキップする
                if($key == 0) {
                    continue;
                }
                
                if($row[0] != "" && $row[1]!= "" && $row[2]!= ""){
                    $email = $row[0];
                    $user_name = $row[1];
                    $student_id = $row[2];
                    $user_type = 1;
                    $password = $student_id;
                    $is_initial_login = true;

                    $memberDAO = new MemberDAO();
                    
                    //email exist check
                    if($memberDAO->email_exists($email) ===true){
                        continue; // メールアドレスがすでに存在してる場合登録しない
                    }

                    //学籍番号チェック
                    if($memberDAO->studentId_exists($student_id) ===true){
                        continue; // 学籍番号がすでに存在してる場合登録しない
                    }

                    //email format check
                    if(!preg_match('/\A[a-zA-Z0-9_.+-]+@[a-zA-Z0-9.-]+\z/', $email)){
                        $errs['errors'] = 'メールアドレスの形式が正しくありません。';
                        continue;
                    }
                
                    $member = new Member();
                    $member->email = $email;
                    $member->password = $password;
                    $member->user_name = $user_name;
                    $member->user_type = $user_type;
                    $member->student_id = $student_id;
            
                    $memberDAO->insert($member);
                    $msg['success'] = "CSVアップロードが完了しました。";
                
                }
                
                if($row[0] == "" || $row[1] == "" || $row[2] == ""){
                    $errs['errors'] = "メールアドレスか名前か学籍番号に空白セルがあります。";
                }

            }
            
        }

        if(empty($msg['success']) && empty($errs['errors'])){
            $errs['errors'] = "すべてのユーザーはもうすでに登録済みです。";
        }
    }
    else{
        $errs['errors'] = "ファイル選択されてません。";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>CSV学生一括登録</title>
    <link href="CSV_upload_student.css" rel="stylesheet" /> 
</head>
<body>
    <?php include "../head/head.php"; ?> 
<!-- nav -->
        <?php if($session_user_type == 1): include "../nav/nav_stu.php"; ?>  
        <?php else: include "../nav/nav_tec.php"; ?>  
        <?php endif; ?>


  <main class="main-content">
  
    <div class="stuUpload-container">
        <h1>CSV学生一括登録</h1>

    <div class="btn-container">
        <p id="u">ユーザー種別</p>
        <input type="button" onclick="location.href='./CSV_upload_teacher.php'" id="teacher_btn" value="教員">
        <input type="button" onclick="location.href=''" id="stu_btn" value="学生">
        <br>
    </div>
  
        <form method="POST" action="" enctype="multipart/form-data"> 
        <div class="file-preview-container">
            <input type="file" id="csv" name="csv_file" onchange="showFileName(this)">
            <label for="csv">ファイルを選択</label>
            <span class="file-name">ファイルが選択されていません</span>
        </div>

        <script>
            function showFileName(input) {
                const fileName = input.files[0]?.name || "ファイルが選択されていません";
                document.querySelector(".file-name").textContent = fileName;
            }
        </script>

            <p id="p">アップロード可能な形式は.csv (UTF-8)です。</p><br>
            <span style="color:#246b8f">※新しいユーザーのみ登録されます。すでに登録済みのユーザーは上書きされません。</span><br>
            <p><a href="StudentCSV Template.csv" download="StudentCSV Template" id="template"> 学生CSVアップロード用テンプレート</a></p>
            
            <?php if (!empty($msg['success']) && empty($errs['errors'])): ?>
                <div class="typing">
                    <div class="typing-effect">
                        <span style="color:#00B06B" id="box1"><?= @$msg['success']?></span><br> <!-- 登録成功メッセージ表示 -->
                    </div>
                </div>
                <?php endif; ?>
                <span style="color:red"><?= @$errs['errors']?></span><br>
                <button type="submit" id="touroku">登録</button>
        </form>
    </div>
    </main>
</body>
<?php include "../head/background.php"; ?> 
</html>