<?php
/*
 * 目的
 * 1.木構造を再現する
 * 2.単一のオブジェクトとその集合を同一視する。
 * ファイルとフォルダが典型的な例。
*/

/**
 * Componentクラスに相当する
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

	public function dump()
	{
		echo $this->code . ":" . $this->name . "<br>\n";
	}
}


/**
 * Composite Class
 * 木構造の枝を表す
 */
class Group extends OrganizationEntry
{

	private $entries;

	public function __construct($code, $name)
	{
		parent::__construct($code, $name);
		$this->entries = array();
	}

	//Composite側で、必要なメソッドはオーバーライドする
	public function add(OrganizationEntry $entry)
	{
		array_push($this->entries, $entry);
	}

	public function dump()
	{
		parent::dump();
		foreach ($this->entries as $entry) {
			$entry->dump();
		}
	}
}


/**
 * Leaf Class
 * 木構造の末端を表す
 */
class Employee extends OrganizationEntry
{
	public function __construct($code, $name)
	{
		parent::__construct($code, $name);
	}

	//Leafクラスは歯止めになるから、持つべきでないメソッドはこんな感じでオーバーライドする
	public function add(OrganizationEntry $entry)
	{
		throw new Exception('method not allowed');
	}
}


/**
 * Client
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

$root_entry->dump();