<?php
/*
 * 目的
 * 1.「データ構造」と「データ構造に対する処理」を分解する。
 * →新しく処理が増えても、データ構造を保持するクラスを変更する必要がなくなる。
 * 
 * 2.特定のデータ構造に依存しない処理を書くことができる。
 * 
 * 3.複数の操作を、別々のクラスに切り出し保守性をあげる。
 * 
 /

/*
 * MEMO
 * 今回の例では、Compositeパターン(樹構造)に対するVisitorの適用例を見ていく。
 * 
 * データ構造が複雑な場合、特に威力を発揮するパターン。
 */

/* 
 *
 * 参考
 * PHPによるデザインパターン
 * http://d.hatena.ne.jp/shimooka/20141219/1418965547
 */


/**
 * Component Class
 */
abstract class OrganizationEntry
{

    private $code;
    private $name;

    public function __construct($code, $name)
    {
        $this->code = $code;
        $this->name = $name;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getName()
    {
        return $this->name;
    }

    public abstract function add(OrganizationEntry $entry);

    public abstract function getChildren();

    //visitorを呼び出す
    public function accept(Visitor $visitor)
    {
        $visitor->visit($this);
    }
}


/**
 * Composite Class
 */
class Group extends OrganizationEntry
{

    private $entries;

    public function __construct($code, $name)
    {
        parent::__construct($code, $name);
        $this->entries = array();
    }

    public function add(OrganizationEntry $entry)
    {
        array_push($this->entries, $entry);
    }

    public function getChildren()
    {
        return $this->entries;
    }
}

/**
 * Leaf Class
 */
class Employee extends OrganizationEntry
{
    public function __construct($code, $name)
    {
        parent::__construct($code, $name);
    }

    //Leafクラスは子要素を持たないので、例外を発生させている
    public function add(OrganizationEntry $entry)
    {
        throw new Exception('method not allowed');
    }

    public function getChildren()
    {
        return array();
    }
}


/**
 * Visitor Class
 */
interface Visitor {
    public function visit(OrganizationEntry $entry);
}


/**
 * Concrete Visitor Class
 */
class DumpVisitor implements Visitor
{
    public function visit(OrganizationEntry $entry)
    {
        if (get_class($entry) === 'Group') {
            echo '■';
        } else {
            echo '&nbsp;&nbsp;';
        }
        echo $entry->getCode() . ":" . $entry->getName() . "<br>\n";

        //今回の例では、Visitor内にデータ構造を渡り歩く処理を持たせる
        foreach ($entry->getChildren() as $ent) {
            $ent->accept($this);
        }
    }
}

class CountVisitor implements Visitor
{
    private $group_count = 0;
    private $employee_count = 0;

    public function visit(OrganizationEntry $entry)
    {
        if (get_class($entry) === 'Group') {
            $this->group_count++;
        } else {
            $this->employee_count++;
        }
        foreach ($entry->getChildren() as $ent) {
            $this->visit($ent);//$ent->accept($this)と同じ
        }
    }

    public function getGroupCount()
    {
        return $this->group_count;
    }

    public function getEmployeeCount()
    {
        return $this->employee_count;
    }
}


/**
 * 木構造を作成
 */
$root_entry = new Group("001", "本社");
$root_entry->add(new Employee("00101", "CEO"));
$root_entry->add(new Employee("00102", "CTO"));

$group1 = new Group("010", "○○支店");
$group1->add(new Employee("01001", "支店長"));
$group1->add(new Employee("01002", "佐々木"));
$group1->add(new Employee("01003", "鈴木"));
$group1->add(new Employee("01003", "吉田"));

$group2 = new Group("110", "△△営業所");
$group2->add(new Employee("11001", "川村"));
$group1->add($group2);
$root_entry->add($group1);

$group3 = new Group("020", "××支店");
$group3->add(new Employee("02001", "萩原"));
$group3->add(new Employee("02002", "田島"));
$group3->add(new Employee("02002", "白井"));
$root_entry->add($group3);

/**
 * 木構造をダンプ
 */
$root_entry->accept(new DumpVisitor());

/**
 * 同じ木構造に対して、別のVisitorを使用する
 */
$visitor = new CountVisitor();
$root_entry->accept($visitor);
echo '組織数：' . $visitor->getGroupCount() . '<br>';
echo '社員数：' . $visitor->getEmployeeCount() . '<br>';