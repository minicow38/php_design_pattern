<?php
ini_set( 'display_errors', 1 );
require_once '../phpLib/myLib.php';



/*
 * 目的
 * 1.クライアントからサブシステムを隠蔽する・クライアント-サブシステムを疎結合にする
 * 2.クラスやメソッドの呼び出し順を規定して、使いやすくする
 */

/*
 * MEMO
 * 1.逆の見方として、Facadeパターンを積極的に使うようにすれば、ロジックを小粒のクラスやメソッドに切り出せるはず。
 * 2.Facadeパターンは、メインロジックを小さく分解することに近い印象を受けた。
 * 3.複数人での開発で有効なパターンかと。他の人が知らなくても良いクラスやメソッドは、Facadeで隠そう。
 *   少しだけのクラスや関数を隠蔽するなら、Adapterパターンでもいいかもね。
 */

/*
 * 参考
 * PHPによるデザインパターン
 * http://d.hatena.ne.jp/shimooka/20141215/1418620292
 */


/*
 * SubSystem Class
 */
 //1つ1つの商品を表す
class Item
{
    private $id;
    private $name;
    private $price;
    public function __construct($id, $name, $price)
    {
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
    }
    public function getId()
    {
        return $this->id;
    }
    public function getName()
    {
        return $this->name;
    }
    public function getPrice()
    {
        return $this->price;
    }
}

//1つ1つの注文を表す
class OrderItem
{
    private $item;
    private $amount;
    public function __construct(Item $item, $amount)
    {
        $this->item = $item;
        $this->amount = $amount;
    }
    public function getItem()
    {
        return $this->item;
    }
    public function getAmount()
    {
        return $this->amount;
    }
}

//複数の注文を表す
class Order
{
    private $items;
    public function __construct()
    {
        $this->items = array();
    }
    public function addItem(OrderItem $order_item)
    {
        $this->items[$order_item->getItem()->getId()] = $order_item;
    }
    public function getItems()
    {
        return $this->items;
    }
}

//ItemDaoクラスとOrderDaoクラスは、以下のようなユーティリティメソッドを提供するクラスとなっている
//良いメソッドが提供されているものの、それをどう使うかはクライアント次第
//->facadeパターンの出番
class ItemDao
{
    private static $instance;
    private $items;
    private function __construct()
    {
        $fp = fopen('item_data.txt', 'r');

         //ヘッダ行を抜く
        $dummy = fgets($fp, 4096);
        
        //1行1行読みだして、インスタンス化。
        //10byteでid/name/priceを区切ったデータを想定している
        $this->items = array();
        while ($buffer = fgets($fp, 4096)) {
            $item_id = trim(substr($buffer, 0, 10));
            $item_name = trim(substr($buffer, 10, 20));
            $item_price = trim(substr($buffer, 30));

            $item = new Item($item_id, $item_name, $item_price);
            $this->items[$item->getId()] = $item;
        }

        fclose($fp);
    }

    //シングルトン
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new ItemDao();
        }
        return self::$instance;
    }

    public function findById($item_id)
    {
        if (array_key_exists($item_id, $this->items)) {
            return $this->items[$item_id];
        } else {
            return null;
        }
    }

    public function setAside(OrderItem $order_item)
    {
        //実際には、データベースの更新処理が入る
        echo $order_item->getItem()->getName() . 'の在庫引当をおこないました<br>';
    }

    public final function __clone() {
        throw new RuntimeException ('Clone is not allowed against ' . get_class($this));
    }
}

class OrderDao
{
    public static function createOrder(Order $order) {
        echo '以下の内容で注文データを作成しました';

        echo '<table border="1">';
        echo '<tr>';
        echo '<th>商品番号</th>';
        echo '<th>商品名</th>';
        echo '<th>単価</th>';
        echo '<th>数量</th>';
        echo '<th>金額</th>';
        echo '</tr>';

        foreach ($order->getItems() as $order_item) {
            echo '<tr>';
            echo '<td>' . $order_item->getItem()->getId() . '</td>';
            echo '<td>' . $order_item->getItem()->getName() . '</td>';
            echo '<td>' . $order_item->getItem()->getPrice() . '</td>';
            echo '<td>' . $order_item->getAmount() . '</td>';
            echo '<td>' . ($order_item->getItem()->getPrice() * $order_item->getAmount()) . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
}


/*
 * Facadeクラス Class
 */
class OrderManager
{
    public static function order(Order $order) {
        $item_dao = ItemDao::getInstance();             //1.ItemDaoインスタンスを取得し、
        foreach ($order->getItems() as $order_item) {   //2.在庫を引き当て、
            $item_dao->setAside($order_item);
        }

        OrderDao::createOrder($order);                  //3.注文結果を表示する
    }
}


/*
 * Client
 */
/* TODO
 * 以下5行、クライアント側にコードが記述されてしまっている!
 * 以下のようにすると良いかも。
 * 1.OrderクラスやItemDaoクラスの使用箇所は、Facadeにまとめる。
 * 2.クライアント側からは、汎用的なデータ(配列など)を注文情報として渡すようにする。
 */ 
$order = new Order();
$item_dao = ItemDao::getInstance();

$order->addItem(new OrderItem($item_dao->findById(1), 2));
$order->addItem(new OrderItem($item_dao->findById(2), 1));
$order->addItem(new OrderItem($item_dao->findById(3), 3));

//注文処理は、この1行に隠蔽される
OrderManager::order($order);
