<?php
ini_set( 'display_errors', 1 );
require_once '../phpLib/myLib.php';

/*
 * 目的
 * 1.オブジェクトの生成処理を切り出す
*/

/*
 * 参考
 * PHPによるデザインパターン
 * http://d.hatena.ne.jp/shimooka/20141215/1418620242
*/

/*
 * Product Class 
 */
interface Reader{
    public function read();
    public function display();
}

class CSVFileReader implements Reader{
    private $fileName;
    private $handler;
    
    public function __construct($fileName){
        if(!is_readable($fileName)){    //is_readable():ファイルの存在と読み込み可能をチェック
            throw Exception('file"' . $fileName .'"is not readable.');
        }
        $this->fileName = $fileName;
    }
    
    public function read(){
        $this->handler = fopen($this->fileName, 'r');
    }
    
    public function display(){
       while ($data = fgetcsv ($this->handler, 1000, ",")) {
           //csvファイルから読み取った1行を処理
            $num = count ($data);
            for ($c = 0; $c < $num; $c++) {
                //行頭の単語
                if ($c == 0) {
                    echo "<b>" . $data[$c] . "</b>";
                    echo "<ul>";
                //それ以外の単語
                }else {
                    echo "<li>";
                    echo $data[$c];
                    echo "</li>";
                }
            }
            echo "</ul>";
        }
        echo "</ul>";
        
        //最後にファイルハンドラを閉じる
        fclose ($this->handler);
    }
}

/*
 * Concrete Product Class 
 */
class XMLFileReader implements Reader{
    private $fileName;
    private $handler;

    public function __construct($fileName){
        if (!is_readable($fileName)) {
            throw new Exception('file "' . $fileName . '" is not readable.');
        }
        $this->fileName = $fileName;
    }

    public function read(){
        $this->handler = simplexml_load_file($this->fileName);  //xmlファイルの読み取りはこの関数で
    }

    private function convert($str){
        return mb_convert_encoding($str, mb_internal_encoding(), "auto");//mb_convert_encoding():文字列をエンコーディングする。
                                                                         //第三引数は変換前のエンコーディング。autoとすると、UTF-8やSJISで展開される
                                                                         //mb_internal_encoding():現在のエンコーディングを返す
    }

    public function display(){
        foreach ($this->handler->artist as $artist) {           //こんな感じで、xml内のタグを取得可能
            echo "<b>" . $this->convert($artist['name']) . "</b>";
            echo "<ul>";
            foreach ($artist->music as $music) {
                echo "<li>";
                echo $this->convert($music['name']);
                echo "</li>";
            }
            echo "</ul>";
        }
    }
}

/*
 * FactoryClass
 * 複雑化してきたら、Concrete Factory Classに分けてもいい
 */
class ReaderFactory{
    public function create($fileName){
        $reader = $this->createReader($fileName);
        return $reader;
    }
    
    public function createReader($fileName){
        //stripos()は文字列を検索し、対象の文字列の位置を返す
        $poscsv = stripos($fileName, 'csv');
        $posxml = stripos($fileName, 'xml');
        
        if($poscsv !== false){
            return new CSVFileReader($fileName);
        }else if($posxml !== false){
            return new XMLFileReader($fileName);
        }else{
            die('This file type is not supported:' . $fileName);
        }
    }
} 


/*
 * Client
 */
//ファイル名
$fileName = 'music.xml';
//$fileName = 'music.csv';

//Factoryクラスを使ってReaderを生成
$factory = new ReaderFactory();
$reader = $factory->create($fileName);

//Readerでファイル読み込み
$reader->read();
$reader->display();
