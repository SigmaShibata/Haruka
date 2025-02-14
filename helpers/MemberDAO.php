<?php
require_once 'DAO.php';

class Member
{ 
    public string $email;
    public string $user_name;
    public string $password;
    public string $student_id;
    public int $user_type;
    
    public bool $is_initial_login = true;
}

class MemberDAO{
    
    public function get_member(string $email, string $password){
        $dbh = DAO::get_db_connect();
        $sql = "SELECT * FROM users WHERE email = :email";

        $stmt = $dbh->prepare($sql);

        $stmt->bindValue(':email', $email, PDO::PARAM_STR);

        $stmt->execute();

        $member = $stmt->fetchObject('Member');
        
        //HASH化してないPWはログインできない
        if($member !== false){
            // if(password_verify($password, password_hash($member->password, PASSWORD_DEFAULT))){
            if(password_verify($password, $member->password)){
            return $member;
                
            }
        }
        return false;
    }

    public function insert(member $member){
        $dbh = DAO::get_db_connect();

        $sql = "INSERT INTO users(email, user_type, user_name, student_id, password, is_initial_login) VALUES (:email, :user_type, :user_name, :student_id, :password, :is_initial_login);";
        
        $stmt = $dbh->prepare($sql);
        $password = password_hash($member->password, PASSWORD_DEFAULT);
        
        $stmt->bindValue(':email', $member->email, PDO::PARAM_STR);
        $stmt->bindValue(':user_type', $member->user_type, PDO::PARAM_INT);
        $stmt->bindValue(':user_name', $member->user_name, PDO::PARAM_STR);
        $stmt->bindValue(':password', $password, PDO::PARAM_STR);
        $stmt->bindValue(':is_initial_login', $member->is_initial_login, PDO::PARAM_BOOL);
        $stmt->bindValue(':student_id', $member->student_id, PDO::PARAM_STR);

        $stmt->execute();


        if ($member->user_type == 2) {  //先生の場合デフォルトスタンプIDをユーザースタンプテーブルに追加
            $sql_stamp = "INSERT INTO stamp_use(email, stamp_id) VALUES (:email, :stamp_id);";
            $stmt_stamp = $dbh->prepare($sql_stamp);

            $stamp_id = 1;
            $stmt_stamp->bindValue(':email', $member->email, PDO::PARAM_STR);
            $stmt_stamp->bindValue(':stamp_id', PDO::PARAM_INT);
            $stmt_stamp->execute();
        }

    }

    public function email_exists(string $email){
        $dbh = DAO::get_db_connect();
        $sql = "SELECT * FROM users WHERE email = :email";

        $stmt = $dbh->prepare($sql);

        $stmt->bindValue(':email', $email, PDO::PARAM_STR);

        $stmt->execute();

        if($stmt->fetch() !== false){
            return true;
        }else{
            return false;
        }

    }

    public function studentId_exists(string $student_id){
        $dbh = DAO::get_db_connect();
        $sql = "SELECT * FROM users WHERE student_id = :student_id";

        $stmt = $dbh->prepare($sql);

        $stmt->bindValue(':student_id', $student_id, PDO::PARAM_STR);

        $stmt->execute();

        if($stmt->fetch() !== false){
            return true;
        }else{
            return false;
        }  

    }


    public function addUserKamoku(member $member, ){ //学生科目テーブルに追加
        $dbh = DAO::get_db_connect();
        $sql = "INSERT INTO user_kamoku(email, user_type, user_name, student_id, password) VALUES (:email, :user_type, :user_name, :student_id, :password);";

        $stmt = $dbh->prepare($sql);

        $stmt->bindValue(':email', $member->email, PDO::PARAM_STR);
        $stmt->bindValue(':kamokuId', $member->user_type, PDO::PARAM_INT);

        $stmt->execute();
    }

    public function changePassword(string $email, string $password ){ //パスワードを変更
        $dbh = DAO::get_db_connect();
        $password = password_hash($password, PASSWORD_DEFAULT);  //PWハッシュ化

        $sql = "UPDATE users SET password = :password, is_initial_login = 0 WHERE email = :email";

        $stmt = $dbh->prepare($sql);

        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':password', $password, PDO::PARAM_STR);
        $stmt->execute();
    }

    public function get_member_by_email(string $email){
        try{
            $dbh = DAO::get_db_connect();
    
            $sql = "SELECT * FROM users WHERE email = :email";

            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->execute();

            $data = $stmt->fetchObject('Member');

            return $data;

        }catch (PDOException $e) {
            error_log("エラー: " . $e->getMessage());
            return [];
        }
    }

    public function get_member_by_studentID(string $student_id){
        try{
            $dbh = DAO::get_db_connect();
    
            $sql = "SELECT * FROM users WHERE student_id = :student_id";

            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(':student_id', $student_id, PDO::PARAM_STR);
            $stmt->execute();

            $data = $stmt->fetchObject('Member');

            return $data;

        }catch (PDOException $e) {
            error_log("エラー: " . $e->getMessage());
            return [];
        }
    }


    public function get_all_student(){
        try{
            $dbh = DAO::get_db_connect();
    
            $sql = "SELECT * FROM users WHERE user_type = 1 ORDER BY student_id";

            $stmt = $dbh->prepare($sql);
            $stmt->execute();

            $data = [];
            while($row = $stmt->fetchObject('Member')){
                $data[] = $row;
            }
            return $data;

        }catch (PDOException $e) {
            error_log("エラー: " . $e->getMessage());
            return [];
        }
    }

    public function get_students_by_kamoku_or_class(int $kamoku_id = null, array $classes = []) { 
        $dbh = DAO::get_db_connect();
    
        try {
            // 基本検索
            $sql = "
                SELECT 
                    users.email, 
                    users.user_name, 
                    users.student_id
                    FROM users
                INNER JOIN user_kamoku ON users.email = user_kamoku.email
                WHERE users.user_type = 1
             ";
    
            // 動的 WHERE 条件
            $conditions = ["user_kamoku.kamoku_id = ?"];
            $params = [$kamoku_id];
    
            if (!empty($classes)) {
                $placeholders = implode(', ', array_fill(0, count($classes), '?'));
                $conditions[] = "SUBSTRING(users.student_id, 5, 2) IN ($placeholders)";
                $params = array_merge($params, $classes);
            }
    
            if (!empty($conditions)) {
                $sql .= " AND " . implode(" AND ", $conditions)." ORDER BY student_id";
            }
    
            $stmt = $dbh->prepare($sql);
    
            // Execute with all parameters
            $stmt->execute($params);
    
            $data = [];
            while ($row = $stmt->fetchObject('Member')) {
                $data[] = $row;
            }
            return $data;
    
        } catch (PDOException $e) {
            error_log("エラー: " . $e->getMessage());
            return [];
        }
    }
    
}
