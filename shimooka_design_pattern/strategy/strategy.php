<?php

/*
 * 目的
 * 1.複雑ロジックの仕様箇所をクラスに切り出し、取り替え可能にする
 */

/*
 * MEMO
 * Strategy部分は、クラスとして切り出すとよい。
 */

/*
 * 参考
 * PHPによるデザインパターン
 * http://d.hatena.ne.jp/shimooka/20141219/1418965548
 * 
 * stdClass
 * http://qiita.com/onomame/items/be2261c6eb566edab030
 */
 
 
 /*
 * Strategy Class
 */
abstract class ReadItemDataStrategy
{
    private $filename;

    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    public function getData()
    {
        if (!is_readable($this->getFilename())) {
            throw new Exception('file [' . $this->getFilename() . '] is not readable !');
        }

        return $this->readData($this->getFilename());
    }

    public function getFilename()
    {
        return $this->filename;
    }

    protected abstract function readData($filename);
}

 /*
 * Concrete Strategy Class
 */
class ReadFixedLengthDataStrategy extends ReadItemDataStrategy
{

    protected function readData($filename)
    {
        $fp = fopen($filename, 'r');
        
        //ヘッダ行を飛ばす
        $dummy = fgets($fp, 4096);

        //一行一行読み込む
        $return_value = array();
        while ($buffer = fgets($fp, 4096)) {
            $item_name = trim(substr($buffer, 0, 20));
            $item_code = trim(substr($buffer, 20, 10));
            $price = (int)substr($buffer, 30, 8);
            $release_date = substr($buffer, 38);

            //忙しくてクラスの定義を書く暇がない場合、このようにstdClassを使うと便利
            $obj = new stdClass();
            $obj->item_name = $item_name;
            $obj->item_code = $item_code;
            $obj->price = $price;
            //mktime($hour,$minute,$second,$month,$date,$year)
            $obj->release_date = mktime(0, 0, 0,
                                        substr($release_date, 4, 2),
                                        substr($release_date, 6, 2),
                                        substr($release_date, 0, 4));

            $return_value[] = $obj;
        }

        fclose($fp);

        return $return_value;
    }
}

class ReadTabSeparatedDataStrategy extends ReadItemDataStrategy
{
    protected function readData($filename)
    {
        $fp = fopen($filename, 'r');

        //ヘッダ行を飛ばす
        $dummy = fgets($fp, 4096);

        //一行一行読み込む
        $return_value = array();
        while ($buffer = fgets($fp, 4096)) {
            list($item_code, $item_name, $price, $release_date) = split("\t", $buffer);

            $obj = new stdClass();
            $obj->item_name = $item_name;
            $obj->item_code = $item_code;
            $obj->price = $price;
            list($year, $mon, $day) = split('/', $release_date);
            $obj->release_date = mktime(0, 0, 0,
                                        $mon,
                                        $day,
                                        $year);

            $return_value[] = $obj;
        }

        fclose($fp);

        return $return_value;
    }
}


/*
 * Context Class
 */
class ItemDataContext
{
    //ロジック部分はStrategy Classに委譲するだけ
    private $strategy;

    public function __construct(ReadItemDataStrategy $strategy)
    {
        $this->strategy = $strategy;
    }

    public function getItemData()
    {
        return $this->strategy->getData();
    }
}


/*
 * Client
 */
function dumpData($data) {
    echo '<dl>';
    foreach ($data as $object) {
        echo '<dt>' . $object->item_name . '</dt>';
        echo '<dd>商品番号：' . $object->item_code . '</dd>';
        echo '<dd>\\' . number_format($object->price) . '-</dd>';
        echo '<dd>' . date('Y/m/d', $object->release_date) . '発売</dd>';
    }
    echo '</dl>';
}

// 固定長データを読み込む
$strategy1 = new ReadFixedLengthDataStrategy('fixed_length_data.txt');
$context1 = new ItemDataContext($strategy1);
dumpData($context1->getItemData());
echo '<hr>';

//タブ区切りデータを読み込む
$strategy2 = new ReadTabSeparatedDataStrategy('tab_separated_data.txt');
$context2 = new ItemDataContext($strategy2);
dumpData($context2->getItemData());
