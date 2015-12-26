<?php
/*
 * 目的
 * 1.実現したい機能を複数クラスに散らす。継承ではこれができない。
 * 2.上位クラスが持つ機能を最小限に出来る(これは継承でも実現可能)
*/

/*
 * MEMO
 * 継承 vs Decoratorパターン
 * 継承 機能の静的な拡張
 * Decoratorパターン 機能の動的な拡張
 * 
 * Component ClassとDecoretor Classは、以下の原則で機能を持たせること
 * どんな場合でも必要とされる機能 Concrete Component Class
 * 場合によっては必要とされる機能 Concrete Decorator Class
 * 
 * Decoratorパターンの最大のメリット
 * 機能を複数のDecoratorクラスに分解できること。
 * 継承では、全組み合わせ分のクラスを作る必要が出てくる。
 * 
 * Decoratorパターンのデメリットは何だろう?
 * ->動的なインスタンス生成が発生するので、パフォーマンスが落ちる?
 * ->Decorator ClassでComponent Classのラッピングが必要になる。これが面倒。
*/

/*
 * 参考
 * PHPによるデザインパターン
 * http://d.hatena.ne.jp/shimooka/20141217/1418788239
*/


/*
 * Component Class
 */
interface Text {
    public function getText();
    public function setText($str);
}


/*
 * Concrete Component Class
 */
class PlainText implements Text
{

    private $textString = null;

    public function getText()
    {
        return $this->textString;
    }

    public function setText($str)
    {
        $this->textString = $str;
    }
}


/*
 * Decorator Class
 */
abstract class TextDecorator implements Text
{
    //Component Classを委譲で保持する
    private $text;

    public function __construct(Text $target)
    {
        $this->text = $target;
    }
    
    //Component Classのメソッドをラッピングする
    public function getText()
    {
        return $this->text->getText();
    }

    public function setText($str)
    {
        $this->text->setText($str);
    }
}


/*
 * Concrete Decorator Class
 */
class UpperCaseText extends TextDecorator
{
    public function __construct(Text $target)
    {
        parent::__construct($target);
    }

    //こんな感じで、必要なメソッドだけオーバーライドする
    public function getText()
    {
        $str = parent::getText();
        $str = mb_strtoupper($str);
        return $str;
    }
}


class DoubleByteText extends TextDecorator
{
    public function __construct(Text $target)
    {
        parent::__construct($target);
    }

    public function getText()
    {
        $str = parent::getText();
        $str = mb_convert_kana($str,"RANSKV");
        return $str;
    }
}


/*
 * Client
 */
$text = (isset($_POST['text'])? $_POST['text'] : '');
$decorate = (isset($_POST['decorate'])? $_POST['decorate'] : array());
if ($text !== '') {
    $text_object = new PlainText();
    $text_object->setText($text);
    
    //ココに注目!新たに生成されるDecoretorClassが、
    //以前のComponentClassやDecoratorクラスを保持するという構成になっている。
    foreach ($decorate as $val) {
        switch ($val) {
            case 'double':
                $text_object = new DoubleByteText($text_object);
                break;
            case 'upper':
                $text_object = new UpperCaseText($text_object);
                break;
            default:
                throw new RuntimeException('invalid decorator');
        }
    }
    echo htmlspecialchars($text_object->getText(), ENT_QUOTES, mb_internal_encoding()) . "<br>";
}
?>
<hr>
<form action="" method="post">
テキスト：<input type="text" name="text"><br>
装飾：<input type="checkbox" name="decorate[]" value="upper">大文字に変換
<input type="checkbox" name="decorate[]" value="double">2バイト文字に変換
<input type="submit">
</form>
