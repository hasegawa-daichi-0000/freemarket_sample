<?php

require('Class/DB/Query.php');

//ユーザー登録クラス
class Register
{
    private $email;
    private $pass;

    const REGISTER_SQL = 'INSERT INTO users (email, pass, login_time, create_date) VALUES(:email, :pass, :login_time, :create_date)';

    // 初期化
    public function __construct($email, $pass)
    {
        $this->email = $email;
        $this->pass = $pass;
    }

    // ユーザー登録
    public function signup($email, $pass)
    {
        $sql = self::REGISTER_SQL;
        $data = array(
            ':email' => $email,
            ':pass' => password_hash($pass, PASSWORD_DEFAULT),
            ':login_time' => date('Y-m-d H:i:s'),
            ':create_date' => date('Y-m-d H:i:s')
        );
        //クエリ実行
        $stmt = Query::queryPost($sql, $data);

        if ($stmt) {
            //ログイン有効時間（デフォルトを1時間とする）
            $sesLimit = 60 + 60;
            //最終ログイン日時を現在日時
            $_SESSION['login_date'] = time();
            $_SESSION['login_limit'] = $sesLimit;
            //ユーザーIDを格納
            $_SESSION['user_id'] = Database::dbConnect()->lastInsertId();
            debug('セッション変数の中身:' . print_r($_SESSION, true));
            header("Location:mypage.php"); //マイページへ
        }
    }
}
