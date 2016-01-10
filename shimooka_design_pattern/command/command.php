<?php
/*
 * 目的
 * 1.「要求」をデータ構造として保持することにより、柔軟な「要求の実行」を可能にする。
 *  ex)要求のlogging、要求のundo/redo
 * 2.「要求」に該当する処理を切り出して保守性を高める。
 * 
 */

/*
 * 命令をオブジェクトとして保持するパターン。
 * 
 * MEMO
 * 構造に関するポイント
 * 1.Invokerが管理者。
 * 2.CommandがRecieverを持つ。InvokerはRecieverを見ない。
 * 
 * 他パターンとの組み合わせ
 * 1.Commandの実行履歴を管理したい場合、Mementoパターンを適用することが考えられる
 * 2.InvokerでCommandを再帰ツリー構造で保持したい場合、Compositeパターンを適用することが考えられる
 */

/*
 * 参考
 * PHPによるデザインパターン
 * http://d.hatena.ne.jp/shimooka/20141216/1418705218
*/


/**
 * Receiver Class
 * Reciver内に各コマンドを定義しておかないといけないので、
 * Reciver自体はあとから変更する可能性が出てきそう。どうにかならないかなぁ。
 * →Decoratorパターン等で、機能を追加する?
 */
class File
{
    private $name;
    public function __construct($name)
    {
        $this->name = $name;
    }
    public function getName()
    {
        return $this->name;
    }
    //Reciever内部に関する処理は、Reciever内にまとめておく
    public function decompress()
    {
        echo $this->name . 'を展開しました<br>';
    }
    public function compress()
    {
        echo $this->name . 'を圧縮しました<br>';
    }
    public function create()
    {
        echo $this->name . 'を作成しました<br>';
    }
}


/**
 * Command Class
 */
interface Command {
    public function execute();
}


/**
 * ConcreteCommand Class
 */
class TouchCommand implements Command
{
    //こんな感じで、CommandがRecieverを保持するのがポイント。
    //Invokerからは、直接Recieverを操作しない
    //→Invoker内で保持するデータ構造で、複数のRecieverに対するCommandを保持できるようになる
    private $file;
    public function __construct(File $file)
    {
        $this->file = $file;
    }
    //Recieverに対する外部的な処理は、Command側で担当する
    public function execute()
    {
        $this->file->create();
    }
}

class CompressCommand implements Command
{
    private $file;
    public function __construct(File $file)
    {
        $this->file = $file;
    }
    public function execute()
    {
        $this->file->compress();
    }
}

class CopyCommand implements Command
{
    private $file;
    public function __construct(File $file)
    {
        $this->file = $file;
    }
    public function execute()
    {
        $file = new File('copy_of_' . $this->file->getName());
        $file->create();
    }
}


/**
 * Invoker Class
 * 各コマンドの調停は、このクラスで行う。
 * コマンドをしまうデータ構造を定義するのもこのクラス。
 * →データ構造を複数想定する場合、TmeplateMethodPatterなどですげ替えられるようにしておくと良いかも。
 */
class Queue
{
    private $commands;//複数のコマンドを保持するデータ構造
    private $current_index;
    public function __construct()
    {
        $this->commands = array();
        $this->current_index = 0;
    }
    public function addCommand(Command $command)
    {
        $this->commands[] = $command;
    }

    public function run()
    {
        while (!is_null($command = $this->next())) {
            $command->execute();
        }
    }

    private function next()
    {
        if (count($this->commands) === 0 ||
            count($this->commands) <= $this->current_index) {
            return null;
        } else {
            return $this->commands[$this->current_index++];
        }
    }
}


/**
 * Client
 */
$queue = new Queue();                           //Invoker
$file = new File("sample.txt");                 //Reciever
$queue->addCommand(new TouchCommand($file));    //Command
$queue->addCommand(new CompressCommand($file));
$queue->addCommand(new CopyCommand($file));

$queue->run();