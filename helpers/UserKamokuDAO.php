<?php
    require_once 'DAO.php';

class UserKamoku{
    public int $kamoku_id;
    public string $email;
}

class UserKamokuDAO{

    public function add_user_to_kamoku(int $kamoku_id, string $email): bool {
        $dbh = DAO::get_db_connect();
        try {
            $sql = "
                MERGE INTO user_kamoku AS target
                USING (SELECT :kamoku_id AS kamoku_id, :email AS email) AS source
                ON target.kamoku_id = source.kamoku_id AND target.email = source.email
                WHEN NOT MATCHED THEN
                    INSERT (kamoku_id, email) VALUES (source.kamoku_id, source.email);
            ";
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(':kamoku_id', $kamoku_id, PDO::PARAM_INT);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("エラー: " . $e->getMessage());
            return false;
        }
    }

    public function remove_user_from_kamoku(int $kamoku_id, string $email): bool {
        $dbh = DAO::get_db_connect();
        try {
            $sql = "DELETE FROM user_kamoku WHERE kamoku_id = :kamoku_id AND email = :email";
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(':kamoku_id', $kamoku_id, PDO::PARAM_INT);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("エラー: " . $e->getMessage());
            return false;
        }
    }

    public function get_users_by_kamoku(int $kamoku_id): array {
        $dbh = DAO::get_db_connect();

        try {
            // この科目に既に登録されているユーザーを取得
            $sql = "SELECT email FROM user_kamoku WHERE kamoku_id = :kamoku_id ";
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(':kamoku_id', $kamoku_id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_COLUMN);  // 一つの列を返す、ここはemail
        } catch (PDOException $e) {
            error_log("エラー: " . $e->getMessage());
            return [];
        }
    }

    public function get_userKamoku_by_email(string $email): array {
        $dbh = DAO::get_db_connect();

        try {
            // このユーザーが入ってる科目を取得
            $sql = "SELECT kamoku.kamoku_id, kamoku.kamoku_name
                    FROM kamoku
                    INNER JOIN user_kamoku ON kamoku.kamoku_id = user_kamoku.kamoku_id
                    WHERE user_kamoku.email = :email
                    ORDER BY kamoku.kamoku_name";

            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(':email', $email, PDO::PARAM_INT);
            $stmt->execute();

            $data = [];
            while ($row = $stmt->fetchObject('Kamoku')) {
                $data[] = $row;
            }

            return $data;  // 一つの列を返す、ここはemail
        } catch (PDOException $e) {
            error_log("エラー: " . $e->getMessage());
            return [];
        }
    }

}

