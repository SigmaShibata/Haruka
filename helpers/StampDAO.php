<?php
    require_once 'DAO.php';

class Stamp{
    public int $stamp_id;
    public string $stamp_image;
}

class StampDAO{

    public function get_stamp(){
        try{
            $dbh = DAO::get_db_connect();
    
            $sql = "SELECT * FROM stamp";

            $stmt = $dbh->prepare($sql);
            $stmt->execute();

            $data = [];
            while($row = $stmt->fetchObject('Stamp')){
                $data[] = $row;
            }
            return $data;

        }catch (PDOException $e) {
            error_log("エラー: " . $e->getMessage());
            return [];
         }
    }

}

