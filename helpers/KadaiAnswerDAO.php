<?php
    require_once 'DAO.php';

class KadaiAnswer{
    public int $answer_id;
    public int $kadai_id;
    public string $email;
    public string $source_code;
    public int $answer_status;
    public bool $is_stamp_check;
    public int|null $stamp_id;
    public string $teacher_comment;
    public string $stamp_date = '';

}

class KadaiAnswerDAO{
    public function kadaiAnswer_submit(KadaiAnswer $kadaiAnswer){
        $dbh = DAO::get_db_connect();
        
        try {
            $sql = "INSERT INTO kadai_answer(kadai_id, email, source_code, answer_status, is_stamp_check, stamp_id) VALUES(:kadai_id, :email, :source_code, :answer_status, :is_stamp_check, :stamp_id)";

            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(':kadai_id',$kadaiAnswer->kadai_id,PDO::PARAM_INT);
            $stmt->bindValue(':email',$kadaiAnswer->email,PDO::PARAM_STR);
            $stmt->bindValue(':source_code',$kadaiAnswer->source_code,PDO::PARAM_STR);
            $stmt->bindValue(':answer_status',$kadaiAnswer->answer_status,PDO::PARAM_INT);
            $stmt->bindValue(':is_stamp_check',$kadaiAnswer->is_stamp_check,PDO::PARAM_BOOL);
            $stmt->bindValue(':stamp_id',$kadaiAnswer->stamp_id,PDO::PARAM_INT);

            $stmt->execute();

        } catch (PDOException $e) {
            error_log("Insert error: " . $e->getMessage());
            return false;
        }
        return true;
    }


    public function get_kadaiAnswer(string $email, int $kadai_id){
        try{
            $dbh = DAO::get_db_connect();
    
            $sql = "SELECT * FROM kadai_answer WHERE email = :email AND kadai_id = :kadai_id";

            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->bindValue(':kadai_id', $kadai_id, PDO::PARAM_INT);
            $stmt->execute();

            $kadaiAnswer = $stmt->fetchObject('KadaiAnswer');
            return $kadaiAnswer;

        }catch (PDOException $e) {
            error_log("エラー: " . $e->getMessage());
            return null;
        }
    }

    public function get_all_kadaiAnswer(){
        try{
            $dbh = DAO::get_db_connect();
    
            $sql = "SELECT * FROM kadai_answer WHERE is_stamp_check = 0";

            $stmt = $dbh->prepare($sql);
            $stmt->execute();

            $data = [];
            while($row = $stmt->fetchObject('KadaiAnswer')){
                $data[] = $row;
            }
            return $data;   

        }catch (PDOException $e) {
            error_log("エラー: " . $e->getMessage());
            return [];
        }
    }

