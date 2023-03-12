<?php

// データベースクラス
class Database
{
    const DATABASE = 'mysql:dbname=freemarket;host=localhost;charset=utf8';
    const DATABASE_USER = 'root';
    const DATABASE_PASSWORD = 'root';
    const DATABASE_OPTIONS = array(
        //SQL実行失敗時に例外をフォロー
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        //デフォルトフェッチモードを連想配列形式に設定
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        //バッファードクエリを使う（一度に結果セットを全て取得し、サーバー負荷を軽減）
        //SELECTで得た結果に対してもrowCountメソッドを使えるようにする
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
    );

    private $dbh;

    // 初期化
    public function __construct($dbh,$sql, $data)
    {
        $this->dbh = $dbh;
    }

    // DB接続
    public static function dbConnect()
    {
        //PDOオブジェクト生成（DBへ接続）
        $dbh = new PDO(self::DATABASE, self::DATABASE_USER, self::DATABASE_PASSWORD, self::DATABASE_OPTIONS);
        return $dbh;
    }
}
