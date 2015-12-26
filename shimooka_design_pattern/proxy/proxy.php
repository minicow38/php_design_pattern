<?php

/*
 * 目的
 * 1.本物のオブジェクトと、ダミーのオブジェクトを切り替える
 *  例えば以下のようなケースで使う
 *   DBへアクセスを行うクラス
 *   ネットワーク接続を行うクラス
 *   インスタンス化すると馬鹿みたいにメモリを消費するクラス
 */

/*
 * MEMO
 * Proxy Classは、Real Subject Classと全く同じように扱える。
 * このことを、「透過的インターフェース」と呼ぶ
 * 
 * 今回のサンプルには含まれていないが、
 * Proxy Classに、各RealSubject Classに共通する処理をまとめても構わない。
 * これはC++のNVI(URL参照)と似た思想だろう。
 * 
 * 特に、テストでこのProxyパターンを利用できそう。
 * どのように使うか、もう少し勉強してみよう。
 */

/*
 * 参考
 * PHPによるデザインパターン
 * http://d.hatena.ne.jp/shimooka/20141218/1418888727
 * 
 * C++ NVI(NonVirtualInterface)
 * http://codezine.jp/article/detail/7294
 */

/*
 * Item Class(他のクラスから利用)
 */
class Item
{
    private $id;
    private $name;
    public function __construct($id, $name)
    {
        $this->id = $id;
        $this->name = $name;
    }
    public function getId()
    {
        return $this->id;
    }
    public function getName()
    {
        return $this->name;
    }
}


/*
 * Subject Class
 */
interface ItemDao {
    public function findById($item_id);
}


/*
 * Real Subject Class
 */
//実際のDBからアクセスを行うクラス(今回は、ファイル入出力で代用)
class DbItemDao implements ItemDao
{
    public function findById($item_id)
    {
        $fp = fopen('item_data.txt', 'r');

        //ヘッダ行を飛ばす
        $dummy = fgets($fp, 4096);

        //一行一行読み込む
        $item = null;
        while ($buffer = fgets($fp, 4096)) {
            $id = trim(substr($buffer, 0, 10));
            $name = trim(substr($buffer, 10));

            if ($item_id === (int)$id) {
                $item = new Item($id, $name);
                break;
            }
        }

        fclose($fp);

        return $item;
    }
}

//DBへのアクセスを行わず、ダミーのテストデータを返すクラス。
class MockItemDao implements ItemDao
{
    public function findById($item_id)
    {
        $item = new Item($item_id, 'ダミー商品');
        return $item;
    }
}


/*
 * Proxy Class
 */
//RealSubject Classと同じインターフェースを実装する。
//これにより、RealSubjectとProxyは同じように扱える(透過的)
class ItemDaoProxy implements ItemDao
{
    private $dao;   //RealSubject Classを委譲で保持する
    private $cache; //データのキャッシュ機能を持つ
   
    public function __construct(ItemDao $dao)
    {
        $this->dao = $dao;      //コンストラクタでRealSubject Classのインスタンスをセット。
                                //これにより、RealSubject Classのすげ替えを可能にする。
        $this->cache = array();
    }
    public function findById($item_id)
    {
        //キャッシュがある場合
        if (array_key_exists($item_id, $this->cache)) {
            echo '<font color="#dd0000">Proxyで保持しているキャッシュからデータを返します</font><br>';
            return $this->cache[$item_id];
        }
        
        //キャッシュが無い場合
        $this->cache[$item_id] = $this->dao->findById($item_id);//実際の処理は、RealSubjectに委譲する
        return $this->cache[$item_id];
    }
}


/*
 * Client
 */
//こんな感じで使う。
//RealSubjectもProxyも同じインタフェースを保持している。
//だから、Proxyクラスを経由しないでRealSubjectを使うことも可能(透過的)。
if (isset($_POST['dao']) && isset($_POST['proxy'])) {
    //まずはRealSubjectの種類をチェック
    $dao = $_POST['dao'];
    switch ($dao) {
        case 1:
            //include_once 'MockItemDao.class.php';
            $dao = new MockItemDao();
            break;
        default:
            //include_once 'DbItemDao.class.php';
            $dao = new DbItemDao();
            break;
    }

    //次に、Proxyの使用をチェック
    $proxy = $_POST['proxy'];
    switch ($proxy) {
        case 1:
            //include_once 'ItemDaoProxy.class.php';
            $dao = new ItemDaoProxy($dao);
            break;
    }
    
    //データの出力
    for ($item_id = 1; $item_id <= 3; $item_id++) {
        $item = $dao->findById($item_id);
        echo 'ID=' . $item_id . 'の商品は「' . $item->getName() . '」です<br>';
    }

    //再度データの出力(ここでキャッシュが使われる)
    $item = $dao->findById(2);
    echo 'ID=' . $item->getId() . 'の商品は「' . $item->getName() . '」です<br>';
}
?>
<hr>
<form action="" method="post">
  <div>
    Daoの種類：
    <input type="radio" name="dao" value="0" checked>DbItemDao
    <input type="radio" name="dao" value="1">MockItemDao
  </div>
  <div>
    Proxyの利用：
    <input type="radio" name="proxy" value="0" checked>しない
    <input type="radio" name="proxy" value="1">する
  </div>
  <div>
    <input type="submit">
  </div>
</form>