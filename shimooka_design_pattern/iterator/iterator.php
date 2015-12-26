<?php
ini_set( 'display_errors', 1 );
require_once '../phpLib/myLib.php';

/*
 * 目的
 * 1.オブジェクトの「データ構造」を問わず、
 *   「好きな順番で」アクセスできるコードを実現する※
 * ->データ構造に問わないイテレートができるようになるので、
 *   後からデータ構造を変えることが容易になる
 * 
 * 2.メインロジックからデータ構造に依存したコードを取り除き、
 *   メインロジックをスッキリさせる
 * 
 * ※C++ STLを思い出そう。
 *   どんなデータ構造に対しても、共通のイテレータでアクセスできたはず。
*/

/*
 * MEMO
 * イテレータクラスの考え方
 * 1.リストの構造が分からない(配列、ツリーetc...)
 * 2.目的に応じて取り出す順序を変えたい(性別、年齢etc...)
 * ->考え方を変え、以下のクラスに分けてみる。
 *   要素を「一つだけ」「特定の順番で」取り出すIteratorクラス(データの処理を担当)、
 *   要素の集合であるAggregateクラス(データの格納を担当)
 * 
 * 素人目線で見たIteratorパターンのメリット
 * 1.オブジェクトでも、イテレートが出来るようになる
 * 2.ソートや絞り込みの処理を、ConcreteIteratorクラスの中に閉じ込められる
*/

/*
 * 参考
 * PHPによるデザインパターン
 * http://d.hatena.ne.jp/shimooka/20141215/1418620355
 * 
 * IteratorAggregate
 * http://php.net/manual/ja/class.iteratoraggregate.php
 * 
 * FilterIterator
 * http://php.net/manual/ja/class.filteriterator.php
*/


/*
 * Employee Class
 * (Aggregateクラスの1要素) 
 */
class Employee
{
    private $name;
    private $age;
    private $job;
    public function __construct($name, $age, $job)
    {
        $this->name = $name;
        $this->age = $age;
        $this->job = $job;
    }
    public function getName()
    {
        return $this->name;
    }
    public function getAge()
    {
        return $this->age;
    }
    public function getJob()
    {
        return $this->job;
    }
}


/*
 * Concrete Aggregate Class 
 */
//IteratorAggregateクラスが、AggregateClassに相当。
//このクラスを継承すると、イテレータを使って、
//クラスの集合を配列のように扱うことが出来るようになる
class Employees implements IteratorAggregate
{
    private $employees;
    public function __construct()
    {
        //ArrayObjectクラスは、オブジェクトを配列の用に使えるようにするクラス
        $this->employees = new ArrayObject();
    }
    public function add(Employee $employee)
    {
        $this->employees[] = $employee;
    }
    //IteratorAggregateのサブクラスは、必ずこのメソッドをオーバーライドする。
    public function getIterator()
    {
        //ArrayObjectとすることで、オブジェクトでもイテレータが使えるようになる
        //ArrayObject::getIteratorは、ArrayIteratorを返す
        return $this->employees->getIterator();
    }
}


/*
 * Concrete Iterator Class 
 */
class SalesmanIterator extends FilterIterator
{
    public function __construct($iterator)
    {
        parent::__construct($iterator);
    }
    //FilterIteratorのサブクラスは、必ずこのメソッドをオーバーライドする。
    //true・falseを返すように実装する
    public function accept()
    {
        $employee = $this->getInnerIterator()->current();   //イテレータクラス内部では、こんな感じでgetInnerIteratorをよく使うことになるだろう
        return ($employee->getJob() === 'SALESMAN');
    }
}


/*
 * Client Class 
 */
//ConcreteAggregateインスタンスの生成
$employees = new Employees();
$employees->add(new Employee('SMITH', 32, 'CLERK'));
$employees->add(new Employee('ALLEN', 26, 'SALESMAN'));
$employees->add(new Employee('MARTIN', 50, 'SALESMAN'));
$employees->add(new Employee('CLARK', 45, 'MANAGER'));
$employees->add(new Employee('KING', 58, 'PRESIDENT'));

//iteratorの利用その1
$iterator = $employees->getIterator();
echo '<ul>';
while ($iterator->valid()) {            //validで有効性を検証
    $employee = $iterator->current();   //currentで現在一に対応するオブジェクトを取れる
    printf('<li>%s (%d, %s)</li>',      //タグの出力は、printfを使うとスッキリ書ける
           $employee->getName(),        //こんな感じで、配列的に使いつつも、しっかりオブジェクトのメソッドも使える
           $employee->getAge(),
           $employee->getJob());
    $iterator->next();                  //iteratorを次に進める
    var_dump($iterator->current());
}
echo '</ul>';
echo '<hr>';
$iterator->rewind();                    //iteratorでもう一回イテレートしたい場合、rewind()を呼ぶ
                                        //ここでは、わざわざrewindしている意味は無い

//iteratorの利用その2(foreach)
//※foreach文実行前にrewind()が呼ばれているので、事前に呼び出しておく必要は無い
echo '<ul>';
foreach ($iterator as $employee) {
    printf('<li>%s (%d, %s)</li>',
           $employee->getName(),
           $employee->getAge(),
           $employee->getJob());
}
echo '</ul>';
echo '<hr>';

//iteratorの利用その3(ArrayIterator以外を使ってみる)
//Employeesクラスを全くいじらずに、イテレート処理を変更できていることに注目しよう
$salesmanIterator = new SalesmanIterator($iterator);
echo '<ul>';
foreach ($salesmanIterator as $employee) {
    printf('<li>%s (%d, %s)</li>',
           $employee->getName(),
           $employee->getAge(),
           $employee->getJob());
}
echo '</ul>';
echo '<hr>';

