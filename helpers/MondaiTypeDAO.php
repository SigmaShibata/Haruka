<?php
    require_once 'DAO.php';

class MondaiType{
    public int $kadai_type_id;
    public string $kadai_type_name;
}

class MondaiTypeDAO{

    public function get_mondai_type(){
        try{
            $dbh = DAO::get_db_connect();
    
            $sql = "SELECT * FROM mondai_type ORDER BY kadai_type_id ASC";

            $stmt = $dbh->prepare($sql);
            $stmt->execute();

            $data = [];
            while($row = $stmt->fetchObject('MondaiType')){
                $data[] = $row;
            }
            return $data;

        }catch (PDOException $e) {
            error_log("エラー: " . $e->getMessage());
            return [];
        }
    }


    public function get_mondai_type_name_by_id($kadai_type_id) {
        try {
            $dbh = DAO::get_db_connect();
    
            $sql = "SELECT kadai_type_name FROM mondai_type WHERE kadai_type_id = :kadai_type_id";
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(':kadai_type_id', $kadai_type_id, PDO::PARAM_INT);
            $stmt->execute();
    
            $data = $stmt->fetchObject('MondaiType');
            return $data->kadai_type_name;

        }catch (PDOException $e) {
            error_log("エラー: " . $e->getMessage());
            return "エラー";
        }
    }

}

