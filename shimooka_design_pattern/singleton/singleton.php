<?php
//初期設定
ini_set( 'display_errors', 1 );
require_once '../phpLib/myLib.php';

/*
 * 目的
 * 1.インスタンスの生成によるコストを防ぐ
 * 2.システム上で一つしか無いものを表現する
*/

/*
 * 参考
 * PHPによるデザインパターン
 * http://d.hatena.ne.jp/shimooka/20141212/1418363981
*/

/*
 * Singleton Class 
 */
class SingletonSample{
    private $id;
    private static $instance;

    //コンストラクタはprivateに
    private function __construct(){
        $this->id = md5(date('r') . mt_rand()); //md5():md5を生成
                                                //date('r'):フォーマットされた日付を表示
                                                //mt_rand():高性能な乱数生成
    }

    public static function getInstance() {
        //自らを表すインスタンスは、関数内static変数としても良い
        if (!isset(self::$instance)) {
            self::$instance = new SingletonSample();
            printLine('a SingletonSample instance was created !', true);
        }
        return self::$instance;
    }

    public function getID(){
        return $this->id;
    }

    //__clone()をオーバーライドして、インスタンスの複製を不可能にする
    public final function __clone() {
        throw new RuntimeException ('Clone is not allowed against ' . get_class($this));    //get_class():インスタンスのクラス名を取得する
    }
}


/*
 * Client
 */
$instance1 = SingletonSample::getInstance();
$instance2 = SingletonSample::getInstance();

//2つのインスタンスは同一であることを確認
if($instance1->getID() === $instance2->getID())
    printLine('Same Instance.',true);
else
    printLine('Different Instance.',true);

//オブジェクトの複製は不可能
$instance1_clone = clone $instance1;
