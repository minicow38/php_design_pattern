<?php
/*
 * 概要
 * インスタンスのコピーに関するパターン。
 * Prototypeという名前からは意外であるが...
 * 
 * 目的
 * 1.何らかの理由で、同じインスタンスを作りたくなった時に必要となる。
 * 2.Factory Classのように、生成専用のクラスを持つ必要がないというメリットもある。
 */

/*
 * MEMO
 * PHPなら、cloneキーワードを使うことで、任意のクラスをコピーすることができる。
 * この時、浅いコピーが行われる。
 * もし独自の__clone()メソッドが実装されていた場合、その__clone()メソッドがcloneキーワードからコールされる。
 * もし深いコピーを行いたいなら、__clone()メソッドをオーバーライドすること。
 * 
 * 浅いコピー
 * 内部で保持している参照をコピーしないコピー。
 * コピーされたインスタンスは、元のオブジェクトと同じメモリを参照する。
 * 
 * 深いコピー
 * 内部で保持している参照もコピーするコピー。
 * 元のインスタンスとコピーされたインスタンスは、
 * それぞれ異なるメモリを参照する。
 * 
 * Concrete Prototype Classの実装だが、
 * 委譲ではダメなのか。委譲の場合、継承関係が変わっても使い回せる可能性が出てくるような...
 */
 
/*
 * 参考
 * PHPによるデザインパターン
 * http://d.hatena.ne.jp/shimooka/20141218/1418888728
 */
 
 /**
 * Prototype Class
 * 通常のクラスに該当する
 */
abstract class ItemPrototype
{
    private $item_code;
    private $item_name;
    private $price;
    private $detail;

    public function __construct($code, $name, $price)
    {
        $this->item_code = $code;
        $this->item_name = $name;
        $this->price = $price;
    }

    public function getCode()
    {
        return $this->item_code;
    }

    public function getName()
    {
        return $this->item_name;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function setDetail(stdClass $detail)
    {
        $this->detail = $detail;
    }

    public function getDetail()
    {
        return $this->detail;
    }

    public function dumpData()
    {
        echo '<dl>';
        echo '<dt>' . $this->getName() . '</dt>';
        echo '<dd>商品番号：' . $this->getCode() . '</dd>';
        echo '<dd>\\' . number_format($this->getPrice()) . '-</dd>';
        echo '<dd>' . $this->detail->comment . '</dd>';
        echo '</dl>';
    }

    //cloneキーワードを使って新しいインスタンスを作成する
    public function newInstance()
    {
        $new_instance = clone $this;
        return $new_instance;
    }

    //protectedメソッドにする事で、外部から直接cloneされないようにしている
    protected abstract function __clone();
}


/**
 * ConcretePrototype Class
 * 親クラスのコピー方法を定義するクラス。
 * 継承側でコピーの方法を実装することで、
 * 元のオブジェクトに影響を与えないし、
 * クラスが継承でまた増えたら、このクラスだけ作り直せば良いことになる。
 */
class DeepCopyItem extends ItemPrototype
{
    /**
     * 深いコピーを行うための実装
     * 内部で保持しているオブジェクトもコピー
     */
    protected function __clone()
    {
        $this->setDetail(clone $this->getDetail());
    }

}

class ShallowCopyItem extends ItemPrototype
{

    //浅いコピーを行うので、空の実装を行う
    //この場合でcloneキーワードを呼ぶと、PHPのデフォルトの実装に応じて浅いコピーが行われる
    protected function __clone()
    {
    }
}


/**
 * Client Class
 * このクラスからはConcretePrototypeクラスは見えていない
 */
class ItemManager
{
    private $items;

    public function __construct()
    {
        $this->items = array();
    }

    public function registItem(ItemPrototype $item)
    {
        $this->items[$item->getCode()] = $item;
    }

    //Prototypeクラスのメソッドを使って、新しいインスタンスを作成
    public function create($item_code)
    {
        if (!array_key_exists($item_code, $this->items)) {
            throw new Exception('item_code [' . $item_code . '] not exists !');
        }
        $cloned_item = $this->items[$item_code]->newInstance();

        return $cloned_item;
    }
}


/**
 * Client
 */
function testCopy(ItemManager $manager, $item_code) {
    /**
     * 商品のインスタンスを2つ作成
     */
    $item1 = $manager->create($item_code);
    $item2 = $manager->create($item_code);

    /**
     * 1つだけコメントを削除
     */
    $item2->getDetail()->comment = 'コメントを書き換えました';

    /**
     * 商品情報を表示
     * 深いコピーをした場合、$item2への変更は$item1に影響しない
     */
    echo '■オリジナル';
    $item1->dumpData();
    echo '■コピー';
    $item2->dumpData();
    echo '<hr>';
}

$manager = new ItemManager();

/**
 * 商品データを登録
 */
$item = new DeepCopyItem('ABC0001', '限定Ｔシャツ', 3800);
$detail = new stdClass();
$detail->comment = '商品Aのコメントです';
$item->setDetail($detail);
$manager->registItem($item);

$item = new ShallowCopyItem('ABC0002', 'ぬいぐるみ', 1500);
$detail = new stdClass();
$detail->comment = '商品Bのコメントです';
$item->setDetail($detail);
$manager->registItem($item);

testCopy($manager, 'ABC0001');
testCopy($manager, 'ABC0002');
