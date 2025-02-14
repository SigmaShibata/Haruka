<?php
    require_once 'DAO.php';

class StampUse{
    public int $stamp_id;
    public string $email;
}

class StampUseDAO{

    public function update(string $email, int $stamp_id){
        try{
            $dbh = DAO::get_db_connect();
    
            $sql = "UPDATE stamp_use SET stamp_id=:stamp_id WHERE email=:email";
            $stmt = $dbh->prepare($sql);

            $stmt->bindValue(':stamp_id',$stamp_id,PDO::PARAM_INT);
            $stmt->bindValue(':email',$email,PDO::PARAM_STR);
            $stmt->execute();


        }catch (PDOException $e) {
            error_log("エラー: " . $e->getMessage());
         }
    }

    public function get_stampID_by_email(string $teacher_email){
        try{
            $dbh = DAO::get_db_connect();
    
            $sql = "SELECT * FROM stamp_use WHERE email = :email";
            $stmt = $dbh->prepare($sql);

            $stmt->bindValue(':email',$teacher_email,PDO::PARAM_STR);
            $stmt->execute();

            $data = $stmt->fetchObject('StampUse');

            return $data->stamp_id;

        }catch (PDOException $e) {
            error_log("エラー: " . $e->getMessage());
            return [];
         }
    }

}

