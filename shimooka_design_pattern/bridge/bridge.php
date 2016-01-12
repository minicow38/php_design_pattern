<?php
/*
 * 目的
 * 1.クラス階層を理解しやすくする。
 *   (クラスが機能と実装で二分されるから。
 *   一つのクラスに機能も実装も詰め込んだ場合と比べて、明らかに分かりやすい)
 * 2.機能と実装が複数パターン存在する場合、bridgeパターンを使えば明らかにコード量を抑えられる。
 */


/*
 * MEMO
 * ・AbstractionClassが特に重要。ここで必要なインタフェースをミスると後からAbstractionClassを変更する必要がある。
 * ・何を持って機能と実装の線引きをするのかが難しいと感じた。
 * ・構造の理解自体は難しくない。TemplateMethod * 機能と実装の分離
 */


/**
 * Implementor Class
 */
interface DataSource {
	public function open();
	public function read();
	public function close();
}


/**
 * ConcreteImplementor Class
 */
class FileDataSource implements DataSource
{
	private $source_name;
	private $handler;

	function __construct($source_name) {
		$this->source_name = $source_name;
	}

	function open() {
		if (!is_readable($this->source_name)) {
			throw new Exception('データソースが見つかりません');
		}
		$this->handler = fopen($this->source_name, 'r');
		if (!$this->handler) {
			throw new Exception('データソースのオープンに失敗しました');
		}
	}

	function read() {
		$buffer = array();
		while (!feof($this->handler)) {
			$buffer[] = fgets($this->handler);
		}
		return join($buffer);//implode()のエイリアス。配列の要素を一つの文字列に結合する。
	}

	function close() {
		if (!is_null($this->handler)) {
			fclose($this->handler);
		}
	}
}


/**
 * Abstraction Class
 * 「機能」側を担うクラスの親となる。
 * 機能と実装をつなぐ橋の役目を担う。Bridge Classと呼んだ方がわかりやすいんじゃ...。
 */
class Listing
{
	private $data_source;//Imprementorを移譲する

	function __construct($data_source) {
		$this->data_source = $data_source;
	}

	function open() {
		$this->data_source->open();//当然、実装は全てImprementorに投げる
	}

	function read() {
		return $this->data_source->read();
	}

	function close() {
		$this->data_source->close();
	}
}


/**
 * Concrete Abstraction Class
 */
class ExtendedListing extends Listing
{
	function __construct($data_source) {
		parent::__construct($data_source);
	}

	function readWithEncode() {
		return htmlspecialchars($this->read(), ENT_QUOTES, mb_internal_encoding());
	}
}



/**
 * Client
 */
$list1 = new Listing(new FileDataSource('data.txt'));
$list2 = new ExtendedListing(new FileDataSource('data.txt'));

try {
	$list1->open();
	$list2->open();
}
catch (Exception $e) {
	die($e->getMessage());
}

$data = $list1->read();
echo $data;

$data = $list2->readWithEncode();
echo $data;

$list1->close();
$list2->close();
