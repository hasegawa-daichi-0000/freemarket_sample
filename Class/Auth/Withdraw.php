<?php

require('Class/DB/Query.php');

//退会クラス
class Withdraw
{
    private $userId;

    //DELETEで物理削除ではなく、delete_flgで論理削除
    const WITHDRAW_USER_SQL =  'UPDATE users SET delete_flg = 1 WHERE id = :user_id';
    const WITHDRAW_PRODUCT_SQL = 'UPDATE products SET delete_flg = 1 WHERE user_id = :user_id';

    // 初期化
    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    //退会
    public function withDraw($userId)
    {
        $userSql = self::WITHDRAW_USER_SQL;
        $productSql = self::WITHDRAW_PRODUCT_SQL;

        $data = array(':user_id' => $userId);

        $stmtUser = Query::queryPost($userSql, $data);
        $stmtProduct = Query::queryPost($productSql, $data);

        if ($stmtUser && $stmtProduct) {
            //セッション削除（つまり、ログアウト）
            session_destroy();
            debug('セッションの中身:' . print_r($_SESSION, true));
            debug('トップページへ遷移します。');
            header('Location: index.php');
        }
    }
}
