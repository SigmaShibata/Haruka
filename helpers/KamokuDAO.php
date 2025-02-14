<?php
    require_once 'DAO.php';

class Kamoku{
    public int $kamoku_id;
    public string $kamoku_name;
}

class KamokuDAO{
    
    public function insert(Kamoku $kamoku): bool{
        try {
            $dbh = DAO::get_db_connect();
            $sql = "INSERT INTO kamoku(kamoku_name) VALUES(:kamoku_name)";

            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(':kamoku_name',$kamoku->kamoku_name,PDO::PARAM_STR);

            $stmt->execute();
            return true;

        } catch (PDOException $e) {
            error_log("エラー: " . $e->getMessage());
            return false;
        }
    }

    public function kamoku_exists(string $kamoku_name){
        $dbh = DAO::get_db_connect();
    
        $sql="SELECT * FROM kamoku WHERE kamoku_name=:kamoku_name";
    
        $stmt=$dbh->prepare($sql);
        $stmt->bindValue(':kamoku_name',$kamoku_name,PDO::PARAM_STR);
    
        $stmt->execute();
    
        if($stmt->fetch()!==false){
            return true;
        }else{
            return false;
        }
    }

    public function kamoku_exists_exactMatch(string $kamoku_name){
        $dbh = DAO::get_db_connect();
    
        $sql="SELECT * FROM kamoku WHERE kamoku_name=:kamoku_name COLLATE Japanese_CS_AS";
    
        $stmt=$dbh->prepare($sql);
        $stmt->bindValue(':kamoku_name',$kamoku_name,PDO::PARAM_STR);
    
        $stmt->execute();
    
        if($stmt->fetch()!==false){
            return true;
        }else{
            return false;
        }
    }

    public function get_kamoku(){
        try{
            $dbh = DAO::get_db_connect();
    
            $sql="SELECT * FROM kamoku";

            $stmt = $dbh->prepare($sql);
            $stmt->execute();

            $data = [];
            while($row = $stmt->fetchObject('kamoku')){
                $data[] = $row;
            }
            return $data;

        }catch (PDOException $e) {
            error_log("エラー: " . $e->getMessage());
            return [];
        }
    }

    public function get_kamokuID($kamoku_name){
        try{
            $dbh = DAO::get_db_connect();
    
            $sql = "SELECT kamoku_id FROM kamoku WHERE kamoku_name=:kamoku_name ";

            $stmt=$dbh->prepare($sql);
            $stmt->bindValue(':kamoku_name',$kamoku_name,PDO::PARAM_STR);
    
            $stmt->execute();

            $data = $stmt->fetchObject('kamoku');
            return $data->kamoku_id;

        }catch (PDOException $e) {
            error_log("エラー: " . $e->getMessage());
            return "エラー";;
         }
    }

    public function get_kamokuName($kamoku_id){
        try{
            $dbh = DAO::get_db_connect();
    
            $sql = "SELECT kamoku_name FROM kamoku WHERE kamoku_id=:kamoku_id ";

            $stmt=$dbh->prepare($sql);
            $stmt->bindValue(':kamoku_id',$kamoku_id,PDO::PARAM_STR);
    
            $stmt->execute();

            $data = $stmt->fetchObject('kamoku');
            return $data->kamoku_name;

        }catch (PDOException $e) {
            error_log("エラー: " . $e->getMessage());
            return "エラー";;
         }
    }

    public function get_kamoku_by_kamoku_id(int $kamoku_id) {
        $dbh = DAO::get_db_connect();

        try {
            $sql = "SELECT * FROM kamoku WHERE kamoku_id = :kamoku_id";
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(':kamoku_id', $kamoku_id, PDO::PARAM_INT);
            $stmt->execute();

            $data = $stmt->fetchObject('Kamoku');
            return $data;

        } catch (PDOException $e) {
            error_log("エラー: " . $e->getMessage());
            return [];
        }
    }

    public function delete_kamoku_by_id(int $kamoku_id): bool {
        $dbh = DAO::get_db_connect();
    
        try {
            $sql = "DELETE FROM kamoku WHERE kamoku_id = ?";
            $stmt = $dbh->prepare($sql);
            $stmt->execute([$kamoku_id]);

            return true;

        } catch (PDOException $e) {
             // 外部キー制約違反エラー (SQLSTATE 23000)
            if ($e->getCode() == '23000') {
                error_log("削除エラー: 外部キー制約違反 - 科目ID: " . $kamoku_id);
                return false; // 削除失敗時false
            }
            // その他のエラーはログに記録して再スロー
            error_log("削除エラー: " . $e->getMessage());
            throw $e;
        }
    }


    public function update_kamoku(int $kamoku_id, string $kamoku_name) {
        $dbh = DAO::get_db_connect();
    
        try {
            $sql = "
                UPDATE kamoku 
                SET kamoku_name = ?
                WHERE kamoku_id = ?
            ";
            $stmt = $dbh->prepare($sql);
            $stmt->execute([$kamoku_name, $kamoku_id]);

        } catch (PDOException $e) {
            error_log("更新エラー: " . $e->getMessage());
        }
    }
}

