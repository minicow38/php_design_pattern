<?php
/*
 * 目的
 * 1.インスタンス化を一度だけにし、コストを節約する
 * →DBからインスタンスをデシリアライズするコスト、不必要なオブジェクトがメモリを占有するコスト
 /

/*
 * MEMO
 * flyweightでインスタンス化するのは、複数種のオブジェクトが生成されるけど、それぞれのオブジェクトは１つしか使われないもの。
 * 例えば、各商品の定価情報など
 * 
 * Flyweight Classを継承するのは、intrinsic(本質的な)クラスと、extrinsic(非本質的な)クラスに分かれる。
 * この例では、extrinsicな例がないので、後で勉強する必要がある。
 */

/* 
 *
 * 参考
 * PHPによるデザインパターン
 * http://d.hatena.ne.jp/shimooka/20141217/1418788238
 */


/**
 * Flyweight Class
 * ConcreteFlyweight Class
 */
class Item
{
    private $code;
    private $name;
    private $price;

    public function __construct($code, $name, $price)
    {
        $this->code = $code;
        $this->name = $name;
        $this->price = $price;
    }

    public function getCode()
    {
        return $this->code;
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


/**
 * FlyweightFactory Class
 */
class ItemFactory
{
    private $pool;
    private static $instance = null;

    //Factory生成時に、全てのItemインスタンスを生成する
    private function __construct($filename)
    {
        $this->buildPool($filename);
    }

    public static function getInstance($filename) {
        if (is_null(self::$instance)) {//シングルトン
            self::$instance = new ItemFactory($filename);
        }
        return self::$instance;
    }

    // ConcreteFlyweightを返す
    public function getItem($code)
    {
        if (array_key_exists($code, $this->pool)) {
            return $this->pool[$code];
        } else {
            return null;
        }
    }

    // データを読み込み、プールを初期化する
    private function buildPool($filename)
    {
        $this->pool = array();

        $fp = fopen($filename, 'r');
        while ($buffer = fgets($fp, 4096)) {
            list($item_code, $item_name, $price) = split("\t", $buffer);
            $this->pool[$item_code] = new Item($item_code, $item_name, $price);
        }
        fclose($fp);
    }

    public final function __clone() {
        throw new RuntimeException ('Clone is not allowed against ' . get_class($this));
    }
}


/**
 * Client Class
 */
function dumpData($data) {
    echo '<dl>';
    foreach ($data as $object) {
        echo '<dt>' . htmlspecialchars($object->getName(), ENT_QUOTES, mb_internal_encoding()) . '</dt>';
        echo '<dd>商品番号：' . $object->getCode() . '</dd>';
        echo '<dd>\\' . number_format($object->getPrice()) . '-</dd>';
    }
    echo '</dl>';
}

$factory = ItemFactory::getInstance('data.txt');


$items = array();
$items[] = $factory->getItem('ABC0001');
$items[] = $factory->getItem('ABC0002');
$items[] = $factory->getItem('ABC0003');

if ($items[0] === $factory->getItem('ABC0001')) {
    echo '同一のオブジェクトです';
} else {
    echo '同一のオブジェクトではありません';
}

dumpData($items);