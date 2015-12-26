<?php
ini_set( 'display_errors', 1 );
require_once '../phpLib/myLib.php';

/*
 * 目的
 * 1.実績のあるクラスを、「変更を加えず」「クライアントに実装を隠して」再利用する
 * ex)特定のAPIの実装を隠して、クライアント側に提供する
 *    実装が汚いクラスを隠蔽する
 * 2.公開するAPIを制限/緩和する
 * ex)隠したいメソッドは、privateでオーバーライドする
 *    公開したいメソッドは、protectedからpublicにしてオーバーライドする
*/

/*
 * 参考
 * PHPによるデザインパターン
 * http://d.hatena.ne.jp/shimooka/20141212/1418364494
*/


/*
 * Target Class
 */
interface DisplaySourceFile
{
    //指定されたソースをハイライト表示する
    public function display();
}


/*
 * Adaptee Class(再利用されるクラス)
 */
class ShowFile
{
    private $filename;
    
    public function __construct($filename)
    {
        if (!is_readable($filename)) {
            throw new Exception('file "' . $filename . '" is not readable !');
        }
        $this->filename = $filename;
    }

    public function showPlain()
    {
        echo '<pre>';
        echo htmlspecialchars(file_get_contents($this->filename),   //file_get_contents():ファイル全体の文字列を返す
                             ENT_QUOTES, mb_internal_encoding());
        echo '</pre>';
    }

    public function showHighlight()
    {
        highlight_file($this->filename);                            //highlight_file():ファイル中のソースをハイライト表示する
    }
}


/*
 * Adapter Class(継承を利用するパターン)
 */
class DisplaySourceFileImpl extends ShowFile implements DisplaySourceFile
{
    public function __construct($fileName)
    {
        parent::__construct($fileName);
    }
    public function display()
    {
        parent::showHighlight();
    }
}


/*
 * Adapter Class(委譲を利用するパターン)
 */
class DisplaySourceFileImpl2 implements DisplaySourceFile
{
    private $showFile;
    
    public function __construct($fileName)
    {
        $this->showFile = new ShowFile($fileName);
    }
    
    public function display()
    {
        $this->showFile->showHighlight();
    }
}

/*
 * Client
 */
$fileName = './sample.php';
$dispSource = new DisplaySourceFileImpl($fileName);
$dispSource->display();
