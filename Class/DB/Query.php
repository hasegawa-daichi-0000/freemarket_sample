<?php

require('Class/DB/Database.php');

// データベースクラス
class Query
{
    private $sql;
    private $data;

    // 初期化
    public function __construct($sql, $data)
    {
        $this->sql = $sql;
        $this->data = $data;
    }

    // クエリ実行
    public static function queryPost($sql, $data)
    {
        //例外処理
        try {
            //クエリ作成
            $stmt = Database::dbConnect()->prepare($sql);
            //プレースホルダーに値をセットし、SQL文を実行
            if (!$stmt->execute($data)) {
                debug('クエリに失敗しました。');
                // エラーメッセージ系のものも後で切り出す。
                // $err_msg['common'] = MSG07; 
                return 0;
            };
            debug('クエリ成功');
            return $stmt;
        } catch (\Exception $e) {
            error_log('エラー発生:' . $e->getMessage());
            // エラーメッセージ系のものも後で切り出す。
            // $err_msg['common'] = MSG07; 
        }
    }
}
