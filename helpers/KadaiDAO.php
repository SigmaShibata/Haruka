<?php
    require_once 'DAO.php';

class Kadai{
    public int $kadai_id;
    public int $kamoku_id;
    public string $kadai_week;
    public string $title;
    public int $kadai_type_id;
}

function sampleSort($a, $b)  {  //スタンプシートの課題表示順のソートメソッド
    //Sample問題かどうか
    $IsSampleA = str_contains(strtolower($a['title']), 'sample');
    $IsSampleB = str_contains(strtolower($b['title']), 'sample');

    //Sample問題を先頭に
    if ($IsSampleA && !$IsSampleB) {
        return -1; //$aが前
    } elseif (!$IsSampleA && $IsSampleB) {
        return 1; //$bが前
    }

    //必須とStepUpそれぞれの中アルファベット順
    if ($a['kadai_type_id'] === $b['kadai_type_id']) {
        return strcmp($a['title'], $b['title']); //アルファベット順
    }

if ($a['kadai_type_id'] < $b['kadai_type_id']) { //必須を先頭に、StepUpを後ろに
        return -1; //$aが前
    } else{
        return 1; //$bが前
    }

}


class KadaiDAO{
    public function insert(Kadai $kadai){
        $dbh = DAO::get_db_connect();
        try {
            $sql = "INSERT INTO kadai(kamoku_id, kadai_week, title, kadai_type_id) VALUES(:kamoku_id, :kadai_week, :title, :kadai_type_id)";

            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(':kamoku_id',$kadai->kamoku_id,PDO::PARAM_INT);
            $stmt->bindValue(':kadai_week',$kadai->kadai_week,PDO::PARAM_INT);
            $stmt->bindValue(':title',$kadai->title,PDO::PARAM_STR);
            $stmt->bindValue(':kadai_type_id',$kadai->kadai_type_id,PDO::PARAM_INT);

            $stmt->execute();

        } catch (PDOException $e) {
            error_log("Insert error: " . $e->getMessage());
            return false;
        }
        return true;
    }


    public function kadai_exists(int $kamoku_id, string $kadai_week, string $title){
        $dbh = DAO::get_db_connect();
    
        $sql = "SELECT * FROM kadai WHERE kamoku_id = :kamoku_id AND kadai_week = :kadai_week AND title = :title";
    
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(':kamoku_id',$kamoku_id,PDO::PARAM_INT);
        $stmt->bindValue(':kadai_week',$kadai_week,PDO::PARAM_STR);
        $stmt->bindValue(':title',$title,PDO::PARAM_STR);
        $stmt->execute();
    
        if($stmt->fetch()!==false){
            return true;
        }else{
            return false;
        }

    }

    public function kadai_exists_except_current($kamoku_id, $week, $title, $kadai_id) {
        $dbh = DAO::get_db_connect();

        $sql = "SELECT COUNT(*) FROM kadai 
                WHERE kamoku_id = :kamoku_id 
                  AND kadai_week = :week 
                  AND title = :title
                  AND kadai_id != :kadai_id"; // 現在の課題を除外して検索
    
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(':kamoku_id', $kamoku_id, PDO::PARAM_INT);
        $stmt->bindValue(':week', $week, PDO::PARAM_STR);
        $stmt->bindValue(':title', $title, PDO::PARAM_STR);
        $stmt->bindValue(':kadai_id', $kadai_id, PDO::PARAM_INT);
        $stmt->execute();
    
        return $stmt->fetchColumn() > 0; // 重複するデータが存在する場合は true を返す
    }


    public function get_kadai(){
        try{
            $dbh = DAO::get_db_connect();
    
            $sql = "SELECT * FROM kadai";

            $stmt = $dbh->prepare($sql);
            $stmt->execute();

            $data = [];
            while($row = $stmt->fetchObject('Kadai')){
                $data[] = $row;
            }
            return $data;

        }catch (PDOException $e) {
            error_log("エラー: " . $e->getMessage());
            return [];
        }
    }


    public function get_kadai_ID($kamoku_id, $kadai_week, $title){
        try{
            $dbh = DAO::get_db_connect();
    
            $sql="SELECT kadai_id FROM kadai WHERE kamoku_id = :kamoku_id AND kadai_week = :kadai_week AND title = :title";
    
            $stmt=$dbh->prepare($sql);
            $stmt->bindValue(':kamoku_id',$kamoku_id,PDO::PARAM_INT);
            $stmt->bindValue(':kadai_week',$kadai_week,PDO::PARAM_STR);
            $stmt->bindValue(':title',$title,PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_COLUMN);

        }catch (PDOException $e) {
            error_log("エラー: " . $e->getMessage());
            return "エラー";
        }
    }

