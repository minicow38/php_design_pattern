<?php


/*
 * 目的
 * 1.似たような処理を共通化しつつ、特定の処理だけ特化させる。
 * (※template methodパターンは、オブジェクト指向の継承と抽象クラスのメリットそのもの)
*/

/*
 * 参考
 * PHPによるデザインパターン
 * http://d.hatena.ne.jp/shimooka/20141212/1418363698
*/

//Abstract Classの役割
//Template Method:処理の順番
//共通処理は実装しておく


/*
 * Abstract Class
 */
//クラスはabstractとしておく。共通部分のメソッドは実装したいので、interfaceにするのはNG。
abstract class AbstractDisplay
{
    private $data;

    public function __construct($data)
    {
        if (!is_array($data)) {
            $data = array($data);
        }
        $this->data = $data;
    }

    //template method。
    //このように処理の大枠/流れとなるものは、Abstract Class側で規定しておく。
    public function display()
    {
        $this->displayHeader();
        $this->displayBody();
        $this->displayFooter();
    }

    public function getData()
    {
        return $this->data;
    }

    protected abstract function displayHeader();

    protected abstract function displayBody();

    protected abstract function displayFooter();
}


/*
 * Concrete Class
 */
class ListDisplay extends AbstractDisplay
{
    protected function displayHeader()
    {
        echo '<dl>';
    }

    protected function displayBody()
    {
        foreach ($this->getData() as $key => $value) {
            echo '<dt>Item ' . $key . '</dt>';
            echo '<dd>' . $value . '</dd>';
        }
    }

    protected function displayFooter()
    {
        echo '</dl>';
    }
}

class TableDisplay extends AbstractDisplay
{

    protected function displayHeader()
    {
        echo '<table border="1" cellpadding="2" cellspacing="2">';
    }

    protected function displayBody()
    {
        foreach ($this->getData() as $key => $value) {
            echo '<tr>';
            echo '<th>' . $key . '</th>';
            echo '<td>' . $value . '</td>';
            echo '</tr>';
        }
    }

    protected function displayFooter()
    {
        echo '</table>';
    }
}


/*
 * Client
 */
$data = array('Design Pattern',
              'Gang of Four',
              'Template Method Sample1',
              'Template Method Sample2');

$display1 = new ListDisplay($data);
$display2 = new TableDisplay($data);

$display1->display();
echo '<hr>';
$display2->display();