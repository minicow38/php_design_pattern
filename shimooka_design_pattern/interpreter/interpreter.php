<?php
/*
 * 目的
 * 1.言語の実装(字句解析＋構文解析)に使える
 *   字句解析:コードを意味のある字句(トークン)に分解すること
 *   構文解析:字句解析の結果を元に、コードが文法に即しているかチェックする
 */

/*
 * MEMO
 * Composite＋独自ロジックによる構文解析という印象を受けた。
 * 
 * 狭義では言語の構文解析に使えるはずだが、
 * 他にも色々な用途に応用できそうだ...
 * 
 * Expression Class間でのやりとりの実装が難しいと感じた。
 * 
 * 今回の例では、以下のような言語を想定する
 *  <Job> ::= begin <CommandList>
 *  <CommandList> ::= <Command>* end
 *  <Command> ::= diskspace | date | line
 * 
 */

/*
 * 参考
 * PHPによるデザインパターン
 * http://d.hatena.ne.jp/shimooka/20141217/1418788237
*/


/**
 * Abstract Expression Class
 */
interface Command {
    public function execute(Context $context);
}


/**
 * Nonterminal Expression Class
 * 再帰による構文解析を担当
 * ※今回は再帰を使っていないが、再帰を使う場合、Experssion Classを委譲で保持する。
 */
class JobCommand implements Command
{
    public function execute(Context $context)
    {
        if ($context->getCurrentCommand() !== 'begin') {
            throw new RuntimeException('illegal command ' . $context->getCurrentCommand());
        }
        $command_list = new CommandListCommand();
        $command_list->execute($context->next());
    }
}


class CommandListCommand implements Command
{
    public function execute(Context $context)
    {
        while (true) {
            $current_command = $context->getCurrentCommand();
            if (is_null($current_command)) {
                throw new RuntimeException('"end" not found ');
            } elseif ($current_command === 'end') {
                break;
            } else {
                $command = new CommandCommand();
                $command->execute($context);
            }
            $context->next();
        }
    }
}


/**
 * Terminal Expression Class
 */
class CommandCommand implements Command
{
    public function execute(Context $context)
    {
        //各々の小さな構文解析を担当する
        $current_command = $context->getCurrentCommand();
        if ($current_command === 'diskspace') {
            $path = './';
            $free_size = disk_free_space($path); //ディスク周りに関するライブラリ
            $max_size = disk_total_space($path);
            $ratio = $free_size / $max_size * 100;
            echo sprintf('Disk Free : %5.1dMB (%3d%%)<br>',
                         $free_size / 1024 / 1024,
                         $ratio);
        } elseif ($current_command === 'date') {
            echo date('Y/m/d H:i:s') . '<br>';
        } elseif ($current_command === 'line') {
            echo '--------------------<br>';
        } else {
            throw new RuntimeException('invalid command [' . $current_command . ']');
        }
    }
}


/**
 * Context Class
 * 各コマンドを保持するクラス。データ構造はこのクラスで規定する。
 * あくまで、データ構造に関する処理のみ担当する。
 */
class Context
{
    private $commands;                                  //各コマンドは配列で保持しておく
    private $current_index = 0;
    private $max_index = 0;
    public function __construct($command)
    {
        $this->commands = split(' +', trim($command));  //コマンド間の空白は取り除いておく
        $this->max_index = count($this->commands);
    }

    public function next()
    {
        $this->current_index++;
        return $this;
    }

    public function getCurrentCommand()
    {
        if (!array_key_exists($this->current_index, $this->commands)) {
            return null;
        }
        return trim($this->commands[$this->current_index]);
    }
}


/**
 * Client
 */
function execute($command) {
    $job = new JobCommand();
    try {
        $job->execute(new Context($command));
    } catch (Exception $e) {
        echo htmlspecialchars($e->getMessage(), ENT_QUOTES, mb_internal_encoding());
    }
    echo '<hr>';
}

    $command = (isset($_POST['command'])? $_POST['command'] : '');
    if ($command !== '') {
        execute($command);
    }
?>
<form action="" method="post">
input command:<input type="text" name="command" size="80" value="begin date line diskspace end">
<input type="submit">
</form>