    public function get_kadai_answers_by_kamoku_or_class(?int $kamoku_id = null, array $classes = []) { 
    $dbh = DAO::get_db_connect();

    try {
        // 基本検索
        $sql = "
            SELECT 
                kadai_answer.answer_id, 
                kadai_answer.kadai_id, 
                kadai_answer.email, 
                kadai_answer.source_code, 
                kadai_answer.answer_status, 
                kadai_answer.is_stamp_check, 
                kadai_answer.stamp_id
            FROM kadai_answer
            INNER JOIN kadai ON kadai_answer.kadai_id = kadai.kadai_id
            INNER JOIN users ON kadai_answer.email = users.email
            
        ";

        // 動的 WHERE 条件
        $conditions = ["kadai_answer.answer_status = 2", "kadai_answer.is_stamp_check = 0"];
        $params = [];

        if (!is_null($kamoku_id)) {
            $conditions[] = "kadai.kamoku_id = ?";
            $params[] = $kamoku_id;
        }
        if (!empty($classes)) {
            $placeholders = implode(', ', array_fill(0, count($classes), '?'));
            $conditions[] = "SUBSTRING(users.student_id, 5, 2) IN ($placeholders)";
            $params = array_merge($params, $classes);
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions)." ORDER BY answer_id"; 
        }

        $stmt = $dbh->prepare($sql);

        // Execute with all parameters
        $stmt->execute($params);

        $data = [];
        while ($row = $stmt->fetchObject('KadaiAnswer')) {
            $data[] = $row;
        }
        return $data;

    } catch (PDOException $e) {
        error_log("エラー: " . $e->getMessage());
        return [];
    }
}

    public function kadaiAnswer_exist(string $email, int $kadai_id){
        $dbh = DAO::get_db_connect();
    
        $sql="SELECT * FROM kadai_answer WHERE email = :email AND kadai_id = :kadai_id";
    
        $stmt=$dbh->prepare($sql);
        $stmt->bindValue(':kadai_id',$kadai_id,PDO::PARAM_INT);
        $stmt->bindValue(':email',$email,PDO::PARAM_STR);

        $stmt->execute();
    
        if($stmt->fetch()){
            return true;
        }else{
            return false;
        }
    
    }

    public function kadaiAnswer_update(string $email, int $kadai_id, string $source_code, int $answer_status ){
        $dbh = DAO::get_db_connect();
        
        try {
            $sql = "UPDATE kadai_answer SET source_code = :source_code, answer_status = :answer_status WHERE email=:email AND kadai_id = :kadai_id";

            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(':kadai_id',$kadai_id,PDO::PARAM_INT);
            $stmt->bindValue(':email',$email,PDO::PARAM_STR);
            $stmt->bindValue(':source_code',$source_code,PDO::PARAM_STR);
            $stmt->bindValue(':answer_status',$answer_status,PDO::PARAM_INT);

            $stmt->execute();

        } catch (PDOException $e) {
            error_log("Update error: " . $e->getMessage());
            return false;
        }
        return true;
    }

    public function kadaiSubmit_cancel(string $email, int $kadai_id, int $answer_status ){
        $dbh = DAO::get_db_connect();
        
        try {
            $sql = "UPDATE kadai_answer SET answer_status = :answer_status WHERE email=:email AND kadai_id = :kadai_id";

            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(':kadai_id',$kadai_id,PDO::PARAM_INT);
            $stmt->bindValue(':email',$email,PDO::PARAM_STR);
            $stmt->bindValue(':answer_status',$answer_status,PDO::PARAM_INT);

            $stmt->execute();

        } catch (PDOException $e) {
            error_log("Update error: " . $e->getMessage());
            return false;
        }
        return true;
    }
   
    public function kadaiAnswer_resubmit(string $email, int $kadai_id, int $answer_status,bool $is_stamp_check, string $stamp_date){
        $dbh = DAO::get_db_connect();
        
        try {
            $sql = "UPDATE kadai_answer SET answer_status = :answer_status, is_stamp_check = :is_stamp_check, stamp_date = :stamp_date WHERE email=:email AND kadai_id = :kadai_id";

            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(':kadai_id',$kadai_id,PDO::PARAM_INT);
            $stmt->bindValue(':email',$email,PDO::PARAM_STR);
            $stmt->bindValue(':answer_status',$answer_status,PDO::PARAM_INT);
            $stmt->bindValue(':is_stamp_check',$is_stamp_check,PDO::PARAM_BOOL);
            $stmt->bindValue(':stamp_date',$stamp_date,PDO::PARAM_STR);

            $stmt->execute();

        } catch (PDOException $e) {
            error_log("Update error: " . $e->getMessage());
            return false;
        }
        return true;
    }

    public function kadaiAnswer_stampOK(string $stu_email, int $kadai_id,int $answer_status, bool $is_stamp_check, int $stamp_id, string $stamp_date ){
        $dbh = DAO::get_db_connect();
        
        try {
            $sql = "UPDATE kadai_answer SET answer_status = :answer_status, is_stamp_check = :is_stamp_check, stamp_id = :stamp_id, stamp_date = :stamp_date WHERE email=:email AND kadai_id = :kadai_id";

            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(':kadai_id',$kadai_id,PDO::PARAM_INT);
            $stmt->bindValue(':email',$stu_email,PDO::PARAM_STR);
            $stmt->bindValue(':answer_status',$answer_status,PDO::PARAM_INT);
            $stmt->bindValue(':is_stamp_check',$stamp_id,PDO::PARAM_BOOL);
            $stmt->bindValue(':stamp_id',$stamp_id,PDO::PARAM_INT);
            $stmt->bindValue(':stamp_date',$stamp_date,PDO::PARAM_STR);

            $stmt->execute();

        } catch (PDOException $e) {
            error_log("Update error: " . $e->getMessage());
            return false;
        }
        return true;
    }

    public function get_completed_kadai_by_student_and_type(int $kamoku_id, int $kadai_type_id, string $email) {
        try {
            $dbh = DAO::get_db_connect();
    
            $sql = "SELECT COUNT(*) AS completed_kadai
                    FROM kadai_answer
                    INNER JOIN kadai ON kadai_answer.kadai_id = kadai.kadai_id
                    WHERE kadai.kamoku_id = :kamoku_id
                    AND kadai.kadai_type_id = :kadai_type_id
                    AND kadai_answer.email = :email
                    AND kadai_answer.is_stamp_check = 1
                    ORDER BY completed_kadai;
                ";
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(':kamoku_id', $kamoku_id, PDO::PARAM_INT);
            $stmt->bindValue(':kadai_type_id', $kadai_type_id, PDO::PARAM_INT);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);   // 連想配列
            return $result['completed_kadai'];

        }catch (PDOException $e) {
            error_log("エラー: " . $e->getMessage());
            return 0;
        }
    }

    public function comment_update(string $email, int $kadai_id, string $teacher_comment){
        $dbh = DAO::get_db_connect();
        
        try {
            $sql = "UPDATE kadai_answer SET teacher_comment = :teacher_comment WHERE email=:email AND kadai_id = :kadai_id";

            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(':kadai_id',$kadai_id,PDO::PARAM_INT);
            $stmt->bindValue(':email',$email,PDO::PARAM_STR);
            $stmt->bindValue(':teacher_comment',$teacher_comment,PDO::PARAM_STR);

            $stmt->execute();

        } catch (PDOException $e) {
            error_log("Update error: " . $e->getMessage());
            return false;
        }
    } 
    
}