    public function get_kadai_by_kadai_id(int $kadai_id) {
        $dbh = DAO::get_db_connect();

        try {
            $sql = "SELECT * FROM kadai WHERE kadai_id = :kadai_id " ;
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(':kadai_id', $kadai_id, PDO::PARAM_INT);
            $stmt->execute();

            $data = $stmt->fetchObject('Kadai');
            return $data;

        } catch (PDOException $e) {
            error_log("エラー: " . $e->getMessage());
            return "エラー";;
        }
    }


    public function get_kadai_with_type_by_kamoku_id(?int $kamoku_id = null) {
        // (?int $kamoku_id = null)当调用这个函数时，如果没有传递 $kamoku_id，它的值就是 null
        $dbh = DAO::get_db_connect();
    
        try {
            // 基本SQL検索
            $sql = "
                SELECT 
                    kadai.kadai_id,
                    kadai.kamoku_id,
                    kadai.kadai_week,
                    kadai.title,
                    kadai.kadai_type_id,
                    mondai_type.kadai_type_name
                FROM kadai
                INNER JOIN mondai_type ON kadai.kadai_type_id = mondai_type.kadai_type_id";
    
            // 条件
            if (!is_null($kamoku_id)) {
                $sql .= " WHERE kadai.kamoku_id = :kamoku_id";
            }
    
            $stmt = $dbh->prepare($sql);
    
            // bindValue
            if (!is_null($kamoku_id)) {
                $stmt->bindValue(':kamoku_id', $kamoku_id, PDO::PARAM_INT);
            }
    
            $stmt->execute();
    
            // 連想配列として返します
            $data = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $data[] = $row;
            }
    
            return $data;
    
        } catch (PDOException $e) {
            error_log("エラー: " . $e->getMessage());
            return [];
        }
    }

    public function delete_kadai_by_id(int $kadai_id): bool {
        $dbh = DAO::get_db_connect();
    
        try {
            $sql = "DELETE FROM kadai WHERE kadai_id = ?";
            $stmt = $dbh->prepare($sql);
            $stmt->execute([$kadai_id]);

            return true;

        } catch (PDOException $e) {
            // 外部キー制約違反エラー (SQLSTATE 23000)
            if ($e->getCode() == '23000') {
                error_log("削除エラー: 外部キー制約違反 - 課題ID: " . $kadai_id);
                return false; // 削除失敗時false
            }
            // その他のエラーはログに記録して再スロー
            error_log("削除エラー: " . $e->getMessage());
            throw $e;
        }
    }


    public function update_kadai(int $kadai_id, string $title, int $kamoku_id, string $kadai_week, int $kadai_type_id) {
        $dbh = DAO::get_db_connect();
    
        try {
            $sql = "
                UPDATE kadai 
                SET title = ?, kamoku_id = ?, kadai_week = ?, kadai_type_id = ?
                WHERE kadai_id = ?
            ";
            $stmt = $dbh->prepare($sql);
            $stmt->execute([$title, $kamoku_id, $kadai_week, $kadai_type_id, $kadai_id]);
            
        } catch (PDOException $e) {
            error_log("更新エラー: " . $e->getMessage());
        }
    }


    public function get_kadai_by_kamoku_week(int $kamoku_id, string $week) {
        $dbh = DAO::get_db_connect();
    
        try {
            $sql = "SELECT * FROM kadai WHERE kamoku_id = :kamoku_id AND kadai_week = :week ORDER BY kadai_type_id";

            $stmt = $dbh->prepare($sql);
    
            $stmt->bindValue(':week',$week,PDO::PARAM_STR);
            $stmt->bindValue(':kamoku_id', $kamoku_id, PDO::PARAM_INT);
           
            $stmt->execute();
    
            // 連想配列として返します
            $data = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $data[] = $row;
            }

            //Sample問題が先頭行くようにソート
            usort($data, 'sampleSort');

            return $data;
    
        } catch (PDOException $e) {
            error_log("エラー: " . $e->getMessage());
            return [];
        }
    }


    public function get_total_kadai_by_type(int $kamoku_id, int $kadai_type_id) {
        try {
            $dbh = DAO::get_db_connect();
    
            $sql = "SELECT COUNT(*) AS total_kadai
                    FROM kadai 
                    WHERE kadai_type_id = :kadai_type_id
                    AND kamoku_id = :kamoku_id
                ";
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(':kamoku_id', $kamoku_id, PDO::PARAM_INT);
            $stmt->bindValue(':kadai_type_id', $kadai_type_id, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);   // 連想配列
            return $result['total_kadai'];

        }catch (PDOException $e) {
            error_log("エラー: " . $e->getMessage());
            return 0;
        }
    }
    
}

